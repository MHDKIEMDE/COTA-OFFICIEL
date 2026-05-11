<script setup>
import { ref, computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    predictions: {
        type: Array,
        default: () => []
    },
    stats: {
        type: Object,
        default: () => ({
            total: 0,
            won: 0,
            lost: 0,
            pending: 0,
            win_rate: 0
        })
    }
});

const page = usePage();
const isAuthenticated = computed(() => !!page.props.auth?.user);

const selectedFilter = ref('all');

const filteredPredictions = computed(() => {
    if (selectedFilter.value === 'all') return props.predictions;
    return props.predictions.filter(p => p.status === selectedFilter.value);
});

const formatDate = (datetime) => {
    return new Date(datetime).toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    });
};

const formatTime = (datetime) => {
    return new Date(datetime).toLocaleTimeString('fr-FR', {
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <Head title="Historique" />

    <AppLayout>
        <template #header>
            <i class="bi bi-clock-history me-2"></i>Historique
        </template>

        <!-- Auth Required Message -->
        <div class="card glass text-center p-5" v-if="!isAuthenticated">
            <i class="bi bi-person-lock display-1 text-primary mb-3"></i>
            <h4 class="text-white mb-2">Connexion requise</h4>
            <p class="text-muted-light mb-4">
                Connectez-vous pour accéder à votre historique de pronostics.
            </p>
            <div class="d-flex justify-content-center gap-3">
                <Link href="/login" class="btn btn-primary">
                    <i class="bi bi-box-arrow-in-right me-2"></i>
                    Se connecter
                </Link>
                <Link href="/register" class="btn btn-outline-light">
                    Créer un compte
                </Link>
            </div>
        </div>

        <template v-else>
            <!-- Stats Overview -->
            <div class="row g-4 mb-4">
                <div class="col-6 col-lg-3">
                    <div class="stat-card text-center">
                        <p class="stat-value text-white">{{ stats.total }}</p>
                        <p class="stat-label">Total</p>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="stat-card text-center">
                        <p class="stat-value text-success">{{ stats.won }}</p>
                        <p class="stat-label">Gagnés</p>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="stat-card text-center">
                        <p class="stat-value text-danger">{{ stats.lost }}</p>
                        <p class="stat-label">Perdus</p>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="stat-card text-center">
                        <p class="stat-value text-primary">{{ stats.win_rate }}%</p>
                        <p class="stat-label">Taux de réussite</p>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card glass mb-4">
                <div class="card-body py-3">
                    <div class="d-flex flex-wrap gap-2">
                        <button class="btn btn-sm" 
                                :class="selectedFilter === 'all' ? 'btn-primary' : 'btn-outline-secondary'"
                                @click="selectedFilter = 'all'">
                            Tous ({{ stats.total }})
                        </button>
                        <button class="btn btn-sm" 
                                :class="selectedFilter === 'won' ? 'btn-success' : 'btn-outline-success'"
                                @click="selectedFilter = 'won'">
                            <i class="bi bi-check-circle me-1"></i>
                            Gagnés ({{ stats.won }})
                        </button>
                        <button class="btn btn-sm" 
                                :class="selectedFilter === 'lost' ? 'btn-danger' : 'btn-outline-danger'"
                                @click="selectedFilter = 'lost'">
                            <i class="bi bi-x-circle me-1"></i>
                            Perdus ({{ stats.lost }})
                        </button>
                        <button class="btn btn-sm" 
                                :class="selectedFilter === 'pending' ? 'btn-warning' : 'btn-outline-warning'"
                                @click="selectedFilter = 'pending'">
                            <i class="bi bi-clock me-1"></i>
                            En attente ({{ stats.pending }})
                        </button>
                    </div>
                </div>
            </div>

            <!-- Predictions List -->
            <div class="card glass" v-if="filteredPredictions.length > 0">
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Match</th>
                                <th>Compétition</th>
                                <th>Pronostic</th>
                                <th>Cote</th>
                                <th>Confiance</th>
                                <th>Résultat</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="prediction in filteredPredictions" :key="prediction.id">
                                <td>
                                    <Link :href="`/predictions/${prediction.id}`" class="text-decoration-none">
                                        <span class="text-white">{{ prediction.home_team }}</span>
                                        <span class="text-muted mx-1">vs</span>
                                        <span class="text-white">{{ prediction.away_team }}</span>
                                    </Link>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ prediction.competition }}</span>
                                </td>
                                <td class="fw-medium">{{ prediction.bet_type }}</td>
                                <td>
                                    <span class="text-success fw-bold">{{ prediction.odds?.toFixed(2) }}</span>
                                </td>
                                <td>
                                    <div class="star-rating">
                                        <i v-for="i in 4" :key="i" 
                                           class="bi star small" 
                                           :class="i <= prediction.confidence ? 'bi-star-fill filled' : 'bi-star'"></i>
                                    </div>
                                </td>
                                <td>
                                    <span class="status-badge" :class="prediction.status">
                                        <i v-if="prediction.status === 'won'" class="bi bi-check-circle-fill"></i>
                                        <i v-else-if="prediction.status === 'lost'" class="bi bi-x-circle-fill"></i>
                                        <i v-else class="bi bi-clock"></i>
                                        {{ prediction.status === 'won' ? 'Gagné' : prediction.status === 'lost' ? 'Perdu' : 'En attente' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted-light small">
                                        {{ formatDate(prediction.match_date) }}<br>
                                        {{ formatTime(prediction.match_date) }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Empty State -->
            <div class="empty-state" v-else>
                <div class="empty-icon">
                    <i class="bi bi-inbox"></i>
                </div>
                <h5 class="text-white">Aucun pronostic trouvé</h5>
                <p>Vous n'avez pas encore de pronostics dans votre historique.</p>
                <Link href="/predictions" class="btn btn-primary">
                    <i class="bi bi-trophy me-2"></i>
                    Voir les pronostics
                </Link>
            </div>
        </template>
    </AppLayout>
</template>

<style scoped>
.table {
    --bs-table-bg: transparent;
    --bs-table-hover-bg: rgba(255, 255, 255, 0.05);
}

.table th {
    border-bottom-color: rgba(255, 255, 255, 0.1);
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.05em;
    color: #94A3B8;
}

.table td {
    border-bottom-color: rgba(255, 255, 255, 0.05);
    vertical-align: middle;
}
</style>

