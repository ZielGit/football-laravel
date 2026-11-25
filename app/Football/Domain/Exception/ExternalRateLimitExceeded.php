<?php

declare(strict_types=1);

namespace App\Football\Domain\Exception;

final class ExternalRateLimitExceeded extends ExternalServiceException
{
    public function __construct(public readonly int $retryAfterSeconds)
    {
        parent::__construct(sprintf(
            'Se alcanzó el límite de peticiones a football-data.org. Reintente en %d segundos.',
            $retryAfterSeconds,
        ));
    }
}
