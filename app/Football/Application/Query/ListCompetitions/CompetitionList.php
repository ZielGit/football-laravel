<?php

declare(strict_types=1);

namespace App\Football\Application\Query\ListCompetitions;

use App\Football\Domain\Model\Competition;
use DateTimeImmutable;

final readonly class CompetitionList
{
    /** @param  list<Competition>  $competitions */
    public function __construct(
        public array $competitions,
        public ?DateTimeImmutable $lastSyncedAt,
        public bool $refreshing,
    ) {}
}
