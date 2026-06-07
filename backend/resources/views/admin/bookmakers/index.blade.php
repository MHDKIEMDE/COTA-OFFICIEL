@extends('admin.layouts.app')

@section('title', 'Bookmakers')
@section('page-title', 'Gestion des Bookmakers')

@section('content')
<div class="space-y-6">

    {{-- ── Header ──────────────────────────────────────────────────────────── --}}
    <div class="flex justify-between items-center">
        <p style="color:var(--dim);font-size:14px">Configurez les liens d'affiliation et les bonus pour chaque bookmaker.</p>
        <a href="{{ route('admin.bookmakers.create') }}" class="btn-primary btn-sm">
            <i class="fa-solid fa-plus mr-2"></i>Ajouter un bookmaker
        </a>
    </div>

    {{-- ── Bonus par défaut ────────────────────────────────────────────────── --}}
    <div class="card">
        <div class="flex items-center justify-between">
            <div>
                <p style="color:var(--ink);font-weight:600;font-size:15px">Bonus par défaut</p>
                <p style="color:var(--dim);font-size:13px;margin-top:2px">Nombre de jours Premium offerts par défaut pour chaque affiliation.</p>
            </div>
            <div class="flex items-center gap-2">
                <span style="font-family:Archivo,sans-serif;font-weight:900;font-size:32px;color:var(--accent)">{{ $defaultBonusDays }}</span>
                <span style="color:var(--dim)">jours</span>
            </div>
        </div>
    </div>

    {{-- ── Bookmakers Grid ──────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($bookmakers as $id => $bookmaker)
            <div class="card" style="padding:0;overflow:hidden;{{ !($bookmaker['is_active'] ?? true) ? 'opacity:.5' : '' }}">
                <div class="p-5" style="border-bottom:1px solid var(--line);background:linear-gradient(135deg,{{ $bookmaker['color'] ?? '#6A1B9A' }}22 0%,transparent 100%)">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span style="font-size:32px">{{ $bookmaker['logo_emoji'] ?? '🎰' }}</span>
                            <div>
                                <h3 style="color:var(--ink);font-family:Archivo,sans-serif;font-weight:700;font-size:18px">{{ $bookmaker['name'] }}</h3>
                                <code style="font-size:11px;color:var(--dim-2);font-family:JetBrains Mono,monospace">{{ $id }}</code>
                            </div>
                        </div>
                        @if($bookmaker['is_active'] ?? true)
                            <span class="badge-win">Actif</span>
                        @else
                            <span class="badge-loss">Inactif</span>
                        @endif
                    </div>
                </div>

                <div class="p-5 space-y-3">
                    <div class="flex items-center justify-between">
                        <span style="color:var(--dim);font-size:13px">Bonus Premium</span>
                        <span style="color:var(--win);font-family:JetBrains Mono,monospace;font-weight:700">+{{ $bookmaker['bonus_days'] ?? 7 }} jours</span>
                    </div>
                    <div>
                        <p style="color:var(--dim);font-size:12px;margin-bottom:2px">Lien d'affiliation</p>
                        <p style="color:var(--accent);font-size:12px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $bookmaker['affiliate_url'] ?? '—' }}</p>
                    </div>
                    @if(!empty($bookmaker['description']))
                        <p style="color:var(--ink-2);font-size:12px">{{ $bookmaker['description'] }}</p>
                    @endif
                </div>

                <div class="flex gap-2 p-4" style="border-top:1px solid var(--line)">
                    <a href="{{ route('admin.bookmakers.edit', $id) }}" class="btn-sm flex-1 text-center"
                       style="background:rgba(232,255,54,.08);border:1px solid rgba(232,255,54,.2);border-radius:8px;color:var(--accent);padding:8px">
                        <i class="fa-solid fa-edit mr-1"></i> Modifier
                    </a>
                    <form action="{{ route('admin.bookmakers.toggle', $id) }}" method="POST" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full btn-sm"
                                style="{{ ($bookmaker['is_active'] ?? true) ? 'background:rgba(245,166,35,.1);border:1px solid rgba(245,166,35,.25);color:#f5a623' : 'background:rgba(61,220,145,.1);border:1px solid rgba(61,220,145,.25);color:var(--win)' }};border-radius:8px;padding:8px;cursor:pointer">
                            @if($bookmaker['is_active'] ?? true)
                                <i class="fa-solid fa-pause mr-1"></i> Désactiver
                            @else
                                <i class="fa-solid fa-play mr-1"></i> Activer
                            @endif
                        </button>
                    </form>
                    <form action="{{ route('admin.bookmakers.destroy', $id) }}" method="POST"
                          onsubmit="return confirm('Supprimer ce bookmaker ?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-sm"
                                style="padding:8px 12px;background:rgba(255,91,58,.12);border:1px solid rgba(255,91,58,.25);border-radius:8px;color:var(--loss);cursor:pointer">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-3 card text-center" style="padding:48px">
                <i class="fa-solid fa-link" style="font-size:36px;color:var(--dim-2);display:block;margin-bottom:16px"></i>
                <h3 style="color:var(--ink);font-weight:700;font-size:18px;margin-bottom:8px">Aucun bookmaker</h3>
                <p style="color:var(--dim);margin-bottom:20px">Ajoutez votre premier bookmaker partenaire.</p>
                <a href="{{ route('admin.bookmakers.create') }}" class="btn-primary btn-sm">
                    <i class="fa-solid fa-plus mr-2"></i>Ajouter un bookmaker
                </a>
            </div>
        @endforelse
    </div>

</div>
@endsection
