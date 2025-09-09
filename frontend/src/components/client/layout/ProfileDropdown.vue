<script setup lang="ts">
import { LogOutIcon } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import { useSkinSettings } from '@/composables/useSkinSettings';
import { optimizeImage } from '@/utils/performance';

interface Props {
    isOpen: boolean;
    profileMenu: Array<{
        name: string;
        icon: unknown;
        href: string;
    }>;
    userInfo: {
        roleName: string;
        roleColor: string;
        firstName: string;
        lastName: string;
        email: string;
        avatar: string;
        background: string;
    };
    stats: {
        tickets: string;
        coins: string;
        servers: string;
    };
}

const props = withDefaults(defineProps<Props>(), {
    stats: () => ({
        tickets: '0',
        coins: '0',
        servers: '0',
    }),
});
const { dropdownSettings } = useSkinSettings();

const handleLogout = () => {
    location.href = '/auth/logout';
};

// Computed styles based on settings
const dropdownClasses = computed(() => {
    const baseClasses =
        'absolute top-16 right-4 w-80 rounded-xl shadow-2xl z-50 overflow-hidden transition-all duration-200 ease-in-out';
    const backgroundClasses = dropdownSettings.glassEffect
        ? `bg-[#0a0a0f]/${Math.round(dropdownSettings.backgroundOpacity * 100)} backdrop-blur-${dropdownSettings.backdropBlur ? 'md' : 'none'}`
        : 'bg-[#0a0a0f]';
    const borderClasses = dropdownSettings.borderGlow
        ? 'border border-[#2a2a3f]/30 hover:border-[#2a2a3f]/50 shadow-lg shadow-indigo-500/10'
        : 'border border-[#2a2a3f]/20';
    const radiusClasses = `rounded-${dropdownSettings.borderRadius}`;

    return [baseClasses, backgroundClasses, borderClasses, radiusClasses].join(' ');
});

const animationClass = computed(() => {
    return `dropdown-${dropdownSettings.animationStyle}`;
});

const backgroundStyle = computed(() => {
    if (!dropdownSettings.showBackground) return {};

    return {
        backgroundImage: `linear-gradient(135deg, 
            rgba(99, 102, 241, 0.1) 0%, 
            rgba(168, 85, 247, 0.05) 50%, 
            rgba(59, 130, 246, 0.1) 100%),
            url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KICA8ZGVmcz4KICAgIDxwYXR0ZXJuIGlkPSJncmlkIiB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHBhdHRlcm5Vbml0cz0idXNlclNwYWNlT25Vc2UiPgogICAgICA8cGF0aCBkPSJNIDQwIDAgTCAwIDAgMCA0MCIgZmlsbD0ibm9uZSIgc3Ryb2tlPSJyZ2JhKDI1NSwgMjU1LCAyNTUsIDAuMDMpIiBzdHJva2Utd2lkdGg9IjEiLz4KICAgIDwvcGF0dGVybj4KICA8L2RlZnM+CiAgPHJlY3Qgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgZmlsbD0idXJsKCNncmlkKSIgLz4KPC9zdmc+')`,
    };
});

const userBackgroundStyle = computed(() => {
    if (!props.userInfo.background) return {};

    const optimizedBg = optimizeImage(props.userInfo.background, { width: 320, height: 128, quality: 80 });

    return {
        backgroundImage: `linear-gradient(135deg, rgba(0, 0, 0, 0.3) 0%, rgba(0, 0, 0, 0.1) 100%), url('${optimizedBg}')`,
        backgroundSize: 'cover',
        backgroundPosition: 'center',
        backgroundRepeat: 'no-repeat',
        boxShadow:
            '0 8px 25px rgba(0, 0, 0, 0.4), 0 4px 10px rgba(0, 0, 0, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.1)',
    };
});

