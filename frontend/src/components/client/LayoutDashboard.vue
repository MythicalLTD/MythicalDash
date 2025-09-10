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
import MythicalDash from '@/mythicaldash/MythicalDash';
import Permissions from '@/mythicaldash/Permissions';
import Roles from '@/mythicaldash/admin/Roles';
import { useI18n } from 'vue-i18n';
import { useSettingsStore } from '@/stores/settings';
import Swal from 'sweetalert2';

MythicalDash.download();
// Check for refresh flag and refresh if needed
const checkRefreshFlag = () => {
    const needsRefresh = localStorage.getItem('needs_refresh');
    if (needsRefresh === 'true') {
        setTimeout(() => {
            localStorage.removeItem('needs_refresh');
            console.log('Refreshing page...');
            window.location.reload();
        }, 3500);
    }
};

// Check refresh flag on mount
onMounted(() => {
    checkRefreshFlag();
});

const router = useRouter();
const { t } = useI18n();
const Settings = useSettingsStore();

// Adblocker detection state
const isAdblockerEnabled = ref(false);
const isAdblockerDetected = ref(false);
const isBlockedByAdblocker = ref(false);
const adblockerCheckCompleted = ref(false);

// Adblocker detection methods
const detectAdblocker = () => {
    console.log('🔍 Starting adblocker detection...');

    // Check if anti-adblocker is enabled in settings
    const antiAdblockerEnabled = Settings.getSetting('anti_adblocker_enabled') === 'true';
    console.log('⚙️ Anti-adblocker enabled:', antiAdblockerEnabled);

    if (!antiAdblockerEnabled) {
        console.log('❌ Anti-adblocker disabled in settings, skipping detection');
        adblockerCheckCompleted.value = true;
        return;
    }

    console.log('🔍 Testing adblocker detection with offsetHeight method...');

    let fakeAd = document.createElement('div');
    fakeAd.className = 'textads banner-ads banner_ads ad-unit ad-zone ad-space adsbox';
    fakeAd.style.height = '1px';

    document.body.appendChild(fakeAd);

    let x_width = fakeAd.offsetHeight;
    console.log('📊 offsetHeight result:', x_width);

    if (x_width) {
        console.log('✅ No adblocker detected - element is visible');
        isAdblockerDetected.value = false;
    } else {
        console.log('🚨 Adblocker detected - element is hidden!');
        isAdblockerDetected.value = true;
        isAdblockerEnabled.value = antiAdblockerEnabled;

        if (antiAdblockerEnabled) {
            console.log('🚨 Showing blocking modal...');
            handleAdblockerDetected();
        }
    }

    document.body.removeChild(fakeAd);
    adblockerCheckCompleted.value = true;
};

const handleAdblockerDetected = () => {
    isBlockedByAdblocker.value = true;

    Swal.fire({
        icon: 'warning',
        title: t('dashboard.alerts.adblocker_detected.title'),
        text: t('dashboard.alerts.adblocker_detected.message'),
        footer: t('dashboard.alerts.adblocker_detected.footer'),
        showCancelButton: false,
        confirmButtonText: t('dashboard.alerts.adblocker_detected.disable_adblocker'),
        allowOutsideClick: false,
        showConfirmButton: false,
        allowEscapeKey: false,
        backdrop: `
            rgba(0,0,123,0.4)
            url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23ff0000' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M18.364 18.364A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728'/%3E%3C/svg%3E")
            no-repeat
            center
        `,
        didOpen: () => {
            // Prevent any interaction with the page
            document.body.style.pointerEvents = 'none';
        },
    }).then((result) => {
        if (result.isConfirmed) {
            // User clicked "Disable Ad Blocker"
            Swal.fire({
                icon: 'info',
                title: t('dashboard.alerts.adblocker_detected.instructions_title'),
                text: t('dashboard.alerts.adblocker_detected.instructions_text'),
                confirmButtonText: t('dashboard.alerts.adblocker_detected.refresh_page'),
                allowOutsideClick: false,
                allowEscapeKey: false,
                showCancelButton: false,
                showConfirmButton: false,
            }).then(() => {
                window.location.reload();
            });
        }
    });
};

// Check for adblocker on mount
onMounted(() => {
    // Delay adblocker detection to ensure page is fully loaded
    setTimeout(() => {
        if (!Session.hasPermission(Permissions.USER_PERMISSION_BYPASS_ADBLOCKER)) {
            detectAdblocker();
        }
    }, 1000);
});

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
const userBackground = ref('');

// Watch for session changes and user data updates
watch(
    () => Session.getInfo('background'),
    (newBackground) => {
        userBackground.value = newBackground || '';
    },
    { immediate: true },
);

// Also update when user data is reloaded
const updateUserBackground = () => {
    userBackground.value = Session.getInfo('background') || '';
};

// Call update function after user data reload
const reloadUserDataWithBackground = async () => {
    await reloadUserData();
    updateUserBackground();
};

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

            <!-- Adblocker Blocking Overlay -->
            <div
                v-if="isBlockedByAdblocker && isAdblockerEnabled"
                class="fixed inset-0 bg-black/90 backdrop-blur-sm z-50 flex items-center justify-center"
            >
                <div class="bg-gray-900/95 border border-red-500/30 rounded-lg p-8 max-w-md mx-4 text-center">
                    <div class="mb-6">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-16 w-16 mx-auto text-red-500 mb-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path
                                d="M18.364 18.364A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"
                            />
                        </svg>
                        <h2 class="text-2xl font-bold text-white mb-2">
                            {{ t('dashboard.alerts.adblocker_detected.title') }}
                        </h2>
                        <p class="text-gray-300 mb-6">
                            {{ t('dashboard.alerts.adblocker_detected.blocked_message') }}
                        </p>
                    </div>
                    <div class="space-y-3">
                        <button
                            @click="handleAdblockerDetected"
                            class="w-full bg-red-600 hover:bg-red-700 text-white font-medium py-3 px-4 rounded-lg transition-colors duration-200"
                        >
                            {{ t('dashboard.alerts.adblocker_detected.disable_adblocker') }}
                        </button>
                    </div>
                </div>
            </div>

            <template v-if="!loading && !isBlockedByAdblocker">
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
                <footer class="relative z-10 py-4 px-6 text-center text-sm text-gray-500">
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
