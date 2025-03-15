<template>
    <div class="relative">
        <textarea
            v-model="inputValue"
            :class="[
                'w-full bg-[#1a1a2e]/50 border border-[#2a2a3f]/30 rounded-lg px-4 py-3 text-sm text-gray-200 placeholder-gray-500 transition-all duration-200',
                'focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500/50',
                'min-h-[100px] resize-y',
                inputClass,
            ]"
            :placeholder="placeholder"
            :rows="rows"
        ></textarea>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps({
    modelValue: String,
    inputClass: {
        type: String,
        default: '',
    },
    placeholder: {
        type: String,
        default: '',
    },
    rows: {
        type: Number,
        default: 4,
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

/* Scrollbar styling */
textarea::-webkit-scrollbar {
    width: 4px;
}

textarea::-webkit-scrollbar-track {
    background: transparent;
}

textarea::-webkit-scrollbar-thumb {
    background: rgba(42, 42, 63, 0.5);
    border-radius: 2px;
}

textarea::-webkit-scrollbar-thumb:hover {
    background: rgba(42, 42, 63, 0.7);
}
</style>
