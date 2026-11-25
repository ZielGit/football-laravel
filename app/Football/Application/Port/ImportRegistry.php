<?php

declare(strict_types=1);

namespace App\Football\Application\Port;

use App\Football\Domain\Model\LeagueImport;

interface ImportRegistry
{
    public function find(int $competitionId): ?LeagueImport;

    public function save(LeagueImport $import): void;
}
