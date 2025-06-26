<template>
    <div class="space-y-6">
        <!-- Header -->
        <div>
            <h2 class="text-xl font-bold text-gray-100 mb-2">{{ t('account.pages.image_hosting.page.title') }}</h2>
            <p class="text-gray-400">{{ t('account.pages.image_hosting.page.description') }}</p>
        </div>

        <!-- Main Content -->
        <div class="space-y-6">
            <!-- Enable/Disable Toggles -->
            <CardComponent class="p-6">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-white">
                                {{ t('account.pages.image_hosting.toggles.hosting.title') }}
                            </h3>
                            <p class="text-sm text-gray-400">
                                {{ t('account.pages.image_hosting.toggles.hosting.description') }}
                            </p>
                        </div>
                        <button
                            @click="toggleImageHosting"
                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2"
                            :class="imageHostingEnabled ? 'bg-pink-500' : 'bg-gray-700'"
                        >
                            <span
                                class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                                :class="imageHostingEnabled ? 'translate-x-6' : 'translate-x-1'"
                            />
                        </button>
                    </div>
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-white">
                                {{ t('account.pages.image_hosting.toggles.embed.title') }}
                            </h3>
                            <p class="text-sm text-gray-400">
                                {{ t('account.pages.image_hosting.toggles.embed.description') }}
                            </p>
                        </div>
                        <button
                            @click="toggleEmbedEnabled"
                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2"
                            :class="embedSettings.enabled ? 'bg-pink-500' : 'bg-gray-700'"
                        >
                            <span
                                class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                                :class="embedSettings.enabled ? 'translate-x-6' : 'translate-x-1'"
                            />
                        </button>
                    </div>
                </div>
            </CardComponent>

            <!-- Embed Settings -->
            <CardComponent v-if="imageHostingEnabled && embedSettings.enabled && imageHostingEnabledGlobal" class="p-6">
                <div class="space-y-4">
                    <h3 class="text-lg font-medium text-white">
                        {{ t('account.pages.image_hosting.settings.title') }}
                    </h3>
                    <p class="text-sm text-gray-400">{{ t('account.pages.image_hosting.settings.description') }}</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">{{
                                t('account.pages.image_hosting.settings.embed_title.label')
                            }}</label>
                            <input
                                type="text"
                                v-model="embedSettings.title"
                                class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                                :placeholder="t('account.pages.image_hosting.settings.embed_title.placeholder')"
                            />
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-400 mb-1">{{
                                t('account.pages.image_hosting.settings.embed_description.label')
                            }}</label>
                            <textarea
                                v-model="embedSettings.description"
                                class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                                rows="2"
                                :placeholder="t('account.pages.image_hosting.settings.embed_description.placeholder')"
                            ></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">{{
                                t('account.pages.image_hosting.settings.embed_color.label')
                            }}</label>
                            <input
                                type="color"
                                v-model="embedSettings.color"
                                class="bg-gray-800/30 border border-gray-700 rounded-lg px-2 py-2 w-full h-10 focus:outline-none focus:ring-2 focus:ring-pink-500"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1">{{
                                t('account.pages.image_hosting.settings.author_name.label')
                            }}</label>
                            <input
                                type="text"
                                v-model="embedSettings.authorName"
                                class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                                :placeholder="t('account.pages.image_hosting.settings.author_name.placeholder')"
                            />
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <Button
                            :loading="savingEmbedSettings"
                            @click="saveEmbedSettings"
                            class="bg-gradient-to-r from-pink-500 to-violet-500"
                        >
                            {{
                                savingEmbedSettings
                                    ? t('account.pages.image_hosting.settings.saving')
                                    : t('account.pages.image_hosting.settings.save_button')
                            }}
                        </Button>
                    </div>
                </div>
            </CardComponent>

            <!-- Embed Preview -->
            <CardComponent v-if="imageHostingEnabled && embedSettings.enabled && imageHostingEnabledGlobal" class="p-6">
                <div class="space-y-4">
                    <h3 class="text-lg font-medium text-white">{{ t('account.pages.image_hosting.preview.title') }}</h3>
                    <p class="text-sm text-gray-400">{{ t('account.pages.image_hosting.preview.description') }}</p>

                    <div class="max-w-2xl mx-auto">
                        <!-- Fake Discord Chat -->
                        <div class="bg-[#313338] rounded-lg p-6 shadow-lg">
                            <!-- Fake conversation above embed -->
                            <div class="mb-4 space-y-3">
                                <div class="flex items-start gap-3">
                                    <img :src="Session.getInfo('avatar')" class="w-10 h-10 rounded-full object-cover" />
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-semibold text-[#f2f3f5]">{{
                                                Session.getInfo('username')
                                            }}</span>
                                            <span class="text-xs text-gray-400"
                                                >Today at {{ new Date().toLocaleTimeString() }}</span
                                            >
                                        </div>
                                        <div class="text-[#dbdee1]">
                                            {{ t('account.pages.image_hosting.preview.image_label') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Your message with embed -->
                            <div class="flex items-start gap-3">
                                <!-- Avatar Placeholder -->
                                <img :src="Session.getInfo('avatar')" class="w-10 h-10 rounded-full object-cover" />
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-[#f2f3f5]">{{
                                            Session.getInfo('username')
                                        }}</span>
                                        <span class="text-xs text-gray-400"
                                            >Today at {{ new Date().toLocaleTimeString() }}</span
                                        >
                                    </div>
                                    <div class="text-[#dbdee1] mb-2">
                                        {{ t('account.pages.image_hosting.preview.link_label') }}:
                                        <a
                                            :href="embedSettings.url"
                                            target="_blank"
                                            class="text-[#00b0f4] hover:underline"
                                            >{{ embedSettings.url }}</a
                                        >
                                    </div>
                                    <!-- Embed -->
                                    <div
                                        :style="{
                                            background: '#2f3136',
                                            borderRadius: '0.5rem',
                                            padding: '1.25rem',
                                            borderLeft: `4px solid ${embedSettings.color}`,
                                            boxShadow: '0 2px 8px 0 #00000040',
                                        }"
                                        class="mt-1"
                                    >
                                        <div class="flex flex-col space-y-1">
                                            <div
                                                v-if="embedSettings.authorName"
                                                class="text-xs font-semibold text-[#7289da] mb-1"
                                            >
                                                {{ embedSettings.authorName }}
                                            </div>
                                            <div class="text-lg font-bold text-white leading-tight">
                                                {{
                                                    embedSettings.title ||
                                                    t('account.pages.image_hosting.preview.default_title')
                                                }}
                                            </div>
                                            <div class="text-sm text-gray-200 mt-1 whitespace-pre-line">
                                                {{
                                                    embedSettings.description ||
                                                    t('account.pages.image_hosting.preview.default_description')
                                                }}
                                            </div>
                                            <div class="flex justify-center items-center mt-3">
                                                <img
                                                    :src="embedSettings.image"
                                                    :alt="t('account.pages.image_hosting.preview.image_alt')"
                                                    class="rounded-md max-h-48 object-contain bg-gray-800"
                                                    style="min-width: 120px; min-height: 80px"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </CardComponent>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import CardComponent from '@/components/client/ui/Card/CardComponent.vue';
