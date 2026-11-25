<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Football\Application\Port\CompetitionsRefreshGuard;

final class FakeRefreshGuard implements CompetitionsRefreshGuard
{
    public bool $held = false;

    public function tryAcquire(): bool
    {
        if ($this->held) {
            return false;
        }

        return $this->held = true;
    }

    public function release(): void
    {
        $this->held = false;
    }

    public function isRefreshing(): bool
    {
        return $this->held;
    }
}
