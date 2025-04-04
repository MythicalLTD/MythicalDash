<template>
    <div>
        <h2 class="text-xl font-semibold text-white mb-4">Security Settings</h2>
        <div class="bg-yellow-500/10 border border-yellow-500/20 rounded-lg p-4 mb-6">
            <div class="flex items-center text-yellow-500">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5 mr-2"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <path
                        d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"
                    />
                    <line x1="12" y1="9" x2="12" y2="13" />
                    <line x1="12" y1="17" x2="12.01" y2="17" />
                </svg>
                <span class="text-sm">
                    For security reasons, existing security settings cannot be viewed. Any changes you make will
                    override the current settings.
                </span>
            </div>
        </div>
        <div class="space-y-6">
            <!-- Cloudflare Turnstile Section -->
            <div class="bg-gray-800/30 p-5 rounded-lg border border-gray-700">
                <div class="flex items-center mb-4">
                    <div class="flex-1">
                        <h3 class="text-lg font-medium text-white">Cloudflare Turnstile</h3>
                        <p class="text-sm text-gray-400">
                            Protect your forms from spam and abuse with Cloudflare Turnstile, a friendly CAPTCHA
                            alternative.
                        </p>
                    </div>
                    <div class="ml-4">
                        <a
                            href="https://www.cloudflare.com/products/turnstile/"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="text-pink-400 hover:text-pink-300 text-sm flex items-center"
                        >
                            <ExternalLinkIcon class="w-3.5 h-3.5 mr-1" />
                            Learn More
                        </a>
                    </div>
                </div>

                <!-- Turnstile Enabled -->
                <div class="flex items-center space-x-2 mb-4">
                    <input
                        type="checkbox"
                        id="turnstile_enabled"
                        v-model="turnstileEnabled"
                        @change="updateSetting('turnstile_enabled', turnstileEnabled ? 'true' : 'false')"
                        class="rounded border-gray-700 text-pink-500 focus:ring-pink-500 bg-gray-800/30"
                    />
                    <label for="turnstile_enabled" class="text-sm font-medium text-gray-400"
                        >Enable Turnstile Protection</label
                    >
                </div>

                <div v-if="turnstileEnabled">
                    <!-- Turnstile Site Key -->
                    <div class="mb-4">
                        <label for="turnstile_key_pub" class="block text-sm font-medium text-gray-400 mb-1"
                            >Site Key (Public)</label
                        >
                        <input
                            id="turnstile_key_pub"
                            type="text"
                            v-model="formData.turnstile_key_pub"
                            @change="updateSetting('turnstile_key_pub', formData.turnstile_key_pub)"
                            class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                            placeholder="1x00000000000000000000AA"
                        />
                        <p class="mt-1 text-xs text-gray-500">Your Cloudflare Turnstile site key (public key).</p>
                    </div>

                    <!-- Turnstile Secret Key -->
                    <div>
                        <label for="turnstile_key_priv" class="block text-sm font-medium text-gray-400 mb-1"
                            >Secret Key</label
                        >
                        <div class="relative">
                            <input
                                id="turnstile_key_priv"
                                :type="showSecretKey ? 'text' : 'password'"
                                v-model="formData.turnstile_key_priv"
                                @change="updateSetting('turnstile_key_priv', formData.turnstile_key_priv)"
                                class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 pr-10 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                                placeholder="1x0000000000000000000000000000000AA"
                            />
                            <button
                                type="button"
                                @click="showSecretKey = !showSecretKey"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center"
                            >
                                <EyeIcon v-if="showSecretKey" class="h-5 w-5 text-gray-400" />
                                <EyeOffIcon v-else class="h-5 w-5 text-gray-400" />
                            </button>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">
                            Your Cloudflare Turnstile secret key. Keep this confidential.
                        </p>
                    </div>
                </div>
            </div>

            <!-- License Key Section -->
            <div class="bg-gray-800/30 p-5 rounded-lg border border-gray-700">
                <h3 class="text-lg font-medium text-white mb-1">License Key</h3>
                <p class="text-sm text-gray-400 mb-4">
                    Your MythicalDash license key that validates your installation.
                </p>

                <div>
                    <label for="license_key" class="block text-sm font-medium text-gray-400 mb-1">License Key</label>
                    <div class="relative">
                        <input
                            id="license_key"
                            :type="showLicenseKey ? 'text' : 'password'"
                            v-model="formData.license_key"
                            @change="updateSetting('license_key', formData.license_key)"
                            class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 pr-10 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                            placeholder="XXXX-XXXX-XXXX-XXXX"
                        />
                        <button
                            type="button"
                            @click="showLicenseKey = !showLicenseKey"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center"
                        >
                            <EyeIcon v-if="showLicenseKey" class="h-5 w-5 text-gray-400" />
                            <EyeOffIcon v-else class="h-5 w-5 text-gray-400" />
                        </button>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">
                        Enter your MythicalDash license key. If you don't have one, please contact support.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, defineProps, defineEmits } from 'vue';
import { EyeIcon, EyeOffIcon, ExternalLinkIcon } from 'lucide-vue-next';

interface Props {
    settings: Record<string, string>;
}

const props = defineProps<Props>();
const emit = defineEmits(['update']);

// Form state
const formData = ref({
    turnstile_key_pub: '',
    turnstile_key_priv: '',
    license_key: '',
});

// Show/hide sensitive data
const showSecretKey = ref(false);
const showLicenseKey = ref(false);

// Computed property for turnstile enabled state
const turnstileEnabled = computed({
    get: () => props.settings?.turnstile_enabled === 'true',
    set: (value) => {
        emit('update', 'turnstile_enabled', value ? 'true' : 'false');
    },
});

// Initialize form with settings values
watch(
    () => props.settings,
    (newSettings) => {
        if (newSettings) {
            formData.value = {
                turnstile_key_pub: newSettings['turnstile_key_pub'] || '',
                turnstile_key_priv: newSettings['turnstile_key_priv'] || '',
                license_key: newSettings['license_key'] || '',
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
