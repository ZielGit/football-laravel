<?php

declare(strict_types=1);

namespace Tests\Unit\Football;

use App\Football\Domain\Exception\ExternalRateLimitExceeded;
use App\Football\Infrastructure\FootballData\FootballDataRateLimiter;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\RateLimiter;
use Illuminate\Cache\Repository;
use PHPUnit\Framework\TestCase;

final class FootballDataRateLimiterTest extends TestCase
{
    private function limiter(int $max): FootballDataRateLimiter
    {
        return new FootballDataRateLimiter(new RateLimiter(new Repository(new ArrayStore)), $max, 60, 'test-key');
    }

    public function test_allows_up_to_the_limit_within_the_window(): void
    {
        $limiter = $this->limiter(10);

        for ($i = 0; $i < 10; $i++) {
            $limiter->acquire();
        }

        $this->assertSame(0, $limiter->remaining());
    }

    public function test_throws_when_the_limit_is_exceeded_with_a_positive_wait(): void
    {
        $limiter = $this->limiter(3);

        for ($i = 0; $i < 3; $i++) {
            $limiter->acquire();
        }

        try {
            $limiter->acquire();
            $this->fail('Debió lanzar ExternalRateLimitExceeded');
        } catch (ExternalRateLimitExceeded $e) {
            $this->assertGreaterThan(0, $e->retryAfterSeconds);
            $this->assertLessThanOrEqual(60, $e->retryAfterSeconds);
        }
    }
}
