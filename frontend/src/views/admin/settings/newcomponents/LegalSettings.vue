<template>
    <div>
        <h2 class="text-xl font-semibold text-white mb-4">Legal Settings</h2>

        <div class="space-y-6">
            <div class="bg-gray-800/30 p-5 rounded-lg border border-gray-700">
                <div class="mb-4">
                    <h3 class="text-lg font-medium text-white mb-1">Terms of Service</h3>
                    <p class="text-sm text-gray-400">
                        Edit your Terms of Service content. You can use basic HTML (e.g. <b>&lt;b&gt;</b>,
                        <b>&lt;strong&gt;</b>, <b>&lt;br&gt;</b>, <b>&lt;ul&gt;</b>, <b>&lt;li&gt;</b>).
                    </p>
                </div>

                <div class="mb-4">
                    <label for="legal_tos" class="block text-sm font-medium text-gray-400 mb-1"
                        >Terms of Service Content</label
                    >
                    <textarea
                        id="legal_tos"
                        v-model="formData.legal_tos"
                        @change="markChanged('legal_tos')"
                        class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500 min-h-[200px] font-mono"
                        placeholder="Enter your Terms of Service HTML here..."
                    />
                    <p class="mt-1 text-xs text-gray-500">
                        You can use basic HTML tags for formatting. Avoid scripts or unsafe tags.
                    </p>
                </div>

                <div class="mb-4">
                    <h3 class="text-lg font-medium text-white mb-1">Privacy Policy</h3>
                    <p class="text-sm text-gray-400">
                        Edit your Privacy Policy content. You can use basic HTML (e.g. <b>&lt;b&gt;</b>,
                        <b>&lt;strong&gt;</b>, <b>&lt;br&gt;</b>, <b>&lt;ul&gt;</b>, <b>&lt;li&gt;</b>).
                    </p>
                </div>

                <div>
                    <label for="legal_privacy" class="block text-sm font-medium text-gray-400 mb-1"
                        >Privacy Policy Content</label
                    >
                    <textarea
                        id="legal_privacy"
                        v-model="formData.legal_privacy"
                        @change="markChanged('legal_privacy')"
                        class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500 min-h-[200px] font-mono"
                        placeholder="Enter your Privacy Policy HTML here..."
                    />
                    <p class="mt-1 text-xs text-gray-500">
                        You can use basic HTML tags for formatting. Avoid scripts or unsafe tags.
                    </p>
                </div>
            </div>

            <div class="bg-yellow-500/20 text-yellow-400 p-4 rounded-lg">
                <div class="flex">
                    <AlertTriangleIcon class="h-5 w-5 mr-2 flex-shrink-0" />
                    <div>
                        <p class="font-medium">Legal Notice</p>
                        <p class="text-sm mt-1">
                            It's important to have proper legal documents for your service. If you don't have these
                            documents yet, consider consulting with a legal professional to create them for your
                            specific use case.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { AlertTriangleIcon } from 'lucide-vue-next';

// Props
const props = defineProps<{
    settings: Record<string, string>;
}>();

// Emits
const emit = defineEmits<{
    update: [key: string, value: string];
    'bulk-update': [updates: Record<string, string>];
}>();

// Form state
const formData = ref({
    legal_tos: '',
    legal_privacy: '',
});

// Track changed fields
const changedFields = ref<Set<string>>(new Set());

// Mark a field as changed
const markChanged = (field: string) => {
    changedFields.value.add(field);

    // Emit the change to parent
    const value = formData.value[field as keyof typeof formData.value];
    emit('update', field, value);
};

// Initialize form with settings values
watch(
    () => props.settings,
    (newSettings) => {
        if (newSettings) {
            formData.value = {
                legal_tos: newSettings['legal_tos'] || '',
                legal_privacy: newSettings['legal_privacy'] || '',
            };

            // Clear changed fields when settings are loaded
            changedFields.value.clear();
        }
    },
    { immediate: true },
);
</script>
