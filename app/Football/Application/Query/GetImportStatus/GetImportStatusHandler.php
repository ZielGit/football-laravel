<?php

declare(strict_types=1);

namespace App\Football\Application\Query\GetImportStatus;

use App\Football\Application\Port\ImportRegistry;
use App\Football\Domain\Exception\CompetitionNotFound;
use App\Football\Domain\Model\LeagueImport;
use App\Football\Domain\Repository\CompetitionRepository;

final readonly class GetImportStatusHandler
{
    public function __construct(
        private ImportRegistry $registry,
        private CompetitionRepository $competitions,
    ) {}

    /**
     * @return LeagueImport|null null si la liga existe pero nunca se importó
     *
     * @throws CompetitionNotFound si no hay ni liga ni importación
     */
    public function handle(GetImportStatusQuery $query): ?LeagueImport
    {
        $import = $this->registry->find($query->competitionId);

        if ($import === null && $this->competitions->find($query->competitionId) === null) {
            throw new CompetitionNotFound($query->competitionId);
        }

        return $import;
    }
}
