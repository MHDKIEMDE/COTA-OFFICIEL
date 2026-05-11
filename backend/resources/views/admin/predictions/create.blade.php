@extends('admin.layouts.app')

@section('title', 'Nouveau Pronostic')
@section('page-title', 'Ajouter un Pronostic')

@section('content')
<div class="max-w-4xl">
    <form action="{{ route('admin.predictions.store') }}" method="POST" class="space-y-6">
        @csrf
        
        <div class="bg-dark-100 rounded-xl border border-gray-700/50 p-6">
            <h3 class="text-lg font-semibold text-white mb-6">
                <i class="fa-solid fa-futbol mr-2 text-primary"></i>
                Informations du match
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Équipe domicile -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Équipe Domicile *</label>
                    <input type="text" name="home_team" value="{{ old('home_team') }}" required
                           class="w-full bg-gray-800 border border-gray-600 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-primary"
                           placeholder="Ex: Paris Saint-Germain">
                </div>
                
                <!-- Équipe extérieur -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Équipe Extérieur *</label>
                    <input type="text" name="away_team" value="{{ old('away_team') }}" required
                           class="w-full bg-gray-800 border border-gray-600 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-primary"
                           placeholder="Ex: Olympique de Marseille">
                </div>
                
                <!-- Compétition -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Compétition *</label>
                    <input type="text" name="competition" value="{{ old('competition') }}" required
                           class="w-full bg-gray-800 border border-gray-600 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-primary"
                           placeholder="Ex: Ligue 1">
                </div>
                
                <!-- Date du match -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Date & Heure du match *</label>
                    <input type="datetime-local" name="match_date" value="{{ old('match_date') }}" required
                           class="w-full bg-gray-800 border border-gray-600 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-primary">
                </div>
            </div>
        </div>
        
        <div class="bg-dark-100 rounded-xl border border-gray-700/50 p-6">
            <h3 class="text-lg font-semibold text-white mb-6">
                <i class="fa-solid fa-chart-line mr-2 text-success"></i>
                Pronostic
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Type de pronostic -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Type de pronostic *</label>
                    <select name="prediction_type" required
                            class="w-full bg-gray-800 border border-gray-600 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-primary">
                        <option value="">Sélectionner...</option>
                        <option value="1X2" {{ old('prediction_type') == '1X2' ? 'selected' : '' }}>1X2 (Résultat final)</option>
                        <option value="Over/Under" {{ old('prediction_type') == 'Over/Under' ? 'selected' : '' }}>Over/Under</option>
                        <option value="BTTS" {{ old('prediction_type') == 'BTTS' ? 'selected' : '' }}>BTTS (Les deux équipes marquent)</option>
                        <option value="Double Chance" {{ old('prediction_type') == 'Double Chance' ? 'selected' : '' }}>Double Chance</option>
                        <option value="HT/FT" {{ old('prediction_type') == 'HT/FT' ? 'selected' : '' }}>Mi-temps / Fin de match</option>
                    </select>
                </div>
                
                <!-- Valeur du pronostic -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Pronostic *</label>
                    <input type="text" name="prediction_value" value="{{ old('prediction_value') }}" required
                           class="w-full bg-gray-800 border border-gray-600 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-primary"
                           placeholder="Ex: 1, X, 2, Over 2.5, BTTS Oui...">
                </div>
                
                <!-- Cote -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Cote *</label>
                    <input type="number" step="0.01" min="1.01" name="odds" value="{{ old('odds') }}" required
                           class="w-full bg-gray-800 border border-gray-600 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-primary"
                           placeholder="Ex: 1.85">
                </div>
                
                <!-- Confiance -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Niveau de confiance (1-4 étoiles) *</label>
                    <select name="confidence" required
                            class="w-full bg-gray-800 border border-gray-600 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-primary">
                        <option value="1" {{ old('confidence') == '1' ? 'selected' : '' }}>⭐ - Faible</option>
                        <option value="2" {{ old('confidence') == '2' ? 'selected' : '' }}>⭐⭐ - Moyen</option>
                        <option value="3" {{ old('confidence', '3') == '3' ? 'selected' : '' }}>⭐⭐⭐ - Élevé</option>
                        <option value="4" {{ old('confidence') == '4' ? 'selected' : '' }}>⭐⭐⭐⭐ - Très élevé</option>
                    </select>
                </div>
            </div>
            
            <!-- Analyse -->
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-300 mb-2">Analyse (optionnel)</label>
                <textarea name="analysis" rows="4"
                          class="w-full bg-gray-800 border border-gray-600 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-primary"
                          placeholder="Justification du pronostic...">{{ old('analysis') }}</textarea>
            </div>
            
            <!-- Options -->
            <div class="mt-6 flex gap-6">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_premium" value="1" {{ old('is_premium') ? 'checked' : '' }}
                           class="w-5 h-5 text-yellow-500 border-gray-600 rounded focus:ring-yellow-500 bg-gray-800">
                    <span class="text-gray-300">
                        <i class="fa-solid fa-crown text-yellow-400 mr-1"></i>
                        Pronostic Premium
                    </span>
                </label>
                
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_combined" value="1" {{ old('is_combined') ? 'checked' : '' }}
                           class="w-5 h-5 text-primary border-gray-600 rounded focus:ring-primary bg-gray-800">
                    <span class="text-gray-300">
                        <i class="fa-solid fa-layer-group text-primary mr-1"></i>
                        Inclure dans le combiné
                    </span>
                </label>
            </div>
        </div>
        
        <!-- Actions -->
        <div class="flex gap-4">
            <button type="submit" class="flex-1 bg-success hover:bg-success/80 text-white font-semibold rounded-lg px-6 py-3 transition">
                <i class="fa-solid fa-save mr-2"></i>
                Enregistrer le pronostic
            </button>
            <a href="{{ route('admin.predictions.index') }}" class="bg-gray-700 hover:bg-gray-600 text-white rounded-lg px-6 py-3 transition">
                Annuler
            </a>
        </div>
    </form>
</div>
@endsection

