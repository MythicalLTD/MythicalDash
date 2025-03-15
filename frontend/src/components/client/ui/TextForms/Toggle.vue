<template>
    <div class="flex items-center">
        <button
            type="button"
            @click="toggle"
            :class="[
                'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:ring-offset-2 focus:ring-offset-[#12121f]',
                inputValue ? 'bg-indigo-600' : 'bg-[#2a2a3f]',
            ]"
            :disabled="disabled"
            :aria-checked="inputValue"
            role="switch"
            :id="id"
        >
            <span class="sr-only">{{ label }}</span>
            <span
                :class="[
                    'pointer-events-none relative inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                    inputValue ? 'translate-x-5' : 'translate-x-0',
                ]"
            >
                <span
                    :class="[
                        'absolute inset-0 flex h-full w-full items-center justify-center transition-opacity',
                        inputValue ? 'opacity-0 duration-100 ease-out' : 'opacity-100 duration-200 ease-in',
                    ]"
                    aria-hidden="true"
                >
                    <svg v-if="icons" class="h-3 w-3 text-gray-400" fill="none" viewBox="0 0 12 12">
                        <path
                            d="M4 8l2-2m0 0l2-2M6 6L4 4m2 2l2 2"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </span>
                <span
                    :class="[
                        'absolute inset-0 flex h-full w-full items-center justify-center transition-opacity',
                        inputValue ? 'opacity-100 duration-200 ease-in' : 'opacity-0 duration-100 ease-out',
                    ]"
                    aria-hidden="true"
                >
                    <svg v-if="icons" class="h-3 w-3 text-indigo-600" fill="currentColor" viewBox="0 0 12 12">
                        <path
                            d="M3.707 5.293a1 1 0 00-1.414 1.414l1.414-1.414zM5 8l-.707.707a1 1 0 001.414 0L5 8zm4.707-3.293a1 1 0 00-1.414-1.414l1.414 1.414zm-7.414 2l2 2 1.414-1.414-2-2-1.414 1.414zm3.414 2l4-4-1.414-1.414-4 4 1.414 1.414z"
                        />
                    </svg>
                </span>
            </span>
        </button>
        <div v-if="$slots.default || label" class="ml-3">
            <label :for="id" class="text-sm font-medium text-gray-300 cursor-pointer">
                <slot>{{ label }}</slot>
            </label>
            <p v-if="description" class="text-sm text-gray-500 mt-1">{{ description }}</p>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },
    label: {
        type: String,
        default: '',
    },
    description: {
        type: String,
        default: '',
    },
    id: {
        type: String,
        default: () => `toggle-${Math.random().toString(36).substring(2, 9)}`,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    icons: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:modelValue']);

const inputValue = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

const toggle = () => {
    if (!props.disabled) {
        inputValue.value = !inputValue.value;
    }
};
</script>

<style scoped>
/* Disabled state */
button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Smooth transitions */
.transition-colors,
.transition {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}
</style>
