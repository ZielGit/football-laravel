<?php

declare(strict_types=1);

namespace App\Football\Domain\Exception;

final class TeamNotFound extends EntityNotFound
{
    public function __construct(int $id)
    {
        parent::__construct('Equipo', $id);
    }

    public function errorCode(): string
    {
        return 'TEAM_NOT_FOUND';
    }
}
