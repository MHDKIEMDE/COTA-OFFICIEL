<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\ApiQuotaUsage;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class MonitorApiQuotasJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const PROVIDERS = [
        'api_football',
        'football_data_org',
        'the_odds_api',
        'open_weather_map',
        'gnews',
        'thesportsdb',
    ];

    public function handle(): void
    {
        foreach (self::PROVIDERS as $provider) {
            $usage = ApiQuotaUsage::where('provider', $provider)
                ->where('date', today())
                ->first();

            if (!$usage || $usage->quota_limit === 0) {
                continue;
            }

            $percentage = round(($usage->requests_count / $usage->quota_limit) * 100, 1);

            if ($percentage >= 95) {
                Log::critical("ApiMonitor: [{$provider}] quota critique à {$percentage}%", [
                    'used'  => $usage->requests_count,
                    'limit' => $usage->quota_limit,
                ]);
                $this->notifyAdmins($provider, $percentage, 'critical');
            } elseif ($percentage >= 80) {
                Log::warning("ApiMonitor: [{$provider}] quota en alerte à {$percentage}%", [
                    'used'  => $usage->requests_count,
                    'limit' => $usage->quota_limit,
                ]);
                $this->notifyAdmins($provider, $percentage, 'warning');
            }
        }
    }

    private function notifyAdmins(string $provider, float $percentage, string $level): void
    {
        try {
            $admins = User::where('is_admin', true)->get();

            if ($admins->isEmpty()) {
                return;
            }

            $emoji   = $level === 'critical' ? '🚨' : '⚠️';
            $message = "{$emoji} API {$provider} : {$percentage}% du quota journalier utilisé";

            Log::info("ApiMonitor: notification admins envoyée", [
                'provider' => $provider,
                'level'    => $level,
                'message'  => $message,
            ]);
        } catch (\Throwable $e) {
            Log::error("ApiMonitor: impossible de notifier les admins", ['error' => $e->getMessage()]);
        }
    }
}
