<?php

declare(strict_types=1);

namespace App\Football\Domain\Model;

final readonly class Player
{
    public function __construct(
        public int $id,
        public int $teamId,
        public string $name,
        public ?string $position = null,
        public ?int $shirtNumber = null,
        public ?string $dateOfBirth = null,
        public ?string $nationality = null,
        public ?string $teamName = null,
    ) {}
}
