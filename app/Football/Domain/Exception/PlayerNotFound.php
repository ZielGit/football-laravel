<?php

declare(strict_types=1);

namespace App\Football\Domain\Exception;

final class PlayerNotFound extends EntityNotFound
{
    public function __construct(int $id)
    {
        parent::__construct('Jugador', $id);
    }

    public function errorCode(): string
    {
        return 'PLAYER_NOT_FOUND';
    }
}
