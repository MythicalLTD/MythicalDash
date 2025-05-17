<script setup lang="ts">
import { ref, computed } from 'vue';
import {
    AlertTriangleIcon,
    BellIcon,
    ChevronDown as ChevronDownIcon,
    ServerIcon,
    TicketIcon,
    Clock as ClockIcon,
    Gift as GiftIcon,
    Users as UsersIcon,
    UserPlus as UserPlusIcon,
    Link as LinkIcon,
    Home as HomeIcon,
    ShoppingCart as ShoppingCartIcon,
    TrophyIcon as LeaderboardIcon,
    Image as ImageIcon,
    SettingsIcon,
    Images,
} from 'lucide-vue-next';
import Translation from '@/mythicaldash/Translation';
import { useSettingsStore } from '@/stores/settings';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const Settings = useSettingsStore();

// Check if AFK Rewards is enabled
const isAfkEnabled = computed(() => {
    return Settings.getSetting('afk_enabled') === 'true';
});

// Check if Code Redemption is enabled
const isCodeRedemptionEnabled = computed(() => {
    return Settings.getSetting('code_redemption_enabled') === 'true';
});

// Check if J4R is enabled
const isJ4REnabled = computed(() => {
    return Settings.getSetting('j4r_enabled') === 'true';
});

// Check if Referrals is enabled
const isReferralsEnabled = computed(() => {
    return Settings.getSetting('referrals_enabled') === 'true';
});

// Check if Image Hosting is enabled
const isImageHostingEnabled = computed(() => {
    return Settings.getSetting('image_hosting_enabled') === 'true';
});

// Check if Link For Rewards is enabled
const isL4REnabled = computed(() => {
    return Settings.getSetting('l4r_enabled') === 'true';
});

// Check if Store is enabled
const isStoreEnabled = computed(() => {
    return Settings.getSetting('store_enabled') === 'true';
});

const isLeaderboardEnabled = computed(() => {
    return Settings.getSetting('leaderboard_enabled') === 'true';
});

const isTicketsEnabled = computed(() => {
    return Settings.getSetting('allow_tickets') === 'true';
});

const isServersEnabled = computed(() => {
    return Settings.getSetting('allow_servers') === 'true';
});

defineProps<{
    isSidebarOpen: boolean;
}>();

const isActiveRoute = (routes: string | string[]) => {
    return routes.includes(window.location.pathname);
};

interface MenuItem {
    name: string;
    icon: typeof ServerIcon;
    href?: string;
    active: boolean;
    expanded?: boolean;
    subitems?: MenuItem[];
    tooltip?: string;
}

interface MenuSection {
    title: string;
    items: MenuItem[];
}

// Define AFK Rewards menu item
const afkRewardsMenuItem = {
    name: t('components.sidebar.afk'),
    icon: ClockIcon,
    href: '/earn/afk',
    active: isActiveRoute(['/earn/afk']),
};

// Define Code Redemption menu item
const codeRedemptionMenuItem = {
    name: t('components.sidebar.code_redemption'),
    icon: GiftIcon,
    href: '/earn/redeem',
    active: isActiveRoute(['/earn/redeem']),
};

// Define Image Hosting menu item
const imageHostingMenuItem = {
    name: t('components.sidebar.image_hosting'),
    icon: ImageIcon,
    subitems: [
        {
            name: t('components.sidebar.images'),
            icon: ImageIcon,
            href: '/images',
            active: isActiveRoute(['/images']),
        },
        {
            name: t('components.sidebar.config'),
            icon: Images,
            href: '/images/config',
            active: isActiveRoute(['/images/config']),
        },
        {
            name: t('components.sidebar.settings'),
            icon: SettingsIcon,
            href: '/account',
            active: isActiveRoute(['/account']),
        },
    ],
    active: isActiveRoute(['/images', '/images/config']),
};

// Define Join For Rewards menu item
const j4rMenuItem = {
    name: t('components.sidebar.j4r'),
    icon: UsersIcon,
    href: '/earn/j4r',
    active: isActiveRoute(['/earn/j4r']),
};

// Define Referrals menu item
const referralsMenuItem = {
    name: t('components.sidebar.referrals'),
    icon: UserPlusIcon,
    href: '/earn/referrals',
    active: isActiveRoute(['/earn/referrals']),
};

