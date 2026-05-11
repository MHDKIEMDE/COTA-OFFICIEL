<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Football\ApiFootballProvider;
use App\Services\Football\CacheProvider;
use App\Services\Football\FootballDataChain;
use App\Services\Football\SportradarProvider;
use App\Services\FootballApiService;
use App\Services\Sms\SmsProviderInterface;
use App\Services\Sms\LogSmsProvider;
use App\Services\Sms\TermiiSmsProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Chaîne de fallback football data : ApiFootball → Sportradar → LocalCache
        $this->app->singleton(FootballDataChain::class, function ($app) {
            return new FootballDataChain([
                new ApiFootballProvider($app->make(FootballApiService::class)),
                new SportradarProvider(),
                new CacheProvider(),
            ]);
        });

        $this->app->bind(SmsProviderInterface::class, function () {
            $provider = config('sms.provider', 'log');

            return match ($provider) {
                'termii' => new TermiiSmsProvider(),
                default => new LogSmsProvider(),
            };
        });

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
