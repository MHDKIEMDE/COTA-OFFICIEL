<?php

declare(strict_types=1);

namespace App\Services\ApiGateway;

use App\Services\ApiGateway\Adapters\MatchDataAdapter;
use App\Services\ApiGateway\Providers\ApiFootballProvider;
use App\Services\ApiGateway\Providers\FootballDataOrgProvider;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ApiGatewayService
{
    public function __construct(
        protected ApiFootballProvider    $apiFootball,
        protected FootballDataOrgProvider $footballDataOrg,
        protected ApiQuotaTracker        $quotaTracker,
        protected ApiFallbackHandler     $fallbackHandler
    ) {}

    public function getMatches(string $date): Collection
    {
        $cacheKey = "matches:{$date}";

        if ($cached = Cache::get($cacheKey)) {
            Log::info("ApiGateway: cache hit pour {$date}");
            return $cached;
        }

        if (!$this->quotaTracker->canCall('api_football')) {
            return $this->getFallbackMatches($date, $cacheKey);
        }

        try {
            $rawData = $this->apiFootball->fetchMatches($date);
            $matches = MatchDataAdapter::fromApiFootball($rawData);

            $this->quotaTracker->record('api_football', 1);
            Cache::put($cacheKey, $matches, $this->getCacheDuration($date));
            Cache::put("{$cacheKey}:stale", $matches, CacheStrategy::STALE_TTL);

            Log::info("ApiGateway: {$date} servi par api_football", ['count' => $matches->count()]);

            return $matches;
        } catch (\Throwable $e) {
            Log::error("ApiGateway: api_football échoué", ['error' => $e->getMessage(), 'date' => $date]);
            return $this->getFallbackMatches($date, $cacheKey);
        }
    }

    public function getLiveMatches(): Collection
    {
        $cacheKey = 'matches:live';

        if ($cached = Cache::get($cacheKey)) {
            return $cached;
        }

        if (!$this->quotaTracker->canCall('api_football')) {
            return collect();
        }

        try {
            $rawData = $this->apiFootball->fetchLive();
            $matches = MatchDataAdapter::fromApiFootball($rawData);

            $this->quotaTracker->record('api_football', 1);
            Cache::put($cacheKey, $matches, CacheStrategy::LIVE_TTL);

            return $matches;
        } catch (\Throwable $e) {
            Log::error("ApiGateway: live fetchLive échoué", ['error' => $e->getMessage()]);
            return collect();
        }
    }

    public function getQuotaStatus(): array
    {
        return $this->quotaTracker->allStatus();
    }

    private function getFallbackMatches(string $date, string $cacheKey): Collection
    {
        try {
            $rawData = $this->footballDataOrg->fetchMatches($date);
            $matches = MatchDataAdapter::fromFootballDataOrg($rawData);

            $this->fallbackHandler->log('api_football', 'football_data_org', $date);
            Cache::put($cacheKey, $matches, $this->getCacheDuration($date));
            Cache::put("{$cacheKey}:stale", $matches, CacheStrategy::STALE_TTL);

            Log::info("ApiGateway: {$date} servi par football_data_org (fallback)", ['count' => $matches->count()]);

            return $matches;
        } catch (\Throwable $e) {
            Log::error("ApiGateway: fallback football_data_org échoué", ['error' => $e->getMessage()]);
            $stale = Cache::get("{$cacheKey}:stale", collect());

            if ($stale->isNotEmpty()) {
                Log::warning("ApiGateway: utilisation du cache stale pour {$date}");
            }

            return $stale;
        }
    }

    private function getCacheDuration(string $date): int
    {
        $matchDate = Carbon::parse($date);

        if ($matchDate->isPast()) {
            return CacheStrategy::HISTORICAL_TTL;
        }

        if ($matchDate->isToday()) {
            return CacheStrategy::TODAY_TTL;
        }

        return CacheStrategy::FUTURE_TTL;
    }
}
