<script setup>
import { ref, computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    referrals: {
        type: Array,
        default: () => []
    },
    stats: {
        type: Object,
        default: () => ({
            total_referrals: 0,
            premium_days_earned: 0,
            pending_rewards: 0
        })
    }
});

const page = usePage();
const user = computed(() => page.props.auth?.user);
const isAuthenticated = computed(() => !!user.value);

const copied = ref(false);

const referralLink = computed(() => {
    if (!user.value?.referral_code) return '';
    return `${window.location.origin}/register?ref=${user.value.referral_code}`;
});

const copyCode = () => {
    navigator.clipboard.writeText(user.value.referral_code);
    copied.value = true;
    setTimeout(() => copied.value = false, 2000);
};

const copyLink = () => {
    navigator.clipboard.writeText(referralLink.value);
    copied.value = true;
    setTimeout(() => copied.value = false, 2000);
};

const shareWhatsApp = () => {
    const text = `🎯 Rejoins COTA, l'app de pronostics football ! Utilise mon code ${user.value.referral_code} et reçois 7 jours Premium gratuits : ${referralLink.value}`;
    window.open(`https://wa.me/?text=${encodeURIComponent(text)}`, '_blank');
};

const shareTelegram = () => {
    const text = `🎯 Rejoins COTA, l'app de pronostics football ! Utilise mon code ${user.value.referral_code} et reçois 7 jours Premium gratuits !`;
    window.open(`https://t.me/share/url?url=${encodeURIComponent(referralLink.value)}&text=${encodeURIComponent(text)}`, '_blank');
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    });
};
</script>

