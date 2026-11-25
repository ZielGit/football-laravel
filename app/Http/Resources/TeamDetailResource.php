<?php

namespace App\Http\Resources;

use App\Football\Application\Query\GetTeam\TeamDetail;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @property TeamDetail $resource */
class TeamDetailResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $d = $this->resource;

        return [
            ...(new TeamResource($d->team))->toArray($request),
            'competitions' => CompetitionResource::collection($d->competitions)->resolve($request),
            'players' => PlayerResource::collection($d->players)->resolve($request),
        ];
    }
}
