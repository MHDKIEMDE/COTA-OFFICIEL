@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Users -->
        <div class="stat-card bg-dark-100 rounded-xl border border-gray-700/50 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Total Utilisateurs</p>
                    <p class="text-3xl font-bold text-white mt-1">{{ number_format($stats['total_users']) }}</p>
                    <p class="text-xs text-success mt-2">
                        <i class="fa-solid fa-arrow-up mr-1"></i>
                        +{{ $stats['new_users_today'] }} aujourd'hui
                    </p>
                </div>
                <div class="w-14 h-14 rounded-xl bg-blue-500/20 flex items-center justify-center">
                    <i class="fa-solid fa-users text-2xl text-blue-400"></i>
                </div>
            </div>
        </div>
        
        <!-- Premium Users -->
        <div class="stat-card bg-dark-100 rounded-xl border border-gray-700/50 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Utilisateurs Premium</p>
                    <p class="text-3xl font-bold text-white mt-1">{{ number_format($stats['premium_users']) }}</p>
                    <p class="text-xs text-gray-400 mt-2">
                        {{ $stats['total_users'] > 0 ? round(($stats['premium_users'] / $stats['total_users']) * 100, 1) : 0 }}% du total
                    </p>
                </div>
                <div class="w-14 h-14 rounded-xl bg-yellow-500/20 flex items-center justify-center">
                    <i class="fa-solid fa-crown text-2xl text-yellow-400"></i>
                </div>
            </div>
        </div>
        
        <!-- Pronostics -->
        <div class="stat-card bg-dark-100 rounded-xl border border-gray-700/50 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Taux de Réussite</p>
                    <p class="text-3xl font-bold text-white mt-1">{{ $predictionStats['win_rate'] }}%</p>
                    <p class="text-xs text-gray-400 mt-2">
                        {{ $predictionStats['won'] }}/{{ $predictionStats['won'] + $predictionStats['lost'] }} gagnés
                    </p>
                </div>
                <div class="w-14 h-14 rounded-xl bg-green-500/20 flex items-center justify-center">
                    <i class="fa-solid fa-chart-line text-2xl text-green-400"></i>
                </div>
            </div>
        </div>
        
        <!-- Revenue -->
        <div class="stat-card bg-dark-100 rounded-xl border border-gray-700/50 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Revenus du Mois</p>
                    <p class="text-3xl font-bold text-white mt-1">{{ number_format($revenueStats['monthly_revenue']) }} FCFA</p>
                    <p class="text-xs text-success mt-2">
                        {{ $revenueStats['total_subscriptions'] }} abonnements
                    </p>
                </div>
                <div class="w-14 h-14 rounded-xl bg-purple-500/20 flex items-center justify-center">
                    <i class="fa-solid fa-wallet text-2xl text-purple-400"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Users Chart -->
        <div class="bg-dark-100 rounded-xl border border-gray-700/50 p-6 overflow-hidden">
            <h3 class="text-lg font-semibold text-white mb-4">
                <i class="fa-solid fa-user-plus mr-2 text-primary"></i>
                Inscriptions (7 derniers jours)
            </h3>
            <div class="chart-container">
                <canvas id="usersChart"></canvas>
            </div>
        </div>
        
        <!-- Predictions Chart -->
        <div class="bg-dark-100 rounded-xl border border-gray-700/50 p-6 overflow-hidden">
            <h3 class="text-lg font-semibold text-white mb-4">
                <i class="fa-solid fa-futbol mr-2 text-success"></i>
                Pronostics (7 derniers jours)
            </h3>
            <div class="chart-container">
                <canvas id="predictionsChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Quick Stats Row -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Pronostics Status -->
        <div class="bg-dark-100 rounded-xl border border-gray-700/50 p-6">
            <h3 class="text-lg font-semibold text-white mb-4">
                <i class="fa-solid fa-futbol mr-2 text-primary"></i>
                État des Pronostics
            </h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-gray-400">En attente</span>
                    <span class="px-3 py-1 rounded-full bg-warning/20 text-warning text-sm font-medium">
                        {{ $predictionStats['pending'] }}
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-400">Gagnés</span>
                    <span class="px-3 py-1 rounded-full bg-success/20 text-success text-sm font-medium">
                        {{ $predictionStats['won'] }}
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-400">Perdus</span>
                    <span class="px-3 py-1 rounded-full bg-danger/20 text-danger text-sm font-medium">
                        {{ $predictionStats['lost'] }}
                    </span>
                </div>
                <div class="pt-3 border-t border-gray-700">
                    <div class="flex justify-between items-center">
                        <span class="text-white font-medium">Total</span>
                        <span class="text-white font-bold">{{ $predictionStats['total'] }}</span>
                    </div>
                </div>
            </div>
            <a href="{{ route('admin.predictions.index') }}" class="mt-4 block text-center text-sm text-primary hover:text-primary/80 transition">
                Voir tous les pronostics →
            </a>
        </div>
        
        <!-- Affiliations -->
        <div class="bg-dark-100 rounded-xl border border-gray-700/50 p-6">
            <h3 class="text-lg font-semibold text-white mb-4">
                <i class="fa-solid fa-handshake mr-2 text-secondary"></i>
                Affiliations
            </h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-gray-400">Total</span>
                    <span class="text-white font-medium">{{ $affiliationStats['total_bonuses'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-400">Vérifiées</span>
                    <span class="px-3 py-1 rounded-full bg-success/20 text-success text-sm font-medium">
                        {{ $affiliationStats['verified_bonuses'] }}
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-400">En attente</span>
                    <span class="px-3 py-1 rounded-full bg-warning/20 text-warning text-sm font-medium">
                        {{ $affiliationStats['pending_bonuses'] }}
                    </span>
                </div>
            </div>
            <a href="{{ route('admin.affiliates.index') }}" class="mt-4 block text-center text-sm text-primary hover:text-primary/80 transition">
                Gérer les affiliations →
            </a>
        </div>
        
        <!-- Referrals -->
        <div class="bg-dark-100 rounded-xl border border-gray-700/50 p-6">
            <h3 class="text-lg font-semibold text-white mb-4">
                <i class="fa-solid fa-gift mr-2 text-success"></i>
                Parrainages
            </h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-gray-400">Total</span>
                    <span class="text-white font-medium">{{ $referralStats['total'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-400">Ce mois</span>
                    <span class="px-3 py-1 rounded-full bg-success/20 text-success text-sm font-medium">
                        {{ $referralStats['this_month'] }}
                    </span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bottom Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Activity -->
        <div class="bg-dark-100 rounded-xl border border-gray-700/50 p-6">
            <h3 class="text-lg font-semibold text-white mb-4">
                <i class="fa-solid fa-clock-rotate-left mr-2 text-primary"></i>
                Activité Récente
            </h3>
            <div class="space-y-4 max-h-80 overflow-y-auto">
                @forelse($recentActivities as $activity)
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-full {{ $activity['color'] }} flex items-center justify-center flex-shrink-0">
                            <i class="fa-solid {{ $activity['icon'] }} text-white text-sm"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-gray-200 text-sm">{{ $activity['message'] }}</p>
                            <p class="text-gray-500 text-xs mt-1">{{ $activity['time']->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-400 text-center py-4">Aucune activité récente</p>
                @endforelse
            </div>
        </div>
        
        <!-- Recent Feedbacks -->
        <div class="bg-dark-100 rounded-xl border border-gray-700/50 p-6">
            <h3 class="text-lg font-semibold text-white mb-4">
                <i class="fa-solid fa-comment-dots mr-2 text-warning"></i>
                Feedbacks Récents
            </h3>
            <div class="space-y-4 max-h-80 overflow-y-auto">
                @forelse($recentFeedbacks as $feedback)
                    <div class="p-4 bg-gray-800/50 rounded-lg">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-sm font-medium text-white">{{ $feedback->user->name ?? 'Anonyme' }}</span>
                            <span class="text-xs text-gray-500">•</span>
                            <span class="text-xs text-gray-500">{{ $feedback->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="text-gray-300 text-sm line-clamp-2">{{ $feedback->message }}</p>
                        @if($feedback->type)
                            <span class="inline-block mt-2 px-2 py-1 rounded text-xs 
                                {{ $feedback->type === 'bug' ? 'bg-danger/20 text-danger' : 'bg-primary/20 text-primary' }}">
                                {{ $feedback->type }}
                            </span>
                        @endif
                    </div>
                @empty
                    <p class="text-gray-400 text-center py-4">Aucun feedback récent</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Users Chart
    const usersCtx = document.getElementById('usersChart').getContext('2d');
    const usersChart = new Chart(usersCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode(array_column($userChartData, 'date')) !!},
            datasets: [{
                label: 'Nouveaux utilisateurs',
                data: {!! json_encode(array_column($userChartData, 'count')) !!},
                borderColor: '#6366F1',
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#6366F1',
                pointRadius: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            layout: {
                padding: {
                    left: 5,
                    right: 5,
                    top: 5,
                    bottom: 5
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    enabled: true
                }
            },
            scales: {
                x: {
                    grid: {
                        color: 'rgba(255,255,255,0.05)',
                        drawBorder: false,
                        display: true
                    },
                    ticks: {
                        color: '#9CA3AF',
                        maxRotation: 0,
                        autoSkip: true,
                        maxTicksLimit: 7
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(255,255,255,0.05)',
                        drawBorder: false,
                        display: true
                    },
                    ticks: {
                        color: '#9CA3AF',
                        maxTicksLimit: 5
                    }
                }
            }
        }
    });
    
    // Predictions Chart
    const predictionsCtx = document.getElementById('predictionsChart').getContext('2d');
    const predictionsChart = new Chart(predictionsCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_column($predictionChartData, 'date')) !!},
            datasets: [
                {
                    label: 'Gagnés',
                    data: {!! json_encode(array_column($predictionChartData, 'won')) !!},
                    backgroundColor: '#10B981',
                    borderRadius: 4,
                },
                {
                    label: 'Perdus',
                    data: {!! json_encode(array_column($predictionChartData, 'lost')) !!},
                    backgroundColor: '#EF4444',
                    borderRadius: 4,
                },
                {
                    label: 'En attente',
                    data: {!! json_encode(array_column($predictionChartData, 'pending')) !!},
                    backgroundColor: '#F59E0B',
                    borderRadius: 4,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            layout: {
                padding: {
                    left: 5,
                    right: 5,
                    top: 5,
                    bottom: 5
                }
            },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: '#9CA3AF',
                        padding: 15,
                        boxWidth: 12,
                        boxHeight: 12
                    }
                },
                tooltip: {
                    enabled: true
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false,
                        drawBorder: false
                    },
                    ticks: {
                        color: '#9CA3AF',
                        maxRotation: 0,
                        autoSkip: true,
                        maxTicksLimit: 7
                    }
                },
                y: {
                    beginAtZero: true,
                    stacked: true,
                    grid: {
                        color: 'rgba(255,255,255,0.05)',
                        drawBorder: false,
                        display: true
                    },
                    ticks: {
                        color: '#9CA3AF',
                        maxTicksLimit: 5
                    }
                }
            }
        }
    });
    
    // Forcer le redimensionnement des graphiques après chargement et lors du resize
    window.addEventListener('resize', function() {
        if (usersChart) usersChart.resize();
        if (predictionsChart) predictionsChart.resize();
    });
    
    // Redimensionner après le chargement complet de la page
    window.addEventListener('load', function() {
        setTimeout(function() {
            if (usersChart) usersChart.resize();
            if (predictionsChart) predictionsChart.resize();
        }, 100);
    });
    
    // Redimensionner après que le DOM soit complètement chargé
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            if (usersChart) usersChart.resize();
            if (predictionsChart) predictionsChart.resize();
        }, 200);
    });
</script>
@endpush

