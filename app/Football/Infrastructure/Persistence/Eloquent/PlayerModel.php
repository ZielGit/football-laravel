<?php

declare(strict_types=1);

namespace App\Football\Infrastructure\Persistence\Eloquent;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property Carbon|null $date_of_birth
 */
class PlayerModel extends Model
{
    protected $table = 'players';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = ['id', 'team_id', 'name', 'position', 'shirt_number', 'date_of_birth', 'nationality'];

    protected function casts(): array
    {
        return ['date_of_birth' => 'date:Y-m-d'];
    }

    /** @return BelongsTo<TeamModel, $this> */
    public function team(): BelongsTo
    {
        return $this->belongsTo(TeamModel::class, 'team_id');
    }
}
