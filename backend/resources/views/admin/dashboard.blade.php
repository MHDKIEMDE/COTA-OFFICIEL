@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-6">

    {{-- ── Stat Cards ──────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- Utilisateurs --}}
        <div class="card card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="tag-mono mb-1">Utilisateurs</p>
                    <p class="text-3xl font-bold" style="color:var(--ink);font-family:Archivo,sans-serif">{{ number_format($stats['total_users']) }}</p>
                    <p class="text-xs mt-2" style="color:var(--win)">
                        <i class="fa-solid fa-arrow-up mr-1"></i>+{{ $stats['new_users_today'] }} aujourd'hui
                    </p>
                </div>
                <div class="w-12 h-12 rounded-xl flex items-center justify-center"
                     style="background:rgba(61,220,145,.12);border:1px solid rgba(61,220,145,.2)">
                    <i class="fa-solid fa-users text-xl" style="color:var(--win)"></i>
                </div>
            </div>
        </div>

        {{-- Premium --}}
        <div class="card card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="tag-mono mb-1">Premium</p>
                    <p class="text-3xl font-bold" style="color:var(--ink);font-family:Archivo,sans-serif">{{ number_format($stats['premium_users']) }}</p>
                    <p class="text-xs mt-2" style="color:var(--dim)">
                        {{ $stats['total_users'] > 0 ? round(($stats['premium_users'] / $stats['total_users']) * 100, 1) : 0 }}% du total
                    </p>
                </div>
                <div class="w-12 h-12 rounded-xl flex items-center justify-center"
                     style="background:rgba(232,255,54,.10);border:1px solid rgba(232,255,54,.2)">
                    <i class="fa-solid fa-crown text-xl" style="color:var(--accent)"></i>
                </div>
            </div>
        </div>

        {{-- Win rate --}}
        <div class="card card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="tag-mono mb-1">Taux réussite</p>
                    <p class="text-3xl font-bold" style="color:var(--ink);font-family:Archivo,sans-serif">{{ $predictionStats['win_rate'] }}%</p>
                    <p class="text-xs mt-2" style="color:var(--dim)">
                        {{ $predictionStats['won'] }}/{{ $predictionStats['won'] + $predictionStats['lost'] }} gagnés
                    </p>
                </div>
                <div class="w-12 h-12 rounded-xl flex items-center justify-center"
                     style="background:rgba(232,255,54,.10);border:1px solid rgba(232,255,54,.2)">
                    <i class="fa-solid fa-chart-line text-xl" style="color:var(--accent)"></i>
                </div>
            </div>
        </div>

        {{-- Revenus --}}
        <div class="card card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="tag-mono mb-1">Revenus / mois</p>
                    <p class="text-2xl font-bold" style="color:var(--ink);font-family:Archivo,sans-serif">{{ number_format($revenueStats['monthly_revenue']) }}<span class="text-sm ml-1" style="color:var(--dim)">FCFA</span></p>
                    <p class="text-xs mt-2" style="color:var(--win)">
                        {{ $revenueStats['total_subscriptions'] }} abonnements
                    </p>
                </div>
                <div class="w-12 h-12 rounded-xl flex items-center justify-center"
                     style="background:rgba(61,220,145,.12);border:1px solid rgba(61,220,145,.2)">
                    <i class="fa-solid fa-wallet text-xl" style="color:var(--win)"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Graphiques ───────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        <div class="card overflow-hidden">
            <p class="tag-mono mb-4">Inscriptions — 7 jours</p>
            <div class="chart-container" style="height:200px">
                <canvas id="usersChart"></canvas>
            </div>
        </div>

        <div class="card overflow-hidden">
            <p class="tag-mono mb-4">Pronostics — 7 jours</p>
            <div class="chart-container" style="height:200px">
                <canvas id="predictionsChart"></canvas>
            </div>
        </div>
    </div>

    {{-- ── Performance revenus ─────────────────────────────────────────────── --}}
    <div class="card overflow-hidden">
        <div class="flex items-center justify-between mb-4">
            <p class="tag-mono">Performance financière — 30 jours</p>
            <div class="flex items-center gap-4 text-xs" style="color:var(--dim)">
                <span class="flex items-center gap-1.5">
                    <span style="display:inline-block;width:10px;height:10px;border-radius:2px;background:#3ddc91"></span>Revenus
                </span>
                <span class="flex items-center gap-1.5">
                    <span style="display:inline-block;width:10px;height:10px;border-radius:2px;background:#e8ff36"></span>Abonnements
                </span>
                <span class="flex items-center gap-1.5">
                    <span style="display:inline-block;width:10px;height:10px;border-radius:2px;background:#ff5b3a"></span>Remboursements
                </span>
            </div>
        </div>
        <div class="chart-container" style="height:180px">
            <canvas id="revenueChart"></canvas>
        </div>
        <div class="grid grid-cols-3 gap-4 mt-4 pt-4" style="border-top:1px solid var(--line)">
            <div class="text-center">
                <p style="font-family:Archivo,sans-serif;font-weight:900;font-size:20px;color:var(--win)">
                    {{ number_format($revenueStats['monthly_revenue']) }}
                </p>
                <p style="font-size:11px;color:var(--dim);margin-top:2px">Revenus ce mois (FCFA)</p>
            </div>
            <div class="text-center">
                <p style="font-family:Archivo,sans-serif;font-weight:900;font-size:20px;color:var(--accent)">
                    {{ $revenueStats['total_subscriptions'] }}
                </p>
                <p style="font-size:11px;color:var(--dim);margin-top:2px">Abonnements actifs</p>
            </div>
            <div class="text-center">
                <p style="font-family:Archivo,sans-serif;font-weight:900;font-size:20px;color:var(--ink)">
                    {{ number_format($revenueStats['weekly_revenue']) }}
                </p>
                <p style="font-size:11px;color:var(--dim);margin-top:2px">Revenus cette semaine (FCFA)</p>
            </div>
        </div>
    </div>

    {{-- ── Quick Stats ──────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        {{-- État pronostics --}}
        <div class="card">
            <p class="tag-mono mb-4">État des pronostics</p>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span style="color:var(--ink-2);font-size:13px">En attente</span>
                    <span class="badge-pending">{{ $predictionStats['pending'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span style="color:var(--ink-2);font-size:13px">Gagnés</span>
                    <span class="badge-win">{{ $predictionStats['won'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span style="color:var(--ink-2);font-size:13px">Perdus</span>
                    <span class="badge-loss">{{ $predictionStats['lost'] }}</span>
                </div>
                <div class="pt-3" style="border-top:1px solid var(--line)">
                    <div class="flex justify-between items-center">
                        <span style="color:var(--ink);font-size:13px;font-weight:600">Total</span>
                        <span style="color:var(--ink);font-weight:700;font-family:JetBrains Mono,monospace">{{ $predictionStats['total'] }}</span>
                    </div>
                </div>
            </div>
            <a href="{{ route('admin.predictions.index') }}"
               class="mt-4 block text-center text-xs font-semibold transition"
               style="color:var(--accent)">
                Voir tous les pronostics →
            </a>
        </div>

        {{-- Affiliations --}}
        <div class="card">
            <p class="tag-mono mb-4">Affiliations</p>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span style="color:var(--ink-2);font-size:13px">Total</span>
                    <span style="color:var(--ink);font-weight:600;font-family:JetBrains Mono,monospace">{{ $affiliationStats['total_bonuses'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span style="color:var(--ink-2);font-size:13px">Vérifiées</span>
                    <span class="badge-win">{{ $affiliationStats['verified_bonuses'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span style="color:var(--ink-2);font-size:13px">En attente</span>
                    <span class="badge-pending">{{ $affiliationStats['pending_bonuses'] }}</span>
                </div>
            </div>
            <a href="{{ route('admin.affiliates.index') }}"
               class="mt-4 block text-center text-xs font-semibold transition"
               style="color:var(--accent)">
                Gérer les affiliations →
            </a>
        </div>

        {{-- Parrainages --}}
        <div class="card">
            <p class="tag-mono mb-4">Parrainages</p>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span style="color:var(--ink-2);font-size:13px">Total</span>
                    <span style="color:var(--ink);font-weight:600;font-family:JetBrains Mono,monospace">{{ $referralStats['total'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span style="color:var(--ink-2);font-size:13px">Ce mois</span>
                    <span class="badge-win">{{ $referralStats['this_month'] }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Activité temps réel ─────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        {{-- Dernières prédictions --}}
        <div class="card" style="padding:0;overflow:hidden">
            <div class="flex items-center justify-between" style="padding:16px 20px;border-bottom:1px solid var(--line)">
                <p class="tag-mono">Dernières prédictions</p>
                <a href="{{ route('admin.predictions.index') }}" style="font-size:12px;color:var(--accent);font-weight:600;text-decoration:none">Voir tout →</a>
            </div>
            @forelse($latestPredictions as $pred)
            <div style="padding:12px 20px;border-bottom:1px solid var(--line);display:flex;align-items:center;gap:12px">
                <div style="flex:1;min-width:0">
                    <div style="font-size:13px;font-weight:700;color:var(--ink);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                        {{ $pred->home_team }} <span style="color:var(--dim)">vs</span> {{ $pred->away_team }}
                    </div>
                    <div style="font-size:11px;color:var(--dim);margin-top:2px;font-family:'JetBrains Mono',monospace">
                        {{ $pred->bet_type }} · {{ $pred->prediction }} · @php echo str_repeat('★', $pred->confidence_stars ?? 1) @endphp · cote {{ $pred->odds }}
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:8px;flex-shrink:0">
                    @if($pred->status === 'won')
                        <span class="badge-win">WON</span>
                    @elseif($pred->status === 'lost')
                        <span class="badge-loss">LOST</span>
                    @else
                        <span class="badge-pending">PENDING</span>
                    @endif
                    @if($pred->is_premium)
                        <span style="font-size:10px;padding:1px 6px;border-radius:20px;background:rgba(232,255,54,.12);color:var(--accent);font-weight:700">PRO</span>
                    @endif
                    <span style="font-size:11px;color:var(--dim-2);font-family:'JetBrains Mono',monospace">{{ $pred->total_score }}pts</span>
                </div>
            </div>
            @empty
            <div style="padding:40px;text-align:center;color:var(--dim);font-size:14px">Aucune prédiction publiée aujourd'hui</div>
            @endforelse
        </div>

        {{-- Derniers abonnements --}}
        <div class="card" style="padding:0;overflow:hidden">
            <div class="flex items-center justify-between" style="padding:16px 20px;border-bottom:1px solid var(--line)">
                <p class="tag-mono">Derniers abonnements</p>
                <a href="{{ route('admin.subscriptions.index') }}" style="font-size:12px;color:var(--accent);font-weight:600;text-decoration:none">Voir tout →</a>
            </div>
            @forelse($latestSubscriptions as $sub)
            <div style="padding:12px 20px;border-bottom:1px solid var(--line);display:flex;align-items:center;gap:12px">
                <div style="width:34px;height:34px;border-radius:8px;background:rgba(232,255,54,.08);border:1px solid rgba(232,255,54,.2);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <i class="fa-solid fa-crown" style="font-size:14px;color:var(--accent)"></i>
                </div>
                <div style="flex:1;min-width:0">
                    <div style="font-size:13px;font-weight:700;color:var(--ink)">{{ $sub->user->name ?? 'Utilisateur #'.$sub->user_id }}</div>
                    <div style="font-size:11px;color:var(--dim);margin-top:2px;font-family:'JetBrains Mono',monospace">
                        {{ ucfirst($sub->plan) }} · {{ number_format($sub->amount) }} FCFA
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:8px;flex-shrink:0">
                    @if($sub->status === 'completed')
                        <span class="badge-win">PAYÉ</span>
                    @elseif($sub->status === 'pending')
                        <span class="badge-pending">EN COURS</span>
                    @else
                        <span class="badge-loss">{{ strtoupper($sub->status) }}</span>
                    @endif
                    <span style="font-size:11px;color:var(--dim-2);font-family:'JetBrains Mono',monospace">{{ $sub->created_at->format('d/m H:i') }}</span>
                </div>
            </div>
            @empty
            <div style="padding:40px;text-align:center;color:var(--dim);font-size:14px">Aucun abonnement</div>
            @endforelse
        </div>
    </div>

    {{-- ── Activité & Feedbacks ─────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        {{-- Activité récente --}}
        <div class="card">
            <p class="tag-mono mb-4">Activité récente</p>
            <div class="space-y-4 max-h-72 overflow-y-auto pr-1">
                @forelse($recentActivities as $activity)
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0"
                             style="background:rgba(232,255,54,.10);border:1px solid rgba(232,255,54,.2)">
                            <i class="fa-solid {{ $activity['icon'] }} text-sm" style="color:var(--accent)"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm" style="color:var(--ink-2)">{{ $activity['message'] }}</p>
                            <p class="text-xs mt-0.5" style="color:var(--dim)">{{ $activity['time']->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-center py-6 text-sm" style="color:var(--dim)">Aucune activité récente</p>
                @endforelse
            </div>
        </div>

        {{-- Feedbacks récents --}}
        <div class="card">
            <p class="tag-mono mb-4">Feedbacks récents</p>
            <div class="space-y-3 max-h-72 overflow-y-auto pr-1">
                @forelse($recentFeedbacks as $feedback)
                    <div class="p-3 rounded-lg" style="background:var(--bg-3);border:1px solid var(--line)">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-sm font-semibold" style="color:var(--ink)">{{ $feedback->user->name ?? 'Anonyme' }}</span>
                            <span style="color:var(--dim-2)">·</span>
                            <span class="text-xs" style="color:var(--dim)">{{ $feedback->created_at->diffForHumans() }}</span>
                            @if($feedback->type)
                                <span class="{{ $feedback->type === 'bug' ? 'badge-loss' : 'badge-pending' }} ml-auto">{{ $feedback->type }}</span>
                            @endif
                        </div>
                        <p class="text-sm line-clamp-2" style="color:var(--ink-2)">{{ $feedback->message }}</p>
                    </div>
                @empty
                    <p class="text-center py-6 text-sm" style="color:var(--dim)">Aucun feedback récent</p>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
const chartDefaults = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false }, tooltip: { enabled: true } },
    scales: {
        x: {
            grid: { color: 'rgba(255,255,255,0.05)', drawBorder: false },
            ticks: { color: '#8b8a85', maxRotation: 0, autoSkip: true, maxTicksLimit: 7 }
        },
        y: {
            beginAtZero: true,
            grid: { color: 'rgba(255,255,255,0.05)', drawBorder: false },
            ticks: { color: '#8b8a85', maxTicksLimit: 5 }
        }
    }
};

// Inscriptions
const usersChart = new Chart(document.getElementById('usersChart').getContext('2d'), {
    type: 'line',
    data: {
        labels: {!! json_encode(array_column($userChartData, 'date')) !!},
        datasets: [{
            label: 'Inscriptions',
            data: {!! json_encode(array_column($userChartData, 'count')) !!},
            borderColor: '#e8ff36',
            backgroundColor: 'rgba(232,255,54,0.08)',
            tension: 0.4,
            fill: true,
            pointBackgroundColor: '#e8ff36',
            pointRadius: 4,
        }]
    },
    options: { ...chartDefaults }
});

// Pronostics
const predictionsChart = new Chart(document.getElementById('predictionsChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: {!! json_encode(array_column($predictionChartData, 'date')) !!},
        datasets: [
            { label: 'Gagnés',    data: {!! json_encode(array_column($predictionChartData, 'won')) !!},     backgroundColor: '#3ddc91', borderRadius: 4 },
            { label: 'Perdus',    data: {!! json_encode(array_column($predictionChartData, 'lost')) !!},    backgroundColor: '#ff5b3a', borderRadius: 4 },
            { label: 'En attente',data: {!! json_encode(array_column($predictionChartData, 'pending')) !!}, backgroundColor: '#2a2e36', borderRadius: 4 },
        ]
    },
    options: {
        ...chartDefaults,
        plugins: {
            legend: {
                display: true,
                position: 'bottom',
                labels: { color: '#8b8a85', padding: 12, boxWidth: 10, boxHeight: 10 }
            }
        },
        scales: {
            ...chartDefaults.scales,
            y: { ...chartDefaults.scales.y, stacked: true }
        }
    }
});

// Revenus 30 jours
const revenueChart = new Chart(document.getElementById('revenueChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: {!! json_encode(array_column($revenueChartData, 'date')) !!},
        datasets: [
            {
                label: 'Revenus (FCFA)',
                data: {!! json_encode(array_column($revenueChartData, 'revenue')) !!},
                backgroundColor: '#3ddc91',
                borderRadius: 3,
                order: 1,
            },
            {
                label: 'Abonnements',
                data: {!! json_encode(array_column($revenueChartData, 'count')) !!},
                type: 'line',
                borderColor: '#e8ff36',
                backgroundColor: 'rgba(232,255,54,0.06)',
                tension: 0.4,
                fill: true,
                pointRadius: 0,
                yAxisID: 'y2',
                order: 0,
            },
        ]
    },
    options: {
        ...chartDefaults,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: (ctx) => ctx.datasetIndex === 0
                        ? `${ctx.parsed.y.toLocaleString()} FCFA`
                        : `${ctx.parsed.y} abonnements`
                }
            }
        },
        scales: {
            x: { ...chartDefaults.scales.x, ticks: { ...chartDefaults.scales.x.ticks, maxTicksLimit: 10 } },
            y: { ...chartDefaults.scales.y, position: 'left' },
            y2: { display: false, beginAtZero: true },
        }
    }
});

window.addEventListener('resize', () => { usersChart.resize(); predictionsChart.resize(); revenueChart.resize(); });
window.addEventListener('load', () => setTimeout(() => { usersChart.resize(); predictionsChart.resize(); revenueChart.resize(); }, 100));
</script>
@endpush
