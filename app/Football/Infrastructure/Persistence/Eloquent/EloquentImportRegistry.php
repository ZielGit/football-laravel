<?php

declare(strict_types=1);

namespace App\Football\Infrastructure\Persistence\Eloquent;

use App\Football\Application\Port\ImportRegistry;
use App\Football\Domain\Model\ImportStatus;
use App\Football\Domain\Model\LeagueImport;
use DateTimeImmutable;

final class EloquentImportRegistry implements ImportRegistry
{
    public function find(int $competitionId): ?LeagueImport
    {
        $m = LeagueImportModel::query()->find($competitionId);

        if ($m === null) {
            return null;
        }

        return new LeagueImport(
            (int) $m->competition_id,
            ImportStatus::from($m->status),
            new DateTimeImmutable($m->updated_at->toDateTimeString()),
            $m->message,
            $m->retry_after_seconds === null ? null : (int) $m->retry_after_seconds,
            $m->teams_imported === null ? null : (int) $m->teams_imported,
            $m->players_imported === null ? null : (int) $m->players_imported,
            $m->requested_by === null ? null : (int) $m->requested_by,
            $m->finished_at?->toDateTimeImmutable(),
        );
    }

    public function save(LeagueImport $import): void
    {
        LeagueImportModel::query()->updateOrCreate(
            ['competition_id' => $import->competitionId],
            [
                'status' => $import->status->value,
                'message' => $import->message,
                'retry_after_seconds' => $import->retryAfterSeconds,
                'teams_imported' => $import->teamsImported,
                'players_imported' => $import->playersImported,
                'requested_by' => $import->requestedBy,
                'finished_at' => $import->finishedAt,
            ],
        );
    }
}
