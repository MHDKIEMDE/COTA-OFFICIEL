<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import StatCard from '@/Components/StatCard.vue';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            total_predictions: 0,
            won: 0,
            lost: 0,
            pending: 0,
            win_rate: 0,
            avg_odds: 0,
            roi: 0,
            best_competition: null,
            best_bet_type: null,
            streak: { type: 'none', count: 0 }
        })
    },
    byCompetition: {
        type: Array,
        default: () => []
    },
    byBetType: {
        type: Array,
        default: () => []
    },
    monthlyStats: {
        type: Array,
        default: () => []
    }
});

const page = usePage();
const isAuthenticated = computed(() => !!page.props.auth?.user);
const isPremium = computed(() => page.props.auth?.user?.is_premium);

const streakClass = computed(() => {
    if (props.stats.streak?.type === 'win') return 'text-success';
    if (props.stats.streak?.type === 'loss') return 'text-danger';
    return 'text-muted';
});
</script>

<template>
    <Head title="Statistiques" />

    <AppLayout>
        <template #header>
            <i class="bi bi-bar-chart-line me-2"></i>Statistiques
        </template>

        <!-- Auth Required Message -->
        <div class="card glass text-center p-5" v-if="!isAuthenticated">
            <i class="bi bi-graph-up display-1 text-primary mb-3"></i>
            <h4 class="text-white mb-2">Statistiques Personnalisées</h4>
            <p class="text-muted-light mb-4">
                Connectez-vous pour accéder à vos statistiques détaillées.
            </p>
            <div class="d-flex justify-content-center gap-3">
                <Link href="/login" class="btn btn-primary">
                    <i class="bi bi-box-arrow-in-right me-2"></i>
                    Se connecter
                </Link>
            </div>
        </div>

        <template v-else>
            <!-- Main Stats -->
            <div class="row g-4 mb-4">
                <div class="col-6 col-lg-3">
                    <StatCard
                        title="Taux de Réussite"
                        :value="`${stats.win_rate}%`"
                        icon="bi-bullseye"
                        color="success"
                    />
                </div>
                <div class="col-6 col-lg-3">
                    <StatCard
                        title="Total Pronostics"
                        :value="stats.total_predictions"
                        icon="bi-collection"
                        color="primary"
                    />
                </div>
                <div class="col-6 col-lg-3">
                    <StatCard
                        title="Cote Moyenne"
                        :value="stats.avg_odds?.toFixed(2) || '0.00'"
                        icon="bi-calculator"
                        color="info"
                    />
                </div>
                <div class="col-6 col-lg-3">
                    <StatCard
                        title="ROI"
                        :value="`${stats.roi > 0 ? '+' : ''}${stats.roi}%`"
                        icon="bi-graph-up-arrow"
                        :color="stats.roi >= 0 ? 'success' : 'danger'"
                    />
                </div>
            </div>

            <div class="row g-4">
                <!-- Performance Overview -->
                <div class="col-lg-8">
                    <div class="card glass h-100">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h5 class="mb-0 text-white">
                                <i class="bi bi-pie-chart me-2 text-primary"></i>
                                Performance Globale
                            </h5>
                        </div>
                        <div class="card-body">
                            <!-- Progress Bars -->
                            <div class="mb-4">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted-light">Gagnés</span>
                                    <span class="text-success fw-bold">{{ stats.won }}</span>
                                </div>
                                <div class="progress" style="height: 12px;">
                                    <div class="progress-bar bg-success" 
                                         :style="{ width: `${stats.total_predictions > 0 ? (stats.won / stats.total_predictions) * 100 : 0}%` }">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted-light">Perdus</span>
                                    <span class="text-danger fw-bold">{{ stats.lost }}</span>
                                </div>
                                <div class="progress" style="height: 12px;">
                                    <div class="progress-bar bg-danger" 
                                         :style="{ width: `${stats.total_predictions > 0 ? (stats.lost / stats.total_predictions) * 100 : 0}%` }">
                                    </div>
                                </div>
                            </div>

                            <div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted-light">En attente</span>
                                    <span class="text-warning fw-bold">{{ stats.pending }}</span>
                                </div>
                                <div class="progress" style="height: 12px;">
                                    <div class="progress-bar bg-warning" 
                                         :style="{ width: `${stats.total_predictions > 0 ? (stats.pending / stats.total_predictions) * 100 : 0}%` }">
                                    </div>
                                </div>
                            </div>

                            <!-- Streak -->
                            <div class="mt-4 pt-4 border-top border-secondary" v-if="stats.streak">
                                <p class="text-muted-light small mb-2">Série actuelle</p>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fs-4 fw-bold" :class="streakClass">
                                        <i class="bi" :class="stats.streak.type === 'win' ? 'bi-fire' : 'bi-snow'"></i>
                                        {{ stats.streak.count }}
                                    </span>
                                    <span class="text-muted-light">
                                        {{ stats.streak.type === 'win' ? 'victoires consécutives' : 
                                           stats.streak.type === 'loss' ? 'défaites consécutives' : 'aucune série' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="col-lg-4">
                    <div class="card glass h-100">
                        <div class="card-header">
                            <h5 class="mb-0 text-white">
                                <i class="bi bi-trophy-fill me-2 text-warning"></i>
                                Meilleure Performance
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-4" v-if="stats.best_competition">
                                <p class="text-muted-light small mb-1">Meilleure compétition</p>
                                <p class="text-white fs-5 fw-bold mb-0">{{ stats.best_competition.name }}</p>
                                <small class="text-success">{{ stats.best_competition.win_rate }}% de réussite</small>
                            </div>

                            <div v-if="stats.best_bet_type">
                                <p class="text-muted-light small mb-1">Meilleur type de pari</p>
                                <p class="text-white fs-5 fw-bold mb-0">{{ stats.best_bet_type.name }}</p>
                                <small class="text-success">{{ stats.best_bet_type.win_rate }}% de réussite</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats by Competition -->
            <div class="card glass mt-4" v-if="byCompetition.length > 0">
                <div class="card-header">
                    <h5 class="mb-0 text-white">
                        <i class="bi bi-list-ul me-2 text-primary"></i>
                        Par Compétition
                    </h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Compétition</th>
                                <th class="text-center">Total</th>
                                <th class="text-center">Gagnés</th>
                                <th class="text-center">Perdus</th>
                                <th class="text-center">Taux</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="comp in byCompetition" :key="comp.name">
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-trophy-fill text-warning"></i>
                                        <span>{{ comp.name }}</span>
                                    </div>
                                </td>
                                <td class="text-center">{{ comp.total }}</td>
                                <td class="text-center text-success">{{ comp.won }}</td>
                                <td class="text-center text-danger">{{ comp.lost }}</td>
                                <td class="text-center">
                                    <span class="badge" 
                                          :class="comp.win_rate >= 70 ? 'bg-success' : comp.win_rate >= 50 ? 'bg-warning' : 'bg-danger'">
                                        {{ comp.win_rate }}%
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Premium CTA -->
            <div class="card bg-gradient-primary border-0 text-white mt-4" v-if="!isPremium">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <h5 class="mb-2">
                                <i class="bi bi-star-fill me-2"></i>
                                Statistiques Premium
                            </h5>
                            <p class="mb-0 opacity-75">
                                Débloquez des analyses avancées, historiques détaillés et comparatifs.
                            </p>
                        </div>
                        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                            <Link href="/subscription" class="btn btn-light">
                                Passer Premium
                            </Link>
                        </div>
                    </div>
                </div>
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

