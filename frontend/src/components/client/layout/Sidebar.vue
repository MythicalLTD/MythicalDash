<script setup lang="ts">
import { ref } from 'vue';
import {
    AlertTriangleIcon,
    BellIcon,
    ChevronDown as ChevronDownIcon,
    LayoutDashboardIcon,
    ServerIcon,
    TicketIcon,
} from 'lucide-vue-next';
import Translation from '@/mythicaldash/Translation';

defineProps<{
    isSidebarOpen: boolean;
}>();

const isActiveRoute = (routes: string | string[]) => {
    return routes.includes(window.location.pathname);
};

interface MenuItem {
    name: string;
    icon: typeof ServerIcon;
    href: string;
    active: boolean;
    expanded?: boolean;
    subitems?: MenuItem[];
    tooltip?: string;
}

interface MenuSection {
    title: string;
    items: MenuItem[];
}

const menuSections = ref<MenuSection[]>([
    {
        title: 'General',
        items: [
            {
                name: Translation.getTranslation('components.sidebar.dashboard'),
                icon: LayoutDashboardIcon,
                href: '/',
                active: isActiveRoute(['/dashboard']),
            },
        ],
    },
    {
        title: Translation.getTranslation('components.sidebar.support'),
        items: [
            {
                name: Translation.getTranslation('components.sidebar.announcements'),
                icon: BellIcon,
                href: '/announcements',
                active: isActiveRoute(['/announcements']),
            },
            {
                name: Translation.getTranslation('components.sidebar.tickets'),
                icon: TicketIcon,
                href: '/ticket',
                active: isActiveRoute(['/ticket']),
                expanded: false,
                subitems: [
                    {
                        name: Translation.getTranslation('components.sidebar.open_ticket'),
                        icon: AlertTriangleIcon,
                        href: '/ticket/create',
                        active: isActiveRoute(['/ticket/create']),
                    },
                    {
                        name: Translation.getTranslation('components.sidebar.all_tickets'),
                        icon: TicketIcon,
                        href: '/ticket',
                        active: isActiveRoute(['/ticket']),
                    },
                ],
            },
        ],
    },
]);

