<?php

declare(strict_types=1);

namespace App\Football\Infrastructure\Console;

use App\Football\Application\Command\SyncCompetitions\SyncCompetitionsHandler;
use App\Football\Application\Port\BackgroundJobDispatcher;
use App\Football\Application\Port\CompetitionsRefreshGuard;
use App\Football\Domain\Exception\ExternalServiceException;
use Illuminate\Console\Command;

final class SyncCompetitionsCommand extends Command
{
    protected $signature = 'football:sync-competitions {--now : Ejecuta en este proceso en lugar de encolar}';

    protected $description = 'Actualiza la lista de competiciones desde football-data.org (la corre el cron cada hora)';

    public function handle(
        SyncCompetitionsHandler $handler,
        CompetitionsRefreshGuard $guard,
        BackgroundJobDispatcher $dispatcher,
    ): int {
        if ($this->option('now')) {
            try {
                $guard->tryAcquire();
                $this->info("Competiciones sincronizadas: {$handler->handle()}");

                return self::SUCCESS;
            } catch (ExternalServiceException $e) {
                $this->error($e->getMessage());

                return self::FAILURE;
            }
        }

        if (! $guard->tryAcquire()) {
            $this->line('Ya hay una actualización en curso.');

            return self::SUCCESS;
        }

        $dispatcher->dispatchCompetitionsRefresh();
        $this->info('Actualización de competiciones encolada.');

        return self::SUCCESS;
    }
}
