<?php

declare(strict_types=1);

namespace App\Football\Application\Query\GetCompetition;

final readonly class GetCompetitionQuery
{
    public function __construct(public int $competitionId) {}
}
