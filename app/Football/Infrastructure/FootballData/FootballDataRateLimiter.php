<?php

declare(strict_types=1);

namespace App\Football\Infrastructure\FootballData;

use App\Football\Domain\Exception\ExternalRateLimitExceeded;
use Illuminate\Cache\RateLimiter;

/**
 * Guarda de cupo compartida por TODOS los procesos (web, workers, cron) porque
 * se apoya en la caché (Redis). Se consume un cupo ANTES de cada llamada saliente.
 */
final readonly class FootballDataRateLimiter
{
    public function __construct(
        private RateLimiter $limiter,
        private int $maxAttempts,
        private int $decaySeconds,
        private string $key,
    ) {}

    /** @throws ExternalRateLimitExceeded si no queda cupo en la ventana actual */
    public function acquire(): void
    {
        $acquired = $this->limiter->attempt($this->key, $this->maxAttempts, static fn (): bool => true, $this->decaySeconds);

        if ($acquired === false) {
            throw new ExternalRateLimitExceeded(max(1, $this->limiter->availableIn($this->key)));
        }
    }

    public function remaining(): int
    {
        return $this->limiter->remaining($this->key, $this->maxAttempts);
    }
}
