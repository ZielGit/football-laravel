<?php

declare(strict_types=1);

namespace App\Football\Application\Command\RequestCompetitionsRefresh;

use App\Football\Application\Port\BackgroundJobDispatcher;
use App\Football\Application\Port\CompetitionsRefreshGuard;
use App\Football\Domain\Repository\CompetitionRepository;
use DateTimeImmutable;

final readonly class RequestCompetitionsRefreshHandler
{
    public function __construct(
        private CompetitionRepository $competitions,
        private CompetitionsRefreshGuard $guard,
        private BackgroundJobDispatcher $dispatcher,
        private int $ttlMinutes,
    ) {}

    /** @return bool true si se encoló una actualización */
    public function handle(): bool
    {
        $last = $this->competitions->lastSyncedAt();
        $stale = $last === null || $last < new DateTimeImmutable("-{$this->ttlMinutes} minutes");

        if (! $stale || ! $this->guard->tryAcquire()) {
            return false;
        }

        $this->dispatcher->dispatchCompetitionsRefresh();

        return true;
    }
}
