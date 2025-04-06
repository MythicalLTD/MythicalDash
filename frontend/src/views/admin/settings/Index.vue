<template>
    <LayoutDashboard>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-400">Dashboard Settings</h1>
        </div>

        <div class="bg-gray-800/50 backdrop-blur-md rounded-lg p-6">
            <!-- Settings Tabs -->
            <div class="mb-6 border-b border-gray-700">
                <div class="flex flex-wrap -mb-px">
                    <button
                        v-for="tab in tabs"
                        :key="tab.id"
                        @click="activeTab = tab.id"
                        :class="[
                            'inline-flex items-center px-4 py-2 text-sm font-medium border-b-2 rounded-t-lg',
                            activeTab === tab.id
                                ? 'text-pink-400 border-pink-400'
                                : 'text-gray-400 border-transparent hover:text-gray-300 hover:border-gray-500',
                        ]"
                    >
                        <component :is="tab.icon" class="w-4 h-4 mr-2" />
                        {{ tab.name }}
                    </button>
                </div>
            </div>

            <!-- Saving Indicator -->
            <div v-if="saving" class="bg-blue-500/20 text-blue-400 p-4 rounded-lg mb-6 flex items-center">
                <LoaderIcon class="w-5 h-5 mr-2 animate-spin" />
                Saving settings...
            </div>

            <!-- Success Message -->
            <div v-if="successMessage" class="bg-green-500/20 text-green-400 p-4 rounded-lg mb-6">
                {{ successMessage }}
            </div>

            <!-- Error Message -->
            <div v-if="errorMessage" class="bg-red-500/20 text-red-400 p-4 rounded-lg mb-6">
                {{ errorMessage }}
            </div>

            <!-- Settings Content -->
            <div>
                <!-- General Settings -->
                <div v-show="activeTab === 'general'" class="space-y-6">
                    <GeneralSettings :settings="settings" @update="updateSettings" />
                </div>

                <!-- Mail Settings -->
                <div v-show="activeTab === 'mail'" class="space-y-6">
                    <MailSettings :settings="settings" @update="updateSettings" @test-email="testEmailSettings" />
                </div>

                <!-- Security Settings -->
                <div v-show="activeTab === 'security'" class="space-y-6">
                    <SecuritySettings :settings="settings" @update="updateSettings" />
                </div>

                <!-- Integration Settings -->
                <div v-show="activeTab === 'integrations'" class="space-y-6">
                    <IntegrationSettings :settings="settings" @update="updateSettings" />
                </div>

                <!-- Earn & Rewards Settings -->
                <div v-show="activeTab === 'earn'" class="space-y-6">
                    <EarnSettings :settings="settings" @update="updateSettings" />
                </div>

                <!-- Legal Settings -->
                <div v-show="activeTab === 'legal'" class="space-y-6">
                    <LegalSettings :settings="settings" @update="updateSettings" />
                </div>

                <!-- Billing Settings -->
                <div v-show="activeTab === 'billing'" class="space-y-6">
                    <BillingSettings :settings="settings" @update="updateSettings" />
                </div>
            </div>
        </div>
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import LayoutDashboard from '@/components/admin/LayoutDashboard.vue';
import { useSettingsStore } from '@/stores/settings';
import {
    Settings as SettingsIcon,
    Mail as MailIcon,
    Shield as ShieldIcon,
    Link as LinkIcon,
    Briefcase as BriefcaseIcon,
    FileText as FileTextIcon,
    CreditCard as CreditCardIcon,
    LoaderIcon,
} from 'lucide-vue-next';

// Import settings components
import GeneralSettings from './components/GeneralSettings.vue';
import MailSettings from './components/MailSettings.vue';
import SecuritySettings from './components/SecuritySettings.vue';
import IntegrationSettings from './components/IntegrationSettings.vue';
import EarnSettings from './components/EarnSettings.vue';
import BillingSettings from './components/BillingSettings.vue';
import LegalSettings from './components/LegalSettings.vue';

// Settings tabs
const tabs = [
    { id: 'general', name: 'General', icon: SettingsIcon },
    { id: 'mail', name: 'Mail', icon: MailIcon },
    { id: 'security', name: 'Security', icon: ShieldIcon },
    { id: 'integrations', name: 'Integrations', icon: LinkIcon },
    { id: 'earn', name: 'Earn & Rewards', icon: BriefcaseIcon },
    { id: 'legal', name: 'Legal', icon: FileTextIcon },
    { id: 'billing', name: 'Billing', icon: CreditCardIcon },
];

// State setup
const settingsStore = useSettingsStore();
const activeTab = ref('general');
const settings = ref<Record<string, string>>({});
const saving = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

// Fetch settings from API
const fetchSettings = async () => {
    try {
        await settingsStore.refreshSettings();
        settings.value = { ...settingsStore.settings };
    } catch (error) {
        console.error('Failed to fetch settings:', error);
        errorMessage.value = 'Failed to load settings. Please try again.';
    }
};

// Update settings
const updateSettings = async (key: string, value: string) => {
    saving.value = true;
    errorMessage.value = '';
    successMessage.value = '';

    try {
        // Update settings in local state
        settings.value[key] = value;

        // Prepare form data for API
        const formData = new FormData();
        formData.append('key', key);
        formData.append('value', value);

        // Send to API
        const response = await fetch('/api/admin/settings/update', {
            method: 'POST',
            body: formData,
        });

        const data = await response.json();

        if (data.success) {
            successMessage.value = 'Settings updated successfully';
            // Refresh global settings
            await settingsStore.refreshSettings();

            // Clear success message after 3 seconds
            setTimeout(() => {
                successMessage.value = '';
            }, 3000);
        } else {
            errorMessage.value = data.message || 'Failed to update settings';
        }
    } catch (error) {
        console.error('Error updating settings:', error);
        errorMessage.value = 'An error occurred while updating settings';
    } finally {
        saving.value = false;
    }
};

// Test email settings
const testEmailSettings = async (email: string) => {
    saving.value = true;
    errorMessage.value = '';
    successMessage.value = '';

    try {
        // Prepare form data
        const formData = new FormData();
        formData.append('email', email);

        // Send test email request
        const response = await fetch('/api/admin/settings/mail/test', {
            method: 'POST',
            body: formData,
        });

        const data = await response.json();

        if (data.success) {
            successMessage.value = 'Test email sent successfully';

            // Clear success message after 3 seconds
            setTimeout(() => {
                successMessage.value = '';
            }, 3000);
        } else {
            errorMessage.value = data.message || 'Failed to send test email';
        }
    } catch (error) {
        console.error('Error sending test email:', error);
        errorMessage.value = 'An error occurred while sending test email';
    } finally {
        saving.value = false;
    }
};

// Fetch settings on component mount
onMounted(() => {
    fetchSettings();
});
</script>
