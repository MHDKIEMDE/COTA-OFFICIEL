@extends('admin.layouts.app')

@section('title', 'Liste d\'attente — Bookmakers')
@section('page-title', 'Bookmakers — Liste d\'attente API')

@section('content')
<div class="space-y-6">

    {{-- ── Alertes ──────────────────────────────────────────────────────────────── --}}
    @if(session('success'))
        <div style="background:rgba(61,220,145,.1);border:1px solid rgba(61,220,145,.3);border-radius:10px;padding:14px 18px;color:var(--win);font-size:14px">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div style="background:rgba(255,91,58,.1);border:1px solid rgba(255,91,58,.3);border-radius:10px;padding:14px 18px;color:var(--loss);font-size:14px">
            {{ session('error') }}
        </div>
    @endif

    {{-- ── Header actions ──────────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <p style="color:var(--dim);font-size:14px">
                Bookmakers détectés automatiquement depuis les APIs. Valide ou rejette avant publication.
            </p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.bookmakers.list') }}"
               style="padding:9px 16px;background:rgba(232,255,54,.08);border:1px solid rgba(232,255,54,.2);border-radius:8px;color:var(--accent);font-size:13px;font-weight:600;text-decoration:none">
                <i class="fa-solid fa-list mr-2"></i>Bookmakers actifs
            </a>
            <form action="{{ route('admin.bookmaker-candidates.fetch') }}" method="POST">
                @csrf
                <button type="submit" class="btn-primary btn-sm">
                    <i class="fa-solid fa-rotate mr-2"></i>Lancer le fetch API
                </button>
            </form>
        </div>
    </div>

    {{-- ── Compteurs statuts ────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-3 gap-4">
        @foreach([
            ['pending',  'En attente', 'var(--accent)', 'fa-clock'],
            ['approved', 'Approuvés',  'var(--win)',    'fa-check-circle'],
            ['rejected', 'Rejetés',    'var(--loss)',   'fa-times-circle'],
        ] as [$key, $label, $color, $icon])
        <a href="{{ route('admin.bookmaker-candidates.index', ['status' => $key]) }}"
           class="card"
           style="text-decoration:none;border:1px solid {{ $status === $key ? $color : 'var(--line)' }};padding:16px">
            <div class="flex items-center gap-3">
                <i class="fa-solid {{ $icon }}" style="font-size:20px;color:{{ $color }}"></i>
                <div>
                    <div style="font-family:Archivo,sans-serif;font-weight:900;font-size:22px;color:{{ $color }}">
                        {{ $counts[$key] }}
                    </div>
                    <div style="color:var(--dim);font-size:12px">{{ $label }}</div>
                </div>
            </div>
        </a>
        @endforeach
    </div>

    {{-- ── Liste candidats ─────────────────────────────────────────────────────── --}}
    <div class="card" style="padding:0;overflow:hidden">
        @forelse($candidates as $c)
        <div style="padding:18px 20px;border-bottom:1px solid var(--line);display:flex;align-items:center;gap:16px">

            {{-- Logo / initiale --}}
            <div style="width:46px;height:46px;border-radius:10px;background:rgba(232,255,54,.08);border:1px solid rgba(232,255,54,.2);flex-shrink:0;display:flex;align-items:center;justify-content:center;overflow:hidden">
                @if($c->logo_url)
                    <img src="{{ $c->logo_url }}" style="width:100%;height:100%;object-fit:contain" onerror="this.style.display='none'">
                @else
                    <span style="font-family:Archivo,sans-serif;font-weight:900;font-size:18px;color:var(--accent)">
                        {{ strtoupper(substr($c->name,0,1)) }}
                    </span>
                @endif
            </div>

            {{-- Infos --}}
            <div style="flex:1;min-width:0">
                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                    <span style="font-family:Archivo,sans-serif;font-weight:700;font-size:15px;color:var(--ink)">{{ $c->name }}</span>
                    <span style="font-size:10px;padding:2px 7px;border-radius:20px;font-family:'JetBrains Mono',monospace;font-weight:700;
                        {{ $c->status === 'pending' ? 'background:rgba(232,255,54,.12);color:var(--accent)' : ($c->status === 'approved' ? 'background:rgba(61,220,145,.12);color:var(--win)' : 'background:rgba(255,91,58,.12);color:var(--loss)') }}">
                        {{ strtoupper($c->status) }}
                    </span>
                    <span style="font-size:10px;color:var(--dim-2);font-family:'JetBrains Mono',monospace">
                        {{ $c->api_source }} #{{ $c->api_id }}
                    </span>
                </div>
                @if($c->bonus_label)
                <div style="font-size:12px;color:var(--accent);margin-top:3px">🎁 {{ $c->bonus_label }}</div>
                @endif
                @if($c->description)
                <div style="font-size:12px;color:var(--dim);margin-top:2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:500px">{{ $c->description }}</div>
                @endif
                @if($c->rejection_reason)
                <div style="font-size:11px;color:var(--loss);margin-top:3px">Raison : {{ $c->rejection_reason }}</div>
                @endif
            </div>

            {{-- Date --}}
            <div style="font-size:11px;color:var(--dim-2);font-family:'JetBrains Mono',monospace;white-space:nowrap;flex-shrink:0">
                {{ $c->created_at->format('d/m H:i') }}
            </div>

            {{-- Actions --}}
            <div style="display:flex;gap:8px;flex-shrink:0">
                @if($c->isPending())
                <a href="{{ route('admin.bookmaker-candidates.show', $c) }}"
                   style="padding:7px 14px;background:rgba(232,255,54,.08);border:1px solid rgba(232,255,54,.25);border-radius:8px;color:var(--accent);font-size:12px;font-weight:700;text-decoration:none">
                    EXAMINER
                </a>
                @else
                <a href="{{ route('admin.bookmaker-candidates.show', $c) }}"
                   style="padding:7px 14px;background:var(--bg-2);border:1px solid var(--line);border-radius:8px;color:var(--dim);font-size:12px;font-weight:600;text-decoration:none">
                    VOIR
                </a>
                @if(!$c->isApproved())
                <form action="{{ route('admin.bookmaker-candidates.reset', $c) }}" method="POST">
                    @csrf
                    <button type="submit" style="padding:7px 14px;background:var(--bg-2);border:1px solid var(--line);border-radius:8px;color:var(--dim);font-size:12px;font-weight:600;cursor:pointer">
                        <i class="fa-solid fa-rotate-left"></i>
                    </button>
                </form>
                @endif
                @if($c->isRejected())
                <form action="{{ route('admin.bookmaker-candidates.destroy', $c) }}" method="POST"
                      onsubmit="return confirm('Supprimer définitivement ?')">
                    @csrf @method('DELETE')
                    <button type="submit" style="padding:7px 12px;background:rgba(255,91,58,.1);border:1px solid rgba(255,91,58,.25);border-radius:8px;color:var(--loss);font-size:12px;cursor:pointer">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </form>
                @endif
                @endif
            </div>
        </div>
        @empty
        <div style="padding:60px;text-align:center">
            <div style="font-size:40px;margin-bottom:16px">🎰</div>
            <p style="color:var(--dim);font-size:15px">
                @if($status === 'pending')
                    Aucun candidat en attente. Lance le fetch API pour en récupérer.
                @elseif($status === 'approved')
                    Aucun candidat approuvé.
                @else
                    Aucun candidat rejeté.
                @endif
            </p>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($candidates->hasPages())
    <div>{{ $candidates->appends(['status' => $status])->links() }}</div>
    @endif

</div>
@endsection
