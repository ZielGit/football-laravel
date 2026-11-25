<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Football\Domain\Model\Competition;
use App\Football\Domain\Model\LeagueSnapshot;
use App\Football\Domain\Model\Player;
use App\Football\Domain\Model\Team;

final class Fixtures
{
    public static function snapshot(int $competitionId = 2021): LeagueSnapshot
    {
        return new LeagueSnapshot(
            new Competition($competitionId, 'Premier League', 'PL', 'LEAGUE', 'England'),
            [new Team(57, 'Arsenal FC', 'Arsenal', 'ARS'), new Team(65, 'Manchester City FC', 'Man City', 'MCI')],
            [
                new Player(1, 57, 'David Raya', 'Goalkeeper', 22),
                new Player(2, 57, 'Bukayo Saka', 'Offence', 7),
                new Player(3, 65, 'Erling Haaland', 'Offence', 9),
            ],
        );
    }

    /** @return array<string, mixed> JSON de ejemplo de GET /competitions/{id}/teams */
    public static function teamsPayload(): array
    {
        return [
            'competition' => ['id' => 2021, 'name' => 'Premier League', 'code' => 'PL', 'type' => 'LEAGUE', 'emblem' => 'pl.png'],
            'teams' => [[
                'id' => 57, 'name' => 'Arsenal FC', 'shortName' => 'Arsenal', 'tla' => 'ARS', 'crest' => 'ars.png',
                'founded' => 1886, 'venue' => 'Emirates Stadium', 'clubColors' => 'Red / White',
                'squad' => [
                    ['id' => 1, 'name' => 'David Raya', 'position' => 'Goalkeeper', 'dateOfBirth' => '1995-09-15', 'nationality' => 'Spain', 'shirtNumber' => 22],
                    ['id' => 2, 'name' => 'Bukayo Saka', 'position' => 'Offence', 'dateOfBirth' => '2001-09-05', 'nationality' => 'England'],
                ],
            ]],
        ];
    }
}
