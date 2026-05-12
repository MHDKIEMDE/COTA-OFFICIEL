@extends('admin.layouts.app')

@section('title', 'Utilisateurs')
@section('page-title', 'Gestion des Utilisateurs')

@section('content')
<div class="space-y-6">
    <!-- Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-dark-100 rounded-lg border border-gray-700/50 p-4 text-center">
            <p class="text-2xl font-bold text-white">{{ number_format($stats['total']) }}</p>
            <p class="text-sm text-gray-400">Total</p>
        </div>
        <div class="bg-dark-100 rounded-lg border border-gray-700/50 p-4 text-center">
            <p class="text-2xl font-bold text-yellow-400">{{ number_format($stats['premium']) }}</p>
            <p class="text-sm text-gray-400">Premium</p>
        </div>
        <div class="bg-dark-100 rounded-lg border border-gray-700/50 p-4 text-center">
            <p class="text-2xl font-bold text-success">{{ $stats['new_today'] }}</p>
            <p class="text-sm text-gray-400">Nouveaux aujourd'hui</p>
        </div>
        <div class="bg-dark-100 rounded-lg border border-gray-700/50 p-4 text-center">
            <p class="text-2xl font-bold text-primary">{{ number_format($stats['active_7_days']) }}</p>
            <p class="text-sm text-gray-400">Actifs (7j)</p>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="bg-dark-100 rounded-xl border border-gray-700/50 p-6">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
            <div>
                <select name="status" class="w-full bg-gray-800 border border-gray-600 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-primary">
                    <option value="">Tous les statuts</option>
                    <option value="premium" {{ request('status') == 'premium' ? 'selected' : '' }}>Premium</option>
                    <option value="free" {{ request('status') == 'free' ? 'selected' : '' }}>Gratuit</option>
                </select>
            </div>
            <div>
                <input type="date" name="date_from" value="{{ request('date_from') }}" 
                       class="w-full bg-gray-800 border border-gray-600 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-primary"
                       placeholder="Date début">
            </div>
            <div>
                <input type="date" name="date_to" value="{{ request('date_to') }}" 
                       class="w-full bg-gray-800 border border-gray-600 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-primary"
                       placeholder="Date fin">
            </div>
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher..." 
                       class="w-full bg-gray-800 border border-gray-600 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-primary">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-primary hover:bg-primary/80 text-white rounded-lg px-4 py-2 transition">
                    <i class="fa-solid fa-search mr-1"></i> Filtrer
                </button>
                <a href="{{ route('admin.users.export') }}" class="bg-success hover:bg-success/80 text-white rounded-lg px-4 py-2 transition" title="Exporter CSV">
                    <i class="fa-solid fa-download"></i>
                </a>
            </div>
        </form>
    </div>
    
    <!-- Table -->
    <div class="bg-dark-100 rounded-xl border border-gray-700/50 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[700px]">
                <thead class="bg-gray-800/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Utilisateur</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Contact</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase">Statut</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase">Premium expire</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase">Code Parrainage</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase">Filleuls</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase">Inscrit le</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700/50">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-800/30 transition">
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-r from-primary to-secondary flex items-center justify-center text-white font-medium">
                                        {{ substr($user->name ?? 'U', 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="text-white font-medium">{{ $user->name ?? 'Sans nom' }}</div>
                                        <div class="text-gray-500 text-xs">#{{ $user->id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="text-gray-300 text-sm">{{ $user->phone }}</div>
                                @if($user->email)
                                    <div class="text-gray-500 text-xs">{{ $user->email }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-center">
                                @if($user->is_premium)
                                    <span class="px-2 py-1 rounded text-xs bg-yellow-500/20 text-yellow-400">
                                        <i class="fa-solid fa-crown mr-1"></i>Premium
                                    </span>
                                @else
                                    <span class="px-2 py-1 rounded text-xs bg-gray-500/20 text-gray-400">Gratuit</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-center text-gray-300 text-sm">
                                @if($user->is_premium && $user->premium_expires_at)
                                    {{ $user->premium_expires_at->format('d/m/Y') }}
                                @elseif($user->is_premium)
                                    <span class="text-yellow-400">∞ À vie</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-4 text-center">
                                <code class="px-2 py-1 bg-gray-800 rounded text-primary text-sm">{{ $user->referral_code }}</code>
                            </td>
                            <td class="px-4 py-4 text-center text-white font-medium">{{ $user->referrals_count ?? 0 }}</td>
                            <td class="px-4 py-4 text-center text-gray-400 text-sm">{{ $user->created_at->format('d/m/Y') }}</td>
                            <td class="px-4 py-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.users.show', $user) }}" 
                                       class="p-2 text-primary hover:bg-primary/20 rounded transition" title="Voir">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.users.edit', $user) }}" 
                                       class="p-2 text-warning hover:bg-warning/20 rounded transition" title="Modifier">
                                        <i class="fa-solid fa-edit"></i>
                                    </a>
                                    @if(!$user->is_super_admin)
                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Supprimer cet utilisateur ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 text-danger hover:bg-danger/20 rounded transition" title="Supprimer">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-gray-400">
                                Aucun utilisateur trouvé
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-gray-700/50">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

