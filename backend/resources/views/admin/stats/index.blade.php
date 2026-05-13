@extends('admin.layouts.app')

@section('title', 'Statistiques avancées')
@section('page-title', 'Statistiques avancées')

@section('content')
<div class="space-y-6">

    {{-- KPIs globaux --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="bg-dark-100 rounded-lg border border-gray-700/50 p-4 text-center">
            <p class="text-3xl font-bold text-white">{{ $winRate }}%</p>
            <p class="text-sm text-gray-400">Taux de réussite</p>
        </div>
        <div class="bg-dark-100 rounded-lg border border-gray-700/50 p-4 text-center">
            <p class="text-3xl font-bold text-success">{{ number_format($won) }}</p>
            <p class="text-sm text-gray-400">Gagnés</p>
        </div>
        <div class="bg-dark-100 rounded-lg border border-gray-700/50 p-4 text-center">
            <p class="text-3xl font-bold text-danger">{{ number_format($lost) }}</p>
            <p class="text-sm text-gray-400">Perdus</p>
        </div>
        <div class="bg-dark-100 rounded-lg border border-gray-700/50 p-4 text-center">
            <p class="text-3xl font-bold text-warning">{{ number_format($pending) }}</p>
            <p class="text-sm text-gray-400">En attente</p>
        </div>
        <div class="bg-dark-100 rounded-lg border border-gray-700/50 p-4 text-center">
            <p class="text-3xl font-bold text-primary">{{ number_format(round($avgOdds, 2)) }}</p>
            <p class="text-sm text-gray-400">Cote moy. gagnée</p>
        </div>
        <div class="bg-dark-100 rounded-lg border border-gray-700/50 p-4 text-center">
            <p class="text-3xl font-bold {{ $roi >= 0 ? 'text-success' : 'text-danger' }}">{{ $roi > 0 ? '+' : '' }}{{ $roi }}%</p>
            <p class="text-sm text-gray-400">ROI estimé</p>
        </div>
    </div>

    {{-- Graphiques ligne 1 : Taux réussite 30j + Croissance users --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-dark-100 rounded-xl border border-gray-700/50 p-6 overflow-hidden">
            <h3 class="text-white font-semibold mb-4">
                <i class="fa-solid fa-chart-line mr-2 text-success"></i>Taux de réussite — 30 derniers jours
            </h3>
            <div class="chart-container">
                <canvas id="winRateChart"></canvas>
            </div>
        </div>
        <div class="bg-dark-100 rounded-xl border border-gray-700/50 p-6 overflow-hidden">
            <h3 class="text-white font-semibold mb-4">
                <i class="fa-solid fa-user-plus mr-2 text-primary"></i>Nouveaux utilisateurs — 30 jours
            </h3>
            <div class="chart-container">
                <canvas id="usersChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Graphique revenus 12 mois --}}
    <div class="bg-dark-100 rounded-xl border border-gray-700/50 p-6 overflow-hidden">
        <h3 class="text-white font-semibold mb-4">
            <i class="fa-solid fa-wallet mr-2 text-secondary"></i>Revenus & abonnements — 12 derniers mois
        </h3>
        <div style="height: 250px; position: relative;">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    {{-- Taux par étoiles + Taux par type de pari --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Par étoiles --}}
        <div class="bg-dark-100 rounded-xl border border-gray-700/50 p-6">
            <h3 class="text-white font-semibold mb-4">
                <i class="fa-solid fa-star mr-2 text-warning"></i>Réussite par niveau de confiance
            </h3>
            <div class="space-y-4">
                @foreach($byStars as $row)
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <div class="flex items-center gap-1">
                            @for($s = 1; $s <= 4; $s++)
                                <i class="fa-solid fa-star text-xs {{ $s <= $row['stars'] ? 'text-warning' : 'text-gray-600' }}"></i>
                            @endfor
                            <span class="text-gray-400 text-sm ml-2">{{ $row['total'] }} pronostics</span>
                        </div>
                        <span class="font-bold {{ $row['win_rate'] >= 60 ? 'text-success' : ($row['win_rate'] >= 45 ? 'text-warning' : 'text-danger') }}">
                            {{ $row['win_rate'] }}%
                        </span>
                    </div>
                    <div class="w-full bg-gray-700 rounded-full h-2">
                        <div class="h-2 rounded-full {{ $row['win_rate'] >= 60 ? 'bg-success' : ($row['win_rate'] >= 45 ? 'bg-warning' : 'bg-danger') }}"
                             style="width: {{ $row['win_rate'] }}%"></div>
                    </div>
                    <div class="flex justify-between text-xs text-gray-500 mt-1">
                        <span>{{ $row['won'] }} gagnés</span>
                        <span>{{ $row['lost'] }} perdus</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Par type de pari --}}
        <div class="bg-dark-100 rounded-xl border border-gray-700/50 p-6">
            <h3 class="text-white font-semibold mb-4">
                <i class="fa-solid fa-futbol mr-2 text-primary"></i>Réussite par type de pari
            </h3>
            @if($byBetType->isEmpty())
                <p class="text-gray-500 text-center py-8">Pas encore de données</p>
            @else
            <div class="space-y-3">
                @foreach($byBetType as $row)
                <div class="flex items-center gap-3">
                    <span class="text-gray-300 text-sm w-28 flex-shrink-0">{{ strtoupper($row['bet_type'] ?? '?') }}</span>
                    <div class="flex-1 bg-gray-700 rounded-full h-2">
                        <div class="h-2 rounded-full {{ $row['win_rate'] >= 60 ? 'bg-success' : ($row['win_rate'] >= 45 ? 'bg-warning' : 'bg-danger') }}"
                             style="width: {{ $row['win_rate'] }}%"></div>
                    </div>
                    <span class="text-sm font-bold w-12 text-right {{ $row['win_rate'] >= 60 ? 'text-success' : ($row['win_rate'] >= 45 ? 'text-warning' : 'text-danger') }}">
                        {{ $row['win_rate'] }}%
                    </span>
                    <span class="text-gray-500 text-xs w-16 text-right">{{ $row['total'] }} paris</span>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- Top 10 compétitions --}}
    <div class="bg-dark-100 rounded-xl border border-gray-700/50 p-6">
        <h3 class="text-white font-semibold mb-4">
            <i class="fa-solid fa-trophy mr-2 text-warning"></i>Top 10 compétitions — taux de réussite
        </h3>
        @if($byCompetition->isEmpty())
            <p class="text-gray-500 text-center py-8">Pas encore de données</p>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-gray-400 text-xs uppercase tracking-wider border-b border-gray-700/50">
                        <th class="pb-3 text-left">Compétition</th>
                        <th class="pb-3 text-center">Gagnés</th>
                        <th class="pb-3 text-center">Perdus</th>
                        <th class="pb-3 text-center">Total</th>
                        <th class="pb-3 text-right">Taux</th>
                        <th class="pb-3 text-right w-40">Barre</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700/30">
                    @foreach($byCompetition as $row)
                    <tr class="hover:bg-gray-800/30">
                        <td class="py-3 text-gray-200 font-medium">{{ $row['competition'] ?? 'Inconnue' }}</td>
                        <td class="py-3 text-center text-success">{{ $row['won'] }}</td>
                        <td class="py-3 text-center text-danger">{{ $row['lost'] }}</td>
                        <td class="py-3 text-center text-gray-400">{{ $row['total'] }}</td>
                        <td class="py-3 text-right font-bold {{ $row['win_rate'] >= 60 ? 'text-success' : ($row['win_rate'] >= 45 ? 'text-warning' : 'text-danger') }}">
                            {{ $row['win_rate'] }}%
                        </td>
                        <td class="py-3 text-right">
                            <div class="w-32 ml-auto bg-gray-700 rounded-full h-1.5">
                                <div class="h-1.5 rounded-full {{ $row['win_rate'] >= 60 ? 'bg-success' : ($row['win_rate'] >= 45 ? 'bg-warning' : 'bg-danger') }}"
                                     style="width: {{ $row['win_rate'] }}%"></div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
