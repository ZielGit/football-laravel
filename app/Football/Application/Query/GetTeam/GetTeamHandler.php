<?php

declare(strict_types=1);

namespace App\Football\Application\Query\GetTeam;

use App\Football\Domain\Exception\TeamNotFound;
use App\Football\Domain\Repository\PlayerRepository;
use App\Football\Domain\Repository\TeamRepository;

final readonly class GetTeamHandler
{
    public function __construct(
        private TeamRepository $teams,
        private PlayerRepository $players,
    ) {}

    /** @throws TeamNotFound */
    public function handle(GetTeamQuery $query): TeamDetail
    {
        $team = $this->teams->find($query->teamId) ?? throw new TeamNotFound($query->teamId);

        return new TeamDetail(
            $team,
            $this->teams->competitionsOf($team->id),
            $this->players->byTeam($team->id),
        );
    }
}
