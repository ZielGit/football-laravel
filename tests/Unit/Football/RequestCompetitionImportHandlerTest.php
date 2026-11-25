<?php

declare(strict_types=1);

namespace Tests\Unit\Football;

use App\Football\Application\Command\RequestCompetitionImport\RequestCompetitionImportCommand;
use App\Football\Application\Command\RequestCompetitionImport\RequestCompetitionImportHandler;
use App\Football\Domain\Model\ImportStatus;
use App\Football\Domain\Model\LeagueImport;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Tests\Support\InMemoryImportRegistry;
use Tests\Support\SpyJobDispatcher;

final class RequestCompetitionImportHandlerTest extends TestCase
{
    private InMemoryImportRegistry $registry;

    private SpyJobDispatcher $dispatcher;

    private RequestCompetitionImportHandler $handler;

    protected function setUp(): void
    {
        $this->registry = new InMemoryImportRegistry;
        $this->dispatcher = new SpyJobDispatcher;
        $this->handler = new RequestCompetitionImportHandler($this->registry, $this->dispatcher, 60);
    }

    public function test_queues_and_dispatches_when_the_league_was_never_imported(): void
    {
        $result = $this->handler->handle(new RequestCompetitionImportCommand(2021, 7));

        $this->assertSame(ImportStatus::Queued, $result->status);
        $this->assertSame([2021], $this->dispatcher->imports);
        $this->assertSame(7, $this->registry->find(2021)->requestedBy);
    }

    public function test_does_not_dispatch_again_while_an_import_is_active(): void
    {
        $this->registry->save(LeagueImport::queued(2021)->running());

        $this->handler->handle(new RequestCompetitionImportCommand(2021));

        $this->assertSame([], $this->dispatcher->imports);
    }

    public function test_does_not_dispatch_when_a_recent_import_is_still_fresh(): void
    {
        $this->registry->save(LeagueImport::queued(2021)->completed(20, 500));

        $this->handler->handle(new RequestCompetitionImportCommand(2021));

        $this->assertSame([], $this->dispatcher->imports);
    }

    public function test_dispatches_again_when_the_previous_import_is_old(): void
    {
        $old = new DateTimeImmutable('-3 hours');
        $this->registry->save(new LeagueImport(2021, ImportStatus::Completed, $old, finishedAt: $old));

        $this->handler->handle(new RequestCompetitionImportCommand(2021));

        $this->assertSame([2021], $this->dispatcher->imports);
    }

    public function test_dispatches_again_after_a_failed_import(): void
    {
        $this->registry->save(LeagueImport::queued(2021)->failed('caído'));

        $this->handler->handle(new RequestCompetitionImportCommand(2021));

        $this->assertSame([2021], $this->dispatcher->imports);
    }

    public function test_an_abandoned_active_import_does_not_block_a_new_request(): void
    {
        $stale = new DateTimeImmutable('-2 hours');
        $this->registry->save(new LeagueImport(2021, ImportStatus::Running, $stale));

        $this->handler->handle(new RequestCompetitionImportCommand(2021));

        $this->assertSame([2021], $this->dispatcher->imports);
    }
}
