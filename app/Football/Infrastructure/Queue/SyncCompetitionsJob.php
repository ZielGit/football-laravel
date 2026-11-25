<?php

declare(strict_types=1);

namespace App\Football\Infrastructure\Queue;

use App\Football\Application\Command\SyncCompetitions\SyncCompetitionsHandler;
use App\Football\Application\Port\CompetitionsRefreshGuard;
use App\Football\Domain\Exception\ExternalRateLimitExceeded;
use App\Football\Domain\Exception\ExternalServiceUnavailable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

/** Refresca la lista de competiciones. La dispara el cron (cada hora) o una consulta con la lista vieja. */
final class SyncCompetitionsJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 0;

    public int $uniqueFor = 900;

    private const MAX_UNAVAILABLE_ATTEMPTS = 4;

    public function uniqueId(): string
    {
        return 'competitions-sync';
    }

    public function retryUntil(): \DateTimeInterface
    {
        return now()->addMinutes(15);
    }

    public function handle(SyncCompetitionsHandler $handler, CompetitionsRefreshGuard $guard): void
    {
        try {
            $handler->handle();
        } catch (ExternalRateLimitExceeded $e) {
            $guard->tryAcquire(); // el handler la soltó; seguimos "actualizando" hasta el reintento
            $this->release($e->retryAfterSeconds + 1);
        } catch (ExternalServiceUnavailable $e) {
            if ($this->attempts() >= self::MAX_UNAVAILABLE_ATTEMPTS) {
                $this->fail($e);

                return;
            }

            $guard->tryAcquire();
            $this->release(30 * $this->attempts());
        }
    }
}
