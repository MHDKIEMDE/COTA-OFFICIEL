@extends('admin.layouts.app')

@section('title', 'Feedback #' . $feedback->id)
@section('page-title', 'Feedback #' . $feedback->id)

@section('content')
<div class="space-y-6 max-w-4xl">

    @if(session('success'))
        <div class="alert-brand alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('admin.feedbacks.index') }}" style="font-size:13px;color:var(--dim)">
        <i class="fa-solid fa-arrow-left mr-2"></i> Retour à la liste
    </a>

    {{-- ── Détail ───────────────────────────────────────────────────────────── --}}
    <div class="card space-y-4">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 style="color:var(--ink);font-weight:700;font-size:18px">{{ $feedback->subject }}</h2>
                <p style="color:var(--dim);font-size:13px;margin-top:4px">
                    Par <span style="color:var(--ink-2)">{{ $feedback->user->name ?? 'Anonyme' }}</span>
                    &bull; {{ $feedback->created_at->format('d/m/Y à H:i') }}
                </p>
            </div>
            <div class="flex gap-2 shrink-0">
                @php
                    $catBadge    = ['bug' => 'badge-loss', 'suggestion' => 'badge-accent', 'question' => 'badge-pending', 'complaint' => 'badge-pending'];
                    $statusBadge = ['open' => 'badge-pending', 'in_progress' => 'badge-accent', 'resolved' => 'badge-win', 'closed' => ''];
                    $statusLabel = ['open' => 'Ouvert', 'in_progress' => 'En cours', 'resolved' => 'Résolu', 'closed' => 'Fermé'];
                @endphp
                <span class="{{ $catBadge[$feedback->category] ?? 'badge-pending' }}">{{ ucfirst($feedback->category) }}</span>
                <span class="{{ $statusBadge[$feedback->status] ?? '' }}"
                      @if(!($statusBadge[$feedback->status] ?? '')) style="font-size:11px;color:var(--dim)" @endif>
                    {{ $statusLabel[$feedback->status] ?? $feedback->status }}
                </span>
            </div>
        </div>

        <div class="p-4 rounded-lg" style="background:var(--bg-3);border:1px solid var(--line)">
            <p style="color:var(--ink-2);white-space:pre-wrap;font-size:14px">{{ $feedback->message }}</p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @if($feedback->app_version)
                <div>
                    <p class="text-xs mb-1" style="color:var(--dim);text-transform:uppercase;letter-spacing:.06em">Version app</p>
                    <p style="color:var(--ink-2);font-size:13px">{{ $feedback->app_version }}</p>
                </div>
            @endif
            @if($feedback->device_info)
                <div>
                    <p class="text-xs mb-1" style="color:var(--dim);text-transform:uppercase;letter-spacing:.06em">Appareil</p>
                    <p style="color:var(--ink-2);font-size:13px">{{ $feedback->device_info }}</p>
                </div>
            @endif
            @if($feedback->prediction)
                <div>
                    <p class="text-xs mb-1" style="color:var(--dim);text-transform:uppercase;letter-spacing:.06em">Pronostic lié</p>
                    <p style="color:var(--accent);font-size:13px">{{ $feedback->prediction->home_team }} vs {{ $feedback->prediction->away_team }}</p>
                </div>
            @endif
            @if($feedback->contest_reason)
                <div class="col-span-2">
                    <p class="text-xs mb-1" style="color:var(--dim);text-transform:uppercase;letter-spacing:.06em">Raison contestation</p>
                    <p style="color:var(--ink-2);font-size:13px">{{ $feedback->contest_reason }}</p>
                </div>
            @endif
        </div>

        @if($feedback->screenshot_url)
            <div>
                <p class="text-xs mb-1" style="color:var(--dim)">Capture d'écran</p>
                <a href="{{ $feedback->screenshot_url }}" target="_blank" style="color:var(--accent);font-size:13px">
                    <i class="fa-solid fa-image mr-1"></i> Voir la capture
                </a>
            </div>
        @endif
    </div>

    {{-- ── Réponse admin existante ─────────────────────────────────────────── --}}
    @if($feedback->admin_response)
    <div class="card" style="border-color:rgba(61,220,145,.3)">
        <p class="tag-mono mb-3" style="color:var(--win)"><i class="fa-solid fa-reply mr-2"></i>Réponse admin</p>
        <p style="color:var(--ink-2);white-space:pre-wrap;font-size:14px">{{ $feedback->admin_response }}</p>
        @if($feedback->resolved_at)
            <p style="font-size:12px;color:var(--dim);margin-top:12px">Résolu le {{ $feedback->resolved_at->format('d/m/Y à H:i') }}</p>
        @endif
    </div>
    @endif

    {{-- ── Formulaire réponse ───────────────────────────────────────────────── --}}
    @if(!in_array($feedback->status, ['closed']))
    <div class="card">
        <p class="tag-mono mb-4"><i class="fa-solid fa-paper-plane mr-2" style="color:var(--accent)"></i>{{ $feedback->admin_response ? 'Modifier la réponse' : 'Répondre' }}</p>
        <form method="POST" action="{{ route('admin.feedbacks.respond', $feedback) }}" class="space-y-4">
            @csrf @method('PATCH')
            <textarea name="admin_response" rows="5" required
                      class="input-brand w-full" style="height:auto;padding:12px;resize:vertical"
                      placeholder="Votre réponse…">{{ old('admin_response', $feedback->admin_response) }}</textarea>
            <div class="flex items-center gap-3">
                <select name="status" class="input-brand" style="height:40px;padding:0 12px">
                    <option value="in_progress" {{ $feedback->status === 'in_progress' ? 'selected' : '' }}>Marquer En cours</option>
                    <option value="resolved"    {{ $feedback->status === 'resolved'    ? 'selected' : '' }}>Marquer Résolu</option>
                    <option value="closed">Fermer</option>
                </select>
                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-save mr-1"></i> Enregistrer
                </button>
            </div>
            @error('admin_response')
                <p style="font-size:12px;color:var(--loss)">{{ $message }}</p>
            @enderror
        </form>
    </div>
    @endif

    {{-- ── Changement statut rapide ────────────────────────────────────────── --}}
    <div class="flex items-center gap-3 flex-wrap">
        <span style="color:var(--dim);font-size:13px">Changer statut :</span>
        @foreach(['open' => 'Ouvert', 'in_progress' => 'En cours', 'resolved' => 'Résolu', 'closed' => 'Fermé'] as $s => $label)
            @if($feedback->status !== $s)
            <form method="POST" action="{{ route('admin.feedbacks.status', $feedback) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="{{ $s }}">
                <button class="btn-secondary btn-sm">{{ $label }}</button>
            </form>
            @endif
        @endforeach
    </div>

</div>
@endsection
