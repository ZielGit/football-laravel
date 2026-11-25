<?php

declare(strict_types=1);

namespace App\Football\Application\Command\SyncCompetitions;

use App\Football\Application\Port\CompetitionsRefreshGuard;
use App\Football\Domain\Gateway\FootballDataGateway;
use App\Football\Domain\Repository\CompetitionRepository;

/** Refresca la lista de competiciones guardada (la usa el job y el cron). */
final readonly class SyncCompetitionsHandler
{
    public function __construct(
        private FootballDataGateway $gateway,
        private CompetitionRepository $competitions,
        private CompetitionsRefreshGuard $guard,
    ) {}

    public function handle(): int
    {
        try {
            $list = $this->gateway->fetchCompetitions();
            $this->competitions->saveMany($list);

            return count($list);
        } finally {
            $this->guard->release();
        }
    }
}
