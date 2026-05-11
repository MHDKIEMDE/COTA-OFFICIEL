@extends('admin.layouts.app')

@section('title', 'Affiliations')
@section('page-title', 'Gestion des Affiliations')

@section('content')
<div class="space-y-6">
    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-dark-100 rounded-lg border border-gray-700/50 p-4 text-center">
            <p class="text-2xl font-bold text-white">{{ $stats['total'] }}</p>
            <p class="text-sm text-gray-400">Total</p>
        </div>
        <div class="bg-dark-100 rounded-lg border border-gray-700/50 p-4 text-center">
            <p class="text-2xl font-bold text-success">{{ $stats['verified'] }}</p>
            <p class="text-sm text-gray-400">Vérifiées</p>
        </div>
        <div class="bg-dark-100 rounded-lg border border-gray-700/50 p-4 text-center">
            <p class="text-2xl font-bold text-warning">{{ $stats['pending'] }}</p>
            <p class="text-sm text-gray-400">En attente</p>
        </div>
        <div class="bg-dark-100 rounded-lg border border-gray-700/50 p-4 text-center">
            <p class="text-2xl font-bold text-primary">{{ $stats['premium_given'] }}</p>
            <p class="text-sm text-gray-400">Jours offerts</p>
        </div>
    </div>
    
    <!-- Bookmaker Stats -->
    @if($bookmakerStats->count() > 0)
        <div class="bg-dark-100 rounded-xl border border-gray-700/50 p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Par Bookmaker</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach($bookmakerStats as $bookmaker => $data)
                    <div class="p-4 bg-gray-800/50 rounded-lg">
                        <p class="text-white font-medium">{{ ucfirst($bookmaker) }}</p>
                        <p class="text-2xl font-bold text-primary mt-1">{{ $data->total }}</p>
                        <p class="text-xs text-gray-400">
                            <span class="text-success">{{ $data->verified }} vérifiés</span>
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
    
    <!-- Filters -->
    <div class="bg-dark-100 rounded-xl border border-gray-700/50 p-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <select name="bookmaker" class="w-full bg-gray-800 border border-gray-600 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-primary">
                    <option value="">Tous les bookmakers</option>
                    <option value="betwinner" {{ request('bookmaker') == 'betwinner' ? 'selected' : '' }}>BetWinner</option>
                    <option value="1xbet" {{ request('bookmaker') == '1xbet' ? 'selected' : '' }}>1xBet</option>
                    <option value="melbet" {{ request('bookmaker') == 'melbet' ? 'selected' : '' }}>Melbet</option>
                </select>
            </div>
            <div>
                <select name="status" class="w-full bg-gray-800 border border-gray-600 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-primary">
                    <option value="">Tous les statuts</option>
                    <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Vérifiées</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                </select>
            </div>
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="ID joueur, nom, téléphone..." 
                       class="w-full bg-gray-800 border border-gray-600 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-primary">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-primary hover:bg-primary/80 text-white rounded-lg px-4 py-2 transition">
                    <i class="fa-solid fa-search mr-1"></i> Filtrer
                </button>
                <a href="{{ route('admin.affiliates.index') }}" class="bg-gray-700 hover:bg-gray-600 text-white rounded-lg px-4 py-2 transition">
                    <i class="fa-solid fa-times"></i>
                </a>
            </div>
        </form>
    </div>
    
    <!-- Table -->
    <div class="bg-dark-100 rounded-xl border border-gray-700/50 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-800/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Utilisateur</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Bookmaker</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">ID Joueur</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase">Bonus</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase">Statut</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase">Date</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700/50">
                    @forelse($affiliations as $affiliation)
                        <tr class="hover:bg-gray-800/30 transition">
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-r from-secondary to-primary flex items-center justify-center text-white text-sm">
                                        {{ substr($affiliation->user->name ?? 'U', 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="text-white font-medium">{{ $affiliation->user->name ?? 'Sans nom' }}</div>
                                        <div class="text-gray-500 text-xs">{{ $affiliation->user->phone ?? '-' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <span class="px-3 py-1 rounded-full bg-primary/20 text-primary text-sm font-medium">
                                    {{ ucfirst($affiliation->bookmaker) }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <code class="px-2 py-1 bg-gray-800 rounded text-white text-sm">{{ $affiliation->player_id }}</code>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="text-success font-medium">+{{ $affiliation->bonus_days }} jours</span>
                            </td>
                            <td class="px-4 py-4 text-center">
                                @if($affiliation->is_verified)
                                    <span class="px-2 py-1 rounded text-xs bg-success/20 text-success">
                                        <i class="fa-solid fa-check mr-1"></i>Vérifié
                                    </span>
                                @else
                                    <span class="px-2 py-1 rounded text-xs bg-warning/20 text-warning">
                                        <i class="fa-solid fa-clock mr-1"></i>En attente
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-center text-gray-400 text-sm">
                                {{ $affiliation->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex justify-end gap-2">
                                    @if(!$affiliation->is_verified)
                                        <form action="{{ route('admin.affiliates.verify', $affiliation) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="p-2 text-success hover:bg-success/20 rounded transition" title="Vérifier">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.affiliates.reject', $affiliation) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="p-2 text-danger hover:bg-danger/20 rounded transition" title="Rejeter">
                                                <i class="fa-solid fa-times"></i>
                                            </button>
                                        </form>
                                    @endif
                                    
                                    <form action="{{ route('admin.affiliates.destroy', $affiliation) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Supprimer cette affiliation ?')">
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
                            <td colspan="7" class="px-4 py-8 text-center text-gray-400">
                                Aucune affiliation trouvée
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($affiliations->hasPages())
            <div class="px-6 py-4 border-t border-gray-700/50">
                {{ $affiliations->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

