<?php

declare(strict_types=1);

namespace App\Football\Application\Query\ListTeams;

final readonly class ListTeamsQuery
{
    public function __construct(
        public int $page = 1,
        public int $perPage = 20,
        public ?string $search = null,
        public ?int $competitionId = null,
    ) {}
}
