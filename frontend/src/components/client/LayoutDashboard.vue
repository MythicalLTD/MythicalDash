<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import { useRouter } from 'vue-router';
import LoadingScreen from '@/components/client/ui/LoadingScreen.vue';
import TopNavBar from '@/components/client/layout/TopNavBar.vue';
import Sidebar from '@/components/client/layout/Sidebar.vue';
import SearchModal from '@/components/client/layout/SearchModal.vue';
import NotificationsDropdown from '@/components/client/layout/NotificationsDropdown.vue';
import ProfileDropdown from '@/components/client/layout/ProfileDropdown.vue';
import { SettingsIcon, UserIcon, UsersIcon } from 'lucide-vue-next';
import Session from '@/mythicaldash/Session';
import StorageMonitor from '@/mythicaldash/StorageMonitor';
import MythicalDash from '@/mythicaldash/MythicalDash';
import { LicenseServer } from '@/mythicaldash/LicenseServer';
import Permissions from '@/mythicaldash/Permissions';
import Roles from '@/mythicaldash/admin/Roles';
import { useI18n } from 'vue-i18n';
import { useSettingsStore } from '@/stores/settings';
import Swal from 'sweetalert2';

MythicalDash.download();

new StorageMonitor();

const router = useRouter();
const { t } = useI18n();
const Settings = useSettingsStore();

if (!Session.isSessionValid()) {
    router.push('/auth/login');
}

try {
    Session.startSession();
} catch (error) {
    console.error('Session failed:', error);
}

// Account linking check function
const checkAccountLinkingRequirements = () => {
    const forceDiscordLink = Settings.getSetting('force_discord_link') === 'true';
    const forceGithubLink = Settings.getSetting('force_github_link') === 'true';

    const discordLinked = Session.getInfo('discord_linked') === 'true';
    const githubLinked = Session.getInfo('github_linked') === 'true';

    // Check if user needs to link any accounts
    const needsDiscordLink = forceDiscordLink && !discordLinked;
    const needsGithubLink = forceGithubLink && !githubLinked;

    // If any linking is required and not completed, redirect to account page
    if (needsDiscordLink || needsGithubLink) {
        // Only redirect if not already on account page to avoid infinite redirects
        if (router.currentRoute.value.path !== '/account') {
            // Determine which accounts need linking for dynamic message
            const requiredAccounts = [];
            if (needsDiscordLink) requiredAccounts.push('Discord');
            if (needsGithubLink) requiredAccounts.push('GitHub');

            let message = '';
            if (requiredAccounts.length === 1) {
                message = t('dashboard.alerts.account_linking.message_single', { type: requiredAccounts[0] });
            } else {
                message = t('dashboard.alerts.account_linking.message_multiple', {
                    types: requiredAccounts.join(' and '),
                });
            }

            // Show alert before redirecting
            Swal.fire({
                icon: 'warning',
                title: t('dashboard.alerts.account_linking.title'),
                text: message,
                footer: t('dashboard.alerts.account_linking.footer'),
                showConfirmButton: true,
                confirmButtonText: t('dashboard.alerts.account_linking.continue'),
                allowOutsideClick: false,
            }).then(() => {
                router.push('/account?tab=' + t('account.pages.index.tabs.linked_accounts'));
            });
        }
    }
};

// Computed property to check if session data is ready
const isSessionReady = computed(() => {
    const uuid = Session.getInfo('uuid');
    const discordLinked = Session.getInfo('discord_linked');
    const githubLinked = Session.getInfo('github_linked');

    // Session is ready when we have basic user data and account linking status
    return uuid && discordLinked !== null && githubLinked !== null;
});

// Watch for session readiness and trigger account linking check
watch(
    isSessionReady,
    (ready) => {
        if (ready) {
            checkAccountLinkingRequirements();
        }
    },
    { immediate: true },
);

const loading = ref(true);
const isSidebarOpen = ref(false);
const isSearchOpen = ref(false);
const isNotificationsOpen = ref(false);
const isReloading = ref(false);
const isProfileOpen = ref(false);

const rolesData = ref<Array<{ id: number; name: string; color: string }>>([]);

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

    fetchRoles();
});

