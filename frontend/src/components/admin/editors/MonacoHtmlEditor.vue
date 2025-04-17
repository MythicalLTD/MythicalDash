<template>
    <div :style="{ height: height }" class="monaco-editor-container">
        <Suspense>
            <template #default>
                <MonacoEditor
                    v-model:value="internalValue"
                    :theme="theme"
                    language="html"
                    :options="editorOptions"
                    @change="onChange"
                />
            </template>
            <template #fallback>
                <div class="flex items-center justify-center h-full bg-gray-800">
                    <div class="text-gray-400">Loading editor...</div>
                </div>
            </template>
        </Suspense>
    </div>
</template>

<script setup lang="ts">
import { ref, watch, onMounted, defineEmits, defineAsyncComponent } from 'vue';

const MonacoEditor = defineAsyncComponent(() => import('monaco-editor-vue3'));

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    height: {
        type: String,
        default: '400px',
    },
    theme: {
        type: String,
        default: 'vs-dark',
    },
    options: {
        type: Object,
        default: () => ({}),
    },
});

const emit = defineEmits(['update:modelValue', 'change']);

const internalValue = ref(props.modelValue);

// Default editor options mixed with any custom options passed in
const editorOptions = {
    wordWrap: 'on',
    minimap: { enabled: false },
    scrollBeyondLastLine: false,
    lineNumbers: 'on',
    automaticLayout: true,
    tabSize: 2,
    ...props.options,
};

// Watch for external changes
watch(
    () => props.modelValue,
    (newVal) => {
        if (newVal !== internalValue.value) {
            internalValue.value = newVal;
        }
    },
);

// Emit changes to parent
const onChange = (value: string) => {
    emit('update:modelValue', value);
    emit('change', value);
};

// Initialize
onMounted(() => {
    internalValue.value = props.modelValue;
});
</script>

<style scoped>
.monaco-editor-container {
    width: 100%;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid #2d3748;
}
</style>
