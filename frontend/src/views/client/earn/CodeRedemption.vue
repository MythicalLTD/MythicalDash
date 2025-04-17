<template>
    <LayoutDashboard>
        <div class="p-6">
            <h1 class="text-2xl font-bold text-white mb-6">{{ t('code_redemption.pages.index.title') }}</h1>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Code Redemption Card -->
                <div class="lg:col-span-2">
                    <CardComponent cardTitle="Redeem Code" cardDescription="Enter a code to earn rewards">
                        <div class="relative overflow-hidden">
                            <!-- Background decorative elements -->
                            <div
                                class="absolute -top-20 -right-20 w-40 h-40 bg-indigo-500/5 rounded-full blur-2xl"
                            ></div>
                            <div
                                class="absolute -bottom-20 -left-20 w-40 h-40 bg-purple-500/5 rounded-full blur-2xl"
                            ></div>

                            <div class="relative z-10">
                                <!-- Code Input -->
                                <div class="mb-6">
                                    <div class="bg-gray-800/50 rounded-xl p-8">
                                        <form @submit.prevent="redeemCode" class="space-y-4">
                                            <div>
                                                <label
                                                    for="code"
                                                    class="block text-sm font-medium text-gray-400 mb-2"
                                                    >{{ t('code_redemption.pages.index.form.label') }}</label
                                                >
                                                <input
                                                    type="text"
                                                    id="code"
                                                    v-model="codeInput"
                                                    class="w-full px-4 py-3 rounded-lg border border-gray-700 bg-gray-800/50 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                                    :placeholder="t('code_redemption.pages.index.form.placeholder')"
                                                />
                                            </div>
                                            <div>
                                                <button
                                                    type="submit"
                                                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-3 rounded-lg transition-colors duration-200"
                                                    :disabled="isRedeeming"
                                                >
                                                    <span v-if="isRedeeming">
                                                        <SpinnerIcon class="inline-block w-5 h-5 mr-2 animate-spin" />
                                                        {{ t('code_redemption.pages.index.form.redeeming') }}
                                                    </span>
                                                    <span v-else>{{
                                                        t('code_redemption.pages.index.form.redeem')
                                                    }}</span>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <!-- Coin Balance -->
                                <div class="bg-gray-800/30 rounded-xl p-5 mb-6">
                                    <div class="flex justify-between items-center">
                                        <div class="flex items-center">
                                            <div
                                                class="w-10 h-10 rounded-full bg-yellow-500/20 flex items-center justify-center mr-3"
                                            >
                                                <Coins class="h-5 w-5 text-yellow-500" />
                                            </div>
                                            <div>
                                                <div class="text-sm text-gray-400">
                                                    {{ t('code_redemption.pages.index.form.currentBalance') }}
                                                </div>
                                                <div class="text-2xl font-bold text-yellow-500">{{ totalCoins }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardComponent>
                </div>

                <!-- Sidebar with Common Codes and Ads -->
                <div class="space-y-6">
                    <!-- Common Codes Card -->
                    <CardComponent cardTitle="Code Tips" cardDescription="Where to find codes">
                        <div class="space-y-4 p-2">
                            <div class="bg-gray-800/30 p-3 rounded-lg">
                                <div class="flex items-center mb-2">
                                    <TwitterIcon class="w-5 h-5 text-blue-400 mr-2" />
                                    <div class="text-sm font-medium text-white">
                                        {{ t('code_redemption.pages.index.social.twitter.title') }}
                                    </div>
                                </div>
                                <p class="text-xs text-gray-400">
                                    {{ t('code_redemption.pages.index.social.twitter.description') }}
                                </p>
                            </div>

                            <div class="bg-gray-800/30 p-3 rounded-lg">
                                <div class="flex items-center mb-2">
                                    <DiscordIcon class="w-5 h-5 text-indigo-400 mr-2" />
                                    <div class="text-sm font-medium text-white">
                                        {{ t('code_redemption.pages.index.social.discord.title') }}
                                    </div>
                                </div>
                                <p class="text-xs text-gray-400">
                                    {{ t('code_redemption.pages.index.social.discord.description') }}
                                </p>
                            </div>

                            <div class="bg-gray-800/30 p-3 rounded-lg">
                                <div class="flex items-center mb-2">
                                    <CalendarIcon class="w-5 h-5 text-purple-400 mr-2" />
                                    <div class="text-sm font-medium text-white">
                                        {{ t('code_redemption.pages.index.social.events.title') }}
                                    </div>
                                </div>
                                <p class="text-xs text-gray-400">
                                    {{ t('code_redemption.pages.index.social.events.description') }}
                                </p>
                            </div>
                        </div>
                    </CardComponent>

                    <!-- Ad Square -->
                    <div class="p-3 bg-gray-800/30 border border-gray-700/30 rounded-lg">
                        <div class="flex justify-between items-center">
                            <div class="text-xs text-gray-500">Advertisement</div>
                            <button class="text-xs text-gray-500 hover:text-gray-400">×</button>
                        </div>
                        <div class="h-80 flex items-center justify-center text-gray-600 text-sm">
                            [Square Ad Placement]
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </LayoutDashboard>
</template>

<script setup lang="ts">
import LayoutDashboard from '@/components/client/LayoutDashboard.vue';
import CardComponent from '@/components/client/ui/Card/CardComponent.vue';
import { ref } from 'vue';
import { Loader as SpinnerIcon, Coins, Calendar as CalendarIcon } from 'lucide-vue-next';
import Session from '@/mythicaldash/Session';
import { useSettingsStore } from '@/stores/settings';
import router from '@/router';
import { useSound } from '@vueuse/sound';
import failedAlertSfx from '@/assets/sounds/error.mp3';
import successAlertSfx from '@/assets/sounds/success.mp3';
import Swal from 'sweetalert2';
import { useI18n } from 'vue-i18n';

const { play: playError } = useSound(failedAlertSfx);
const { play: playSuccess } = useSound(successAlertSfx);
const { t } = useI18n();
// Custom icons
const TwitterIcon = defineComponent({
    setup() {
        return () =>
            h(
                'svg',
                {
                    xmlns: 'http://www.w3.org/2000/svg',
                    viewBox: '0 0 24 24',
                    fill: 'none',
                    stroke: 'currentColor',
                    'stroke-width': '2',
                    'stroke-linecap': 'round',
                    'stroke-linejoin': 'round',
                },
                [
                    h('path', {
                        d: 'M22 4s-.7 2.1-2 3.4c1.6 10-9.4 17.3-18 11.6 2.2.1 4.4-.6 6-2C3 15.5.5 9.6 3 5c2.2 2.6 5.6 4.1 9 4-.9-4.2 4-6.6 7-3.8 1.1 0 3-1.2 3-1.2z',
                    }),
                ],
            );
    },
});

const DiscordIcon = defineComponent({
    setup() {
        return () =>
            h(
                'svg',
                {
                    xmlns: 'http://www.w3.org/2000/svg',
                    viewBox: '0 0 24 24',
                    fill: 'none',
                    stroke: 'currentColor',
                    'stroke-width': '2',
                    'stroke-linecap': 'round',
                    'stroke-linejoin': 'round',
                },
                [
                    h('path', { d: 'M9 11s1-1 3-1 3 1 3 1' }),
                    h('path', {
                        d: 'M7 5c-1.5 2.9-1.2 5.5-1.2 5.5C3.4 12.4 4 17 4 17l2 1c1.6-1 3-1.5 4-2 2 0 3 .5 5 2l2-1s.6-4.6-1.8-6.5c0 0 .3-2.6-1.2-5.5',
                    }),
                    h('path', { d: 'M7 5h10' }),
                ],
            );
    },
});

import { MythicalDOM } from '@/mythicaldash/MythicalDOM';
MythicalDOM.setPageTitle(t('code_redemption.pages.index.title'));

const Settings = useSettingsStore();
import { defineComponent, h } from 'vue';

// If code redemption is disabled, redirect to dashboard
if (Settings.getSetting('code_redemption_enabled') === 'false') {
    Swal.fire({
        title: t('code_redemption.notEnabled.title'),
        text: t('code_redemption.notEnabled.text'),
        icon: 'error',
        confirmButtonText: t('code_redemption.notEnabled.button'),
    });
    playError();
    router.push('/dashboard');
}

// State
const codeInput = ref('');
const isRedeeming = ref(false);
const totalCoins = ref(Session.getInfoInt('credits'));

// Handle code redemption
const redeemCode = async () => {
    if (!codeInput.value.trim()) {
        Swal.fire({
            icon: 'error',
            title: t('code_redemption.pages.index.alerts.error.title'),
            text: t('code_redemption.pages.index.alerts.error.generic'),
            footer: t('code_redemption.pages.index.alerts.error.footer'),
            confirmButtonText: t('code_redemption.pages.index.alerts.error.button'),
            showConfirmButton: true,
        });
        return;
    }

    isRedeeming.value = true;

    try {
        // Call API to redeem code
        const formData = new FormData();
        formData.append('code', codeInput.value.trim());

        const response = await fetch('/api/user/earn/redeem', {
            method: 'POST',
            body: formData,
        });

        const data = await response.json();

        if (response.ok && data.success === true) {
            // Code was successfully redeemed
            Swal.fire({
                icon: 'success',
                title: t('code_redemption.pages.index.alerts.success.title'),
                text: data.message || t('code_redemption.pages.index.alerts.success.code_redeemed'),
                footer: t('code_redemption.pages.index.alerts.success.footer'),
                showConfirmButton: true,
            });
            playSuccess();
            // Update coin balance if applicable
            if (data.credits_added) {
                totalCoins.value += parseInt(data.credits_added);
            }

            // Clear input
            codeInput.value = '';
        } else {
            // Error redeeming code
            Swal.fire({
                icon: 'error',
                title: t('code_redemption.pages.index.alerts.error.title'),
                text: data.message || t('code_redemption.pages.index.alerts.error.generic'),
                footer: t('code_redemption.pages.index.alerts.error.footer'),
                confirmButtonText: t('code_redemption.pages.index.alerts.error.button'),
                showConfirmButton: true,
            });
            playError();
        }
    } catch (error) {
        console.error('Error redeeming code:', error);
        Swal.fire({
            icon: 'error',
            title: t('code_redemption.pages.index.alerts.error.title'),
            text: t('code_redemption.pages.index.alerts.error.generic'),
            footer: t('code_redemption.pages.index.alerts.error.footer'),
            confirmButtonText: t('code_redemption.pages.index.alerts.error.button'),
            showConfirmButton: true,
        });
        playError();
    } finally {
        isRedeeming.value = false;
    }
};
</script>

<style scoped>
/* Add some nice hover effects for the history items */
.history-item:hover {
    background-color: rgba(79, 70, 229, 0.1);
}

/* Animated spinner for the loading state */
.animate-spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}
</style>
