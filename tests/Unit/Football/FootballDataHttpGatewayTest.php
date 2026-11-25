<?php

declare(strict_types=1);

namespace Tests\Unit\Football;

use App\Football\Domain\Exception\ExternalRateLimitExceeded;
use App\Football\Domain\Exception\ExternalResourceNotFound;
use App\Football\Domain\Exception\ExternalServiceUnavailable;
use App\Football\Infrastructure\FootballData\FootballDataHttpGateway;
use App\Football\Infrastructure\FootballData\FootballDataRateLimiter;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\RateLimiter;
use Illuminate\Cache\Repository;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory;
use PHPUnit\Framework\TestCase;
use Tests\Support\Fixtures;

final class FootballDataHttpGatewayTest extends TestCase
{
    private function gateway(Factory $http, int $limit = 10, ?string $token = 'secret-token'): FootballDataHttpGateway
    {
        $limiter = new FootballDataRateLimiter(new RateLimiter(new Repository(new ArrayStore)), $limit, 60, 'k');

        return new FootballDataHttpGateway($http, $limiter, 'https://api.example.test/v4', $token, 5);
    }

    public function test_maps_the_league_snapshot_with_teams_and_squads(): void
    {
        $http = new Factory;
        $http->fake(['*' => $http->response(Fixtures::teamsPayload(), 200)]);

        $snapshot = $this->gateway($http)->fetchLeagueSnapshot(2021);

        $this->assertSame('Premier League', $snapshot->competition->name);
        $this->assertCount(1, $snapshot->teams);
        $this->assertCount(2, $snapshot->players);
        $this->assertSame(22, $snapshot->players[0]->shirtNumber);
        $this->assertNull($snapshot->players[1]->shirtNumber);
        $this->assertSame(57, $snapshot->players[1]->teamId);
        $http->assertSent(fn ($request) => $request->hasHeader('X-Auth-Token', 'secret-token')
            && str_ends_with($request->url(), '/competitions/2021/teams'));
    }

    public function test_maps_the_competitions_list(): void
    {
        $http = new Factory;
        $http->fake(['*' => $http->response(['competitions' => [
            ['id' => 2021, 'name' => 'Premier League', 'code' => 'PL', 'type' => 'LEAGUE', 'area' => ['name' => 'England'], 'emblem' => 'x.png'],
        ]], 200)]);

        $list = $this->gateway($http)->fetchCompetitions();

        $this->assertCount(1, $list);
        $this->assertSame('England', $list[0]->area);
    }

    public function test_404_becomes_external_resource_not_found(): void
    {
        $http = new Factory;
        $http->fake(['*' => $http->response([], 404)]);

        $this->expectException(ExternalResourceNotFound::class);
        $this->gateway($http)->fetchLeagueSnapshot(999);
    }

    public function test_429_from_provider_becomes_rate_limit_with_retry_after(): void
    {
        $http = new Factory;
        $http->fake(['*' => $http->response([], 429, ['Retry-After' => '30'])]);

        try {
            $this->gateway($http)->fetchCompetitions();
            $this->fail('Debió lanzar ExternalRateLimitExceeded');
        } catch (ExternalRateLimitExceeded $e) {
            $this->assertSame(30, $e->retryAfterSeconds);
        }
    }

    public function test_server_error_becomes_service_unavailable(): void
    {
        $http = new Factory;
        $http->fake(['*' => $http->response([], 500)]);

        $this->expectException(ExternalServiceUnavailable::class);
        $this->gateway($http)->fetchCompetitions();
    }

    public function test_connection_failure_becomes_service_unavailable(): void
    {
        $http = new Factory;
        $http->fake(static function (): never {
            throw new ConnectionException('timeout');
        });

        $this->expectException(ExternalServiceUnavailable::class);
        $this->gateway($http)->fetchCompetitions();
    }

    public function test_missing_token_fails_without_calling_the_provider(): void
    {
        $http = new Factory;
        $http->fake();

        try {
            $this->gateway($http, token: null)->fetchCompetitions();
            $this->fail('Debió lanzar ExternalServiceUnavailable');
        } catch (ExternalServiceUnavailable) {
            $http->assertNothingSent();
            $this->addToAssertionCount(1);
        }
    }

    public function test_local_rate_limit_blocks_the_call_before_it_leaves_the_server(): void
    {
        $http = new Factory;
        $http->fake(['*' => $http->response(['competitions' => []], 200)]);
        $gateway = $this->gateway($http, limit: 1);

        $gateway->fetchCompetitions();

        try {
            $gateway->fetchCompetitions();
            $this->fail('Debió lanzar ExternalRateLimitExceeded');
        } catch (ExternalRateLimitExceeded) {
            $http->assertSentCount(1);
            $this->addToAssertionCount(1);
        }
    }
}
