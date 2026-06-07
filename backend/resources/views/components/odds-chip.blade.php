@props([
    'odds'      => null,
    'highlight' => false,
])

@if($odds)
    <span style="display:inline-flex;align-items:center;padding:3px 10px;border-radius:6px;font-family:'JetBrains Mono',monospace;font-size:0.8125rem;font-weight:700;
        background:{{ $highlight ? 'var(--cota-accent)' : 'var(--cota-bg-elevated)' }};
        color:{{ $highlight ? 'var(--cota-on-accent)' : 'var(--cota-text-primary)' }};
        border:1px solid {{ $highlight ? 'transparent' : 'var(--cota-border)' }};">
        {{ number_format((float) $odds, 2) }}
    </span>
@endif
