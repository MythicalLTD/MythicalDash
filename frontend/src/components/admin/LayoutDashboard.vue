<!-- eslint-disable @typescript-eslint/no-unused-vars -->
<!-- eslint-disable @typescript-eslint/no-explicit-any -->
<template>
    <div class="min-h-screen bg-[#0A0B14] text-gray-100 font-sans">
        <!-- Mobile Menu Button -->
        <button
            @click="isSidebarOpen = !isSidebarOpen"
            class="lg:hidden fixed top-4 left-4 z-50 p-2 bg-[#12141F]/50 rounded-full backdrop-blur-xs"
        >
            <Menu v-if="!isSidebarOpen" class="w-6 h-6 text-[#7C3AED]" />
            <X v-else class="w-6 h-6 text-[#7C3AED]" />
        </button>

        <!-- Sidebar -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 z-40 w-64 transition-transform duration-300 ease-in-out transform',
                isSidebarOpen ? 'translate-x-0' : '-translate-x-full',
                'lg:translate-x-0 bg-[#12141F] border-r border-gray-800/30',
            ]"
        >
            <!-- Logo section -->
            <div class="p-6 border-b border-gray-800/30">
                <div class="flex items-center">
                    <img src="https://github.com/mythicalltd.png" alt="Logo" class="h-8 w-8 mr-3" />
                    <div>
                        <h1 class="text-xl font-semibold text-white">
                            {{ Settings.getSetting('debug_name') }}
                        </h1>
                        <span class="text-xs text-gray-400">{{ Settings.getSetting('debug_version') }}</span>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="p-4">
                <div v-for="(menuGroup, index) in menuGroups" :key="index" class="mb-6">
                    <h3 class="text-xs font-medium uppercase tracking-wider text-gray-400 mb-4 px-4">
                        {{ menuGroup.title }}
                    </h3>
                    <ul class="space-y-1">
                        <li v-for="item in menuGroup.items" :key="item.name">
                            <!-- Menu item with submenu -->
                            <template v-if="item.subMenu">
                                <div
                                    @click="toggleSubmenu(item)"
                                    class="flex items-center px-4 py-2.5 rounded-lg transition-all duration-200 hover:bg-[#1A1D2D] cursor-pointer"
                                    :class="{ 'bg-[#1A1D2D]': item.isOpen }"
                                >
                                    <component :is="item.icon" class="w-5 h-5 mr-3 text-[#7C3AED]" />
                                    <span class="text-sm">{{ item.name }}</span>
                                    <ChevronDown
                                        class="w-4 h-4 ml-auto transition-transform duration-200"
                                        :class="{ 'rotate-180': item.isOpen }"
                                    />
                                    <span
                                        v-if="'count' in item"
                                        class="ml-2 text-xs bg-[#7C3AED] text-white px-2 py-0.5 rounded-full"
                                    >
                                        {{ item.count }}
                                    </span>
                                </div>
                                <!-- Submenu items -->
                                <ul v-if="item.isOpen" class="mt-1 ml-4 space-y-1">
                                    <li v-for="subItem in item.subMenu" :key="subItem.name">
                                        <RouterLink
                                            :to="subItem.path || ''"
                                            class="flex items-center px-4 py-2.5 rounded-lg transition-all duration-200 hover:bg-[#1A1D2D] text-sm"
                                            :class="{ 'bg-[#1A1D2D]': route.path === subItem.path }"
                                        >
                                            <component :is="subItem.icon" class="w-5 h-5 mr-3 text-[#7C3AED]" />
                                            <span>{{ subItem.name }}</span>
                                        </RouterLink>
                                    </li>
                                </ul>
                            </template>
                            <!-- Regular menu item -->
                            <RouterLink
                                v-else
                                :to="item.path || ''"
                                class="flex items-center px-4 py-2.5 rounded-lg transition-all duration-200 hover:bg-[#1A1D2D] text-sm"
                                :class="{ 'bg-[#1A1D2D]': item.active }"
                            >
                                <component :is="item.icon" class="w-5 h-5 mr-3 text-[#7C3AED]" />
                                <span>{{ item.name }}</span>
                                <span
                                    v-if="'count' in item"
                                    class="ml-auto text-xs bg-[#7C3AED] text-white px-2 py-0.5 rounded-full"
                                >
                                    {{ item.count }}
                                </span>
                            </RouterLink>
                        </li>
                    </ul>
                </div>
            </nav>
        </aside>

        <!-- Main Content Area -->
        <div class="lg:ml-64 min-h-screen flex flex-col">
            <!-- Top Navigation -->
            <header class="bg-[#12141F] border-b border-gray-800/30 px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-6 flex-1">
                    <div class="relative w-96">
                        <input
                            v-model="searchQuery"
                            type="search"
                            placeholder="Search... (Ctrl + K)"
                            class="w-full bg-[#1A1D2D] text-gray-100 placeholder-gray-400 rounded-lg py-2.5 pl-10 pr-4 focus:outline-none focus:ring-2 focus:ring-[#7C3AED]"
                            @focus="isSearchFocused = true"
                            @blur="handleSearchBlur"
                        />
                        <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5" />

                        <!-- Search Results Dropdown -->
                        <div
                            v-if="isSearchFocused && filteredResults.length > 0"
                            class="absolute z-50 w-full mt-2 bg-[#12141F] rounded-lg shadow-xl border border-gray-800/30"
                        >
                            <div class="py-2">
                                <RouterLink
                                    v-for="result in filteredResults"
                                    :key="result.id"
                                    :to="result.path"
                                    class="flex items-center px-4 py-2 text-sm hover:bg-[#1A1D2D] transition-colors duration-200"
                                    @mousedown.prevent="handleResultClick(result)"
                                >
                                    <span>{{ result.name }}</span>
                                </RouterLink>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Status & User Profile -->
                <div class="flex items-center gap-6">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full bg-green-400"></div>
                        <span class="text-sm text-gray-400">System Normal</span>
                    </div>

                    <div class="relative">
                        <button
                            @click="isProfileOpen = !isProfileOpen"
                            class="flex items-center gap-2 hover:bg-[#1A1D2D] rounded-lg px-3 py-2 transition-colors"
                        >
                            <img :src="Session.getInfo('avatar')" alt="User Avatar" class="w-8 h-8 rounded-full" />
                            <span class="text-sm font-medium">{{ Session.getInfo('username') }}</span>
                            <ChevronDown class="w-4 h-4" :class="{ 'rotate-180': isProfileOpen }" />
                        </button>

                        <!-- Profile Dropdown -->
                        <div
                            v-if="isProfileOpen"
                            class="absolute right-0 mt-2 w-48 bg-[#12141F] rounded-lg shadow-xl border border-gray-800/30 py-1 animate-fadeIn"
                        >
                            <RouterLink
                                v-for="item in profileMenu"
                                :key="item.name"
                                :to="item.path"
                                class="block px-4 py-2 text-sm hover:bg-[#1A1D2D] transition-colors duration-200"
                            >
                                {{ item.name }}
                            </RouterLink>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-1 p-6 bg-[#0A0B14]">
                <slot></slot>
            </main>

            <!-- Footer -->
            <footer class="bg-[#12141F] border-t border-gray-800/30">
                <div class="max-w-7xl mx-auto px-6 py-4">
                    <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                        <div class="flex items-center gap-2">
                            <img src="https://github.com/mythicalltd.png" alt="Logo" class="h-6 w-6" />
                            <div class="text-sm text-gray-400">
                                <span>© {{ new Date().getFullYear() }} MythicalSystems. All rights reserved.</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-6">
                            <a
                                v-for="link in footerLinks"
                                :key="link.name"
                                :href="link.path"
                                class="text-sm text-gray-400 hover:text-[#7C3AED] transition-colors"
                            >
                                {{ link.name }}
                            </a>
                        </div>
                        <div class="text-sm text-gray-400">
                            <span>Version {{ Settings.getSetting('debug_version') }}</span>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import {
    LayoutDashboard,
    Users,
    Database,
    HardDrive,
    Search,
    ChevronDown,
    Menu,
    X,
    PaperclipIcon,
    InfoIcon,
    PlusCircle,
    SettingsIcon,
    EggIcon,
	Building
} from 'lucide-vue-next';
import Session from '@/mythicaldash/Session';
import StorageMonitor from '@/mythicaldash/StorageMonitor';
import { useSettingsStore } from '@/stores/settings';
import { useRoute, useRouter } from 'vue-router';
import Dashboard from '@/mythicaldash/admin/Dashboard';

const Settings = useSettingsStore();
const router = useRouter();

new StorageMonitor();

if (!Session.isSessionValid()) {
    router.push('/auth/login');
}

try {
    Session.startSession();
} catch (error) {
    console.error('Session failed:', error);
}

if (Session.getInfo('role') == '1' && Session.getInfo('role') == '2') {
    router.push('/dashboard');
}

const handleResultClick = (result: { id: number; name: string; path: string }) => {
    searchQuery.value = '';
    isSearchFocused.value = false;
    router.push(result.path);
};

const toggleSubmenu = (item: MenuItem) => {
    item.isOpen = !item.isOpen;
    menuGroups.value = [...menuGroups.value];
};

const isSidebarOpen = ref(false);
const isProfileOpen = ref(false);
const searchQuery = ref('');
const isSearchFocused = ref(false);

const handleSearchBlur = () => {
    setTimeout(() => {
        isSearchFocused.value = false;
    }, 200);
};

const route = useRoute();
const adminBaseUri = '/mc-admin';

interface MenuItem {
    name: string;
    path?: string;
    icon: unknown;
    active?: boolean;
    count?: unknown;
    subMenu?: MenuItem[];
    isOpen?: boolean;
}

const dashBoard = ref({ count: { user_count: 0, locations_count: 0, tickets_count: 0, eggs_count: 0, departments_count: 0 } });
Dashboard.get().then((data) => {
    dashBoard.value = data;
});

const menuGroups = ref<{ title: string; items: MenuItem[] }[]>([
    {
        title: 'Main Menu',
        items: [
            {
                name: 'Dashboard',
                path: `${adminBaseUri}`,
                icon: LayoutDashboard,
                active: route.path === `${adminBaseUri}`,
            },
        ],
    },
    {
        title: 'Management',
        items: [
            {
                name: 'Users',
                path: `${adminBaseUri}/users`,
                icon: Users,
                count: computed(() => dashBoard.value.count.user_count || 0),
                active: route.path === `${adminBaseUri}/users`,
            },
            {
                name: 'Locations',
                icon: PaperclipIcon,
                subMenu: [
                    {
                        name: 'All Locations',
                        path: `${adminBaseUri}/locations`,
                        icon: PaperclipIcon,
                    },
                    {
                        name: 'Create Location',
                        path: `${adminBaseUri}/locations/create`,
                        icon: PlusCircle,
                    },
                ],
                active: route.path === `${adminBaseUri}/locations`,
                count: computed(() => dashBoard.value.count.locations_count || 0),
            },
            {
                name: 'Eggs',
                icon: EggIcon,
                count: computed(() => dashBoard.value.count.eggs_count || 0),
                subMenu: [
                    {
                        name: 'Categories',
                        path: `${adminBaseUri}/egg-categories`,
                        icon: EggIcon,
                    },

                    {
                        name: 'Create Category',
                        path: `${adminBaseUri}/egg-categories/create`,
                        icon: PlusCircle,
                    },
                    {
                        name: 'All Eggs',
                        path: `${adminBaseUri}/eggs`,
                        icon: EggIcon,
                    },
                    {
                        name: 'Create Egg',
                        path: `${adminBaseUri}/eggs/create`,
                        icon: PlusCircle,
                    },
                ],
            },
        ],
    },
    {
        title: 'Support Buddy',
        items: [
            {
                name: 'Tickets',
                path: `${adminBaseUri}/tickets`,
                icon: InfoIcon,
                active: route.path === `${adminBaseUri}/tickets`,
                count: computed(() => dashBoard.value.count.tickets_count || 0),
            },
			{
				name: 'Departments',
				path: `${adminBaseUri}/departments`,
				icon: Building,	
				count: computed(() => dashBoard.value.count.departments_count || 0),
				active: route.path === `${adminBaseUri}/departments`,
			},
        ],
    },
    {
        title: 'Advanced',
        items: [
            {
                name: 'Settings',
                path: `${adminBaseUri}/settings`,
                icon: SettingsIcon,
                active: route.path === `${adminBaseUri}/settings`,
            },
            {
                name: 'MythicalCloud (Synced)',
                path: `${adminBaseUri}/mythicalcloud`,
                icon: Database,
                active: route.path === `${adminBaseUri}/mythicalcloud`,
            },
            {
                name: 'Addons',
                path: `${adminBaseUri}/addons`,
                icon: HardDrive,
                active: route.path === `${adminBaseUri}/addons`,
            },
        ],
    },
]);

const profileMenu = [
    { name: 'Profile', path: '/account' },
    { name: 'Exit Admin', path: '/dashboard' },
    { name: 'Sign out', path: '/auth/logout' },
];

const footerLinks = [
    { name: 'Buy a license', path: 'https://mythicalclient.com' },
    { name: 'Documentation', path: 'https://mythical.systems' },
    { name: 'Status', path: 'https://status.mythical.systems' },
];

const searchResults = [
    { id: 1, name: 'Dashboard', path: `${adminBaseUri}` },
    { id: 2, name: 'Locations', path: `${adminBaseUri}/locations` },
    { id: 3, name: 'Create Location', path: `${adminBaseUri}/locations/create` },
    { id: 4, name: 'Egg Categories', path: `${adminBaseUri}/egg-categories` },
    { id: 5, name: 'Create Egg Category', path: `${adminBaseUri}/egg-categories/create` },
    { id: 6, name: 'Tickets', path: `${adminBaseUri}/tickets` },
    { id: 7, name: 'Users', path: `${adminBaseUri}/users` },
	{ id: 8, name: 'Departments', path: `${adminBaseUri}/departments` },
	{ id: 9, name: 'Create Department', path: `${adminBaseUri}/departments/create` },
];

const filteredResults = computed(() => {
    if (!searchQuery.value) return [];
    const query = searchQuery.value.toLowerCase();
    return searchResults.filter((result) => result.name.toLowerCase().includes(query));
});

// Add keyboard shortcut for search
onMounted(() => {
    document.addEventListener('keydown', (e: KeyboardEvent) => {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            const searchInput = document.querySelector('input[type="search"]') as HTMLInputElement;
            if (searchInput) {
                searchInput.focus();
            }
        }
    });
});

onUnmounted(() => {
    document.removeEventListener('keydown', (e: KeyboardEvent) => {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
        }
    });
});
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

body {
    font-family: 'Inter', sans-serif;
}

.animate-fadeIn {
    animation: fadeIn 0.2s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Custom scrollbar styles */
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.1);
}

::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.3);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.5);
}

/* Glassmorphism effect */
.backdrop-blur-md {
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
}

/* Gradient text effect */
.bg-gradient-text {
    background-clip: text;
    -webkit-background-clip: text;
    color: transparent;
}

/* Smooth transitions */
.transition-all {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}

/* Hover effects */
.hover\:opacity-80:hover {
    opacity: 0.8;
}

.hover\:bg-gray-700\/50:hover {
    background-color: rgba(55, 65, 81, 0.5);
}

/* Responsive layout adjustments */
@media (max-width: 1023px) {
    .lg\:ml-64 {
        margin-left: 0;
    }
}

.search-results-enter-active,
.search-results-leave-active {
    transition: opacity 0.2s ease;
}

.search-results-enter-from,
.search-results-leave-to {
    opacity: 0;
}
</style>
