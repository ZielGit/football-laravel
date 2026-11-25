<?php

declare(strict_types=1);

namespace App\Football\Domain\Exception;

use Throwable;

final class ExternalServiceUnavailable extends ExternalServiceException
{
    public static function connection(Throwable $previous): self
    {
        return new self('No se pudo conectar con football-data.org.', 0, $previous);
    }

    public static function notConfigured(): self
    {
        return new self('El token de football-data.org no está configurado (FOOTBALL_DATA_TOKEN).');
    }

    public static function unexpectedStatus(int $status): self
    {
        return new self("football-data.org respondió con el estado HTTP {$status}.");
    }
}
