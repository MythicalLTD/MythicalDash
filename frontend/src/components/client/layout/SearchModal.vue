<template>
    <div
        v-if="isSearchOpen"
        class="fixed inset-0 bg-[#0a0a0f]/95 backdrop-blur-md z-50 flex items-start justify-center"
        @click="$emit('close')"
        @keydown.ctrl.k.prevent="$emit('close')"
        @keydown.ctrl.d.prevent="toggleDashboard"
    >
        <div class="w-full max-w-3xl mx-auto pt-24 px-4 md:px-6" @click.stop>
            <!-- Search Header -->
            <div class="mb-2 flex items-center justify-between">
                <h2 class="text-lg font-medium text-gray-300">{{ t('Components.QuickSearch.title') }}</h2>
                <div class="flex items-center gap-2">
                    <kbd
                        class="px-2 py-1 text-xs font-medium text-gray-400 bg-[#1a1a2e] rounded-md border border-[#2a2a3f]/50"
                    >
                        {{ t('Components.QuickSearch.esc_key') }}
                    </kbd>
                    <span class="text-gray-500">{{ t('Components.QuickSearch.to_close') }}</span>
                </div>
            </div>

            <!-- Search Input -->
            <div class="relative">
                <div class="absolute left-4 top-3.5 text-gray-400">
                    <SearchIcon class="h-5 w-5" />
                </div>
                <input
                    type="text"
                    :placeholder="$t('components.search.placeholder')"
                    v-model="searchQuery"
                    class="w-full bg-[#1a1a2e]/70 border border-[#2a2a3f]/50 rounded-xl pl-11 pr-20 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500/50 text-gray-100 placeholder-gray-500 transition-all duration-200"
                    @keydown.esc="$emit('close')"
                    @input="performSearch"
                    @keydown.enter="handleEnter"
                    @keydown.arrow-up.prevent="navigateResults(-1)"
                    @keydown.arrow-down.prevent="navigateResults(1)"
                    ref="searchInput"
                />
                <div class="absolute right-4 top-3 flex space-x-2">
                    <kbd
                        class="px-2 py-1 text-xs font-medium text-gray-400 bg-[#0a0a0f]/70 rounded-md border border-[#2a2a3f]/50"
                    >
                        ↑↓
                    </kbd>
                    <kbd
                        class="px-2 py-1 text-xs font-medium text-gray-400 bg-[#0a0a0f]/70 rounded-md border border-[#2a2a3f]/50"
                    >
                        {{ t('Components.QuickSearch.enter_key') }}
                    </kbd>
                </div>
            </div>

            <!-- Search Results -->
            <div
                v-if="searchResults.length > 0"
                class="mt-4 bg-[#1a1a2e]/50 rounded-xl border border-[#2a2a3f]/30 overflow-hidden shadow-xl"
            >
                <div
                    v-for="(result, index) in searchResults"
                    :key="result.id || result.href"
                    :class="[
                        'p-4 cursor-pointer transition-all duration-150',
                        selectedIndex === index
                            ? 'bg-indigo-500/20 border-l-2 border-indigo-500'
                            : 'hover:bg-[#2a2a3f]/30 border-l-2 border-transparent',
                    ]"
                    @click="navigateToResult(result)"
                    @mouseover="selectedIndex = index"
                >
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-lg bg-[#2a2a3f]/30 flex items-center justify-center">
                            <component :is="result.icon" class="w-5 h-5 text-indigo-400" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-medium text-gray-100 flex items-center">
                                <span v-if="result.id" class="text-indigo-400 mr-2 font-mono">#{{ result.id }}</span>
                                {{ result.title }}
                            </div>
                            <div class="text-sm text-gray-400 truncate">{{ result.description }}</div>
                        </div>
                        <div v-if="result.shortcut" class="text-sm text-gray-500 flex items-center">
                            <ArrowRightIcon class="w-4 h-4 mr-1" />
                            {{ result.shortcut }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- No Results -->
            <div
                v-else-if="searchQuery"
                class="mt-4 p-6 bg-[#1a1a2e]/50 rounded-xl border border-[#2a2a3f]/30 text-center"
            >
                <div class="flex flex-col items-center">
                    <SearchXIcon class="w-12 h-12 text-gray-500 mb-3" />
                    <p class="text-gray-400 mb-1">{{ $t('components.search.no_results') }}</p>
                    <p class="text-sm text-gray-500">{{ t('Components.QuickSearch.notfound') }}</p>
                </div>
            </div>

            <!-- Quick Links -->
            <div v-else class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3">
                <div
                    class="p-4 bg-[#1a1a2e]/50 rounded-xl border border-[#2a2a3f]/30 hover:bg-[#1a1a2e]/70 transition-colors duration-200"
                >
                    <h3 class="font-medium text-gray-300 mb-2">{{ t('Components.QuickSearch.quickNav') }}</h3>
                    <div class="space-y-2">
                        <div
                            v-for="item in quickLinks"
                            :key="item.href"
                            class="flex items-center gap-3 p-2 rounded-lg hover:bg-[#2a2a3f]/30 cursor-pointer transition-colors duration-150"
                            @click="navigateToResult(item)"
                        >
                            <component :is="item.icon" class="w-4 h-4 text-indigo-400" />
                            <span class="text-sm text-gray-300">{{ item.title }}</span>
                        </div>
                    </div>
                </div>

                <div class="p-4 bg-[#1a1a2e]/50 rounded-xl border border-[#2a2a3f]/30">
                    <h3 class="font-medium text-gray-300 mb-2">{{ t('Components.QuickSearch.keyboardShortcuts') }}</h3>
                    <div class="space-y-2">
                        <div class="flex items-center justify-between p-2">
                            <span class="text-sm text-gray-400">{{ t('Components.QuickSearch.search') }}</span>
                            <div class="flex items-center gap-1">
                                <kbd
                                    class="px-2 py-1 text-xs font-medium text-gray-400 bg-[#0a0a0f]/70 rounded-md border border-[#2a2a3f]/50"
                                    >{{ t('Components.QuickSearch.ctrl_key') }}</kbd
                                >
                                <span class="text-gray-500">+</span>
                                <kbd
                                    class="px-2 py-1 text-xs font-medium text-gray-400 bg-[#0a0a0f]/70 rounded-md border border-[#2a2a3f]/50"
                                    >{{ t('Components.QuickSearch.k_key') }}</kbd
                                >
                            </div>
                        </div>
                        <div class="flex items-center justify-between p-2">
                            <span class="text-sm text-gray-400">{{ t('Components.QuickSearch.goToDashboard') }}</span>
                            <div class="flex items-center gap-1">
                                <kbd
                                    class="px-2 py-1 text-xs font-medium text-gray-400 bg-[#0a0a0f]/70 rounded-md border border-[#2a2a3f]/50"
                                    >{{ t('Components.QuickSearch.ctrl_key') }}</kbd
                                >
                                <span class="text-gray-500">+</span>
                                <kbd
                                    class="px-2 py-1 text-xs font-medium text-gray-400 bg-[#0a0a0f]/70 rounded-md border border-[#2a2a3f]/50"
                                    >{{ t('Components.QuickSearch.d_key') }}</kbd
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import {
    LayoutDashboard as LayoutDashboardIcon,
    Search as SearchIcon,
    Ticket as TicketIcon,
    Server as ServerIcon,
    ArrowRight as ArrowRightIcon,
    SearchX as SearchXIcon,
} from 'lucide-vue-next';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
    isSearchOpen: boolean;
}>();

const emit = defineEmits(['close', 'navigate', 'shortcut']);
const { t } = useI18n();
const router = useRouter();
const searchQuery = ref('');
const selectedIndex = ref(0);

interface SearchResult {
    id?: number;
    title: string;
    description: string;
    href: string;
    icon: unknown;
    shortcut?: string;
}

const searchResults = ref<SearchResult[]>([]);
const searchInput = ref<HTMLInputElement | null>(null);
import { useSettingsStore } from '@/stores/settings';
import {
    AlertTriangle as AlertTriangleIcon,
    Bell as BellIcon,
    ShoppingCart as ShoppingCartIcon,
    Clock as ClockIcon,
    Gift as GiftIcon,
    Users as UsersIcon,
    UserPlus as UserPlusIcon,
    Link as LinkIcon,
} from 'lucide-vue-next';

const Settings = useSettingsStore();

// Check if features are enabled
const isStoreEnabled = computed(() => Settings.getSetting('store_enabled') === 'true');
const isAfkEnabled = computed(() => Settings.getSetting('afk_enabled') === 'true');
const isCodeRedemptionEnabled = computed(() => Settings.getSetting('code_redemption_enabled') === 'true');
const isJ4REnabled = computed(() => Settings.getSetting('j4r_enabled') === 'true');
const isReferralsEnabled = computed(() => Settings.getSetting('referrals_enabled') === 'true');
const isL4REnabled = computed(() => Settings.getSetting('l4r_enabled') === 'true');

const searchableItems: SearchResult[] = [
    {
        id: 1,
        title: t('dashboard.title'),
        description: t('components.search.items.dashboard'),
        href: '/',
        icon: LayoutDashboardIcon,
    },
    {
        id: 2,
        title: t('components.sidebar.tickets'),
        description: t('components.search.items.tickets'),
        href: '/ticket',
        icon: TicketIcon,
    },
    {
        id: 3,
        title: t('Components.ServerList.title'),
        description: t('Components.ServerList.description'),
        href: '/dashboard',
        icon: ServerIcon,
    },
    {
        id: 4,
        title: t('Components.ManageAccount.title'),
        description: t('Components.ManageAccount.description'),
        href: '/account',
        icon: LayoutDashboardIcon,
    },
    {
        id: 5,
        title: t('Components.Announcements.Title'),
        description: t('Components.QuickLinks.announcements.description'),
        href: '/announcements',
        icon: LayoutDashboardIcon,
    },
    {
        id: 6,
        title: t('Components.Leaderboard.title'),
        description: t('Components.Leaderboard.description'),
        href: '/leaderboard',
        icon: LayoutDashboardIcon,
    },
    {
        id: 7,
        title: t('components.sidebar.create'),
        description: t('components.sidebar.create'),
        href: '/server/create',
        icon: ServerIcon,
    },
    ...(isStoreEnabled.value
        ? [
              {
                  id: 8,
                  title: t('components.sidebar.store'),
                  description: t('components.sidebar.store'),
                  href: '/store',
                  icon: ShoppingCartIcon,
              },
          ]
        : []),
    ...(isAfkEnabled.value
        ? [
              {
                  id: 9,
                  title: t('components.sidebar.afk'),
                  description: t('components.sidebar.afk'),
                  href: '/earn/afk',
                  icon: ClockIcon,
              },
          ]
        : []),
    ...(isCodeRedemptionEnabled.value
        ? [
              {
                  id: 10,
                  title: t('components.sidebar.code_redemption'),
                  description: t('components.sidebar.code_redemption'),
                  href: '/earn/redeem',
                  icon: GiftIcon,
              },
          ]
        : []),
    ...(isJ4REnabled.value
        ? [
              {
                  id: 11,
                  title: t('components.sidebar.j4r'),
                  description: t('components.sidebar.j4r'),
                  href: '/earn/j4r',
                  icon: UsersIcon,
              },
          ]
        : []),
    ...(isReferralsEnabled.value
        ? [
              {
                  id: 12,
                  title: t('components.sidebar.referrals'),
                  description: t('components.sidebar.referrals'),
                  href: '/earn/referrals',
                  icon: UserPlusIcon,
              },
          ]
        : []),
    ...(isL4REnabled.value
        ? [
              {
                  id: 13,
                  title: t('components.sidebar.l4r'),
                  description: t('components.sidebar.l4r'),
                  href: '/earn/links',
                  icon: LinkIcon,
              },
          ]
        : []),
    {
        id: 14,
        title: t('components.sidebar.tickets'),
        description: t('components.sidebar.tickets'),
        href: '/ticket',
        icon: TicketIcon,
    },
    {
        id: 15,
        title: t('components.sidebar.open_ticket'),
        description: t('components.sidebar.open_ticket'),
        href: '/ticket/create',
        icon: AlertTriangleIcon,
    },
    {
        id: 16,
        title: t('components.sidebar.all_tickets'),
        description: t('components.sidebar.all_tickets'),
        href: '/ticket',
        icon: TicketIcon,
    },
    {
        id: 17,
        title: t('components.sidebar.announcements'),
        description: t('components.sidebar.announcements'),
        href: '/announcements',
        icon: BellIcon,
    },
];

const quickLinks = computed(() => searchableItems.slice(0, 4));

const performSearch = () => {
    selectedIndex.value = 0;
    if (!searchQuery.value.trim()) {
        searchResults.value = [];
        return;
    }

    const query = searchQuery.value.toLowerCase();
    const isNumeric = /^\d+$/.test(query);

    if (isNumeric) {
        // First try exact ID match
        searchResults.value = searchableItems.filter((item) => item.id?.toString() === query);

        // If no exact matches, try partial ID matches
        if (searchResults.value.length === 0) {
            searchResults.value = searchableItems.filter((item) => item.id?.toString().includes(query));
        }
    } else {
        searchResults.value = searchableItems.filter(
            (item) => item.title.toLowerCase().includes(query) || item.description.toLowerCase().includes(query),
        );
    }
};

const navigateToResult = (result: SearchResult) => {
    emit('close');
    router.push(result.href);
};

const handleEnter = () => {
    if (searchResults.value.length > 0) {
        navigateToResult(searchResults.value[selectedIndex.value]);
    }
};

const navigateResults = (direction: number) => {
    const newIndex = selectedIndex.value + direction;
    if (newIndex >= 0 && newIndex < searchResults.value.length) {
        selectedIndex.value = newIndex;
    }
};

const toggleDashboard = () => {
    emit('close');
    router.push('/');
};

watch(
    () => props.isSearchOpen,
    (newValue) => {
        if (newValue) {
            searchQuery.value = '';
            searchResults.value = [];
            selectedIndex.value = 0;
            setTimeout(() => {
                searchInput.value?.focus();
            }, 100);
        }
    },
);
</script>

<style scoped>
/* Keyboard shortcut styling */
kbd {
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;
    font-size: 0.75rem;
    line-height: 1;
}

/* Smooth transitions */
.transition-all {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

/* Focus styles */
input:focus {
    box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2);
}
</style>
