<?php

namespace App\Http\Resources;

use App\Football\Domain\Model\Player;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @property Player $resource */
class PlayerResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $p = $this->resource;

        return [
            'id' => $p->id,
            'name' => $p->name,
            'position' => $p->position,
            'shirt_number' => $p->shirtNumber,
            'date_of_birth' => $p->dateOfBirth,
            'nationality' => $p->nationality,
            'team_id' => $p->teamId,
            'team_name' => $p->teamName,
        ];
    }
}
