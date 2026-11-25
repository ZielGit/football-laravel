<?php

declare(strict_types=1);

namespace Tests\Unit\Football;

use App\Football\Application\Command\RequestCompetitionsRefresh\RequestCompetitionsRefreshHandler;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Tests\Support\FakeRefreshGuard;
use Tests\Support\InMemoryCompetitionRepository;
use Tests\Support\SpyJobDispatcher;

final class RequestCompetitionsRefreshHandlerTest extends TestCase
{
    private InMemoryCompetitionRepository $repo;

    private FakeRefreshGuard $guard;

    private SpyJobDispatcher $dispatcher;

    private RequestCompetitionsRefreshHandler $handler;

    protected function setUp(): void
    {
        $this->repo = new InMemoryCompetitionRepository;
        $this->guard = new FakeRefreshGuard;
        $this->dispatcher = new SpyJobDispatcher;
        $this->handler = new RequestCompetitionsRefreshHandler($this->repo, $this->guard, $this->dispatcher, 60);
    }

    public function test_dispatches_when_nothing_was_ever_synced(): void
    {
        $this->assertTrue($this->handler->handle());
        $this->assertSame(1, $this->dispatcher->refreshes);
    }

    public function test_dispatches_when_the_list_is_stale(): void
    {
        $this->repo->syncedAt = new DateTimeImmutable('-2 hours');

        $this->assertTrue($this->handler->handle());
    }

    public function test_does_nothing_when_the_list_is_fresh(): void
    {
        $this->repo->syncedAt = new DateTimeImmutable('-5 minutes');

        $this->assertFalse($this->handler->handle());
        $this->assertSame(0, $this->dispatcher->refreshes);
    }

    public function test_does_not_dispatch_twice_while_a_refresh_is_running(): void
    {
        $this->handler->handle();
        $this->handler->handle();

        $this->assertSame(1, $this->dispatcher->refreshes);
    }
}
