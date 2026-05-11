<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class RevenueChartWidget extends ChartWidget
{
    protected static ?int $sort = 3;
    protected static ?string $heading = 'Inscriptions & Abonnements (30 derniers jours)';
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $days   = collect(range(29, 0))->map(fn ($i) => Carbon::now()->subDays($i)->format('Y-m-d'));
        $labels = $days->map(fn ($d) => Carbon::parse($d)->format('d/m'))->values()->all();

        $users = DB::table('users')
            ->whereDate('created_at', '>=', Carbon::now()->subDays(29))
            ->selectRaw("DATE(created_at) as day, COUNT(*) as total")
            ->groupBy('day')
            ->pluck('total', 'day');

        $subs = DB::table('subscriptions')
            ->whereDate('created_at', '>=', Carbon::now()->subDays(29))
            ->where('status', 'active')
            ->selectRaw("DATE(created_at) as day, COUNT(*) as total")
            ->groupBy('day')
            ->pluck('total', 'day');

        return [
            'datasets' => [
                [
                    'label'           => 'Nouvelles inscriptions',
                    'data'            => $days->map(fn ($d) => $users[$d] ?? 0)->values()->all(),
                    'borderColor'     => '#00CED1',
                    'backgroundColor' => 'rgba(0,206,209,0.1)',
                    'tension'         => 0.4,
                    'fill'            => true,
                ],
                [
                    'label'           => 'Abonnements actifs',
                    'data'            => $days->map(fn ($d) => $subs[$d] ?? 0)->values()->all(),
                    'borderColor'     => '#f59e0b',
                    'backgroundColor' => 'rgba(245,158,11,0.1)',
                    'tension'         => 0.4,
                    'fill'            => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
