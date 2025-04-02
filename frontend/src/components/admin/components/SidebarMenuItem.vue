<template>
    <li>
        <!-- Menu item with submenu -->
        <template v-if="item.subMenu">
            <div
                @click="$emit('toggleSubmenu', item)"
                class="flex items-center px-3 py-2.5 rounded-xl transition-all duration-200 hover:bg-white/5 cursor-pointer group"
                :class="{ 'bg-gradient-to-r from-indigo-600/20 to-indigo-600/10 text-white': item.isOpen }"
            >
                <div
                    class="p-1.5 rounded-lg bg-gray-800/50 mr-3 group-hover:bg-indigo-500/20 transition-colors"
                    :class="{ 'bg-indigo-500/20': item.isOpen }"
                >
                    <component :is="item.icon" class="w-4 h-4 text-indigo-400" />
                </div>
                <span
                    class="text-sm font-medium text-gray-300 group-hover:text-white transition-colors"
                    :class="{ 'text-white': item.isOpen }"
                >
                    {{ item.name }}
                </span>
                <ChevronDown
                    class="w-4 h-4 ml-auto text-gray-500 transition-transform duration-200 group-hover:text-white"
                    :class="{ 'rotate-180 text-white': item.isOpen }"
                />
                <span
                    v-if="'count' in item"
                    class="ml-2 text-xs bg-indigo-500/20 text-indigo-300 px-2 py-0.5 rounded-full"
                >
                    {{ item.count }}
                </span>
            </div>

            <!-- Submenu items -->
            <div v-if="item.isOpen" class="mt-1 ml-4 space-y-1 animate-slideDown">
                <RouterLink
                    v-for="subItem in item.subMenu"
                    :key="subItem.name"
                    :to="subItem.path ?? ''"
                    class="flex items-center px-3 py-2 rounded-xl transition-all duration-200 hover:bg-white/5 text-sm group"
                    :class="{ 'bg-white/5 text-white': isActive(subItem.path ?? '') }"
                >
                    <div
                        class="p-1.5 rounded-lg bg-gray-800/50 mr-3 group-hover:bg-indigo-500/20 transition-colors"
                        :class="{ 'bg-indigo-500/20': isActive(subItem.path ?? '') }"
                    >
                        <component :is="subItem.icon" class="w-3.5 h-3.5 text-indigo-400" />
                    </div>
                    <span
                        class="text-gray-400 group-hover:text-white transition-colors"
                        :class="{ 'text-white': isActive(subItem.path ?? '') }"
                    >
                        {{ subItem.name }}
                    </span>
                </RouterLink>
            </div>
        </template>

        <!-- Regular menu item -->
        <RouterLink
            v-else
            :to="item.path || ''"
            class="flex items-center px-3 py-2.5 rounded-xl transition-all duration-200 hover:bg-white/5 text-sm group"
            :class="{ 'bg-gradient-to-r from-indigo-600/20 to-indigo-600/10 text-white': item.active }"
        >
            <div
                class="p-1.5 rounded-lg bg-gray-800/50 mr-3 group-hover:bg-indigo-500/20 transition-colors"
                :class="{ 'bg-indigo-500/20': item.active }"
            >
                <component :is="item.icon" class="w-4 h-4 text-indigo-400" />
            </div>
            <span
                class="text-sm font-medium text-gray-300 group-hover:text-white transition-colors"
                :class="{ 'text-white': item.active }"
            >
                {{ item.name }}
            </span>
            <span
                v-if="'count' in item"
                class="ml-auto text-xs bg-indigo-500/20 text-indigo-300 px-2 py-0.5 rounded-full"
            >
                {{ item.count }}
            </span>
        </RouterLink>
    </li>
</template>

<script setup lang="ts">
import { ChevronDown } from 'lucide-vue-next';
import { useRoute } from 'vue-router';
import type { MenuItem } from '../types';

defineProps<{
    item: MenuItem;
}>();

defineEmits<{
    (e: 'toggleSubmenu', item: MenuItem): void;
}>();

const route = useRoute();

const isActive = (path: string): boolean => {
    return route.path === path;
};
</script>

<style scoped>
.animate-slideDown {
    animation: slideDown 0.2s ease-out forwards;
    transform-origin: top;
    overflow: hidden;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: scaleY(0);
    }
    to {
        opacity: 1;
        transform: scaleY(1);
    }
}
</style>
