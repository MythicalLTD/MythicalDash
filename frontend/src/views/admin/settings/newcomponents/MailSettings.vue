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
                    @change="markChanged('smtp_enabled')"
                    class="rounded border-gray-700 text-pink-500 focus:ring-pink-500 bg-gray-800/30"
                />
                <label for="smtp_enabled" class="text-sm font-medium text-gray-400">Enable SMTP Mail</label>
            </div>

            <div v-if="formData.smtp_enabled === 'true'">
                <div class="flex items-center space-x-2 mb-4">
                    <input
                        type="checkbox"
                        id="force_mail_link"
                        v-model="formData.force_mail_link"
                        @change="markChanged('force_mail_link')"
                        class="rounded border-gray-700 text-pink-500 focus:ring-pink-500 bg-gray-800/30"
                    />
                    <label for="force_mail_link" class="text-sm font-medium text-gray-400">
                        Force users to link their email (required for registration)
                    </label>
                </div>
                <!-- SMTP Host -->
                <div class="mb-4">
                    <label for="smtp_host" class="block text-sm font-medium text-gray-400 mb-1">SMTP Host</label>
                    <input
                        id="smtp_host"
                        type="text"
                        v-model="formData.smtp_host"
                        @input="markChanged('smtp_host')"
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
                        @change="markChanged('smtp_port')"
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
                        @change="markChanged('smtp_encryption')"
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
                        @change="markChanged('smtp_user')"
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
                            autocomplete="new-password"
                            v-model="formData.smtp_pass"
                            @change="markChanged('smtp_pass')"
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
                        @change="markChanged('smtp_from')"
                        class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                        placeholder="noreply@example.com"
                    />
                    <p class="mt-1 text-xs text-gray-500">The email address that will appear in the "From" field.</p>
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
import { ref, watch } from 'vue';
import { EyeIcon, EyeOffIcon, MilkOffIcon as MailOffIcon } from 'lucide-vue-next';

// Props
const props = defineProps<{
    settings: Record<string, string>;
}>();

// Emits
const emit = defineEmits<{
    update: [key: string, value: string];
    'bulk-update': [updates: Record<string, string>];
    'test-email': [];
}>();

// Form state with default values
const formData = ref({
    smtp_enabled: 'false',
    smtp_host: '',
    smtp_port: '',
    smtp_encryption: '',
    smtp_user: '',
    smtp_pass: '',
    smtp_from: '',
    force_mail_link: 'false',
});

const showPassword = ref(false);

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
                smtp_enabled: newSettings['smtp_enabled'] || 'false',
                smtp_host: newSettings['smtp_host'] || '',
                smtp_port: newSettings['smtp_port'] || '',
                smtp_encryption: newSettings['smtp_encryption'] || '',
                smtp_user: newSettings['smtp_user'] || '',
                smtp_pass: newSettings['smtp_pass'] || '',
                smtp_from: newSettings['smtp_from'] || '',
                force_mail_link: newSettings['force_mail_link'] || 'false',
            };

            // Clear changed fields when settings are loaded
            changedFields.value.clear();
        }
    },
    { immediate: true },
);
</script>
