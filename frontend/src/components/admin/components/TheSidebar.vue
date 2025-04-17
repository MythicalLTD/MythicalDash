<template>
    <aside
        :class="[
            'fixed inset-y-0 left-0 z-40 w-72 transition-all duration-300 ease-in-out',
            isSidebarOpen ? 'translate-x-0' : '-translate-x-full',
            'lg:translate-x-0 bg-gradient-to-b from-[#0F1322] to-[#0A0E1A] border-r border-gray-800/20 shadow-xl',
        ]"
    >
        <!-- Logo section -->
        <div class="p-6">
            <div class="flex items-center">
                <div class="bg-gradient-to-r from-indigo-500 to-purple-600 p-2 rounded-xl shadow-lg mr-3">
                    <img src="https://github.com/mythicalltd.png" alt="Logo" class="h-8 w-8" />
                </div>
                <div>
                    <h1 class="text-xl font-bold text-white tracking-tight">
                        {{ settings.getSetting('debug_name') }}
                    </h1>
                    <div class="flex items-center">
                        <span class="text-xs text-indigo-300">v{{ settings.getSetting('debug_version') }}</span>
                        <span class="mx-2 text-gray-500">•</span>
                        <span class="text-xs px-2 py-0.5 bg-indigo-500/20 text-indigo-300 rounded-full">Admin</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <div class="mt-2">
            <nav class="px-3 py-2 overflow-y-auto max-h-[calc(100vh-140px)] hide-scrollbar">
                <SidebarMenuGroup
                    v-for="(menuGroup, index) in menuGroups"
                    :key="index"
                    :menu-group="menuGroup"
                    @toggle-submenu="$emit('toggleSubmenu', $event)"
                />
            </nav>
        </div>
    </aside>
</template>

<script setup lang="ts">
import SidebarMenuGroup from './SidebarMenuGroup.vue';
import type { MenuItem, MenuGroup, SettingsStore } from '../types';

defineProps<{
    isSidebarOpen: boolean;
    menuGroups: MenuGroup[];
    settings: SettingsStore;
}>();

defineEmits<{
    (e: 'toggleSubmenu', item: MenuItem): void;
}>();
</script>

<style scoped>
/* Custom scrollbar styles */
::-webkit-scrollbar {
    width: 6px;
}

::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.05);
}

::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.3);
}

.hide-scrollbar::-webkit-scrollbar {
    display: none;
}
.hide-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
