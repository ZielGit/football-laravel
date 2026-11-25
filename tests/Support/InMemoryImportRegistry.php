<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Football\Application\Port\ImportRegistry;
use App\Football\Domain\Model\LeagueImport;

final class InMemoryImportRegistry implements ImportRegistry
{
    /** @var array<int, LeagueImport> */
    public array $items = [];

    public function find(int $competitionId): ?LeagueImport
    {
        return $this->items[$competitionId] ?? null;
    }

    public function save(LeagueImport $import): void
    {
        $this->items[$import->competitionId] = $import;
    }
}
