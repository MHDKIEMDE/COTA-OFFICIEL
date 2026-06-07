@extends('admin.layouts.app')

@section('title', 'Compétitions')
@section('page-title', 'Gestion des Compétitions')

@section('content')
<div class="space-y-6">

    {{-- ── Stats ───────────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach([
            ['Total', $stats['total'], 'var(--ink)', 'fa-trophy', 'rgba(232,255,54,.12)', 'rgba(232,255,54,.2)', 'var(--accent)'],
            ['Actives', $stats['active'], 'var(--win)', 'fa-check-circle', 'rgba(61,220,145,.12)', 'rgba(61,220,145,.2)', 'var(--win)'],
            ['En tendance', $stats['trending'], '#f97316', 'fa-fire', 'rgba(249,115,22,.12)', 'rgba(249,115,22,.2)', '#f97316'],
        ] as [$label, $val, $valColor, $icon, $bg, $border, $iconColor])
            <div class="card card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="tag-mono mb-1">{{ $label }}</p>
                        <p class="text-3xl font-bold" style="color:{{ $valColor }};font-family:Archivo,sans-serif">{{ $val }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center"
                         style="background:{{ $bg }};border:1px solid {{ $border }}">
                        <i class="fa-solid {{ $icon }} text-xl" style="color:{{ $iconColor }}"></i>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ── Actions Bar ──────────────────────────────────────────────────────── --}}
    <div class="card">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <form method="GET" class="flex flex-wrap items-center gap-3">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher…"
                       class="input-brand" style="height:40px;width:200px">
                <select name="trending" class="input-brand" style="height:40px;padding:0 12px">
                    <option value="">Tous</option>
                    <option value="1" {{ request('trending') === '1' ? 'selected' : '' }}>En tendance</option>
                    <option value="0" {{ request('trending') === '0' ? 'selected' : '' }}>Non tendance</option>
                </select>
                <select name="active" class="input-brand" style="height:40px;padding:0 12px">
                    <option value="">Toutes</option>
                    <option value="1" {{ request('active') === '1' ? 'selected' : '' }}>Actives</option>
                    <option value="0" {{ request('active') === '0' ? 'selected' : '' }}>Inactives</option>
                </select>
                <button type="submit" class="btn-primary btn-sm">
                    <i class="fa-solid fa-filter mr-2"></i>Filtrer
                </button>
                @if(request()->hasAny(['search', 'trending', 'active']))
                    <a href="{{ route('admin.competitions.index') }}" class="btn-secondary btn-sm">
                        <i class="fa-solid fa-times mr-2"></i>Réinitialiser
                    </a>
                @endif
            </form>
            <div class="flex items-center gap-3">
                <form action="{{ route('admin.competitions.clear-cache') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="btn-secondary btn-sm">
                        <i class="fa-solid fa-broom mr-2"></i>Vider Cache
                    </button>
                </form>
                <a href="{{ route('admin.competitions.create') }}" class="btn-primary btn-sm">
                    <i class="fa-solid fa-plus mr-2"></i>Ajouter
                </a>
            </div>
        </div>
    </div>

    {{-- ── Table ────────────────────────────────────────────────────────────── --}}
    <div class="card" style="padding:0;overflow:hidden">
        <div class="overflow-x-auto">
            <table class="table-brand w-full">
                <thead>
                    <tr>
                        <th class="text-left">Compétition</th>
                        <th class="text-left">Pays</th>
                        <th class="text-center">Priorité</th>
                        <th class="text-center">Statut</th>
                        <th class="text-center">Tendance</th>
                        <th class="text-left">Période</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($competitions as $competition)
                        <tr style="{{ $competition->isTrendingNow() ? 'background:rgba(249,115,22,.04)' : '' }}">
                            <td>
                                <div class="flex items-center gap-3">
                                    <span style="font-size:22px">{{ $competition->icon ?? '⚽' }}</span>
                                    <div>
                                        <p style="color:var(--ink);font-weight:600;font-size:14px">{{ $competition->name }}</p>
                                        <p style="color:var(--dim);font-size:12px">{{ $competition->full_name }}</p>
                                        <p style="color:var(--dim-2);font-size:11px;font-family:JetBrains Mono,monospace">{{ $competition->sportradar_id }}</p>
                                    </div>
                                </div>
                            </td>
                            <td style="color:var(--ink-2);font-size:13px">{{ $competition->country ?? '—' }}</td>
                            <td class="text-center">
                                <span style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:50%;font-size:12px;font-weight:700;
                                    {{ $competition->priority <= 2 ? 'background:rgba(61,220,145,.15);color:var(--win)' : ($competition->priority <= 5 ? 'background:rgba(232,255,54,.12);color:var(--accent)' : 'background:var(--bg-3);color:var(--dim)') }}">
                                    {{ $competition->priority }}
                                </span>
                            </td>
                            <td class="text-center">
                                <form action="{{ route('admin.competitions.toggle-active', $competition) }}" method="POST" class="inline">
                                    @csrf @method('PATCH')
                                    <button type="submit"
                                            class="{{ $competition->is_active ? 'badge-win' : 'badge-pending' }}"
                                            style="cursor:pointer;border:none;background:inherit">
                                        {{ $competition->is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </form>
                            </td>
                            <td class="text-center">
                                <form action="{{ route('admin.competitions.toggle-trending', $competition) }}" method="POST" class="inline">
                                    @csrf @method('PATCH')
                                    <button type="submit"
                                            style="cursor:pointer;border:none;background:inherit;{{ $competition->isTrendingNow() ? 'color:#f97316;font-weight:600;font-size:13px' : 'color:var(--dim);font-size:13px' }}">
                                        {{ $competition->isTrendingNow() ? '🔥 Oui' : 'Non' }}
                                    </button>
                                </form>
                            </td>
                            <td style="font-size:12px;color:var(--dim)">
                                @if($competition->trending_start && $competition->trending_end)
                                    {{ $competition->trending_start->format('d/m') }} — {{ $competition->trending_end->format('d/m/Y') }}
                                @else
                                    <span style="color:var(--dim-2)">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.competitions.edit', $competition) }}" title="Modifier"
                                       style="padding:6px 10px;background:rgba(232,255,54,.08);border:1px solid rgba(232,255,54,.2);border-radius:8px;color:var(--accent)">
                                        <i class="fa-solid fa-edit" style="font-size:12px"></i>
                                    </a>
                                    <form action="{{ route('admin.competitions.destroy', $competition) }}" method="POST"
                                          onsubmit="return confirm('Supprimer cette compétition ?')" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" title="Supprimer"
                                                style="padding:6px 10px;background:rgba(255,91,58,.12);border:1px solid rgba(255,91,58,.25);border-radius:8px;color:var(--loss);cursor:pointer">
                                            <i class="fa-solid fa-trash" style="font-size:12px"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding:48px;text-align:center;color:var(--dim)">
                                <i class="fa-solid fa-trophy" style="font-size:32px;display:block;margin-bottom:12px;color:var(--dim-2)"></i>
                                Aucune compétition trouvée
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($competitions->hasPages())
            <div style="padding:16px 24px;border-top:1px solid var(--line)">
                {{ $competitions->withQueryString()->links() }}
            </div>
        @endif
    </div>

    {{-- ── Info Box ─────────────────────────────────────────────────────────── --}}
    <div class="alert-brand" style="padding:20px 24px">
        <p style="color:var(--accent);font-weight:600;margin-bottom:12px"><i class="fa-solid fa-info-circle mr-2"></i>Comment fonctionne la gestion des tendances ?</p>
        <ul style="color:var(--ink-2);font-size:13px;line-height:1.8;list-style:none;padding:0;margin:0">
            <li><strong>Tendance manuelle :</strong> Activez le bouton "Tendance" pour mettre une compétition en avant immédiatement.</li>
            <li><strong>Tendance programmée :</strong> Définissez une période de début/fin pour activer automatiquement la tendance.</li>
            <li><strong>Priorité :</strong> Plus le chiffre est bas, plus la compétition apparaît en premier (1 = très prioritaire).</li>
            <li><strong>Cache :</strong> Les modifications sont appliquées immédiatement. Videz le cache si nécessaire.</li>
        </ul>
    </div>

</div>
@endsection
