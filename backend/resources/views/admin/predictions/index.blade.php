@extends('admin.layouts.app')

@section('title', 'Pronostics')
@section('page-title', 'Gestion des Pronostics')

@section('content')
<div class="space-y-6">
    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-dark-100 rounded-lg border border-gray-700/50 p-4 text-center">
            <p class="text-2xl font-bold text-white">{{ $stats['total'] }}</p>
            <p class="text-sm text-gray-400">Total</p>
        </div>
        <div class="bg-dark-100 rounded-lg border border-gray-700/50 p-4 text-center">
            <p class="text-2xl font-bold text-warning">{{ $stats['pending'] }}</p>
            <p class="text-sm text-gray-400">En attente</p>
        </div>
        <div class="bg-dark-100 rounded-lg border border-gray-700/50 p-4 text-center">
            <p class="text-2xl font-bold text-success">{{ $stats['won'] }}</p>
            <p class="text-sm text-gray-400">Gagnés</p>
        </div>
        <div class="bg-dark-100 rounded-lg border border-gray-700/50 p-4 text-center">
            <p class="text-2xl font-bold text-danger">{{ $stats['lost'] }}</p>
            <p class="text-sm text-gray-400">Perdus</p>
        </div>
    </div>
    
    <!-- Filters & Actions -->
    <div class="bg-dark-100 rounded-xl border border-gray-700/50 p-6">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <div>
                <select name="status" class="w-full bg-gray-800 border border-gray-600 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-primary">
                    <option value="">Tous les statuts</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                    <option value="won" {{ request('status') == 'won' ? 'selected' : '' }}>Gagnés</option>
                    <option value="lost" {{ request('status') == 'lost' ? 'selected' : '' }}>Perdus</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Annulés</option>
                </select>
            </div>
            <div>
                <select name="prediction_type" class="w-full bg-gray-800 border border-gray-600 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-primary">
                    <option value="">Tous les types</option>
                    <option value="1X2" {{ request('prediction_type') == '1X2' ? 'selected' : '' }}>1X2</option>
                    <option value="Over/Under" {{ request('prediction_type') == 'Over/Under' ? 'selected' : '' }}>Over/Under</option>
                    <option value="BTTS" {{ request('prediction_type') == 'BTTS' ? 'selected' : '' }}>BTTS</option>
                    <option value="Double Chance" {{ request('prediction_type') == 'Double Chance' ? 'selected' : '' }}>Double Chance</option>
                </select>
            </div>
            <div>
                <select name="is_premium" class="w-full bg-gray-800 border border-gray-600 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-primary">
                    <option value="">Tous</option>
                    <option value="true" {{ request('is_premium') == 'true' ? 'selected' : '' }}>Premium</option>
                    <option value="false" {{ request('is_premium') == 'false' ? 'selected' : '' }}>Gratuit</option>
                </select>
            </div>
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher..." 
                       class="w-full bg-gray-800 border border-gray-600 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-primary">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-primary hover:bg-primary/80 text-white rounded-lg px-4 py-2 transition">
                    <i class="fa-solid fa-search mr-1"></i> Filtrer
                </button>
                <a href="{{ route('admin.predictions.index') }}" class="bg-gray-700 hover:bg-gray-600 text-white rounded-lg px-4 py-2 transition">
                    <i class="fa-solid fa-times"></i>
                </a>
            </div>
            <div>
                <a href="{{ route('admin.predictions.create') }}" class="block bg-success hover:bg-success/80 text-white rounded-lg px-4 py-2 text-center transition">
                    <i class="fa-solid fa-plus mr-1"></i> Nouveau
                </a>
            </div>
        </form>
    </div>
    
    <!-- Table -->
    <div class="bg-dark-100 rounded-xl border border-gray-700/50 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px]">
                <thead class="bg-gray-800/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Match</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Compétition</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Date</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase">Prono</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase">Cote</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase">Score</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase">Statut</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase">Type</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700/50">
                    @forelse($predictions as $prediction)
                        <tr class="hover:bg-gray-800/30 transition">
                            <td class="px-4 py-4">
                                <div class="text-white font-medium">{{ $prediction->home_team }}</div>
                                <div class="text-gray-400 text-sm">vs {{ $prediction->away_team }}</div>
                            </td>
                            <td class="px-4 py-4 text-gray-300 text-sm">{{ $prediction->competition }}</td>
                            <td class="px-4 py-4 text-gray-300 text-sm">{{ \Carbon\Carbon::parse($prediction->match_date)->format('d/m H:i') }}</td>
                            <td class="px-4 py-4 text-center">
                                <span class="px-2 py-1 bg-primary/20 text-primary rounded text-xs">
                                    {{ $prediction->prediction_value }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-center text-white font-medium">{{ $prediction->odds }}</td>
                            <td class="px-4 py-4 text-center">
                                @if($prediction->home_score !== null && $prediction->away_score !== null)
                                    <span class="text-white font-medium">{{ $prediction->home_score }} - {{ $prediction->away_score }}</span>
                                @else
                                    <span class="text-gray-500">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-center">
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-warning/20 text-warning',
                                        'won' => 'bg-success/20 text-success',
                                        'lost' => 'bg-danger/20 text-danger',
                                        'cancelled' => 'bg-gray-500/20 text-gray-400',
                                    ];
                                    $statusLabels = [
                                        'pending' => 'En attente',
                                        'won' => 'Gagné',
                                        'lost' => 'Perdu',
                                        'cancelled' => 'Annulé',
                                    ];
                                @endphp
                                <span class="px-2 py-1 rounded text-xs {{ $statusColors[$prediction->status] ?? 'bg-gray-500/20 text-gray-400' }}">
                                    {{ $statusLabels[$prediction->status] ?? $prediction->status }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-center">
                                @if($prediction->is_premium)
                                    <span class="px-2 py-1 bg-yellow-500/20 text-yellow-400 rounded text-xs">
                                        <i class="fa-solid fa-crown"></i> Premium
                                    </span>
                                @else
                                    <span class="px-2 py-1 bg-gray-500/20 text-gray-400 rounded text-xs">Gratuit</span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex justify-end gap-2">
                                    <!-- Quick Status Update -->
                                    @if($prediction->status === 'pending')
                                        <form action="{{ route('admin.predictions.updateStatus', $prediction) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="won">
                                            <button type="submit" class="p-2 text-success hover:bg-success/20 rounded transition" title="Marquer gagné">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.predictions.updateStatus', $prediction) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="lost">
                                            <button type="submit" class="p-2 text-danger hover:bg-danger/20 rounded transition" title="Marquer perdu">
                                                <i class="fa-solid fa-times"></i>
                                            </button>
                                        </form>
                                    @endif
                                    
                                    <a href="{{ route('admin.predictions.edit', $prediction) }}" 
                                       class="p-2 text-primary hover:bg-primary/20 rounded transition" title="Modifier">
                                        <i class="fa-solid fa-edit"></i>
                                    </a>
                                    
                                    <form action="{{ route('admin.predictions.destroy', $prediction) }}" method="POST" class="inline" 
                                          onsubmit="return confirm('Supprimer ce pronostic ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-danger hover:bg-danger/20 rounded transition" title="Supprimer">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-8 text-center text-gray-400">
                                Aucun pronostic trouvé
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($predictions->hasPages())
            <div class="px-6 py-4 border-t border-gray-700/50">
                {{ $predictions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

