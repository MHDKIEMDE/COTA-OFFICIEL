@extends('admin.layouts.app')

@section('title', 'Statistiques avancées')
@section('page-title', 'Statistiques avancées')

@section('content')
<div class="space-y-6">

    {{-- ── KPIs ────────────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        @foreach([
            ['Taux réussite', $winRate . '%',                           'var(--accent)'],
            ['Gagnés',        number_format($won),                      'var(--win)'],
            ['Perdus',        number_format($lost),                     'var(--loss)'],
            ['En attente',    number_format($pending),                  '#f5a623'],
            ['Cote moy.',     number_format(round($avgOdds, 2)),        'var(--ink)'],
            ['ROI estimé',    ($roi > 0 ? '+' : '') . $roi . '%',       $roi >= 0 ? 'var(--win)' : 'var(--loss)'],
        ] as [$label, $val, $color])
            <div class="card text-center">
                <p class="text-2xl font-bold" style="color:{{ $color }};font-family:Archivo,sans-serif">{{ $val }}</p>
                <p class="text-sm mt-1" style="color:var(--dim)">{{ $label }}</p>
            </div>
        @endforeach
    </div>

    {{-- ── Graphiques ligne 1 ───────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="card overflow-hidden">
            <p class="tag-mono mb-4"><i class="fa-solid fa-chart-line mr-2" style="color:var(--win)"></i>Taux de réussite — 30 jours</p>
            <div style="height:180px"><canvas id="winRateChart"></canvas></div>
        </div>
        <div class="card overflow-hidden">
            <p class="tag-mono mb-4"><i class="fa-solid fa-user-plus mr-2" style="color:var(--accent)"></i>Nouveaux utilisateurs — 30 jours</p>
            <div style="height:180px"><canvas id="usersChart"></canvas></div>
        </div>
    </div>

    {{-- ── Revenus 12 mois ─────────────────────────────────────────────────── --}}
    <div class="card overflow-hidden">
        <p class="tag-mono mb-4"><i class="fa-solid fa-wallet mr-2" style="color:var(--accent)"></i>Revenus & abonnements — 12 derniers mois</p>
        <div style="height:250px"><canvas id="revenueChart"></canvas></div>
    </div>

    {{-- ── Par étoiles + Par type ───────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <div class="card">
            <p class="tag-mono mb-4"><i class="fa-solid fa-star mr-2" style="color:#f5a623"></i>Réussite par niveau de confiance</p>
            <div class="space-y-4">
                @foreach($byStars as $row)
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <div class="flex items-center gap-1">
                            @for($s = 1; $s <= 4; $s++)
                                <i class="fa-solid fa-star" style="font-size:11px;color:{{ $s <= $row['stars'] ? '#f5a623' : 'var(--dim-2)' }}"></i>
                            @endfor
                            <span style="color:var(--dim);font-size:13px;margin-left:8px">{{ $row['total'] }} pronostics</span>
                        </div>
                        <span style="font-weight:700;color:{{ $row['win_rate'] >= 60 ? 'var(--win)' : ($row['win_rate'] >= 45 ? '#f5a623' : 'var(--loss)') }}">{{ $row['win_rate'] }}%</span>
                    </div>
                    <div style="height:6px;border-radius:3px;background:var(--bg-3);overflow:hidden">
                        <div style="height:100%;border-radius:3px;width:{{ $row['win_rate'] }}%;background:{{ $row['win_rate'] >= 60 ? 'var(--win)' : ($row['win_rate'] >= 45 ? '#f5a623' : 'var(--loss)') }}"></div>
                    </div>
                    <div class="flex justify-between mt-1" style="font-size:11px;color:var(--dim-2)">
                        <span>{{ $row['won'] }} gagnés</span>
                        <span>{{ $row['lost'] }} perdus</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="card">
            <p class="tag-mono mb-4"><i class="fa-solid fa-futbol mr-2" style="color:var(--accent)"></i>Réussite par type de pari</p>
            @if($byBetType->isEmpty())
                <p style="color:var(--dim);text-align:center;padding:32px 0">Pas encore de données</p>
            @else
                <div class="space-y-3">
                    @foreach($byBetType as $row)
                        <div class="flex items-center gap-3">
                            <span style="color:var(--ink-2);font-size:13px;width:100px;flex-shrink:0">{{ strtoupper($row['bet_type'] ?? '?') }}</span>
                            <div style="flex:1;height:6px;border-radius:3px;background:var(--bg-3);overflow:hidden">
                                <div style="height:100%;border-radius:3px;width:{{ $row['win_rate'] }}%;background:{{ $row['win_rate'] >= 60 ? 'var(--win)' : ($row['win_rate'] >= 45 ? '#f5a623' : 'var(--loss)') }}"></div>
                            </div>
                            <span style="font-weight:700;font-size:13px;width:44px;text-align:right;color:{{ $row['win_rate'] >= 60 ? 'var(--win)' : ($row['win_rate'] >= 45 ? '#f5a623' : 'var(--loss)') }}">{{ $row['win_rate'] }}%</span>
                            <span style="color:var(--dim);font-size:12px;width:64px;text-align:right">{{ $row['total'] }} paris</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- ── Top compétitions ────────────────────────────────────────────────── --}}
    <div class="card">
        <p class="tag-mono mb-4"><i class="fa-solid fa-trophy mr-2" style="color:#f5a623"></i>Top 10 compétitions — taux de réussite</p>
        @if($byCompetition->isEmpty())
            <p style="color:var(--dim);text-align:center;padding:32px 0">Pas encore de données</p>
        @else
            <div class="overflow-x-auto">
                <table class="table-brand w-full">
                    <thead>
                        <tr>
                            <th class="text-left">Compétition</th>
                            <th class="text-center">Gagnés</th>
                            <th class="text-center">Perdus</th>
                            <th class="text-center">Total</th>
                            <th class="text-right">Taux</th>
                            <th class="text-right" style="width:160px">Barre</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($byCompetition as $row)
                        <tr>
                            <td style="color:var(--ink-2);font-weight:500">{{ $row['competition'] ?? 'Inconnue' }}</td>
                            <td class="text-center" style="color:var(--win)">{{ $row['won'] }}</td>
                            <td class="text-center" style="color:var(--loss)">{{ $row['lost'] }}</td>
                            <td class="text-center" style="color:var(--dim)">{{ $row['total'] }}</td>
                            <td class="text-right" style="font-weight:700;color:{{ $row['win_rate'] >= 60 ? 'var(--win)' : ($row['win_rate'] >= 45 ? '#f5a623' : 'var(--loss)') }}">{{ $row['win_rate'] }}%</td>
                            <td class="text-right">
                                <div style="width:128px;height:5px;border-radius:3px;background:var(--bg-3);overflow:hidden;margin-left:auto">
                                    <div style="height:100%;border-radius:3px;width:{{ $row['win_rate'] }}%;background:{{ $row['win_rate'] >= 60 ? 'var(--win)' : ($row['win_rate'] >= 45 ? '#f5a623' : 'var(--loss)') }}"></div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- ── UTILISATEURS ACTIFS (5 dernières minutes, polling 30 s) ──────────── --}}
    <div class="card" id="activeUsersCard">
        <div class="flex items-center justify-between mb-4">
            <p class="tag-mono"><i class="fa-solid fa-circle fa-beat-fade mr-2" style="color:#22c55e;font-size:9px"></i>Utilisateurs actifs <span style="color:var(--dim);font-size:11px">(5 min)</span></p>
            <div class="flex items-center gap-3">
                <span id="activeCount" style="font-size:28px;font-weight:900;color:var(--ink);font-family:'Archivo',sans-serif">–</span>
                <span id="premiumBadge" class="hidden px-2 py-1 rounded text-xs font-semibold" style="background:rgba(245,166,35,.12);color:#f5a623;border:1px solid rgba(245,166,35,.25)"></span>
            </div>
        </div>
        <div id="activeUsersList" class="space-y-2 max-h-64 overflow-y-auto pr-1"></div>
        <p id="activeUpdatedAt" class="text-right mt-3" style="color:var(--dim);font-size:11px"></p>
    </div>

</div>
@endsection

@push('scripts')
<script>
const chartBase = {
    responsive: true, maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
        x: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: '#8b8a85', maxTicksLimit: 7 } },
        y: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: '#8b8a85' } }
    }
};