import Button from '@/components/client/ui/Button.vue';
import Session from '@/mythicaldash/Session';
import { useI18n } from 'vue-i18n';
import Swal from 'sweetalert2';
import { useSound } from '@vueuse/sound';
import successAlertSfx from '@/assets/sounds/success.mp3';
import failedAlertSfx from '@/assets/sounds/error.mp3';
import { useSettingsStore } from '@/stores/settings';

const Settings = useSettingsStore();
const { play: playSuccess } = useSound(successAlertSfx);
const { play: playError } = useSound(failedAlertSfx);
const { t } = useI18n();

// State
const savingEmbedSettings = ref(false);
const imageHostingEnabled = ref(Session.getInfo('image_hosting_enabled') === 'true');
const imageHostingEnabledGlobal = ref(Settings.getSetting('image_hosting_enabled'));

// Embed settings
const embedSettings = ref({
    enabled: Session.getInfo('image_hosting_embed_enabled') === 'true',
    title: Session.getInfo('image_hosting_embed_title') || 'MythicalDash Image',
    description:
        Session.getInfo('image_hosting_embed_description') ||
        'MythicalDash ImageHosting is a nice plugin bridge for creating a image hosting inside mythicaldash :D',
    color: Session.getInfo('image_hosting_embed_color') || '#000000',
    image: Session.getInfo('image_hosting_embed_image') || 'https://cdn.mythical.systems/background.gif',
    thumbnail: Session.getInfo('image_hosting_embed_thumbnail') || 'https://cdn.mythical.systems/background.gif',
    url: Session.getInfo('image_hosting_embed_url') || 'https://cdn.mythical.systems/background.gif',
    authorName: Session.getInfo('image_hosting_embed_author_name') || 'MythicalDash',
});

