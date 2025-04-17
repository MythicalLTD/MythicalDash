<template>
    <div class="relative">
        <button
            @click="$emit('update:isProfileOpen', !isProfileOpen)"
            class="flex items-center gap-2 hover:bg-gray-800/50 rounded-xl px-3 py-2 transition-colors"
        >
            <img
                :src="session.getInfo('avatar')"
                alt="User Avatar"
                class="h-8 w-8 rounded-lg border-2 border-indigo-500/30 object-cover"
            />
            <div class="hidden md:block text-left">
                <span class="text-sm font-medium text-white">{{ session.getInfo('username') }}</span>
                <p class="text-xs text-gray-500">Administrator</p>
            </div>
            <ChevronDown class="w-4 h-4 text-gray-500" :class="{ 'rotate-180': isProfileOpen }" />
        </button>

        <!-- Profile Dropdown -->
        <div
            v-if="isProfileOpen"
            class="absolute right-0 mt-2 w-56 bg-[#0F1322] rounded-xl shadow-2xl border border-gray-800/30 py-1 animate-fadeIn"
        >
            <div class="px-4 py-3 border-b border-gray-800/30">
                <p class="text-sm font-medium text-white">{{ session.getInfo('username') }}</p>
                <p class="text-xs text-gray-500 mt-0.5">{{ session.getInfo('email') || 'admin@example.com' }}</p>
            </div>

            <div class="py-1">
                <RouterLink
                    v-for="item in profileMenu"
                    :key="item.name"
                    :to="item.path"
                    class="flex items-center px-4 py-2 text-sm hover:bg-white/5 transition-colors"
                >
                    <component :is="getIconForMenuItem(item.name)" class="w-4 h-4 mr-3 text-gray-500" />
                    <span class="text-gray-300">{{ item.name }}</span>
                </RouterLink>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ChevronDown, User, LogOut, Settings } from 'lucide-vue-next';
import type { ProfileMenuItem, SessionInfo } from '../types';

defineProps<{
    isProfileOpen: boolean;
    profileMenu: ProfileMenuItem[];
    session: SessionInfo;
}>();

defineEmits<{
    (e: 'update:isProfileOpen', value: boolean): void;
}>();

const getIconForMenuItem = (name: string) => {
    switch (name.toLowerCase()) {
        case 'profile':
            return User;
        case 'settings':
            return Settings;
        case 'sign out':
        case 'logout':
            return LogOut;
        default:
            return User;
    }
};
</script>

<style scoped>
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
</style>
