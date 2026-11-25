<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Football\Domain\Gateway\FootballDataGateway;
use App\Football\Domain\Model\Competition;
use App\Football\Domain\Model\LeagueSnapshot;
use Throwable;

final class FakeFootballDataGateway implements FootballDataGateway
{
    public int $calls = 0;

    public ?Throwable $failWith = null;

    /** @param  list<Competition>  $competitions */
    public function __construct(public ?LeagueSnapshot $snapshot = null, public array $competitions = []) {}

    public function fetchCompetitions(): array
    {
        $this->calls++;

        if ($this->failWith !== null) {
            throw $this->failWith;
        }

        return $this->competitions;
    }

    public function fetchLeagueSnapshot(int $competitionId): LeagueSnapshot
    {
        $this->calls++;

        if ($this->failWith !== null) {
            throw $this->failWith;
        }

        return $this->snapshot ?? throw new \LogicException('Snapshot no configurado');
    }
}
