<?php

declare(strict_types=1);

namespace App\Football\Infrastructure\Queue;

use App\Football\Application\Port\CompetitionsRefreshGuard;
use Illuminate\Contracts\Cache\Repository;

final readonly class CacheCompetitionsRefreshGuard implements CompetitionsRefreshGuard
{
    private const KEY = 'football:competitions:refreshing';

    public function __construct(private Repository $cache) {}

    public function tryAcquire(): bool
    {
        return $this->cache->add(self::KEY, true, now()->addMinutes(10));
    }

    public function release(): void
    {
        $this->cache->forget(self::KEY);
    }

    public function isRefreshing(): bool
    {
        return $this->cache->has(self::KEY);
    }
}
