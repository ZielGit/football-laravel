<?php

namespace App\Http\Resources;

use App\Football\Domain\Model\Competition;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @property Competition $resource */
class CompetitionResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $c = $this->resource;

        return [
            'id' => $c->id,
            'name' => $c->name,
            'code' => $c->code,
            'type' => $c->type,
            'area' => $c->area,
            'emblem' => $c->emblem,
            'synced_at' => $c->syncedAt?->format(DATE_ATOM),
        ];
    }
}
