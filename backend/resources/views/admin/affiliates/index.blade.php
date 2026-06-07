@extends('admin.layouts.app')

@section('title', 'Affiliations')
@section('page-title', 'Gestion des Affiliations')

@section('content')
<div class="space-y-6">

    {{-- ── Stats ───────────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach([
            ['Total',        $stats['total'],         'var(--ink)'],
            ['Vérifiées',    $stats['verified'],      'var(--win)'],
            ['En attente',   $stats['pending'],       '#f5a623'],
            ['Jours offerts',$stats['premium_given'], 'var(--accent)'],
        ] as [$label, $val, $color])
            <div class="card text-center">
                <p class="text-2xl font-bold" style="color:{{ $color }};font-family:Archivo,sans-serif">{{ $val }}</p>
                <p class="text-sm mt-1" style="color:var(--dim)">{{ $label }}</p>
            </div>
        @endforeach
    </div>

    {{-- ── Par Bookmaker ────────────────────────────────────────────────────── --}}
    @if($bookmakerStats->count() > 0)
        <div class="card">
            <p class="tag-mono mb-4">Par Bookmaker</p>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach($bookmakerStats as $bookmaker => $data)
                    <div class="p-4 rounded-lg" style="background:var(--bg-3);border:1px solid var(--line)">
                        <p style="color:var(--ink);font-weight:600;font-size:14px">{{ ucfirst($bookmaker) }}</p>
                        <p style="color:var(--accent);font-family:Archivo,sans-serif;font-weight:900;font-size:22px;margin-top:4px">{{ $data->total }}</p>
                        <p style="font-size:12px;color:var(--win);margin-top:2px">{{ $data->verified }} vérifiés</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ── Filtres ──────────────────────────────────────────────────────────── --}}
    <div class="card">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <select name="bookmaker" class="input-brand" style="height:40px;padding:0 12px">
                <option value="">Tous les bookmakers</option>
                <option value="betwinner" {{ request('bookmaker') == 'betwinner' ? 'selected' : '' }}>BetWinner</option>
                <option value="1xbet"     {{ request('bookmaker') == '1xbet'     ? 'selected' : '' }}>1xBet</option>
                <option value="melbet"    {{ request('bookmaker') == 'melbet'    ? 'selected' : '' }}>Melbet</option>
            </select>
            <select name="status" class="input-brand" style="height:40px;padding:0 12px">
                <option value="">Tous les statuts</option>
                <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Vérifiées</option>
                <option value="pending"  {{ request('status') == 'pending'  ? 'selected' : '' }}>En attente</option>
            </select>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="ID joueur, nom, téléphone…"
                   class="input-brand" style="height:40px">
            <div class="flex gap-2">
                <button type="submit" class="btn-primary btn-sm flex-1">
                    <i class="fa-solid fa-search mr-1"></i> Filtrer
                </button>
                <a href="{{ route('admin.affiliates.index') }}" class="btn-secondary btn-sm px-3">
                    <i class="fa-solid fa-times"></i>
                </a>
            </div>
        </form>
    </div>

    {{-- ── Table ────────────────────────────────────────────────────────────── --}}
    <div class="card" style="padding:0;overflow:hidden">
        <div class="overflow-x-auto">
            <table class="table-brand w-full">
                <thead>
                    <tr>
                        <th class="text-left">Utilisateur</th>
                        <th class="text-left">Bookmaker</th>
                        <th class="text-left">ID Joueur</th>
                        <th class="text-center">Bonus</th>
                        <th class="text-center">Statut</th>
                        <th class="text-center">Date</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($affiliations as $affiliation)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0"
                                         style="background:rgba(232,255,54,.12);border:1px solid rgba(232,255,54,.2);color:var(--accent);font-weight:700;font-size:12px">
                                        {{ strtoupper(substr($affiliation->user->name ?? 'U', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div style="color:var(--ink);font-weight:600;font-size:14px">{{ $affiliation->user->name ?? 'Sans nom' }}</div>
                                        <div style="color:var(--dim);font-size:12px">{{ $affiliation->user->phone ?? '—' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge-accent">{{ ucfirst($affiliation->bookmaker) }}</span></td>
                            <td>
                                <code style="padding:2px 8px;background:var(--bg-3);border:1px solid var(--line-2);border-radius:6px;color:var(--ink-2);font-family:JetBrains Mono,monospace;font-size:12px">{{ $affiliation->player_id }}</code>
                            </td>
                            <td class="text-center" style="color:var(--win);font-family:JetBrains Mono,monospace;font-weight:600">+{{ $affiliation->bonus_days }} jours</td>
                            <td class="text-center">
                                @if($affiliation->is_verified)
                                    <span class="badge-win"><i class="fa-solid fa-check mr-1"></i>Vérifié</span>
                                @else
                                    <span class="badge-pending"><i class="fa-solid fa-clock mr-1"></i>En attente</span>
                                @endif
                            </td>
                            <td class="text-center" style="font-size:12px;color:var(--dim)">{{ $affiliation->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <div class="flex justify-end gap-2">
                                    @if(!$affiliation->is_verified)
                                        <form action="{{ route('admin.affiliates.verify', $affiliation) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" title="Vérifier"
                                                    style="padding:6px 10px;background:rgba(61,220,145,.12);border:1px solid rgba(61,220,145,.25);border-radius:8px;color:var(--win);cursor:pointer">
                                                <i class="fa-solid fa-check" style="font-size:12px"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.affiliates.reject', $affiliation) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" title="Rejeter"
                                                    style="padding:6px 10px;background:rgba(255,91,58,.12);border:1px solid rgba(255,91,58,.25);border-radius:8px;color:var(--loss);cursor:pointer">
                                                <i class="fa-solid fa-times" style="font-size:12px"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <form action="{{ route('admin.affiliates.destroy', $affiliation) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Supprimer cette affiliation ?')">
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
                            <td colspan="7" style="padding:32px;text-align:center;color:var(--dim)">Aucune affiliation trouvée</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($affiliations->hasPages())
            <div style="padding:16px 24px;border-top:1px solid var(--line)">
                {{ $affiliations->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
