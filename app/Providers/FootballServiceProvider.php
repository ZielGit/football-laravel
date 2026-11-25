<?php

declare(strict_types=1);

namespace App\Providers;

use App\Football\Application\Command\RequestCompetitionImport\RequestCompetitionImportHandler;
use App\Football\Application\Command\RequestCompetitionsRefresh\RequestCompetitionsRefreshHandler;
use App\Football\Application\Port\BackgroundJobDispatcher;
use App\Football\Application\Port\CompetitionsRefreshGuard;
use App\Football\Application\Port\ImportRegistry;
use App\Football\Application\Port\TransactionRunner;
use App\Football\Domain\Gateway\FootballDataGateway;
use App\Football\Domain\Repository\CompetitionRepository;
use App\Football\Domain\Repository\PlayerRepository;
use App\Football\Domain\Repository\TeamRepository;
use App\Football\Infrastructure\Console\SyncCompetitionsCommand;
use App\Football\Infrastructure\FootballData\FootballDataHttpGateway;
use App\Football\Infrastructure\FootballData\FootballDataRateLimiter;
use App\Football\Infrastructure\Persistence\Eloquent\DatabaseTransactionRunner;
use App\Football\Infrastructure\Persistence\Eloquent\EloquentCompetitionRepository;
use App\Football\Infrastructure\Persistence\Eloquent\EloquentImportRegistry;
use App\Football\Infrastructure\Persistence\Eloquent\EloquentPlayerRepository;
use App\Football\Infrastructure\Persistence\Eloquent\EloquentTeamRepository;
use App\Football\Infrastructure\Queue\CacheCompetitionsRefreshGuard;
use App\Football\Infrastructure\Queue\LaravelBackgroundJobDispatcher;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\ServiceProvider;

/**
 * Composition root del contexto Football: aquí (y solo aquí) se enlazan
 * los puertos del dominio con sus adaptadores de infraestructura.
 */
final class FootballServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CompetitionRepository::class, EloquentCompetitionRepository::class);
        $this->app->bind(TeamRepository::class, EloquentTeamRepository::class);
        $this->app->bind(PlayerRepository::class, EloquentPlayerRepository::class);
        $this->app->bind(ImportRegistry::class, EloquentImportRegistry::class);
        $this->app->bind(TransactionRunner::class, DatabaseTransactionRunner::class);
        $this->app->bind(BackgroundJobDispatcher::class, LaravelBackgroundJobDispatcher::class);
        $this->app->bind(CompetitionsRefreshGuard::class, CacheCompetitionsRefreshGuard::class);

        $this->app->singleton(FootballDataRateLimiter::class, fn ($app) => new FootballDataRateLimiter(
            $app->make(RateLimiter::class),
            (int) config('football.rate_limit.max_attempts'),
            (int) config('football.rate_limit.decay_seconds'),
            (string) config('football.rate_limit.key'),
        ));

        $this->app->bind(FootballDataGateway::class, fn ($app) => new FootballDataHttpGateway(
            $app->make(HttpFactory::class),
            $app->make(FootballDataRateLimiter::class),
            (string) config('football.base_url'),
            config('football.token'),
            (int) config('football.timeout'),
        ));

        $this->app->when(RequestCompetitionImportHandler::class)
            ->needs('$importTtlMinutes')->giveConfig('football.import_ttl_minutes');

        $this->app->when(RequestCompetitionsRefreshHandler::class)
            ->needs('$ttlMinutes')->giveConfig('football.competitions_ttl_minutes');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([SyncCompetitionsCommand::class]);
        }
    }
}
