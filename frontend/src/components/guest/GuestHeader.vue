<template>
    <header class="bg-gray-800/50 border-b border-gray-700/50 sticky top-0 z-10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 h-14 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <router-link :to="backLink" class="text-gray-400 hover:text-white transition-colors duration-150">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </router-link>
                <div class="flex items-center space-x-2">
                    <h1 class="text-white font-medium">{{ title }}</h1>
                    <span v-if="subtitle" class="text-sm text-gray-400 font-mono">{{ subtitle }}</span>
                </div>
            </div>
            <div v-if="metadata" class="flex items-center space-x-3 text-sm text-gray-400">
                <slot name="metadata">{{ metadata }}</slot>
            </div>
        </div>
    </header>
</template>

<script setup lang="ts">
interface Props {
    title: string;
    subtitle?: string;
    metadata?: string;
    backLink?: string;
}

withDefaults(defineProps<Props>(), {
    backLink: '/dashboard',
});
</script>
