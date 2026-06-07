@props([
    'status' => 'pending',
])

@php
    $map = [
        'won'       => ['label' => 'Gagné',      'icon' => 'bi-check-circle-fill', 'color' => 'var(--cota-win)',  'bg' => 'rgba(61,220,145,.14)'],
        'lost'      => ['label' => 'Perdu',      'icon' => 'bi-x-circle-fill',     'color' => 'var(--cota-loss)', 'bg' => 'rgba(255,91,58,.14)'],
        'live'      => ['label' => 'LIVE',       'icon' => 'bi-broadcast',         'color' => 'var(--cota-loss)', 'bg' => 'rgba(255,91,58,.14)'],
        'pending'   => ['label' => 'En attente', 'icon' => 'bi-clock',             'color' => '#f5a623',          'bg' => 'rgba(245,166,35,.14)'],
        'void'      => ['label' => 'Annulé',     'icon' => 'bi-slash-circle',      'color' => 'var(--cota-text-muted)', 'bg' => 'rgba(139,138,133,.14)'],
        'cancelled' => ['label' => 'Annulé',     'icon' => 'bi-slash-circle',      'color' => 'var(--cota-text-muted)', 'bg' => 'rgba(139,138,133,.14)'],
    ];
    $cfg = $map[$status] ?? $map['pending'];
@endphp

<span style="display:inline-flex;align-items:center;gap:4px;font-size:0.6875rem;font-weight:700;padding:3px 8px;border-radius:6px;background:{{ $cfg['bg'] }};color:{{ $cfg['color'] }};">
    <i class="bi {{ $cfg['icon'] }}" style="font-size:0.6875rem;"></i>
    {{ $cfg['label'] }}
</span>
