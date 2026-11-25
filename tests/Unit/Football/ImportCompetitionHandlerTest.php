<?php

declare(strict_types=1);

namespace Tests\Unit\Football;

use App\Football\Application\Command\ImportCompetition\ImportCompetitionCommand;
use App\Football\Application\Command\ImportCompetition\ImportCompetitionHandler;
use App\Football\Domain\Exception\ExternalRateLimitExceeded;
use App\Football\Domain\Exception\ExternalServiceUnavailable;
use App\Football\Domain\Model\ImportStatus;
use PHPUnit\Framework\TestCase;
use Tests\Support\FakeFootballDataGateway;
use Tests\Support\Fixtures;
use Tests\Support\InMemoryCompetitionRepository;
use Tests\Support\InMemoryImportRegistry;
use Tests\Support\InMemoryPlayerRepository;
use Tests\Support\InMemoryTeamRepository;
use Tests\Support\PassThroughTransaction;

final class ImportCompetitionHandlerTest extends TestCase
{
    private FakeFootballDataGateway $gateway;

    private InMemoryCompetitionRepository $competitions;

    private InMemoryTeamRepository $teams;

    private InMemoryPlayerRepository $players;

    private InMemoryImportRegistry $registry;

    private ImportCompetitionHandler $handler;

    protected function setUp(): void
    {
        $this->gateway = new FakeFootballDataGateway(Fixtures::snapshot());
        $this->competitions = new InMemoryCompetitionRepository;
        $this->teams = new InMemoryTeamRepository($this->competitions);
        $this->players = new InMemoryPlayerRepository;
        $this->registry = new InMemoryImportRegistry;
        $this->handler = new ImportCompetitionHandler(
            $this->gateway, $this->competitions, $this->teams, $this->players, $this->registry, new PassThroughTransaction,
        );
    }

    public function test_imports_competition_teams_and_players_and_marks_completed(): void
    {
        $result = $this->handler->handle(new ImportCompetitionCommand(2021));

        $this->assertSame(ImportStatus::Completed, $result->status);
        $this->assertSame(2, $result->teamsImported);
        $this->assertSame(3, $result->playersImported);
        $this->assertCount(1, $this->competitions->items);
        $this->assertCount(2, $this->teams->items);
        $this->assertCount(3, $this->players->items);
        $this->assertSame(2, $this->competitions->countTeams(2021));
        $this->assertSame(1, $this->gateway->calls, 'Una liga debe costar una sola petición externa');
    }

    public function test_importing_the_same_league_twice_does_not_duplicate_records(): void
    {
        $this->handler->handle(new ImportCompetitionCommand(2021));
        $this->handler->handle(new ImportCompetitionCommand(2021));

        $this->assertCount(1, $this->competitions->items);
        $this->assertCount(2, $this->teams->items);
        $this->assertCount(3, $this->players->items);
    }

    public function test_rate_limit_marks_waiting_and_rethrows_so_the_job_can_release(): void
    {
        $this->gateway->failWith = new ExternalRateLimitExceeded(42);

        try {
            $this->handler->handle(new ImportCompetitionCommand(2021));
            $this->fail('Debió relanzar la excepción');
        } catch (ExternalRateLimitExceeded) {
            $import = $this->registry->find(2021);
            $this->assertSame(ImportStatus::WaitingRateLimit, $import->status);
            $this->assertSame(42, $import->retryAfterSeconds);
            $this->assertCount(0, $this->teams->items);
        }
    }

    public function test_provider_failure_marks_failed_and_keeps_previous_data_untouched(): void
    {
        $this->handler->handle(new ImportCompetitionCommand(2021));
        $this->gateway->failWith = new ExternalServiceUnavailable('caído');

        try {
            $this->handler->handle(new ImportCompetitionCommand(2021));
            $this->fail('Debió relanzar la excepción');
        } catch (ExternalServiceUnavailable) {
            $this->assertSame(ImportStatus::Failed, $this->registry->find(2021)->status);
            $this->assertCount(2, $this->teams->items, 'Lo ya guardado debe seguir disponible');
        }
    }
}
