<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Football\Application\Port\TransactionRunner;

final class PassThroughTransaction implements TransactionRunner
{
    public function run(callable $callback): mixed
    {
        return $callback();
    }
}
