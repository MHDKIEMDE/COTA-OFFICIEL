@extends('admin.layouts.app')

@section('title', 'Compétitions')
@section('page-title', 'Gestion des Compétitions')

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="stat-card bg-dark-100 rounded-xl p-6 border border-gray-700/50">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Total Compétitions</p>
                    <p class="text-3xl font-bold text-white mt-1">{{ $stats['total'] }}</p>
                </div>
                <div class="w-12 h-12 bg-primary/20 rounded-lg flex items-center justify-center">
                    <i class="fa-solid fa-trophy text-primary text-xl"></i>
                </div>
            </div>
        </div>

        <div class="stat-card bg-dark-100 rounded-xl p-6 border border-gray-700/50">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Compétitions Actives</p>
                    <p class="text-3xl font-bold text-success mt-1">{{ $stats['active'] }}</p>
                </div>
                <div class="w-12 h-12 bg-success/20 rounded-lg flex items-center justify-center">
                    <i class="fa-solid fa-check-circle text-success text-xl"></i>
                </div>
            </div>
        </div>

        <div class="stat-card bg-dark-100 rounded-xl p-6 border border-gray-700/50">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">🔥 En Tendance</p>
                    <p class="text-3xl font-bold text-orange-400 mt-1">{{ $stats['trending'] }}</p>
                </div>
                <div class="w-12 h-12 bg-orange-500/20 rounded-lg flex items-center justify-center">
                    <i class="fa-solid fa-fire text-orange-400 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions Bar -->
    <div class="bg-dark-100 rounded-xl p-4 border border-gray-700/50">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <!-- Filtres -->
            <form method="GET" class="flex flex-wrap items-center gap-3">
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Rechercher..." 
                           class="bg-dark-200 border border-gray-700 rounded-lg px-4 py-2 pl-10 text-white placeholder-gray-500 focus:border-primary focus:ring-1 focus:ring-primary">
                    <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-500"></i>
                </div>

                <select name="trending" class="bg-dark-200 border border-gray-700 rounded-lg px-4 py-2 text-white">
                    <option value="">Tous</option>
                    <option value="1" {{ request('trending') === '1' ? 'selected' : '' }}>🔥 Tendance</option>
                    <option value="0" {{ request('trending') === '0' ? 'selected' : '' }}>Non tendance</option>
                </select>

                <select name="active" class="bg-dark-200 border border-gray-700 rounded-lg px-4 py-2 text-white">
                    <option value="">Toutes</option>
                    <option value="1" {{ request('active') === '1' ? 'selected' : '' }}>Actives</option>
                    <option value="0" {{ request('active') === '0' ? 'selected' : '' }}>Inactives</option>
                </select>

                <button type="submit" class="px-4 py-2 bg-primary hover:bg-primary/80 text-white rounded-lg transition">
                    <i class="fa-solid fa-filter mr-2"></i>Filtrer
                </button>

                @if(request()->hasAny(['search', 'trending', 'active']))
                    <a href="{{ route('admin.competitions.index') }}" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition">
                        <i class="fa-solid fa-times mr-2"></i>Réinitialiser
                    </a>
                @endif
            </form>

            <!-- Actions -->
            <div class="flex items-center gap-3">
                <form action="{{ route('admin.competitions.clear-cache') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-warning/20 text-warning hover:bg-warning/30 rounded-lg transition">
                        <i class="fa-solid fa-broom mr-2"></i>Vider Cache
                    </button>
                </form>

                <a href="{{ route('admin.competitions.create') }}" class="px-4 py-2 bg-success hover:bg-success/80 text-white rounded-lg transition">
                    <i class="fa-solid fa-plus mr-2"></i>Ajouter
                </a>
            </div>
        </div>
    </div>

    <!-- Competitions Table -->
    <div class="bg-dark-100 rounded-xl border border-gray-700/50 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-dark-200 border-b border-gray-700/50">
                    <tr>
                        <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Compétition</th>
                        <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Pays</th>
                        <th class="text-center px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Priorité</th>
                        <th class="text-center px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Statut</th>
                        <th class="text-center px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">🔥 Tendance</th>
                        <th class="text-left px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Période</th>
                        <th class="text-right px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700/50">
                    @forelse($competitions as $competition)
                        <tr class="hover:bg-dark-200/50 transition {{ $competition->isTrendingNow() ? 'bg-orange-500/5' : '' }}">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl">{{ $competition->icon ?? '⚽' }}</span>
                                    <div>
                                        <p class="font-medium text-white">{{ $competition->name }}</p>
                                        <p class="text-sm text-gray-400">{{ $competition->full_name }}</p>
                                        <p class="text-xs text-gray-500 font-mono">{{ $competition->sportradar_id }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-300">{{ $competition->country ?? '-' }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full text-sm font-bold
                                    @if($competition->priority <= 2) bg-success/20 text-success
                                    @elseif($competition->priority <= 5) bg-primary/20 text-primary
                                    @else bg-gray-700 text-gray-400 @endif">
                                    {{ $competition->priority }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <form action="{{ route('admin.competitions.toggle-active', $competition) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="px-3 py-1 rounded-full text-xs font-medium transition
                                        {{ $competition->is_active 
                                            ? 'bg-success/20 text-success hover:bg-success/30' 
                                            : 'bg-gray-700 text-gray-400 hover:bg-gray-600' }}">
                                        {{ $competition->is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </form>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <form action="{{ route('admin.competitions.toggle-trending', $competition) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="px-3 py-1 rounded-full text-xs font-medium transition
                                        {{ $competition->isTrendingNow() 
                                            ? 'bg-orange-500/20 text-orange-400 hover:bg-orange-500/30' 
                                            : 'bg-gray-700 text-gray-400 hover:bg-gray-600' }}">
                                        @if($competition->isTrendingNow())
                                            🔥 Oui
                                        @else
                                            Non
                                        @endif
                                    </button>
                                </form>
                            </td>
                            <td class="px-6 py-4">
                                @if($competition->trending_start && $competition->trending_end)
                                    <span class="text-sm text-gray-400">
                                        {{ $competition->trending_start->format('d/m') }} - {{ $competition->trending_end->format('d/m/Y') }}
                                    </span>
                                @else
                                    <span class="text-sm text-gray-500">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.competitions.edit', $competition) }}" 
                                       class="p-2 text-gray-400 hover:text-primary transition" title="Modifier">
                                        <i class="fa-solid fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.competitions.destroy', $competition) }}" method="POST" 
                                          onsubmit="return confirm('Supprimer cette compétition ?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-gray-400 hover:text-danger transition" title="Supprimer">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                                <i class="fa-solid fa-trophy text-4xl mb-4 opacity-50"></i>
                                <p>Aucune compétition trouvée</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($competitions->hasPages())
            <div class="px-6 py-4 border-t border-gray-700/50">
                {{ $competitions->withQueryString()->links() }}
            </div>
        @endif
    </div>

    <!-- Info Box -->
    <div class="bg-primary/10 border border-primary/30 rounded-xl p-6">
        <h3 class="font-semibold text-primary mb-3">
            <i class="fa-solid fa-info-circle mr-2"></i>Comment fonctionne la gestion des tendances ?
        </h3>
        <ul class="text-sm text-gray-300 space-y-2">
            <li><strong>🔥 Tendance manuelle :</strong> Activez le bouton "Tendance" pour mettre une compétition en avant immédiatement.</li>
            <li><strong>📅 Tendance programmée :</strong> Définissez une période de début/fin pour activer automatiquement la tendance.</li>
            <li><strong>⭐ Priorité :</strong> Plus le chiffre est bas, plus la compétition apparaît en premier (1 = très prioritaire, 99 = normal).</li>
            <li><strong>🔄 Cache :</strong> Les modifications sont appliquées immédiatement. Videz le cache si nécessaire.</li>
        </ul>
    </div>
</div>
@endsection

