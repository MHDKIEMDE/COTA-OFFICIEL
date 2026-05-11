<script setup>
import { computed } from 'vue';

const props = defineProps({
    title: {
        type: String,
        required: true
    },
    value: {
        type: [String, Number],
        required: true
    },
    icon: {
        type: String,
        default: 'bi-graph-up'
    },
    color: {
        type: String,
        default: 'primary' // primary, success, warning, danger, info
    },
    badge: {
        type: String,
        default: null
    },
    trend: {
        type: Object,
        default: null // { value: '+12%', direction: 'up' }
    }
});

const colorClasses = computed(() => {
    const colors = {
        primary: { bg: 'rgba(233, 30, 140, 0.15)', text: '#E91E8C' },
        success: { bg: 'rgba(16, 185, 129, 0.15)', text: '#10B981' },
        warning: { bg: 'rgba(245, 158, 11, 0.15)', text: '#F59E0B' },
        danger: { bg: 'rgba(239, 68, 68, 0.15)', text: '#EF4444' },
        info: { bg: 'rgba(59, 130, 246, 0.15)', text: '#3B82F6' },
    };
    return colors[props.color] || colors.primary;
});
</script>

<template>
    <div class="stat-card h-100">
        <div class="d-flex align-items-start justify-content-between">
            <div class="flex-grow-1">
                <p class="stat-label mb-1">{{ title }}</p>
                <p class="stat-value text-white mb-0">{{ value }}</p>
                
                <!-- Trend -->
                <div class="d-flex align-items-center gap-2 mt-2" v-if="trend">
                    <span class="small" 
                          :class="trend.direction === 'up' ? 'text-success' : 'text-danger'">
                        <i class="bi" :class="trend.direction === 'up' ? 'bi-arrow-up' : 'bi-arrow-down'"></i>
                        {{ trend.value }}
                    </span>
                    <span class="text-muted-light small">vs hier</span>
                </div>

                <!-- Badge -->
                <span class="badge bg-success mt-2" v-if="badge">
                    {{ badge }}
                </span>
            </div>

            <div class="stat-icon" :style="{ background: colorClasses.bg }">
                <i class="bi" :class="icon" :style="{ color: colorClasses.text }"></i>
            </div>
        </div>
    </div>
</template>

