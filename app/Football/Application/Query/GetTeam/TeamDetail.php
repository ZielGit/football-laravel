<?php

declare(strict_types=1);

namespace App\Football\Application\Query\GetTeam;

use App\Football\Domain\Model\Competition;
use App\Football\Domain\Model\Player;
use App\Football\Domain\Model\Team;

final readonly class TeamDetail
{
    /**
     * @param  list<Competition>  $competitions
     * @param  list<Player>  $players
     */
    public function __construct(
        public Team $team,
        public array $competitions,
        public array $players,
    ) {}
}
