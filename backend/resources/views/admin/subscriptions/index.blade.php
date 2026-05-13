@extends('admin.layouts.app')

@section('title', 'Abonnements')
@section('page-title', 'Abonnements & Paiements')

@section('content')
<div class="space-y-6">

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
        <div class="bg-dark-100 rounded-lg border border-gray-700/50 p-4 text-center">
            <p class="text-2xl font-bold text-white">{{ number_format($stats['total']) }}</p>
            <p class="text-sm text-gray-400">Total</p>
        </div>
        <div class="bg-dark-100 rounded-lg border border-gray-700/50 p-4 text-center">
            <p class="text-2xl font-bold text-success">{{ number_format($stats['active']) }}</p>
            <p class="text-sm text-gray-400">Actifs</p>
        </div>
        <div class="bg-dark-100 rounded-lg border border-gray-700/50 p-4 text-center">
            <p class="text-2xl font-bold text-warning">{{ number_format($stats['pending']) }}</p>
            <p class="text-sm text-gray-400">En attente</p>
        </div>
        <div class="bg-dark-100 rounded-lg border border-gray-700/50 p-4 text-center">
            <p class="text-2xl font-bold text-primary">{{ number_format($stats['revenue_month']) }} <span class="text-sm">FCFA</span></p>
            <p class="text-sm text-gray-400">Ce mois</p>
        </div>
        <div class="bg-dark-100 rounded-lg border border-gray-700/50 p-4 text-center">
            <p class="text-2xl font-bold text-secondary">{{ number_format($stats['revenue_total']) }} <span class="text-sm">FCFA</span></p>
            <p class="text-sm text-gray-400">Total revenus</p>
        </div>
    </div>

    {{-- Répartition plans + Graphique revenus --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Répartition --}}
        <div class="bg-dark-100 rounded-xl border border-gray-700/50 p-6">
            <h3 class="text-white font-semibold mb-4"><i class="fa-solid fa-chart-pie mr-2 text-primary"></i>Répartition plans</h3>
            <div class="space-y-3">
                @foreach(['weekly' => ['Hebdomadaire', 'text-blue-400', $stats['weekly_count']], 'monthly' => ['Mensuel', 'text-success', $stats['monthly_count']], 'quarterly' => ['Trimestriel', 'text-secondary', $stats['quarterly_count']]] as $plan => [$label, $color, $count])
                <div class="flex items-center justify-between">
                    <span class="text-gray-400 text-sm">{{ $label }}</span>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-500">{{ $stats['total'] > 0 ? round(($count / $stats['total']) * 100) : 0 }}%</span>
                        <span class="px-3 py-1 rounded-full text-xs font-medium bg-gray-700 {{ $color }}">{{ $count }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Graphique revenus 6 mois --}}
        <div class="lg:col-span-2 bg-dark-100 rounded-xl border border-gray-700/50 p-6 overflow-hidden">
            <h3 class="text-white font-semibold mb-4"><i class="fa-solid fa-chart-bar mr-2 text-success"></i>Revenus (6 derniers mois)</h3>
            <div class="chart-container">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Accorder abonnement manuel --}}
    <div class="bg-dark-100 rounded-xl border border-primary/30 p-6">
        <h3 class="text-white font-semibold mb-4"><i class="fa-solid fa-plus-circle mr-2 text-primary"></i>Accorder un abonnement manuel</h3>
        <form method="POST" action="{{ route('admin.subscriptions.grant') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            @csrf
            <select name="user_id" required class="bg-gray-800 border border-gray-600 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-primary">
                <option value="">Sélectionner un utilisateur</option>
                @foreach(\App\Models\User::orderBy('name')->get() as $u)
                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email ?? $u->phone }})</option>
                @endforeach
            </select>
            <select name="plan" required class="bg-gray-800 border border-gray-600 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-primary">
                <option value="weekly">Hebdomadaire (7j)</option>
                <option value="monthly" selected>Mensuel (30j)</option>
                <option value="quarterly">Trimestriel (90j)</option>
            </select>
            <input type="text" name="reason" required placeholder="Raison (ex: compensation bug)"
                   class="bg-gray-800 border border-gray-600 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-primary">
            <button type="submit" class="bg-primary hover:bg-primary/80 text-white rounded-lg px-4 py-2 transition font-medium">
                <i class="fa-solid fa-gift mr-1"></i> Accorder
            </button>
        </form>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="bg-success/20 border border-success/40 text-success rounded-lg px-4 py-3">{{ session('success') }}</div>
    @endif

    {{-- Filtres --}}
    <div class="bg-dark-100 rounded-xl border border-gray-700/50 p-5">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">
            <select name="status" class="bg-gray-800 border border-gray-600 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-primary">
                <option value="">Tous statuts</option>
                <option value="active"    {{ request('status') === 'active'    ? 'selected' : '' }}>Actif</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Annulé</option>
                <option value="expired"   {{ request('status') === 'expired'   ? 'selected' : '' }}>Expiré</option>
            </select>
            <select name="plan" class="bg-gray-800 border border-gray-600 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-primary">
                <option value="">Tous les plans</option>
                <option value="weekly"    {{ request('plan') === 'weekly'    ? 'selected' : '' }}>Hebdomadaire</option>
                <option value="monthly"   {{ request('plan') === 'monthly'   ? 'selected' : '' }}>Mensuel</option>
                <option value="quarterly" {{ request('plan') === 'quarterly' ? 'selected' : '' }}>Trimestriel</option>
            </select>
            <select name="payment_method" class="bg-gray-800 border border-gray-600 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-primary">
                <option value="">Tous moyens</option>
                <option value="paydunya"    {{ request('payment_method') === 'paydunya'    ? 'selected' : '' }}>Paydunya</option>
                <option value="admin"       {{ request('payment_method') === 'admin'       ? 'selected' : '' }}>Admin</option>
                <option value="referral"    {{ request('payment_method') === 'referral'    ? 'selected' : '' }}>Parrainage</option>
                <option value="affiliation" {{ request('payment_method') === 'affiliation' ? 'selected' : '' }}>Affiliation</option>
            </select>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher utilisateur..."
                   class="bg-gray-800 border border-gray-600 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-primary">
            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-primary hover:bg-primary/80 text-white rounded-lg px-4 py-2 transition">
                    <i class="fa-solid fa-search mr-1"></i> Filtrer
                </button>
                <a href="{{ route('admin.subscriptions.index') }}" class="bg-gray-700 hover:bg-gray-600 text-white rounded-lg px-4 py-2 transition">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-dark-100 rounded-xl border border-gray-700/50 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-700/50 text-gray-400 text-xs uppercase tracking-wider">
                        <th class="px-6 py-4 text-left">#</th>
                        <th class="px-6 py-4 text-left">Utilisateur</th>
                        <th class="px-6 py-4 text-left">Plan</th>
                        <th class="px-6 py-4 text-left">Montant</th>
                        <th class="px-6 py-4 text-left">Moyen</th>
                        <th class="px-6 py-4 text-left">Statut</th>
                        <th class="px-6 py-4 text-left">Expiration</th>
                        <th class="px-6 py-4 text-left">Date</th>
                        <th class="px-6 py-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700/30">
                    @forelse($subscriptions as $sub)
                    @php
                        $isActive  = $sub->status === 'active' && $sub->expires_at && $sub->expires_at->isFuture();
                        $planLabels = ['weekly' => 'Hebdo', 'monthly' => 'Mensuel', 'quarterly' => 'Trimestriel'];
                        $statusColor = match($sub->status) {
                            'active'    => $isActive ? 'bg-success/20 text-success' : 'bg-gray-500/20 text-gray-400',
                            'cancelled' => 'bg-danger/20 text-danger',
                            'expired'   => 'bg-gray-500/20 text-gray-400',
                            default     => 'bg-warning/20 text-warning',
                        };
                        $statusLabel = $isActive ? 'Actif' : ucfirst($sub->status);
                    @endphp
                    <tr class="hover:bg-gray-800/30 transition">
                        <td class="px-6 py-4 text-gray-500">{{ $sub->id }}</td>
                        <td class="px-6 py-4">
                            <p class="text-white font-medium">{{ $sub->user->name ?? '—' }}</p>
                            <p class="text-gray-500 text-xs">{{ $sub->user->email ?? $sub->user->phone ?? '' }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded-full text-xs font-medium bg-primary/20 text-primary">
                                {{ $planLabels[$sub->plan] ?? $sub->plan }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-white font-medium">
                            {{ $sub->amount > 0 ? number_format($sub->amount) . ' FCFA' : 'Gratuit' }}
                        </td>
                        <td class="px-6 py-4 text-gray-400 capitalize">{{ $sub->payment_method }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $statusColor }}">{{ $statusLabel }}</span>
                        </td>
                        <td class="px-6 py-4 text-gray-400 text-xs">
                            {{ $sub->expires_at ? $sub->expires_at->format('d/m/Y') : '—' }}
                            @if($isActive)
                                <span class="block text-success">{{ $sub->expires_at->diffForHumans() }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-gray-400 text-xs">{{ $sub->created_at->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 text-right">
                            @if($sub->status === 'active' && $isActive)
                            <form method="POST" action="{{ route('admin.subscriptions.cancel', $sub) }}"
                                  onsubmit="return confirm('Annuler cet abonnement ?')">
                                @csrf @method('PATCH')
                                <button class="px-3 py-1.5 rounded-lg text-xs bg-danger/20 text-danger hover:bg-danger/30 transition">
                                    Annuler
                                </button>
                            </form>
                            @else
                                <span class="text-gray-600 text-xs">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                            <i class="fa-solid fa-credit-card text-3xl mb-3 block"></i>
                            Aucun abonnement trouvé
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($subscriptions->hasPages())
        <div class="px-6 py-4 border-t border-gray-700/50">
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
                backgroundColor: 'rgba(99,102,241,0.7)',
                borderColor: '#6366F1',
                borderWidth: 1,
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#9CA3AF' } },
                y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#9CA3AF' } }
            }
        }
    });
</script>
@endpush
