<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Football\Domain\Model\Competition;
use App\Football\Domain\Repository\CompetitionRepository;
use DateTimeImmutable;

final class InMemoryCompetitionRepository implements CompetitionRepository
{
    /** @var array<int, Competition> */
    public array $items = [];

    /** @var array<int, list<int>> competitionId => teamIds (lo llena InMemoryTeamRepository) */
    public array $teamLinks = [];

    public ?DateTimeImmutable $syncedAt = null;

    public function all(): array
    {
        return array_values($this->items);
    }

    public function find(int $id): ?Competition
    {
        return $this->items[$id] ?? null;
    }

    public function saveMany(array $competitions): void
    {
        foreach ($competitions as $competition) {
            $this->items[$competition->id] = $competition;
        }
        $this->syncedAt = new DateTimeImmutable;
    }

    public function save(Competition $competition): void
    {
        $this->items[$competition->id] = $competition;
    }

    public function lastSyncedAt(): ?DateTimeImmutable
    {
        return $this->syncedAt;
    }

    public function countTeams(int $competitionId): int
    {
        return count($this->teamLinks[$competitionId] ?? []);
    }

    public function countPlayers(int $competitionId): int
    {
        return 0;
    }
}