// Taux de réussite 30j
const winRateCtx = document.getElementById('winRateChart').getContext('2d');
const winRateData = {!! json_encode($last30Days) !!};
new Chart(winRateCtx, {
    type: 'line',
    data: {
        labels: winRateData.map(d => d.date),
        datasets: [{
            label: 'Taux de réussite (%)',
            data: winRateData.map(d => d.win_rate),
            borderColor: '#10B981',
            backgroundColor: 'rgba(16,185,129,0.1)',
            tension: 0.4,
            fill: true,
            pointRadius: 2,
            spanGaps: true,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#9CA3AF', maxTicksLimit: 7 } },
            y: { min: 0, max: 100, grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#9CA3AF', callback: v => v + '%' } }
        }
    }
});

// Croissance users
const usersCtx = document.getElementById('usersChart').getContext('2d');
const usersData = {!! json_encode($userGrowth) !!};
new Chart(usersCtx, {
    type: 'bar',
    data: {
        labels: usersData.map(d => d.date),
        datasets: [{
            label: 'Nouveaux utilisateurs',
            data: usersData.map(d => d.count),
            backgroundColor: 'rgba(99,102,241,0.7)',
            borderRadius: 3,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false }, ticks: { color: '#9CA3AF', maxTicksLimit: 7 } },
            y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#9CA3AF' } }
        }
    }
});

// Revenus 12 mois
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
const revenueData = {!! json_encode($revenueByMonth) !!};
new Chart(revenueCtx, {
    type: 'bar',
    data: {
        labels: revenueData.map(d => d.month),
        datasets: [
            {
                label: 'Revenus (FCFA)',
                data: revenueData.map(d => d.amount),
                backgroundColor: 'rgba(139,92,246,0.7)',
                borderRadius: 4,
                yAxisID: 'y',
            },
            {
                label: 'Nouveaux abonnés',
                data: revenueData.map(d => d.new_premium),
                type: 'line',
                borderColor: '#F59E0B',
                backgroundColor: 'rgba(245,158,11,0.1)',
                tension: 0.4,
                fill: false,
                pointRadius: 3,
                yAxisID: 'y1',
            }
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom', labels: { color: '#9CA3AF', boxWidth: 12 } } },
        scales: {
            x: { grid: { display: false }, ticks: { color: '#9CA3AF' } },
            y:  { beginAtZero: true, position: 'left',  grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#9CA3AF' } },
            y1: { beginAtZero: true, position: 'right', grid: { display: false }, ticks: { color: '#F59E0B' } }
        }
    }
});
</script>
@endpush
