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
    UserSearchIcon,
} from 'lucide-vue-next';
import Translation from '@/mythicaldash/Translation';
import { useSettingsStore } from '@/stores/settings';
import { useI18n } from 'vue-i18n';
import { useSkinSettings } from '@/composables/useSkinSettings';

const { t } = useI18n();
const Settings = useSettingsStore();
const { sidebarSettings } = useSkinSettings();

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

// Check if Lookup is enabled
const isLookupEnabled = computed(() => {
    return Settings.getSetting('allow_public_profiles') === 'true';
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

// Define Lookup menu item
const lookupMenuItem = {
    name: t('components.sidebar.lookup'),
    icon: UserSearchIcon,
    href: '/lookup',
    active: isActiveRoute(['/lookup']),
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
            name: t('components.sidebar.upload'),
            icon: ImageIcon,
            href: '/images/upload',
            active: isActiveRoute(['/images/upload']),
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
    active: isActiveRoute(['/images', '/images/config', '/images/upload']),
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
            ...(isLookupEnabled.value ? [lookupMenuItem] : []),
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

// Computed styles based on settings
const sidebarClasses = computed(() => {
    const baseClasses =
        'fixed top-0 left-0 h-full w-64 transform transition-all duration-300 ease-in-out z-50 lg:translate-x-0 lg:z-20';
    const backgroundClasses = sidebarSettings.glassEffect
        ? `bg-[#0a0a0f]/${Math.round(sidebarSettings.backgroundOpacity * 100)} backdrop-blur-md`
        : 'bg-[#0a0a0f]';
    const borderClasses = sidebarSettings.borderGlow
        ? 'border-r border-[#2a2a3f]/30 hover:border-[#2a2a3f]/50 shadow-lg shadow-indigo-500/5'
        : 'border-r border-[#2a2a3f]/20';

    return [baseClasses, backgroundClasses, borderClasses].join(' ');
});

const backgroundStyle = computed(() => {
    if (!sidebarSettings.glassEffect) return {};

    return {
        backgroundImage: `linear-gradient(180deg, 
            rgba(99, 102, 241, 0.02) 0%, 
            rgba(168, 85, 247, 0.01) 50%, 
            rgba(59, 130, 246, 0.02) 100%)`,
    };
});

const compactClass = computed(() => (sidebarSettings.compactMode ? 'compact' : ''));
</script>

<template>
    <aside
        :class="[sidebarClasses, compactClass, isSidebarOpen ? 'translate-x-0' : '-translate-x-full']"
        :style="backgroundStyle"
    >
        <!-- Sidebar Content -->
        <div class="flex flex-col h-full pt-16">
            <div class="flex-1 overflow-y-auto scrollbar-thin scrollbar-track-transparent scrollbar-thumb-[#2a2a3f]/50">
                <nav :class="sidebarSettings.compactMode ? 'p-2' : 'p-4'">
                    <div
                        v-for="(section, index) in menuSections"
                        :key="index"
                        :class="sidebarSettings.compactMode ? 'mb-4' : 'mb-6'"
                    >
                        <!-- Only show the Earn section if it has items -->
                        <template v-if="section.title !== 'Earn' || section.items.length > 0">
                            <!-- Section Header -->
                            <div
                                v-if="sidebarSettings.showSectionDividers"
                                :class="[
                                    'text-xs uppercase tracking-wider text-gray-500 font-medium flex items-center gap-2',
                                    sidebarSettings.compactMode ? 'px-2 mb-1' : 'px-4 mb-2',
                                ]"
                            >
                                <div
                                    class="h-px flex-1 bg-gradient-to-r from-transparent via-[#2a2a3f]/30 to-transparent"
                                ></div>
                                <span class="px-2 text-xs">{{ section.title }}</span>
                                <div
                                    class="h-px flex-1 bg-gradient-to-r from-transparent via-[#2a2a3f]/30 to-transparent"
                                ></div>
                            </div>

                            <!-- Menu Items -->
                            <div class="space-y-1">
                                <template v-for="item in section.items" :key="item.name">
                                    <div v-if="item.subitems">
                                        <button
                                            @click="toggleSubitems(item)"
                                            :class="[
                                                'w-full flex items-center justify-between gap-3 rounded-lg hover:bg-[#1a1a2e]/50 transition-all duration-200 group',
                                                sidebarSettings.compactMode ? 'px-2 py-1.5' : 'px-4 py-2.5',
                                                sidebarSettings.borderGlow && item.active
                                                    ? 'shadow-md shadow-indigo-500/20'
                                                    : '',
                                                item.active ? 'bg-indigo-500/10 text-indigo-400' : '',
                                            ]"
                                        >
                                            <div class="flex items-center gap-3">
                                                <div
                                                    :class="[
                                                        'rounded-lg bg-[#1a1a2e]/50 flex items-center justify-center transition-all duration-200 group-hover:bg-indigo-500/10',
                                                        sidebarSettings.compactMode ? 'w-6 h-6' : 'w-8 h-8',
                                                    ]"
                                                >
                                                    <component
                                                        :is="item.icon"
                                                        :class="sidebarSettings.compactMode ? 'w-3.5 h-3.5' : 'w-4 h-4'"
                                                    />
                                                </div>
                                                <span
                                                    :class="[
                                                        'font-medium',
                                                        sidebarSettings.compactMode ? 'text-xs' : 'text-sm',
                                                    ]"
                                                    >{{ item.name }}</span
                                                >
                                            </div>
                                            <ChevronDownIcon
                                                :class="[
                                                    'transition-transform duration-200 text-gray-400 group-hover:text-indigo-400',
                                                    sidebarSettings.compactMode ? 'w-3 h-3' : 'w-4 h-4',
                                                    item.expanded ? 'rotate-180' : '',
                                                ]"
                                            />
                                        </button>

                                        <!-- Subitems with animation -->
                                        <transition
                                            enter-active-class="transition-all duration-300 ease-in-out"
                                            leave-active-class="transition-all duration-300 ease-in-out"
                                            enter-from-class="opacity-0 max-h-0"
                                            enter-to-class="opacity-100 max-h-[500px]"
                                            leave-from-class="opacity-100 max-h-[500px]"
                                            leave-to-class="opacity-0 max-h-0"
                                        >
                                            <div
                                                v-show="item.expanded"
                                                :class="[
                                                    'overflow-hidden',
                                                    sidebarSettings.compactMode
                                                        ? 'ml-2 mt-0.5 space-y-0.5'
                                                        : 'ml-4 mt-1 space-y-1',
                                                ]"
                                            >
                                                <template v-for="subitem in item.subitems" :key="subitem.name">
                                                    <RouterLink
                                                        :to="subitem.href || ''"
                                                        :class="[
                                                            'flex items-center gap-3 rounded-lg hover:bg-[#1a1a2e]/50 transition-all duration-200 group',
                                                            sidebarSettings.compactMode
                                                                ? 'px-2 py-1.5 text-xs'
                                                                : 'px-4 py-2 text-sm',
                                                            sidebarSettings.borderGlow && subitem.active
                                                                ? 'shadow-sm shadow-indigo-500/20'
                                                                : '',
                                                            subitem.active ? 'bg-indigo-500/10 text-indigo-400' : '',
                                                        ]"
                                                    >
                                                        <div
                                                            :class="[
                                                                'rounded-lg bg-[#1a1a2e]/50 flex items-center justify-center transition-all duration-200 group-hover:bg-indigo-500/10',
                                                                sidebarSettings.compactMode ? 'w-5 h-5' : 'w-7 h-7',
                                                            ]"
                                                        >
                                                            <component
                                                                :is="subitem.icon"
                                                                :class="
                                                                    sidebarSettings.compactMode
                                                                        ? 'w-3 h-3'
                                                                        : 'w-3.5 h-3.5'
                                                                "
                                                            />
                                                        </div>
                                                        {{ subitem.name }}
                                                    </RouterLink>
                                                </template>
                                            </div>
                                        </transition>
                                    </div>

                                    <!-- Regular menu item -->
                                    <RouterLink
                                        v-else
                                        :to="item.href || ''"
                                        :class="[
                                            'flex items-center gap-3 rounded-lg hover:bg-[#1a1a2e]/50 transition-all duration-200 group',
                                            sidebarSettings.compactMode ? 'px-2 py-1.5' : 'px-4 py-2.5',
                                            sidebarSettings.borderGlow && item.active
                                                ? 'shadow-md shadow-indigo-500/20'
                                                : '',
                                            item.active ? 'bg-indigo-500/10 text-indigo-400' : '',
                                        ]"
                                    >
                                        <div
                                            :class="[
                                                'rounded-lg bg-[#1a1a2e]/50 flex items-center justify-center transition-all duration-200 group-hover:bg-indigo-500/10',
                                                sidebarSettings.compactMode ? 'w-6 h-6' : 'w-8 h-8',
                                            ]"
                                        >
                                            <component
                                                :is="item.icon"
                                                :class="sidebarSettings.compactMode ? 'w-3.5 h-3.5' : 'w-4 h-4'"
                                            />
                                        </div>
                                        <span
                                            :class="[
                                                'font-medium',
                                                sidebarSettings.compactMode ? 'text-xs' : 'text-sm',
                                            ]"
                                            >{{ item.name }}</span
                                        >
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

/* Compact mode adjustments */
.compact .scrollbar-thin {
    padding: 0.25rem;
}

/* Smooth transitions */
.transition-all {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 200ms;
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