const winRateData = {!! json_encode($last30Days) !!};
new Chart(document.getElementById('winRateChart').getContext('2d'), {
    type: 'line',
    data: {
        labels: winRateData.map(d => d.date),
        datasets: [{ data: winRateData.map(d => d.win_rate), borderColor: '#3ddc91', backgroundColor: 'rgba(61,220,145,0.08)', tension: 0.4, fill: true, pointRadius: 2, spanGaps: true }]
    },
    options: { ...chartBase, scales: { ...chartBase.scales, y: { ...chartBase.scales.y, min: 0, max: 100, ticks: { color: '#8b8a85', callback: v => v + '%' } } } }
});

const usersData = {!! json_encode($userGrowth) !!};
new Chart(document.getElementById('usersChart').getContext('2d'), {
    type: 'bar',
    data: { labels: usersData.map(d => d.date), datasets: [{ data: usersData.map(d => d.count), backgroundColor: 'rgba(232,255,54,0.5)', borderRadius: 3 }] },
    options: { ...chartBase }
});

const revenueData = {!! json_encode($revenueByMonth) !!};
new Chart(document.getElementById('revenueChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: revenueData.map(d => d.month),
        datasets: [
            { label: 'Revenus (FCFA)', data: revenueData.map(d => d.amount), backgroundColor: 'rgba(61,220,145,0.5)', borderRadius: 4, yAxisID: 'y' },
            { label: 'Nouveaux abonnés', data: revenueData.map(d => d.new_premium), type: 'line', borderColor: '#f5a623', backgroundColor: 'rgba(245,166,35,0.1)', tension: 0.4, fill: false, pointRadius: 3, yAxisID: 'y1' }
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: true, position: 'bottom', labels: { color: '#8b8a85', boxWidth: 12 } } },
        scales: {
            x: { grid: { display: false }, ticks: { color: '#8b8a85' } },
            y:  { beginAtZero: true, position: 'left',  grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: '#8b8a85' } },
            y1: { beginAtZero: true, position: 'right', grid: { display: false }, ticks: { color: '#f5a623' } }
        }
    }
});

