@extends('admin.layouts.app')

@section('title', 'Modifier Pronostic')
@section('page-title', 'Modifier le Pronostic')

@section('content')
<div class="max-w-4xl">
    <form action="{{ route('admin.predictions.update', $prediction) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')
        
        <div class="bg-dark-100 rounded-xl border border-gray-700/50 p-6">
            <h3 class="text-lg font-semibold text-white mb-6">
                <i class="fa-solid fa-futbol mr-2 text-primary"></i>
                Informations du match
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Équipe Domicile *</label>
                    <input type="text" name="home_team" value="{{ old('home_team', $prediction->home_team) }}" required
                           class="w-full bg-gray-800 border border-gray-600 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-primary">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Équipe Extérieur *</label>
                    <input type="text" name="away_team" value="{{ old('away_team', $prediction->away_team) }}" required
                           class="w-full bg-gray-800 border border-gray-600 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-primary">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Compétition *</label>
                    <input type="text" name="competition" value="{{ old('competition', $prediction->competition) }}" required
                           class="w-full bg-gray-800 border border-gray-600 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-primary">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Date & Heure du match *</label>
                    <input type="datetime-local" name="match_date" 
                           value="{{ old('match_date', \Carbon\Carbon::parse($prediction->match_date)->format('Y-m-d\TH:i')) }}" required
                           class="w-full bg-gray-800 border border-gray-600 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-primary">
                </div>
            </div>
        </div>
        
        <div class="bg-dark-100 rounded-xl border border-gray-700/50 p-6">
            <h3 class="text-lg font-semibold text-white mb-6">
                <i class="fa-solid fa-chart-line mr-2 text-success"></i>
                Pronostic & Résultat
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Type de pronostic *</label>
                    <select name="prediction_type" required
                            class="w-full bg-gray-800 border border-gray-600 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-primary">
                        <option value="1X2" {{ old('prediction_type', $prediction->prediction_type) == '1X2' ? 'selected' : '' }}>1X2</option>
                        <option value="Over/Under" {{ old('prediction_type', $prediction->prediction_type) == 'Over/Under' ? 'selected' : '' }}>Over/Under</option>
                        <option value="BTTS" {{ old('prediction_type', $prediction->prediction_type) == 'BTTS' ? 'selected' : '' }}>BTTS</option>
                        <option value="Double Chance" {{ old('prediction_type', $prediction->prediction_type) == 'Double Chance' ? 'selected' : '' }}>Double Chance</option>
                        <option value="HT/FT" {{ old('prediction_type', $prediction->prediction_type) == 'HT/FT' ? 'selected' : '' }}>HT/FT</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Pronostic *</label>
                    <input type="text" name="prediction_value" value="{{ old('prediction_value', $prediction->prediction_value) }}" required
                           class="w-full bg-gray-800 border border-gray-600 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-primary">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Cote *</label>
                    <input type="number" step="0.01" min="1.01" name="odds" value="{{ old('odds', $prediction->odds) }}" required
                           class="w-full bg-gray-800 border border-gray-600 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-primary">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Confiance *</label>
                    <select name="confidence" required
                            class="w-full bg-gray-800 border border-gray-600 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-primary">
                        @for($i = 1; $i <= 4; $i++)
                            <option value="{{ $i }}" {{ old('confidence', $prediction->confidence) == $i ? 'selected' : '' }}>
                                {{ str_repeat('⭐', $i) }}
                            </option>
                        @endfor
                    </select>
                </div>
                
                <!-- Statut -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Statut *</label>
                    <select name="status" required
                            class="w-full bg-gray-800 border border-gray-600 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-primary">
                        <option value="pending" {{ old('status', $prediction->status) == 'pending' ? 'selected' : '' }}>⏳ En attente</option>
                        <option value="won" {{ old('status', $prediction->status) == 'won' ? 'selected' : '' }}>✅ Gagné</option>
                        <option value="lost" {{ old('status', $prediction->status) == 'lost' ? 'selected' : '' }}>❌ Perdu</option>
                        <option value="cancelled" {{ old('status', $prediction->status) == 'cancelled' ? 'selected' : '' }}>🚫 Annulé</option>
                    </select>
                </div>
                
                <!-- Score -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Score Domicile</label>
                        <input type="number" min="0" name="home_score" value="{{ old('home_score', $prediction->home_score) }}"
                               class="w-full bg-gray-800 border border-gray-600 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-primary"
                               placeholder="0">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Score Extérieur</label>
                        <input type="number" min="0" name="away_score" value="{{ old('away_score', $prediction->away_score) }}"
                               class="w-full bg-gray-800 border border-gray-600 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-primary"
                               placeholder="0">
                    </div>
                </div>
            </div>
            
            <!-- Analyse -->
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-300 mb-2">Analyse</label>
                <textarea name="analysis" rows="4"
                          class="w-full bg-gray-800 border border-gray-600 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-primary">{{ old('analysis', $prediction->analysis) }}</textarea>
            </div>
            
            <!-- Options -->
            <div class="mt-6 flex gap-6">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_premium" value="1" {{ old('is_premium', $prediction->is_premium) ? 'checked' : '' }}
                           class="w-5 h-5 text-yellow-500 border-gray-600 rounded focus:ring-yellow-500 bg-gray-800">
                    <span class="text-gray-300">
                        <i class="fa-solid fa-crown text-yellow-400 mr-1"></i>
                        Pronostic Premium
                    </span>
                </label>
                
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_combined" value="1" {{ old('is_combined', $prediction->is_combined) ? 'checked' : '' }}
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
            <button type="submit" class="flex-1 bg-primary hover:bg-primary/80 text-white font-semibold rounded-lg px-6 py-3 transition">
                <i class="fa-solid fa-save mr-2"></i>
                Enregistrer les modifications
            </button>
            <a href="{{ route('admin.predictions.index') }}" class="bg-gray-700 hover:bg-gray-600 text-white rounded-lg px-6 py-3 transition">
                Annuler
            </a>
        </div>
    </form>
</div>
@endsection

