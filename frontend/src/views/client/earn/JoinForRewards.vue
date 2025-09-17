<template>
    <LayoutDashboard>
        <div class="p-6">
            <h1 class="text-2xl font-bold text-white mb-6">{{ t('j4r.pages.index.title') }}</h1>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main J4R Content -->
                <div class="lg:col-span-2">
                    <CardComponent
                        :cardTitle="t('j4r.pages.index.serverList.title')"
                        :cardDescription="t('j4r.pages.index.serverList.description')"
                    >
                        <div class="relative overflow-hidden">
                            <!-- Background decorative elements -->
                            <div
                                class="absolute -top-20 -right-20 w-40 h-40 bg-indigo-500/5 rounded-full blur-2xl"
                            ></div>
                            <div
                                class="absolute -bottom-20 -left-20 w-40 h-40 bg-purple-500/5 rounded-full blur-2xl"
                            ></div>

                            <div class="relative z-10">
                                <!-- Claim Rewards Button -->
                                <div class="mb-6">
                                    <Button
                                        @click="claimRewards"
                                        variant="primary"
                                        :disabled="isClaiming || !discordLinked"
                                        :loading="isClaiming"
                                        fullWidth
                                        large
                                        class="flex items-center justify-center"
                                    >
                                        <template #icon>
                                            <Coins class="w-6 h-6" />
                                        </template>
                                        {{ t('j4r.pages.index.claimButton') }}
                                    </Button>

                                    <div v-if="!discordLinked" class="mt-3 text-center">
                                        <p class="text-sm text-amber-400">
                                            {{ t('j4r.pages.index.alerts.error.discord_not_linked') }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Server List -->
                                <div class="mb-6">
                                    <div v-if="isLoading" class="py-10 flex flex-col items-center justify-center">
                                        <Loader class="w-12 h-12 text-indigo-500 animate-spin mb-3" />
                                        <p class="text-gray-400">{{ t('j4r.pages.index.loading') }}</p>
                                    </div>

                                    <div
                                        v-else-if="servers.length === 0"
                                        class="py-10 flex flex-col items-center justify-center"
                                    >
                                        <ServerOff class="w-16 h-16 text-gray-600 mb-3" />
                                        <p class="text-gray-400 text-center">{{ t('j4r.pages.index.empty') }}</p>
                                        <p class="text-gray-500 text-sm text-center mt-2">
                                            Check back later for new opportunities
                                        </p>
                                    </div>

                                    <div v-else class="space-y-4">
                                        <!-- Server Cards -->
                                        <div
                                            v-for="server in servers"
                                            :key="server.id"
                                            class="bg-gray-800/50 rounded-xl overflow-hidden border border-gray-700/50 hover:border-gray-600/50 transition-colors"
                                        >
                                            <div class="flex flex-col md:flex-row">
                                                <!-- Server Icon -->
                                                <div class="md:w-1/4 relative">
                                                    <div
                                                        class="h-36 md:h-full w-full bg-gray-700/50 flex items-center justify-center"
                                                    >
                                                        <img
                                                            v-if="server.icon_url"
                                                            :src="server.icon_url"
                                                            alt="Server Icon"
                                                            class="w-16 h-16 rounded-full"
                                                        />
                                                        <div
                                                            v-else
                                                            class="w-16 h-16 rounded-full bg-indigo-600 flex items-center justify-center"
                                                        >
                                                            <ServerOff class="w-8 h-8 text-white" />
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Server Info -->
                                                <div class="md:w-3/4 p-5">
                                                    <div class="flex justify-between items-start mb-3">
                                                        <div>
                                                            <h3 class="text-lg font-bold text-white">
                                                                {{ server.name }}
                                                            </h3>
                                                            <p v-if="server.description" class="text-sm text-gray-400">
                                                                {{ server.description }}
                                                            </p>
                                                        </div>
                                                        <div class="flex items-center">
                                                            <div
                                                                class="bg-indigo-900/40 text-indigo-400 px-3 py-1 rounded-full text-xs font-medium inline-flex items-center"
                                                            >
                                                                <Coins class="h-3 w-3 mr-1" />
                                                                {{ server.coins }}
                                                                {{ t('j4r.pages.index.serverList.coins') }}
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="flex items-center justify-between">
                                                        <div
                                                            v-if="server.joined"
                                                            class="text-green-400 text-sm flex items-center"
                                                        >
                                                            <CheckCircle class="w-4 h-4 mr-1" />
                                                            {{ t('j4r.pages.index.serverList.joined') }}
                                                        </div>
                                                        <div v-else class="text-gray-400 text-sm flex items-center">
                                                            <XCircle class="w-4 h-4 mr-1" />
                                                            {{ t('j4r.pages.index.serverList.notJoined') }}
                                                        </div>

                                                        <a
                                                            :href="server.invite_url"
                                                            target="_blank"
                                                            rel="noopener noreferrer"
                                                            class="inline-flex items-center justify-center gap-2 px-3 py-1.5 text-sm font-medium rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition-all duration-200 shadow-sm shadow-indigo-900/20 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-indigo-500/50 focus:ring-offset-[#12121f]"
                                                        >
                                                            {{ t('j4r.pages.index.serverList.inviteLink') }}
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Status Messages -->
                                <transition
                                    enter-active-class="transition-opacity duration-300"
                                    leave-active-class="transition-opacity duration-300"
                                    enter-from-class="opacity-0"
                                    enter-to-class="opacity-100"
                                    leave-from-class="opacity-100"
                                    leave-to-class="opacity-0"
                                >
                                    <div
                                        v-if="statusMessage.text"
                                        :class="[
                                            'p-4 rounded-lg mt-6',
                                            statusMessage.type === 'success'
                                                ? 'bg-emerald-900/30 border border-emerald-700/50 text-emerald-400'
                                                : 'bg-red-900/30 border border-red-700/50 text-red-400',
                                        ]"
                                    >
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0">
                                                <CheckCircle v-if="statusMessage.type === 'success'" class="h-5 w-5" />
                                                <AlertTriangle v-else class="h-5 w-5" />
                                            </div>
                                            <div class="ml-3">
                                                <p class="font-medium">{{ statusMessage.text }}</p>
                                            </div>
                                            <button @click="statusMessage.text = ''" class="ml-auto">
                                                <X class="h-4 w-4" />
                                            </button>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                        </div>
                    </CardComponent>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Stats Card -->
                    <CardComponent
                        :cardTitle="t('j4r.pages.index.stats.title')"
                        :cardDescription="t('j4r.pages.index.stats.description')"
                    >
                        <div class="p-1 space-y-4">
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
                                            {{ t('j4r.pages.index.stats.currentBalance') }}
                                        </div>
                                        <div class="text-2xl font-bold text-yellow-500">{{ totalCoins }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Stats Grid -->
                            <div class="grid grid-cols-2 gap-3">
                                <div class="bg-gray-800/30 rounded-xl p-4">
                                    <div class="text-sm text-gray-400">
                                        {{ t('j4r.pages.index.stats.serversJoined') }}
                                    </div>
                                    <div class="text-xl font-bold text-white">{{ joinedServers }}</div>
                                </div>
                                <div class="bg-gray-800/30 rounded-xl p-4">
                                    <div class="text-sm text-gray-400">
                                        {{ t('j4r.pages.index.stats.availableServers') }}
                                    </div>
                                    <div class="text-xl font-bold text-white">{{ availableServers }}</div>
                                </div>
                            </div>

                            <div class="bg-gray-800/30 rounded-xl p-4">
                                <div class="text-sm text-gray-400">{{ t('j4r.pages.index.stats.totalEarned') }}</div>
                                <div class="text-xl font-bold text-green-400">{{ earnedCoins }}</div>
                            </div>

                            <div class="bg-gray-800/30 rounded-xl p-4">
                                <div class="text-sm text-gray-400">{{ t('j4r.pages.index.stats.remainingCoins') }}</div>
                                <div class="text-xl font-bold text-yellow-400">{{ remainingCoins }}</div>
                            </div>
                        </div>
                    </CardComponent>

                    <!-- How It Works Card -->
                    <CardComponent
                        :cardTitle="t('j4r.pages.index.steps.title')"
                        :cardDescription="t('j4r.pages.index.steps.description')"
                    >
                        <div class="space-y-4">
                            <div v-for="(step, index) in steps" :key="index" class="flex items-start space-x-3">
                                <div
                                    class="flex-shrink-0 w-8 h-8 bg-indigo-600 rounded-full flex items-center justify-center text-white text-sm font-bold"
                                >
                                    {{ step.number }}
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-white">{{ step.title }}</h4>
                                    <p class="text-xs text-gray-400 mt-1">{{ step.description }}</p>
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
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import LayoutDashboard from '@/components/client/LayoutDashboard.vue';
import CardComponent from '@/components/client/ui/Card/CardComponent.vue';
import Button from '@/components/client/ui/Button.vue';
import { Coins, Loader, ServerOff, CheckCircle, XCircle, AlertTriangle, X } from 'lucide-vue-next';

// TypeScript interfaces
interface J4RServer {
    id: number;
    name: string;
    server_id: string;
    description?: string;
    icon_url?: string;
    coins: number;
    joined: boolean;
    invite_code: string;
    invite_url: string;
}

interface StatusMessage {
    text: string;
    type: 'success' | 'error' | '';
}

const { t } = useI18n();

// Reactive data
const isLoading = ref(true);
const isClaiming = ref(false);
const discordLinked = ref(false);
const servers = ref<J4RServer[]>([]);
const totalCoins = ref(0);
const joinedServers = ref(0);
const availableServers = ref(0);
const earnedCoins = ref(0);
const remainingCoins = ref(0);
const statusMessage = ref<StatusMessage>({ text: '', type: '' });

// Steps for how it works
const steps = ref([
    {
        number: '1',
        title: t('j4r.pages.index.steps.one.title'),
        description: t('j4r.pages.index.steps.one.description'),
    },
    {
        number: '2',
        title: t('j4r.pages.index.steps.two.title'),
        description: t('j4r.pages.index.steps.two.description'),
    },
    {
        number: '3',
        title: t('j4r.pages.index.steps.three.title'),
        description: t('j4r.pages.index.steps.three.description'),
    },
    {
        number: '4',
        title: t('j4r.pages.index.steps.four.title'),
        description: t('j4r.pages.index.steps.four.description'),
    },
]);

// Load J4R data
const loadJ4RData = async () => {
    try {
        isLoading.value = true;

        // Load server list
        const listResponse = await fetch('/api/user/j4r/list');
        const listData = await listResponse.json();

        if (listData.success) {
            servers.value = listData.servers;
            joinedServers.value = listData.joined_servers;
            availableServers.value = listData.available_servers;
        }

        // Load status for stats
        const statusResponse = await fetch('/api/user/j4r/status');
        const statusData = await statusResponse.json();

        if (statusData.success) {
            discordLinked.value = statusData.discord_linked;
            totalCoins.value = statusData.total_coins || 0;
            earnedCoins.value = statusData.earned_coins || 0;
            remainingCoins.value = statusData.remaining_coins || 0;
        }
    } catch (error) {
        console.error('Failed to load J4R data:', error);
        showStatusMessage(t('j4r.pages.index.alerts.error.generic'), 'error');
    } finally {
        isLoading.value = false;
    }
};

// Claim rewards
const claimRewards = async () => {
    if (isClaiming.value || !discordLinked.value) return;

    try {
        isClaiming.value = true;
        statusMessage.value = { text: '', type: '' };

        // Redirect to J4R check endpoint
        window.location.href = '/api/user/j4r/check';
    } catch (error) {
        console.error('Failed to claim rewards:', error);
        showStatusMessage(t('j4r.pages.index.alerts.error.claim_failed'), 'error');
    } finally {
        isClaiming.value = false;
    }
};

// Show status message
const showStatusMessage = (text: string, type: 'success' | 'error') => {
    statusMessage.value = { text, type };
    setTimeout(() => {
        statusMessage.value = { text: '', type: '' };
    }, 5000);
};

// Check for success/error messages in URL parameters
const checkUrlParams = () => {
    const urlParams = new URLSearchParams(window.location.search);
    const success = urlParams.get('success');
    const error = urlParams.get('error');

    if (success === 'j4r_check_completed') {
        showStatusMessage(t('j4r.pages.index.alerts.success.rewards_claimed'), 'success');

        // Clean up URL
        window.history.replaceState({}, document.title, window.location.pathname);

        // Refresh data
        setTimeout(() => {
            loadJ4RData();
        }, 1000);
    } else if (error) {
        let errorMessage = t('j4r.pages.index.alerts.error.generic');

        switch (error) {
            case 'discord_not_enabled':
                errorMessage = t('j4r.pages.index.alerts.error.discord_not_enabled');
                break;
            case 'discord_not_linked':
                errorMessage = t('j4r.pages.index.alerts.error.discord_not_linked');
                break;
            case 'discord_token_failed':
                errorMessage = t('j4r.pages.index.alerts.error.check_failed');
                break;
            case 'discord_user_failed':
                errorMessage = t('j4r.pages.index.alerts.error.check_failed');
                break;
            case 'discord_user_mismatch':
                errorMessage = t('j4r.pages.index.alerts.error.check_failed');
                break;
        }

        showStatusMessage(errorMessage, 'error');

        // Clean up URL
        window.history.replaceState({}, document.title, window.location.pathname);
    }
};

// Initialize component
onMounted(() => {
    loadJ4RData();
    checkUrlParams();
});
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