// Define Link For Rewards menu item
const linkForRewardsMenuItem = {
    name: t('components.sidebar.l4r'),
    icon: LinkIcon,
    href: '/earn/links',
    active: isActiveRoute(['/earn/links']),
};

// Define menu items
const dashboardMenuItem = {
    name: t('components.sidebar.dashboard'),
    icon: HomeIcon,
    href: '/dashboard',
    active: isActiveRoute(['/dashboard']),
};

const storeMenuItem = {
    name: t('components.sidebar.store'),
    icon: ShoppingCartIcon,
    href: '/store',
    active: isActiveRoute(['/store']),
};

const leaderboardMenuItem = {
    name: t('components.sidebar.leaderboard'),
    icon: LeaderboardIcon,
    href: '/leaderboard',
    active: isActiveRoute(['/leaderboard']),
};

const ticketsMenuItem = {
    name: t('components.sidebar.tickets'),
    icon: TicketIcon,
    href: '/ticket',
    active: isActiveRoute(['/ticket']),
    expanded: false,
    subitems: [
        {
            name: Translation.getTranslation('components.sidebar.open_ticket'),
            icon: AlertTriangleIcon,
            href: '/ticket/create',
            active: isActiveRoute(['/ticket/create']),
        },
        {
            name: Translation.getTranslation('components.sidebar.all_tickets'),
            icon: TicketIcon,
            href: '/ticket',
            active: isActiveRoute(['/ticket']),
        },
    ],
};

const serversMenuItem = {
    name: t('components.sidebar.create'),
    icon: ServerIcon,
    href: '/server/create',
    active: isActiveRoute(['/server/create']),
};

// Get the Earn section items based on which features are enabled
const getEarnItems = computed(() => {
    const items: MenuItem[] = [];

    if (isAfkEnabled.value) {
        items.push(afkRewardsMenuItem);
    }

    if (isCodeRedemptionEnabled.value) {
        items.push(codeRedemptionMenuItem);
    }

    if (isJ4REnabled.value) {
        items.push(j4rMenuItem);
    }

    if (isReferralsEnabled.value) {
        items.push(referralsMenuItem);
    }

    if (isL4REnabled.value) {
        items.push(linkForRewardsMenuItem);
    }

    return items;
});

const menuSections = ref<MenuSection[]>([
    {
        title: t('components.sidebar.general'),
        items: [
            dashboardMenuItem,
            ...(isServersEnabled.value ? [serversMenuItem] : []),
            ...(isStoreEnabled.value ? [storeMenuItem] : []),
            ...(isImageHostingEnabled.value ? [imageHostingMenuItem] : []),
        ],
    },
    {
        title: t('components.sidebar.earn'),
        items: getEarnItems.value,
    },
    {
        title: t('components.sidebar.support'),
        items: [
            ...(isTicketsEnabled.value ? [ticketsMenuItem] : []),
            {
                name: Translation.getTranslation('components.sidebar.announcements'),
                icon: BellIcon,
                href: '/announcements',
                active: isActiveRoute(['/announcements']),
            },
            ...(isLeaderboardEnabled.value ? [leaderboardMenuItem] : []),
        ],
    },
]);

