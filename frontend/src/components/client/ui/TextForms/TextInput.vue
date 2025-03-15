<template>
    <div class="relative">
        <input
            :type="type"
            v-model="inputValue"
            :class="[
                'w-full bg-[#1a1a2e]/50 border border-[#2a2a3f]/30 rounded-lg px-4 py-2 text-sm text-gray-200 placeholder-gray-500 transition-all duration-200',
                'focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500/50',
                { 'pr-10': icon },
                inputClass,
            ]"
            :placeholder="placeholder"
        />
        <div v-if="icon" class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
            <component :is="icon" class="h-5 w-5 text-gray-400" />
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps({
    modelValue: String,
    type: {
        type: String,
        default: 'text',
    },
    inputClass: {
        type: String,
        default: '',
    },
    placeholder: {
        type: String,
        default: '',
    },
    icon: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['update:modelValue']);

const inputValue = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});
</script>

<style scoped>
/* Smooth transitions */
.transition-all {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 200ms;
}

/* Focus styles */
:focus {
    outline: none;
}
</style>
