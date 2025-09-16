<template>
    <div class="simple-text-editor" :style="{ height }">
        <div class="editor-header bg-gray-700 px-4 py-3 rounded-t-lg border-b border-gray-600">
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-300 font-medium">HTML Content</span>
                <div class="flex items-center space-x-3">
                    <span class="text-xs text-gray-400">{{ wordCount }} words</span>
                </div>
            </div>
        </div>

        <textarea
            ref="textareaRef"
            v-model="internalValue"
            :style="{ height: 'calc(100% - 40px)', minHeight: '200px' }"
            class="w-full bg-gray-900 text-gray-100 p-4 rounded-b-lg border-0 resize-none focus:outline-none font-mono text-sm leading-relaxed"
            :placeholder="placeholder"
            @input="onInput"
            @keydown="onKeydown"
        ></textarea>
    </div>
</template>

<script setup lang="ts">
import { ref, watch, computed, nextTick } from 'vue';

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    height: {
        type: String,
        default: '400px',
    },
    placeholder: {
        type: String,
        default: 'Enter your HTML content here...',
    },
});

const emit = defineEmits(['update:modelValue', 'change']);

const internalValue = ref<string>(props.modelValue);
const textareaRef = ref<HTMLTextAreaElement | null>(null);

// Simple word count
const wordCount = computed(() => {
    if (!internalValue.value || internalValue.value.trim() === '') {
        return 0;
    }

    const words = internalValue.value
        .trim()
        .split(/\s+/)
        .filter((word) => word.length > 0);
    return words.length;
});

// Watch for external changes
watch(
    () => props.modelValue,
    (val) => {
        if (val !== internalValue.value) {
            internalValue.value = val;
        }
    },
);

// Handle input changes
const onInput = () => {
    emit('update:modelValue', internalValue.value);
    emit('change', internalValue.value);
};

// Handle keyboard shortcuts
const onKeydown = (event: KeyboardEvent) => {
    // Tab key handling
    if (event.key === 'Tab') {
        event.preventDefault();
        insertText('    '); // 4 spaces
    }

    // Ctrl/Cmd + S to format
    if ((event.ctrlKey || event.metaKey) && event.key === 's') {
        event.preventDefault();
    }
};

// Insert text at cursor position
const insertText = (text: string) => {
    const textarea = textareaRef.value;
    if (!textarea) return;

    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;

    const before = internalValue.value.substring(0, start);
    const after = internalValue.value.substring(end);

    internalValue.value = before + text + after;

    // Set cursor position after inserted text
    nextTick(() => {
        const newPosition = start + text.length;
        textarea.setSelectionRange(newPosition, newPosition);
        textarea.focus();
    });

    emit('update:modelValue', internalValue.value);
    emit('change', internalValue.value);
};
</script>

<style scoped>
.simple-text-editor {
    width: 100%;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid #2d3748;
    background: #1a202c;
}

textarea:focus {
    outline: none;
}

/* Custom scrollbar */
textarea::-webkit-scrollbar {
    width: 8px;
}

textarea::-webkit-scrollbar-track {
    background: #2d3748;
}

textarea::-webkit-scrollbar-thumb {
    background: #4a5568;
    border-radius: 4px;
}

textarea::-webkit-scrollbar-thumb:hover {
    background: #718096;
}
</style>
