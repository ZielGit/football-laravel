<?php

declare(strict_types=1);

namespace App\Football\Infrastructure\Persistence\Eloquent;

use App\Football\Application\Port\TransactionRunner;
use Illuminate\Support\Facades\DB;

final class DatabaseTransactionRunner implements TransactionRunner
{
    public function run(callable $callback): mixed
    {
        return DB::transaction($callback);
    }
}
