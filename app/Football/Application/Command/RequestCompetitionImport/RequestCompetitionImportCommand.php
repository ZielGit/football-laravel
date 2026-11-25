<?php

declare(strict_types=1);

namespace App\Football\Application\Command\RequestCompetitionImport;

final readonly class RequestCompetitionImportCommand
{
    public function __construct(public int $competitionId, public ?int $requestedBy = null) {}
}
