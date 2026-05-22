<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Football\ApiFootballProvider as FootballChainProvider;
use App\Services\Football\CacheProvider;
use App\Services\Football\FootballDataChain;
use App\Services\Football\SportradarProvider;
use App\Services\FootballApiService;
use App\Services\ApiGateway\ApiGatewayService;
use App\Services\ApiGateway\ApiQuotaTracker;
use App\Services\ApiGateway\ApiFallbackHandler;
use App\Services\ApiGateway\Providers\ApiFootballProvider as GatewayApiFootballProvider;
use App\Services\ApiGateway\Providers\FootballDataOrgProvider;
use App\Services\RapidApiService;
use App\Services\PredictionAlgorithmService;
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
                new FootballChainProvider($app->make(FootballApiService::class)),
                new SportradarProvider(),
                new CacheProvider(),
            ]);
        });

        // ApiGatewayService — nouvelle architecture multi-API
        $this->app->singleton(ApiGatewayService::class, function ($app) {
            return new ApiGatewayService(
                new GatewayApiFootballProvider($app->make(FootballApiService::class)),
                new FootballDataOrgProvider(),
                $app->make(ApiQuotaTracker::class),
                $app->make(ApiFallbackHandler::class),
            );
        });

        $this->app->singleton(RapidApiService::class, fn() => new RapidApiService());

        // PredictionAlgorithmService avec injection du 10ème critère (RapidAPI)
        $this->app->singleton(PredictionAlgorithmService::class, function ($app) {
            return new PredictionAlgorithmService(
                $app->make(FootballApiService::class),
                $app->make(RapidApiService::class),
            );
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
