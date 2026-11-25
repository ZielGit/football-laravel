<?php

declare(strict_types=1);

namespace App\Football\Domain\Repository;

use App\Football\Domain\Model\Competition;
use App\Football\Domain\Model\Page;
use App\Football\Domain\Model\Team;

interface TeamRepository
{
    /** @return Page<Team> */
    public function paginate(int $page, int $perPage, ?string $search = null, ?int $competitionId = null): Page;

    public function find(int $id): ?Team;

    /** @param  list<Team>  $teams */
    public function saveMany(array $teams): void;

    /** Reemplaza los equipos de la competición por los indicados. @param  list<int>  $teamIds */
    public function syncCompetitionTeams(int $competitionId, array $teamIds): void;

    /** @return list<Competition> */
    public function competitionsOf(int $teamId): array;
}
