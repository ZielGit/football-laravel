<?php

declare(strict_types=1);

namespace App\Football\Infrastructure\Queue;

use App\Football\Application\Command\ImportCompetition\ImportCompetitionCommand;
use App\Football\Application\Command\ImportCompetition\ImportCompetitionHandler;
use App\Football\Domain\Exception\ExternalRateLimitExceeded;
use App\Football\Domain\Exception\ExternalResourceNotFound;
use App\Football\Domain\Exception\ExternalServiceUnavailable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Importa una liga en segundo plano. Si no hay cupo de football-data, se devuelve
 * a la cola con la espera exacta que indica el limitador (no se pierde ni se falla).
 */
final class ImportCompetitionJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Sin tope de intentos: los reintentos por límite de cupo no son errores. */
    public int $tries = 0;

    public int $uniqueFor = 1800;

    private const MAX_UNAVAILABLE_ATTEMPTS = 4;

    public function __construct(public readonly int $competitionId) {}

    public function uniqueId(): string
    {
        return 'league-import-'.$this->competitionId;
    }

    public function retryUntil(): \DateTimeInterface
    {
        return now()->addMinutes(30);
    }

    public function handle(ImportCompetitionHandler $handler): void
    {
        try {
            $handler->handle(new ImportCompetitionCommand($this->competitionId));
        } catch (ExternalRateLimitExceeded $e) {
            $this->release($e->retryAfterSeconds + 1);
        } catch (ExternalResourceNotFound $e) {
            $this->fail($e); // reintentar no ayuda: la liga no existe en el proveedor
        } catch (ExternalServiceUnavailable $e) {
            if ($this->attempts() >= self::MAX_UNAVAILABLE_ATTEMPTS) {
                $this->fail($e);

                return;
            }

            $this->release(30 * $this->attempts());
        }
    }
}
