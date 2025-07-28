<template>
    <Transition :name="animationClass" mode="out-in">
        <div
            v-if="isOpen"
            :class="dropdownClasses"
            :style="[backgroundStyle, { position: 'fixed', top: '4rem', right: '1rem', zIndex: 1000 }]"
            @click.stop
        >
            <!-- Decorative header gradient -->
            <div
                v-if="notificationSettings.showBackground"
                class="absolute top-0 left-0 right-0 h-20 bg-gradient-to-br from-indigo-500/10 via-purple-500/5 to-blue-500/10 pointer-events-none"
            ></div>

            <!-- Header -->
            <div
                class="relative p-4 border-b border-[#2a2a3f]/30 flex items-center justify-between bg-gradient-to-br from-[#1a1a2e]/30 to-transparent"
            >
                <h3 class="font-semibold text-gray-200 flex items-center gap-2 drop-shadow-sm">
                    <div class="w-8 h-8 rounded-lg bg-indigo-500/10 flex items-center justify-center">
                        <BellIcon class="h-4 w-4 text-indigo-400" />
                    </div>
                    {{ t('components.notifications.title') }}
                </h3>
                <div class="flex items-center gap-2">
                    <button
                        class="text-xs text-indigo-400 hover:text-indigo-300 transition-all duration-200 hover:scale-105 px-2 py-1 rounded-md hover:bg-indigo-500/10"
                    >
                        {{ t('components.notifications.mark_all_read') }}
                    </button>
                </div>
            </div>

            <!-- Notifications List -->
            <div
                class="max-h-[350px] overflow-y-auto scrollbar-thin scrollbar-track-transparent scrollbar-thumb-[#2a2a3f]/50"
            >
                <div v-if="notifications.length > 0" class="divide-y divide-[#2a2a3f]/20">
                    <div
                        v-for="notification in notifications"
                        :key="notification.id"
                        class="flex items-start gap-3 p-4 hover:bg-gradient-to-r hover:from-[#1a1a2e]/40 hover:to-indigo-500/5 transition-all duration-200 cursor-pointer transform hover:translate-x-1 hover:scale-[1.01] group"
                        :class="notificationSettings.borderGlow ? 'hover:shadow-sm hover:shadow-indigo-500/10' : ''"
                    >
                        <div
                            class="w-10 h-10 rounded-lg bg-indigo-500/10 flex items-center justify-center shrink-0 transition-all duration-200 group-hover:bg-indigo-500/20 group-hover:scale-105 shadow-sm"
                        >
                            <component :is="notification.icon" class="h-5 w-5 text-indigo-400" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-200 group-hover:text-white transition-colors duration-200">
                                {{ notification.title }}
                            </p>
                            <p
                                class="text-sm text-gray-400 mt-0.5 group-hover:text-gray-300 transition-colors duration-200"
                            >
                                {{ notification.time }}
                            </p>
                        </div>
                        <div
                            class="w-2 h-2 rounded-full bg-indigo-500 mt-2 animate-pulse shadow-sm shadow-indigo-500/50"
                        ></div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="p-8 text-center">
                    <div class="flex flex-col items-center">
                        <div
                            class="w-16 h-16 rounded-full bg-gradient-to-br from-[#1a1a2e]/50 to-[#2a2a3f]/30 flex items-center justify-center mb-4 transition-all duration-200 hover:scale-105 shadow-lg"
                        >
                            <BellOffIcon class="h-8 w-8 text-gray-500" />
                        </div>
                        <p class="text-gray-300 font-medium mb-1">
                            {{ t('components.notifications.no_notifications') }}
                        </p>
                        <p class="text-sm text-gray-500">{{ t('components.notifications.check_back') }}</p>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div
                class="p-3 bg-gradient-to-r from-[#1a1a2e]/20 to-[#2a2a3f]/10 text-center border-t border-[#2a2a3f]/30"
            >
                <a
                    href="#"
                    class="text-sm text-indigo-400 hover:text-indigo-300 transition-all duration-200 hover:scale-105 inline-block px-3 py-1 rounded-md hover:bg-indigo-500/10"
                >
                    {{ t('components.notifications.view_all') }}
                </a>
            </div>
        </div>
    </Transition>
</template>

<script setup lang="ts">
import Session from '@/mythicaldash/Session';
import { AlertTriangle as AlertTriangleIcon, Bell as BellIcon, BellOff as BellOffIcon } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import { computed } from 'vue';
import { useSkinSettings } from '@/composables/useSkinSettings';

const { t } = useI18n();
const { notificationSettings } = useSkinSettings();

defineProps<{
    isOpen: boolean;
}>();

const notifications = [
    {
        id: 1,
        title: t('components.notifications.credits', { credits: Session.getInfo('credits') }),
        time: new Date().toLocaleTimeString(),
        icon: AlertTriangleIcon,
    },
];

// Computed styles based on settings
const dropdownClasses = computed(() => {
    const baseClasses =
        'absolute top-16 right-4 w-80 rounded-xl shadow-2xl z-50 overflow-hidden transition-all duration-200 ease-in-out';
    const backgroundClasses = notificationSettings.glassEffect
        ? `bg-[#0a0a0f]/${Math.round(notificationSettings.backgroundOpacity * 100)} backdrop-blur-md`
        : 'bg-[#0a0a0f]';
    const borderClasses = notificationSettings.borderGlow
        ? 'border border-[#2a2a3f]/30 hover:border-[#2a2a3f]/50 shadow-lg shadow-indigo-500/10'
        : 'border border-[#2a2a3f]/20';

    return [baseClasses, backgroundClasses, borderClasses].join(' ');
});

const animationClass = computed(() => {
    return `dropdown-${notificationSettings.animationStyle}`;
});

const backgroundStyle = computed(() => {
    if (!notificationSettings.showBackground) return {};

    return {
        backgroundImage: `linear-gradient(135deg, 
            rgba(99, 102, 241, 0.05) 0%, 
            rgba(168, 85, 247, 0.02) 50%, 
            rgba(59, 130, 246, 0.05) 100%),
            url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KICA8ZGVmcz4KICAgIDxwYXR0ZXJuIGlkPSJncmlkIiB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHBhdHRlcm5Vbml0cz0idXNlclNwYWNlT25Vc2UiPgogICAgICA8cGF0aCBkPSJNIDQwIDAgTCAwIDAgMCA0MCIgZmlsbD0ibm9uZSIgc3Ryb2tlPSJyZ2JhKDI1NSwgMjU1LCAyNTUsIDAuMDEpIiBzdHJva2Utd2lkdGg9IjEiLz4KICAgIDwvcGF0dGVybj4KICA8L2RlZnM+CiAgPHJlY3Qgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgZmlsbD0idXJsKCNncmlkKSIgLz4KPC9zdmc+')`,
    };
});
</script>

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

.hover\:scale-\[1\.01\]:hover {
    transform: scale(1.01);
}

/* Animation for notification dot */
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
</style>
