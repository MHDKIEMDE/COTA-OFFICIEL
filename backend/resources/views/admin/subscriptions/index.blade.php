@extends('admin.layouts.app')

@section('title', 'Abonnements')
@section('page-title', 'Abonnements & Paiements')

@section('content')
<div class="space-y-6">

    {{-- ── Stats ───────────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
        @foreach([
            ['Total',         number_format($stats['total']),            'var(--ink)'],
            ['Actifs',        number_format($stats['active']),           'var(--win)'],
            ['En attente',    number_format($stats['pending']),          '#f5a623'],
            ['Ce mois',       number_format($stats['revenue_month']) . ' FCFA', 'var(--accent)'],
            ['Total revenus', number_format($stats['revenue_total']) . ' FCFA',  'var(--ink-2)'],
        ] as [$label, $val, $color])
            <div class="card text-center">
                <p class="text-xl font-bold" style="color:{{ $color }};font-family:Archivo,sans-serif">{{ $val }}</p>
                <p class="text-sm mt-1" style="color:var(--dim)">{{ $label }}</p>
            </div>
        @endforeach
    </div>

    {{-- ── Répartition + Graphique ─────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="card">
            <p class="tag-mono mb-4"><i class="fa-solid fa-chart-pie mr-2" style="color:var(--accent)"></i>Répartition plans</p>
            <div class="space-y-3">
                @foreach(['weekly' => ['Hebdomadaire', $stats['weekly_count']], 'monthly' => ['Mensuel', $stats['monthly_count']], 'quarterly' => ['Trimestriel', $stats['quarterly_count']]] as $plan => [$label, $count])
                    <div class="flex items-center justify-between">
                        <span style="color:var(--ink-2);font-size:13px">{{ $label }}</span>
                        <div class="flex items-center gap-2">
                            <span style="font-size:11px;color:var(--dim)">{{ $stats['total'] > 0 ? round(($count / $stats['total']) * 100) : 0 }}%</span>
                            <span class="badge-accent">{{ $count }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card lg:col-span-2 overflow-hidden">
            <p class="tag-mono mb-4"><i class="fa-solid fa-chart-bar mr-2" style="color:var(--win)"></i>Revenus (6 derniers mois)</p>
            <div style="height:180px">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>

    {{-- ── Accord abonnement manuel ─────────────────────────────────────────── --}}
    <div class="card" style="border-color:rgba(232,255,54,.25)">
        <p class="tag-mono mb-4"><i class="fa-solid fa-plus-circle mr-2" style="color:var(--accent)"></i>Accorder un abonnement manuel</p>
        <form method="POST" action="{{ route('admin.subscriptions.grant') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            @csrf
            <select name="user_id" required class="input-brand" style="height:40px;padding:0 12px">
                <option value="">Sélectionner un utilisateur</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email ?? $u->phone }})</option>
                @endforeach
            </select>
            <select name="plan" required class="input-brand" style="height:40px;padding:0 12px">
                <option value="weekly">Hebdomadaire (7j)</option>
                <option value="monthly" selected>Mensuel (30j)</option>
                <option value="quarterly">Trimestriel (90j)</option>
            </select>
            <input type="text" name="reason" required placeholder="Raison (ex: compensation bug)"
                   class="input-brand" style="height:40px">
            <button type="submit" class="btn-primary">
                <i class="fa-solid fa-gift mr-1"></i> Accorder
            </button>
        </form>
    </div>

    {{-- ── Flash ────────────────────────────────────────────────────────────── --}}
    @if(session('success'))
        <div class="alert-brand alert-success">{{ session('success') }}</div>
    @endif

    {{-- ── Filtres ──────────────────────────────────────────────────────────── --}}
    <div class="card">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
            <select name="status" class="input-brand" style="height:40px;padding:0 12px">
                <option value="">Tous statuts</option>
                <option value="active"    {{ request('status') === 'active'    ? 'selected' : '' }}>Actif</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Annulé</option>
                <option value="expired"   {{ request('status') === 'expired'   ? 'selected' : '' }}>Expiré</option>
            </select>
            <select name="plan" class="input-brand" style="height:40px;padding:0 12px">
                <option value="">Tous les plans</option>
                <option value="weekly"    {{ request('plan') === 'weekly'    ? 'selected' : '' }}>Hebdomadaire</option>
                <option value="monthly"   {{ request('plan') === 'monthly'   ? 'selected' : '' }}>Mensuel</option>
                <option value="quarterly" {{ request('plan') === 'quarterly' ? 'selected' : '' }}>Trimestriel</option>
            </select>
            <select name="payment_method" class="input-brand" style="height:40px;padding:0 12px">
                <option value="">Tous moyens</option>
                <option value="paydunya"    {{ request('payment_method') === 'paydunya'    ? 'selected' : '' }}>Paydunya</option>
                <option value="admin"       {{ request('payment_method') === 'admin'       ? 'selected' : '' }}>Admin</option>
                <option value="referral"    {{ request('payment_method') === 'referral'    ? 'selected' : '' }}>Parrainage</option>
                <option value="affiliation" {{ request('payment_method') === 'affiliation' ? 'selected' : '' }}>Affiliation</option>
            </select>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher utilisateur…"
                   class="input-brand" style="height:40px">
            <div class="flex gap-2">
                <button type="submit" class="btn-primary btn-sm flex-1">
                    <i class="fa-solid fa-search mr-1"></i> Filtrer
                </button>
                <a href="{{ route('admin.subscriptions.index') }}" class="btn-secondary btn-sm px-3">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            </div>
        </form>
    </div>

    {{-- ── Table ────────────────────────────────────────────────────────────── --}}
    <div class="card" style="padding:0;overflow:hidden">
        <div class="overflow-x-auto">
            <table class="table-brand w-full">
                <thead>
                    <tr>
                        <th class="text-left">#</th>
                        <th class="text-left">Utilisateur</th>
                        <th class="text-left">Plan</th>
                        <th class="text-left">Montant</th>
                        <th class="text-left">Moyen</th>
                        <th class="text-left">Statut</th>
                        <th class="text-left">Expiration</th>
                        <th class="text-left">Date</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subscriptions as $sub)
                    @php
                        $isActive = $sub->status === 'active' && $sub->expires_at && $sub->expires_at->isFuture();
                    @endphp
                    <tr>
                        <td style="color:var(--dim-2);font-size:12px;font-family:JetBrains Mono,monospace">{{ $sub->id }}</td>
                        <td>
                            <p style="color:var(--ink);font-weight:600;font-size:14px">{{ $sub->user->name ?? '—' }}</p>
                            <p style="color:var(--dim);font-size:12px">{{ $sub->user->email ?? $sub->user->phone ?? '' }}</p>
                        </td>
                        <td>
                            <span class="badge-accent">{{ ['weekly' => 'Hebdo', 'monthly' => 'Mensuel', 'quarterly' => 'Trimestriel'][$sub->plan] ?? $sub->plan }}</span>
                        </td>
                        <td style="color:var(--ink);font-weight:600;font-family:JetBrains Mono,monospace;font-size:13px">
                            {{ $sub->amount > 0 ? number_format($sub->amount) . ' FCFA' : 'Gratuit' }}
                        </td>
                        <td style="color:var(--ink-2);font-size:13px;text-transform:capitalize">{{ $sub->payment_method }}</td>
                        <td>
                            @if($isActive)
                                <span class="badge-win">Actif</span>
                            @elseif($sub->status === 'cancelled')
                                <span class="badge-loss">Annulé</span>
                            @else
                                <span style="font-size:11px;color:var(--dim)">{{ ucfirst($sub->status) }}</span>
                            @endif
                        </td>
                        <td style="font-size:12px;color:var(--dim)">
                            {{ $sub->expires_at ? $sub->expires_at->format('d/m/Y') : '—' }}
                            @if($isActive)
                                <span style="display:block;color:var(--win)">{{ $sub->expires_at->diffForHumans() }}</span>
                            @endif
                        </td>
                        <td style="font-size:12px;color:var(--dim)">{{ $sub->created_at->format('d/m/Y') }}</td>
                        <td class="text-right">
                            @if($sub->status === 'active' && $isActive)
                                <form method="POST" action="{{ route('admin.subscriptions.cancel', $sub) }}"
                                      onsubmit="return confirm('Annuler cet abonnement ?')">
                                    @csrf @method('PATCH')
                                    <button class="btn-danger btn-sm">Annuler</button>
                                </form>
                            @else
                                <span style="color:var(--dim-2);font-size:12px">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" style="padding:48px;text-align:center;color:var(--dim)">
                            <i class="fa-solid fa-credit-card" style="font-size:28px;display:block;margin-bottom:12px;color:var(--dim-2)"></i>
                            Aucun abonnement trouvé
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($subscriptions->hasPages())
        <div style="padding:16px 24px;border-top:1px solid var(--line)">
            {{ $subscriptions->links('vendor.pagination.tailwind') }}
        </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
new Chart(revenueCtx, {
    type: 'bar',
    data: {
        labels: {!! json_encode(array_column($revenueChart, 'month')) !!},
        datasets: [{
            label: 'Revenus (FCFA)',
            data: {!! json_encode(array_column($revenueChart, 'amount')) !!},
            backgroundColor: 'rgba(61,220,145,0.5)',
            borderColor: '#3ddc91',
            borderWidth: 1,
            borderRadius: 4,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: '#8b8a85' } },
            y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: '#8b8a85' } }
        }
    }
});
</script>
@endpush
