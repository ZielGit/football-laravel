<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Football\Application\Port\BackgroundJobDispatcher;

final class SpyJobDispatcher implements BackgroundJobDispatcher
{
    /** @var list<int> */
    public array $imports = [];

    public int $refreshes = 0;

    public function dispatchLeagueImport(int $competitionId): void
    {
        $this->imports[] = $competitionId;
    }

    public function dispatchCompetitionsRefresh(): void
    {
        $this->refreshes++;
    }
}
