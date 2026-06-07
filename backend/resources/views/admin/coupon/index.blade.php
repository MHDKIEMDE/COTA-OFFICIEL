@extends('admin.layouts.app')

@section('title', 'Coupon IA')
@section('page-title', 'Coupon IA — Gestion')

@section('content')
<div class="space-y-6">

    @if(session('success'))
    <div style="background:rgba(61,220,145,.1);border:1px solid rgba(61,220,145,.3);border-radius:10px;padding:14px 18px;color:var(--win);font-size:14px">
        {{ session('success') }}
    </div>
    @endif

    {{-- ── Coupon du jour ───────────────────────────────────────────────────── --}}
    <div class="card">
        <div class="flex items-center justify-between mb-4">
            <p class="tag-mono">Coupon du jour</p>
            <form method="GET" class="flex items-center gap-2">
                <input type="date" name="date" value="{{ $date }}"
                    style="background:var(--bg-3);border:1px solid var(--line);border-radius:8px;padding:7px 12px;color:var(--ink);font-size:13px;font-family:'JetBrains Mono',monospace">
                <button type="submit" class="btn-primary btn-sm">Filtrer</button>
            </form>
        </div>

        @if($todayCoupon)
        @php
            $picks = is_string($todayCoupon->details) ? json_decode($todayCoupon->details, true) : [];
            $picks = $picks ?? [];
        @endphp
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div style="background:var(--bg-3);border:1px solid var(--line);border-radius:10px;padding:16px;text-align:center">
                <div style="font-family:'JetBrains Mono',monospace;font-size:26px;font-weight:700;color:var(--accent)">{{ $todayCoupon->total_odds }}</div>
                <div style="font-size:11px;color:var(--dim);margin-top:4px">Cote totale</div>
            </div>
            <div style="background:var(--bg-3);border:1px solid var(--line);border-radius:10px;padding:16px;text-align:center">
                <div style="font-family:Archivo,sans-serif;font-weight:900;font-size:26px;color:var(--ink)">{{ $todayCoupon->predictions_count }}</div>
                <div style="font-size:11px;color:var(--dim);margin-top:4px">Picks</div>
            </div>
            <div style="background:var(--bg-3);border:1px solid var(--line);border-radius:10px;padding:16px;text-align:center">
                <div style="font-family:'JetBrains Mono',monospace;font-size:26px;font-weight:700;color:var(--win)">{{ number_format($todayCoupon->potential_payout ?? 0) }}</div>
                <div style="font-size:11px;color:var(--dim);margin-top:4px">Gain pot. (FCFA / 1 000)</div>
            </div>
            <div style="background:var(--bg-3);border:1px solid var(--line);border-radius:10px;padding:16px;text-align:center">
                @if($todayCoupon->status === 'won')
                    <div style="font-family:Archivo,sans-serif;font-weight:900;font-size:22px;color:var(--win)">GAGNÉ</div>
                @elseif($todayCoupon->status === 'lost')
                    <div style="font-family:Archivo,sans-serif;font-weight:900;font-size:22px;color:var(--loss)">PERDU</div>
                @elseif($todayCoupon->status === 'partial')
                    <div style="font-family:Archivo,sans-serif;font-weight:900;font-size:22px;color:var(--accent)">PARTIEL</div>
                @else
                    <div style="font-family:Archivo,sans-serif;font-weight:900;font-size:22px;color:var(--dim)">EN COURS</div>
                @endif
                <div style="font-size:11px;color:var(--dim);margin-top:4px">Résultat</div>
            </div>
        </div>

        {{-- Picks --}}
        @if(!empty($picks))
        <div style="border:1px solid var(--line);border-radius:10px;overflow:hidden;margin-bottom:16px">
            <div style="padding:10px 16px;background:var(--bg-3);border-bottom:1px solid var(--line)">
                <p class="tag-mono">Picks inclus</p>
            </div>
            @foreach($picks as $pick)
            <div style="padding:12px 16px;border-bottom:1px solid var(--line);display:flex;align-items:center;gap:12px">
                <div style="flex:1">
                    <div style="font-size:13px;font-weight:700;color:var(--ink)">{{ $pick['match'] ?? '—' }}</div>
                    <div style="font-size:11px;color:var(--dim);margin-top:2px;font-family:'JetBrains Mono',monospace">
                        {{ $pick['league'] ?? '' }} · {{ $pick['prediction'] ?? '' }}
                    </div>
                </div>
                <div style="font-family:'JetBrains Mono',monospace;font-size:14px;font-weight:700;color:var(--accent)">@{{ $pick['odds'] ?? '—' }}</div>
                <div style="font-size:11px;color:var(--dim)">{{ $pick['confidence'] ?? '' }}%</div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Actions coupon du jour --}}
        <div class="flex flex-wrap gap-3">
            @if(!$todayCoupon->is_published)
            <form action="{{ route('admin.coupon.publish', $todayCoupon->id) }}" method="POST">
                @csrf
                <button type="submit" class="btn-primary btn-sm">
                    <i class="fa-solid fa-eye mr-2"></i>Publier
                </button>
            </form>
            @else
            <form action="{{ route('admin.coupon.unpublish', $todayCoupon->id) }}" method="POST">
                @csrf
                <button type="submit" style="padding:8px 16px;background:rgba(255,91,58,.1);border:1px solid rgba(255,91,58,.3);border-radius:8px;color:var(--loss);font-size:13px;font-weight:600;cursor:pointer">
                    <i class="fa-solid fa-eye-slash mr-2"></i>Dépublier
                </button>
            </form>
            @endif

            <form action="{{ route('admin.coupon.status', $todayCoupon->id) }}" method="POST" class="flex items-center gap-2">
                @csrf
                <select name="status"
                    style="background:var(--bg-3);border:1px solid var(--line);border-radius:8px;padding:7px 12px;color:var(--ink);font-size:13px">
                    <option value="pending"  {{ $todayCoupon->status === 'pending'  ? 'selected' : '' }}>En attente</option>
                    <option value="won"      {{ $todayCoupon->status === 'won'      ? 'selected' : '' }}>Gagné</option>
                    <option value="lost"     {{ $todayCoupon->status === 'lost'     ? 'selected' : '' }}>Perdu</option>
                    <option value="partial"  {{ $todayCoupon->status === 'partial'  ? 'selected' : '' }}>Partiel</option>
                </select>
                <button type="submit" style="padding:8px 16px;background:var(--bg-3);border:1px solid var(--line);border-radius:8px;color:var(--ink-2);font-size:13px;font-weight:600;cursor:pointer">
                    Valider résultat
                </button>
            </form>
        </div>

        @else
        <div style="padding:40px;text-align:center">
            <div style="font-size:40px;margin-bottom:16px">🎟️</div>
            <p style="color:var(--dim);font-size:15px">Aucun coupon généré pour le {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}.</p>
            <p style="color:var(--dim-2);font-size:13px;margin-top:6px">Le coupon est généré automatiquement à 08h00 par <code>GenerateAllPredictionsJob</code>.</p>
        </div>
        @endif
    </div>

    {{-- ── Compteurs statuts ────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach([
            ['total',   'Total',      'var(--ink)',    'fa-ticket'],
            ['pending', 'En attente', 'var(--accent)', 'fa-clock'],
            ['won',     'Gagnés',     'var(--win)',    'fa-check-circle'],
            ['lost',    'Perdus',     'var(--loss)',   'fa-times-circle'],
        ] as [$key, $label, $color, $icon])
        <a href="{{ route('admin.coupon.index', ['status' => $key === 'total' ? null : $key]) }}"
           class="card" style="text-decoration:none;padding:16px;border:1px solid {{ ($status ?? 'total') === $key ? $color : 'var(--line)' }}">
            <div class="flex items-center gap-3">
                <i class="fa-solid {{ $icon }}" style="font-size:20px;color:{{ $color }}"></i>
                <div>
                    <div style="font-family:Archivo,sans-serif;font-weight:900;font-size:24px;color:{{ $color }}">{{ $counts[$key] }}</div>
                    <div style="color:var(--dim);font-size:12px">{{ $label }}</div>
                </div>
            </div>
        </a>
        @endforeach
    </div>

    {{-- ── Historique coupons ──────────────────────────────────────────────── --}}
    <div class="card" style="padding:0;overflow:hidden">
        <div style="padding:16px 20px;border-bottom:1px solid var(--line)">
            <p class="tag-mono">Historique des coupons</p>
        </div>

        @forelse($coupons as $coupon)
        <div style="padding:14px 20px;border-bottom:1px solid var(--line);display:flex;align-items:center;gap:16px">

            {{-- Date + type --}}
            <div style="width:90px;flex-shrink:0">
                <div style="font-family:'JetBrains Mono',monospace;font-size:13px;font-weight:700;color:var(--ink)">
                    {{ \Carbon\Carbon::parse($coupon->date)->format('d/m/Y') }}
                </div>
                <div style="font-size:10px;color:var(--dim);margin-top:2px;text-transform:uppercase;letter-spacing:.05em">
                    {{ $coupon->type }}
                </div>
            </div>

            {{-- Stats --}}
            <div style="flex:1;display:flex;align-items:center;gap:20px">
                <div>
                    <span style="font-family:'JetBrains Mono',monospace;font-size:16px;font-weight:700;color:var(--accent)">@{{ $coupon->total_odds }}</span>
                    <span style="font-size:11px;color:var(--dim);margin-left:4px">cote</span>
                </div>
                <div>
                    <span style="font-family:Archivo,sans-serif;font-weight:700;font-size:14px;color:var(--ink)">{{ $coupon->predictions_count }}</span>
                    <span style="font-size:11px;color:var(--dim);margin-left:4px">picks</span>
                </div>
                @if($coupon->won_count > 0 || $coupon->lost_count > 0)
                <div style="font-size:12px;color:var(--dim)">
                    <span style="color:var(--win)">{{ $coupon->won_count }}✓</span>
                    <span style="margin:0 4px">/</span>
                    <span style="color:var(--loss)">{{ $coupon->lost_count }}✗</span>
                </div>
                @endif
            </div>

            {{-- Statut --}}
            <div style="flex-shrink:0">
                @if($coupon->status === 'won')
                    <span class="badge-win">GAGNÉ</span>
                @elseif($coupon->status === 'lost')
                    <span class="badge-loss">PERDU</span>
                @elseif($coupon->status === 'partial')
                    <span style="font-size:10px;padding:3px 8px;border-radius:20px;background:rgba(232,255,54,.12);color:var(--accent);font-family:'JetBrains Mono',monospace;font-weight:700">PARTIEL</span>
                @else
                    <span class="badge-pending">EN COURS</span>
                @endif
            </div>

            {{-- Publié --}}
            <div style="flex-shrink:0">
                @if($coupon->is_published)
                    <span style="font-size:10px;padding:3px 8px;border-radius:20px;background:rgba(61,220,145,.1);color:var(--win);font-family:'JetBrains Mono',monospace;font-weight:700">PUBLIÉ</span>
                @else
                    <span style="font-size:10px;padding:3px 8px;border-radius:20px;background:var(--bg-3);color:var(--dim);font-family:'JetBrains Mono',monospace;font-weight:700">BROUILLON</span>
                @endif
            </div>

            {{-- Actions --}}
            <div style="display:flex;gap:6px;flex-shrink:0">
                <form action="{{ route('admin.coupon.status', $coupon->id) }}" method="POST" class="flex items-center gap-1">
                    @csrf
                    <select name="status"
                        style="background:var(--bg-3);border:1px solid var(--line);border-radius:6px;padding:5px 8px;color:var(--ink);font-size:11px">
                        <option value="pending" {{ $coupon->status === 'pending' ? 'selected' : '' }}>En attente</option>
                        <option value="won"     {{ $coupon->status === 'won'     ? 'selected' : '' }}>Gagné</option>
                        <option value="lost"    {{ $coupon->status === 'lost'    ? 'selected' : '' }}>Perdu</option>
                        <option value="partial" {{ $coupon->status === 'partial' ? 'selected' : '' }}>Partiel</option>
                    </select>
                    <button type="submit" style="padding:5px 10px;background:var(--bg-3);border:1px solid var(--line);border-radius:6px;color:var(--dim);font-size:11px;cursor:pointer">OK</button>
                </form>
                <form action="{{ route('admin.coupon.destroy', $coupon->id) }}" method="POST"
                      onsubmit="return confirm('Supprimer ce coupon ?')">
                    @csrf @method('DELETE')
                    <button type="submit" style="padding:5px 10px;background:rgba(255,91,58,.08);border:1px solid rgba(255,91,58,.2);border-radius:6px;color:var(--loss);font-size:11px;cursor:pointer">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div style="padding:60px;text-align:center">
            <div style="font-size:40px;margin-bottom:16px">🎟️</div>
            <p style="color:var(--dim);font-size:15px">Aucun coupon dans l'historique.</p>
        </div>
        @endforelse
    </div>

    @if($coupons->hasPages())
    <div>{{ $coupons->links() }}</div>
    @endif

</div>
@endsection
