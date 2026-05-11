<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    prediction: {
        type: Object,
        required: true
    }
});

const page = usePage();
const isPremium = computed(() => page.props.auth?.user?.is_premium);
const isPremiumPrediction = computed(() => props.prediction.confidence >= 3);
const isLocked = computed(() => isPremiumPrediction.value && !isPremium.value);

const confidenceClass = computed(() => {
    const conf = props.prediction.confidence;
    if (conf >= 4) return 'high';
    if (conf >= 3) return 'medium';
    return 'low';
});

const confidenceLabel = computed(() => {
    const conf = props.prediction.confidence;
    if (conf >= 4) return 'Très Haute';
    if (conf >= 3) return 'Haute';
    if (conf >= 2) return 'Moyenne';
    return 'Faible';
});

const statusClass = computed(() => {
    return props.prediction.status;
});

const formatDateTime = (datetime) => {
    const date = new Date(datetime);
    return date.toLocaleDateString('fr-FR', { 
        weekday: 'long', 
        day: 'numeric', 
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

// Parse analysis if it's a JSON string
const analysisItems = computed(() => {
    if (!props.prediction.analysis) return [];
    
    try {
        const analysis = typeof props.prediction.analysis === 'string' 
            ? JSON.parse(props.prediction.analysis) 
            : props.prediction.analysis;
        
        return Object.entries(analysis).map(([key, value]) => ({
            label: key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()),
            value: value
        }));
    } catch {
        return [{ label: 'Analyse', value: props.prediction.analysis }];
    }
});
</script>

<template>
    <Head :title="`${prediction.home_team} vs ${prediction.away_team}`" />

    <AppLayout>
        <template #header>
            <Link href="/predictions" class="text-decoration-none text-muted-light">
                <i class="bi bi-arrow-left me-2"></i>
            </Link>
            Détail du Pronostic
        </template>

        <div class="row g-4">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Match Card -->
                <div class="card glass mb-4">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <div class="competition-badge">
                            <img v-if="prediction.competition_logo" 
                                 :src="prediction.competition_logo" 
                                 :alt="prediction.competition">
                            <i v-else class="bi bi-trophy"></i>
                            <span>{{ prediction.competition }}</span>
                        </div>
                        <span class="status-badge" :class="statusClass">
                            <i v-if="prediction.status === 'won'" class="bi bi-check-circle-fill"></i>
                            <i v-else-if="prediction.status === 'lost'" class="bi bi-x-circle-fill"></i>
                            <i v-else class="bi bi-clock"></i>
                            {{ prediction.status === 'won' ? 'Gagné' : prediction.status === 'lost' ? 'Perdu' : 'En attente' }}
                        </span>
                    </div>

                    <div class="card-body p-4">
                        <!-- Teams -->
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <!-- Home Team -->
                            <div class="text-center" style="flex: 1;">
                                <img v-if="prediction.home_team_logo" 
                                     :src="prediction.home_team_logo" 
                                     :alt="prediction.home_team"
                                     class="mb-3"
                                     style="width: 80px; height: 80px; object-fit: contain;">
                                <div v-else class="mx-auto mb-3 bg-secondary bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center"
                                     style="width: 80px; height: 80px;">
                                    <i class="bi bi-shield-fill fs-2 text-muted"></i>
                                </div>
                                <h5 class="text-white mb-0">{{ prediction.home_team }}</h5>
                                <small class="text-muted-light">Domicile</small>
                            </div>

                            <!-- Score/VS -->
                            <div class="text-center px-4">
                                <template v-if="prediction.status !== 'pending'">
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="display-4 fw-bold text-white">{{ prediction.home_score ?? 0 }}</span>
                                        <span class="text-muted fs-4">-</span>
                                        <span class="display-4 fw-bold text-white">{{ prediction.away_score ?? 0 }}</span>
                                    </div>
                                    <small class="text-muted-light">Score Final</small>
                                </template>
                                <template v-else>
                                    <div class="vs-badge fs-5 px-4 py-2 mb-2">VS</div>
                                    <small class="text-white d-block">{{ formatDateTime(prediction.match_date) }}</small>
                                </template>
                            </div>

                            <!-- Away Team -->
                            <div class="text-center" style="flex: 1;">
                                <img v-if="prediction.away_team_logo" 
                                     :src="prediction.away_team_logo" 
                                     :alt="prediction.away_team"
                                     class="mb-3"
                                     style="width: 80px; height: 80px; object-fit: contain;">
                                <div v-else class="mx-auto mb-3 bg-secondary bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center"
                                     style="width: 80px; height: 80px;">
                                    <i class="bi bi-shield-fill fs-2 text-muted"></i>
                                </div>
                                <h5 class="text-white mb-0">{{ prediction.away_team }}</h5>
                                <small class="text-muted-light">Extérieur</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Analysis Card (if not locked) -->
                <div class="card glass" v-if="!isLocked && prediction.analysis">
                    <div class="card-header">
                        <h5 class="mb-0 text-white">
                            <i class="bi bi-graph-up me-2 text-primary"></i>
                            Analyse Détaillée
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6" v-for="item in analysisItems" :key="item.label">
                                <div class="p-3 rounded bg-dark bg-opacity-50">
                                    <p class="text-muted-light small mb-1">{{ item.label }}</p>
                                    <p class="text-white mb-0 fw-medium">{{ item.value }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Locked Content -->
                <div class="card glass text-center p-5" v-if="isLocked">
                    <i class="bi bi-lock-fill display-1 text-warning mb-3"></i>
                    <h4 class="text-white mb-2">Contenu Premium</h4>
                    <p class="text-muted-light mb-4">
                        L'analyse complète de ce pronostic est réservée aux membres Premium.
                    </p>
                    <Link href="/subscription" class="btn btn-warning btn-lg">
                        <i class="bi bi-star-fill me-2"></i>
                        Devenir Premium
                    </Link>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Prediction Info -->
                <div class="card glass mb-4">
                    <div class="card-header">
                        <h5 class="mb-0 text-white">
                            <i class="bi bi-lightbulb me-2 text-warning"></i>
                            Notre Pronostic
                        </h5>
                    </div>
                    <div class="card-body" v-if="!isLocked">
                        <div class="mb-3 pb-3 border-bottom border-secondary">
                            <p class="text-muted-light small mb-1">Type de pari</p>
                            <p class="text-white fs-5 fw-bold mb-0">{{ prediction.bet_type }}</p>
                        </div>

                        <div class="mb-3 pb-3 border-bottom border-secondary">
                            <p class="text-muted-light small mb-1">Cote estimée</p>
                            <span class="odds-badge fs-4">{{ prediction.odds?.toFixed(2) }}</span>
                        </div>

                        <div class="mb-3 pb-3 border-bottom border-secondary">
                            <p class="text-muted-light small mb-1">Niveau de confiance</p>
                            <div class="d-flex align-items-center gap-3">
                                <div class="star-rating">
                                    <i v-for="i in 4" :key="i" 
                                       class="bi star fs-5" 
                                       :class="i <= prediction.confidence ? 'bi-star-fill filled' : 'bi-star'"></i>
                                </div>
                                <span class="confidence-badge" :class="confidenceClass">
                                    {{ confidenceLabel }}
                                </span>
                            </div>
                        </div>

                        <div v-if="prediction.score">
                            <p class="text-muted-light small mb-1">Score de confiance</p>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-primary" 
                                     :style="{ width: `${prediction.score}%` }"></div>
                            </div>
                            <small class="text-muted-light">{{ prediction.score }}/100 points</small>
                        </div>
                    </div>

                    <!-- Locked State -->
                    <div class="card-body text-center py-5" v-else>
                        <i class="bi bi-lock fs-1 text-warning mb-2"></i>
                        <p class="text-muted-light mb-0">Premium requis</p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="d-grid gap-2">
                    <Link href="/predictions" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-left me-2"></i>
                        Retour aux pronostics
                    </Link>
                    
                    <button class="btn btn-outline-secondary" 
                            onclick="navigator.share?.({ title: 'Pronostic COTA', url: window.location.href })">
                        <i class="bi bi-share me-2"></i>
                        Partager
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

