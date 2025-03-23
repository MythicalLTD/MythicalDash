<template>
    <LayoutDashboard>
        <div class="p-6">
            <h1 class="text-2xl font-bold text-white mb-6">Code Redemption</h1>

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
                                                <label for="code" class="block text-sm font-medium text-gray-400 mb-2"
                                                    >Enter Redemption Code</label
                                                >
                                                <input
                                                    type="text"
                                                    id="code"
                                                    v-model="codeInput"
                                                    class="w-full px-4 py-3 rounded-lg border border-gray-700 bg-gray-800/50 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                                    placeholder="Enter your code here"
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
                                                        Redeeming...
                                                    </span>
                                                    <span v-else>Redeem Code</span>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <!-- Redemption History -->
                                <div class="mb-6">
                                    <div class="flex items-center mb-3">
                                        <TicketIcon class="w-5 h-5 text-indigo-400 mr-2" />
                                        <h3 class="text-lg font-medium text-white">Redemption History</h3>
                                    </div>

                                    <div class="bg-gray-800/30 rounded-xl p-5 mb-4">
                                        <div
                                            v-if="redemptionHistory.length === 0"
                                            class="text-center text-gray-400 py-6"
                                        >
                                            <ArchiveIcon class="w-12 h-12 mx-auto mb-3 text-gray-600" />
                                            <p>No redemption history available</p>
                                        </div>

                                        <div v-else>
                                            <div class="mb-3">
                                                <label for="history" class="text-sm text-gray-400"
                                                    >Previously Redeemed Codes</label
                                                >
                                                <div class="relative mt-1">
                                                    <button
                                                        @click="toggleHistoryDropdown"
                                                        class="w-full flex items-center justify-between gap-3 px-4 py-2 rounded-lg bg-gray-700/50 border border-gray-600/50 hover:bg-gray-700 text-white transition-colors"
                                                    >
                                                        <span>{{ redemptionHistory.length }} codes redeemed</span>
                                                        <ChevronDownIcon
                                                            class="w-4 h-4 transition-transform duration-200"
                                                            :class="{ 'rotate-180': isHistoryOpen }"
                                                        />
                                                    </button>

                                                    <div
                                                        v-if="isHistoryOpen"
                                                        class="absolute z-10 mt-1 w-full bg-gray-800 border border-gray-700 rounded-lg shadow-lg max-h-60 overflow-y-auto"
                                                    >
                                                        <div class="py-1">
                                                            <div
                                                                v-for="(item, index) in redemptionHistory"
                                                                :key="index"
                                                                class="px-4 py-3 hover:bg-gray-700 cursor-pointer"
                                                            >
                                                                <div class="flex justify-between items-center">
                                                                    <div>
                                                                        <div class="font-medium text-white">
                                                                            {{ item.code }}
                                                                        </div>
                                                                        <div class="text-sm text-gray-400">
                                                                            {{ item.reward }}
                                                                        </div>
                                                                    </div>
                                                                    <div class="text-xs text-gray-500">
                                                                        {{ formatDate(item.date) }}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
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
                                                <div class="text-sm text-gray-400">Current Balance</div>
                                                <div class="text-2xl font-bold text-yellow-500">{{ totalCoins }}</div>
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
                                            'p-4 rounded-lg mb-6',
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

                <!-- Sidebar with Common Codes and Ads -->
                <div class="space-y-6">
                    <!-- Common Codes Card -->
                    <CardComponent cardTitle="Code Tips" cardDescription="Where to find codes">
                        <div class="space-y-4 p-2">
                            <div class="bg-gray-800/30 p-3 rounded-lg">
                                <div class="flex items-center mb-2">
                                    <TwitterIcon class="w-5 h-5 text-blue-400 mr-2" />
                                    <div class="text-sm font-medium text-white">Twitter/X</div>
                                </div>
                                <p class="text-xs text-gray-400">
                                    Follow us on Twitter for regular code drops and announcements.
                                </p>
                            </div>

                            <div class="bg-gray-800/30 p-3 rounded-lg">
                                <div class="flex items-center mb-2">
                                    <DiscordIcon class="w-5 h-5 text-indigo-400 mr-2" />
                                    <div class="text-sm font-medium text-white">Discord</div>
                                </div>
                                <p class="text-xs text-gray-400">
                                    Join our Discord community for exclusive code giveaways.
                                </p>
                            </div>

                            <div class="bg-gray-800/30 p-3 rounded-lg">
                                <div class="flex items-center mb-2">
                                    <CalendarIcon class="w-5 h-5 text-purple-400 mr-2" />
                                    <div class="text-sm font-medium text-white">Events</div>
                                </div>
                                <p class="text-xs text-gray-400">
                                    Special codes are released during seasonal events and promotions.
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
import { ref, onMounted } from 'vue';
import {
    Ticket as TicketIcon,
    ChevronDown as ChevronDownIcon,
    Archive as ArchiveIcon,
    CheckCircle as CheckCircleIcon,
    AlertTriangle as AlertTriangleIcon,
    Loader as SpinnerIcon,
    X as XIcon,
    Coins,
    Calendar as CalendarIcon,
} from 'lucide-vue-next';
import Session from '@/mythicaldash/Session';
import { useSettingsStore } from '@/stores/settings';
import router from '@/router';
import Swal from 'sweetalert2';

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

