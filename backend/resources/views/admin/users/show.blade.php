@extends('admin.layouts.app')

@section('title', 'Détails Utilisateur')
@section('page-title', 'Détails Utilisateur')

@section('content')
<div class="space-y-6">
    <!-- Header Card -->
    <div class="bg-dark-100 rounded-xl border border-gray-700/50 p-6">
        <div class="flex flex-col md:flex-row gap-6 items-start">
            <!-- Avatar -->
            <div class="w-24 h-24 rounded-full bg-gradient-to-r from-primary to-secondary flex items-center justify-center text-white text-3xl font-bold">
                {{ substr($user->name ?? 'U', 0, 1) }}
            </div>
            
            <!-- Info -->
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                    <h2 class="text-2xl font-bold text-white">{{ $user->name ?? 'Sans nom' }}</h2>
                    @if($user->is_premium)
                        <span class="px-3 py-1 rounded-full bg-yellow-500/20 text-yellow-400 text-sm font-medium">
                            <i class="fa-solid fa-crown mr-1"></i>Premium
                        </span>
                    @endif
                    @if($user->is_super_admin)
                        <span class="px-3 py-1 rounded-full bg-danger/20 text-danger text-sm font-medium">
                            <i class="fa-solid fa-shield mr-1"></i>Super Admin
                        </span>
                    @endif
                </div>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <span class="text-gray-400">Téléphone</span>
                        <p class="text-white font-medium">{{ $user->phone }}</p>
                    </div>
                    <div>
                        <span class="text-gray-400">Email</span>
                        <p class="text-white font-medium">{{ $user->email ?? '-' }}</p>
                    </div>
                    <div>
                        <span class="text-gray-400">Inscrit le</span>
                        <p class="text-white font-medium">{{ $user->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        <span class="text-gray-400">Dernière connexion</span>
                        <p class="text-white font-medium">{{ $user->last_login_at?->format('d/m/Y H:i') ?? '-' }}</p>
                    </div>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="flex gap-2">
                <a href="{{ route('admin.users.edit', $user) }}" class="bg-primary hover:bg-primary/80 text-white px-4 py-2 rounded-lg transition">
                    <i class="fa-solid fa-edit mr-1"></i> Modifier
                </a>
            </div>
        </div>
    </div>
    
    <!-- Stats & Premium -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Stats -->
        <div class="bg-dark-100 rounded-xl border border-gray-700/50 p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Statistiques</h3>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-400">Abonnements</span>
                    <span class="text-white font-medium">{{ $user->subscriptions_count ?? 0 }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Parrainages</span>
                    <span class="text-white font-medium">{{ $user->referrals_count ?? 0 }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Feedbacks</span>
                    <span class="text-white font-medium">{{ $user->feedbacks_count ?? 0 }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Affiliations</span>
                    <span class="text-white font-medium">{{ $user->affiliation_bonus_count ?? 0 }}</span>
                </div>
            </div>
        </div>
        
        <!-- Premium Management -->
        <div class="bg-dark-100 rounded-xl border border-gray-700/50 p-6 md:col-span-2">
            <h3 class="text-lg font-semibold text-white mb-4">
                <i class="fa-solid fa-crown text-yellow-400 mr-2"></i>
                Gestion Premium
            </h3>
            
            <div class="mb-4">
                @if($user->is_premium)
                    <div class="p-4 bg-yellow-500/10 border border-yellow-500/30 rounded-lg">
                        <div class="flex items-center gap-2 text-yellow-400 font-medium mb-2">
                            <i class="fa-solid fa-check-circle"></i>
                            Abonnement Premium actif
                        </div>
                        @if($user->premium_expires_at)
                            <p class="text-sm text-gray-300">
                                Expire le <strong>{{ $user->premium_expires_at->format('d/m/Y H:i') }}</strong>
                                ({{ $user->premium_expires_at->diffForHumans() }})
                            </p>
                        @else
                            <p class="text-sm text-yellow-300">Premium à vie</p>
                        @endif
                        <p class="text-xs text-gray-400 mt-1">Source: {{ $user->premium_source ?? 'Non spécifié' }}</p>
                    </div>
                @else
                    <div class="p-4 bg-gray-700/30 border border-gray-600 rounded-lg">
                        <p class="text-gray-400">Cet utilisateur n'a pas d'abonnement Premium actif.</p>
                    </div>
                @endif
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Ajouter des jours -->
                <form action="{{ route('admin.users.addPremium', $user) }}" method="POST" class="flex gap-2">
                    @csrf
                    <input type="number" name="days" min="1" max="365" value="7" 
                           class="flex-1 bg-gray-800 border border-gray-600 rounded-lg px-3 py-2 text-white text-sm">
                    <button type="submit" class="bg-success hover:bg-success/80 text-white px-4 py-2 rounded-lg text-sm transition">
                        + Jours
                    </button>
                </form>
                
                <!-- Premium à vie -->
                <form action="{{ route('admin.users.lifetimePremium', $user) }}" method="POST"
                      onsubmit="return confirm('Accorder le premium à vie ?')">
                    @csrf
                    <button type="submit" class="w-full bg-yellow-500 hover:bg-yellow-600 text-black px-4 py-2 rounded-lg text-sm font-medium transition">
                        <i class="fa-solid fa-infinity mr-1"></i> Premium à vie
                    </button>
                </form>
                
                <!-- Révoquer -->
                @if($user->is_premium)
                    <form action="{{ route('admin.users.revokePremium', $user) }}" method="POST"
                          onsubmit="return confirm('Révoquer le premium ?')">
                        @csrf
                        <button type="submit" class="w-full bg-danger hover:bg-danger/80 text-white px-4 py-2 rounded-lg text-sm transition">
                            <i class="fa-solid fa-ban mr-1"></i> Révoquer
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Parrainage -->
    <div class="bg-dark-100 rounded-xl border border-gray-700/50 p-6">
        <h3 class="text-lg font-semibold text-white mb-4">
            <i class="fa-solid fa-gift text-primary mr-2"></i>
            Parrainage
        </h3>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="p-4 bg-gray-800/50 rounded-lg text-center">
                <p class="text-gray-400 text-sm">Code parrain</p>
                <code class="text-xl font-bold text-primary">{{ $user->referral_code }}</code>
            </div>
            <div class="p-4 bg-gray-800/50 rounded-lg text-center">
                <p class="text-gray-400 text-sm">Filleuls</p>
                <p class="text-xl font-bold text-white">{{ $user->referral_count ?? 0 }}</p>
            </div>
            <div class="p-4 bg-gray-800/50 rounded-lg text-center">
                <p class="text-gray-400 text-sm">Jours gagnés</p>
                <p class="text-xl font-bold text-success">{{ $user->referral_days_earned ?? 0 }}</p>
            </div>
            <div class="p-4 bg-gray-800/50 rounded-lg text-center">
                <p class="text-gray-400 text-sm">Parrainé par</p>
                <p class="text-xl font-bold text-white">
                    @if($user->referred_by)
                        <a href="{{ route('admin.users.show', $user->referred_by) }}" class="text-primary hover:underline">
                            #{{ $user->referred_by }}
                        </a>
                    @else
                        -
                    @endif
                </p>
            </div>
        </div>
        
        @if($user->referrals->count() > 0)
            <h4 class="text-sm font-medium text-gray-400 mb-3">Derniers filleuls</h4>
            <div class="space-y-2">
                @foreach($user->referrals as $referral)
                    <div class="flex items-center justify-between p-3 bg-gray-800/30 rounded-lg">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-gray-700 flex items-center justify-center text-white text-sm">
                                {{ substr($referral->referredUser->name ?? 'U', 0, 1) }}
                            </div>
                            <div>
                                <p class="text-white text-sm">{{ $referral->referredUser->name ?? 'Sans nom' }}</p>
                                <p class="text-gray-500 text-xs">{{ $referral->created_at->format('d/m/Y') }}</p>
                            </div>
                        </div>
                        <span class="text-success text-sm">+{{ $referral->bonus_days ?? 0 }}j</span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
    
    <!-- Affiliations -->
    @if($user->affiliationBonus->count() > 0)
        <div class="bg-dark-100 rounded-xl border border-gray-700/50 p-6">
            <h3 class="text-lg font-semibold text-white mb-4">
                <i class="fa-solid fa-handshake text-secondary mr-2"></i>
                Affiliations
            </h3>
            <div class="space-y-2">
                @foreach($user->affiliationBonus as $bonus)
                    <div class="flex items-center justify-between p-3 bg-gray-800/30 rounded-lg">
                        <div>
                            <span class="text-white font-medium">{{ ucfirst($bonus->bookmaker) }}</span>
                            <span class="text-gray-500 text-sm ml-2">ID: {{ $bonus->player_id }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-success text-sm">+{{ $bonus->bonus_days }}j</span>
                            @if($bonus->is_verified)
                                <span class="px-2 py-1 rounded text-xs bg-success/20 text-success">Vérifié</span>
                            @else
                                <span class="px-2 py-1 rounded text-xs bg-warning/20 text-warning">En attente</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
    
    <!-- Back -->
    <div>
        <a href="{{ route('admin.users.index') }}" class="text-gray-400 hover:text-white transition">
            <i class="fa-solid fa-arrow-left mr-2"></i> Retour à la liste
        </a>
    </div>
</div>
@endsection

