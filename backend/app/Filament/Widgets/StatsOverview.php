<?php

namespace App\Filament\Widgets;

use App\Services\AdminStatsService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $data = app(AdminStatsService::class)->overview();

        $u = $data['users'];
        $p = $data['premium'];
        $pr = $data['predictions'];
        $sr = $data['success_rate'];

        $userTrend = $u['yesterday'] > 0
            ? round((($u['today'] - $u['yesterday']) / $u['yesterday']) * 100)
            : 0;

        return [
            Stat::make('Utilisateurs', number_format($u['total'], 0, ',', ' '))
                ->description($u['today'] . ' nouveaux aujourd\'hui')
                ->descriptionIcon($userTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($userTrend >= 0 ? 'success' : 'danger')
                ->chart([$u['yesterday'], $u['today']]),

            Stat::make('Abonnés Premium', number_format($p['active'], 0, ',', ' '))
                ->description(($p['trend'] >= 0 ? '+' : '') . $p['trend'] . '% vs mois dernier')
                ->descriptionIcon($p['trend'] >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($p['trend'] >= 0 ? 'warning' : 'danger'),

            Stat::make('Prédictions publiées', $pr['today'] . ' aujourd\'hui')
                ->description($pr['yesterday'] . ' hier')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('info'),

            Stat::make('Taux de réussite (30j)', $sr['rate'] . '%')
                ->description($sr['prev_rate'] > 0 ? "Mois précédent : {$sr['prev_rate']}%" : 'Pas encore de données')
                ->descriptionIcon($sr['rate'] >= $sr['prev_rate'] ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($sr['rate'] >= 70 ? 'success' : ($sr['rate'] >= 55 ? 'warning' : 'danger')),
        ];
    }
}
