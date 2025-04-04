<template>
    <div>
        <h2 class="text-xl font-semibold text-white mb-4">Integration Settings</h2>

        <div class="space-y-6">
            <!-- Pterodactyl Integration -->
            <div class="bg-gray-800/30 p-5 rounded-lg border border-gray-700">
                <div class="flex items-center mb-4">
                    <div class="flex-1">
                        <h3 class="text-lg font-medium text-white flex items-center">
                            <FeatherIcon class="w-5 h-5 mr-2 text-pink-400" />
                            Pterodactyl Panel
                        </h3>
                        <p class="text-sm text-gray-400">Connect to your Pterodactyl Panel for server management.</p>
                    </div>
                    <div>
                        <span
                            :class="[
                                'px-2 py-1 text-xs rounded-md',
                                pterodactylConnected ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400',
                            ]"
                        >
                            {{ pterodactylConnected ? 'Connected' : 'Not Connected' }}
                        </span>
                    </div>
                </div>

                <!-- Panel URL -->
                <div class="mb-4">
                    <label for="pterodactyl_base_url" class="block text-sm font-medium text-gray-400 mb-1"
                        >Panel URL</label
                    >
                    <input
                        id="pterodactyl_base_url"
                        type="url"
                        v-model="formData.pterodactyl_base_url"
                        @change="updateSetting('pterodactyl_base_url', formData.pterodactyl_base_url)"
                        class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                        placeholder="https://panel.yourdomain.com"
                    />
                    <p class="mt-1 text-xs text-gray-500">
                        The base URL of your Pterodactyl Panel. Must include http:// or https://.
                    </p>
                </div>

                <!-- API Key -->
                <div class="mb-4">
                    <label for="pterodactyl_api_key" class="block text-sm font-medium text-gray-400 mb-1"
                        >API Key</label
                    >
                    <div class="relative">
                        <input
                            id="pterodactyl_api_key"
                            :type="showApiKey ? 'text' : 'password'"
                            v-model="formData.pterodactyl_api_key"
                            @change="updateSetting('pterodactyl_api_key', formData.pterodactyl_api_key)"
                            class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 pr-10 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                            placeholder="ptlc_••••••••••••••••••••••••••••••"
                        />
                        <button
                            type="button"
                            @click="showApiKey = !showApiKey"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center"
                        >
                            <EyeIcon v-if="showApiKey" class="h-5 w-5 text-gray-400" />
                            <EyeOffIcon v-else class="h-5 w-5 text-gray-400" />
                        </button>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">
                        Enter your Pterodactyl API key. This should be a full access Application API key, not a Client
                        API key.
                    </p>
                </div>

                <!-- Test Connection -->
                <div class="mt-6 flex justify-end">
                    <button
                        type="button"
                        @click="testPterodactylConnection"
                        :disabled="testingConnection || !formData.pterodactyl_base_url || !formData.pterodactyl_api_key"
                        class="px-4 py-2 bg-gradient-to-r from-pink-500 to-violet-500 rounded-lg text-white hover:opacity-90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
                    >
                        <LoaderIcon v-if="testingConnection" class="animate-spin w-4 h-4 mr-2" />
                        <ActivityIcon v-else class="w-4 h-4 mr-2" />
                        Test Connection
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, defineProps, defineEmits } from 'vue';
import { EyeIcon, EyeOffIcon, ActivityIcon, LoaderIcon, FeatherIcon } from 'lucide-vue-next';

interface Props {
    settings: Record<string, string>;
}

const props = defineProps<Props>();
const emit = defineEmits(['update']);

// Form state
const formData = ref({
    pterodactyl_base_url: '',
    pterodactyl_api_key: '',
});

// UI state
const showApiKey = ref(false);
const testingConnection = ref(false);

// Computed property to check if Pterodactyl is connected
const pterodactylConnected = computed(() => {
    return formData.value.pterodactyl_base_url !== '' && formData.value.pterodactyl_api_key !== '';
});

// Initialize form with settings values
watch(
    () => props.settings,
    (newSettings) => {
        if (newSettings) {
            formData.value = {
                pterodactyl_base_url: newSettings['pterodactyl_base_url'] || '',
                pterodactyl_api_key: newSettings['pterodactyl_api_key'] || '',
            };
        }
    },
    { immediate: true },
);

// Update a setting
const updateSetting = (key: string, value: string) => {
    emit('update', key, value);
};

// Test Pterodactyl connection
const testPterodactylConnection = async () => {
    if (!formData.value.pterodactyl_base_url || !formData.value.pterodactyl_api_key) {
        return;
    }

    testingConnection.value = true;

    try {
        // Send test request to API
        const response = await fetch('/api/admin/settings/pterodactyl/test', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                url: formData.value.pterodactyl_base_url,
                key: formData.value.pterodactyl_api_key,
            }),
        });

        const data = await response.json();

        if (data.success) {
            // Show success message - this would be better handled by the parent component
            alert('Successfully connected to Pterodactyl Panel!');
        } else {
            // Show error message
            alert(`Failed to connect: ${data.message || 'Unknown error'}`);
        }
    } catch (error) {
        console.error('Error testing Pterodactyl connection:', error);
        alert('Failed to test connection. Please check your network connection and try again.');
    } finally {
        testingConnection.value = false;
    }
};
</script>