<template>
    <Head title="Parrainage" />

    <AppLayout>
        <template #header>
            <i class="bi bi-gift me-2"></i>Parrainage
        </template>

        <!-- Auth Required -->
        <div class="card glass text-center p-5" v-if="!isAuthenticated">
            <i class="bi bi-gift display-1 text-success mb-3"></i>
            <h4 class="text-white mb-2">Programme de Parrainage</h4>
            <p class="text-muted-light mb-4">
                Connectez-vous pour inviter vos amis et gagner des jours Premium gratuits !
            </p>
            <Link href="/login" class="btn btn-primary">
                <i class="bi bi-box-arrow-in-right me-2"></i>
                Se connecter
            </Link>
        </div>

        <template v-else>
            <!-- Hero Section -->
            <div class="card glass mb-4">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-lg-7">
                            <h2 class="text-white fw-bold mb-3">
                                <i class="bi bi-gift-fill text-success me-2"></i>
                                Invitez vos amis, gagnez Premium !
                            </h2>
                            <p class="text-muted-light mb-4">
                                Pour chaque ami qui s'inscrit avec votre code, vous recevez tous les deux 
                                <strong class="text-success">7 jours Premium gratuits</strong>.
                                Plus vous parrainez, plus vous gagnez !
                            </p>

                            <!-- Steps -->
                            <div class="d-flex flex-wrap gap-4">
                                <div class="d-flex align-items-start gap-2">
                                    <span class="badge bg-primary rounded-circle p-2">1</span>
                                    <div>
                                        <p class="text-white mb-0 fw-medium">Partagez</p>
                                        <small class="text-muted-light">Votre code unique</small>
                                    </div>
                                </div>
                                <div class="d-flex align-items-start gap-2">
                                    <span class="badge bg-primary rounded-circle p-2">2</span>
                                    <div>
                                        <p class="text-white mb-0 fw-medium">Inscription</p>
                                        <small class="text-muted-light">Votre ami s'inscrit</small>
                                    </div>
                                </div>
                                <div class="d-flex align-items-start gap-2">
                                    <span class="badge bg-success rounded-circle p-2">3</span>
                                    <div>
                                        <p class="text-white mb-0 fw-medium">Bonus !</p>
                                        <small class="text-muted-light">7 jours Premium chacun</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-5 text-center mt-4 mt-lg-0">
                            <i class="bi bi-people-fill" style="font-size: 8rem; opacity: 0.1; color: #10B981;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Stats -->
                <div class="col-lg-4">
                    <div class="card glass h-100">
                        <div class="card-body">
                            <h5 class="text-white mb-4">
                                <i class="bi bi-bar-chart me-2 text-primary"></i>
                                Vos Statistiques
                            </h5>

                            <div class="mb-4">
                                <p class="text-muted-light small mb-1">Total Filleuls</p>
                                <p class="fs-2 fw-bold text-white mb-0">{{ stats.total_referrals }}</p>
                            </div>

                            <div class="mb-4">
                                <p class="text-muted-light small mb-1">Jours Premium Gagnés</p>
                                <p class="fs-2 fw-bold text-success mb-0">
                                    <i class="bi bi-star-fill me-1"></i>
                                    {{ stats.premium_days_earned }}
                                </p>
                            </div>

                            <div>
                                <p class="text-muted-light small mb-1">Récompenses en attente</p>
                                <p class="fs-2 fw-bold text-warning mb-0">{{ stats.pending_rewards }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Share Section -->
                <div class="col-lg-8">
                    <div class="card glass h-100">
                        <div class="card-body">
                            <h5 class="text-white mb-4">
                                <i class="bi bi-share me-2 text-primary"></i>
                                Partagez votre code
                            </h5>

                            <!-- Code -->
                            <div class="mb-4">
                                <label class="form-label text-muted-light">Votre code de parrainage</label>
                                <div class="input-group input-group-lg">
                                    <input type="text" 
                                           class="form-control text-center fw-bold fs-4"
                                           :value="user.referral_code"
                                           readonly>
                                    <button class="btn btn-primary" @click="copyCode">
                                        <i class="bi" :class="copied ? 'bi-check-lg' : 'bi-clipboard'"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Link -->
                            <div class="mb-4">
                                <label class="form-label text-muted-light">Lien d'invitation</label>
                                <div class="input-group">
                                    <input type="text" 
                                           class="form-control"
                                           :value="referralLink"
                                           readonly>
                                    <button class="btn btn-outline-primary" @click="copyLink">
                                        <i class="bi" :class="copied ? 'bi-check-lg' : 'bi-link-45deg'"></i>
                                        Copier
                                    </button>
                                </div>
                            </div>

                            <!-- Share Buttons -->
                            <div class="d-flex flex-wrap gap-2">
                                <button class="btn btn-success" @click="shareWhatsApp">
                                    <i class="bi bi-whatsapp me-2"></i>
                                    WhatsApp
                                </button>
                                <button class="btn btn-info" @click="shareTelegram">
                                    <i class="bi bi-telegram me-2"></i>
                                    Telegram
                                </button>
                                <button class="btn btn-outline-secondary" 
                                        @click="navigator.share?.({ title: 'Rejoins COTA !', text: `Utilise mon code ${user.referral_code}`, url: referralLink })">
                                    <i class="bi bi-share me-2"></i>
                                    Plus d'options
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Referrals List -->
            <div class="card glass mt-4" v-if="referrals.length > 0">
                <div class="card-header">
                    <h5 class="mb-0 text-white">
                        <i class="bi bi-people me-2 text-primary"></i>
                        Vos Filleuls
                    </h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Filleul</th>
                                <th>Date d'inscription</th>
                                <th>Statut</th>
                                <th>Récompense</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="referral in referrals" :key="referral.id">
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold"
                                             style="width: 36px; height: 36px;">
                                            {{ referral.name?.charAt(0).toUpperCase() }}
                                        </div>
                                        <span class="text-white">{{ referral.name }}</span>
                                    </div>
                                </td>
                                <td class="text-muted-light">{{ formatDate(referral.created_at) }}</td>
                                <td>
                                    <span class="badge" 
                                          :class="referral.status === 'verified' ? 'bg-success' : 'bg-warning'">
                                        {{ referral.status === 'verified' ? 'Vérifié' : 'En attente' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-success fw-medium" v-if="referral.status === 'verified'">
                                        <i class="bi bi-check-circle me-1"></i>
                                        +7 jours
                                    </span>
                                    <span class="text-muted-light" v-else>-</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Empty State -->
            <div class="card glass mt-4 text-center p-5" v-else>
                <i class="bi bi-people display-1 text-muted mb-3"></i>
                <h5 class="text-white">Aucun filleul pour le moment</h5>
                <p class="text-muted-light mb-0">Partagez votre code pour commencer à gagner des récompenses !</p>
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

