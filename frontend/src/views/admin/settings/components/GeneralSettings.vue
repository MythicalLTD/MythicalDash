<template>
    <div>
        <h2 class="text-xl font-semibold text-white mb-4">General Settings</h2>

        <div class="space-y-6">
            <!-- App Name -->
            <div>
                <label for="app_name" class="block text-sm font-medium text-gray-400 mb-1">App Name</label>
                <input
                    id="app_name"
                    type="text"
                    v-model="formData.app_name"
                    @change="updateSetting('app_name', formData.app_name)"
                    class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                />
                <p class="mt-1 text-xs text-gray-500">
                    The name of your application displayed in the browser title and throughout the dashboard.
                </p>
            </div>

            <!-- App Language -->
            <div>
                <label for="app_lang" class="block text-sm font-medium text-gray-400 mb-1">Default Language</label>
                <select
                    id="app_lang"
                    v-model="formData.app_lang"
                    @change="updateSetting('app_lang', formData.app_lang)"
                    class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                >
                    <option value="en" selected>English</option>
                </select>
                <p class="mt-1 text-xs text-gray-500">
                    The default language for the dashboard. Users can change their individual language preference.
                </p>
            </div>

            <!-- App URL -->
            <div>
                <label for="app_url" class="block text-sm font-medium text-gray-400 mb-1">App URL</label>
                <input
                    id="app_url"
                    type="url"
                    v-model="formData.app_url"
                    @change="updateSetting('app_url', formData.app_url)"
                    class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                    placeholder="https://yourdomain.com"
                />
                <p class="mt-1 text-xs text-gray-500">The URL of your application. Must include http:// or https://.</p>
            </div>

            <!-- App Timezone -->
            <div>
                <label for="app_timezone" class="block text-sm font-medium text-gray-400 mb-1">Timezone</label>
                <select
                    id="app_timezone"
                    v-model="formData.app_timezone"
                    @change="updateSetting('app_timezone', formData.app_timezone)"
                    class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                >
                    <option value="UTC">UTC</option>
                    <option value="America/New_York">Eastern Time (US & Canada)</option>
                    <option value="America/Chicago">Central Time (US & Canada)</option>
                    <option value="America/Denver">Mountain Time (US & Canada)</option>
                    <option value="America/Los_Angeles">Pacific Time (US & Canada)</option>
                    <option value="Europe/London">London</option>
                    <option value="Europe/Paris">Paris</option>
                    <option value="Europe/Berlin">Berlin</option>
                    <option value="Europe/Madrid">Madrid</option>
                    <option value="Europe/Rome">Rome</option>
                    <option value="Europe/Amsterdam">Amsterdam</option>
                    <option value="Asia/Tokyo">Tokyo</option>
                    <option value="Asia/Shanghai">Shanghai</option>
                    <option value="Australia/Sydney">Sydney</option>
                </select>
                <p class="mt-1 text-xs text-gray-500">
                    The timezone for your application. This affects how dates and times are displayed.
                </p>
            </div>

            <!-- App Logo -->
            <div>
                <label for="app_logo" class="block text-sm font-medium text-gray-400 mb-1">App Logo URL</label>
                <div class="flex gap-4">
                    <input
                        id="app_logo"
                        type="url"
                        v-model="formData.app_logo"
                        @change="updateSetting('app_logo', formData.app_logo)"
                        class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                        placeholder="https://yourdomain.com/logo.png"
                    />
                    <div class="h-10 w-10 bg-gray-800 rounded-lg flex items-center justify-center">
                        <img
                            v-if="formData.app_logo"
                            :src="formData.app_logo"
                            alt="Logo Preview"
                            class="max-h-8 max-w-8"
                        />
                        <ImageIcon v-else class="h-5 w-5 text-gray-500" />
                    </div>
                </div>
                <p class="mt-1 text-xs text-gray-500">URL for your application logo. Recommended size: 512x512px.</p>
            </div>

            <!-- SEO Settings -->
            <div class="pt-4 border-t border-gray-700">
                <h3 class="text-lg font-medium text-white mb-3">SEO Settings</h3>

                <!-- SEO Description -->
                <div class="mb-4">
                    <label for="seo_description" class="block text-sm font-medium text-gray-400 mb-1"
                        >Meta Description</label
                    >
                    <textarea
                        id="seo_description"
                        v-model="formData.seo_description"
                        @change="updateSetting('seo_description', formData.seo_description)"
                        rows="2"
                        class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                        placeholder="A brief description of your application"
                    ></textarea>
                    <p class="mt-1 text-xs text-gray-500">
                        A short description of your application for search engines. Recommended: 150-160 characters.
                    </p>
                </div>

                <!-- SEO Keywords -->
                <div>
                    <label for="seo_keywords" class="block text-sm font-medium text-gray-400 mb-1">Meta Keywords</label>
                    <input
                        id="seo_keywords"
                        type="text"
                        v-model="formData.seo_keywords"
                        @change="updateSetting('seo_keywords', formData.seo_keywords)"
                        class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                        placeholder="dashboard, game, hosting, etc."
                    />
                    <p class="mt-1 text-xs text-gray-500">
                        Comma-separated keywords to help search engines categorize your site.
                    </p>
                </div>
            </div>

            <!-- App Version (Display Only) -->
            <div class="pt-4 border-t border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-medium text-white">App Version</h3>
                        <p class="text-sm text-gray-400">Current version of the dashboard</p>
                    </div>
                    <div class="text-gray-300 bg-gray-800/50 px-3 py-1.5 rounded-lg border border-gray-700">
                        {{ formData.app_version || 'Unknown' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, watch, defineEmits } from 'vue';
import { ImageIcon } from 'lucide-vue-next';

interface Props {
    settings: Record<string, string>;
}

const props = defineProps<Props>();
const emit = defineEmits(['update']);

// Form state with default values
const formData = ref({
    app_name: '',
    app_lang: 'en',
    app_url: '',
    app_timezone: 'UTC',
    app_logo: '',
    app_version: '',
    seo_description: '',
    seo_keywords: '',
});

// Initialize form with settings values
watch(
    () => props.settings,
    (newSettings) => {
        if (newSettings) {
            formData.value = {
                app_name: newSettings['app_name'] || '',
                app_lang: newSettings['app_lang'] || 'en',
                app_url: newSettings['app_url'] || '',
                app_timezone: newSettings['app_timezone'] || 'UTC',
                app_logo: newSettings['app_logo'] || '',
                app_version: newSettings['app_version'] || '',
                seo_description: newSettings['seo_description'] || '',
                seo_keywords: newSettings['seo_keywords'] || '',
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
