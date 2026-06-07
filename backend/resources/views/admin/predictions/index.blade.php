@extends('admin.layouts.app')

@section('title', 'Pronostics')
@section('page-title', 'Gestion des Pronostics')

@section('content')
<div class="space-y-6">

    {{-- ── Stat Cards ──────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="card text-center">
            <p class="text-2xl font-bold" style="color:var(--ink);font-family:Archivo,sans-serif">{{ $stats['total'] }}</p>
            <p class="text-sm mt-1" style="color:var(--dim)">Total</p>
        </div>
        <div class="card text-center">
            <p class="text-2xl font-bold" style="color:#f5a623;font-family:Archivo,sans-serif">{{ $stats['pending'] }}</p>
            <p class="text-sm mt-1" style="color:var(--dim)">En attente</p>
        </div>
        <div class="card text-center">
            <p class="text-2xl font-bold" style="color:var(--win);font-family:Archivo,sans-serif">{{ $stats['won'] }}</p>
            <p class="text-sm mt-1" style="color:var(--dim)">Gagnés</p>
        </div>
        <div class="card text-center">
            <p class="text-2xl font-bold" style="color:var(--loss);font-family:Archivo,sans-serif">{{ $stats['lost'] }}</p>
            <p class="text-sm mt-1" style="color:var(--dim)">Perdus</p>
        </div>
    </div>

    {{-- ── Filtres & Actions ────────────────────────────────────────────────── --}}
    <div class="card">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
            <select name="status" class="input-brand" style="height:40px;padding:0 12px">
                <option value="">Tous les statuts</option>
                <option value="pending"   {{ request('status') == 'pending'   ? 'selected' : '' }}>En attente</option>
                <option value="won"       {{ request('status') == 'won'       ? 'selected' : '' }}>Gagnés</option>
                <option value="lost"      {{ request('status') == 'lost'      ? 'selected' : '' }}>Perdus</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Annulés</option>
            </select>

            <select name="prediction_type" class="input-brand" style="height:40px;padding:0 12px">
                <option value="">Tous les types</option>
                <option value="1X2"           {{ request('prediction_type') == '1X2'           ? 'selected' : '' }}>1X2</option>
                <option value="Over/Under"    {{ request('prediction_type') == 'Over/Under'    ? 'selected' : '' }}>Over/Under</option>
                <option value="BTTS"          {{ request('prediction_type') == 'BTTS'          ? 'selected' : '' }}>BTTS</option>
                <option value="Double Chance" {{ request('prediction_type') == 'Double Chance' ? 'selected' : '' }}>Double Chance</option>
            </select>

            <select name="is_premium" class="input-brand" style="height:40px;padding:0 12px">
                <option value="">Tous</option>
                <option value="true"  {{ request('is_premium') == 'true'  ? 'selected' : '' }}>Premium</option>
                <option value="false" {{ request('is_premium') == 'false' ? 'selected' : '' }}>Gratuit</option>
            </select>

            <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher…"
                   class="input-brand" style="height:40px">

            <div class="flex gap-2">
                <button type="submit" class="btn-primary btn-sm flex-1">
                    <i class="fa-solid fa-search mr-1"></i> Filtrer
                </button>
                <a href="{{ route('admin.predictions.index') }}" class="btn-secondary btn-sm px-3">
                    <i class="fa-solid fa-times"></i>
                </a>
            </div>

            <a href="{{ route('admin.predictions.create') }}" class="btn-primary btn-sm text-center">
                <i class="fa-solid fa-plus mr-1"></i> Nouveau
            </a>
        </form>
    </div>

    {{-- ── Table ────────────────────────────────────────────────────────────── --}}
    <div class="card" style="padding:0;overflow:hidden">
        <div class="overflow-x-auto">
            <table class="table-brand w-full min-w-[760px]">
                <thead>
                    <tr>
                        <th class="text-left">Match</th>
                        <th class="text-left">Compétition</th>
                        <th class="text-left">Date</th>
                        <th class="text-center">Prono</th>
                        <th class="text-center">Cote</th>
                        <th class="text-center">Score</th>
                        <th class="text-center">Statut</th>
                        <th class="text-center">Type</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($predictions as $prediction)
                        <tr>
                            <td>
                                <div style="color:var(--ink);font-weight:600">{{ $prediction->home_team }}</div>
                                <div style="color:var(--dim);font-size:13px">vs {{ $prediction->away_team }}</div>
                            </td>
                            <td style="color:var(--ink-2);font-size:13px">{{ $prediction->competition }}</td>
                            <td style="color:var(--ink-2);font-size:13px">{{ \Carbon\Carbon::parse($prediction->match_date)->format('d/m H:i') }}</td>
                            <td class="text-center">
                                <span class="badge-accent">{{ $prediction->prediction_value }}</span>
                            </td>
                            <td class="text-center" style="color:var(--ink);font-weight:600;font-family:JetBrains Mono,monospace">{{ $prediction->odds }}</td>
                            <td class="text-center">
                                @if($prediction->home_score !== null && $prediction->away_score !== null)
                                    <span style="color:var(--ink);font-weight:600;font-family:JetBrains Mono,monospace">{{ $prediction->home_score }} - {{ $prediction->away_score }}</span>
                                @else
                                    <span style="color:var(--dim-2)">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @php
                                    $statusBadge = [
                                        'pending'   => 'badge-pending',
                                        'won'       => 'badge-win',
                                        'lost'      => 'badge-loss',
                                        'cancelled' => '',
                                    ];
                                    $statusLabel = [
                                        'pending'   => 'En attente',
                                        'won'       => 'Gagné',
                                        'lost'      => 'Perdu',
                                        'cancelled' => 'Annulé',
                                    ];
                                @endphp
                                <span class="{{ $statusBadge[$prediction->status] ?? '' }}"
                                      @if(!($statusBadge[$prediction->status] ?? ''))
                                          style="padding:2px 8px;border-radius:4px;font-size:11px;background:rgba(139,138,133,.15);color:var(--dim)"
                                      @endif>
                                    {{ $statusLabel[$prediction->status] ?? $prediction->status }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($prediction->is_premium)
                                    <span class="badge-accent"><i class="fa-solid fa-crown mr-1"></i>Premium</span>
                                @else
                                    <span style="font-size:11px;color:var(--dim)">Gratuit</span>
                                @endif
                            </td>
                            <td>
                                <div class="flex justify-end gap-2">
                                    @if($prediction->status === 'pending')
                                        <form action="{{ route('admin.predictions.updateStatus', $prediction) }}" method="POST" class="inline">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="won">
                                            <button type="submit" class="btn-sm" title="Marquer gagné"
                                                    style="padding:6px 10px;background:rgba(61,220,145,.12);border:1px solid rgba(61,220,145,.25);border-radius:8px;color:var(--win);cursor:pointer">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.predictions.updateStatus', $prediction) }}" method="POST" class="inline">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="lost">
                                            <button type="submit" class="btn-sm" title="Marquer perdu"
                                                    style="padding:6px 10px;background:rgba(255,91,58,.12);border:1px solid rgba(255,91,58,.25);border-radius:8px;color:var(--loss);cursor:pointer">
                                                <i class="fa-solid fa-times"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('admin.predictions.edit', $prediction) }}"
                                       class="btn-sm" title="Modifier"
                                       style="padding:6px 10px;background:rgba(232,255,54,.08);border:1px solid rgba(232,255,54,.2);border-radius:8px;color:var(--accent)">
                                        <i class="fa-solid fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.predictions.destroy', $prediction) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Supprimer ce pronostic ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-sm" title="Supprimer"
                                                style="padding:6px 10px;background:rgba(255,91,58,.12);border:1px solid rgba(255,91,58,.25);border-radius:8px;color:var(--loss);cursor:pointer">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="padding:32px;text-align:center;color:var(--dim)">
                                Aucun pronostic trouvé
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($predictions->hasPages())
            <div style="padding:16px 24px;border-top:1px solid var(--line)">
                {{ $predictions->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
