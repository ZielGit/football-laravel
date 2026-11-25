<?php

declare(strict_types=1);

namespace App\Football\Application\Port;

/** Puerto de salida para encolar trabajo en segundo plano (la cola es un detalle de infraestructura). */
interface BackgroundJobDispatcher
{
    public function dispatchLeagueImport(int $competitionId): void;

    public function dispatchCompetitionsRefresh(): void;
}
