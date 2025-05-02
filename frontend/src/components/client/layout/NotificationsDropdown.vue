<template>
    <Transition name="dropdown">
        <div
            v-if="isOpen"
            class="absolute top-16 right-4 w-80 bg-[#0a0a0f]/95 backdrop-blur-md border border-[#2a2a3f]/30 rounded-xl shadow-2xl z-50 overflow-hidden hover:border-[#2a2a3f]/50 transition-all duration-200"
            @click.stop
        >
            <!-- Header -->
            <div
                class="p-4 border-b border-[#2a2a3f]/30 flex items-center justify-between bg-gradient-to-br from-[#1a1a2e]/50 to-transparent"
            >
                <h3 class="font-medium text-gray-200 flex items-center gap-2">
                    <BellIcon class="h-4 w-4 text-indigo-400" />
                    {{ t('components.notifications.title') }}
                </h3>
                <div class="flex items-center gap-2">
                    <button
                        class="text-xs text-indigo-400 hover:text-indigo-300 transition-all duration-200 hover:scale-105"
                    >
                        {{ t('components.notifications.mark_all_read') }}
                    </button>
                </div>
            </div>

            <!-- Notifications List -->
            <div
                class="max-h-[350px] overflow-y-auto scrollbar-thin scrollbar-track-[#0a0a0f] scrollbar-thumb-[#2a2a3f]/50"
            >
                <div v-if="notifications.length > 0" class="divide-y divide-[#2a2a3f]/30">
                    <div
                        v-for="notification in notifications"
                        :key="notification.id"
                        class="flex items-start gap-3 p-4 hover:bg-[#1a1a2e]/50 transition-all duration-200 cursor-pointer transform hover:translate-x-1"
                    >
                        <div
                            class="w-10 h-10 rounded-lg bg-indigo-500/10 flex items-center justify-center shrink-0 transition-all duration-200 group-hover:bg-indigo-500/20"
                        >
                            <component :is="notification.icon" class="h-5 w-5 text-indigo-400" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-200">{{ notification.title }}</p>
                            <p class="text-sm text-gray-400 mt-0.5">{{ notification.time }}</p>
                        </div>
                        <div class="w-2 h-2 rounded-full bg-indigo-500 mt-2 animate-pulse"></div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="p-8 text-center">
                    <div class="flex flex-col items-center">
                        <div
                            class="w-16 h-16 rounded-full bg-[#1a1a2e]/50 flex items-center justify-center mb-4 transition-all duration-200 hover:bg-[#1a1a2e]/70"
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
            <div class="p-3 bg-[#1a1a2e]/30 text-center border-t border-[#2a2a3f]/30">
                <a
                    href="#"
                    class="text-sm text-indigo-400 hover:text-indigo-300 transition-all duration-200 hover:scale-105 inline-block"
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
const { t } = useI18n();

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
</script>

<style scoped>
.dropdown-enter-active,
.dropdown-leave-active {
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.dropdown-enter-from,
.dropdown-leave-to {
    opacity: 0;
    transform: translateY(-12px) scale(0.96);
}

.dropdown-enter-to,
.dropdown-leave-from {
    opacity: 1;
    transform: translateY(0) scale(1);
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
