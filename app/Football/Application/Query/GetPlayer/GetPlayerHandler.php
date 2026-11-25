<?php

declare(strict_types=1);

namespace App\Football\Application\Query\GetPlayer;

use App\Football\Domain\Exception\PlayerNotFound;
use App\Football\Domain\Model\Player;
use App\Football\Domain\Repository\PlayerRepository;

final readonly class GetPlayerHandler
{
    public function __construct(private PlayerRepository $players) {}

    /** @throws PlayerNotFound */
    public function handle(GetPlayerQuery $query): Player
    {
        return $this->players->find($query->playerId) ?? throw new PlayerNotFound($query->playerId);
    }
}
