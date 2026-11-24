<?php

declare(strict_types=1);

namespace App\Football\Domain\Model;

use DateTimeImmutable;

final readonly class Competition
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $code = null,
        public ?string $type = null,
        public ?string $area = null,
        public ?string $emblem = null,
        public ?DateTimeImmutable $syncedAt = null,
    ) {}
}
