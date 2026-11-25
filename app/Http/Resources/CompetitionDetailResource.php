<?php

namespace App\Http\Resources;

use App\Football\Application\Query\GetCompetition\CompetitionDetail;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @property CompetitionDetail $resource */
class CompetitionDetailResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $d = $this->resource;

        return [
            ...(new CompetitionResource($d->competition))->toArray($request),
            'teams_count' => $d->teamsCount,
            'players_count' => $d->playersCount,
            'import' => $d->import === null ? null : (new LeagueImportResource($d->import))->toArray($request),
        ];
    }
}
