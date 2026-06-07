@extends('admin.layouts.app')

@section('title', 'Feedbacks')
@section('page-title', 'Gestion des Feedbacks')

@section('content')
<div class="space-y-6">

    {{-- ── Stats ───────────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        @foreach([
            ['Total',     $stats['total'],       'var(--ink)'],
            ['Ouverts',   $stats['open'],         '#f5a623'],
            ['En cours',  $stats['in_progress'],  'var(--accent)'],
            ['Résolus',   $stats['resolved'],     'var(--win)'],
            ['Fermés',    $stats['closed'],       'var(--dim)'],
            ['Bugs',      $stats['bugs'],         'var(--loss)'],
        ] as [$label, $val, $color])
            <div class="card text-center">
                <p class="text-2xl font-bold" style="color:{{ $color }};font-family:Archivo,sans-serif">{{ $val }}</p>
                <p class="text-sm mt-1" style="color:var(--dim)">{{ $label }}</p>
            </div>
        @endforeach
    </div>

    {{-- ── Filtres ──────────────────────────────────────────────────────────── --}}
    <div class="card">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
            <select name="status" class="input-brand" style="height:40px;padding:0 12px">
                <option value="">Tous les statuts</option>
                <option value="open"        {{ request('status') === 'open'        ? 'selected' : '' }}>Ouvert</option>
                <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>En cours</option>
                <option value="resolved"    {{ request('status') === 'resolved'    ? 'selected' : '' }}>Résolu</option>
                <option value="closed"      {{ request('status') === 'closed'      ? 'selected' : '' }}>Fermé</option>
            </select>
            <select name="category" class="input-brand" style="height:40px;padding:0 12px">
                <option value="">Toutes les catégories</option>
                <option value="bug"        {{ request('category') === 'bug'        ? 'selected' : '' }}>Bug</option>
                <option value="suggestion" {{ request('category') === 'suggestion' ? 'selected' : '' }}>Suggestion</option>
                <option value="question"   {{ request('category') === 'question'   ? 'selected' : '' }}>Question</option>
                <option value="complaint"  {{ request('category') === 'complaint'  ? 'selected' : '' }}>Réclamation</option>
                <option value="other"      {{ request('category') === 'other'      ? 'selected' : '' }}>Autre</option>
            </select>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher…" class="input-brand" style="height:40px">
            <div class="flex gap-2">
                <button type="submit" class="btn-primary btn-sm flex-1">
                    <i class="fa-solid fa-search mr-1"></i> Filtrer
                </button>
                <a href="{{ route('admin.feedbacks.index') }}" class="btn-secondary btn-sm px-3" title="Réinitialiser">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            </div>
        </form>
    </div>

    {{-- ── Flash ────────────────────────────────────────────────────────────── --}}
    @if(session('success'))
        <div class="alert-brand alert-success">{{ session('success') }}</div>
    @endif

    {{-- ── Table ────────────────────────────────────────────────────────────── --}}
    <div class="card" style="padding:0;overflow:hidden">
        <div class="overflow-x-auto">
            <table class="table-brand w-full">
                <thead>
                    <tr>
                        <th class="text-left">#</th>
                        <th class="text-left">Utilisateur</th>
                        <th class="text-left">Sujet</th>
                        <th class="text-left">Catégorie</th>
                        <th class="text-left">Statut</th>
                        <th class="text-left">Date</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($feedbacks as $feedback)
                    <tr>
                        <td style="color:var(--dim-2);font-family:JetBrains Mono,monospace;font-size:12px">{{ $feedback->id }}</td>
                        <td style="color:var(--ink);font-weight:600;font-size:14px">{{ $feedback->user->name ?? 'Anonyme' }}</td>
                        <td style="max-width:260px">
                            <p style="color:var(--ink-2);font-size:13px" class="truncate">{{ $feedback->subject }}</p>
                            <p style="color:var(--dim);font-size:12px" class="truncate mt-0.5">{{ Str::limit($feedback->message, 60) }}</p>
                        </td>
                        <td>
                            @php
                                $catBadge = ['bug' => 'badge-loss', 'suggestion' => 'badge-accent', 'question' => 'badge-pending', 'complaint' => 'badge-pending', 'other' => ''];
                            @endphp
                            <span class="{{ $catBadge[$feedback->category] ?? '' }}"
                                  @if(!($catBadge[$feedback->category] ?? '')) style="font-size:11px;color:var(--dim)" @endif>
                                {{ ucfirst($feedback->category) }}
                            </span>
                        </td>
                        <td>
                            @php
                                $statusBadge = ['open' => 'badge-pending', 'in_progress' => 'badge-accent', 'resolved' => 'badge-win', 'closed' => ''];
                                $statusLabel = ['open' => 'Ouvert', 'in_progress' => 'En cours', 'resolved' => 'Résolu', 'closed' => 'Fermé'];
                            @endphp
                            <span class="{{ $statusBadge[$feedback->status] ?? '' }}"
                                  @if(!($statusBadge[$feedback->status] ?? '')) style="font-size:11px;color:var(--dim)" @endif>
                                {{ $statusLabel[$feedback->status] ?? $feedback->status }}
                            </span>
                        </td>
                        <td style="color:var(--dim);font-size:12px">{{ $feedback->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.feedbacks.show', $feedback) }}" title="Voir / Répondre"
                                   style="padding:6px 10px;background:rgba(232,255,54,.08);border:1px solid rgba(232,255,54,.2);border-radius:8px;color:var(--accent)">
                                    <i class="fa-solid fa-eye" style="font-size:12px"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.feedbacks.destroy', $feedback) }}"
                                      onsubmit="return confirm('Supprimer ce feedback ?')">
                                    @csrf @method('DELETE')
                                    <button style="padding:6px 10px;background:rgba(255,91,58,.12);border:1px solid rgba(255,91,58,.25);border-radius:8px;color:var(--loss);cursor:pointer" title="Supprimer">
                                        <i class="fa-solid fa-trash" style="font-size:12px"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="padding:48px;text-align:center;color:var(--dim)">
                            <i class="fa-solid fa-comment-slash" style="font-size:28px;display:block;margin-bottom:12px;color:var(--dim-2)"></i>
                            Aucun feedback trouvé
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($feedbacks->hasPages())
        <div style="padding:16px 24px;border-top:1px solid var(--line)">
            {{ $feedbacks->links('vendor.pagination.tailwind') }}
        </div>
        @endif
    </div>

</div>
@endsection
