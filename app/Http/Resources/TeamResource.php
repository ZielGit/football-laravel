<?php

namespace App\Http\Resources;

use App\Football\Domain\Model\Team;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @property Team $resource */
class TeamResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $t = $this->resource;

        return [
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
        ];
    }
}
