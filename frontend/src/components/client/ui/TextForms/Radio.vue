<template>
    <div class="flex items-center">
        <div class="relative flex items-start">
            <div class="flex items-center h-5">
                <input
                    :id="id"
                    type="radio"
                    :name="name"
                    :value="value"
                    v-model="inputValue"
                    class="h-4 w-4 border-[#2a2a3f] bg-[#1a1a2e]/50 text-indigo-600 focus:ring-indigo-500/30 focus:ring-offset-[#12121f] transition-all duration-200"
                    :disabled="disabled"
                />
            </div>
            <div v-if="$slots.default || label" class="ml-3 text-sm">
                <label :for="id" class="font-medium text-gray-300 cursor-pointer">
                    <slot>{{ label }}</slot>
                </label>
                <p v-if="description" class="text-gray-500 mt-1">{{ description }}</p>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps({
    modelValue: {
        type: [String, Number, Boolean],
        required: true,
    },
    value: {
        type: [String, Number, Boolean],
        required: true,
    },
    label: {
        type: String,
        default: '',
    },
    description: {
        type: String,
        default: '',
    },
    name: {
        type: String,
        required: true,
    },
    id: {
        type: String,
        default: () => `radio-${Math.random().toString(36).substring(2, 9)}`,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:modelValue']);

const inputValue = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});
</script>

<style scoped>
/* Custom radio styling */
input[type='radio'] {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    display: inline-block;
    position: relative;
    background-color: rgba(26, 26, 46, 0.5);
    border: 1px solid rgba(42, 42, 63, 0.5);
    border-radius: 50%;
    cursor: pointer;
}

input[type='radio']:checked {
    background-color: #6366f1;
    border-color: #6366f1;
}

input[type='radio']:checked::after {
    content: '';
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background-color: white;
}

input[type='radio']:focus {
    outline: none;
    box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.3);
}

input[type='radio']:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Smooth transitions */
.transition-all {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 200ms;
}
</style>
