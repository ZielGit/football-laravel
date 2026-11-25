<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Football\Domain\Model\Page;
use App\Football\Domain\Model\Team;
use App\Football\Domain\Repository\TeamRepository;

final class InMemoryTeamRepository implements TeamRepository
{
    /** @var array<int, Team> */
    public array $items = [];

    public function __construct(private readonly InMemoryCompetitionRepository $competitions) {}

    public function paginate(int $page, int $perPage, ?string $search = null, ?int $competitionId = null): Page
    {
        return new Page(array_values($this->items), count($this->items), $page, $perPage);
    }

    public function find(int $id): ?Team
    {
        return $this->items[$id] ?? null;
    }

    public function saveMany(array $teams): void
    {
        foreach ($teams as $team) {
            $this->items[$team->id] = $team; // upsert por id
        }
    }

    public function syncCompetitionTeams(int $competitionId, array $teamIds): void
    {
        $this->competitions->teamLinks[$competitionId] = $teamIds;
    }

    public function competitionsOf(int $teamId): array
    {
        $result = [];

        foreach ($this->competitions->teamLinks as $competitionId => $teamIds) {
            if (in_array($teamId, $teamIds, true) && isset($this->competitions->items[$competitionId])) {
                $result[] = $this->competitions->items[$competitionId];
            }
        }

        return $result;
    }
}
