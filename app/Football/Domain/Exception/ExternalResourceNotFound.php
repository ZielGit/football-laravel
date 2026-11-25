<?php

declare(strict_types=1);

namespace App\Football\Domain\Exception;

final class ExternalResourceNotFound extends ExternalServiceException
{
    public function __construct(int $competitionId)
    {
        parent::__construct("football-data.org no tiene la competición {$competitionId} (o el plan del token no la incluye).");
    }
}
