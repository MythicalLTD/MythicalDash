<template>
    <LayoutDashboard>
        <div class="p-6">
            <h1 class="text-2xl font-bold text-white mb-6">{{ t('l4r.pages.index.title') }}</h1>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main L4R Content -->
                <div class="lg:col-span-2">
                    <CardComponent
                        :cardTitle="t('l4r.pages.index.title')"
                        :cardDescription="t('l4r.pages.index.subTitle')"
                    >
                        <div class="relative overflow-hidden">
                            <!-- Background decorative elements -->
                            <div
                                class="absolute -top-20 -right-20 w-40 h-40 bg-indigo-500/5 rounded-full blur-2xl"
                            ></div>
                            <div
                                class="absolute -bottom-20 -left-20 w-40 h-40 bg-purple-500/5 rounded-full blur-2xl"
                            ></div>
                            <div class="relative z-10 p-6">
                                <!-- Link Provider Cards -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Linkvertise -->
                                    <div
                                        v-if="Settings.getSetting('l4r_linkadvertise_enabled') === 'true'"
                                        class="bg-gray-800/50 rounded-xl overflow-hidden border border-gray-700/30"
                                    >
                                        <div class="p-5">
                                            <div class="flex items-center mb-3">
                                                <img
                                                    :src="getProviderLogo('linkvertise')"
                                                    alt="Linkvertise"
                                                    class="h-10 w-10 mr-3 rounded-lg"
                                                />
                                                <div>
                                                    <h3 class="text-lg font-bold text-white">
                                                        {{ t('l4r.pages.index.linkvertise.title') }}
                                                    </h3>
                                                    <span class="text-xs text-gray-400">{{
                                                        t('l4r.pages.index.linkvertise.tag')
                                                    }}</span>
                                                </div>
                                            </div>

                                            <p class="text-gray-300 text-sm mb-4">
                                                {{ t('l4r.pages.index.linkvertise.description') }}
                                            </p>

                                            <div class="flex items-center justify-between">
                                                <div
                                                    class="flex items-center bg-indigo-900/40 text-indigo-400 px-3 py-1 rounded-full text-xs font-medium"
                                                >
                                                    <Coins class="h-3 w-3 mr-1" />
                                                    {{
                                                        t('l4r.pages.index.linkvertise.reward', [
                                                            Settings.getSetting('l4r_linkadvertise_coins_per_link'),
                                                        ])
                                                    }}
                                                </div>

                                                <button
                                                    @click="goToLinkProvider('linkvertise')"
                                                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors bg-indigo-600 hover:bg-indigo-700 text-white"
                                                >
                                                    {{ t('l4r.pages.index.linkvertise.button') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- ShareUs -->
                                    <div
                                        v-if="Settings.getSetting('l4r_shareus_enabled') === 'true'"
                                        class="bg-gray-800/50 rounded-xl overflow-hidden border border-gray-700/30"
                                    >
                                        <div class="p-5">
                                            <div class="flex items-center mb-3">
                                                <img
                                                    :src="getProviderLogo('shareus')"
                                                    alt="ShareUs"
                                                    class="h-10 w-10 mr-3 rounded-lg"
                                                />
                                                <div>
                                                    <h3 class="text-lg font-bold text-white">
                                                        {{ t('l4r.pages.index.shareus.title') }}
                                                    </h3>
                                                    <span class="text-xs text-gray-400">
                                                        {{ t('l4r.pages.index.shareus.tag') }}
                                                    </span>
                                                </div>
                                            </div>

                                            <p class="text-gray-300 text-sm mb-4">
                                                {{ t('l4r.pages.index.shareus.description') }}
                                            </p>

                                            <div class="flex items-center justify-between">
                                                <div
                                                    class="flex items-center bg-indigo-900/40 text-indigo-400 px-3 py-1 rounded-full text-xs font-medium"
                                                >
                                                    <Coins class="h-3 w-3 mr-1" />
                                                    {{
                                                        t('l4r.pages.index.shareus.reward', [
                                                            Settings.getSetting('l4r_shareus_coins_per_link'),
                                                        ])
                                                    }}
                                                </div>

                                                <button
                                                    @click="goToLinkProvider('shareus')"
                                                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors bg-indigo-600 hover:bg-indigo-700 text-white"
                                                >
                                                    {{ t('l4r.pages.index.shareus.button') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- LinkPays -->
                                    <div
                                        v-if="Settings.getSetting('l4r_linkpays_enabled') === 'true'"
                                        class="bg-gray-800/50 rounded-xl overflow-hidden border border-gray-700/30"
                                    >
                                        <div class="p-5">
                                            <div class="flex items-center mb-3">
                                                <img
                                                    :src="getProviderLogo('linkpays')"
                                                    alt="LinkPays"
                                                    class="h-10 w-10 mr-3 rounded-lg"
                                                />
                                                <div>
                                                    <h3 class="text-lg font-bold text-white">
                                                        {{ t('l4r.pages.index.linkpays.title') }}
                                                    </h3>
                                                    <span class="text-xs text-gray-400">
                                                        {{ t('l4r.pages.index.linkpays.tag') }}
                                                    </span>
                                                </div>
                                            </div>

                                            <p class="text-gray-300 text-sm mb-4">
                                                {{ t('l4r.pages.index.linkpays.description') }}
                                            </p>

                                            <div class="flex items-center justify-between">
                                                <div
                                                    class="flex items-center bg-indigo-900/40 text-indigo-400 px-3 py-1 rounded-full text-xs font-medium"
                                                >
                                                    <Coins class="h-3 w-3 mr-1" />
                                                    {{
                                                        t('l4r.pages.index.linkpays.reward', [
                                                            Settings.getSetting('l4r_linkpays_coins_per_link'),
                                                        ])
                                                    }}
                                                </div>

                                                <button
                                                    @click="goToLinkProvider('linkpays')"
                                                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors bg-indigo-600 hover:bg-indigo-700 text-white"
                                                >
                                                    {{ t('l4r.pages.index.linkpays.button') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- GyaniLinks -->
                                    <div
                                        v-if="Settings.getSetting('l4r_gyanilinks_enabled') === 'true'"
                                        class="bg-gray-800/50 rounded-xl overflow-hidden border border-gray-700/30"
                                    >
                                        <div class="p-5">
                                            <div class="flex items-center mb-3">
                                                <img
                                                    :src="getProviderLogo('gyanilinks')"
                                                    alt="GyaniLinks"
                                                    class="h-10 w-10 mr-3 rounded-lg"
                                                />
                                                <div>
                                                    <h3 class="text-lg font-bold text-white">
                                                        {{ t('l4r.pages.index.gyanilinks.title') }}
                                                    </h3>
                                                    <span class="text-xs text-gray-400">
                                                        {{ t('l4r.pages.index.gyanilinks.tag') }}
                                                    </span>
                                                </div>
                                            </div>

                                            <p class="text-gray-300 text-sm mb-4">
                                                {{ t('l4r.pages.index.gyanilinks.description') }}
                                            </p>

                                            <div class="flex items-center justify-between">
                                                <div
                                                    class="flex items-center bg-indigo-900/40 text-indigo-400 px-3 py-1 rounded-full text-xs font-medium"
                                                >
                                                    <Coins class="h-3 w-3 mr-1" />
                                                    {{
                                                        t('l4r.pages.index.gyanilinks.reward', [
                                                            Settings.getSetting('l4r_gyanilinks_coins_per_link'),
                                                        ])
                                                    }}
                                                </div>

                                                <button
                                                    @click="goToLinkProvider('gyanilinks')"
                                                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors bg-indigo-600 hover:bg-indigo-700 text-white"
                                                >
                                                    {{ t('l4r.pages.index.gyanilinks.button') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardComponent>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Stats Card -->
                    <CardComponent
                        :cardTitle="t('l4r.pages.index.yourStats.title')"
                        :cardDescription="t('l4r.pages.index.yourStats.description')"
                    >
                        <div class="p-5 space-y-4">
                            <!-- Coin Balance -->
                            <div class="bg-gray-800/30 rounded-xl p-5 flex justify-between items-center">
                                <div class="flex items-center">
                                    <div
                                        class="w-10 h-10 rounded-full bg-yellow-500/20 flex items-center justify-center mr-3"
                                    >
                                        <Coins class="h-5 w-5 text-yellow-500" />
                                    </div>
                                    <div>
                                        <div class="text-sm text-gray-400">
                                            {{ t('l4r.pages.index.yourStats.currentBalance') }}
                                        </div>
                                        <div class="text-2xl font-bold text-yellow-500">{{ totalCoins }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardComponent>

                    <!-- How It Works Card -->
                    <CardComponent
                        :cardTitle="t('l4r.pages.index.steps.title')"
                        :cardDescription="t('l4r.pages.index.steps.description')"
                    >
                        <div class="p-4 space-y-4">
                            <div class="bg-gray-800/30 p-4 rounded-lg">
                                <div class="flex items-start mb-2">
                                    <div
                                        class="bg-indigo-900/50 text-indigo-400 w-5 h-5 rounded-full flex items-center justify-center mr-2 flex-shrink-0"
                                    >
                                        <span class="text-xs">{{ t('l4r.pages.index.steps.one.number') }}</span>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-white mb-1">
                                            {{ t('l4r.pages.index.steps.one.title') }}
                                        </h4>
                                        <p class="text-xs text-gray-400">
                                            {{ t('l4r.pages.index.steps.one.description') }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-800/30 p-4 rounded-lg">
                                <div class="flex items-start mb-2">
                                    <div
                                        class="bg-indigo-900/50 text-indigo-400 w-5 h-5 rounded-full flex items-center justify-center mr-2 flex-shrink-0"
                                    >
                                        <span class="text-xs">{{ t('l4r.pages.index.steps.two.number') }}</span>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-white mb-1">
                                            {{ t('l4r.pages.index.steps.two.title') }}
                                        </h4>
                                        <p class="text-xs text-gray-400">
                                            {{ t('l4r.pages.index.steps.two.description') }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-800/30 p-4 rounded-lg">
                                <div class="flex items-start mb-2">
                                    <div
                                        class="bg-indigo-900/50 text-indigo-400 w-5 h-5 rounded-full flex items-center justify-center mr-2 flex-shrink-0"
                                    >
                                        <span class="text-xs">{{ t('l4r.pages.index.steps.three.number') }}</span>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-white mb-1">
                                            {{ t('l4r.pages.index.steps.three.title') }}
                                        </h4>
                                        <p class="text-xs text-gray-400">
                                            {{ t('l4r.pages.index.steps.three.description') }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-800/30 p-4 rounded-lg">
                                <div class="flex items-start mb-2">
                                    <div
                                        class="bg-indigo-900/50 text-indigo-400 w-5 h-5 rounded-full flex items-center justify-center mr-2 flex-shrink-0"
                                    >
                                        <span class="text-xs">{{ t('l4r.pages.index.steps.four.number') }}</span>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-white mb-1">
                                            {{ t('l4r.pages.index.steps.four.title') }}
                                        </h4>
                                        <p class="text-xs text-gray-400">
                                            {{ t('l4r.pages.index.steps.four.description') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardComponent>
                </div>
            </div>
        </div>
    </LayoutDashboard>
</template>

<script setup lang="ts">
import LayoutDashboard from '@/components/client/LayoutDashboard.vue';
import CardComponent from '@/components/client/ui/Card/CardComponent.vue';
import { ref } from 'vue';
import { Coins } from 'lucide-vue-next';
import Session from '@/mythicaldash/Session';
import { useSettingsStore } from '@/stores/settings';
import router from '@/router';
import Swal from 'sweetalert2';
import { MythicalDOM } from '@/mythicaldash/MythicalDOM';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const Settings = useSettingsStore();

MythicalDOM.setPageTitle(t('l4r.pages.index.title'));

// If L4R is disabled, redirect to dashboard
if (Settings.getSetting('l4r_enabled') === 'false') {
    Swal.fire({
        title: t('l4r.notEnabled.title'),
        text: t('l4r.notEnabled.text'),
        icon: 'error',
        footer: t('l4r.notEnabled.footer'),
        confirmButtonText: t('l4r.notEnabled.confirmButtonText'),
    });
    router.push('/dashboard');
}

// State
const totalCoins = ref(Session.getInfoInt('credits'));

// Get logo for provider
const getProviderLogo = (provider: string): string => {
    const logoMap: Record<string, string> = {
        linkvertise: 'https://cdn.mythical.systems/mythicaldash/linkvertise.png',
        shareus: 'https://cdn.mythical.systems/mythicaldash/shareus.png',
        linkpays: 'https://cdn.mythical.systems/mythicaldash/linkpays.jpg',
        gyanilinks: 'http://cdn.mythical.systems/mythicaldash/gyanilinks.png',
    };

    return logoMap[provider.toLowerCase()] || 'https://via.placeholder.com/48';
};

// Go to link provider
const goToLinkProvider = async (providerKey: string) => {
    if (providerKey === 'linkvertise') {
        window.open('/api/user/earn/l4r/linkvertise/start');
    }

    if (providerKey === 'shareus') {
        window.open('/api/user/earn/l4r/shareus/start');
    }

    if (providerKey === 'linkpays') {
        window.open('/api/user/earn/l4r/linkpays/start');
    }

    if (providerKey === 'gyanilinks') {
        window.open('/api/user/earn/l4r/gyanilinks/start');
    }
};
</script>

<style scoped>
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