const toggleSubitems = (item: MenuItem) => {
    item.expanded = !item.expanded;
};
</script>
<template>
    <aside
        class="fixed top-0 left-0 h-full w-64 bg-[#0a0a0f]/95 backdrop-blur-md border-r border-[#2a2a3f]/30 transform transition-all duration-300 ease-in-out z-50 lg:translate-x-0 lg:z-20 hover:border-[#2a2a3f]/50"
        :class="isSidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    >
        <!-- Sidebar Content -->
        <div class="flex flex-col h-full pt-16">
            <div class="flex-1 overflow-y-auto scrollbar-thin scrollbar-track-[#0a0a0f] scrollbar-thumb-[#2a2a3f]/50">
                <nav class="p-4">
                    <div v-for="(section, index) in menuSections" :key="index" class="mb-6">
                        <!-- Only show the Earn section if it has items -->
                        <template v-if="section.title !== 'Earn' || section.items.length > 0">
                            <div
                                class="text-xs uppercase tracking-wider text-gray-500 font-medium px-4 mb-2 flex items-center gap-2"
                            >
                                <div
                                    class="h-px flex-1 bg-gradient-to-r from-transparent via-[#2a2a3f]/30 to-transparent"
                                ></div>
                                <span class="px-2">{{ section.title }}</span>
                                <div
                                    class="h-px flex-1 bg-gradient-to-r from-transparent via-[#2a2a3f]/30 to-transparent"
                                ></div>
                            </div>
                            <div class="space-y-1">
                                <template v-for="item in section.items" :key="item.name">
                                    <div v-if="item.subitems">
                                        <button
                                            @click="toggleSubitems(item)"
                                            class="w-full flex items-center justify-between gap-3 px-4 py-2.5 rounded-lg hover:bg-[#1a1a2e]/50 transition-all duration-200 group"
                                            :class="{ 'bg-indigo-500/10 text-indigo-400': item.active }"
                                        >
                                            <div class="flex items-center gap-3">
                                                <div
                                                    class="w-8 h-8 rounded-lg bg-[#1a1a2e]/50 flex items-center justify-center transition-all duration-200 group-hover:bg-indigo-500/10"
                                                >
                                                    <component :is="item.icon" class="w-4 h-4" />
                                                </div>
                                                <span class="text-sm font-medium">{{ item.name }}</span>
                                            </div>
                                            <ChevronDownIcon
                                                class="w-4 h-4 transition-transform duration-200 text-gray-400 group-hover:text-indigo-400"
                                                :class="{ 'rotate-180': item.expanded }"
                                            />
                                        </button>
                                        <transition
                                            enter-active-class="transition-all duration-300 ease-in-out"
                                            leave-active-class="transition-all duration-300 ease-in-out"
                                            enter-from-class="opacity-0 max-h-0"
                                            enter-to-class="opacity-100 max-h-[500px]"
                                            leave-from-class="opacity-100 max-h-[500px]"
                                            leave-to-class="opacity-0 max-h-0"
                                        >
                                            <div v-show="item.expanded" class="ml-4 mt-1 space-y-1 overflow-hidden">
                                                <template v-for="subitem in item.subitems" :key="subitem.name">
                                                    <!-- Regular subitem -->
                                                    <div>
                                                        <div
                                                            v-if="subitem.subitems && subitem.subitems.length"
                                                            class="w-full"
                                                        >
                                                            <button
                                                                @click="toggleSubitems(subitem)"
                                                                class="w-full flex items-center justify-between gap-3 px-4 py-2 rounded-lg hover:bg-[#1a1a2e]/50 transition-all duration-200 text-sm group"
                                                                :class="{
                                                                    'bg-indigo-500/10 text-indigo-400': subitem.active,
                                                                }"
                                                            >
                                                                <div class="flex items-center gap-3">
                                                                    <div
                                                                        class="w-7 h-7 rounded-lg bg-[#1a1a2e]/50 flex items-center justify-center transition-all duration-200 group-hover:bg-indigo-500/10"
                                                                    >
                                                                        <component
                                                                            :is="subitem.icon"
                                                                            class="w-3.5 h-3.5"
                                                                        />
                                                                    </div>
                                                                    {{ subitem.name }}
                                                                </div>
                                                                <ChevronDownIcon
                                                                    v-if="subitem.subitems.length"
                                                                    class="w-4 h-4 transition-transform duration-200 text-gray-400 group-hover:text-indigo-400"
                                                                    :class="{ 'rotate-180': subitem.expanded }"
                                                                />
                                                            </button>

                                                            <!-- Nested categories -->
                                                            <transition
                                                                enter-active-class="transition-all duration-300 ease-in-out"
                                                                leave-active-class="transition-all duration-300 ease-in-out"
                                                                enter-from-class="opacity-0 max-h-0"
                                                                enter-to-class="opacity-100 max-h-[500px]"
                                                                leave-from-class="opacity-100 max-h-[500px]"
                                                                leave-to-class="opacity-0 max-h-0"
                                                            >
                                                                <div
                                                                    v-if="subitem.subitems.length"
                                                                    v-show="subitem.expanded"
                                                                    class="ml-4 mt-1 space-y-1 overflow-hidden"
                                                                >
                                                                    <RouterLink
                                                                        v-for="category in subitem.subitems || []"
                                                                        :key="category.name"
                                                                        :to="category.href || ''"
                                                                        class="group relative flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-[#1a1a2e]/50 transition-all duration-200 text-sm"
                                                                        :class="{
                                                                            'bg-indigo-500/10 text-indigo-400':
                                                                                category.active,
                                                                        }"
                                                                    >
                                                                        <div
                                                                            class="w-7 h-7 rounded-lg bg-[#1a1a2e]/50 flex items-center justify-center transition-all duration-200 group-hover:bg-indigo-500/10"
                                                                        >
                                                                            <component
                                                                                :is="category.icon"
                                                                                class="w-3.5 h-3.5"
                                                                            />
                                                                        </div>
                                                                        {{ category.name }}

                                                                        <!-- Tooltip -->
                                                                        <div
                                                                            v-if="category.tooltip"
                                                                            class="absolute left-full ml-2 px-3 py-1.5 bg-[#1a1a2e] border border-[#2a2a3f]/30 text-white text-xs rounded-md whitespace-nowrap opacity-0 group-hover:opacity-100 transition-all duration-200 z-50 pointer-events-none backdrop-blur-md shadow-lg transform group-hover:translate-x-1"
                                                                        >
                                                                            {{ category.tooltip }}
                                                                        </div>
                                                                    </RouterLink>
                                                                </div>
                                                            </transition>
                                                        </div>

                                                        <RouterLink
                                                            v-else
                                                            :to="subitem.href || ''"
                                                            class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-[#1a1a2e]/50 transition-all duration-200 text-sm group"
                                                            :class="{
                                                                'bg-indigo-500/10 text-indigo-400': subitem.active,
                                                            }"
                                                        >
                                                            <div
                                                                class="w-7 h-7 rounded-lg bg-[#1a1a2e]/50 flex items-center justify-center transition-all duration-200 group-hover:bg-indigo-500/10"
                                                            >
                                                                <component :is="subitem.icon" class="w-3.5 h-3.5" />
                                                            </div>
                                                            {{ subitem.name }}
                                                        </RouterLink>
                                                    </div>
                                                </template>
                                            </div>
                                        </transition>
                                    </div>
                                    <RouterLink
                                        v-else
                                        :to="item.href || ''"
                                        class="flex items-center gap-3 px-4 py-2.5 rounded-lg hover:bg-[#1a1a2e]/50 transition-all duration-200 group"
                                        :class="{ 'bg-indigo-500/10 text-indigo-400': item.active }"
                                    >
                                        <div
                                            class="w-8 h-8 rounded-lg bg-[#1a1a2e]/50 flex items-center justify-center transition-all duration-200 group-hover:bg-indigo-500/10"
                                        >
                                            <component :is="item.icon" class="w-4 h-4" />
                                        </div>
                                        <span class="text-sm font-medium">{{ item.name }}</span>
                                    </RouterLink>
                                </template>
                            </div>
                        </template>
                    </div>
                </nav>
            </div>
        </div>
    </aside>
