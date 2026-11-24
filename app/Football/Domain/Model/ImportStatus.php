<?php

declare(strict_types=1);

namespace App\Football\Domain\Model;

enum ImportStatus: string
{
    case Queued = 'queued';
    case Running = 'running';
    case WaitingRateLimit = 'waiting_rate_limit';
    case Completed = 'completed';
    case Failed = 'failed';

    public function isActive(): bool
    {
        return match ($this) {
            self::Queued, self::Running, self::WaitingRateLimit => true,
            default => false,
        };
    }
}