// Toggle image hosting
const toggleImageHosting = async () => {
    try {
        const response = await fetch('/api/user/images/toggle', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                enabled: !imageHostingEnabled.value,
            }),
        });

        if (!response.ok) {
            playError();
            Swal.fire({
                icon: 'error',
                title: t('image_hosting.alerts.error.title'),
                text: t('image_hosting.alerts.error.toggle_hosting'),
                footer: t('image_hosting.alerts.error.footer'),
            });
            return;
        }

        imageHostingEnabled.value = !imageHostingEnabled.value;
        playSuccess();
        Swal.fire({
            icon: 'success',
            title: t('image_hosting.alerts.success.title'),
            text: t('image_hosting.alerts.success.toggle_hosting'),
            footer: t('image_hosting.alerts.success.footer'),
        });
    } catch (error) {
        console.error('Error toggling image hosting:', error);
        playError();
        Swal.fire({
            icon: 'error',
            title: t('image_hosting.alerts.error.title'),
            text: t('image_hosting.alerts.error.generic'),
            footer: t('image_hosting.alerts.error.footer'),
        });
    }
};

// Toggle embed enabled
const toggleEmbedEnabled = async () => {
    try {
        const response = await fetch('/api/user/images/embed-settings/toggle', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                enabled: !embedSettings.value.enabled,
            }),
        });

        if (!response.ok) {
            playError();
            Swal.fire({
                icon: 'error',
                title: t('image_hosting.alerts.error.title'),
                text: t('image_hosting.alerts.error.toggle_embed'),
                footer: t('image_hosting.alerts.error.footer'),
            });
            return;
        }

        embedSettings.value.enabled = !embedSettings.value.enabled;
        playSuccess();
        Swal.fire({
            icon: 'success',
            title: t('image_hosting.alerts.success.title'),
            text: t('image_hosting.alerts.success.toggle_embed'),
            footer: t('image_hosting.alerts.success.footer'),
        });
    } catch (error) {
        console.error('Error toggling embed:', error);
        playError();
        Swal.fire({
            icon: 'error',
            title: t('image_hosting.alerts.error.title'),
            text: t('image_hosting.alerts.error.generic'),
            footer: t('image_hosting.alerts.error.footer'),
        });
    }
};

// Save embed settings
const saveEmbedSettings = async () => {
    savingEmbedSettings.value = true;
    try {
        const response = await fetch('/api/user/images/embed/settings', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(embedSettings.value),
        });

        if (!response.ok) {
            playError();
            Swal.fire({
                icon: 'error',
                title: t('image_hosting.alerts.error.title'),
                text: t('image_hosting.alerts.error.save_embed'),
                footer: t('image_hosting.alerts.error.footer'),
            });
            return;
        }

        playSuccess();
        Swal.fire({
            icon: 'success',
            title: t('image_hosting.alerts.success.title'),
            text: t('image_hosting.alerts.success.save_embed'),
            footer: t('image_hosting.alerts.success.footer'),
        });
    } catch (error) {
        console.error('Error saving embed settings:', error);
        playError();
        Swal.fire({
            icon: 'error',
            title: t('image_hosting.alerts.error.title'),
            text: t('image_hosting.alerts.error.generic'),
            footer: t('image_hosting.alerts.error.footer'),
        });
    } finally {
        savingEmbedSettings.value = false;
    }
};
</script>
