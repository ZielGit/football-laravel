<?php

declare(strict_types=1);

namespace App\Football\Application\Query\GetPlayer;

final readonly class GetPlayerQuery
{
    public function __construct(public int $playerId) {}
}
