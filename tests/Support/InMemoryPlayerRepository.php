<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Football\Domain\Model\Page;
use App\Football\Domain\Model\Player;
use App\Football\Domain\Repository\PlayerRepository;

final class InMemoryPlayerRepository implements PlayerRepository
{
    /** @var array<int, Player> */
    public array $items = [];

    public function paginate(
        int $page,
        int $perPage,
        ?string $search = null,
        ?int $teamId = null,
        ?string $position = null,
        ?int $competitionId = null,
    ): Page {
        return new Page(array_values($this->items), count($this->items), $page, $perPage);
    }

    public function find(int $id): ?Player
    {
        return $this->items[$id] ?? null;
    }

    public function byTeam(int $teamId): array
    {
        return array_values(array_filter($this->items, static fn (Player $p): bool => $p->teamId === $teamId));
    }

    public function saveMany(array $players): void
    {
        foreach ($players as $player) {
            $this->items[$player->id] = $player;
        }
    }
}
