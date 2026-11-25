<?php

declare(strict_types=1);

namespace App\Football\Application\Query\GetTeam;

final readonly class GetTeamQuery
{
    public function __construct(public int $teamId) {}
}
