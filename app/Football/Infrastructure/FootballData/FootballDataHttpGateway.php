<?php

declare(strict_types=1);

namespace App\Football\Infrastructure\FootballData;

use App\Football\Domain\Exception\ExternalRateLimitExceeded;
use App\Football\Domain\Exception\ExternalResourceNotFound;
use App\Football\Domain\Exception\ExternalServiceUnavailable;
use App\Football\Domain\Gateway\FootballDataGateway;
use App\Football\Domain\Model\Competition;
use App\Football\Domain\Model\LeagueSnapshot;
use App\Football\Domain\Model\Player;
use App\Football\Domain\Model\Team;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory as Http;
use Illuminate\Http\Client\Response;

/**
 * ÚNICA clase que habla con football-data.org (adaptador del puerto FootballDataGateway).
 * Traduce JSON externo a objetos de dominio y fallos HTTP a excepciones de dominio.
 */
final readonly class FootballDataHttpGateway implements FootballDataGateway
{
    public function __construct(
        private Http $http,
        private FootballDataRateLimiter $rateLimiter,
        private string $baseUrl,
        private ?string $token,
        private int $timeoutSeconds = 10,
    ) {}

    public function fetchCompetitions(): array
    {
        $payload = $this->get('/competitions');

        return array_values(array_map(
            fn (array $c): Competition => $this->mapCompetition($c),
            $payload['competitions'] ?? [],
        ));
    }

    public function fetchLeagueSnapshot(int $competitionId): LeagueSnapshot
    {
        $payload = $this->get("/competitions/{$competitionId}/teams", $competitionId);

        $teams = [];
        $players = [];

        foreach ($payload['teams'] ?? [] as $rawTeam) {
            $teams[] = $this->mapTeam($rawTeam);

            foreach ($rawTeam['squad'] ?? [] as $rawPlayer) {
                $players[] = $this->mapPlayer($rawPlayer, (int) $rawTeam['id']);
            }
        }

        $competition = $this->mapCompetition($payload['competition'] ?? ['id' => $competitionId, 'name' => "Competición {$competitionId}"]);

        return new LeagueSnapshot($competition, $teams, $players);
    }

    /** @return array<string, mixed> */
    private function get(string $path, ?int $competitionId = null): array
    {
        if ($this->token === null || $this->token === '') {
            throw ExternalServiceUnavailable::notConfigured();
        }

        $this->rateLimiter->acquire();

        try {
            $response = $this->http
                ->baseUrl($this->baseUrl)
                ->withHeaders(['X-Auth-Token' => $this->token])
                ->acceptJson()
                ->timeout($this->timeoutSeconds)
                ->get($path);
        } catch (ConnectionException $e) {
            throw ExternalServiceUnavailable::connection($e);
        }

        return $this->handle($response, $competitionId);
    }

    /** @return array<string, mixed> */
    private function handle(Response $response, ?int $competitionId): array
    {
        $status = $response->status();

        if ($response->successful()) {
            return $response->json() ?? [];
        }

        if ($status === 429) {
            // El proveedor también limita: respetamos su cabecera si viene.
            $retry = (int) ($response->header('Retry-After') ?: $response->header('X-RequestCounter-Reset') ?: 60);
            throw new ExternalRateLimitExceeded(max(1, $retry));
        }

        if ($status === 404 && $competitionId !== null) {
            throw new ExternalResourceNotFound($competitionId);
        }

        throw ExternalServiceUnavailable::unexpectedStatus($status);
    }

    /** @param  array<string, mixed>  $c */
    private function mapCompetition(array $c): Competition
    {
        return new Competition(
            (int) $c['id'],
            (string) ($c['name'] ?? "Competición {$c['id']}"),
            $c['code'] ?? null,
            $c['type'] ?? null,
            $c['area']['name'] ?? null,
            $c['emblem'] ?? null,
        );
    }

    /** @param  array<string, mixed>  $t */
    private function mapTeam(array $t): Team
    {
        return new Team(
            (int) $t['id'],
            (string) $t['name'],
            $t['shortName'] ?? null,
            $t['tla'] ?? null,
            $t['crest'] ?? null,
            $t['address'] ?? null,
            $t['website'] ?? null,
            isset($t['founded']) ? (int) $t['founded'] : null,
            $t['clubColors'] ?? null,
            $t['venue'] ?? null,
        );
    }

    /** @param  array<string, mixed>  $p */
    private function mapPlayer(array $p, int $teamId): Player
    {
        return new Player(
            (int) $p['id'],
            $teamId,
            (string) ($p['name'] ?? 'Sin nombre'),
            $p['position'] ?? null,
            // El plan gratuito no siempre entrega el dorsal; se guarda si viene.
            isset($p['shirtNumber']) ? (int) $p['shirtNumber'] : null,
            $p['dateOfBirth'] ?? null,
            $p['nationality'] ?? null,
        );
    }
}
