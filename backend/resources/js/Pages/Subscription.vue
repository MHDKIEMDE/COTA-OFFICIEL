<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    plans: {
        type: Array,
        default: () => [
            {
                id: 'weekly',
                name: 'Hebdomadaire',
                price: 2000,
                duration: '7 jours',
                features: [
                    'Tous les pronostics 3-4 étoiles',
                    'Alertes en temps réel',
                    'Analyses détaillées'
                ]
            },
            {
                id: 'monthly',
                name: 'Mensuel',
                price: 5000,
                duration: '30 jours',
                popular: true,
                savings: '30%',
                features: [
                    'Tous les pronostics 3-4 étoiles',
                    'Alertes en temps réel',
                    'Analyses détaillées',
                    'Statistiques avancées',
                    'Support prioritaire'
                ]
            },
            {
                id: 'yearly',
                name: 'Annuel',
                price: 40000,
                duration: '365 jours',
                savings: '50%',
                features: [
                    'Tous les avantages Mensuel',
                    'Accès aux paris combinés',
                    'Badge membre VIP',
                    'Support WhatsApp dédié'
                ]
            }
        ]
    },
    currentSubscription: {
        type: Object,
        default: null
    }
});

const page = usePage();
const user = computed(() => page.props.auth?.user);
const isAuthenticated = computed(() => !!user.value);
const isPremium = computed(() => user.value?.is_premium);

const selectedPlan = ref(null);
const selectedPayment = ref('wave');
const loading = ref(false);

const paymentMethods = [
    { id: 'wave', name: 'Wave', icon: 'bi-phone', color: '#1DC9FF' },
    { id: 'orange', name: 'Orange Money', icon: 'bi-phone', color: '#FF6600' },
    { id: 'mtn', name: 'MTN MoMo', icon: 'bi-phone', color: '#FFCC00' },
    { id: 'moov', name: 'Moov Money', icon: 'bi-phone', color: '#0066CC' }
];

const selectPlan = (plan) => {
    selectedPlan.value = plan;
};

const form = useForm({
    plan_id: '',
    payment_method: 'wave'
});

const subscribe = () => {
    if (!selectedPlan.value) return;
    
    form.plan_id = selectedPlan.value.id;
    form.payment_method = selectedPayment.value;
    
    form.post('/subscriptions/purchase', {
        preserveScroll: true
    });
};

const formatPrice = (price) => {
    return new Intl.NumberFormat('fr-FR').format(price);
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
};
</script>

