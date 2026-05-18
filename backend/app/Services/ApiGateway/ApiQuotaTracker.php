<?php

declare(strict_types=1);

namespace App\Services\ApiGateway;

use App\Models\ApiQuotaUsage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ApiQuotaTracker
{
    private const LIMITS = [
        'api_football'     => 100,
        'football_data_org' => 10000,
        'the_odds_api'     => 500,
        'open_weather_map' => 1000,
        'gnews'            => 100,
        'thesportsdb'      => 10000,
    ];

    private const SAFETY_MARGIN = 5;

    public function canCall(string $provider): bool
    {
        $limit = self::LIMITS[$provider] ?? PHP_INT_MAX;
        $used  = $this->getTodayCount($provider);

        return ($used + self::SAFETY_MARGIN) < $limit;
    }

    public function record(string $provider, int $calls = 1): void
    {
        $today    = Carbon::today()->format('Y-m-d');
        $cacheKey = "quota:{$provider}:{$today}";

        Cache::increment($cacheKey, $calls);
        Cache::expire($cacheKey, 86400);

        try {
            ApiQuotaUsage::updateOrCreate(
                ['provider' => $provider, 'date' => $today],
                ['quota_limit' => self::LIMITS[$provider] ?? 0]
            )->increment('requests_count', $calls);

            ApiQuotaUsage::where('provider', $provider)
                ->where('date', $today)
                ->update(['last_request_at' => Carbon::now()]);
        } catch (\Throwable $e) {
            Log::error("ApiQuotaTracker: impossible d'enregistrer usage DB", [
                'provider' => $provider,
                'error'    => $e->getMessage(),
            ]);
        }
    }

    public function getTodayCount(string $provider): int
    {
        $today    = Carbon::today()->format('Y-m-d');
        $cacheKey = "quota:{$provider}:{$today}";

        if ($count = Cache::get($cacheKey)) {
            return (int) $count;
        }

        try {
            $usage = ApiQuotaUsage::where('provider', $provider)
                ->where('date', $today)
                ->first();

            $count = $usage?->requests_count ?? 0;
            Cache::put($cacheKey, $count, 3600);

            return $count;
        } catch (\Throwable) {
            return 0;
        }
    }

    public function getUsagePercentage(string $provider): float
    {
        $limit = self::LIMITS[$provider] ?? 1;
        $used  = $this->getTodayCount($provider);

        return round(($used / $limit) * 100, 1);
    }

    public function allStatus(): array
    {
        return array_map(function (string $provider, int $limit): array {
            $used       = $this->getTodayCount($provider);
            $percentage = round(($used / $limit) * 100, 1);

            return [
                'provider'   => $provider,
                'used'       => $used,
                'limit'      => $limit,
                'remaining'  => max(0, $limit - $used),
                'percentage' => $percentage,
                'status'     => match(true) {
                    $percentage >= 95 => 'critical',
                    $percentage >= 80 => 'warning',
                    default           => 'ok',
                },
                'can_call'   => $this->canCall($provider),
            ];
        }, array_keys(self::LIMITS), self::LIMITS);
    }
}
