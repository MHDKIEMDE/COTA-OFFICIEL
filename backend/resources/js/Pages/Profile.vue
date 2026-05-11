<script setup>
import { computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const isAuthenticated = computed(() => !!user.value);

const form = useForm({
    name: user.value?.name || '',
    email: user.value?.email || '',
    phone: user.value?.phone || ''
});

const submit = () => {
    form.put('/user/profile', {
        preserveScroll: true
    });
};

const getUserInitials = () => {
    if (!user.value?.name) return '?';
    return user.value.name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
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
    <Head title="Profil" />

    <AppLayout>
        <template #header>
            <i class="bi bi-person me-2"></i>Mon Profil
        </template>

        <!-- Auth Required -->
        <div class="card glass text-center p-5" v-if="!isAuthenticated">
            <i class="bi bi-person-lock display-1 text-primary mb-3"></i>
            <h4 class="text-white mb-2">Connexion requise</h4>
            <p class="text-muted-light mb-4">
                Connectez-vous pour accéder à votre profil.
            </p>
            <Link href="/login" class="btn btn-primary">
                <i class="bi bi-box-arrow-in-right me-2"></i>
                Se connecter
            </Link>
        </div>

        <template v-else>
            <div class="row g-4">
                <!-- Profile Card -->
                <div class="col-lg-4">
                    <div class="card glass text-center">
                        <div class="card-body p-4">
                            <!-- Avatar -->
                            <div class="profile-avatar mx-auto mb-3">
                                {{ getUserInitials() }}
                            </div>

                            <h4 class="text-white mb-1">{{ user.name }}</h4>
                            <p class="text-muted-light mb-3">
                                {{ user.email || user.phone }}
                            </p>

                            <!-- Premium Badge -->
                            <div class="mb-4">
                                <span v-if="user.is_premium" class="premium-badge">
                                    <i class="bi bi-star-fill"></i>
                                    Membre Premium
                                </span>
                                <span v-else class="badge bg-secondary">
                                    Compte Gratuit
                                </span>
                            </div>

                            <!-- Quick Stats -->
                            <div class="row g-2 text-center">
                                <div class="col-4">
                                    <p class="fs-4 fw-bold text-white mb-0">{{ user.predictions_count || 0 }}</p>
                                    <small class="text-muted-light">Pronostics</small>
                                </div>
                                <div class="col-4">
                                    <p class="fs-4 fw-bold text-success mb-0">{{ user.win_rate || 0 }}%</p>
                                    <small class="text-muted-light">Réussite</small>
                                </div>
                                <div class="col-4">
                                    <p class="fs-4 fw-bold text-primary mb-0">{{ user.referrals_count || 0 }}</p>
                                    <small class="text-muted-light">Filleuls</small>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer bg-transparent border-top border-secondary">
                            <small class="text-muted-light">
                                Membre depuis {{ formatDate(user.created_at) }}
                            </small>
                        </div>
                    </div>

                    <!-- Subscription Info -->
                    <div class="card glass mt-4" v-if="user.is_premium">
                        <div class="card-body">
                            <h6 class="text-white mb-3">
                                <i class="bi bi-credit-card me-2 text-warning"></i>
                                Abonnement Actif
                            </h6>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted-light">Type</span>
                                <span class="text-white fw-medium">{{ user.subscription_type || 'Premium' }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted-light">Expire le</span>
                                <span class="text-white fw-medium">{{ formatDate(user.subscription_expires_at) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Upgrade CTA -->
                    <div class="card bg-gradient-primary border-0 mt-4" v-else>
                        <div class="card-body text-white">
                            <h6 class="mb-2">
                                <i class="bi bi-rocket-takeoff me-2"></i>
                                Passez Premium
                            </h6>
                            <p class="small opacity-75 mb-3">
                                Accédez à tous les pronostics haute confiance.
                            </p>
                            <Link href="/subscription" class="btn btn-light btn-sm w-100">
                                Voir les offres
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Edit Form -->
                <div class="col-lg-8">
                    <div class="card glass">
                        <div class="card-header">
                            <h5 class="mb-0 text-white">
                                <i class="bi bi-pencil-square me-2 text-primary"></i>
                                Modifier mes informations
                            </h5>
                        </div>
                        <div class="card-body">
                            <form @submit.prevent="submit">
                                <!-- Name -->
                                <div class="mb-3">
                                    <label for="name" class="form-label text-muted-light">Nom complet</label>
                                    <input type="text" 
                                           id="name" 
                                           class="form-control"
                                           :class="{ 'is-invalid': form.errors.name }"
                                           v-model="form.name">
                                    <div class="invalid-feedback" v-if="form.errors.name">
                                        {{ form.errors.name }}
                                    </div>
                                </div>

                                <!-- Email -->
                                <div class="mb-3">
                                    <label for="email" class="form-label text-muted-light">Email</label>
                                    <input type="email" 
                                           id="email" 
                                           class="form-control"
                                           :class="{ 'is-invalid': form.errors.email }"
                                           v-model="form.email">
                                    <div class="invalid-feedback" v-if="form.errors.email">
                                        {{ form.errors.email }}
                                    </div>
                                </div>

                                <!-- Phone -->
                                <div class="mb-4">
                                    <label for="phone" class="form-label text-muted-light">Téléphone</label>
                                    <input type="tel" 
                                           id="phone" 
                                           class="form-control"
                                           :class="{ 'is-invalid': form.errors.phone }"
                                           v-model="form.phone">
                                    <div class="invalid-feedback" v-if="form.errors.phone">
                                        {{ form.errors.phone }}
                                    </div>
                                </div>

                                <!-- Success Message -->
                                <div class="alert alert-success" v-if="form.recentlySuccessful">
                                    <i class="bi bi-check-circle me-2"></i>
                                    Profil mis à jour avec succès !
                                </div>

                                <!-- Submit -->
                                <button type="submit" 
                                        class="btn btn-primary"
                                        :disabled="form.processing">
                                    <span v-if="form.processing" class="spinner-border spinner-border-sm me-2"></span>
                                    <i v-else class="bi bi-check-lg me-2"></i>
                                    Enregistrer
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Referral Code -->
                    <div class="card glass mt-4">
                        <div class="card-header">
                            <h5 class="mb-0 text-white">
                                <i class="bi bi-gift me-2 text-success"></i>
                                Mon Code de Parrainage
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted-light mb-3">
                                Partagez ce code avec vos amis. Vous et votre filleul recevrez chacun 7 jours Premium gratuits !
                            </p>
                            <div class="input-group">
                                <input type="text" 
                                       class="form-control form-control-lg text-center fw-bold"
                                       :value="user.referral_code"
                                       readonly>
                                <button class="btn btn-outline-primary" 
                                        type="button"
                                        @click="navigator.clipboard.writeText(user.referral_code)">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </div>
                            <div class="mt-3">
                                <Link href="/referral" class="btn btn-success">
                                    <i class="bi bi-people me-2"></i>
                                    Voir mes filleuls
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Danger Zone -->
                    <div class="card border-danger mt-4">
                        <div class="card-header bg-danger bg-opacity-10 border-danger">
                            <h5 class="mb-0 text-danger">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                Zone Dangereuse
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted-light mb-3">
                                Ces actions sont irréversibles. Procédez avec prudence.
                            </p>
                            <div class="d-flex gap-2 flex-wrap">
                                <Link href="/user/data-export" 
                                      method="post"
                                      class="btn btn-outline-secondary">
                                    <i class="bi bi-download me-2"></i>
                                    Exporter mes données
                                </Link>
                                <button class="btn btn-outline-danger"
                                        onclick="confirm('Êtes-vous sûr de vouloir supprimer votre compte ?') && document.getElementById('delete-form').submit()">
                                    <i class="bi bi-trash me-2"></i>
                                    Supprimer mon compte
                                </button>
                            </div>
                            <form id="delete-form" action="/user/data-delete" method="POST" class="d-none">
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </AppLayout>
</template>

