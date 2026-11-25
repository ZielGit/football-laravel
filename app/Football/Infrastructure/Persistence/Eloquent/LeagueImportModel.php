<?php

declare(strict_types=1);

namespace App\Football\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;

class LeagueImportModel extends Model
{
    protected $table = 'league_imports';

    protected $primaryKey = 'competition_id';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'competition_id', 'status', 'message', 'retry_after_seconds',
        'teams_imported', 'players_imported', 'requested_by', 'finished_at',
    ];

    protected function casts(): array
    {
        return ['finished_at' => 'datetime'];
    }
}