const toggleSubitems = (item: MenuItem) => {
    item.expanded = !item.expanded;
};
</script>
<template>
    <aside
        class="fixed top-0 left-0 h-full w-64 bg-[#0a0a0f]/95 backdrop-blur-md border-r border-[#2a2a3f]/30 transform transition-transform duration-200 ease-in-out z-50 lg:translate-x-0 lg:z-20"
        :class="isSidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    >
        <!-- Sidebar Content -->
        <div class="flex flex-col h-full pt-16">
            <div class="flex-1 overflow-y-auto scrollbar-thin scrollbar-track-[#0a0a0f] scrollbar-thumb-[#2a2a3f]/50">
                <nav class="p-4">
                    <div v-for="(section, index) in menuSections" :key="index" class="mb-6">
                        <div class="text-xs uppercase tracking-wider text-gray-500 font-medium px-4 mb-2">
                            {{ section.title }}
                        </div>
                        <div class="space-y-1">
                            <template v-for="item in section.items" :key="item.name">
                                <div v-if="item.subitems">
                                    <button
                                        @click="toggleSubitems(item)"
                                        class="w-full flex items-center justify-between gap-3 px-4 py-2 rounded-lg hover:bg-[#1a1a2e]/50 transition-colors duration-200"
                                        :class="{ 'bg-indigo-500/10 text-indigo-400': item.active }"
                                    >
                                        <div class="flex items-center gap-3">
                                            <component :is="item.icon" class="w-5 h-5" />
                                            {{ item.name }}
                                        </div>
                                        <ChevronDownIcon
                                            class="w-4 h-4 transition-transform duration-200"
                                            :class="{ 'rotate-180': item.expanded }"
                                        />
                                    </button>
                                    <transition
                                        enter-active-class="transition-all duration-300 ease-in-out"
                                        leave-active-class="transition-all duration-300 ease-in-out"
                                        enter-from-class="opacity-0 max-h-0"
                                        enter-to-class="opacity-100 max-h-[500px]"
                                        leave-from-class="opacity-100 max-h-[500px]"
                                        leave-to-class="opacity-0 max-h-0"
                                    >
                                        <div v-show="item.expanded" class="ml-4 mt-1 space-y-1 overflow-hidden">
                                            <template v-for="subitem in item.subitems" :key="subitem.name">
                                                <!-- Regular subitem -->
                                                <div>
                                                    <div
                                                        v-if="subitem.subitems && subitem.subitems.length"
                                                        class="w-full"
                                                    >
                                                        <button
                                                            @click="toggleSubitems(subitem)"
                                                            class="w-full flex items-center justify-between gap-3 px-4 py-2 rounded-lg hover:bg-[#1a1a2e]/50 transition-colors duration-200 text-sm"
                                                            :class="{
                                                                'bg-indigo-500/10 text-indigo-400': subitem.active,
                                                            }"
                                                        >
                                                            <div class="flex items-center gap-3">
                                                                <component :is="subitem.icon" class="w-4 h-4" />
                                                                {{ subitem.name }}
                                                            </div>
                                                            <ChevronDownIcon
                                                                v-if="subitem.subitems.length"
                                                                class="w-4 h-4 transition-transform duration-200"
                                                                :class="{ 'rotate-180': subitem.expanded }"
                                                            />
                                                        </button>

                                                        <!-- Nested categories -->
                                                        <transition
                                                            enter-active-class="transition-all duration-300 ease-in-out"
                                                            leave-active-class="transition-all duration-300 ease-in-out"
                                                            enter-from-class="opacity-0 max-h-0"
                                                            enter-to-class="opacity-100 max-h-[500px]"
                                                            leave-from-class="opacity-100 max-h-[500px]"
                                                            leave-to-class="opacity-0 max-h-0"
                                                        >
                                                            <div
                                                                v-if="subitem.subitems.length"
                                                                v-show="subitem.expanded"
                                                                class="ml-4 mt-1 space-y-1 overflow-hidden"
                                                            >
                                                                <RouterLink
                                                                    v-for="category in subitem.subitems || []"
                                                                    :key="category.name"
                                                                    :to="category.href"
                                                                    class="group relative flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-[#1a1a2e]/50 transition-colors duration-200 text-sm"
                                                                    :class="{
                                                                        'bg-indigo-500/10 text-indigo-400':
                                                                            category.active,
                                                                    }"
                                                                >
                                                                    <component :is="category.icon" class="w-4 h-4" />
                                                                    {{ category.name }}

                                                                    <!-- Tooltip -->
                                                                    <div
                                                                        v-if="category.tooltip"
                                                                        class="absolute left-full ml-2 px-3 py-1.5 bg-[#1a1a2e] border border-[#2a2a3f]/30 text-white text-xs rounded-md whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 z-50 pointer-events-none backdrop-blur-md"
                                                                    >
                                                                        {{ category.tooltip }}
                                                                    </div>
                                                                </RouterLink>
                                                            </div>
                                                        </transition>
                                                    </div>

                                                    <RouterLink
                                                        v-else
                                                        :to="subitem.href"
                                                        class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-[#1a1a2e]/50 transition-colors duration-200 text-sm"
                                                        :class="{ 'bg-indigo-500/10 text-indigo-400': subitem.active }"
                                                    >
                                                        <component :is="subitem.icon" class="w-4 h-4" />
                                                        {{ subitem.name }}
                                                    </RouterLink>
                                                </div>
                                            </template>
                                        </div>
                                    </transition>
                                </div>
                                <RouterLink
                                    v-else
                                    :to="item.href"
                                    class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-[#1a1a2e]/50 transition-colors duration-200"
                                    :class="{ 'bg-indigo-500/10 text-indigo-400': item.active }"
                                >
                                    <component :is="item.icon" class="w-5 h-5" />
                                    {{ item.name }}
                                </RouterLink>
                            </template>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
    </aside>
</template>

<style scoped>
.rotate-180 {
    transform: rotate(180deg);
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

/* Tooltip z-index handling */
.group:hover {
    z-index: 100;
}

/* Smooth transitions */
.transition-colors {
    transition-property: background-color, border-color, color, fill, stroke;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 200ms;
}
</style>
