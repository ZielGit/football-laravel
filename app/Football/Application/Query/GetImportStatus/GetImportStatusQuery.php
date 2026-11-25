<?php

declare(strict_types=1);

namespace App\Football\Application\Query\GetImportStatus;

final readonly class GetImportStatusQuery
{
    public function __construct(public int $competitionId) {}
}
