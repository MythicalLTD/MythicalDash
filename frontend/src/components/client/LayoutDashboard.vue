<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import LoadingScreen from '@/components/client/ui/LoadingScreen.vue';
import TopNavBar from '@/components/client/layout/TopNavBar.vue';
import Sidebar from '@/components/client/layout/Sidebar.vue';
import SearchModal from '@/components/client/layout/SearchModal.vue';
import NotificationsDropdown from '@/components/client/layout/NotificationsDropdown.vue';
import ProfileDropdown from '@/components/client/layout/ProfileDropdown.vue';
import { SettingsIcon, UsersIcon } from 'lucide-vue-next';
import Session from '@/mythicaldash/Session';
import StorageMonitor from '@/mythicaldash/StorageMonitor';
import MythicalDash from '@/mythicaldash/MythicalDash';
import { LicenseServer } from '@/mythicaldash/LicenseServer';
import ReloadAnimation from '@/components/client/ui/ReloadAnimation.vue';

MythicalDash.download();

new StorageMonitor();

const router = useRouter();

if (!Session.isSessionValid()) {
    router.push('/auth/login');
}

try {
    Session.startSession();
} catch (error) {
    console.error('Session failed:', error);
}

const loading = ref(true);
const isSidebarOpen = ref(false);
const isSearchOpen = ref(false);
const isNotificationsOpen = ref(false);
const isReloading = ref(false);
const isProfileOpen = ref(false);

// Toggle functions
const toggleSidebar = () => {
    isSidebarOpen.value = !isSidebarOpen.value;
};

const closeSidebar = () => {
    isSidebarOpen.value = false;
};

const toggleSearch = () => {
    isSearchOpen.value = true;
    isNotificationsOpen.value = false;
    isProfileOpen.value = false;
};

const toggleNotifications = () => {
    isNotificationsOpen.value = !isNotificationsOpen.value;
    isProfileOpen.value = false;
    isSearchOpen.value = false;
};

const toggleProfile = () => {
    isProfileOpen.value = !isProfileOpen.value;
    isNotificationsOpen.value = false;
    isSearchOpen.value = false;
};

const closeSearch = () => {
    isSearchOpen.value = false;
};

const navigateToResult = (href: string) => {
    closeSearch();
    router.push(href);
};

const closeDropdowns = () => {
    isNotificationsOpen.value = false;
    isProfileOpen.value = false;
};

// Event handlers
const handleClickOutside = (event: MouseEvent) => {
    const target = event.target as HTMLElement | null;
    if (target && !target.closest('.dropdown') && !target.closest('button')) {
        closeDropdowns();
    }
};

const handleKeydown = async (event: KeyboardEvent) => {
    if (event.key === 'F5' || (event.ctrlKey && event.key === 'r')) {
        event.preventDefault();
        await reloadUserData();
        return;
    }
    if (event.ctrlKey && event.key === 'S') {
        event.preventDefault();
        toggleSearch();
    }
    if (event.key === 'Escape') {
        closeSearch();
        closeDropdowns();
        closeSidebar();
    }

    if ((event.ctrlKey || event.metaKey) && event.key === 'k') {
        event.preventDefault();
        toggleSearch();
    }
    // Ctrl + D to open search
    if ((event.ctrlKey || event.metaKey) && event.key === 'd') {
        event.preventDefault();
        toggleSearch();
    }
};

const handleVisibilityChange = () => {
    document.title = document.hidden ? `${document.title} - Inactive` : document.title.replace(' - Inactive', '');
};

// Lifecycle hooks
onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    document.addEventListener('keydown', handleKeydown);
    document.addEventListener('visibilitychange', handleVisibilityChange);

    if (sessionStorage.getItem('firstLoad') === null) {
        loading.value = true;
        setTimeout(() => {
            loading.value = false;
            sessionStorage.setItem('firstLoad', 'false');
        }, 2000);
    } else {
        loading.value = false;
    }
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
    document.removeEventListener('keydown', handleKeydown);
    document.removeEventListener('visibilitychange', handleVisibilityChange);
});

// Computed properties
const profileMenu = computed(() => {
    const menu = [{ name: 'Settings', icon: SettingsIcon, href: '/account' }];
    const role = Session.getInfo('role_real_name') ?? '';
    if (['admin', 'administrator', 'support', 'supportbuddy'].includes(role)) {
        menu.splice(1, 0, { name: 'Admin Area', icon: UsersIcon, href: '/mc-admin' });
    }

    return menu;
});

const userInfo = computed(() => ({
    firstName: Session.getInfo('first_name'),
    lastName: Session.getInfo('last_name'),
    roleName: Session.getInfo('role_name'),
    email: Session.getInfo('email'),
    avatar: Session.getInfo('avatar'),
}));

const showFooter = ref(true);

onMounted(async () => {
    try {
        const isValid = await LicenseServer.isLicenseValid();
        showFooter.value = !isValid;
    } catch (error) {
        console.error('Error checking license:', error);
        showFooter.value = true;
    }
});

