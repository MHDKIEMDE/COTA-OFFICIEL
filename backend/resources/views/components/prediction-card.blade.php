@props([
    'prediction',
    'isLive' => false,
])

@php
    $isPremium = auth()->check() && auth()->user()->is_premium;
    $isLocked  = ($prediction->confidence_stars ?? $prediction->confidence ?? 0) >= 3 && !$isPremium;

    $statusColor = match($prediction->status ?? 'pending') {
        'won'  => 'var(--cota-win)',
        'lost' => 'var(--cota-loss)',
        'live' => 'var(--cota-loss)',
        default => '#f5a623',
    };
    $statusLabel = match($prediction->status ?? 'pending') {
        'won'  => 'Gagné',
        'lost' => 'Perdu',
        'live' => 'LIVE',
        default => 'En attente',
    };
    $statusIcon = match($prediction->status ?? 'pending') {
        'won'  => 'bi-check-circle-fill',
        'lost' => 'bi-x-circle-fill',
        'live' => 'bi-broadcast',
        default => 'bi-clock',
    };
@endphp

<a href="{{ route('predictions.show', $prediction) }}"
   style="display:block;background:var(--cota-bg-secondary);border:1px solid var(--cota-border);border-radius:16px;overflow:hidden;text-decoration:none;transition:border-color .2s;"
   onmouseover="this.style.borderColor='var(--cota-accent)'"
   onmouseout="this.style.borderColor='var(--cota-border)'">

    {{-- Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 14px;border-bottom:1px solid var(--cota-border);">
        @if($prediction->competition ?? null)
            <span style="font-size:0.6875rem;font-weight:600;color:var(--cota-text-muted);text-transform:uppercase;letter-spacing:0.04em;">{{ $prediction->competition }}</span>
        @endif
        <span style="display:inline-flex;align-items:center;gap:4px;font-size:0.6875rem;font-weight:700;padding:3px 8px;border-radius:6px;background:{{ $statusColor }}18;color:{{ $statusColor }};">
            <i class="bi {{ $statusIcon }}" style="font-size:0.6875rem;"></i>
            {{ $statusLabel }}
        </span>
    </div>

    {{-- Teams --}}
    <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 14px 12px;">
        {{-- Home --}}
        <div style="flex:1;text-align:center;">
            <div style="width:40px;height:40px;border-radius:10px;background:var(--cota-bg-tertiary);display:flex;align-items:center;justify-content:center;margin:0 auto 6px;font-weight:800;font-size:0.875rem;color:var(--cota-text-primary);">
                @if($prediction->home_team_logo ?? null)
                    <img src="{{ $prediction->home_team_logo }}" style="width:28px;height:28px;object-fit:contain;" alt="">
                @else
                    {{ strtoupper(substr($prediction->home_team ?? 'H', 0, 2)) }}
                @endif
            </div>
            <span style="font-size:0.75rem;font-weight:600;color:var(--cota-text-primary);display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:80px;margin:0 auto;">{{ $prediction->home_team ?? '?' }}</span>
        </div>

        {{-- VS --}}
        <div style="padding:0 8px;text-align:center;">
            <span style="font-size:0.6875rem;font-weight:800;color:var(--cota-text-muted);letter-spacing:0.08em;">VS</span>
            <div style="font-size:0.6875rem;color:var(--cota-text-muted);margin-top:2px;">{{ \Carbon\Carbon::parse($prediction->match_date)->format('H:i') }}</div>
        </div>

        {{-- Away --}}
        <div style="flex:1;text-align:center;">
            <div style="width:40px;height:40px;border-radius:10px;background:var(--cota-bg-tertiary);display:flex;align-items:center;justify-content:center;margin:0 auto 6px;font-weight:800;font-size:0.875rem;color:var(--cota-text-primary);">
                @if($prediction->away_team_logo ?? null)
                    <img src="{{ $prediction->away_team_logo }}" style="width:28px;height:28px;object-fit:contain;" alt="">
                @else
                    {{ strtoupper(substr($prediction->away_team ?? 'A', 0, 2)) }}
                @endif
            </div>
            <span style="font-size:0.75rem;font-weight:600;color:var(--cota-text-primary);display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:80px;margin:0 auto;">{{ $prediction->away_team ?? '?' }}</span>
        </div>
    </div>

    {{-- Prediction / Lock --}}
    @if($isLocked)
        <div style="margin:0 14px 12px;padding:12px;background:rgba(232,255,54,.06);border:1px dashed rgba(232,255,54,.25);border-radius:10px;text-align:center;">
            <i class="bi bi-lock-fill" style="color:var(--cota-accent);font-size:1rem;display:block;margin-bottom:4px;"></i>
            <p style="font-size:0.75rem;color:var(--cota-text-muted);margin-bottom:6px;">Pronostic Premium</p>
            <a href="{{ route('subscription') }}" style="display:inline-flex;align-items:center;gap:4px;background:var(--cota-accent);color:var(--cota-on-accent);font-size:0.75rem;font-weight:700;padding:5px 12px;border-radius:8px;text-decoration:none;">
                <i class="bi bi-star-fill"></i> Débloquer
            </a>
        </div>
    @else
        <div style="margin:0 14px 12px;padding:10px 12px;background:var(--cota-bg-tertiary);border-radius:10px;display:flex;align-items:center;justify-content:space-between;">
            <div>
                <div style="font-size:0.6875rem;color:var(--cota-text-muted);">Pronostic</div>
                <div style="font-size:0.9375rem;font-weight:700;color:var(--cota-text-primary);">{{ $prediction->bet_type ?? $prediction->prediction_value ?? '?' }}</div>
            </div>
            <x-odds-chip :odds="$prediction->odds ?? null" />
        </div>
    @endif

    {{-- Footer : étoiles --}}
    <div style="padding:8px 14px 12px;display:flex;align-items:center;justify-content:space-between;">
        <x-confidence-ring :stars="$prediction->confidence_stars ?? $prediction->confidence ?? 0" size="sm" />
        <span style="font-size:0.75rem;color:var(--cota-accent);font-weight:600;">Détails →</span>
    </div>
</a>
