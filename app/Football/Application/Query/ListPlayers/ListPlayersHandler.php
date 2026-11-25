<?php

declare(strict_types=1);

namespace App\Football\Application\Query\ListPlayers;

use App\Football\Domain\Model\Page;
use App\Football\Domain\Model\Player;
use App\Football\Domain\Repository\PlayerRepository;

final readonly class ListPlayersHandler
{
    public function __construct(private PlayerRepository $players) {}

    /** @return Page<Player> */
    public function handle(ListPlayersQuery $query): Page
    {
        return $this->players->paginate(
            $query->page,
            $query->perPage,
            $query->search,
            $query->teamId,
            $query->position,
            $query->competitionId,
        );
    }
}