const reloadUserData = async () => {
    isReloading.value = true;

    try {
        console.log('Reloading user data...');

        await Session.cleanup();
        await Session.startSession();

        setTimeout(() => {
            isReloading.value = false;
        }, 3500);

        router.go(0);

        console.log('User data reloaded successfully');
    } catch (error) {
        console.error('Failed to reload user data:', error);
        isReloading.value = false;
    }
};
</script>
<template>
    <ReloadAnimation :isReloading="isReloading" />
    <div class="min-h-screen bg-[#030305] relative overflow-hidden">
        <!-- Background elements -->
        <div class="absolute inset-0 bg-gradient-to-b from-[#030305] via-[#0a0a15] to-[#030305]">
            <div class="stars"></div>
            <div class="grid-overlay"></div>
            <div class="glow-effects"></div>
        </div>

        <!-- Content wrapper -->
        <div class="relative z-10 min-h-screen">
            <LoadingScreen v-if="loading" />

            <template v-if="!loading">
                <!-- Backdrop for mobile sidebar -->
                <div
                    v-if="isSidebarOpen"
                    class="fixed inset-0 bg-black/80 backdrop-blur-sm z-40 lg:hidden"
                    @click="closeSidebar"
                ></div>

                <TopNavBar
                    :isSidebarOpen="isSidebarOpen"
                    @toggle-sidebar="toggleSidebar"
                    @toggle-search="toggleSearch"
                    @toggle-notifications="toggleNotifications"
                    @toggle-profile="toggleProfile"
                    class="bg-[#050508]/90 backdrop-blur-md border-b border-[#1a1a2f]/30"
                />

                <!-- Sidebar with updated styling -->
                <Sidebar
                    :isSidebarOpen="isSidebarOpen"
                    class="bg-[#050508]/95 backdrop-blur-md border-r border-[#1a1a2f]/30"
                />

                <!-- Main Content -->
                <main class="pt-16 lg:pl-64 min-h-screen relative">
                    <div class="p-4 md:p-6 max-w-7xl mx-auto">
                        <slot></slot>
                    </div>
                </main>

                <!-- Modals and dropdowns -->
                <SearchModal
                    :isSearchOpen="isSearchOpen"
                    @close="closeSearch"
                    @navigate="navigateToResult"
                    class="bg-[#050508]/95 backdrop-blur-lg border border-[#1a1a2f]/30"
                />

                <NotificationsDropdown
                    :isOpen="isNotificationsOpen"
                    class="bg-[#050508]/95 backdrop-blur-lg border border-[#1a1a2f]/30"
                />

                <ProfileDropdown
                    :isOpen="isProfileOpen"
                    :profileMenu="profileMenu"
                    :stats="{
                        tickets: Session.getInfo('tickets'),
                        coins: Session.getInfo('credits'),
                        servers: Session.getInfo('servers'),
                    }"
                    :userInfo="{
                        firstName: userInfo.firstName || '',
                        lastName: userInfo.lastName || '',
                        roleName: userInfo.roleName || '',
                        email: userInfo.email || '',
                        avatar: userInfo.avatar || '',
                    }"
                    class="bg-[#050508]/95 backdrop-blur-lg border border-[#1a1a2f]/30"
                />

                <!-- Footer -->
                <footer v-if="showFooter" class="relative z-10 py-4 px-6 text-center text-sm text-gray-500">
                    <a href="https://mythical.systems" class="hover:text-indigo-400 transition-colors">
                        MythicalSystems
                    </a>
                    <p>LTD 2020 - {{ new Date().getFullYear() }}</p>
                </footer>
            </template>
        </div>
    </div>
</template>

<style scoped>
.stars {
    position: absolute;
    inset: 0;
    background-image:
        radial-gradient(1px 1px at 20% 30%, rgba(255, 255, 255, 0.2) 0%, transparent 100%),
        radial-gradient(1px 1px at 40% 70%, rgba(255, 255, 255, 0.15) 0%, transparent 100%),
        radial-gradient(1px 1px at 60% 40%, rgba(255, 255, 255, 0.2) 0%, transparent 100%),
        radial-gradient(2px 2px at 80% 10%, rgba(255, 255, 255, 0.15) 0%, transparent 100%);
    background-size:
        250px 250px,
        200px 200px,
        300px 300px,
        350px 350px;
    animation: twinkle 10s infinite;
}

.grid-overlay {
    position: absolute;
    inset: 0;
    background-image:
        linear-gradient(to right, rgba(42, 42, 63, 0.07) 1px, transparent 1px),
        linear-gradient(to bottom, rgba(42, 42, 63, 0.07) 1px, transparent 1px);
    background-size: 50px 50px;
    mask-image: linear-gradient(to bottom, transparent, rgba(0, 0, 0, 0.4));
}

.glow-effects {
    position: absolute;
    inset: 0;
    background:
        radial-gradient(circle at 20% 20%, rgba(99, 102, 241, 0.03) 0%, transparent 50%),
        radial-gradient(circle at 80% 80%, rgba(99, 102, 241, 0.03) 0%, transparent 50%);
    pointer-events: none;
}

@keyframes twinkle {
    0%,
    100% {
        opacity: 0.3;
    }
    50% {
        opacity: 0.5;
    }
}

/* Mobile optimizations */
@media (max-width: 768px) {
    .grid-overlay {
        background-size: 30px 30px;
    }
}
</style>
