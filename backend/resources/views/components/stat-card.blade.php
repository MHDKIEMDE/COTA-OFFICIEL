@props([
    'title' => '',
    'value' => 0,
    'icon'  => 'bi-collection',
    'color' => 'var(--cota-accent)',
    'badge' => null,
])

<div style="background:var(--cota-bg-secondary);border:1px solid var(--cota-border);border-radius:16px;padding:18px 16px;">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:12px;">
        <div style="width:40px;height:40px;border-radius:10px;background:{{ $color }}20;display:flex;align-items:center;justify-content:center;">
            <i class="bi {{ $icon }}" style="font-size:1.125rem;color:{{ $color }}"></i>
        </div>
        @if($badge)
            <span style="font-size:0.6875rem;font-weight:700;padding:3px 8px;border-radius:20px;background:rgba(61,220,145,0.15);color:var(--cota-win);">{{ $badge }}</span>
        @endif
    </div>
    <div style="font-size:1.75rem;font-weight:800;color:var(--cota-text-primary);font-family:'Plus Jakarta Sans',sans-serif;line-height:1;">{{ $value }}</div>
    <div style="font-size:0.75rem;color:var(--cota-text-muted);margin-top:4px;text-transform:uppercase;letter-spacing:0.04em;">{{ $title }}</div>
</div>
