<?php

declare(strict_types=1);

namespace App\Football\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TeamModel extends Model
{
    protected $table = 'teams';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'id', 'name', 'short_name', 'tla', 'crest', 'address', 'website', 'founded', 'club_colors', 'venue',
    ];

    /** @return BelongsToMany<CompetitionModel, $this> */
    public function competitions(): BelongsToMany
    {
        return $this->belongsToMany(CompetitionModel::class, 'competition_team', 'team_id', 'competition_id');
    }

    /** @return HasMany<PlayerModel, $this> */
    public function players(): HasMany
    {
        return $this->hasMany(PlayerModel::class, 'team_id');
    }
}
