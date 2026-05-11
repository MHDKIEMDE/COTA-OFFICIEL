@props([
    'title' => '',
    'value' => 0,
    'icon' => 'bi-collection',
    'color' => 'primary',
    'badge' => null
])

@php
    $colorClasses = [
        'primary' => 'bg-primary bg-opacity-10 text-primary',
        'success' => 'bg-success bg-opacity-10 text-success',
        'warning' => 'bg-warning bg-opacity-10 text-warning',
        'danger' => 'bg-danger bg-opacity-10 text-danger',
        'info' => 'bg-info bg-opacity-10 text-info',
    ];
    $bgColor = $colorClasses[$color] ?? $colorClasses['primary'];
@endphp

<div class="stat-card">
    <div class="d-flex align-items-start justify-content-between mb-3">
        <div class="stat-icon {{ $bgColor }}">
            <i class="bi {{ $icon }}"></i>
        </div>
        @if($badge)
            <span class="badge bg-success">{{ $badge }}</span>
        @endif
    </div>
    <div class="stat-value text-white">{{ $value }}</div>
    <div class="stat-label">{{ $title }}</div>
</div>

