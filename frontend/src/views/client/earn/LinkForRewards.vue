<template>
    <LayoutDashboard>
        <div class="p-6">
            <h1 class="text-2xl font-bold text-white mb-6">Link For Rewards</h1>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main L4R Content -->
                <div class="lg:col-span-2">
                    <CardComponent
                        cardTitle="Available Link Providers"
                        cardDescription="Click any provider to complete offers and earn coins"
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
                                <!-- Loading State -->
                                <div v-if="isLoading" class="py-10 flex flex-col items-center justify-center">
                                    <LoaderIcon class="w-12 h-12 text-indigo-500 animate-spin mb-3" />
                                    <p class="text-gray-400">Loading link providers...</p>
                                </div>

                                <!-- Link Provider Cards -->
                                <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Linkvertise -->
                                    <div class="bg-gray-800/50 rounded-xl overflow-hidden border border-gray-700/30">
                                        <div class="p-5">
                                            <div class="flex items-center mb-3">
                                                <img
                                                    :src="getProviderLogo('linkvertise')"
                                                    alt="Linkvertise"
                                                    class="h-10 w-10 mr-3 rounded-lg"
                                                />
                                                <div>
                                                    <h3 class="text-lg font-bold text-white">Linkvertise</h3>
                                                    <span class="text-xs text-gray-400">Fast & easy rewards</span>
                                                </div>
                                            </div>

                                            <p class="text-gray-300 text-sm mb-4">
                                                Complete simple tasks through Linkvertise like surveys, watching videos,
                                                or visiting websites.
                                            </p>

                                            <div class="flex items-center justify-between">
                                                <div
                                                    class="flex items-center bg-indigo-900/40 text-indigo-400 px-3 py-1 rounded-full text-xs font-medium"
                                                >
                                                    <Coins class="h-3 w-3 mr-1" />
                                                    Up to 90 coins
                                                </div>

                                                <button
                                                    @click="goToLinkProvider('linkvertise')"
                                                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors bg-indigo-600 hover:bg-indigo-700 text-white"
                                                >
                                                    Go to Linkvertise
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- ShareUs -->
                                    <div class="bg-gray-800/50 rounded-xl overflow-hidden border border-gray-700/30">
                                        <div class="p-5">
                                            <div class="flex items-center mb-3">
                                                <img
                                                    :src="getProviderLogo('shareus')"
                                                    alt="ShareUs"
                                                    class="h-10 w-10 mr-3 rounded-lg"
                                                />
                                                <div>
                                                    <h3 class="text-lg font-bold text-white">ShareUs</h3>
                                                    <span class="text-xs text-gray-400">Popular link shortener</span>
                                                </div>
                                            </div>

                                            <p class="text-gray-300 text-sm mb-4">
                                                Visit partner websites through ShareUs and earn coins for your time
                                                exploring content.
                                            </p>

                                            <div class="flex items-center justify-between">
                                                <div
                                                    class="flex items-center bg-indigo-900/40 text-indigo-400 px-3 py-1 rounded-full text-xs font-medium"
                                                >
                                                    <Coins class="h-3 w-3 mr-1" />
                                                    Up to 50 coins
                                                </div>

                                                <button
                                                    @click="goToLinkProvider('shareus')"
                                                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors bg-indigo-600 hover:bg-indigo-700 text-white"
                                                >
                                                    Go to ShareUs
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- LinkPays -->
                                    <div class="bg-gray-800/50 rounded-xl overflow-hidden border border-gray-700/30">
                                        <div class="p-5">
                                            <div class="flex items-center mb-3">
                                                <img
                                                    :src="getProviderLogo('linkpays')"
                                                    alt="LinkPays"
                                                    class="h-10 w-10 mr-3 rounded-lg"
                                                />
                                                <div>
                                                    <h3 class="text-lg font-bold text-white">LinkPays</h3>
                                                    <span class="text-xs text-gray-400">Higher rewards</span>
                                                </div>
                                            </div>

                                            <p class="text-gray-300 text-sm mb-4">
                                                Download apps, complete offers, and earn higher rewards through LinkPays
                                                network.
                                            </p>

                                            <div class="flex items-center justify-between">
                                                <div
                                                    class="flex items-center bg-indigo-900/40 text-indigo-400 px-3 py-1 rounded-full text-xs font-medium"
                                                >
                                                    <Coins class="h-3 w-3 mr-1" />
                                                    Up to 120 coins
                                                </div>

                                                <button
                                                    @click="goToLinkProvider('linkpays')"
                                                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors bg-indigo-600 hover:bg-indigo-700 text-white"
                                                >
                                                    Go to LinkPays
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- GyaniLinks -->
                                    <div class="bg-gray-800/50 rounded-xl overflow-hidden border border-gray-700/30">
                                        <div class="p-5">
                                            <div class="flex items-center mb-3">
                                                <img
                                                    :src="getProviderLogo('gyanilinks')"
                                                    alt="GyaniLinks"
                                                    class="h-10 w-10 mr-3 rounded-lg"
                                                />
                                                <div>
                                                    <h3 class="text-lg font-bold text-white">GyaniLinks</h3>
                                                    <span class="text-xs text-gray-400">Quick & easy tasks</span>
                                                </div>
                                            </div>

                                            <p class="text-gray-300 text-sm mb-4">
                                                Watch videos and complete simple tasks to earn coins through GyaniLinks
                                                platform.
                                            </p>

                                            <div class="flex items-center justify-between">
                                                <div
                                                    class="flex items-center bg-indigo-900/40 text-indigo-400 px-3 py-1 rounded-full text-xs font-medium"
                                                >
                                                    <Coins class="h-3 w-3 mr-1" />
                                                    Up to 100 coins
                                                </div>

                                                <button
                                                    @click="goToLinkProvider('gyanilinks')"
                                                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors bg-indigo-600 hover:bg-indigo-700 text-white"
                                                >
                                                    Go to GyaniLinks
                                                </button>
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
                                                <CheckCircleIcon
                                                    v-if="statusMessage.type === 'success'"
                                                    class="h-5 w-5"
                                                />
                                                <AlertTriangleIcon v-else class="h-5 w-5" />
                                            </div>
                                            <div class="ml-3">
                                                <p class="font-medium">{{ statusMessage.text }}</p>
                                            </div>
                                            <button @click="statusMessage.text = ''" class="ml-auto">
                                                <XIcon class="h-4 w-4" />
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
                    <CardComponent cardTitle="Your Stats" cardDescription="Link For Rewards statistics">
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
                                        <div class="text-sm text-gray-400">Current Balance</div>
                                        <div class="text-2xl font-bold text-yellow-500">{{ totalCoins }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Stats Grid -->
                            <div class="grid grid-cols-2 gap-3">
                                <div class="bg-gray-800/30 rounded-lg p-4">
                                    <div class="text-xs text-gray-400 mb-1">Offers Completed</div>
                                    <div class="text-lg font-semibold text-white">{{ stats.offersCompleted }}</div>
                                </div>
                                <div class="bg-gray-800/30 rounded-lg p-4">
                                    <div class="text-xs text-gray-400 mb-1">Total Earned</div>
                                    <div class="text-lg font-semibold text-yellow-500">
                                        {{ stats.totalEarned }} coins
                                    </div>
                                </div>
                                <div class="bg-gray-800/30 rounded-lg p-4">
                                    <div class="text-xs text-gray-400 mb-1">Available Now</div>
                                    <div class="text-lg font-semibold text-indigo-400">4</div>
                                </div>
                                <div class="bg-gray-800/30 rounded-lg p-4">
                                    <div class="text-xs text-gray-400 mb-1">Completed Today</div>
                                    <div class="text-lg font-semibold text-white">{{ stats.completedToday }}</div>
                                </div>
                            </div>
                        </div>
                    </CardComponent>

                    <!-- How It Works Card -->
                    <CardComponent cardTitle="How It Works" cardDescription="Complete link offers for rewards">
                        <div class="p-4 space-y-4">
                            <div class="bg-gray-800/30 p-4 rounded-lg">
                                <div class="flex items-start mb-2">
                                    <div
                                        class="bg-indigo-900/50 text-indigo-400 w-5 h-5 rounded-full flex items-center justify-center mr-2 flex-shrink-0"
                                    >
                                        <span class="text-xs">1</span>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-white mb-1">Choose a Provider</h4>
                                        <p class="text-xs text-gray-400">
                                            Click on any of the link providers to get started
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-800/30 p-4 rounded-lg">
                                <div class="flex items-start mb-2">
                                    <div
                                        class="bg-indigo-900/50 text-indigo-400 w-5 h-5 rounded-full flex items-center justify-center mr-2 flex-shrink-0"
                                    >
                                        <span class="text-xs">2</span>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-white mb-1">Complete Link Tasks</h4>
                                        <p class="text-xs text-gray-400">
                                            Follow the instructions and navigate through the link shortener
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-800/30 p-4 rounded-lg">
                                <div class="flex items-start mb-2">
                                    <div
                                        class="bg-indigo-900/50 text-indigo-400 w-5 h-5 rounded-full flex items-center justify-center mr-2 flex-shrink-0"
                                    >
                                        <span class="text-xs">3</span>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-white mb-1">Verification</h4>
                                        <p class="text-xs text-gray-400">
                                            Our system will automatically verify your completion
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-800/30 p-4 rounded-lg">
                                <div class="flex items-start mb-2">
                                    <div
                                        class="bg-indigo-900/50 text-indigo-400 w-5 h-5 rounded-full flex items-center justify-center mr-2 flex-shrink-0"
                                    >
                                        <span class="text-xs">4</span>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-white mb-1">Get Rewarded</h4>
                                        <p class="text-xs text-gray-400">
                                            Coins will be added to your account automatically
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
import { ref, onMounted } from 'vue';
import {
    Loader as LoaderIcon,
    CheckCircle as CheckCircleIcon,
    AlertTriangle as AlertTriangleIcon,
    X as XIcon,
    Coins,
} from 'lucide-vue-next';
import Session from '@/mythicaldash/Session';
import { useSettingsStore } from '@/stores/settings';
import router from '@/router';
import Swal from 'sweetalert2';
import { MythicalDOM } from '@/mythicaldash/MythicalDOM';
MythicalDOM.setPageTitle('Link For Rewards');

const Settings = useSettingsStore();

// If L4R is disabled, redirect to dashboard
if (Settings.getSetting('l4r_enabled') === 'false') {
    Swal.fire({
        title: 'Link For Rewards',
        text: 'Link For Rewards is not enabled on this host!',
        icon: 'error',
        confirmButtonText: 'OK',
    });
    router.push('/dashboard');
}

// Provider URLs
interface ProviderConfig {
    name: string;
    url: string;
    maxReward: number;
}

// State
const isLoading = ref(true);
const totalCoins = ref(Session.getInfoInt('credits'));
const statusMessage = ref({
    type: 'success',
    text: '',
});

// Provider configurations
const providers: Record<string, ProviderConfig> = {
    linkvertise: {
        name: 'Linkvertise',
        url: 'https://linkvertise.com/user/dashboard?referral=mythicaldash',
        maxReward: 90,
    },
    shareus: {
        name: 'ShareUs',
        url: 'https://shareus.io/?referral=mythicaldash',
        maxReward: 50,
    },
    linkpays: {
        name: 'LinkPays',
        url: 'https://linkpays.in/?ref=mythicaldash',
        maxReward: 120,
    },
    gyanilinks: {
        name: 'GyaniLinks',
        url: 'https://gyanilinks.com/?ref=mythicaldash',
        maxReward: 100,
    },
};

// User statistics
const stats = ref({
    offersCompleted: 0,
    totalEarned: 0,
    completedToday: 0,
});

// Get logo for provider
const getProviderLogo = (provider: string): string => {
    const logoMap: Record<string, string> = {
        linkvertise: 'https://i.imgur.com/EGgUjQu.png',
        shareus: 'https://i.imgur.com/iQ5ID0M.png',
        linkpays: 'https://i.imgur.com/c8s6sAl.png',
        gyanilinks: 'https://i.imgur.com/JN3VJt9.png',
    };

    return logoMap[provider.toLowerCase()] || 'https://via.placeholder.com/48';
};

// Go to link provider
const goToLinkProvider = async (providerKey: string) => {
    try {
        const provider = providers[providerKey];
        if (!provider) {
            showStatusMessage('Invalid provider selected', 'error');
            return;
        }

        // Log the provider visit
        await fetch('/api/user/earn/l4r/start', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
            },
            body: JSON.stringify({
                provider: providerKey,
            }),
        });

        // Open provider URL in new tab
        window.open(provider.url, '_blank');

        // Show success message
        showStatusMessage(`You've been redirected to ${provider.name}. Complete offers to earn rewards!`, 'success');
    } catch (error) {
        console.error('Error opening link provider:', error);
        showStatusMessage('An error occurred while processing your request', 'error');
    }
};

// Show status message
const showStatusMessage = (text: string, type: 'success' | 'error') => {
    statusMessage.value = { text, type };

    // Clear message after 5 seconds
    setTimeout(() => {
        statusMessage.value.text = '';
    }, 5000);
};

// Load stats from API
const loadStats = async () => {
    isLoading.value = true;

    try {
        // In a real implementation, call the API
        // const response = await fetch('/api/user/earn/l4r/stats');
        // const data = await response.json();

        // For demo purposes, we'll use mock data
        setTimeout(() => {
            // Mock stats
            stats.value = {
                offersCompleted: 12,
                totalEarned: 450,
                completedToday: 3,
            };

            isLoading.value = false;
        }, 1000);
    } catch (error) {
        console.error('Error loading stats:', error);
        showStatusMessage('An error occurred while loading your statistics', 'error');
        isLoading.value = false;
    }
};

onMounted(() => {
    loadStats();
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