const userBackground = computed(() => {
    return Session.getInfo('background');
});

const pageBackgroundStyle = computed(() => {
    if (!userBackground.value) {
        return {};
    }

    return {
        backgroundImage: `linear-gradient(135deg, rgba(3, 3, 5, 0.85) 0%, rgba(10, 10, 21, 0.85) 50%, rgba(3, 3, 5, 0.85) 100%), url('${userBackground.value}')`,
        backgroundSize: 'cover',
        backgroundPosition: 'center',
        backgroundRepeat: 'no-repeat',
        backgroundAttachment: 'fixed',
    };
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
    document.removeEventListener('keydown', handleKeydown);
    document.removeEventListener('visibilitychange', handleVisibilityChange);
});
const isProfileEnabled = computed(() => {
    return Settings.getSetting('allow_public_profiles') === 'true';
});
// Computed properties
const profileMenu = computed(() => {
    const menu = [{ name: t('components.profileDropdown.settings'), icon: SettingsIcon, href: '/account' }];
    if (isProfileEnabled.value) {
        menu.push({
            name: t('components.profileDropdown.profile'),
            icon: UserIcon,
            href: `/profile/${Session.getInfo('uuid')}`,
        });
    }
    if (Session.hasPermission(Permissions.ADMIN_DASHBOARD_VIEW)) {
        menu.splice(1, 0, { name: t('components.profileDropdown.adminArea'), icon: UsersIcon, href: '/mc-admin' });
    }
    return menu;
});

const userInfo = computed(() => {
    const roleId = Number(Session.getInfo('role'));
    const roleInfo = getRoleInfo(roleId);
    return {
        firstName: Session.getInfo('first_name'),
        lastName: Session.getInfo('last_name'),
        roleName: roleInfo.name,
        roleColor: roleInfo.color,
        email: Session.getInfo('email'),
        avatar: Session.getInfo('avatar'),
        background: Session.getInfo('background'),
    };
});

const showFooter = ref(true);

onMounted(async () => {
    try {
        const isValid = await LicenseServer.isLicenseValid('branding-removal');
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

        // The account linking check will be triggered by the watcher when session data is ready
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

// Watch for session changes to check account linking requirements
watch(
    () => [Session.getInfo('discord_linked'), Session.getInfo('github_linked'), Session.getInfo('email_verified')],
    () => {
        checkAccountLinkingRequirements();
    },
);

const fetchRoles = async () => {
    try {
        const response = await Roles.getRoles();
        if (response.success) {
            rolesData.value = response.roles;
        }
    } catch (error) {
        console.error('Error fetching roles:', error);
    }
};

const getRoleInfo = (roleId: number) => {
    const role = rolesData.value.find((r) => r.id === roleId);
    if (role) return role;
    return { name: 'User', color: '#9CA3AF' };
};
</script>
<template>
    <div class="min-h-screen bg-[#030305] relative overflow-hidden" :style="pageBackgroundStyle">
        <!-- Background elements -->
        <div
            class="absolute inset-0"
            :class="
                userBackground
                    ? 'bg-gradient-to-b from-black/30 via-black/20 to-black/30'
                    : 'bg-gradient-to-b from-[#030305] via-[#0a0a15] to-[#030305]'
            "
        >
            <div class="stars" :class="{ 'opacity-50': userBackground }"></div>
            <div class="grid-overlay" :class="{ 'opacity-30': userBackground }"></div>
            <div class="glow-effects" :class="{ 'opacity-50': userBackground }"></div>
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
                        roleColor: userInfo.roleColor || '',
                        email: userInfo.email || '',
                        avatar: userInfo.avatar || '',
                        background: userInfo.background || '',
                    }"
                    class="bg-[#050508]/95 backdrop-blur-lg border border-[#1a1a2f]/30"
                />

                <!-- Footer -->
                <footer v-if="showFooter" class="relative z-10 py-4 px-6 text-center text-sm text-gray-500">
                    <a href="https://mythical.systems" class="hover:text-indigo-400 transition-colors">
                        MythicalSystems
                    </a>
                    <p>2020 - {{ new Date().getFullYear() }}</p>
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
