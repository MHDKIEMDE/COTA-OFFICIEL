@extends('admin.layouts.app')

@section('title', 'Feedbacks')
@section('page-title', 'Gestion des Feedbacks')

@section('content')
<div class="space-y-6">

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="bg-dark-100 rounded-lg border border-gray-700/50 p-4 text-center">
            <p class="text-2xl font-bold text-white">{{ $stats['total'] }}</p>
            <p class="text-sm text-gray-400">Total</p>
        </div>
        <div class="bg-dark-100 rounded-lg border border-gray-700/50 p-4 text-center">
            <p class="text-2xl font-bold text-warning">{{ $stats['open'] }}</p>
            <p class="text-sm text-gray-400">Ouverts</p>
        </div>
        <div class="bg-dark-100 rounded-lg border border-gray-700/50 p-4 text-center">
            <p class="text-2xl font-bold text-primary">{{ $stats['in_progress'] }}</p>
            <p class="text-sm text-gray-400">En cours</p>
        </div>
        <div class="bg-dark-100 rounded-lg border border-gray-700/50 p-4 text-center">
            <p class="text-2xl font-bold text-success">{{ $stats['resolved'] }}</p>
            <p class="text-sm text-gray-400">Résolus</p>
        </div>
        <div class="bg-dark-100 rounded-lg border border-gray-700/50 p-4 text-center">
            <p class="text-2xl font-bold text-gray-400">{{ $stats['closed'] }}</p>
            <p class="text-sm text-gray-400">Fermés</p>
        </div>
        <div class="bg-dark-100 rounded-lg border border-gray-700/50 p-4 text-center">
            <p class="text-2xl font-bold text-danger">{{ $stats['bugs'] }}</p>
            <p class="text-sm text-gray-400">Bugs</p>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="bg-dark-100 rounded-xl border border-gray-700/50 p-5">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
            <select name="status" class="bg-gray-800 border border-gray-600 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-primary">
                <option value="">Tous les statuts</option>
                <option value="open"        {{ request('status') === 'open'        ? 'selected' : '' }}>Ouvert</option>
                <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>En cours</option>
                <option value="resolved"    {{ request('status') === 'resolved'    ? 'selected' : '' }}>Résolu</option>
                <option value="closed"      {{ request('status') === 'closed'      ? 'selected' : '' }}>Fermé</option>
            </select>

            <select name="category" class="bg-gray-800 border border-gray-600 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-primary">
                <option value="">Toutes les catégories</option>
                <option value="bug"        {{ request('category') === 'bug'        ? 'selected' : '' }}>Bug</option>
                <option value="suggestion" {{ request('category') === 'suggestion' ? 'selected' : '' }}>Suggestion</option>
                <option value="question"   {{ request('category') === 'question'   ? 'selected' : '' }}>Question</option>
                <option value="complaint"  {{ request('category') === 'complaint'  ? 'selected' : '' }}>Réclamation</option>
                <option value="other"      {{ request('category') === 'other'      ? 'selected' : '' }}>Autre</option>
            </select>

            <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher..."
                   class="bg-gray-800 border border-gray-600 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-primary">

            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-primary hover:bg-primary/80 text-white rounded-lg px-4 py-2 transition">
                    <i class="fa-solid fa-search mr-1"></i> Filtrer
                </button>
                <a href="{{ route('admin.feedbacks.index') }}" class="bg-gray-700 hover:bg-gray-600 text-white rounded-lg px-4 py-2 transition" title="Réinitialiser">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            </div>
        </form>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="bg-success/20 border border-success/40 text-success rounded-lg px-4 py-3">
            {{ session('success') }}
        </div>
    @endif

    {{-- Table --}}
    <div class="bg-dark-100 rounded-xl border border-gray-700/50 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-700/50 text-gray-400 text-xs uppercase tracking-wider">
                        <th class="px-6 py-4 text-left">#</th>
                        <th class="px-6 py-4 text-left">Utilisateur</th>
                        <th class="px-6 py-4 text-left">Sujet</th>
                        <th class="px-6 py-4 text-left">Catégorie</th>
                        <th class="px-6 py-4 text-left">Statut</th>
                        <th class="px-6 py-4 text-left">Date</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700/30">
                    @forelse($feedbacks as $feedback)
                    <tr class="hover:bg-gray-800/30 transition">
                        <td class="px-6 py-4 text-gray-500">{{ $feedback->id }}</td>

                        <td class="px-6 py-4">
                            <span class="text-white font-medium">{{ $feedback->user->name ?? 'Anonyme' }}</span>
                        </td>

                        <td class="px-6 py-4 max-w-xs">
                            <p class="text-gray-200 truncate">{{ $feedback->subject }}</p>
                            <p class="text-gray-500 text-xs truncate mt-0.5">{{ Str::limit($feedback->message, 60) }}</p>
                        </td>

                        <td class="px-6 py-4">
                            @php
                                $catColors = [
                                    'bug'        => 'bg-danger/20 text-danger',
                                    'suggestion' => 'bg-primary/20 text-primary',
                                    'question'   => 'bg-blue-500/20 text-blue-400',
                                    'complaint'  => 'bg-orange-500/20 text-orange-400',
                                    'other'      => 'bg-gray-500/20 text-gray-400',
                                ];
                            @endphp
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $catColors[$feedback->category] ?? 'bg-gray-500/20 text-gray-400' }}">
                                {{ ucfirst($feedback->category) }}
                            </span>
                        </td>

                        <td class="px-6 py-4">
                            @php
                                $statusColors = [
                                    'open'        => 'bg-warning/20 text-warning',
                                    'in_progress' => 'bg-primary/20 text-primary',
                                    'resolved'    => 'bg-success/20 text-success',
                                    'closed'      => 'bg-gray-500/20 text-gray-400',
                                ];
                                $statusLabels = [
                                    'open'        => 'Ouvert',
                                    'in_progress' => 'En cours',
                                    'resolved'    => 'Résolu',
                                    'closed'      => 'Fermé',
                                ];
                            @endphp
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $statusColors[$feedback->status] ?? '' }}">
                                {{ $statusLabels[$feedback->status] ?? $feedback->status }}
                            </span>
                        </td>

                        <td class="px-6 py-4 text-gray-400 text-xs">
                            {{ $feedback->created_at->format('d/m/Y H:i') }}
                        </td>

                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.feedbacks.show', $feedback) }}"
                                   class="p-2 rounded-lg bg-primary/20 text-primary hover:bg-primary/30 transition" title="Voir / Répondre">
                                    <i class="fa-solid fa-eye text-xs"></i>
                                </a>

                                <form method="POST" action="{{ route('admin.feedbacks.destroy', $feedback) }}"
                                      onsubmit="return confirm('Supprimer ce feedback ?')">
                                    @csrf @method('DELETE')
                                    <button class="p-2 rounded-lg bg-danger/20 text-danger hover:bg-danger/30 transition" title="Supprimer">
                                        <i class="fa-solid fa-trash text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <i class="fa-solid fa-comment-slash text-3xl mb-3 block"></i>
                            Aucun feedback trouvé
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($feedbacks->hasPages())
        <div class="px-6 py-4 border-t border-gray-700/50">
            {{ $feedbacks->links('vendor.pagination.tailwind') }}
        </div>
        @endif
    </div>
</div>
@endsection
