<?php

declare(strict_types=1);

namespace App\Football\Infrastructure\Persistence\Eloquent;

use App\Football\Domain\Model\Competition;
use App\Football\Domain\Model\Page;
use App\Football\Domain\Model\Team;
use App\Football\Domain\Repository\TeamRepository;

final class EloquentTeamRepository implements TeamRepository
{
    public function paginate(int $page, int $perPage, ?string $search = null, ?int $competitionId = null): Page
    {
        $paginator = TeamModel::query()
            ->when($search, function ($query, string $term) {
                $like = '%'.addcslashes($term, '%_\\').'%';
                $query->where(fn ($q) => $q->where('name', 'like', $like)
                    ->orWhere('short_name', 'like', $like)
                    ->orWhere('tla', 'like', $like));
            })
            ->when($competitionId, fn ($query, int $id) => $query->whereHas(
                'competitions',
                fn ($q) => $q->where('competitions.id', $id),
            ))
            ->orderBy('name')
            ->paginate($perPage, ['*'], 'page', $page);

        return new Page(
            $paginator->getCollection()->map(fn (TeamModel $m): Team => self::toDomain($m))->values()->all(),
            $paginator->total(),
            $paginator->currentPage(),
            $paginator->perPage(),
        );
    }

    public function find(int $id): ?Team
    {
        $model = TeamModel::query()->find($id);

        return $model === null ? null : self::toDomain($model);
    }

    public function saveMany(array $teams): void
    {
        if ($teams === []) {
            return;
        }

        $now = now();
        $rows = array_map(static fn (Team $t): array => [
            'id' => $t->id,
            'name' => $t->name,
            'short_name' => $t->shortName,
            'tla' => $t->tla,
            'crest' => $t->crest,
            'address' => $t->address,
            'website' => $t->website,
            'founded' => $t->founded,
            'club_colors' => $t->clubColors,
            'venue' => $t->venue,
            'created_at' => $now,
            'updated_at' => $now,
        ], $teams);

        // upsert por id externo: idempotente, sin duplicados.
        TeamModel::query()->upsert($rows, ['id'], [
            'name', 'short_name', 'tla', 'crest', 'address', 'website', 'founded', 'club_colors', 'venue', 'updated_at',
        ]);
    }

    public function syncCompetitionTeams(int $competitionId, array $teamIds): void
    {
        CompetitionModel::query()->findOrFail($competitionId)->teams()->sync($teamIds);
    }

    public function competitionsOf(int $teamId): array
    {
        $team = TeamModel::query()->find($teamId);

        if ($team === null) {
            return [];
        }

        return $team->competitions()->orderBy('name')->get()
            ->map(fn (CompetitionModel $m): Competition => new Competition(
                (int) $m->id, $m->name, $m->code, $m->type, $m->area, $m->emblem,
            ))->values()->all();
    }

    private static function toDomain(TeamModel $m): Team
    {
        return new Team(
            (int) $m->id,
            $m->name,
            $m->short_name,
            $m->tla,
            $m->crest,
            $m->address,
            $m->website,
            $m->founded === null ? null : (int) $m->founded,
            $m->club_colors,
            $m->venue,
        );
    }
}
