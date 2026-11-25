<?php

declare(strict_types=1);

namespace App\Football\Application\Command\ImportCompetition;

use App\Football\Application\Port\ImportRegistry;
use App\Football\Application\Port\TransactionRunner;
use App\Football\Domain\Exception\ExternalRateLimitExceeded;
use App\Football\Domain\Exception\ExternalServiceException;
use App\Football\Domain\Gateway\FootballDataGateway;
use App\Football\Domain\Model\LeagueImport;
use App\Football\Domain\Model\LeagueSnapshot;
use App\Football\Domain\Repository\CompetitionRepository;
use App\Football\Domain\Repository\PlayerRepository;
use App\Football\Domain\Repository\TeamRepository;

/**
 * Caso de uso (lado escritura): trae una liga de football-data.org y la guarda.
 * Una sola petición externa por liga (GET /competitions/{id}/teams incluye las plantillas).
 */
final readonly class ImportCompetitionHandler
{
    public function __construct(
        private FootballDataGateway $gateway,
        private CompetitionRepository $competitions,
        private TeamRepository $teams,
        private PlayerRepository $players,
        private ImportRegistry $registry,
        private TransactionRunner $transaction,
    ) {}

    /**
     * @throws ExternalServiceException el job decide si reintenta o abandona.
     */
    public function handle(ImportCompetitionCommand $command): LeagueImport
    {
        $import = $this->registry->find($command->competitionId) ?? LeagueImport::queued($command->competitionId);
        $this->registry->save($import = $import->running());

        try {
            $snapshot = $this->gateway->fetchLeagueSnapshot($command->competitionId);
            $this->persist($snapshot);
        } catch (ExternalRateLimitExceeded $e) {
            $this->registry->save($import->waitingForRateLimit($e->retryAfterSeconds));
            throw $e;
        } catch (ExternalServiceException $e) {
            $this->registry->save($import->failed($e->getMessage()));
            throw $e;
        }

        $completed = $import->completed(count($snapshot->teams), count($snapshot->players));
        $this->registry->save($completed);

        return $completed;
    }

    private function persist(LeagueSnapshot $snapshot): void
    {
        $this->transaction->run(function () use ($snapshot): void {
            $this->competitions->save($snapshot->competition);
            $this->teams->saveMany($snapshot->teams);
            $this->teams->syncCompetitionTeams($snapshot->competition->id, $snapshot->teamIds());
            $this->players->saveMany($snapshot->players);
        });
    }
}
