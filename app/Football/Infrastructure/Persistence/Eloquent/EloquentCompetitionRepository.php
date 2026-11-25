<?php

declare(strict_types=1);

namespace App\Football\Infrastructure\Persistence\Eloquent;

use App\Football\Domain\Model\Competition;
use App\Football\Domain\Repository\CompetitionRepository;
use DateTimeImmutable;

final class EloquentCompetitionRepository implements CompetitionRepository
{
    public function all(): array
    {
        return CompetitionModel::query()->orderBy('name')->get()
            ->map(fn (CompetitionModel $m): Competition => self::toDomain($m))
            ->values()->all();
    }

    public function find(int $id): ?Competition
    {
        $model = CompetitionModel::query()->find($id);

        return $model === null ? null : self::toDomain($model);
    }

    public function saveMany(array $competitions): void
    {
        foreach ($competitions as $competition) {
            $this->persist($competition, now());
        }
    }

    public function save(Competition $competition): void
    {
        // Importar una liga no cuenta como "lista de competiciones sincronizada".
        $this->persist($competition, null);
    }

    public function lastSyncedAt(): ?DateTimeImmutable
    {
        $value = CompetitionModel::query()->max('synced_at');

        return $value === null ? null : new DateTimeImmutable((string) $value);
    }

    public function countTeams(int $competitionId): int
    {
        return CompetitionModel::query()->find($competitionId)?->teams()->count() ?? 0;
    }

    public function countPlayers(int $competitionId): int
    {
        return PlayerModel::query()
            ->whereHas('team.competitions', fn ($q) => $q->where('competitions.id', $competitionId))
            ->count();
    }

    private function persist(Competition $competition, mixed $syncedAt): void
    {
        $attributes = array_filter([
            'name' => $competition->name,
            'code' => $competition->code,
            'type' => $competition->type,
            'area' => $competition->area,
            'emblem' => $competition->emblem,
            'synced_at' => $syncedAt,
        ], static fn ($value): bool => $value !== null);

        CompetitionModel::query()->updateOrCreate(['id' => $competition->id], $attributes);
    }

    private static function toDomain(CompetitionModel $m): Competition
    {
        return new Competition(
            (int) $m->id,
            $m->name,
            $m->code,
            $m->type,
            $m->area,
            $m->emblem,
            $m->synced_at?->toDateTimeImmutable(),
        );
    }
}
