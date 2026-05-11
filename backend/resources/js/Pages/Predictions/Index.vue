<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PredictionCard from '@/Components/PredictionCard.vue';

const props = defineProps({
    predictions: {
        type: Array,
        default: () => []
    },
    competitions: {
        type: Array,
        default: () => []
    },
    filters: {
        type: Object,
        default: () => ({
            competition: null,
            date: null,
            confidence: null
        })
    }
});

const selectedCompetition = ref(props.filters.competition);
const selectedDate = ref(props.filters.date || new Date().toISOString().split('T')[0]);
const selectedConfidence = ref(props.filters.confidence);

const loading = ref(false);

const filteredPredictions = computed(() => {
    let result = props.predictions;
    
    if (selectedConfidence.value) {
        result = result.filter(p => p.confidence >= parseInt(selectedConfidence.value));
    }
    
    return result;
});

const groupedPredictions = computed(() => {
    const groups = {};
    
    filteredPredictions.value.forEach(prediction => {
        const comp = prediction.competition || 'Autre';
        if (!groups[comp]) {
            groups[comp] = [];
        }
        groups[comp].push(prediction);
    });
    
    return groups;
});

const applyFilters = () => {
    loading.value = true;
    router.get('/predictions', {
        competition: selectedCompetition.value,
        date: selectedDate.value,
        confidence: selectedConfidence.value
    }, {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => {
            loading.value = false;
        }
    });
};

const clearFilters = () => {
    selectedCompetition.value = null;
    selectedDate.value = new Date().toISOString().split('T')[0];
    selectedConfidence.value = null;
    applyFilters();
};

// Dates navigation
const goToDate = (days) => {
    const date = new Date(selectedDate.value);
    date.setDate(date.getDate() + days);
    selectedDate.value = date.toISOString().split('T')[0];
    applyFilters();
};

const formatDateHeader = (date) => {
    const d = new Date(date);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    const diff = Math.floor((d - today) / (1000 * 60 * 60 * 24));
    
    if (diff === 0) return "Aujourd'hui";
    if (diff === 1) return "Demain";
    if (diff === -1) return "Hier";
    
    return d.toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long' });
};
</script>

<template>
    <Head title="Pronostics" />

    <AppLayout>
        <template #header>
            <i class="bi bi-trophy me-2"></i>Pronostics
        </template>

        <!-- Filters Section -->
        <div class="card glass mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <!-- Date Navigation -->
                    <div class="col-12 col-md-4">
                        <label class="form-label text-muted-light small">Date</label>
                        <div class="input-group">
                            <button class="btn btn-outline-secondary" @click="goToDate(-1)">
                                <i class="bi bi-chevron-left"></i>
                            </button>
                            <input type="date" 
                                   class="form-control text-center"
                                   v-model="selectedDate"
                                   @change="applyFilters">
                            <button class="btn btn-outline-secondary" @click="goToDate(1)">
                                <i class="bi bi-chevron-right"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Competition Filter -->
                    <div class="col-6 col-md-3">
                        <label class="form-label text-muted-light small">Compétition</label>
                        <select class="form-select" v-model="selectedCompetition" @change="applyFilters">
                            <option :value="null">Toutes</option>
                            <option v-for="comp in competitions" :key="comp.id" :value="comp.id">
                                {{ comp.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Confidence Filter -->
                    <div class="col-6 col-md-3">
                        <label class="form-label text-muted-light small">Confiance min.</label>
                        <select class="form-select" v-model="selectedConfidence" @change="applyFilters">
                            <option :value="null">Toutes</option>
                            <option value="2">2+ étoiles</option>
                            <option value="3">3+ étoiles</option>
                            <option value="4">4 étoiles</option>
                        </select>
                    </div>

                    <!-- Clear Button -->
                    <div class="col-12 col-md-2">
                        <button class="btn btn-outline-secondary w-100" @click="clearFilters">
                            <i class="bi bi-x-circle me-1"></i>
                            Réinitialiser
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Date Header -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h4 class="text-white mb-0">
                <i class="bi bi-calendar-event me-2 text-primary"></i>
                {{ formatDateHeader(selectedDate) }}
            </h4>
            <span class="badge bg-primary rounded-pill">
                {{ filteredPredictions.length }} pronostic(s)
            </span>
        </div>

        <!-- Loading State -->
        <div class="loading-spinner" v-if="loading">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Chargement...</span>
            </div>
        </div>

        <!-- Predictions Grid -->
        <template v-else>
            <template v-if="Object.keys(groupedPredictions).length > 0">
                <div v-for="(preds, competition) in groupedPredictions" :key="competition" class="mb-4">
                    <!-- Competition Header -->
                    <h5 class="text-muted-light mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-trophy-fill text-warning"></i>
                        {{ competition }}
                        <span class="badge bg-secondary">{{ preds.length }}</span>
                    </h5>

                    <!-- Cards Grid -->
                    <div class="row g-4">
                        <div class="col-md-6 col-xl-4" v-for="prediction in preds" :key="prediction.id">
                            <PredictionCard :prediction="prediction" />
                        </div>
                    </div>
                </div>
            </template>

            <!-- Empty State -->
            <div class="empty-state" v-else>
                <div class="empty-icon">
                    <i class="bi bi-calendar-x"></i>
                </div>
                <h5 class="text-white">Aucun pronostic trouvé</h5>
                <p>Aucun pronostic ne correspond à vos critères de recherche.</p>
                <button class="btn btn-primary" @click="clearFilters">
                    <i class="bi bi-arrow-clockwise me-2"></i>
                    Réinitialiser les filtres
                </button>
            </div>
        </template>
    </AppLayout>
</template>

