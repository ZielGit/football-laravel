<?php

declare(strict_types=1);

namespace App\Football\Application\Command\RequestCompetitionImport;

use App\Football\Application\Port\BackgroundJobDispatcher;
use App\Football\Application\Port\ImportRegistry;
use App\Football\Domain\Model\LeagueImport;

/**
 * Pide importar una liga sin tocar football-data en la petición HTTP:
 * registra el pedido y encola el job. Es idempotente: no duplica trabajo
 * si ya hay una importación activa o una reciente.
 */
final readonly class RequestCompetitionImportHandler
{
    public function __construct(
        private ImportRegistry $registry,
        private BackgroundJobDispatcher $dispatcher,
        private int $importTtlMinutes,
    ) {}

    public function handle(RequestCompetitionImportCommand $command): LeagueImport
    {
        $current = $this->registry->find($command->competitionId);

        if ($current !== null && ($current->isActive() || $current->isFresh($this->importTtlMinutes))) {
            return $current;
        }

        $queued = LeagueImport::queued($command->competitionId, $command->requestedBy);
        $this->registry->save($queued);
        $this->dispatcher->dispatchLeagueImport($command->competitionId);

        return $queued;
    }
}
