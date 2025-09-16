<template>
    <div class="space-y-6">
        <div class="grid grid-cols-1 gap-6">
            <!-- Custom CSS Editor -->
            <div class="bg-gray-900/50 rounded-lg p-4">
                <h3 class="text-lg font-medium text-gray-200 mb-4">Custom CSS</h3>
                <div class="h-[400px] border border-gray-700 rounded-lg overflow-hidden">
                    <SimpleHtmlEditor
                        v-model="customCss"
                        height="100%"
                        placeholder="Enter your custom CSS here..."
                        @change="handleCssChange"
                    />
                </div>
            </div>

            <!-- Custom JS Editor -->
            <div class="bg-gray-900/50 rounded-lg p-4">
                <h3 class="text-lg font-medium text-gray-200 mb-4">Custom JavaScript</h3>
                <div class="h-[400px] border border-gray-700 rounded-lg overflow-hidden">
                    <SimpleHtmlEditor
                        v-model="customJs"
                        height="100%"
                        placeholder="Enter your custom JavaScript here..."
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
import { useSettingsStore } from '@/stores/settings';

import SimpleHtmlEditor from '@/components/admin/editors/SimpleHtmlEditor.vue';

// Props
const props = defineProps<{
    settings: Record<string, string>;
}>();

// Emits
const emit = defineEmits<{
    update: [key: string, value: string];
    'bulk-update': [updates: Record<string, string>];
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

// Track changed fields
const changedFields = ref<Set<string>>(new Set());

// Mark a field as changed
const markChanged = (field: string) => {
    changedFields.value.add(field);

    // Emit the change to parent
    let value: string;
    if (field === 'custom_css') {
        value = customCss.value;
    } else if (field === 'custom_js') {
        value = customJs.value;
    } else {
        value = '';
    }

    emit('update', field, value);
};

// Compute if there are any changes
const hasChanges = computed(() => {
    return customCss.value !== originalCss.value || customJs.value !== originalJs.value;
});

const handleCssChange = (value: string) => {
    customCss.value = value;
    markChanged('custom_css');
};

const handleJsChange = (value: string) => {
    customJs.value = value;
    markChanged('custom_js');
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

            // Clear changed fields when settings are loaded
            changedFields.value.clear();
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
