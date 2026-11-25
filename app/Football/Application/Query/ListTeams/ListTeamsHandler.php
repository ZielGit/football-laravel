<?php

declare(strict_types=1);

namespace App\Football\Application\Query\ListTeams;

use App\Football\Domain\Model\Page;
use App\Football\Domain\Model\Team;
use App\Football\Domain\Repository\TeamRepository;

final readonly class ListTeamsHandler
{
    public function __construct(private TeamRepository $teams) {}

    /** @return Page<Team> */
    public function handle(ListTeamsQuery $query): Page
    {
        return $this->teams->paginate($query->page, $query->perPage, $query->search, $query->competitionId);
    }
}
