<?php

declare(strict_types=1);

namespace App\Football\Domain\Repository;

use App\Football\Domain\Model\Page;
use App\Football\Domain\Model\Player;

interface PlayerRepository
{
    /** @return Page<Player> */
    public function paginate(
        int $page,
        int $perPage,
        ?string $search = null,
        ?int $teamId = null,
        ?string $position = null,
        ?int $competitionId = null,
    ): Page;

    public function find(int $id): ?Player;

    /** @return list<Player> */
    public function byTeam(int $teamId): array;

    /** @param  list<Player>  $players */
    public function saveMany(array $players): void;
}