const Settings = useSettingsStore();
import { defineComponent, h } from 'vue';

// If code redemption is disabled, redirect to dashboard
if (Settings.getSetting('code_redemption_enabled') === 'false') {
    Swal.fire({
        title: 'Code Redemption',
        text: 'Code redemption is not enabled on this host!',
        icon: 'error',
        confirmButtonText: 'OK',
    });
    router.push('/dashboard');
}

// State
const codeInput = ref('');
const isRedeeming = ref(false);
const isHistoryOpen = ref(false);
const totalCoins = ref(Session.getInfoInt('credits'));
const statusMessage = ref({
    type: 'success',
    text: '',
});

// Redemption history
interface RedemptionRecord {
    code: string;
    reward: string;
    date: Date;
}

const redemptionHistory = ref<RedemptionRecord[]>([]);

// Format date for display
const formatDate = (date: Date) => {
    return new Date(date).toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

// Toggle history dropdown
const toggleHistoryDropdown = () => {
    isHistoryOpen.value = !isHistoryOpen.value;
};

// Handle code redemption
const redeemCode = async () => {
    if (!codeInput.value.trim()) {
        showStatusMessage('Please enter a code', 'error');
        return;
    }

    isRedeeming.value = true;

    try {
        // Call API to redeem code
        const response = await fetch('/api/user/redeem/code', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
            },
            body: JSON.stringify({
                code: codeInput.value.trim(),
            }),
        });

        const data = await response.json();

        if (response.ok && data.status === 200) {
            // Code was successfully redeemed
            showStatusMessage(data.message || 'Code redeemed successfully!', 'success');

            // Add to history
            redemptionHistory.value.unshift({
                code: codeInput.value.trim(),
                reward: data.reward || 'Unknown reward',
                date: new Date(),
            });

            // Save history to local storage
            saveRedemptionHistory();

            // Update coin balance if applicable
            if (data.coins) {
                totalCoins.value += parseInt(data.coins);
            }

            // Clear input
            codeInput.value = '';
        } else {
            // Error redeeming code
            showStatusMessage(data.message || 'Error redeeming code', 'error');
        }
    } catch (error) {
        console.error('Error redeeming code:', error);
        showStatusMessage('An error occurred while redeeming the code', 'error');
    } finally {
        isRedeeming.value = false;
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

// Save redemption history to local storage
const saveRedemptionHistory = () => {
    try {
        localStorage.setItem('redemption_history', JSON.stringify(redemptionHistory.value));
    } catch (error) {
        console.error('Error saving redemption history:', error);
    }
};

// Load redemption history from local storage
const loadRedemptionHistory = () => {
    try {
        const savedHistory = localStorage.getItem('redemption_history');
        if (savedHistory) {
            // Parse dates properly
            const parsedHistory = JSON.parse(savedHistory);
            redemptionHistory.value = parsedHistory.map((item: RedemptionRecord) => ({
                ...item,
                date: new Date(item.date),
            }));
        }
    } catch (error) {
        console.error('Error loading redemption history:', error);
    }
};

// On component mount
onMounted(() => {
    loadRedemptionHistory();
});
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
