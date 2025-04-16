<template>
    <LayoutDashboard>
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-100 mb-2">{{ t('leaderboard.title') }}</h1>
            <p class="text-gray-400">{{ t('leaderboard.description') }}</p>
        </div>

        <!-- Leaderboard Card -->
        <div class="bg-[#0a0a15]/50 border border-[#1a1a2f]/30 rounded-xl shadow-lg overflow-hidden">
            <!-- Tabs Navigation -->
            <div class="border-b border-[#1a1a2f]/30 bg-[#050508]/70">
                <div class="flex overflow-x-auto scrollbar-hide">
                    <button
                        v-for="category in leaderboardCategories"
                        :key="category.type"
                        class="flex items-center gap-2 px-6 py-4 text-sm font-medium whitespace-nowrap transition-all duration-200"
                        :class="[
                            activeCategory === category.type
                                ? 'text-indigo-400 border-b-2 border-indigo-500 bg-[#0a0a15]/50'
                                : 'text-gray-400 hover:text-gray-200 hover:bg-[#0a0a15]/30',
                        ]"
                        @click="setActiveCategory(category.type)"
                    >
                        <component :is="category.icon" class="w-4 h-4" />
                        {{ category.name }}
                    </button>
                </div>
            </div>

            <!-- Leaderboard Content -->
            <div class="p-6">
                <div v-if="loading" class="flex justify-center p-10">
                    <LoadingAnimation />
                </div>
                <div v-else-if="error" class="text-center p-8">
                    <div
                        class="inline-flex items-center justify-center p-3 bg-red-500/10 text-red-400 rounded-full mb-4"
                    >
                        <AlertCircle class="h-6 w-6" />
                    </div>
                    <h3 class="text-lg font-medium text-gray-200 mb-2">{{ t('leaderboard.error.title') }}</h3>
                    <p class="text-gray-400 mb-4">{{ error }}</p>
                    <Button @click="fetchLeaderboard" variant="secondary">
                        {{ t('leaderboard.error.retry') }}
                    </Button>
                </div>
                <div v-else class="tab-content">
                    <Transition name="fade" mode="out-in">
                        <div :key="activeCategory" class="space-y-6">
                            <!-- Category Title and Description -->
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-medium text-gray-200">
                                    {{ getActiveCategory.name }} {{ t('leaderboard.leaderboard') }}
                                </h3>
                                <div class="text-sm text-gray-400">{{ getCurrentDate }}</div>
                            </div>

                            <!-- Podium for Top 3 (only shown when there are at least 3 entries) -->
                            <div v-if="leaderboardData.length >= 3" class="grid grid-cols-1 md:grid-cols-3 gap-4 my-6">
                                <!-- Second Place -->
                                <div class="flex flex-col items-center">
                                    <div class="relative w-20 h-20 mb-4">
                                        <div class="absolute inset-0 bg-gray-700/30 rounded-full"></div>
                                        <img
                                            :src="leaderboardData[1].avatar"
                                            :alt="leaderboardData[1].username"
                                            class="w-20 h-20 rounded-full border-4 border-gray-400 object-cover relative z-10"
                                        />
                                        <div
                                            class="absolute -bottom-2 left-1/2 transform -translate-x-1/2 bg-gray-700/80 text-white px-3 py-1 rounded-full text-sm font-bold z-20"
                                        >
                                            2
                                        </div>
                                    </div>
                                    <h3 class="text-base font-semibold text-gray-300 mb-1">
                                        {{ leaderboardData[1].username }}
                                    </h3>
                                    <span class="text-gray-400 text-sm mb-2">{{
                                        getRoleName(leaderboardData[1].role)
                                    }}</span>
                                    <span class="bg-gray-700/30 text-gray-300 px-3 py-1 rounded-md text-sm font-medium">
                                        {{ formatValue(getCategoryValue(leaderboardData[1]), activeCategory) }}
                                    </span>
                                </div>

                                <!-- First Place (Larger) -->
                                <div class="flex flex-col items-center -mt-4">
                                    <div class="relative w-24 h-24 mb-4">
                                        <div class="absolute inset-0 bg-amber-900/30 rounded-full"></div>
                                        <img
                                            :src="leaderboardData[0].avatar"
                                            :alt="leaderboardData[0].username"
                                            class="w-24 h-24 rounded-full border-4 border-amber-500 object-cover relative z-10"
                                        />
                                        <div
                                            class="absolute -bottom-2 left-1/2 transform -translate-x-1/2 bg-amber-500/80 text-white px-3 py-1 rounded-full text-sm font-bold z-20"
                                        >
                                            1
                                        </div>
                                        <svg
                                            class="absolute -top-5 left-1/2 transform -translate-x-1/2 h-8 w-8 text-amber-400"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118l-2.8-2.034c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                                                clip-rule="evenodd"
                                            ></path>
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-bold text-white mb-1">{{ leaderboardData[0].username }}</h3>
                                    <span class="text-gray-400 text-sm mb-2">{{
                                        getRoleName(leaderboardData[0].role)
                                    }}</span>
                                    <span
                                        class="bg-amber-900/30 text-amber-400 px-4 py-1.5 rounded-md text-base font-medium"
                                    >
                                        {{ formatValue(getCategoryValue(leaderboardData[0]), activeCategory) }}
                                    </span>
                                </div>

                                <!-- Third Place -->
                                <div class="flex flex-col items-center">
                                    <div class="relative w-20 h-20 mb-4">
                                        <div class="absolute inset-0 bg-amber-800/20 rounded-full"></div>
                                        <img
                                            :src="leaderboardData[2].avatar"
                                            :alt="leaderboardData[2].username"
                                            class="w-20 h-20 rounded-full border-4 border-amber-700/50 object-cover relative z-10"
                                        />
                                        <div
                                            class="absolute -bottom-2 left-1/2 transform -translate-x-1/2 bg-amber-700/80 text-white px-3 py-1 rounded-full text-sm font-bold z-20"
                                        >
                                            3
                                        </div>
                                    </div>
                                    <h3 class="text-base font-semibold text-gray-300 mb-1">
                                        {{ leaderboardData[2].username }}
                                    </h3>
                                    <span class="text-gray-400 text-sm mb-2">{{
                                        getRoleName(leaderboardData[2].role)
                                    }}</span>
                                    <span
                                        class="bg-amber-800/20 text-amber-600 px-3 py-1 rounded-md text-sm font-medium"
                                    >
                                        {{ formatValue(getCategoryValue(leaderboardData[2]), activeCategory) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Rest of the leaderboard in card grid -->
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <div
                                    v-for="user in leaderboardData.slice(3)"
                                    :key="user.uuid"
                                    class="bg-[#12121f]/50 border border-[#2a2a3f]/30 rounded-lg p-4 hover:bg-[#1a1a2e]/50 transition-all duration-200"
                                >
                                    <div class="flex items-start">
                                        <div class="relative">
                                            <div
                                                class="bg-[#1a1a2e]/50 text-gray-400 w-8 h-8 rounded-full flex items-center justify-center mr-3 flex-shrink-0"
                                            >
                                                <span class="text-sm font-medium">{{ user.rank }}</span>
                                            </div>
                                        </div>
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-2">
                                                <img
                                                    :src="user.avatar"
                                                    :alt="user.username"
                                                    class="w-8 h-8 rounded-full bg-[#1a1a2e]"
                                                />
                                                <div>
                                                    <h4 class="text-sm font-medium text-white">{{ user.username }}</h4>
                                                    <span class="text-xs text-gray-400">{{
                                                        getRoleName(user.role)
                                                    }}</span>
                                                </div>
                                            </div>
                                            <div class="flex justify-between items-center mt-2">
                                                <span class="text-indigo-400 font-medium">{{
                                                    formatValue(getCategoryValue(user), activeCategory)
                                                }}</span>
                                                <a
                                                    :href="`/profile/${user.uuid}`"
                                                    class="text-gray-400 hover:text-indigo-400 transition-colors"
                                                >
                                                    <ExternalLink class="w-4 h-4" />
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- No data state -->
                            <div
                                v-if="leaderboardData.length === 0"
                                class="flex flex-col items-center justify-center py-10"
                            >
                                <div class="bg-[#1a1a2e]/50 p-4 rounded-full mb-4">
                                    <AlertCircle class="h-8 w-8 text-gray-400" />
                                </div>
                                <h3 class="text-lg font-medium text-gray-200 mb-2">
                                    {{ t('leaderboard.no_data.title') }}
                                </h3>
                                <p class="text-gray-400 text-center max-w-md">
                                    {{ t('leaderboard.no_data.message') }}
                                </p>
                            </div>
                        </div>
                    </Transition>
                </div>
            </div>
        </div>
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import LayoutDashboard from '@/components/client/LayoutDashboard.vue';
import LoadingAnimation from '@/components/client/ui/LoadingAnimation.vue';
import Button from '@/components/client/ui/Button.vue';
import { MythicalDOM } from '@/mythicaldash/MythicalDOM';
import { AlertCircle, Coins, Server, Clock, ExternalLink } from 'lucide-vue-next';
import { useSettingsStore } from '@/stores/settings';
import { useRouter } from 'vue-router';
import Swal from 'sweetalert2';

const { t } = useI18n();
const Settings = useSettingsStore();
const router = useRouter();

const isLeaderboardEnabled = computed(() => {
    return Settings.getSetting('leaderboard_enabled') === 'true';
});

if (!isLeaderboardEnabled.value) {
    router.push('/');
    Swal.fire({
        title: t('leaderboard.notEnabled.title'),
        text: t('leaderboard.notEnabled.description'),
        confirmButtonText: t('leaderboard.notEnabled.button'),
        icon: 'error',
    });
}

MythicalDOM.setPageTitle(t('leaderboard.title'));

interface Category {
    type: string;
    name: string;
    icon: object;
}

interface LeaderboardUser {
    uuid: string;
    username: string;
    avatar: string;
    role: number;
    rank: number;
    credits_count?: number;
    server_count?: number;
    minutes_afk_count?: number;
    linkvertise_count?: number;
    shareus_count?: number;
    gyanilinks_count?: number;
    linkpays_count?: number;
    referral_count?: number;
    [key: string]: unknown;
}

// Leaderboard categories with corresponding icons
const leaderboardCategories: Category[] = [
    { type: 'coins', name: t('leaderboard.categories.coins'), icon: Coins },
    { type: 'servers', name: t('leaderboard.categories.servers'), icon: Server },
    { type: 'minutes_afk', name: t('leaderboard.categories.minutes_afk'), icon: Clock },
];

const activeCategory = ref<string>('coins');
const leaderboardData = ref<LeaderboardUser[]>([]);
const loading = ref<boolean>(true);
const error = ref<string | null>(null);

// Get the active category details
const getActiveCategory = computed((): Category => {
    return leaderboardCategories.find((cat) => cat.type === activeCategory.value) || leaderboardCategories[0];
});

// Current date formatted
const getCurrentDate = computed((): string => {
    const now = new Date();
    return now.toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' });
});

// Set active category and fetch data
const setActiveCategory = (category: string): void => {
    activeCategory.value = category;
    fetchLeaderboard();
};

// Helper function to get role name
const getRoleName = (roleId: number): string => {
    // This would ideally come from a role mapping or translation
    const roles: Record<number, string> = {
        1: 'Default',
        2: 'VIP',
        3: 'Support Buddy',
        4: 'Support',
        5: 'Support LVL 3',
        6: 'Support LVL 4',
        7: 'Admin',
        8: 'Administrator',
    };

    return roles[roleId] || 'User';
};

// Get the appropriate value from user data based on active category
const getCategoryValue = (user: LeaderboardUser): number => {
    const valueMap: Record<string, string> = {
        coins: 'credits_count',
        servers: 'server_count',
        minutes_afk: 'minutes_afk_count',
        linkvertise: 'linkvertise_count',
        shareus: 'shareus_count',
        gyanilinks: 'gyanilinks_count',
        linkpays: 'linkpays_count',
        referrals: 'referral_count',
    };

    const key = valueMap[activeCategory.value];
    const value = user[key];

    // Convert to number and provide default of 0
    return typeof value === 'number' ? value : 0;
};

// Format the value based on category type
const formatValue = (value: number, category: string): string => {
    if (category === 'coins') {
        return `${value.toLocaleString()} ${t('leaderboard.currency')}`;
    }
    return value.toLocaleString();
};

// Fetch leaderboard data for the active category
const fetchLeaderboard = async (): Promise<void> => {
    loading.value = true;
    error.value = null;

    try {
        const response = await fetch(`/api/user/leaderboard/${activeCategory.value}`);
        const data = await response.json();

        if (data.success && Array.isArray(data.leaderboard)) {
            leaderboardData.value = data.leaderboard;
        } else {
            throw new Error(data.error || t('leaderboard.error.failed_fetch'));
        }
    } catch (err) {
        error.value = err instanceof Error ? err.message : t('leaderboard.error.generic');
        console.error('Failed to fetch leaderboard data:', err);
    } finally {
        loading.value = false;
    }
};

// Initial data fetch
onMounted(() => {
    fetchLeaderboard();
});
</script>

<style scoped>
/* Hide scrollbar for Chrome, Safari and Opera */
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}

/* Hide scrollbar for IE, Edge and Firefox */
.scrollbar-hide {
    -ms-overflow-style: none; /* IE and Edge */
    scrollbar-width: none; /* Firefox */
}

/* Tab content animation */
.tab-content {
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(5px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Fade transition */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
