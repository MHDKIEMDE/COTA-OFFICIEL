@extends('admin.layouts.app')

@section('title', 'Ajouter une Compétition')
@section('page-title', 'Ajouter une Compétition')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-dark-100 rounded-xl border border-gray-700/50 overflow-hidden">
        <div class="bg-gradient-to-r from-primary to-secondary px-6 py-4">
            <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                <i class="fa-solid fa-trophy"></i>
                Nouvelle Compétition
            </h2>
        </div>

        <form action="{{ route('admin.competitions.store') }}" method="POST" class="p-6 space-y-6">
            @csrf

            <!-- Informations principales -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        ID Sportradar <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="sportradar_id" value="{{ old('sportradar_id') }}" 
                           placeholder="sr:competition:17"
                           class="w-full bg-dark-200 border border-gray-700 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:border-primary focus:ring-1 focus:ring-primary"
                           required>
                    <p class="mt-1 text-xs text-gray-500">Format: sr:competition:ID</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        Nom court <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}" 
                           placeholder="Premier League"
                           class="w-full bg-dark-200 border border-gray-700 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:border-primary focus:ring-1 focus:ring-primary"
                           required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        Nom complet
                    </label>
                    <input type="text" name="full_name" value="{{ old('full_name') }}" 
                           placeholder="English Premier League"
                           class="w-full bg-dark-200 border border-gray-700 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:border-primary focus:ring-1 focus:ring-primary">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        Pays / Région
                    </label>
                    <input type="text" name="country" value="{{ old('country') }}" 
                           placeholder="Angleterre"
                           class="w-full bg-dark-200 border border-gray-700 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:border-primary focus:ring-1 focus:ring-primary">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        Icône (emoji)
                    </label>
                    <input type="text" name="icon" value="{{ old('icon', '⚽') }}" 
                           placeholder="🏴󠁧󠁢󠁥󠁮󠁧󠁿"
                           class="w-full bg-dark-200 border border-gray-700 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:border-primary focus:ring-1 focus:ring-primary"
                           maxlength="10">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        Priorité <span class="text-danger">*</span>
                    </label>
                    <input type="number" name="priority" value="{{ old('priority', 99) }}" 
                           min="1" max="99"
                           class="w-full bg-dark-200 border border-gray-700 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:border-primary focus:ring-1 focus:ring-primary"
                           required>
                    <p class="mt-1 text-xs text-gray-500">1 = Très prioritaire, 99 = Normal</p>
                </div>
            </div>

            <!-- Description -->
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">
                    Description
                </label>
                <textarea name="description" rows="3"
                          placeholder="Description optionnelle de la compétition..."
                          class="w-full bg-dark-200 border border-gray-700 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:border-primary focus:ring-1 focus:ring-primary resize-none">{{ old('description') }}</textarea>
            </div>

            <!-- Options -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="flex items-center gap-3">
                    <input type="checkbox" name="is_active" id="is_active" value="1" 
                           {{ old('is_active', true) ? 'checked' : '' }}
                           class="w-5 h-5 rounded border-gray-700 bg-dark-200 text-primary focus:ring-primary">
                    <label for="is_active" class="text-gray-300">
                        <span class="font-medium">Compétition active</span>
                        <p class="text-xs text-gray-500">Sera prise en compte lors de la génération des pronostics</p>
                    </label>
                </div>

                <div class="flex items-center gap-3">
                    <input type="checkbox" name="is_trending" id="is_trending" value="1" 
                           {{ old('is_trending') ? 'checked' : '' }}
                           class="w-5 h-5 rounded border-gray-700 bg-dark-200 text-orange-500 focus:ring-orange-500">
                    <label for="is_trending" class="text-gray-300">
                        <span class="font-medium">🔥 Tendance</span>
                        <p class="text-xs text-gray-500">Mettre en avant cette compétition</p>
                    </label>
                </div>
            </div>

            <!-- Période de tendance -->
            <div class="bg-orange-500/10 border border-orange-500/30 rounded-lg p-4">
                <h3 class="font-medium text-orange-400 mb-3">
                    <i class="fa-solid fa-calendar mr-2"></i>Période de tendance programmée
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-gray-400 mb-2">Date de début</label>
                        <input type="date" name="trending_start" value="{{ old('trending_start') }}" 
                               class="w-full bg-dark-200 border border-gray-700 rounded-lg px-4 py-2 text-white focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-2">Date de fin</label>
                        <input type="date" name="trending_end" value="{{ old('trending_end') }}" 
                               class="w-full bg-dark-200 border border-gray-700 rounded-lg px-4 py-2 text-white focus:border-orange-500 focus:ring-1 focus:ring-orange-500">
                    </div>
                </div>
                <p class="mt-2 text-xs text-gray-500">La compétition sera automatiquement en tendance pendant cette période.</p>
            </div>

            <!-- Boutons -->
            <div class="flex items-center justify-between pt-6 border-t border-gray-700/50">
                <a href="{{ route('admin.competitions.index') }}" class="px-6 py-3 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition">
                    <i class="fa-solid fa-arrow-left mr-2"></i>Retour
                </a>
                <button type="submit" class="px-6 py-3 bg-success hover:bg-success/80 text-white rounded-lg transition font-medium">
                    <i class="fa-solid fa-check mr-2"></i>Créer la compétition
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

