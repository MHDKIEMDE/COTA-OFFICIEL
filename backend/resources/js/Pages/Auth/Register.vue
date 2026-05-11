<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const registerMethod = ref('phone');

const form = useForm({
    name: '',
    phone: '',
    email: '',
    referral_code: '',
    method: 'phone'
});

const switchMethod = (method) => {
    registerMethod.value = method;
    form.method = method;
    form.clearErrors();
};

const submit = () => {
    form.method = registerMethod.value;
    form.post('/auth/register', {
        preserveScroll: true
    });
};

const loginWithFacebook = () => {
    window.location.href = '/auth/facebook';
};
</script>

<template>
    <Head title="Inscription" />

    <AuthLayout>
        <div class="text-center mb-4">
            <h3 class="text-white fw-bold mb-2">Créer un compte 🚀</h3>
            <p class="text-muted-light">Rejoignez la communauté des gagnants</p>
        </div>

        <!-- Method Tabs -->
        <ul class="nav nav-pills-custom mb-4">
            <li class="nav-item flex-fill">
                <button class="nav-link w-100" 
                        :class="{ 'active': registerMethod === 'phone' }"
                        @click="switchMethod('phone')">
                    <i class="bi bi-phone me-2"></i>Téléphone
                </button>
            </li>
            <li class="nav-item flex-fill">
                <button class="nav-link w-100" 
                        :class="{ 'active': registerMethod === 'email' }"
                        @click="switchMethod('email')">
                    <i class="bi bi-envelope me-2"></i>Email
                </button>
            </li>
        </ul>

        <form @submit.prevent="submit">
            <!-- Name -->
            <div class="mb-3">
                <label for="name" class="form-label text-muted-light">Nom complet</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-person"></i>
                    </span>
                    <input type="text" 
                           id="name" 
                           class="form-control"
                           :class="{ 'is-invalid': form.errors.name }"
                           v-model="form.name"
                           placeholder="Votre nom"
                           autocomplete="name"
                           required>
                </div>
                <div class="invalid-feedback d-block" v-if="form.errors.name">
                    {{ form.errors.name }}
                </div>
            </div>

            <!-- Phone Input -->
            <div class="mb-3" v-if="registerMethod === 'phone'">
                <label for="phone" class="form-label text-muted-light">Numéro de téléphone</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-telephone"></i>
                    </span>
                    <input type="tel" 
                           id="phone" 
                           class="form-control"
                           :class="{ 'is-invalid': form.errors.phone }"
                           v-model="form.phone"
                           placeholder="+226 70 00 00 00"
                           autocomplete="tel">
                </div>
                <div class="invalid-feedback d-block" v-if="form.errors.phone">
                    {{ form.errors.phone }}
                </div>
            </div>

            <!-- Email Input -->
            <div class="mb-3" v-else>
                <label for="email" class="form-label text-muted-light">Adresse email</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-envelope"></i>
                    </span>
                    <input type="email" 
                           id="email" 
                           class="form-control"
                           :class="{ 'is-invalid': form.errors.email }"
                           v-model="form.email"
                           placeholder="votre@email.com"
                           autocomplete="email">
                </div>
                <div class="invalid-feedback d-block" v-if="form.errors.email">
                    {{ form.errors.email }}
                </div>
            </div>

            <!-- Referral Code (Optional) -->
            <div class="mb-4">
                <label for="referral_code" class="form-label text-muted-light">
                    Code de parrainage <small class="text-muted">(optionnel)</small>
                </label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-gift"></i>
                    </span>
                    <input type="text" 
                           id="referral_code" 
                           class="form-control"
                           :class="{ 'is-invalid': form.errors.referral_code }"
                           v-model="form.referral_code"
                           placeholder="CODE123"
                           autocomplete="off">
                </div>
                <div class="invalid-feedback d-block" v-if="form.errors.referral_code">
                    {{ form.errors.referral_code }}
                </div>
                <small class="text-muted-light">Recevez 7 jours Premium gratuits avec un code valide !</small>
            </div>

            <!-- Submit Button -->
            <button type="submit" 
                    class="btn btn-primary w-100 py-2 mb-3"
                    :disabled="form.processing">
                <span v-if="form.processing" class="spinner-border spinner-border-sm me-2"></span>
                <i v-else class="bi bi-rocket-takeoff me-2"></i>
                Créer mon compte
            </button>

            <!-- General Error -->
            <div class="alert alert-danger" v-if="form.errors.error">
                <i class="bi bi-exclamation-triangle me-2"></i>
                {{ form.errors.error }}
            </div>
        </form>

        <!-- Divider -->
        <div class="d-flex align-items-center my-4">
            <hr class="flex-grow-1 border-secondary">
            <span class="px-3 text-muted-light small">ou continuer avec</span>
            <hr class="flex-grow-1 border-secondary">
        </div>

        <!-- Social Register -->
        <button type="button" 
                class="btn btn-outline-light w-100 py-2"
                @click="loginWithFacebook">
            <i class="bi bi-facebook me-2 text-primary"></i>
            Facebook
        </button>

        <!-- Login Link -->
        <p class="text-center mt-4 mb-0 text-muted-light">
            Déjà un compte ?
            <Link href="/login" class="text-primary text-decoration-none fw-medium">
                Se connecter
            </Link>
        </p>

        <!-- Terms -->
        <p class="text-center mt-3 small text-muted">
            En vous inscrivant, vous acceptez nos
            <a href="#" class="text-muted-light">CGU</a> et
            <a href="#" class="text-muted-light">Politique de confidentialité</a>
        </p>
    </AuthLayout>
</template>

