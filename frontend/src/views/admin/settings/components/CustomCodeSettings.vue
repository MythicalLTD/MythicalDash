<template>
    <div class="space-y-6">
        <div class="grid grid-cols-1 gap-6">
            <!-- Custom CSS Editor -->
            <div class="bg-gray-900/50 rounded-lg p-4">
                <h3 class="text-lg font-medium text-gray-200 mb-4">Custom CSS</h3>
                <div class="h-[400px] border border-gray-700 rounded-lg overflow-hidden">
                    <MonacoEditor
                        v-model:value="customCss"
                        theme="vs-dark"
                        language="css"
                        :options="{
                            fontSize: 14,
                            wordWrap: 'on',
                            formatOnPaste: true,
                            formatOnType: true,
                            minimap: { enabled: false },
                            scrollBeyondLastLine: false,
                            lineNumbers: 'on',
                            automaticLayout: true,
                            tabSize: 2,
                        }"
                        @change="handleCssChange"
                    />
                </div>
            </div>

            <!-- Custom JS Editor -->
            <div class="bg-gray-900/50 rounded-lg p-4">
                <h3 class="text-lg font-medium text-gray-200 mb-4">Custom JavaScript</h3>
                <div class="h-[400px] border border-gray-700 rounded-lg overflow-hidden">
                    <MonacoEditor
                        v-model:value="customJs"
                        theme="vs-dark"
                        language="javascript"
                        :options="{
                            fontSize: 14,
                            wordWrap: 'on',
                            formatOnPaste: true,
                            formatOnType: true,
                            minimap: { enabled: false },
                            scrollBeyondLastLine: false,
                            lineNumbers: 'on',
                            automaticLayout: true,
                            tabSize: 2,
                        }"
                        @change="handleJsChange"
                    />
                </div>
            </div>
        </div>

        <!-- Save Button -->
        <div class="flex justify-end space-x-3 pt-4 border-t border-gray-700">
            <button
                type="button"
                @click="saveChanges"
                :disabled="saving || !hasChanges"
                class="px-4 py-2 bg-gradient-to-r from-pink-500 to-violet-500 rounded-lg text-white hover:opacity-90 transition-colors flex items-center disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <LoaderIcon v-if="saving" class="animate-spin w-4 h-4 mr-2" />
                <SaveIcon v-else class="w-4 h-4 mr-2" />
                {{ saving ? 'Saving...' : 'Save Changes' }}
            </button>
        </div>

        <!-- Success Message -->
        <div v-if="successMessage" class="bg-green-500/20 text-green-400 p-4 rounded-lg">
            {{ successMessage }}
        </div>

        <!-- Error Message -->
        <div v-if="errorMessage" class="bg-red-500/20 text-red-400 p-4 rounded-lg">
            {{ errorMessage }}
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, defineAsyncComponent, computed, watch } from 'vue';
import { SaveIcon, LoaderIcon } from 'lucide-vue-next';
import '@/utils/monaco';
import { useSettingsStore } from '@/stores/settings';

const MonacoEditor = defineAsyncComponent(() => import('monaco-editor-vue3'));

const props = defineProps<{
    settings: Record<string, string>;
}>();

const emit = defineEmits<{
    (e: 'update', key: string, value: string): void;
}>();

const settingsStore = useSettingsStore();
const customCss = ref('');
const customJs = ref('');
const saving = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

// Track original values for change detection
const originalCss = ref('');
const originalJs = ref('');

// Compute if there are any changes
const hasChanges = computed(() => {
    return customCss.value !== originalCss.value || customJs.value !== originalJs.value;
});

const handleCssChange = (value: string) => {
    customCss.value = value;
};

const handleJsChange = (value: string) => {
    customJs.value = value;
};

const saveChanges = async () => {
    if (!hasChanges.value) return;

    saving.value = true;
    errorMessage.value = '';
    successMessage.value = '';

    try {
        // Save CSS
        if (customCss.value !== originalCss.value) {
            emit('update', 'custom_css', customCss.value);
            originalCss.value = customCss.value;
        }

        // Save JS
        if (customJs.value !== originalJs.value) {
            emit('update', 'custom_js', customJs.value);
            originalJs.value = customJs.value;
        }

        successMessage.value = 'Changes saved successfully';
        // Clear success message after 3 seconds
        setTimeout(() => {
            successMessage.value = '';
        }, 3000);
    } catch (error) {
        console.error('Error saving changes:', error);
        errorMessage.value = 'Failed to save changes';
    } finally {
        saving.value = false;
    }
};

// Watch for changes in settings prop
watch(
    () => props.settings,
    (newSettings) => {
        if (newSettings) {
            customCss.value = newSettings.custom_css || '';
            customJs.value = newSettings.custom_js || '';
            originalCss.value = customCss.value;
            originalJs.value = customJs.value;
        }
    },
    { immediate: true },
);

// Initialize settings on mount
onMounted(async () => {
    if (!settingsStore.isInitialized) {
        await settingsStore.initialize();
    }
});
</script>
