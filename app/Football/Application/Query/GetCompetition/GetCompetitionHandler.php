<?php

declare(strict_types=1);

namespace App\Football\Application\Query\GetCompetition;

use App\Football\Application\Port\ImportRegistry;
use App\Football\Domain\Repository\CompetitionRepository;

final readonly class GetCompetitionHandler
{
    public function __construct(
        private CompetitionRepository $competitions,
        private ImportRegistry $registry,
    ) {}

    /** @return CompetitionDetail|null null si la liga aún no está guardada localmente */
    public function handle(GetCompetitionQuery $query): ?CompetitionDetail
    {
        $competition = $this->competitions->find($query->competitionId);

        if ($competition === null) {
            return null;
        }

        return new CompetitionDetail(
            $competition,
            $this->competitions->countTeams($competition->id),
            $this->competitions->countPlayers($competition->id),
            $this->registry->find($competition->id),
        );
    }
}
