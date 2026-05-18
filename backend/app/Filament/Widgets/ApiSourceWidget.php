<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\ApiQuotaUsage;
use Carbon\Carbon;
use Filament\Widgets\Widget;

class ApiSourceWidget extends Widget
{
    protected static string $view = 'filament.widgets.api-source-widget';

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    private const PROVIDERS = [
        'api_football'     => ['label' => 'API-Football',      'icon' => '⚡', 'limit' => 100],
        'football_data_org'=> ['label' => 'Football-Data.org', 'icon' => '⚽', 'limit' => 10],
        'the_odds_api'     => ['label' => 'The Odds API',      'icon' => '📊', 'limit' => 500],
        'open_weather_map' => ['label' => 'OpenWeatherMap',    'icon' => '🌤', 'limit' => 1000],
        'gnews'            => ['label' => 'GNews',             'icon' => '📰', 'limit' => 100],
        'thesportsdb'      => ['label' => 'TheSportsDB',       'icon' => '🆓', 'limit' => 0],
    ];

    public function getViewData(): array
    {
        $usages = ApiQuotaUsage::whereDate('date', Carbon::today())
            ->get()
            ->keyBy('provider');

        $providers = [];
        $globalAlert = null;

        foreach (self::PROVIDERS as $key => $meta) {
            $usage = $usages->get($key);

            $used       = $usage?->requests_count ?? 0;
            $limit      = $usage?->quota_limit     ?? $meta['limit'];
            $percentage = ($limit > 0) ? round($used / $limit * 100, 1) : 0.0;
            $remaining  = max(0, $limit - $used);
            $status     = match (true) {
                $percentage >= 95 => 'critical',
                $percentage >= 80 => 'warning',
                default           => 'ok',
            };

            if ($status === 'critical' && $globalAlert === null) {
                $globalAlert = [
                    'type'    => 'warning',
                    'icon'    => '🚨',
                    'message' => "Quota critique : {$meta['label']} à {$percentage}% ({$remaining} restantes). Fallback automatique actif.",
                ];
            }

            $providers[$key] = [
                'label'      => $meta['label'],
                'icon'       => $meta['icon'],
                'used'       => $used,
                'limit'      => $limit,
                'remaining'  => $remaining,
                'percentage' => $percentage,
                'status'     => $status,
                'last_used'  => $usage?->last_request_at?->diffForHumans(),
                'unlimited'  => $limit === 0,
            ];
        }

        return [
            'providers'   => $providers,
            'globalAlert' => $globalAlert,
            'today'       => Carbon::today()->format('d/m/Y'),
        ];
    }
}
