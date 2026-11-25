<?php

declare(strict_types=1);

namespace App\Football\Application\Query\ListPlayers;

final readonly class ListPlayersQuery
{
    public function __construct(
        public int $page = 1,
        public int $perPage = 20,
        public ?string $search = null,
        public ?int $teamId = null,
        public ?string $position = null,
        public ?int $competitionId = null,
    ) {}
}
