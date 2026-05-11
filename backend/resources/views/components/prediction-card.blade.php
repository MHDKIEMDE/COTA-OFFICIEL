@props([
    'prediction',
    'isLive' => false
])

@php
    $isPremium = auth()->check() && auth()->user()->is_premium;
    $isLocked = $prediction->confidence >= 3 && !$isPremium;
    
    $statusClasses = [
        'pending' => 'pending',
        'won' => 'won',
        'lost' => 'lost',
        'live' => 'live',
    ];
    $statusClass = $statusClasses[$prediction->status] ?? 'pending';
@endphp

<div class="prediction-card h-100">
    <div class="card-header d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            @if($prediction->competition)
                <span class="competition-badge">
                    {{ $prediction->competition }}
                </span>
            @endif
        </div>
        <div class="d-flex align-items-center gap-2">
            @if($isLive)
                <span class="status-badge live">
                    <i class="bi bi-broadcast"></i> LIVE
                </span>
            @else
                <span class="status-badge {{ $statusClass }}">
                    @if($prediction->status === 'won')
                        <i class="bi bi-check-circle-fill"></i> Gagné
                    @elseif($prediction->status === 'lost')
                        <i class="bi bi-x-circle-fill"></i> Perdu
                    @else
                        <i class="bi bi-clock"></i> En attente
                    @endif
                </span>
            @endif
        </div>
    </div>
    
    <div class="card-body p-4">
        <!-- Teams -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div class="text-center flex-grow-1">
                <div class="mb-2">
                    @if($prediction->home_team_logo)
                        <img src="{{ $prediction->home_team_logo }}" alt="{{ $prediction->home_team }}" class="team-logo">
                    @else
                        <div class="d-flex align-items-center justify-content-center mx-auto rounded-circle bg-secondary bg-opacity-25" 
                             style="width: 48px; height: 48px;">
                            <i class="bi bi-shield fs-4 text-muted-light"></i>
                        </div>
                    @endif
                </div>
                <span class="text-white fw-medium small d-block text-truncate" style="max-width: 100px;">
                    {{ $prediction->home_team }}
                </span>
            </div>
            
            <div class="px-3">
                <span class="vs-badge">VS</span>
            </div>
            
            <div class="text-center flex-grow-1">
                <div class="mb-2">
                    @if($prediction->away_team_logo)
                        <img src="{{ $prediction->away_team_logo }}" alt="{{ $prediction->away_team }}" class="team-logo">
                    @else
                        <div class="d-flex align-items-center justify-content-center mx-auto rounded-circle bg-secondary bg-opacity-25" 
                             style="width: 48px; height: 48px;">
                            <i class="bi bi-shield fs-4 text-muted-light"></i>
                        </div>
                    @endif
                </div>
                <span class="text-white fw-medium small d-block text-truncate" style="max-width: 100px;">
                    {{ $prediction->away_team }}
                </span>
            </div>
        </div>
        
        <!-- Match Time -->
        <div class="text-center mb-4">
            <div class="match-timer d-inline-flex">
                <i class="bi bi-clock"></i>
                <span>{{ \Carbon\Carbon::parse($prediction->match_date)->format('H:i') }}</span>
            </div>
        </div>
        
        <!-- Prediction (locked or visible) -->
        @if($isLocked)
            <div class="text-center py-3 rounded-3" style="background: rgba(233, 30, 140, 0.1); border: 1px dashed rgba(233, 30, 140, 0.3);">
                <i class="bi bi-lock-fill text-primary fs-4 mb-2 d-block"></i>
                <p class="text-muted-light small mb-2">Pronostic Premium</p>
                <a href="{{ route('subscription') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-star me-1"></i> Débloquer
                </a>
            </div>
        @else
            <div class="text-center py-3 rounded-3" style="background: rgba(255, 255, 255, 0.03);">
                <p class="text-muted-light small mb-1">Pronostic</p>
                <h5 class="text-white fw-bold mb-2">{{ $prediction->bet_type }}</h5>
                <span class="odds-badge">{{ number_format($prediction->odds, 2) }}</span>
            </div>
        @endif
        
        <!-- Confidence Stars -->
        <div class="d-flex align-items-center justify-content-between mt-4 pt-3 border-top border-secondary">
            <div class="star-rating">
                @for($i = 1; $i <= 4; $i++)
                    <i class="bi bi-star{{ $i <= $prediction->confidence ? '-fill' : '' }} star {{ $i <= $prediction->confidence ? 'filled' : '' }}"></i>
                @endfor
            </div>
            <a href="{{ route('predictions.show', $prediction) }}" class="btn btn-link text-primary text-decoration-none p-0">
                Détails <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
</div>