// ── Utilisateurs actifs — polling toutes les 30 s ──────────────────────────
function fetchActiveUsers() {
    fetch('{{ route("admin.stats.active-users") }}')
        .then(r => r.json())
        .then(res => {
            if (!res.success) return;
            const d = res.data;
            document.getElementById('activeCount').textContent = d.count;
            const badge = document.getElementById('premiumBadge');
            if (d.premium > 0) {
                badge.textContent = d.premium + ' premium';
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
            const list = document.getElementById('activeUsersList');
            if (d.users.length === 0) {
                list.innerHTML = '<p style="color:var(--dim);font-size:13px;text-align:center;padding:12px 0">Aucun utilisateur actif ces 5 dernières minutes</p>';
            } else {
                list.innerHTML = d.users.map(u => `
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 10px;border-radius:8px;background:var(--bg-3);border:1px solid var(--line)">
                        <div style="display:flex;align-items:center;gap:10px">
                            <div style="width:8px;height:8px;border-radius:50%;background:#22c55e;flex-shrink:0"></div>
                            <div>
                                <p style="color:var(--ink);font-size:13px;font-weight:600">${u.name || u.email}</p>
                                ${u.name ? `<p style="color:var(--dim);font-size:11px">${u.email}</p>` : ''}
                            </div>
                        </div>
                        <div style="display:flex;align-items:center;gap:8px">
                            ${u.is_premium ? '<span style="padding:2px 7px;border-radius:4px;font-size:10px;font-weight:700;background:rgba(245,166,35,.12);color:#f5a623;border:1px solid rgba(245,166,35,.25)">PREMIUM</span>' : ''}
                            <span style="color:var(--dim);font-size:11px;font-family:\'Space Mono\',monospace">${new Date(u.last_seen).toLocaleTimeString('fr-FR', {hour:'2-digit',minute:'2-digit'})}</span>
                        </div>
                    </div>
                `).join('');
            }
            document.getElementById('activeUpdatedAt').textContent = 'Actualisé à ' + new Date().toLocaleTimeString('fr-FR');
        })
        .catch(() => {});
}
fetchActiveUsers();
setInterval(fetchActiveUsers, 30000);
</script>
@endpush
