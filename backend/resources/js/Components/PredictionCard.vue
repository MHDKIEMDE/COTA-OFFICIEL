<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    prediction: {
        type: Object,
        required: true
    },
    isLive: {
        type: Boolean,
        default: false
    }
});

const page = usePage();
const isPremium = computed(() => page.props.auth?.user?.is_premium);

// Check if this is a premium prediction (3-4 stars)
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
    if (props.isLive) return 'live';
    return props.prediction.status;
});

const formatTime = (datetime) => {
    const date = new Date(datetime);
    return date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
};

const formatDate = (datetime) => {
    const date = new Date(datetime);
    return date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' });
};
</script>

<template>
    <div class="prediction-card h-100" :class="{ 'locked': isLocked }">
        <!-- Header -->
        <div class="card-header d-flex align-items-center justify-content-between">
            <div class="competition-badge">
                <img v-if="prediction.competition_logo" :src="prediction.competition_logo" :alt="prediction.competition">
                <i v-else class="bi bi-trophy"></i>
                <span>{{ prediction.competition }}</span>
            </div>
            <span class="status-badge" :class="statusClass">
                <i v-if="isLive" class="bi bi-broadcast"></i>
                <i v-else-if="prediction.status === 'won'" class="bi bi-check-circle-fill"></i>
                <i v-else-if="prediction.status === 'lost'" class="bi bi-x-circle-fill"></i>
                <i v-else class="bi bi-clock"></i>
                {{ isLive ? 'LIVE' : prediction.status === 'won' ? 'Gagné' : prediction.status === 'lost' ? 'Perdu' : 'En attente' }}
            </span>
        </div>

        <!-- Body -->
        <div class="card-body">
            <!-- Teams -->
            <div class="d-flex align-items-center justify-content-between mb-3">
                <!-- Home Team -->
                <div class="text-center" style="flex: 1;">
                    <img v-if="prediction.home_team_logo" 
                         :src="prediction.home_team_logo" 
                         :alt="prediction.home_team"
                         class="team-logo mb-2">
                    <div v-else class="team-logo mb-2 mx-auto bg-secondary bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center">
                        <i class="bi bi-shield-fill text-muted"></i>
                    </div>
                    <p class="mb-0 text-white fw-medium small text-truncate">{{ prediction.home_team }}</p>
                </div>

                <!-- VS / Score -->
                <div class="text-center px-3">
                    <template v-if="isLive || prediction.status !== 'pending'">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fs-4 fw-bold text-white">{{ prediction.home_score ?? 0 }}</span>
                            <span class="text-muted">-</span>
                            <span class="fs-4 fw-bold text-white">{{ prediction.away_score ?? 0 }}</span>
                        </div>
                        <small class="text-muted-light" v-if="isLive">{{ prediction.match_minute }}'</small>
                    </template>
                    <template v-else>
                        <span class="vs-badge">VS</span>
                        <div class="mt-2">
                            <small class="text-muted-light d-block">{{ formatDate(prediction.match_date) }}</small>
                            <small class="text-white fw-medium">{{ formatTime(prediction.match_date) }}</small>
                        </div>
                    </template>
                </div>

                <!-- Away Team -->
                <div class="text-center" style="flex: 1;">
                    <img v-if="prediction.away_team_logo" 
                         :src="prediction.away_team_logo" 
                         :alt="prediction.away_team"
                         class="team-logo mb-2">
                    <div v-else class="team-logo mb-2 mx-auto bg-secondary bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center">
                        <i class="bi bi-shield-fill text-muted"></i>
                    </div>
                    <p class="mb-0 text-white fw-medium small text-truncate">{{ prediction.away_team }}</p>
                </div>
            </div>

            <!-- Prediction Info -->
            <div class="border-top border-secondary pt-3" v-if="!isLocked">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted-light small">Pronostic</span>
                    <span class="fw-semibold text-white">{{ prediction.bet_type }}</span>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted-light small">Cote</span>
                    <span class="odds-badge">{{ prediction.odds?.toFixed(2) }}</span>
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <span class="text-muted-light small">Confiance</span>
                    <div class="d-flex align-items-center gap-2">
                        <div class="star-rating">
                            <i v-for="i in 4" :key="i" 
                               class="bi star" 
                               :class="i <= prediction.confidence ? 'bi-star-fill filled' : 'bi-star'"></i>
                        </div>
                        <span class="confidence-badge" :class="confidenceClass">
                            {{ confidenceLabel }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Locked State -->
            <div class="border-top border-secondary pt-3 text-center" v-else>
                <div class="py-3">
                    <i class="bi bi-lock-fill fs-2 text-warning mb-2 d-block"></i>
                    <p class="text-muted-light small mb-2">Pronostic Premium</p>
                    <Link href="/subscription" class="btn btn-warning btn-sm">
                        <i class="bi bi-star-fill me-1"></i>
                        Débloquer
                    </Link>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="card-footer bg-transparent border-top border-secondary">
            <Link :href="`/predictions/${prediction.id}`" 
                  class="btn btn-outline-primary btn-sm w-100"
                  :class="{ 'disabled': isLocked }">
                <i class="bi bi-eye me-1"></i>
                Voir les détails
            </Link>
        </div>
    </div>
</template>

<style scoped>
.prediction-card.locked {
    position: relative;
}

.prediction-card.locked::after {
    content: '';
    position: absolute;
    inset: 0;
    background: rgba(15, 23, 42, 0.5);
    backdrop-filter: blur(2px);
    border-radius: inherit;
    pointer-events: none;
    z-index: 1;
}

.prediction-card.locked .card-body > :last-child {
    position: relative;
    z-index: 2;
    background: rgba(30, 41, 59, 0.95);
    margin: -1rem;
    padding: 1rem;
    border-radius: 0 0 0.75rem 0.75rem;
}
</style>

