<?php

declare(strict_types=1);

namespace App\Football\Infrastructure\Persistence\Eloquent;

use App\Football\Domain\Model\Page;
use App\Football\Domain\Model\Player;
use App\Football\Domain\Repository\PlayerRepository;

final class EloquentPlayerRepository implements PlayerRepository
{
    public function paginate(
        int $page,
        int $perPage,
        ?string $search = null,
        ?int $teamId = null,
        ?string $position = null,
        ?int $competitionId = null,
    ): Page {
        $paginator = PlayerModel::query()
            ->with('team:id,name')
            ->when($search, function ($query, string $term) {
                $query->where('name', 'like', '%'.addcslashes($term, '%_\\').'%');
            })
            ->when($teamId, fn ($query, int $id) => $query->where('team_id', $id))
            ->when($position, fn ($query, string $value) => $query->where('position', $value))
            ->when($competitionId, fn ($query, int $id) => $query->whereHas(
                'team.competitions',
                fn ($q) => $q->where('competitions.id', $id),
            ))
            ->orderBy('name')
            ->paginate($perPage, ['*'], 'page', $page);

        return new Page(
            $paginator->getCollection()->map(fn (PlayerModel $m): Player => self::toDomain($m))->values()->all(),
            $paginator->total(),
            $paginator->currentPage(),
            $paginator->perPage(),
        );
    }

    public function find(int $id): ?Player
    {
        $model = PlayerModel::query()->with('team:id,name')->find($id);

        return $model === null ? null : self::toDomain($model);
    }

    public function byTeam(int $teamId): array
    {
        return PlayerModel::query()->where('team_id', $teamId)->orderBy('name')->get()
            ->map(fn (PlayerModel $m): Player => self::toDomain($m))->values()->all();
    }

    public function saveMany(array $players): void
    {
        $now = now();

        foreach (array_chunk($players, 500) as $chunk) {
            $rows = array_map(static fn (Player $p): array => [
                'id' => $p->id,
                'team_id' => $p->teamId,
                'name' => $p->name,
                'position' => $p->position,
                'shirt_number' => $p->shirtNumber,
                'date_of_birth' => $p->dateOfBirth,
                'nationality' => $p->nationality,
                'created_at' => $now,
                'updated_at' => $now,
            ], $chunk);

            PlayerModel::query()->upsert($rows, ['id'], [
                'team_id', 'name', 'position', 'shirt_number', 'date_of_birth', 'nationality', 'updated_at',
            ]);
        }
    }

    private static function toDomain(PlayerModel $m): Player
    {
        return new Player(
            (int) $m->id,
            (int) $m->team_id,
            $m->name,
            $m->position,
            $m->shirt_number === null ? null : (int) $m->shirt_number,
            $m->date_of_birth?->format('Y-m-d'),
            $m->nationality,
            $m->relationLoaded('team') ? $m->team?->name : null,
        );
    }
}
