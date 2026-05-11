@extends('layouts.app')

@php
    $hideDate = true;
@endphp

@section('header')
    <div class="app-header__title">
        <i class="bi bi-bar-chart-line"></i>
        <span>Statistiques</span>
    </div>
    <div class="app-header__actions">
        <button class="app-header__btn">
            <i class="bi bi-calendar3"></i>
        </button>
    </div>
@endsection

@section('content')
    {{-- Main Stats --}}
    <div style="padding: var(--cota-spacing-md);">
        {{-- Win Rate Circle --}}
        <div style="background: var(--cota-bg-secondary); border-radius: var(--cota-spacing-lg); padding: var(--cota-spacing-lg); text-align: center; margin-bottom: var(--cota-spacing-md);">
            <div style="position: relative; width: 120px; height: 120px; margin: 0 auto 16px;">
                <svg viewBox="0 0 36 36" style="transform: rotate(-90deg);">
                    <path
                        d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                        fill="none"
                        stroke="var(--cota-bg-tertiary)"
                        stroke-width="3"
                    />
                    <path
                        d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                        fill="none"
                        stroke="var(--cota-win)"
                        stroke-width="3"
                        stroke-dasharray="{{ $stats['win_rate'] ?? 0 }}, 100"
                        stroke-linecap="round"
                    />
                </svg>
                <div style="position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                    <span style="font-size: 2rem; font-weight: 800; color: var(--cota-text-primary);">{{ $stats['win_rate'] ?? 0 }}%</span>
                    <span style="font-size: 0.75rem; color: var(--cota-text-muted);">Taux de réussite</span>
                </div>
            </div>
            
            <div style="display: flex; justify-content: center; gap: 32px;">
                <div>
                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--cota-win);">{{ $stats['won'] ?? 0 }}</div>
                    <div style="font-size: 0.75rem; color: var(--cota-text-muted);">Gagnés</div>
                </div>
                <div style="width: 1px; background: var(--cota-border);"></div>
                <div>
                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--cota-loss);">{{ $stats['lost'] ?? 0 }}</div>
                    <div style="font-size: 0.75rem; color: var(--cota-text-muted);">Perdus</div>
                </div>
                <div style="width: 1px; background: var(--cota-border);"></div>
                <div>
                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--cota-draw);">{{ $stats['pending'] ?? 0 }}</div>
                    <div style="font-size: 0.75rem; color: var(--cota-text-muted);">En cours</div>
                </div>
            </div>
        </div>
        
        {{-- Key Metrics --}}
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-bottom: var(--cota-spacing-md);">
            <div style="background: var(--cota-bg-secondary); border-radius: var(--cota-spacing-sm); padding: 16px;">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                    <div style="width: 40px; height: 40px; background: rgba(59, 130, 246, 0.15); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-graph-up-arrow" style="color: var(--cota-accent); font-size: 1.125rem;"></i>
                    </div>
                    <span style="font-size: 0.8125rem; color: var(--cota-text-muted);">ROI</span>
                </div>
                <div style="font-size: 1.5rem; font-weight: 700; color: {{ ($stats['roi'] ?? 0) >= 0 ? 'var(--cota-win)' : 'var(--cota-loss)' }};">
                    {{ ($stats['roi'] ?? 0) >= 0 ? '+' : '' }}{{ $stats['roi'] ?? 0 }}%
                </div>
            </div>
            
            <div style="background: var(--cota-bg-secondary); border-radius: var(--cota-spacing-sm); padding: 16px;">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                    <div style="width: 40px; height: 40px; background: rgba(245, 158, 11, 0.15); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-calculator" style="color: var(--cota-favorite); font-size: 1.125rem;"></i>
                    </div>
                    <span style="font-size: 0.8125rem; color: var(--cota-text-muted);">Cote moyenne</span>
                </div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--cota-text-primary);">
                    {{ number_format($stats['avg_odds'] ?? 0, 2) }}
                </div>
            </div>
            
            <div style="background: var(--cota-bg-secondary); border-radius: var(--cota-spacing-sm); padding: 16px;">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                    <div style="width: 40px; height: 40px; background: rgba(16, 185, 129, 0.15); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-fire" style="color: var(--cota-win); font-size: 1.125rem;"></i>
                    </div>
                    <span style="font-size: 0.8125rem; color: var(--cota-text-muted);">Série</span>
                </div>
                <div style="font-size: 1.5rem; font-weight: 700; color: {{ ($stats['streak']['type'] ?? '') === 'win' ? 'var(--cota-win)' : 'var(--cota-loss)' }};">
                    {{ $stats['streak']['count'] ?? 0 }} {{ ($stats['streak']['type'] ?? '') === 'win' ? 'W' : 'L' }}
                </div>
            </div>
            
            <div style="background: var(--cota-bg-secondary); border-radius: var(--cota-spacing-sm); padding: 16px;">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                    <div style="width: 40px; height: 40px; background: rgba(99, 102, 241, 0.15); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-lightning-charge-fill" style="color: var(--cota-accent); font-size: 1.125rem;"></i>
                    </div>
                    <span style="font-size: 0.8125rem; color: var(--cota-text-muted);">Total</span>
                </div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--cota-text-primary);">
                    {{ $stats['total_predictions'] ?? 0 }}
                </div>
            </div>
        </div>
    </div>
    
    {{-- By Competition --}}
    <div class="prediction-section">
        <div class="prediction-section__header">
            <i class="bi bi-trophy"></i>
            <span>Par compétition</span>
        </div>
        <div class="prediction-section__content" style="padding: 0;">
            @forelse($byCompetition ?? [] as $comp)
                <div style="display: flex; align-items: center; padding: 14px var(--cota-spacing-md); border-bottom: 1px solid var(--cota-border);">
                    <div style="flex: 1;">
                        <div style="font-weight: 500; color: var(--cota-text-primary); margin-bottom: 4px;">{{ $comp->name }}</div>
                        <div style="font-size: 0.75rem; color: var(--cota-text-muted);">
                            {{ $comp->won }} gagnés sur {{ $comp->total }}
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 1.125rem; font-weight: 700; color: {{ $comp->win_rate >= 60 ? 'var(--cota-win)' : ($comp->win_rate >= 50 ? 'var(--cota-draw)' : 'var(--cota-loss)') }};">
                            {{ $comp->win_rate }}%
                        </div>
                    </div>
                </div>
            @empty
                <div class="empty-state" style="padding: 24px;">
                    <p class="empty-state__text mb-0">Pas assez de données</p>
                </div>
            @endforelse
        </div>
    </div>
    
    {{-- Best Performance --}}
    @if($stats['best_competition'] ?? null)
        <div style="padding: var(--cota-spacing-md);">
            <div style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.15) 0%, rgba(59, 130, 246, 0.15) 100%); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: var(--cota-spacing-lg); padding: var(--cota-spacing-md);">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                    <i class="bi bi-award" style="font-size: 1.5rem; color: var(--cota-win);"></i>
                    <span style="font-weight: 600; color: var(--cota-text-primary);">Meilleure performance</span>
                </div>
                <div style="color: var(--cota-text-secondary); font-size: 0.875rem;">
                    <strong style="color: var(--cota-text-primary);">{{ $stats['best_competition']['name'] }}</strong> 
                    avec un taux de réussite de 
                    <strong style="color: var(--cota-win);">{{ $stats['best_competition']['win_rate'] }}%</strong>
                </div>
            </div>
        </div>
    @endif
@endsection
