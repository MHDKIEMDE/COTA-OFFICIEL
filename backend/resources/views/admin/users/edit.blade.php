@extends('admin.layouts.app')

@section('title', 'Modifier Utilisateur')
@section('page-title', 'Modifier l\'Utilisateur')

@section('content')
<div class="max-w-2xl">
    <form action="{{ route('admin.users.update', $user) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')
        
        <div class="bg-dark-100 rounded-xl border border-gray-700/50 p-6 space-y-6">
            <h3 class="text-lg font-semibold text-white">
                <i class="fa-solid fa-user mr-2 text-primary"></i>
                Informations de base
            </h3>
            
            <!-- Nom -->
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Nom *</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                       class="w-full bg-gray-800 border border-gray-600 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-primary">
            </div>
            
            <!-- Téléphone -->
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Téléphone *</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" required
                       class="w-full bg-gray-800 border border-gray-600 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-primary">
            </div>
            
            <!-- Email -->
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}"
                       class="w-full bg-gray-800 border border-gray-600 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-primary">
            </div>
        </div>
        
        <div class="bg-dark-100 rounded-xl border border-gray-700/50 p-6 space-y-6">
            <h3 class="text-lg font-semibold text-white">
                <i class="fa-solid fa-shield mr-2 text-warning"></i>
                Permissions
            </h3>
            
            <!-- Premium -->
            <div>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_premium" value="1" {{ old('is_premium', $user->is_premium) ? 'checked' : '' }}
                           class="w-5 h-5 text-yellow-500 border-gray-600 rounded focus:ring-yellow-500 bg-gray-800">
                    <span class="text-gray-300">
                        <i class="fa-solid fa-crown text-yellow-400 mr-1"></i>
                        Compte Premium
                    </span>
                </label>
            </div>
            
            <!-- Admin -->
            <div>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_admin" value="1" {{ old('is_admin', $user->is_admin) ? 'checked' : '' }}
                           class="w-5 h-5 text-primary border-gray-600 rounded focus:ring-primary bg-gray-800">
                    <span class="text-gray-300">
                        <i class="fa-solid fa-user-tie text-primary mr-1"></i>
                        Administrateur
                    </span>
                </label>
            </div>
            
            <!-- Super Admin -->
            @if(auth()->user()->is_super_admin)
                <div>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="is_super_admin" value="1" {{ old('is_super_admin', $user->is_super_admin) ? 'checked' : '' }}
                               class="w-5 h-5 text-danger border-gray-600 rounded focus:ring-danger bg-gray-800">
                        <span class="text-gray-300">
                            <i class="fa-solid fa-shield text-danger mr-1"></i>
                            Super Administrateur (accès complet)
                        </span>
                    </label>
                    <p class="text-xs text-gray-500 mt-1 ml-8">⚠️ Donne accès au panel d'administration complet</p>
                </div>
            @endif
        </div>
        
        <!-- Info lecture seule -->
        <div class="bg-dark-100 rounded-xl border border-gray-700/50 p-6">
            <h3 class="text-lg font-semibold text-white mb-4">
                <i class="fa-solid fa-info-circle mr-2 text-gray-400"></i>
                Informations système
            </h3>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-gray-400">ID</span>
                    <p class="text-white font-medium">#{{ $user->id }}</p>
                </div>
                <div>
                    <span class="text-gray-400">Code parrainage</span>
                    <p class="text-primary font-medium">{{ $user->referral_code }}</p>
                </div>
                <div>
                    <span class="text-gray-400">Inscrit le</span>
                    <p class="text-white">{{ $user->created_at->format('d/m/Y H:i') }}</p>
                </div>
                <div>
                    <span class="text-gray-400">Dernière connexion</span>
                    <p class="text-white">{{ $user->last_login_at?->format('d/m/Y H:i') ?? 'Jamais' }}</p>
                </div>
            </div>
        </div>
        
        <!-- Actions -->
        <div class="flex gap-4">
            <button type="submit" class="flex-1 bg-primary hover:bg-primary/80 text-white font-semibold rounded-lg px-6 py-3 transition">
                <i class="fa-solid fa-save mr-2"></i>
                Enregistrer les modifications
            </button>
            <a href="{{ route('admin.users.show', $user) }}" class="bg-gray-700 hover:bg-gray-600 text-white rounded-lg px-6 py-3 transition">
                Annuler
            </a>
        </div>
    </form>
</div>
@endsection

