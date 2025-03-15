<template>
    <div class="space-y-4">
        <!-- Card Component -->
        <div
            class="bg-[#12121f]/50 border border-[#2a2a3f]/30 rounded-xl p-5 shadow-lg transition-all duration-200 hover:shadow-xl"
            :class="{ 'hover:border-indigo-500/20': interactive }"
        >
            <div v-if="cardTitle" class="relative">
                <div class="mb-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-medium text-gray-200">{{ cardTitle }}</h2>
                        <slot name="header-action"></slot>
                    </div>
                    <p v-if="cardDescription" class="text-sm text-gray-400 mt-2">{{ cardDescription }}</p>
                </div>
            </div>
            <div v-if="cardTitle && !noSeparator" class="my-3 border-t border-[#2a2a3f]/30"></div>
            <div :class="{ 'pt-2': cardTitle && !noSeparator }">
                <slot></slot>
            </div>
            <div v-if="$slots.footer" class="mt-4 pt-4 border-t border-[#2a2a3f]/30">
                <slot name="footer"></slot>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
defineProps({
    cardTitle: {
        type: String,
        required: false,
    },
    cardDescription: {
        type: String,
        required: false,
    },
    noSeparator: {
        type: Boolean,
        default: false,
    },
    interactive: {
        type: Boolean,
        default: false,
    },
});
</script>

<style scoped>
/* Smooth transitions */
.transition-all {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 200ms;
}

/* Shadow effects */
.shadow-lg {
    box-shadow:
        0 10px 15px -3px rgba(0, 0, 0, 0.1),
        0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.shadow-xl {
    box-shadow:
        0 20px 25px -5px rgba(0, 0, 0, 0.1),
        0 10px 10px -5px rgba(0, 0, 0, 0.04);
}
</style>
