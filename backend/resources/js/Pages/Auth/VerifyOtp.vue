<script setup>
import { ref, onMounted } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';

const props = defineProps({
    contact: {
        type: String,
        required: true
    },
    method: {
        type: String,
        default: 'phone'
    }
});

const otpInputs = ref([]);
const form = useForm({
    otp: '',
    contact: props.contact,
    method: props.method
});

const resendTimer = ref(60);
const canResend = ref(false);

// Initialize countdown
const startCountdown = () => {
    resendTimer.value = 60;
    canResend.value = false;
    
    const interval = setInterval(() => {
        resendTimer.value--;
        if (resendTimer.value <= 0) {
            canResend.value = true;
            clearInterval(interval);
        }
    }, 1000);
};

onMounted(() => {
    startCountdown();
    // Focus first input
    if (otpInputs.value[0]) {
        otpInputs.value[0].focus();
    }
});

const handleOtpInput = (index, event) => {
    const value = event.target.value;
    
    // Move to next input
    if (value && index < 5) {
        otpInputs.value[index + 1]?.focus();
    }
    
    // Update form OTP
    updateOtp();
    
    // Auto-submit when complete
    if (form.otp.length === 6) {
        submit();
    }
};

const handleOtpKeydown = (index, event) => {
    // Handle backspace
    if (event.key === 'Backspace' && !event.target.value && index > 0) {
        otpInputs.value[index - 1]?.focus();
    }
};

const handlePaste = (event) => {
    event.preventDefault();
    const paste = event.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6);
    
    paste.split('').forEach((char, i) => {
        if (otpInputs.value[i]) {
            otpInputs.value[i].value = char;
        }
    });
    
    updateOtp();
    
    if (form.otp.length === 6) {
        submit();
    }
};

const updateOtp = () => {
    form.otp = otpInputs.value.map(input => input?.value || '').join('');
};

const submit = () => {
    form.post('/auth/verify-otp', {
        preserveScroll: true,
        onSuccess: () => {
            // Redirect handled by backend
        }
    });
};

const resendOtp = () => {
    if (!canResend.value) return;
    
    router.post('/auth/send-otp', {
        [props.method]: props.contact,
        method: props.method
    }, {
        preserveScroll: true,
        onSuccess: () => {
            startCountdown();
        }
    });
};

const maskContact = (contact) => {
    if (props.method === 'phone') {
        return contact.slice(0, -4).replace(/./g, '*') + contact.slice(-4);
    }
    const [local, domain] = contact.split('@');
    return local.slice(0, 2) + '***@' + domain;
};
</script>

<template>
    <Head title="Vérification OTP" />

    <AuthLayout>
        <div class="text-center mb-4">
            <div class="mb-3">
                <i class="bi bi-envelope-check fs-1 text-primary"></i>
            </div>
            <h3 class="text-white fw-bold mb-2">Vérification</h3>
            <p class="text-muted-light">
                Entrez le code à 6 chiffres envoyé à<br>
                <strong class="text-white">{{ maskContact(contact) }}</strong>
            </p>
        </div>

        <form @submit.prevent="submit">
            <!-- OTP Inputs -->
            <div class="d-flex justify-content-center gap-2 mb-4" @paste="handlePaste">
                <input v-for="i in 6" 
                       :key="i"
                       :ref="el => otpInputs[i-1] = el"
                       type="text"
                       inputmode="numeric"
                       maxlength="1"
                       class="form-control text-center fw-bold fs-4"
                       :class="{ 'is-invalid': form.errors.otp }"
                       style="width: 50px; height: 56px;"
                       @input="handleOtpInput(i-1, $event)"
                       @keydown="handleOtpKeydown(i-1, $event)">
            </div>

            <!-- Error Message -->
            <div class="alert alert-danger text-center" v-if="form.errors.otp || form.errors.error">
                <i class="bi bi-exclamation-triangle me-2"></i>
                {{ form.errors.otp || form.errors.error }}
            </div>

            <!-- Submit Button -->
            <button type="submit" 
                    class="btn btn-primary w-100 py-2 mb-3"
                    :disabled="form.processing || form.otp.length < 6">
                <span v-if="form.processing" class="spinner-border spinner-border-sm me-2"></span>
                <i v-else class="bi bi-check-circle me-2"></i>
                Vérifier
            </button>

            <!-- Resend -->
            <p class="text-center mb-0">
                <template v-if="canResend">
                    <button type="button" 
                            class="btn btn-link text-primary text-decoration-none p-0"
                            @click="resendOtp">
                        <i class="bi bi-arrow-clockwise me-1"></i>
                        Renvoyer le code
                    </button>
                </template>
                <template v-else>
                    <span class="text-muted-light">
                        Renvoyer dans <strong class="text-white">{{ resendTimer }}s</strong>
                    </span>
                </template>
            </p>
        </form>

        <!-- Back Link -->
        <div class="text-center mt-4">
            <Link href="/login" class="text-muted-light text-decoration-none">
                <i class="bi bi-arrow-left me-1"></i>
                Retour à la connexion
            </Link>
        </div>
    </AuthLayout>
</template>