<template>
    <Head title="Abonnement" />

    <AppLayout>
        <template #header>
            <i class="bi bi-credit-card me-2"></i>Abonnement
        </template>

        <!-- Current Subscription (if premium) -->
        <div class="card bg-gradient-primary border-0 text-white mb-4" v-if="isPremium && currentSubscription">
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="rounded-circle bg-white bg-opacity-25 p-3">
                                <i class="bi bi-star-fill fs-4"></i>
                            </div>
                            <div>
                                <h4 class="mb-0">Vous êtes Premium !</h4>
                                <p class="mb-0 opacity-75">Profitez de tous les avantages</p>
                            </div>
                        </div>
                        <p class="mb-0">
                            <i class="bi bi-calendar me-2"></i>
                            Expire le : <strong>{{ formatDate(user.subscription_expires_at) }}</strong>
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <span class="premium-badge fs-5">
                            <i class="bi bi-check-circle-fill me-1"></i>
                            Actif
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page Title -->
        <div class="text-center mb-5">
            <h2 class="text-white fw-bold mb-2">Choisissez votre formule</h2>
            <p class="text-muted-light">
                Débloquez l'accès complet aux pronostics haute confiance
            </p>
        </div>

        <!-- Plans Grid -->
        <div class="row g-4 mb-5">
            <div class="col-md-4" v-for="plan in plans" :key="plan.id">
                <div class="subscription-card h-100" 
                     :class="{ 'popular': plan.popular, 'selected': selectedPlan?.id === plan.id }"
                     @click="selectPlan(plan)"
                     style="cursor: pointer;">
                    
                    <!-- Savings Badge -->
                    <span v-if="plan.savings" 
                          class="badge bg-success position-absolute"
                          style="top: 1rem; right: 1rem;">
                        -{{ plan.savings }}
                    </span>

                    <h5 class="text-white mb-3">{{ plan.name }}</h5>
                    
                    <div class="price mb-1">
                        {{ formatPrice(plan.price) }}
                        <small>FCFA</small>
                    </div>
                    <p class="text-muted-light mb-4">{{ plan.duration }}</p>

                    <ul class="list-unstyled text-start mb-4">
                        <li v-for="feature in plan.features" :key="feature" class="mb-2">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            <span class="text-muted-light">{{ feature }}</span>
                        </li>
                    </ul>

                    <button class="btn w-100"
                            :class="selectedPlan?.id === plan.id ? 'btn-primary' : 'btn-outline-primary'">
                        <i class="bi bi-check-lg me-1" v-if="selectedPlan?.id === plan.id"></i>
                        {{ selectedPlan?.id === plan.id ? 'Sélectionné' : 'Choisir' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Payment Section -->
        <div class="card glass" v-if="selectedPlan">
            <div class="card-header">
                <h5 class="mb-0 text-white">
                    <i class="bi bi-wallet2 me-2 text-primary"></i>
                    Mode de paiement
                </h5>
            </div>
            <div class="card-body">
                <!-- Auth Required -->
                <div class="text-center py-4" v-if="!isAuthenticated">
                    <p class="text-muted-light mb-3">Connectez-vous pour finaliser votre achat</p>
                    <Link href="/login" class="btn btn-primary">
                        <i class="bi bi-box-arrow-in-right me-2"></i>
                        Se connecter
                    </Link>
                </div>

                <template v-else>
                    <!-- Payment Methods -->
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-md-3" v-for="method in paymentMethods" :key="method.id">
                            <div class="card h-100" 
                                 :class="{ 'border-primary': selectedPayment === method.id, 'bg-dark': selectedPayment !== method.id }"
                                 style="cursor: pointer;"
                                 @click="selectedPayment = method.id">
                                <div class="card-body text-center py-3">
                                    <i :class="['bi', method.icon, 'fs-3 mb-2 d-block']" 
                                       :style="{ color: method.color }"></i>
                                    <span class="small" :class="selectedPayment === method.id ? 'text-white' : 'text-muted-light'">
                                        {{ method.name }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="bg-dark rounded p-4 mb-4">
                        <h6 class="text-white mb-3">Récapitulatif</h6>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted-light">Formule</span>
                            <span class="text-white">{{ selectedPlan.name }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted-light">Durée</span>
                            <span class="text-white">{{ selectedPlan.duration }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted-light">Paiement</span>
                            <span class="text-white">{{ paymentMethods.find(m => m.id === selectedPayment)?.name }}</span>
                        </div>
                        <hr class="border-secondary">
                        <div class="d-flex justify-content-between">
                            <span class="text-white fw-bold">Total</span>
                            <span class="text-primary fs-4 fw-bold">{{ formatPrice(selectedPlan.price) }} FCFA</span>
                        </div>
                    </div>

                    <!-- Error Message -->
                    <div class="alert alert-danger" v-if="form.errors.error">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        {{ form.errors.error }}
                    </div>

                    <!-- Submit -->
                    <button class="btn btn-primary btn-lg w-100"
                            :disabled="form.processing"
                            @click="subscribe">
                        <span v-if="form.processing" class="spinner-border spinner-border-sm me-2"></span>
                        <i v-else class="bi bi-lock-fill me-2"></i>
                        Payer {{ formatPrice(selectedPlan.price) }} FCFA
                    </button>

                    <p class="text-center text-muted-light small mt-3 mb-0">
                        <i class="bi bi-shield-check me-1"></i>
                        Paiement sécurisé via Paydunya
                    </p>
                </template>
            </div>
        </div>

        <!-- Features Section -->
        <div class="mt-5">
            <h4 class="text-white text-center mb-4">Pourquoi passer Premium ?</h4>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="rounded-circle bg-primary bg-opacity-10 p-4 d-inline-flex mb-3">
                            <i class="bi bi-trophy-fill fs-2 text-primary"></i>
                        </div>
                        <h5 class="text-white">Pronostics Premium</h5>
                        <p class="text-muted-light">Accès exclusif aux prédictions 3-4 étoiles avec un taux de réussite supérieur à 75%</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="rounded-circle bg-success bg-opacity-10 p-4 d-inline-flex mb-3">
                            <i class="bi bi-bell-fill fs-2 text-success"></i>
                        </div>
                        <h5 class="text-white">Alertes Temps Réel</h5>
                        <p class="text-muted-light">Notifications instantanées pour ne jamais manquer une opportunité</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="rounded-circle bg-warning bg-opacity-10 p-4 d-inline-flex mb-3">
                            <i class="bi bi-graph-up-arrow fs-2 text-warning"></i>
                        </div>
                        <h5 class="text-white">Analyses Détaillées</h5>
                        <p class="text-muted-light">Comprenez chaque pronostic avec nos analyses approfondies</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.subscription-card {
    position: relative;
}

.subscription-card.selected {
    border-color: #E91E8C !important;
    box-shadow: 0 0 0 2px rgba(233, 30, 140, 0.3);
}
</style>