</template>

<style scoped>
.rotate-180 {
    transform: rotate(180deg);
}

/* Custom scrollbar styling */
.scrollbar-thin::-webkit-scrollbar {
    width: 4px;
}

.scrollbar-thin::-webkit-scrollbar-track {
    background: transparent;
}

.scrollbar-thin::-webkit-scrollbar-thumb {
    background: rgba(42, 42, 63, 0.5);
    border-radius: 2px;
}

.scrollbar-thin::-webkit-scrollbar-thumb:hover {
    background: rgba(42, 42, 63, 0.7);
}

/* Tooltip z-index handling */
.group:hover {
    z-index: 100;
}

/* Smooth transitions */
.transition-all {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 200ms;
}

/* Hover effects */
.hover\:translate-x-1:hover {
    transform: translateX(0.25rem);
}

/* Active state glow effect */
.bg-indigo-500\/10 {
    box-shadow: 0 0 15px -3px rgba(99, 102, 241, 0.1);
}

/* Gradient line animation */
@keyframes gradientLine {
    0% {
        background-position: 0% 50%;
    }
    50% {
        background-position: 100% 50%;
    }
    100% {
        background-position: 0% 50%;
    }
}

.bg-gradient-to-r {
    background-size: 200% 100%;
    animation: gradientLine 3s ease infinite;
}
</style>
