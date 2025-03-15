<template>
    <button
        :type="type"
        :disabled="disabled || loading"
        :class="[
            'flex items-center justify-center gap-2 px-4 py-2 rounded-lg transition-all duration-200 font-medium focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-offset-[#12121f]',
            {
                'opacity-60 cursor-not-allowed': disabled || loading,
                // Primary variant
                'bg-indigo-600 text-white hover:bg-indigo-700 focus:ring-indigo-500/50 shadow-md shadow-indigo-900/20':
                    variant === 'primary',
                // Secondary variant
                'bg-[#1a1a2e]/50 text-gray-300 hover:bg-[#1a1a2e]/70 border border-[#2a2a3f]/30 focus:ring-gray-500/30':
                    variant === 'secondary',
                // Danger variant
                'bg-red-500/10 text-red-400 hover:bg-red-500/20 border border-red-500/20 focus:ring-red-500/30':
                    variant === 'danger',
                // Success variant
                'bg-green-500/10 text-green-400 hover:bg-green-500/20 border border-green-500/20 focus:ring-green-500/30':
                    variant === 'success',
                // Ghost variant
                'bg-transparent text-gray-400 hover:bg-[#1a1a2e]/30 hover:text-gray-200 focus:ring-gray-500/20':
                    variant === 'ghost',
            },
            {
                'px-3 py-1.5 text-sm rounded': small,
                'px-5 py-2.5 text-base': large,
                'w-full': fullWidth,
            },
            className,
        ]"
        @click="$emit('click', $event)"
    >
        <div v-if="loading" class="w-4 h-4 relative">
            <LoaderIcon class="w-4 h-4 animate-spin absolute" />
        </div>
        <slot v-else name="icon"></slot>
        <slot>{{ text }}</slot>
    </button>
</template>

<script setup lang="ts">
import { Loader as LoaderIcon } from 'lucide-vue-next';

interface Props {
    text?: string;
    type?: 'button' | 'submit' | 'reset';
    variant?: 'primary' | 'secondary' | 'danger' | 'success' | 'ghost';
    disabled?: boolean;
    loading?: boolean;
    small?: boolean;
    large?: boolean;
    fullWidth?: boolean;
    className?: string;
}

withDefaults(defineProps<Props>(), {
    type: 'button',
    variant: 'primary',
    disabled: false,
    loading: false,
    small: false,
    large: false,
    fullWidth: false,
});

defineEmits<{
    (e: 'click', event: MouseEvent): void;
}>();
</script>

<style scoped>
@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.animate-spin {
    animation: spin 1s linear infinite;
}

/* Smooth transitions */
.transition-all {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 200ms;
}
</style>
