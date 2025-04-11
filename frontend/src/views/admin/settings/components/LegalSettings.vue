<template>
    <div>
        <h2 class="text-xl font-semibold text-white mb-4">Legal Settings</h2>

        <div class="space-y-6">
            <div class="bg-gray-800/30 p-5 rounded-lg border border-gray-700">
                <div class="mb-4">
                    <h3 class="text-lg font-medium text-white mb-1">Terms of Service</h3>
                    <p class="text-sm text-gray-400">Provide a link to your Terms of Service document.</p>
                </div>

                <div class="mb-4">
                    <label for="legal_tos_url" class="block text-sm font-medium text-gray-400 mb-1"
                        >Terms of Service URL</label
                    >
                    <input
                        id="legal_tos_url"
                        type="url"
                        v-model="formData.legal_tos_url"
                        @change="updateSetting('legal_tos_url', formData.legal_tos_url)"
                        class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                        placeholder="https://yourdomain.com/terms"
                    />
                    <p class="mt-1 text-xs text-gray-500">
                        Link to your Terms of Service page. Must include http:// or https://.
                    </p>
                </div>

                <div class="mb-4">
                    <h3 class="text-lg font-medium text-white mb-1">Privacy Policy</h3>
                    <p class="text-sm text-gray-400">Provide a link to your Privacy Policy document.</p>
                </div>

                <div>
                    <label for="legal_privacy_url" class="block text-sm font-medium text-gray-400 mb-1"
                        >Privacy Policy URL</label
                    >
                    <input
                        id="legal_privacy_url"
                        type="url"
                        v-model="formData.legal_privacy_url"
                        @change="updateSetting('legal_privacy_url', formData.legal_privacy_url)"
                        class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                        placeholder="https://yourdomain.com/privacy"
                    />
                    <p class="mt-1 text-xs text-gray-500">
                        Link to your Privacy Policy page. Must include http:// or https://.
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
import { ref, watch, defineEmits } from 'vue';
import { AlertTriangleIcon } from 'lucide-vue-next';

interface Props {
    settings: Record<string, string>;
}

const props = defineProps<Props>();
const emit = defineEmits(['update']);

// Form state
const formData = ref({
    legal_tos_url: '',
    legal_privacy_url: '',
});

// Initialize form with settings values
watch(
    () => props.settings,
    (newSettings) => {
        if (newSettings) {
            formData.value = {
                legal_tos_url: newSettings['legal_tos_url'] || '',
                legal_privacy_url: newSettings['legal_privacy_url'] || '',
            };
        }
    },
    { immediate: true },
);

// Update a setting
const updateSetting = (key: string, value: string) => {
    emit('update', key, value);
};
</script>
