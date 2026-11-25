<?php

declare(strict_types=1);

namespace Tests\Unit\Football;

use App\Football\Application\Query\GetImportStatus\GetImportStatusHandler;
use App\Football\Application\Query\GetImportStatus\GetImportStatusQuery;
use App\Football\Application\Query\GetPlayer\GetPlayerHandler;
use App\Football\Application\Query\GetPlayer\GetPlayerQuery;
use App\Football\Application\Query\GetTeam\GetTeamHandler;
use App\Football\Application\Query\GetTeam\GetTeamQuery;
use App\Football\Domain\Exception\CompetitionNotFound;
use App\Football\Domain\Exception\PlayerNotFound;
use App\Football\Domain\Exception\TeamNotFound;
use App\Football\Domain\Model\Competition;
use App\Football\Domain\Model\Player;
use App\Football\Domain\Model\Team;
use PHPUnit\Framework\TestCase;
use Tests\Support\InMemoryCompetitionRepository;
use Tests\Support\InMemoryImportRegistry;
use Tests\Support\InMemoryPlayerRepository;
use Tests\Support\InMemoryTeamRepository;

final class QueryHandlersTest extends TestCase
{
    public function test_get_team_returns_team_with_its_players_and_competitions(): void
    {
        $competitions = new InMemoryCompetitionRepository;
        $teams = new InMemoryTeamRepository($competitions);
        $players = new InMemoryPlayerRepository;
        $competitions->save(new Competition(2021, 'Premier League'));
        $teams->saveMany([new Team(57, 'Arsenal FC')]);
        $teams->syncCompetitionTeams(2021, [57]);
        $players->saveMany([new Player(1, 57, 'David Raya'), new Player(2, 65, 'Otro')]);

        $detail = (new GetTeamHandler($teams, $players))->handle(new GetTeamQuery(57));

        $this->assertSame('Arsenal FC', $detail->team->name);
        $this->assertCount(1, $detail->players);
        $this->assertSame('Premier League', $detail->competitions[0]->name);
    }

    public function test_get_team_throws_not_found_for_unknown_team(): void
    {
        $handler = new GetTeamHandler(new InMemoryTeamRepository(new InMemoryCompetitionRepository), new InMemoryPlayerRepository);

        $this->expectException(TeamNotFound::class);
        $handler->handle(new GetTeamQuery(999));
    }

    public function test_get_player_throws_not_found_for_unknown_player(): void
    {
        $this->expectException(PlayerNotFound::class);
        (new GetPlayerHandler(new InMemoryPlayerRepository))->handle(new GetPlayerQuery(999));
    }

    public function test_import_status_is_null_for_a_known_league_never_imported(): void
    {
        $competitions = new InMemoryCompetitionRepository;
        $competitions->save(new Competition(2021, 'Premier League'));

        $status = (new GetImportStatusHandler(new InMemoryImportRegistry, $competitions))->handle(new GetImportStatusQuery(2021));

        $this->assertNull($status);
    }

    public function test_import_status_throws_when_league_and_import_are_both_unknown(): void
    {
        $this->expectException(CompetitionNotFound::class);
        (new GetImportStatusHandler(new InMemoryImportRegistry, new InMemoryCompetitionRepository))->handle(new GetImportStatusQuery(1));
    }
}
