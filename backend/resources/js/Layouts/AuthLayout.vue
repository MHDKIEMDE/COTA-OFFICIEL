<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="auth-page">
        <div class="auth-card">
            <!-- Logo -->
            <div class="auth-logo">
                <i class="bi bi-lightning-charge-fill"></i>
            </div>

            <!-- Content -->
            <slot />

            <!-- Footer -->
            <div class="text-center mt-4 pt-4 border-top border-secondary">
                <p class="text-muted-light small mb-0">
                    &copy; {{ new Date().getFullYear() }} COTA. Tous droits réservés.
                </p>
            </div>
        </div>
    </div>
</template>

