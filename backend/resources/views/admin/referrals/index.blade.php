@extends('admin.layouts.app')

@section('title', 'Parrainages')
@section('page-title', 'Système de Parrainage')

@section('content')
<div class="space-y-6">

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="bg-dark-100 rounded-lg border border-gray-700/50 p-4 text-center">
            <p class="text-2xl font-bold text-white">{{ $stats['total'] }}</p>
            <p class="text-sm text-gray-400">Total</p>
        </div>
        <div class="bg-dark-100 rounded-lg border border-gray-700/50 p-4 text-center">
            <p class="text-2xl font-bold text-success">{{ $stats['completed'] }}</p>
            <p class="text-sm text-gray-400">Complétés</p>
        </div>
        <div class="bg-dark-100 rounded-lg border border-gray-700/50 p-4 text-center">
            <p class="text-2xl font-bold text-warning">{{ $stats['pending'] }}</p>
            <p class="text-sm text-gray-400">En attente</p>
        </div>
        <div class="bg-dark-100 rounded-lg border border-gray-700/50 p-4 text-center">
            <p class="text-2xl font-bold text-primary">{{ $stats['rewards_given'] }}</p>
            <p class="text-sm text-gray-400">Récompenses</p>
        </div>
        <div class="bg-dark-100 rounded-lg border border-gray-700/50 p-4 text-center">
            <p class="text-2xl font-bold text-secondary">{{ $stats['this_month'] }}</p>
            <p class="text-sm text-gray-400">Ce mois</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Top parrains --}}
        <div class="bg-dark-100 rounded-xl border border-gray-700/50 p-6">
            <h3 class="text-white font-semibold mb-4">
                <i class="fa-solid fa-trophy mr-2 text-warning"></i>Top Parrains
            </h3>
            <div class="space-y-3">
                @forelse($topReferrers as $i => $referrer)
                <div class="flex items-center gap-3">
                    <span class="w-6 text-center text-xs font-bold {{ $i === 0 ? 'text-yellow-400' : ($i === 1 ? 'text-gray-300' : ($i === 2 ? 'text-amber-600' : 'text-gray-500')) }}">
                        #{{ $i + 1 }}
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="text-white text-sm font-medium truncate">{{ $referrer->name }}</p>
                        <p class="text-gray-500 text-xs">Code: {{ $referrer->referral_code }}</p>
                    </div>
                    <span class="px-2 py-1 rounded-full text-xs font-bold bg-success/20 text-success">
                        {{ $referrer->referral_count }} filleuls
                    </span>
                </div>
                @empty
                <p class="text-gray-500 text-sm text-center py-4">Aucun parrainage complété</p>
                @endforelse
            </div>
        </div>

        {{-- Paliers de récompenses --}}
        <div class="bg-dark-100 rounded-xl border border-gray-700/50 p-6">
            <h3 class="text-white font-semibold mb-4">
                <i class="fa-solid fa-gift mr-2 text-primary"></i>Paliers de récompenses
            </h3>
            <div class="space-y-3">
                @foreach([1 => '3 jours', 3 => '7 jours', 10 => '30 jours', 50 => '🏆 Premium à vie'] as $filleuls => $reward)
                <div class="flex items-center justify-between p-3 bg-gray-800/50 rounded-lg">
                    <span class="text-gray-300 text-sm">{{ $filleuls }} filleul{{ $filleuls > 1 ? 's' : '' }}</span>
                    <span class="text-primary font-medium text-sm">{{ $reward }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Filtres --}}
        <div class="bg-dark-100 rounded-xl border border-gray-700/50 p-6">
            <h3 class="text-white font-semibold mb-4"><i class="fa-solid fa-filter mr-2 text-gray-400"></i>Filtres</h3>
            <form method="GET" class="space-y-3">
                <select name="status" class="w-full bg-gray-800 border border-gray-600 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-primary">
                    <option value="">Tous les statuts</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Complété</option>
                    <option value="pending"   {{ request('status') === 'pending'   ? 'selected' : '' }}>En attente</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Annulé</option>
                </select>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher parrain / filleul..."
                       class="w-full bg-gray-800 border border-gray-600 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-primary">
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 bg-primary hover:bg-primary/80 text-white rounded-lg px-4 py-2 transition">
                        <i class="fa-solid fa-search mr-1"></i> Filtrer
                    </button>
                    <a href="{{ route('admin.referrals.index') }}" class="bg-gray-700 hover:bg-gray-600 text-white rounded-lg px-4 py-2 transition">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="bg-success/20 border border-success/40 text-success rounded-lg px-4 py-3">{{ session('success') }}</div>
    @endif

    {{-- Table --}}
    <div class="bg-dark-100 rounded-xl border border-gray-700/50 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-700/50 text-gray-400 text-xs uppercase tracking-wider">
                        <th class="px-6 py-4 text-left">#</th>
                        <th class="px-6 py-4 text-left">Parrain</th>
                        <th class="px-6 py-4 text-left">Filleul</th>
                        <th class="px-6 py-4 text-left">Code</th>
                        <th class="px-6 py-4 text-left">Statut</th>
                        <th class="px-6 py-4 text-left">Récompense</th>
                        <th class="px-6 py-4 text-left">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700/30">
                    @forelse($referrals as $referral)
                    @php
                        $statusColors = [
                            'completed' => 'bg-success/20 text-success',
                            'pending'   => 'bg-warning/20 text-warning',
                            'cancelled' => 'bg-danger/20 text-danger',
                        ];
                        $statusLabels = ['completed' => 'Complété', 'pending' => 'En attente', 'cancelled' => 'Annulé'];
                    @endphp
                    <tr class="hover:bg-gray-800/30 transition">
                        <td class="px-6 py-4 text-gray-500">{{ $referral->id }}</td>
                        <td class="px-6 py-4">
                            <p class="text-white font-medium">{{ $referral->referrer->name ?? '—' }}</p>
                            <p class="text-gray-500 text-xs">{{ $referral->referrer->email ?? $referral->referrer->phone ?? '' }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-white">{{ $referral->referred->name ?? '—' }}</p>
                            <p class="text-gray-500 text-xs">{{ $referral->referred->email ?? $referral->referred->phone ?? '' }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-mono text-xs text-gray-300 bg-gray-800 px-2 py-1 rounded">
                                {{ $referral->referral_code ?? '—' }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $statusColors[$referral->status] ?? 'bg-gray-500/20 text-gray-400' }}">
                                {{ $statusLabels[$referral->status] ?? $referral->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($referral->reward_granted)
                                <span class="px-2 py-1 rounded-full text-xs bg-primary/20 text-primary">
                                    {{ $referral->reward_days ?? '?' }}j offerts
                                </span>
                            @else
                                <span class="text-gray-600 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-gray-400 text-xs">{{ $referral->created_at->format('d/m/Y') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <i class="fa-solid fa-people-arrows text-3xl mb-3 block"></i>
                            Aucun parrainage trouvé
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($referrals->hasPages())
        <div class="px-6 py-4 border-t border-gray-700/50">
            {{ $referrals->links('vendor.pagination.tailwind') }}
        </div>
        @endif
    </div>
</div>
@endsection
