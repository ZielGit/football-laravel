<?php

declare(strict_types=1);

namespace App\Football\Domain\Model;

final readonly class Team
{
    public function __construct(
        public int $id,
        public string $name,
        public ?string $shortName = null,
        public ?string $tla = null,
        public ?string $crest = null,
        public ?string $address = null,
        public ?string $website = null,
        public ?int $founded = null,
        public ?string $clubColors = null,
        public ?string $venue = null,
    ) {}
}