const optimizedAvatar = computed(() => {
    return optimizeImage(props.userInfo.avatar, { width: 64, height: 64, quality: 85 });
});
</script>
<template>
    <Transition :name="animationClass" mode="out-in">
        <div v-if="isOpen" :class="dropdownClasses" :style="[backgroundStyle, { position: 'fixed' }]" @click.stop>
            <!-- Decorative header gradient -->
            <div
                v-if="dropdownSettings.showBackground"
                class="absolute top-0 left-0 right-0 h-32 bg-gradient-to-br from-indigo-500/10 via-purple-500/5 to-blue-500/10 pointer-events-none"
            ></div>

            <!-- User Profile Section -->
            <div
                class="relative p-5 border-b border-[#2a2a3f]/30 overflow-hidden"
                :style="
                    props.userInfo.background && dropdownSettings.showUserBackground
                        ? userBackgroundStyle
                        : { background: 'linear-gradient(to bottom right, rgba(26, 26, 46, 0.5), transparent)' }
                "
            >
                <!-- Background overlay for better text readability -->
                <div
                    v-if="props.userInfo.background && dropdownSettings.showUserBackground"
                    class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-black/50"
                ></div>
                <div class="flex items-center gap-4 relative z-10">
                    <div class="relative group">
                        <div
                            class="h-16 w-16 rounded-xl overflow-hidden transition-all duration-300 group-hover:scale-105 ring-2 ring-white/30 shadow-2xl"
                            :class="
                                dropdownSettings.borderGlow ? 'shadow-2xl shadow-black/50' : 'shadow-xl shadow-black/30'
                            "
                        >
                            <img :src="optimizedAvatar" alt="User Avatar" class="h-full w-full object-cover" />
                        </div>
                        <div
                            class="absolute -bottom-1 -right-1 h-5 w-5 rounded-full bg-gradient-to-r from-green-400 to-emerald-500 ring-3 ring-white shadow-lg animate-pulse"
                        ></div>
                    </div>
                    <div class="flex-1 min-w-0 relative z-10">
                        <h3 class="font-semibold text-white truncate text-lg drop-shadow-lg">
                            {{ userInfo.firstName }} {{ userInfo.lastName }}
                        </h3>
                        <p class="text-sm text-white/80 truncate mb-3 drop-shadow-md">{{ userInfo.email }}</p>

                        <div
                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-black/30 backdrop-blur-sm text-white border border-white/20 shadow-lg"
                            :style="{ backgroundColor: userInfo.roleColor }"
                        >
                            <span class="w-2 h-2 rounded-full bg-white mr-2 animate-pulse"></span>
                            {{ userInfo.roleName }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Menu Items -->
            <div class="p-3 space-y-1">
                <RouterLink
                    v-for="item in profileMenu"
                    :key="item.name"
                    :to="item.href"
                    class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gradient-to-r hover:from-[#1a1a2e]/70 hover:to-indigo-500/10 transition-all duration-200 group transform hover:translate-x-1 hover:scale-[1.02]"
                    :class="dropdownSettings.borderGlow ? 'hover:shadow-md hover:shadow-indigo-500/10' : ''"
                >
                    <div
                        class="w-10 h-10 rounded-lg bg-gradient-to-br from-[#1a1a2e]/50 to-[#2a2a3f]/30 flex items-center justify-center group-hover:from-indigo-500/10 group-hover:to-purple-500/10 transition-all duration-200 border border-[#2a2a3f]/20 group-hover:border-indigo-500/30"
                    >
                        <component
                            :is="item.icon"
                            class="h-5 w-5 text-gray-400 group-hover:text-indigo-400 transition-colors duration-200"
                        />
                    </div>
                    <span class="text-sm font-medium text-gray-300 group-hover:text-gray-100">{{ item.name }}</span>
                    <div class="ml-auto opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                        <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 5l7 7-7 7"
                            ></path>
                        </svg>
                    </div>
                </RouterLink>

                <!-- Logout Button -->
                <button
                    @click="handleLogout"
                    class="w-full flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gradient-to-r hover:from-red-500/10 hover:to-red-600/10 text-gray-300 hover:text-red-400 transition-all duration-200 group mt-3 transform hover:translate-x-1 hover:scale-[1.02] border border-transparent hover:border-red-500/20"
                    :class="dropdownSettings.borderGlow ? 'hover:shadow-md hover:shadow-red-500/10' : ''"
                >
                    <div
                        class="w-10 h-10 rounded-lg bg-gradient-to-br from-[#1a1a2e]/50 to-[#2a2a3f]/30 flex items-center justify-center group-hover:from-red-500/10 group-hover:to-red-600/10 transition-all duration-200 border border-[#2a2a3f]/20 group-hover:border-red-500/30"
                    >
                        <LogOutIcon
                            class="h-5 w-5 text-gray-400 group-hover:text-red-400 transition-colors duration-200"
                        />
                    </div>
                    <span class="text-sm font-medium">{{ $t('components.profile.logout') }}</span>
                    <div class="ml-auto opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                        <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 5l7 7-7 7"
                            ></path>
                        </svg>
                    </div>
                </button>
            </div>

            <!-- Footer -->
            <div
                class="p-4 bg-gradient-to-r from-[#1a1a2e]/30 to-[#2a2a3f]/20 text-center text-xs text-gray-500 border-t border-[#2a2a3f]/30"
            >
                <p class="flex items-center justify-center gap-2">
                    <span>Made with</span>
                    <span class="text-red-400 animate-pulse">❤️</span>
                    <span>by</span>
                    <a
                        href="https://mythical.systems"
                        target="_blank"
                        class="text-indigo-400 hover:text-indigo-300 transition-colors duration-200 font-medium hover:underline"
                        >MythicalSystems</a
                    >
                </p>
            </div>
        </div>
    </Transition>
</template>
<style scoped>
/* Fade Animation */
.dropdown-fade-enter-active,
.dropdown-fade-leave-active {
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.dropdown-fade-enter-from,
.dropdown-fade-leave-to {
    opacity: 0;
    transform: translateY(-12px) scale(0.96);
}

.dropdown-fade-enter-to,
.dropdown-fade-leave-from {
    opacity: 1;
    transform: translateY(0) scale(1);
}

/* Slide Animation */
.dropdown-slide-enter-active,
.dropdown-slide-leave-active {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.dropdown-slide-enter-from,
.dropdown-slide-leave-to {
    opacity: 0;
    transform: translateX(20px) translateY(-10px);
}

.dropdown-slide-enter-to,
.dropdown-slide-leave-from {
    opacity: 1;
    transform: translateX(0) translateY(0);
}

/* Scale Animation */
.dropdown-scale-enter-active,
.dropdown-scale-leave-active {
    transition: all 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.dropdown-scale-enter-from,
.dropdown-scale-leave-to {
    opacity: 0;
    transform: scale(0.8) translateY(-20px);
}

.dropdown-scale-enter-to,
.dropdown-scale-leave-from {
    opacity: 1;
    transform: scale(1) translateY(0);
}

/* Bounce Animation */
.dropdown-bounce-enter-active {
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.dropdown-bounce-leave-active {
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.dropdown-bounce-enter-from,
.dropdown-bounce-leave-to {
    opacity: 0;
    transform: scale(0.3) translateY(-30px);
}

.dropdown-bounce-enter-to,
.dropdown-bounce-leave-from {
    opacity: 1;
    transform: scale(1) translateY(0);
}

/* Smooth transitions */
.transition-all {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 200ms;
}

/* Hover effects */
.hover\:scale-105:hover {
    transform: scale(1.05);
}

.hover\:translate-x-1:hover {
    transform: translateX(0.25rem);
}

.hover\:scale-\[1\.02\]:hover {
    transform: scale(1.02);
}

/* Animation for online status */
@keyframes pulse {
    0%,
    100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Custom scrollbar for menu items */
.space-y-1::-webkit-scrollbar {
    width: 4px;
}

.space-y-1::-webkit-scrollbar-track {
    background: rgba(42, 42, 63, 0.1);
    border-radius: 2px;
}

.space-y-1::-webkit-scrollbar-thumb {
    background: rgba(99, 102, 241, 0.3);
    border-radius: 2px;
}

.space-y-1::-webkit-scrollbar-thumb:hover {
    background: rgba(99, 102, 241, 0.5);
}
</style>
