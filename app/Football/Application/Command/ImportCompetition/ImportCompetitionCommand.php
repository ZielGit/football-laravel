<?php

declare(strict_types=1);

namespace App\Football\Application\Command\ImportCompetition;

final readonly class ImportCompetitionCommand
{
    public function __construct(public int $competitionId) {}
}
