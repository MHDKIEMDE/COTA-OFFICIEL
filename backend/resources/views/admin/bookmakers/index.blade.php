@extends('admin.layouts.app')

@section('title', 'Bookmakers')
@section('page-title', 'Gestion des Bookmakers')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <p class="text-gray-400">Configurez les liens d'affiliation et les bonus pour chaque bookmaker.</p>
        </div>
        <a href="{{ route('admin.bookmakers.create') }}" class="bg-success hover:bg-success/80 text-white px-4 py-2 rounded-lg transition">
            <i class="fa-solid fa-plus mr-2"></i>Ajouter un bookmaker
        </a>
    </div>
    
    <!-- Default Bonus Days -->
    <div class="bg-dark-100 rounded-xl border border-gray-700/50 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-white">Bonus par défaut</h3>
                <p class="text-gray-400 text-sm">Nombre de jours Premium offerts par défaut pour chaque affiliation.</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-3xl font-bold text-primary">{{ $defaultBonusDays }}</span>
                <span class="text-gray-400">jours</span>
            </div>
        </div>
    </div>
    
    <!-- Bookmakers Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($bookmakers as $id => $bookmaker)
            <div class="bg-dark-100 rounded-xl border border-gray-700/50 overflow-hidden {{ ($bookmaker['is_active'] ?? true) ? '' : 'opacity-50' }}">
                <!-- Header -->
                <div class="p-6 border-b border-gray-700/50" style="background: linear-gradient(135deg, {{ $bookmaker['color'] ?? '#6A1B9A' }}33 0%, transparent 100%);">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="text-4xl">{{ $bookmaker['logo_emoji'] ?? '🎰' }}</span>
                            <div>
                                <h3 class="text-xl font-bold text-white">{{ $bookmaker['name'] }}</h3>
                                <code class="text-xs text-gray-400">{{ $id }}</code>
                            </div>
                        </div>
                        @if($bookmaker['is_active'] ?? true)
                            <span class="px-2 py-1 rounded text-xs bg-success/20 text-success">Actif</span>
                        @else
                            <span class="px-2 py-1 rounded text-xs bg-danger/20 text-danger">Inactif</span>
                        @endif
                    </div>
                </div>
                
                <!-- Body -->
                <div class="p-6 space-y-4">
                    <!-- Bonus -->
                    <div class="flex items-center justify-between">
                        <span class="text-gray-400">Bonus Premium</span>
                        <span class="text-success font-bold">+{{ $bookmaker['bonus_days'] ?? 7 }} jours</span>
                    </div>
                    
                    <!-- URL -->
                    <div>
                        <span class="text-gray-400 text-sm">Lien d'affiliation</span>
                        <p class="text-primary text-sm truncate mt-1">{{ $bookmaker['affiliate_url'] ?? '-' }}</p>
                    </div>
                    
                    <!-- Description -->
                    @if(!empty($bookmaker['description']))
                        <div>
                            <span class="text-gray-400 text-sm">Description</span>
                            <p class="text-gray-300 text-sm mt-1">{{ $bookmaker['description'] }}</p>
                        </div>
                    @endif
                </div>
                
                <!-- Actions -->
                <div class="p-4 border-t border-gray-700/50 flex gap-2">
                    <a href="{{ route('admin.bookmakers.edit', $id) }}" 
                       class="flex-1 bg-primary/20 hover:bg-primary/30 text-primary text-center px-4 py-2 rounded-lg transition">
                        <i class="fa-solid fa-edit mr-1"></i> Modifier
                    </a>
                    
                    <form action="{{ route('admin.bookmakers.toggle', $id) }}" method="POST" class="flex-1">
                        @csrf
                        <button type="submit" 
                                class="w-full {{ ($bookmaker['is_active'] ?? true) ? 'bg-warning/20 hover:bg-warning/30 text-warning' : 'bg-success/20 hover:bg-success/30 text-success' }} px-4 py-2 rounded-lg transition">
                            @if($bookmaker['is_active'] ?? true)
                                <i class="fa-solid fa-pause mr-1"></i> Désactiver
                            @else
                                <i class="fa-solid fa-play mr-1"></i> Activer
                            @endif
                        </button>
                    </form>
                    
                    <form action="{{ route('admin.bookmakers.destroy', $id) }}" method="POST"
                          onsubmit="return confirm('Supprimer ce bookmaker ?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-danger/20 hover:bg-danger/30 text-danger px-4 py-2 rounded-lg transition">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-3 bg-dark-100 rounded-xl border border-gray-700/50 p-12 text-center">
                <i class="fa-solid fa-link text-4xl text-gray-600 mb-4"></i>
                <h3 class="text-xl font-semibold text-white mb-2">Aucun bookmaker</h3>
                <p class="text-gray-400 mb-4">Ajoutez votre premier bookmaker partenaire.</p>
                <a href="{{ route('admin.bookmakers.create') }}" class="inline-block bg-success hover:bg-success/80 text-white px-6 py-2 rounded-lg transition">
                    <i class="fa-solid fa-plus mr-2"></i>Ajouter un bookmaker
                </a>
            </div>
        @endforelse
    </div>
</div>
@endsection

