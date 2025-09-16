<template>
    <LayoutDashboard>
        <!-- CLI Configuration Notice -->
        <div
            class="bg-yellow-500/20 border border-yellow-500/30 text-yellow-400 p-4 rounded-lg mb-6 flex items-center justify-between"
        >
            <div class="flex items-center">
                <AlertTriangleIcon class="w-5 h-5 mr-3" />
                <div>
                    <p class="font-medium">Recommended: Use CLI for Configuration</p>
                    <p class="text-sm text-yellow-300">
                        For better reliability and advanced configuration options, we recommend using the CLI command:
                        <code class="bg-yellow-600/30 px-2 py-1 rounded text-xs font-mono"
                            >php mythicaldash settings</code
                        >
                    </p>
                </div>
            </div>
        </div>

        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">System Settings</h1>
                <p class="text-gray-400">Manage your application configuration and preferences</p>
            </div>
            <div class="flex items-center space-x-3">
                <button
                    @click="saveAllSettings"
                    :disabled="saving || !hasChanges"
                    class="flex items-center px-4 py-2 bg-pink-600 hover:bg-pink-700 disabled:bg-gray-600 disabled:cursor-not-allowed text-white rounded-lg transition-colors"
                >
                    <SaveIcon v-if="!saving" class="w-4 h-4 mr-2" />
                    <LoaderIcon v-else class="w-4 h-4 mr-2 animate-spin" />
                    {{ saving ? 'Saving...' : 'Save All Changes' }}
                </button>
                <button
                    @click="resetChanges"
                    :disabled="!hasChanges"
                    class="flex items-center px-3 py-2 bg-gray-700 hover:bg-gray-600 disabled:bg-gray-800 disabled:cursor-not-allowed text-gray-300 rounded-lg transition-colors"
                >
                    <RotateCcwIcon class="w-4 h-4 mr-2" />
                    Reset
                </button>
            </div>
        </div>

        <!-- Status Messages -->
        <div
            v-if="successMessage"
            class="bg-green-500/20 border border-green-500/30 text-green-400 p-4 rounded-lg mb-6 flex items-center"
        >
            <CheckCircleIcon class="w-5 h-5 mr-2" />
            {{ successMessage }}
        </div>

        <div
            v-if="errorMessage"
            class="bg-red-500/20 border border-red-500/30 text-red-400 p-4 rounded-lg mb-6 flex items-center"
        >
            <AlertCircleIcon class="w-5 h-5 mr-2" />
            {{ errorMessage }}
        </div>

        <!-- Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Sidebar Navigation -->
            <div class="lg:col-span-1">
                <div class="bg-gray-800/50 backdrop-blur-md rounded-lg p-4 sticky top-4">
                    <h3 class="text-lg font-semibold text-white mb-4">Settings Categories</h3>
                    <nav class="space-y-1">
                        <button
                            v-for="category in categories"
                            :key="category.id"
                            @click="activeCategory = category.id"
                            :class="[
                                'w-full flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors',
                                activeCategory === category.id
                                    ? 'bg-pink-600/20 text-pink-400 border border-pink-500/30'
                                    : 'text-gray-400 hover:text-gray-300 hover:bg-gray-700/50',
                            ]"
                        >
                            <component :is="category.icon" class="w-4 h-4 mr-3" />
                            {{ category.name }}
                            <span
                                v-if="category.badge"
                                class="ml-auto bg-pink-600 text-white text-xs px-2 py-1 rounded-full"
                            >
                                {{ category.badge }}
                            </span>
                        </button>
                    </nav>
                </div>
            </div>

            <!-- Settings Content -->
            <div class="lg:col-span-3">
                <div class="bg-gray-800/50 backdrop-blur-md rounded-lg p-6">
                    <!-- Category Header -->
                    <div class="mb-6">
                        <h2 class="text-2xl font-bold text-white mb-2">
                            {{ getCurrentCategory?.name }}
                        </h2>
                        <p class="text-gray-400">{{ getCurrentCategory?.description }}</p>
                    </div>

                    <!-- Settings Content -->
                    <div class="space-y-6">
                        <!-- General Settings -->
                        <div v-show="activeCategory === 'general'" class="space-y-6">
                            <GeneralSettings
                                :settings="settings"
                                @update="updateSetting"
                                @bulk-update="bulkUpdateSettings"
                            />
                        </div>

                        <!-- Application Settings -->
                        <div v-show="activeCategory === 'application'" class="space-y-6">
                            <ApplicationSettings
                                :settings="settings"
                                @update="updateSetting"
                                @bulk-update="bulkUpdateSettings"
                            />
                        </div>

                        <!-- Security Settings -->
                        <div v-show="activeCategory === 'security'" class="space-y-6">
                            <SecuritySettings
                                :settings="settings"
                                @update="updateSetting"
                                @bulk-update="bulkUpdateSettings"
                            />
                        </div>

                        <!-- Integration Settings -->
                        <div v-show="activeCategory === 'integrations'" class="space-y-6">
                            <IntegrationSettings
                                :settings="settings"
                                @update="updateSetting"
                                @bulk-update="bulkUpdateSettings"
                            />
                        </div>

                        <!-- Earn & Rewards Settings -->
                        <div v-show="activeCategory === 'earn'" class="space-y-6">
                            <EarnSettings
                                :settings="settings"
                                @update="updateSetting"
                                @bulk-update="bulkUpdateSettings"
                            />
                        </div>

                        <!-- Billing & Payment Settings -->
                        <div v-show="activeCategory === 'billing'" class="space-y-6">
                            <BillingSettings
                                :settings="settings"
                                @update="updateSetting"
                                @bulk-update="bulkUpdateSettings"
                            />
                        </div>

                        <!-- Legal & Compliance Settings -->
                        <div v-show="activeCategory === 'legal'" class="space-y-6">
                            <LegalSettings
                                :settings="settings"
                                @update="updateSetting"
                                @bulk-update="bulkUpdateSettings"
                            />
                        </div>

                        <!-- Custom Code Settings -->
                        <div v-show="activeCategory === 'custom-code'" class="space-y-6">
                            <CustomCodeSettings
                                :settings="settings"
                                @update="updateSetting"
                                @bulk-update="bulkUpdateSettings"
                            />
                        </div>

                        <!-- Mail Settings -->
                        <div v-show="activeCategory === 'mail'" class="space-y-6">
                            <MailSettings
                                :settings="settings"
                                @update="updateSetting"
                                @bulk-update="bulkUpdateSettings"
                            />
                        </div>

                        <!-- Image Hosting Settings -->
                        <div v-show="activeCategory === 'image-hosting'" class="space-y-6">
                            <ImageHostingSettings
                                :settings="settings"
                                @update="updateSetting"
                                @bulk-update="bulkUpdateSettings"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Changes Summary Modal -->
        <div
            v-if="showChangesModal"
            class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50"
        >
            <div class="bg-gray-800 rounded-lg p-6 max-w-2xl w-full mx-4 max-h-[80vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-white">Review Changes</h3>
                    <button @click="showChangesModal = false" class="text-gray-400 hover:text-white">
                        <XIcon class="w-5 h-5" />
                    </button>
                </div>

                <div class="space-y-4">
                    <div v-for="(change, key) in pendingChanges" :key="key" class="bg-gray-700/50 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="font-medium text-white">{{ key }}</h4>
                                <p class="text-sm text-gray-400">{{ getSettingDescription(key) }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-400 line-through">{{ change.oldValue || 'Not set' }}</p>
                                <p class="text-sm text-green-400">{{ change.newValue }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 mt-6">
                    <button
                        @click="showChangesModal = false"
                        class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        @click="confirmSaveChanges"
                        class="px-4 py-2 bg-pink-600 hover:bg-pink-700 text-white rounded-lg transition-colors"
                    >
                        Save Changes
                    </button>
                </div>
            </div>
        </div>
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import LayoutDashboard from '@/components/admin/LayoutDashboard.vue';
import { useSettingsStore } from '@/stores/settings';
import {
    Settings as SettingsIcon,
    Globe as GlobeIcon,
    Shield as ShieldIcon,
    Link as LinkIcon,
    Briefcase as BriefcaseIcon,
    CreditCard as CreditCardIcon,
    FileText as FileTextIcon,
    Code as CodeIcon,
    Database as DatabaseIcon,
    Mail as MailIcon,
    Image as ImageIcon,
    Save as SaveIcon,
    RotateCcw as RotateCcwIcon,
    CheckCircle as CheckCircleIcon,
    AlertCircle as AlertCircleIcon,
    AlertTriangle as AlertTriangleIcon,
    LoaderIcon,
    X as XIcon,
} from 'lucide-vue-next';

// Import new settings components
import GeneralSettings from './newcomponents/GeneralSettings.vue';
import MailSettings from './newcomponents/MailSettings.vue';
import SecuritySettings from './newcomponents/SecuritySettings.vue';
import IntegrationSettings from './newcomponents/IntegrationSettings.vue';
import EarnSettings from './newcomponents/EarnSettings.vue';
import BillingSettings from './newcomponents/BillingSettings.vue';
import LegalSettings from './newcomponents/LegalSettings.vue';
import CustomCodeSettings from './newcomponents/CustomCodeSettings.vue';
import ImageHostingSettings from './newcomponents/ImageHostingSettings.vue';

// Settings categories
const categories = [
    {
        id: 'general',
        name: 'General',
        icon: SettingsIcon,
        description: 'Basic application settings and configuration',
        badge: null,
    },
    {
        id: 'security',
        name: 'Security',
        icon: ShieldIcon,
        description: 'Security settings and firewall configuration',
        badge: 'Important',
    },
    {
        id: 'integrations',
        name: 'Integrations',
        icon: LinkIcon,
        description: 'Third-party integrations and API settings',
        badge: null,
    },
    {
        id: 'mail',
        name: 'Mail Server',
        icon: MailIcon,
        description: 'Email server configuration and SMTP settings',
        badge: null,
    },
    {
        id: 'image-hosting',
        name: 'Image Hosting',
        icon: ImageIcon,
        description: 'Image hosting and file upload settings',
        badge: null,
    },
    {
        id: 'earn',
        name: 'Earn & Rewards',
        icon: BriefcaseIcon,
        description: 'Earning systems and reward configurations',
        badge: null,
    },
    {
        id: 'billing',
        name: 'Billing & Payments',
        icon: CreditCardIcon,
        description: 'Payment gateways and billing settings',
        badge: null,
    },
    {
        id: 'legal',
        name: 'Legal & Compliance',
        icon: FileTextIcon,
        description: 'Legal pages and compliance settings',
        badge: null,
    },
    {
        id: 'custom-code',
        name: 'Custom Code',
        icon: CodeIcon,
        description: 'Custom CSS, JS, and code injection',
        badge: null,
    },
];

// State setup
const settingsStore = useSettingsStore();
const activeCategory = ref('general');
const settings = ref<Record<string, string>>({});
const originalSettings = ref<Record<string, string>>({});
const pendingChanges = ref<Record<string, { oldValue: string; newValue: string }>>({});
const saving = ref(false);
const successMessage = ref('');
const errorMessage = ref('');
const showChangesModal = ref(false);
const saveTimeout = ref<ReturnType<typeof setTimeout> | null>(null);

// Computed properties
const getCurrentCategory = computed(() => categories.find((cat) => cat.id === activeCategory.value));

const hasChanges = computed(() => Object.keys(pendingChanges.value).length > 0);

// Fetch settings from API
const fetchSettings = async () => {
    try {
        await settingsStore.refreshSettings();
        settings.value = { ...settingsStore.settings };
        originalSettings.value = { ...settingsStore.settings };
    } catch (error) {
        console.error('Failed to fetch settings:', error);
        errorMessage.value = 'Failed to load settings. Please try again.';
    }
};

// Update single setting
const updateSetting = (key: string, value: string) => {
    const oldValue = originalSettings.value[key] || '';

    if (value !== oldValue) {
        pendingChanges.value[key] = {
            oldValue,
            newValue: value,
        };
    } else {
        delete pendingChanges.value[key];
    }

    settings.value[key] = value;

    // Debounced auto-save
    if (saveTimeout.value) {
        clearTimeout(saveTimeout.value);
    }

    saveTimeout.value = setTimeout(() => {
        if (Object.keys(pendingChanges.value).length > 0) {
            autoSaveChanges();
        }
    }, 2000); // Auto-save after 2 seconds of inactivity
};

// Bulk update settings
const bulkUpdateSettings = (updates: Record<string, string>) => {
    Object.entries(updates).forEach(([key, value]) => {
        updateSetting(key, value);
    });
};

// Auto-save changes without showing modal
const autoSaveChanges = async () => {
    if (!hasChanges.value || saving.value) return;

    saving.value = true;
    errorMessage.value = '';

    try {
        // Process each change individually like Index.vue
        for (const [key, change] of Object.entries(pendingChanges.value)) {
            // Prepare form data for API
            const formData = new FormData();
            formData.append('key', key);
            formData.append('value', change.newValue);

            // Send to API
            const response = await fetch('/api/admin/settings/update', {
                method: 'POST',
                body: formData,
            });

            const data = await response.json();

            if (data.success) {
                // Update original settings for successful updates
                originalSettings.value[key] = change.newValue;
                delete pendingChanges.value[key];
            }
        }

        // Refresh global settings
        await settingsStore.refreshSettings();

        // Show success message if any settings were updated
        if (Object.keys(pendingChanges.value).length === 0) {
            successMessage.value = 'Settings updated successfully (A refresh may be needed (CTRL + R))';
            setTimeout(() => {
                successMessage.value = '';
            }, 3000);
        }
    } catch (error) {
        console.error('Error auto-saving settings:', error);
        errorMessage.value = 'An error occurred while auto-saving settings';
        setTimeout(() => {
            errorMessage.value = '';
        }, 5000);
    } finally {
        saving.value = false;
    }
};

// Save all changes
const saveAllSettings = async () => {
    if (!hasChanges.value) return;

    saving.value = true;
    errorMessage.value = '';
    successMessage.value = '';

    try {
        // Process each change individually like Index.vue
        for (const [key, change] of Object.entries(pendingChanges.value)) {
            // Prepare form data for API
            const formData = new FormData();
            formData.append('key', key);
            formData.append('value', change.newValue);

            // Send to API
            const response = await fetch('/api/admin/settings/update', {
                method: 'POST',
                body: formData,
            });

            const data = await response.json();

            if (data.success) {
                // Update original settings for successful updates
                originalSettings.value[key] = change.newValue;
                delete pendingChanges.value[key];
            }
        }

        // Refresh global settings
        await settingsStore.refreshSettings();

        // Show success message if any settings were updated
        if (Object.keys(pendingChanges.value).length === 0) {
            successMessage.value = 'Settings updated successfully (A refresh may be needed (CTRL + R))';
            setTimeout(() => {
                successMessage.value = '';
            }, 3000);
        } else {
            errorMessage.value = 'Failed to update some settings';
        }
    } catch (error) {
        console.error('Error updating settings:', error);
        errorMessage.value = 'An error occurred while updating settings';
    } finally {
        saving.value = false;
    }
};

// Reset changes
const resetChanges = () => {
    settings.value = { ...originalSettings.value };
    pendingChanges.value = {};
};

// Show changes modal
const showChanges = () => {
    if (hasChanges.value) {
        showChangesModal.value = true;
    }
};

// Confirm save changes
const confirmSaveChanges = () => {
    showChangesModal.value = false;
    saveAllSettings();
};

// Get setting description
const getSettingDescription = (key: string): string => {
    const descriptions: Record<string, string> = {
        APP_NAME: 'The name of your application',
        APP_URL: 'The base URL of your application',
        DISCORD_ENABLED: 'Enable Discord integration',
        SMTP_ENABLED: 'Enable email functionality',
        // Add more descriptions as needed
    };
    return descriptions[key] || 'Setting configuration';
};

// Watch for changes and show modal (removed auto-modal)
watch(pendingChanges, (changes) => {
    // Remove the auto-modal functionality since we now have auto-save
    // The modal will only be shown when user explicitly clicks save
});

// Fetch settings on component mount
onMounted(() => {
    fetchSettings();
});
</script>

<style scoped>
/* Custom scrollbar for the changes modal */
.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: #374151;
    border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #6b7280;
    border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
}
</style>
