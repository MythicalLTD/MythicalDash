<template>
    <div>
        <h2 class="text-xl font-semibold text-white mb-4">Mail Settings</h2>
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
                    For security reasons, existing mail settings cannot be viewed. Any changes you make will override
                    the current settings.
                </span>
            </div>
        </div>
        <div class="space-y-6">
            <!-- SMTP Enabled -->
            <div class="flex items-center space-x-2">
                <input
                    type="checkbox"
                    id="smtp_enabled"
                    v-model="formData.smtp_enabled"
                    @change="updateSetting('smtp_enabled', formData.smtp_enabled ? 'true' : 'false')"
                    class="rounded border-gray-700 text-pink-500 focus:ring-pink-500 bg-gray-800/30"
                />
                <label for="smtp_enabled" class="text-sm font-medium text-gray-400">Enable SMTP Mail</label>
            </div>

            <div v-if="formData.smtp_enabled === 'true'">
                <!-- SMTP Host -->
                <div class="mb-4">
                    <label for="smtp_host" class="block text-sm font-medium text-gray-400 mb-1">SMTP Host</label>
                    <input
                        id="smtp_host"
                        type="text"
                        v-model="formData.smtp_host"
                        @change="updateSetting('smtp_host', formData.smtp_host)"
                        class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                        placeholder="smtp.example.com"
                    />
                    <p class="mt-1 text-xs text-gray-500">The hostname of your SMTP server.</p>
                </div>

                <!-- SMTP Port -->
                <div class="mb-4">
                    <label for="smtp_port" class="block text-sm font-medium text-gray-400 mb-1">SMTP Port</label>
                    <input
                        id="smtp_port"
                        type="number"
                        v-model="formData.smtp_port"
                        @change="updateSetting('smtp_port', formData.smtp_port)"
                        class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                        placeholder="587"
                    />
                    <p class="mt-1 text-xs text-gray-500">Common ports: 25, 465 (SSL), 587 (TLS), 2525.</p>
                </div>

                <!-- SMTP Encryption -->
                <div class="mb-4">
                    <label for="smtp_encryption" class="block text-sm font-medium text-gray-400 mb-1">Encryption</label>
                    <select
                        id="smtp_encryption"
                        v-model="formData.smtp_encryption"
                        @change="updateSetting('smtp_encryption', formData.smtp_encryption)"
                        class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                    >
                        <option value="">None</option>
                        <option value="tls">TLS</option>
                        <option value="ssl">SSL</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Select the encryption method required by your SMTP server.</p>
                </div>

                <!-- SMTP Username -->
                <div class="mb-4">
                    <label for="smtp_user" class="block text-sm font-medium text-gray-400 mb-1">SMTP Username</label>
                    <input
                        id="smtp_user"
                        type="text"
                        v-model="formData.smtp_user"
                        @change="updateSetting('smtp_user', formData.smtp_user)"
                        class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                        placeholder="username@example.com"
                    />
                    <p class="mt-1 text-xs text-gray-500">The username for authenticating with your SMTP server.</p>
                </div>

                <!-- SMTP Password -->
                <div class="mb-4">
                    <label for="smtp_pass" class="block text-sm font-medium text-gray-400 mb-1">SMTP Password</label>
                    <div class="relative">
                        <input
                            id="smtp_pass"
                            :type="showPassword ? 'text' : 'password'"
                            v-model="formData.smtp_pass"
                            @change="updateSetting('smtp_pass', formData.smtp_pass)"
                            class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 pr-10 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                            placeholder="••••••••"
                        />
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center"
                            :aria-label="showPassword ? 'Hide password' : 'Show password'"
                        >
                            <EyeIcon v-if="showPassword" class="h-5 w-5 text-gray-400" />
                            <EyeOffIcon v-else class="h-5 w-5 text-gray-400" />
                        </button>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">The password for authenticating with your SMTP server.</p>
                </div>

                <!-- From Email -->
                <div class="mb-4">
                    <label for="smtp_from" class="block text-sm font-medium text-gray-400 mb-1"
                        >From Email Address</label
                    >
                    <input
                        id="smtp_from"
                        type="email"
                        v-model="formData.smtp_from"
                        @change="updateSetting('smtp_from', formData.smtp_from)"
                        class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                        placeholder="noreply@example.com"
                    />
                    <p class="mt-1 text-xs text-gray-500">The email address that will appear in the "From" field.</p>
                </div>

                <!-- Test Email -->
                <div class="mt-6 pt-4 border-t border-gray-700">
                    <h3 class="text-md font-medium text-white mb-3">Test Email Configuration</h3>
                    <div class="flex gap-2">
                        <input
                            type="email"
                            v-model="testEmail"
                            placeholder="Enter test email address"
                            class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                        />
                        <button
                            type="button"
                            @click="sendTestEmail"
                            :disabled="testEmailInProgress || !testEmail"
                            class="px-4 py-2 bg-gradient-to-r from-pink-500 to-violet-500 rounded-lg text-white hover:opacity-90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center whitespace-nowrap"
                        >
                            <LoaderIcon v-if="testEmailInProgress" class="animate-spin w-4 h-4 mr-2" />
                            <SendIcon v-else class="w-4 h-4 mr-2" />
                            Send Test
                        </button>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Send a test email to verify your SMTP configuration.</p>
                </div>
            </div>

            <div v-else class="bg-gray-800/30 p-4 rounded-lg border border-gray-700">
                <div class="flex items-center text-gray-400">
                    <MailOffIcon class="w-5 h-5 mr-2 text-gray-500" />
                    SMTP mail delivery is currently disabled. Enable it to configure mail settings.
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, watch, defineEmits } from 'vue';
import { EyeIcon, EyeOffIcon, LoaderIcon, SendIcon, MilkOffIcon as MailOffIcon } from 'lucide-vue-next';

interface Props {
    settings: Record<string, string>;
}

const props = defineProps<Props>();
const emit = defineEmits(['update', 'test-email']);

// Form state with default values
const formData = ref({
    smtp_enabled: 'false',
    smtp_host: '',
    smtp_port: '',
    smtp_encryption: '',
    smtp_user: '',
    smtp_pass: '',
    smtp_from: '',
});

const showPassword = ref(false);
const testEmail = ref('');
const testEmailInProgress = ref(false);

// Initialize form with settings values
watch(
    () => props.settings,
    (newSettings) => {
        if (newSettings) {
            formData.value = {
                smtp_enabled: newSettings['smtp_enabled'] || 'false',
                smtp_host: newSettings['smtp_host'] || '',
                smtp_port: newSettings['smtp_port'] || '',
                smtp_encryption: newSettings['smtp_encryption'] || '',
                smtp_user: newSettings['smtp_user'] || '',
                smtp_pass: newSettings['smtp_pass'] || '',
                smtp_from: newSettings['smtp_from'] || '',
            };
        }
    },
    { immediate: true },
);

// Update a setting
const updateSetting = (key: string, value: string) => {
    emit('update', key, value);
};

// Send test email
const sendTestEmail = () => {
    if (!testEmail.value) return;

    testEmailInProgress.value = true;

    // Emit event to parent
    emit('test-email', testEmail.value);

    // Reset progress after delay (parent will handle the actual API call)
    setTimeout(() => {
        testEmailInProgress.value = false;
    }, 1000);
};
</script>
