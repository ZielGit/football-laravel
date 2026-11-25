<?php

namespace App\Http\Resources;

use App\Football\Domain\Model\LeagueImport;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @property LeagueImport $resource */
class LeagueImportResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $i = $this->resource;

        return [
            'competition_id' => $i->competitionId,
            'status' => $i->status->value,
            'active' => $i->isActive(),
            'message' => $i->message,
            'retry_after_seconds' => $i->retryAfterSeconds,
            'teams_imported' => $i->teamsImported,
            'players_imported' => $i->playersImported,
            'updated_at' => $i->updatedAt->format(DATE_ATOM),
            'finished_at' => $i->finishedAt?->format(DATE_ATOM),
        ];
    }
}
