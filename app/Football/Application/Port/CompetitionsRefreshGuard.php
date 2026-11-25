<?php

declare(strict_types=1);

namespace App\Football\Application\Port;

/** Evita encolar varias actualizaciones de la lista de competiciones a la vez. */
interface CompetitionsRefreshGuard
{
    public function tryAcquire(): bool;

    public function release(): void;

    public function isRefreshing(): bool;
}
