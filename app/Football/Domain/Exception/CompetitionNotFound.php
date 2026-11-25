<?php

declare(strict_types=1);

namespace App\Football\Domain\Exception;

final class CompetitionNotFound extends EntityNotFound
{
    public function __construct(int $id)
    {
        parent::__construct('Competición', $id);
    }

    public function errorCode(): string
    {
        return 'COMPETITION_NOT_FOUND';
    }
}
