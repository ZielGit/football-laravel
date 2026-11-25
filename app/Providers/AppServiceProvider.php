<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Passport::personalAccessTokensExpireIn(now()->addHours((int) config('auth.token_ttl_hours')));

        // Límite de NUESTRA API (distinto al límite de football-data.org).
        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(120)->by($request->user()?->getAuthIdentifier() ?: $request->ip()));

        // Freno a la fuerza bruta en el login.
        RateLimiter::for('login', fn (Request $request) => Limit::perMinute(10)->by($request->ip().'|'.strtolower((string) $request->input('email'))));
    }
}
