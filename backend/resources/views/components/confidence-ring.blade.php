@props([
    'stars' => 0,
    'size'  => 'md', // 'sm' | 'md' | 'lg'
])

@php
    $max      = 4;
    $stars    = (int) $stars;
    $pct      = $max > 0 ? round(($stars / $max) * 100) : 0;
    $color    = match(true) {
        $stars >= 4 => 'var(--cota-accent)',
        $stars >= 3 => 'var(--cota-win)',
        $stars >= 2 => '#f5a623',
        default     => 'var(--cota-text-muted)',
    };
    $dim = match($size) {
        'sm' => 28,
        'lg' => 64,
        default => 44,
    };
    $r         = ($dim / 2) - 3;
    $circ      = round(2 * M_PI * $r, 2);
    $dasharray = round($circ * $pct / 100, 2) . ' ' . $circ;
@endphp

<div style="position:relative;width:{{ $dim }}px;height:{{ $dim }}px;flex-shrink:0;">
    <svg viewBox="0 0 {{ $dim }} {{ $dim }}" width="{{ $dim }}" height="{{ $dim }}" style="transform:rotate(-90deg);">
        <circle cx="{{ $dim/2 }}" cy="{{ $dim/2 }}" r="{{ $r }}" fill="none"
                stroke="var(--cota-bg-elevated)" stroke-width="2.5"/>
        <circle cx="{{ $dim/2 }}" cy="{{ $dim/2 }}" r="{{ $r }}" fill="none"
                stroke="{{ $color }}" stroke-width="2.5"
                stroke-dasharray="{{ $dasharray }}" stroke-linecap="round"/>
    </svg>
    <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:{{ $size === 'sm' ? '0.5625rem' : ($size === 'lg' ? '1rem' : '0.6875rem') }};color:{{ $color }};">
        {{ $stars }}<span style="font-size:0.5em;opacity:.7;">★</span>
    </div>
</div>
