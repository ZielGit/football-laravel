<?php

declare(strict_types=1);

namespace App\Football\Domain\Exception;

use RuntimeException;

abstract class EntityNotFound extends RuntimeException
{
    public function __construct(public readonly string $entity, public readonly int $entityId)
    {
        parent::__construct(sprintf('%s con id %d no existe en los datos guardados.', $entity, $entityId));
    }

    abstract public function errorCode(): string;
}
