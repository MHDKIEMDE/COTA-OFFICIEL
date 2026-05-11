<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PredictionCard from '@/Components/PredictionCard.vue';
import StatCard from '@/Components/StatCard.vue';

const props = defineProps({
    predictions: {
        type: Array,
        default: () => []
    },
    stats: {
        type: Object,
        default: () => ({
            total_predictions: 0,
            win_rate: 0,
            today_count: 0,
            premium_count: 0
        })
    }
});

const page = usePage();
const user = computed(() => page.props.auth?.user);
const isPremium = computed(() => user.value?.is_premium);

const todayPredictions = computed(() => {
    return props.predictions.filter(p => p.status === 'pending').slice(0, 6);
});

const livePredictions = computed(() => {
    return props.predictions.filter(p => p.status === 'live');
});
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout>
        <template #header>
            <i class="bi bi-house-door me-2"></i>Dashboard
        </template>

        <!-- Welcome Banner -->
        <div class="card glass mb-4 border-0 overflow-hidden">
            <div class="card-body position-relative p-4">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h2 class="text-white fw-bold mb-2">
                            Bienvenue{{ user ? `, ${user.name}` : '' }} ! 👋
                        </h2>
                        <p class="text-muted-light mb-3">
                            Découvrez les meilleurs pronostics du jour sélectionnés par notre algorithme intelligent.
                        </p>
                        <div class="d-flex gap-3 flex-wrap">
                            <Link href="/predictions" class="btn btn-primary">
                                <i class="bi bi-trophy me-2"></i>
                                Voir tous les pronostics
                            </Link>
                            <Link href="/subscription" class="btn btn-outline-light" v-if="!isPremium">
                                <i class="bi bi-star me-2"></i>
                                Passer Premium
                            </Link>
                        </div>
                    </div>
                    <div class="col-lg-4 d-none d-lg-block text-end">
                        <i class="bi bi-trophy-fill" style="font-size: 8rem; opacity: 0.1; color: #E91E8C;"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Row -->
        <div class="row g-4 mb-4">
            <div class="col-6 col-lg-3">
                <StatCard
                    title="Pronostics Aujourd'hui"
                    :value="stats.today_count"
                    icon="bi-calendar-check"
                    color="primary"
                />
            </div>
            <div class="col-6 col-lg-3">
                <StatCard
                    title="Taux de Réussite"
                    :value="`${stats.win_rate}%`"
                    icon="bi-graph-up-arrow"
                    color="success"
                />
            </div>
            <div class="col-6 col-lg-3">
                <StatCard
                    title="Total Pronostics"
                    :value="stats.total_predictions"
                    icon="bi-collection"
                    color="info"
                />
            </div>
            <div class="col-6 col-lg-3">
                <StatCard
                    title="Premium"
                    :value="stats.premium_count"
                    icon="bi-star-fill"
                    color="warning"
                    :badge="isPremium ? 'Actif' : null"
                />
            </div>
        </div>

        <!-- Live Matches -->
        <div class="mb-4" v-if="livePredictions.length > 0">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h4 class="text-white mb-0">
                    <i class="bi bi-broadcast text-danger me-2"></i>
                    Matchs en Direct
                </h4>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-lg-4" v-for="prediction in livePredictions" :key="prediction.id">
                    <PredictionCard :prediction="prediction" :is-live="true" />
                </div>
            </div>
        </div>

        <!-- Today's Predictions -->
        <div class="mb-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h4 class="text-white mb-0">
                    <i class="bi bi-lightning-charge text-warning me-2"></i>
                    Pronostics du Jour
                </h4>
                <Link href="/predictions" class="btn btn-link text-primary text-decoration-none">
                    Voir tout <i class="bi bi-arrow-right ms-1"></i>
                </Link>
            </div>

            <div class="row g-4" v-if="todayPredictions.length > 0">
                <div class="col-md-6 col-xl-4" v-for="prediction in todayPredictions" :key="prediction.id">
                    <PredictionCard :prediction="prediction" />
                </div>
            </div>

            <!-- Empty State -->
            <div class="empty-state" v-else>
                <div class="empty-icon">
                    <i class="bi bi-calendar-x"></i>
                </div>
                <h5 class="text-white">Aucun pronostic disponible</h5>
                <p>Les pronostics du jour seront disponibles prochainement.</p>
                <Link href="/predictions" class="btn btn-primary">
                    <i class="bi bi-clock-history me-2"></i>
                    Voir l'historique
                </Link>
            </div>
        </div>

        <!-- Premium CTA (for non-premium users) -->
        <div class="card bg-gradient-primary border-0 text-white" v-if="!isPremium">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="rounded-circle bg-white bg-opacity-25 p-3">
                                <i class="bi bi-star-fill fs-4"></i>
                            </div>
                            <div>
                                <h4 class="mb-1">Passez à Premium</h4>
                                <p class="mb-0 opacity-75">Débloquez toutes les prédictions haute confiance</p>
                            </div>
                        </div>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2"><i class="bi bi-check-circle-fill me-2"></i>Accès à tous les pronostics 3-4 étoiles</li>
                            <li class="mb-2"><i class="bi bi-check-circle-fill me-2"></i>Alertes en temps réel</li>
                            <li><i class="bi bi-check-circle-fill me-2"></i>Statistiques détaillées</li>
                        </ul>
                    </div>
                    <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                        <Link href="/subscription" class="btn btn-light btn-lg">
                            <i class="bi bi-rocket-takeoff me-2"></i>
                            Devenir Premium
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

