<?php

declare(strict_types=1);

namespace App\Football\Domain\Repository;

use App\Football\Domain\Model\Competition;
use DateTimeImmutable;

interface CompetitionRepository
{
    /** @return list<Competition> */
    public function all(): array;

    public function find(int $id): ?Competition;

    /** @param  list<Competition>  $competitions */
    public function saveMany(array $competitions): void;

    public function save(Competition $competition): void;

    public function lastSyncedAt(): ?DateTimeImmutable;

    public function countTeams(int $competitionId): int;

    public function countPlayers(int $competitionId): int;
}
