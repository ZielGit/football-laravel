<?php

declare(strict_types=1);

namespace App\Football\Infrastructure\Queue;

use App\Football\Application\Port\BackgroundJobDispatcher;

final class LaravelBackgroundJobDispatcher implements BackgroundJobDispatcher
{
    public function dispatchLeagueImport(int $competitionId): void
    {
        ImportCompetitionJob::dispatch($competitionId);
    }

    public function dispatchCompetitionsRefresh(): void
    {
        SyncCompetitionsJob::dispatch();
    }
}
