<template>
    <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-gray-800/50">
        <div class="flex items-center">
            <div class="w-2 h-2 rounded-full animate-pulse" :class="statusColor"></div>
            <span class="ml-2 text-sm font-medium" :class="statusTextColor">{{ statusText }}</span>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

// Define a type for the system status
type SystemStatusType = 'normal' | 'warning' | 'error';

// In a real app, you might fetch this from an API
const systemStatus: SystemStatusType = 'normal';

const statusColor = computed(() => {
    if (systemStatus === 'normal') return 'bg-emerald-400';
    if (systemStatus === 'warning') return 'bg-amber-400';
    if (systemStatus === 'error') return 'bg-rose-500';
    return 'bg-emerald-400';
});

const statusTextColor = computed(() => {
    if (systemStatus === 'normal') return 'text-emerald-400';
    if (systemStatus === 'warning') return 'text-amber-400';
    if (systemStatus === 'error') return 'text-rose-500';
    return 'text-emerald-400';
});

const statusText = computed(() => {
    if (systemStatus === 'normal') return 'Operational';
    if (systemStatus === 'warning') return 'Degraded';
    if (systemStatus === 'error') return 'Outage';
    return 'Operational';
});
</script>
