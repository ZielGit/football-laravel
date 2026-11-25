<?php

declare(strict_types=1);

namespace App\Football\Application\Query\GetCompetition;

use App\Football\Domain\Model\Competition;
use App\Football\Domain\Model\LeagueImport;

final readonly class CompetitionDetail
{
    public function __construct(
        public Competition $competition,
        public int $teamsCount,
        public int $playersCount,
        public ?LeagueImport $import,
    ) {}
}
