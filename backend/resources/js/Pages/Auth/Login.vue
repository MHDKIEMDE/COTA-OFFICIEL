<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const loginMethod = ref('phone'); // 'phone' or 'email'

const form = useForm({
    phone: '',
    email: '',
    method: 'phone'
});

const switchMethod = (method) => {
    loginMethod.value = method;
    form.method = method;
    form.clearErrors();
};

const submit = () => {
    form.method = loginMethod.value;
    form.post('/auth/send-otp', {
        preserveScroll: true,
        onSuccess: () => {
            // Redirect to OTP page is handled by backend
        }
    });
};

const loginWithFacebook = () => {
    window.location.href = '/auth/facebook';
};
</script>

<template>
    <Head title="Connexion" />

    <AuthLayout>
        <div class="text-center mb-4">
            <h3 class="text-white fw-bold mb-2">Bienvenue ! 👋</h3>
            <p class="text-muted-light">Connectez-vous pour accéder à vos pronostics</p>
        </div>

        <!-- Login Method Tabs -->
        <ul class="nav nav-pills-custom mb-4">
            <li class="nav-item flex-fill">
                <button class="nav-link w-100" 
                        :class="{ 'active': loginMethod === 'phone' }"
                        @click="switchMethod('phone')">
                    <i class="bi bi-phone me-2"></i>Téléphone
                </button>
            </li>
            <li class="nav-item flex-fill">
                <button class="nav-link w-100" 
                        :class="{ 'active': loginMethod === 'email' }"
                        @click="switchMethod('email')">
                    <i class="bi bi-envelope me-2"></i>Email
                </button>
            </li>
        </ul>

        <form @submit.prevent="submit">
            <!-- Phone Input -->
            <div class="mb-3" v-if="loginMethod === 'phone'">
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
                <small class="text-muted-light">Entrez votre numéro avec l'indicatif pays</small>
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

            <!-- Submit Button -->
            <button type="submit" 
                    class="btn btn-primary w-100 py-2 mb-3" 
                    :disabled="form.processing">
                <span v-if="form.processing" class="spinner-border spinner-border-sm me-2"></span>
                <i v-else class="bi bi-send me-2"></i>
                Recevoir le code OTP
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

        <!-- Social Login -->
        <button type="button" 
                class="btn btn-outline-light w-100 py-2"
                @click="loginWithFacebook">
            <i class="bi bi-facebook me-2 text-primary"></i>
            Facebook
        </button>

        <!-- Register Link -->
        <p class="text-center mt-4 mb-0 text-muted-light">
            Pas encore de compte ?
            <Link href="/register" class="text-primary text-decoration-none fw-medium">
                Créer un compte
            </Link>
        </p>
    </AuthLayout>
</template>

