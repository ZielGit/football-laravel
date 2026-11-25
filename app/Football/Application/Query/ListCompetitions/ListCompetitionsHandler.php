<?php

declare(strict_types=1);

namespace App\Football\Application\Query\ListCompetitions;

use App\Football\Application\Port\CompetitionsRefreshGuard;
use App\Football\Domain\Repository\CompetitionRepository;

/** Lado lectura: responde solo con lo guardado, aunque football-data no esté disponible. */
final readonly class ListCompetitionsHandler
{
    public function __construct(
        private CompetitionRepository $competitions,
        private CompetitionsRefreshGuard $guard,
    ) {}

    public function handle(): CompetitionList
    {
        return new CompetitionList(
            $this->competitions->all(),
            $this->competitions->lastSyncedAt(),
            $this->guard->isRefreshing(),
        );
    }
}
