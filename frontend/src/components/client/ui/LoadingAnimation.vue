<template>
    <div class="flex flex-col items-center justify-center py-8">
        <!-- Spinner Animation -->
        <div class="relative w-16 h-16 mb-6">
            <!-- Outer spinner -->
            <div class="absolute inset-0 rounded-full border-4 border-indigo-500/10 animate-pulse"></div>

            <!-- Middle spinner -->
            <svg
                class="animate-spin-slow absolute inset-0"
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
            >
                <circle class="opacity-20" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"></circle>
                <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                ></path>
            </svg>

            <!-- Inner spinner -->
            <svg
                class="animate-spin absolute inset-0 text-indigo-500"
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
            >
                <circle class="opacity-25" cx="12" cy="12" r="8" stroke="currentColor" stroke-width="2"></circle>
                <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                ></path>
            </svg>
        </div>

        <!-- Loading Text -->
        <div class="text-center">
            <h3 class="text-lg font-medium text-gray-200 mb-1">{{ loadingText }}</h3>
            <p class="text-sm text-gray-400">{{ description }}</p>
        </div>
    </div>
</template>

<script setup lang="ts">
defineProps({
    loadingText: {
        type: String,
        default: 'Loading...',
    },
    description: {
        type: String,
        default: 'Please wait while we load your data',
    },
});
</script>

<style scoped>
@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

@keyframes spin-reverse {
    to {
        transform: rotate(-360deg);
    }
}

.animate-spin {
    animation: spin 1.5s linear infinite;
}

.animate-spin-slow {
    animation: spin-reverse 3s linear infinite;
}

@keyframes pulse {
    0%,
    100% {
        opacity: 0.3;
    }
    50% {
        opacity: 0.6;
    }
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
