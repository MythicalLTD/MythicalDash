<!-- eslint-disable @typescript-eslint/no-unused-vars -->
<!-- eslint-disable @typescript-eslint/no-explicit-any -->
<template>
    <div
        class="min-h-screen bg-gradient-to-br from-[#0A0B14] via-[#0D0F1A] to-[#070A16] text-gray-100 font-sans relative"
    >
        <!-- Background Image -->
        <div
            class="fixed inset-0 bg-cover bg-center bg-no-repeat opacity-20 z-0"
            style="background-image: url('https://cdn.mythical.systems/mc.jpg')"
        ></div>

        <!-- Mobile Menu Button -->
        <MobileMenuButton :is-sidebar-open="isSidebarOpen" @toggle="isSidebarOpen = !isSidebarOpen" />

        <!-- Sidebar -->
        <TheSidebar
            :is-sidebar-open="isSidebarOpen"
            :menu-groups="menuGroups"
            :settings="Settings"
            @toggle-submenu="toggleSubmenu"
        />

        <!-- Main Content Area -->
        <div class="lg:ml-72 min-h-screen flex flex-col relative z-10">
            <!-- Top Navigation -->
            <TheHeader
                :search-query="searchQuery"
                :is-search-focused="isSearchFocused"
                :filtered-results="filteredResults"
                :is-profile-open="isProfileOpen"
                :profile-menu="profileMenu"
                :session="{ getInfo: (key: string) => Session.getInfo(key) }"
                @update:search-query="searchQuery = $event"
                @update:is-search-focused="isSearchFocused = $event"
                @update:is-profile-open="isProfileOpen = $event"
                @handle-search-blur="handleSearchBlur"
                @handle-result-click="handleResultClick"
            />

            <!-- Main Content -->
            <main class="flex-1 p-6">
                <div class="max-w-7xl mx-auto">
                    <div class="relative">
                        <!-- Background Pattern -->
                        <div class="absolute inset-0 opacity-30">
                            <div
                                class="absolute inset-0"
                                style="
                                    background-image: radial-gradient(
                                        circle at 1px 1px,
                                        rgba(255, 255, 255, 0.05) 1px,
                                        transparent 0
                                    );
                                    background-size: 20px 20px;
                                "
                            ></div>
                        </div>
                        <div class="relative z-10">
                            <slot></slot>
                        </div>
                    </div>
                </div>
            </main>

            <!-- Footer -->
            <TheFooter :footer-links="footerLinks" :settings="Settings" />
        </div>
    </div>
</template>

<script setup lang="ts">
import Swal from 'sweetalert2';
import { ref, onMounted, onUnmounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import Session from '@/mythicaldash/Session';
import { useSettingsStore } from '@/stores/settings';
import Dashboard from '@/mythicaldash/admin/Dashboard';

// Import components
import MobileMenuButton from '@/components/admin/components/MobileMenuButton.vue';
import TheSidebar from '@/components/admin/components/TheSidebar.vue';
import TheHeader from '@/components/admin/components/TheHeader.vue';
import TheFooter from '@/components/admin/components/TheFooter.vue';

// Import menu configuration
import { useAdminMenu } from '@/components/admin/composables/useAdminMenu';
import { useSearchResults } from '@/components/admin/composables/useSearchResults';
import { MythicalDOM } from '@/mythicaldash/MythicalDOM';

MythicalDOM.setPageTitle('Admin');

const Settings = useSettingsStore();
const router = useRouter();
const route = useRoute();

// Authentication checks
if (!Session.isSessionValid()) {
    router.push('/auth/login');
}

try {
    Session.startSession();
} catch (error) {
    console.error('Session failed:', error);
}

// State management
const isSidebarOpen = ref(false);
const isProfileOpen = ref(false);
const searchQuery = ref('');
const isSearchFocused = ref(false);

// Dashboard data
const dashBoard = ref({
    count: {
        user_count: 0,
        locations_count: 0,
        tickets_count: 0,
        eggs_count: 0,
        departments_count: 0,
        announcements_count: 0,
        server_queue_count: 0,
        mail_templates_count: 0,
        settings_count: 0,
        redeem_codes_count: 0,
        servers_count: 0,
        plugins_count: 0,
        backups_count: 0,
        images_count: 0,
        redirect_links_count: 0,
        roles_count: 0,
        j4r_servers_count: 0,
        image_reports_count: 0,
    },
});

// Fetch dashboard data
Dashboard.get().then((data) => {
    dashBoard.value = data;
});

// Get menu configuration
const { menuGroups, profileMenu } = useAdminMenu(route, dashBoard);

// Get search functionality
const { filteredResults } = useSearchResults(searchQuery);

// Event handlers
const handleResultClick = (result: { id: number; name: string; path: string }) => {
    searchQuery.value = '';
    isSearchFocused.value = false;
    router.push(result.path);
};

interface MenuItem {
    name: string;
    path?: string;
    icon: unknown;
    active?: boolean;
    count?: unknown;
    subMenu?: MenuItem[];
    isOpen?: boolean;
}

const toggleSubmenu = (item: MenuItem) => {
    item.isOpen = !item.isOpen;
    menuGroups.value = [...menuGroups.value];
};

const handleSearchBlur = () => {
    setTimeout(() => {
        isSearchFocused.value = false;
    }, 200);
};

// Footer links
const footerLinks = [
    { name: 'Buy a license', path: 'https://discord.mythical.systems' },
    { name: 'Documentation', path: 'https://mythical.systems' },
    { name: 'Status', path: 'https://status.mythical.systems' },
];

// Keyboard shortcuts
onMounted(() => {
    document.addEventListener('keydown', handleKeyDown);
    if (Settings.getSetting('force_2fa') == 'true' && Session.getInfo('2fa_enabled') == 'false') {
        Swal.fire({
            title: 'Two-factor authentication is required',
            text: 'Please enable 2FA in your account settings.',
            icon: 'warning',
            confirmButtonText: 'OK',
            confirmButtonColor: '#3085d6',
            showCancelButton: true,
            cancelButtonText: 'Cancel',
            cancelButtonColor: '#d33',
        });
        router.push('/account?tab=2fa');
    }
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeyDown);
});

const handleKeyDown = (e: KeyboardEvent) => {
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        const searchInput = document.querySelector('input[type="search"]') as HTMLInputElement;
        if (searchInput) {
            searchInput.focus();
        }
    }
};
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

body {
    font-family: 'Inter', sans-serif;
}
</style>
