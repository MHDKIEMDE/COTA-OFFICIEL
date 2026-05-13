@extends('admin.layouts.app')

@section('title', 'Feedback #' . $feedback->id)
@section('page-title', 'Feedback #' . $feedback->id)

@section('content')
<div class="space-y-6 max-w-4xl">

    {{-- Flash --}}
    @if(session('success'))
        <div class="bg-success/20 border border-success/40 text-success rounded-lg px-4 py-3">
            {{ session('success') }}
        </div>
    @endif

    {{-- Retour --}}
    <a href="{{ route('admin.feedbacks.index') }}" class="inline-flex items-center gap-2 text-gray-400 hover:text-white transition text-sm">
        <i class="fa-solid fa-arrow-left"></i> Retour à la liste
    </a>

    {{-- Détail du feedback --}}
    <div class="bg-dark-100 rounded-xl border border-gray-700/50 p-6 space-y-4">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-white font-semibold text-lg">{{ $feedback->subject }}</h2>
                <p class="text-gray-400 text-sm mt-1">
                    Par <span class="text-white">{{ $feedback->user->name ?? 'Anonyme' }}</span>
                    &bull; {{ $feedback->created_at->format('d/m/Y à H:i') }}
                </p>
            </div>

            <div class="flex gap-2 flex-shrink-0">
                @php
                    $catColors = [
                        'bug'        => 'bg-danger/20 text-danger',
                        'suggestion' => 'bg-primary/20 text-primary',
                        'question'   => 'bg-blue-500/20 text-blue-400',
                        'complaint'  => 'bg-orange-500/20 text-orange-400',
                        'other'      => 'bg-gray-500/20 text-gray-400',
                    ];
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
                <span class="px-3 py-1 rounded-full text-xs font-medium {{ $catColors[$feedback->category] ?? '' }}">
                    {{ ucfirst($feedback->category) }}
                </span>
                <span class="px-3 py-1 rounded-full text-xs font-medium {{ $statusColors[$feedback->status] ?? '' }}">
                    {{ $statusLabels[$feedback->status] ?? $feedback->status }}
                </span>
            </div>
        </div>

        <div class="bg-gray-800/50 rounded-lg p-4">
            <p class="text-gray-200 whitespace-pre-wrap">{{ $feedback->message }}</p>
        </div>

        {{-- Infos contextuelles --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            @if($feedback->app_version)
            <div>
                <p class="text-gray-500">Version app</p>
                <p class="text-gray-200">{{ $feedback->app_version }}</p>
            </div>
            @endif
            @if($feedback->device_info)
            <div>
                <p class="text-gray-500">Appareil</p>
                <p class="text-gray-200">{{ $feedback->device_info }}</p>
            </div>
            @endif
            @if($feedback->prediction)
            <div>
                <p class="text-gray-500">Pronostic lié</p>
                <p class="text-primary">{{ $feedback->prediction->home_team }} vs {{ $feedback->prediction->away_team }}</p>
            </div>
            @endif
            @if($feedback->contest_reason)
            <div class="col-span-2">
                <p class="text-gray-500">Raison contestation</p>
                <p class="text-gray-200">{{ $feedback->contest_reason }}</p>
            </div>
            @endif
        </div>

        @if($feedback->screenshot_url)
        <div>
            <p class="text-gray-500 text-sm mb-2">Capture d'écran</p>
            <a href="{{ $feedback->screenshot_url }}" target="_blank" class="text-primary hover:underline text-sm">
                <i class="fa-solid fa-image mr-1"></i> Voir la capture
            </a>
        </div>
        @endif
    </div>

    {{-- Réponse admin --}}
    @if($feedback->admin_response)
    <div class="bg-dark-100 rounded-xl border border-success/30 p-6">
        <h3 class="text-success font-semibold mb-3">
            <i class="fa-solid fa-reply mr-2"></i>Réponse admin
        </h3>
        <p class="text-gray-200 whitespace-pre-wrap">{{ $feedback->admin_response }}</p>
        @if($feedback->resolved_at)
        <p class="text-gray-500 text-xs mt-3">Résolu le {{ $feedback->resolved_at->format('d/m/Y à H:i') }}</p>
        @endif
    </div>
    @endif

    {{-- Formulaire réponse --}}
    @if(!in_array($feedback->status, ['closed']))
    <div class="bg-dark-100 rounded-xl border border-gray-700/50 p-6">
        <h3 class="text-white font-semibold mb-4">
            <i class="fa-solid fa-paper-plane mr-2 text-primary"></i>
            {{ $feedback->admin_response ? 'Modifier la réponse' : 'Répondre' }}
        </h3>

        <form method="POST" action="{{ route('admin.feedbacks.respond', $feedback) }}" class="space-y-4">
            @csrf @method('PATCH')

            <textarea name="admin_response" rows="5" required
                      class="w-full bg-gray-800 border border-gray-600 rounded-lg px-4 py-3 text-white focus:ring-2 focus:ring-primary resize-none"
                      placeholder="Votre réponse...">{{ old('admin_response', $feedback->admin_response) }}</textarea>

            <div class="flex items-center gap-3">
                <select name="status" class="bg-gray-800 border border-gray-600 rounded-lg px-4 py-2 text-white focus:ring-2 focus:ring-primary">
                    <option value="in_progress" {{ $feedback->status === 'in_progress' ? 'selected' : '' }}>Marquer En cours</option>
                    <option value="resolved"    {{ $feedback->status === 'resolved'    ? 'selected' : '' }}>Marquer Résolu</option>
                    <option value="closed">Fermer</option>
                </select>

                <button type="submit" class="bg-primary hover:bg-primary/80 text-white rounded-lg px-6 py-2 transition font-medium">
                    <i class="fa-solid fa-save mr-1"></i> Enregistrer
                </button>
            </div>

            @error('admin_response')
                <p class="text-danger text-sm">{{ $message }}</p>
            @enderror
        </form>
    </div>
    @endif

    {{-- Changer statut rapide --}}
    <div class="flex items-center gap-3 flex-wrap">
        <span class="text-gray-400 text-sm">Changer statut :</span>
        @foreach(['open' => 'Ouvert', 'in_progress' => 'En cours', 'resolved' => 'Résolu', 'closed' => 'Fermé'] as $s => $label)
            @if($feedback->status !== $s)
            <form method="POST" action="{{ route('admin.feedbacks.status', $feedback) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="{{ $s }}">
                <button class="px-3 py-1.5 rounded-lg text-xs font-medium bg-gray-700 hover:bg-gray-600 text-gray-200 transition">
                    {{ $label }}
                </button>
            </form>
            @endif
        @endforeach
    </div>

</div>
@endsection
