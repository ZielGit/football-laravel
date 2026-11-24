<?php

declare(strict_types=1);

namespace App\Football\Domain\Model;

/**
 * Foto de una liga tal como la entrega el proveedor externo:
 * la competición, sus equipos y los jugadores de cada plantilla.
 */
final readonly class LeagueSnapshot
{
    /**
     * @param  list<Team>  $teams
     * @param  list<Player>  $players
     */
    public function __construct(
        public Competition $competition,
        public array $teams,
        public array $players,
    ) {}

    /** @return list<int> */
    public function teamIds(): array
    {
        return array_map(static fn (Team $team): int => $team->id, $this->teams);
    }
}
