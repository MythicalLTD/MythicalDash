<template>
    <footer class="bg-gray-800/50 border-t border-gray-700/50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 h-12 flex items-center justify-between">
            <div class="flex items-center space-x-3 text-sm text-gray-400">
                <slot name="left">
                    <span v-if="leftText">{{ leftText }}</span>
                </slot>
            </div>
            <div class="flex items-center text-sm">
                <slot name="right">
                    <span v-if="rightText" class="text-gray-400">{{ rightText }}</span>
                </slot>
            </div>
        </div>
    </footer>
</template>

<script setup lang="ts">
interface Props {
    leftText?: string;
    rightText?: string;
}

defineProps<Props>();
</script>
