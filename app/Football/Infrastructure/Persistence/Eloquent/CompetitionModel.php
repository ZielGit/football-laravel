<?php

declare(strict_types=1);

namespace App\Football\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CompetitionModel extends Model
{
    protected $table = 'competitions';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = ['id', 'name', 'code', 'type', 'area', 'emblem', 'synced_at'];

    protected function casts(): array
    {
        return ['synced_at' => 'datetime'];
    }

    /** @return BelongsToMany<TeamModel, $this> */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(TeamModel::class, 'competition_team', 'competition_id', 'team_id');
    }
}
