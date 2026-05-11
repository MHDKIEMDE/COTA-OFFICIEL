@extends('admin.layouts.app')

@section('title', 'Modifier Bookmaker')
@section('page-title', 'Modifier le Bookmaker')

@section('content')
<div class="max-w-2xl">
    <form action="{{ route('admin.bookmakers.update', $bookmaker['id']) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')
        
        <div class="bg-dark-100 rounded-xl border border-gray-700/50 p-6 space-y-6">
            <!-- ID (lecture seule) -->
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Identifiant</label>
                <input type="text" value="{{ $bookmaker['id'] }}" disabled
                       class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 text-gray-400 cursor-not-allowed">
            </div>
            
            <!-- Nom -->
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Nom affiché *</label>
                <input type="text" name="name" value="{{ old('name', $bookmaker['name']) }}" required
                       class="w-full bg-gray-800 border border-gray-600 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-primary">
            </div>
            
            <!-- URL Affiliation -->
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Lien d'affiliation *</label>
                <input type="url" name="affiliate_url" value="{{ old('affiliate_url', $bookmaker['affiliate_url']) }}" required
                       class="w-full bg-gray-800 border border-gray-600 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-primary">
            </div>
            
            <!-- Bonus Days -->
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Jours Premium offerts *</label>
                <input type="number" name="bonus_days" value="{{ old('bonus_days', $bookmaker['bonus_days'] ?? 7) }}" required min="1" max="365"
                       class="w-full bg-gray-800 border border-gray-600 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-primary">
            </div>
            
            <!-- Apparence -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Emoji Logo</label>
                    <input type="text" name="logo_emoji" value="{{ old('logo_emoji', $bookmaker['logo_emoji'] ?? '🎰') }}" maxlength="10"
                           class="w-full bg-gray-800 border border-gray-600 rounded-lg px-4 py-3 text-white text-2xl text-center focus:ring-2 focus:ring-primary">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Couleur</label>
                    <input type="color" name="color" value="{{ old('color', $bookmaker['color'] ?? '#6A1B9A') }}"
                           class="w-full h-12 bg-gray-800 border border-gray-600 rounded-lg cursor-pointer">
                </div>
            </div>
            
            <!-- Description -->
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Description</label>
                <textarea name="description" rows="3"
                          class="w-full bg-gray-800 border border-gray-600 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-primary">{{ old('description', $bookmaker['description'] ?? '') }}</textarea>
            </div>
            
            <!-- Actif -->
            <div>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ ($bookmaker['is_active'] ?? true) ? 'checked' : '' }}
                           class="w-5 h-5 text-success border-gray-600 rounded focus:ring-success bg-gray-800">
                    <span class="text-gray-300">Bookmaker actif (visible pour les utilisateurs)</span>
                </label>
            </div>
        </div>
        
        <!-- Actions -->
        <div class="flex gap-4">
            <button type="submit" class="flex-1 bg-primary hover:bg-primary/80 text-white font-semibold rounded-lg px-6 py-3 transition">
                <i class="fa-solid fa-save mr-2"></i>
                Enregistrer les modifications
            </button>
            <a href="{{ route('admin.bookmakers.index') }}" class="bg-gray-700 hover:bg-gray-600 text-white rounded-lg px-6 py-3 transition">
                Annuler
            </a>
        </div>
    </form>
</div>
@endsection

