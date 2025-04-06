<template>
    <LayoutDashboard>
        <div class="space-y-6">
            <!-- Welcome Header with Layout Controls -->
            <div
                class="flex flex-col md:flex-row md:items-center md:justify-between bg-gradient-to-r from-gray-900/70 to-gray-800/50 backdrop-blur-md rounded-xl p-6 border border-gray-800/30"
            >
                <div>
                    <h1 class="text-3xl font-bold mb-2">Welcome to {{ Settings.getSetting('debug_name') }}!</h1>
                    <p class="text-gray-400">Version: {{ Settings.getSetting('debug_version') }}</p>
                </div>
                <div class="mt-4 md:mt-0 flex flex-wrap gap-3">
                    <button
                        @click="refreshData"
                        class="flex items-center gap-2 px-4 py-2 bg-gray-800/50 hover:bg-gray-700/50 border border-gray-700/30 rounded-lg transition-all duration-200"
                        :class="{ 'opacity-50 cursor-wait': isRefreshing }"
                    >
                        <RefreshCcw v-if="!isRefreshing" class="w-4 h-4 text-gray-400" />
                        <Loader v-else class="w-4 h-4 text-gray-400 animate-spin" />
                        <span class="text-sm text-gray-300">Refresh</span>
                    </button>
                    <button
                        @click="toggleLayoutMode"
                        class="flex items-center gap-2 px-4 py-2 bg-gray-800/50 hover:bg-gray-700/50 border border-gray-700/30 rounded-lg transition-all duration-200"
                        :class="{ 'bg-indigo-600/30 border-indigo-500/30': layoutActive }"
                    >
                        <GripVertical class="w-4 h-4" :class="layoutActive ? 'text-indigo-400' : 'text-gray-400'" />
                        <span class="text-sm" :class="layoutActive ? 'text-indigo-300' : 'text-gray-300'">
                            {{ layoutActive ? 'Layout Mode' : 'Customize Layout' }}
                        </span>
                    </button>
                    <RouterLink
                        to="/mc-admin/settings"
                        class="flex items-center gap-2 px-4 py-2 bg-indigo-600/20 hover:bg-indigo-600/30 border border-indigo-700/30 rounded-lg transition-all duration-200"
                    >
                        <SettingsIcon2 class="w-4 h-4 text-indigo-400" />
                        <span class="text-sm text-gray-300">Settings</span>
                    </RouterLink>
                </div>
            </div>

            <!-- Layout Controls (only visible in layout mode) -->
            <div
                v-if="layoutActive"
                class="bg-indigo-900/30 backdrop-blur-md rounded-xl p-4 border border-indigo-800/30 animate-fadeIn"
            >
                <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                    <div>
                        <h2 class="text-lg font-medium text-white mb-1">Layout Customization Mode</h2>
                        <p class="text-indigo-200/80 text-sm">
                            Drag widgets to reposition them or resize by dragging the corners.
                        </p>
                    </div>
                    <div class="flex gap-3">
                        <button
                            @click="saveWidgetLayout"
                            class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors duration-200"
                        >
                            <CheckCircle class="w-4 h-4" />
                            <span>Save Layout</span>
                        </button>
                        <button
                            @click="resetWidgetLayout"
                            class="flex items-center gap-2 px-4 py-2 bg-transparent hover:bg-white/10 text-white border border-white/30 rounded-lg transition-colors duration-200"
                        >
                            <RefreshCcw class="w-4 h-4" />
                            <span>Reset to Default</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Dashboard Grid with Widgets -->
            <div class="grid grid-cols-12 gap-4 relative dashboard-grid" :class="{ 'layout-active': layoutActive }">
                <!-- Widget for Stats Cards -->
                <div
                    v-for="widget in widgets"
                    :key="widget.id"
                    class="dashboard-widget transition-all duration-300"
                    :class="{
                        'col-span-12': widget.cols === 12,
                        'col-span-6': widget.cols === 6,
                        'col-span-4': widget.cols === 4,
                        'col-span-3': widget.cols === 3,
                        'dashboard-widget-draggable': layoutActive,
                        'dashboard-widget-dragging': isDragging && layoutActive,
                        'ring-2 ring-indigo-500/50 shadow-lg': layoutActive,
                    }"
                    :style="{
                        order: widget.y * 12 + widget.x,
                        gridColumnStart: widget.x + 1,
                        gridColumnEnd: widget.x + widget.cols + 1,
                    }"
                    @mousedown="layoutActive && onDragStart(widget.id, $event)"
                    @mouseup="layoutActive && onDragEnd()"
                    @mousemove="layoutActive && isDragging && onDragMove($event)"
                >
                    <!-- Widget Header -->
                    <div
                        class="dashboard-widget-header bg-gray-800/80 rounded-t-xl p-3 border border-gray-800/30 flex items-center justify-between"
                    >
                        <h3 class="font-medium text-gray-100">{{ widget.title }}</h3>
                        <div class="flex items-center gap-2">
                            <!-- Widget Controls (only visible in layout mode) -->
                            <div v-if="layoutActive" class="flex items-center mr-2">
                                <button
                                    class="p-1 hover:bg-gray-700/50 rounded-md text-gray-400 hover:text-gray-300 transition-colors duration-200"
                                    @click="() => onWidgetResize(widget.id, widget.cols === 12 ? 6 : 12, widget.rows)"
                                >
                                    <Maximize2 v-if="widget.cols < 12" class="w-4 h-4" />
                                    <Minimize2 v-else class="w-4 h-4" />
                                </button>
                            </div>
                            <button
                                class="p-1 hover:bg-gray-700/50 rounded-md text-gray-400 hover:text-gray-300 transition-colors duration-200"
                                @click="toggleWidgetCollapse(widget.id)"
                            >
                                <Minimize2 v-if="!widget.collapsed" class="w-4 h-4" />
                                <Maximize2 v-else class="w-4 h-4" />
                            </button>
                        </div>
                    </div>

                    <!-- Widget Content -->
                    <div
                        v-if="!widget.collapsed"
                        class="dashboard-widget-content bg-gray-800/50 rounded-b-xl border-x border-b border-gray-800/30 p-4"
                    >
                        <!-- Stats Overview -->
                        <div v-if="widget.id === 'stats'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div
                                v-for="(stat, index) in statsCards"
                                :key="index"
                                class="bg-gray-800/80 backdrop-blur-md rounded-xl p-5 border border-gray-800/50 hover:border-indigo-500/30 transition-all duration-300"
                            >
                                <div class="flex items-center gap-3 mb-3">
                                    <div
                                        class="w-10 h-10 rounded-lg flex items-center justify-center"
                                        :class="stat.iconBg"
                                    >
                                        <component :is="stat.icon" class="w-5 h-5" :class="stat.iconColor" />
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-medium text-gray-400">{{ stat.title }}</h3>
                                        <p class="text-xl font-bold" :class="stat.valueColor">{{ stat.value }}</p>
                                    </div>
                                </div>
                                <div class="mt-3 pt-3 border-t border-gray-700/20 flex items-center justify-between">
                                    <span class="text-xs text-gray-500">{{ stat.description }}</span>
                                    <RouterLink
                                        :to="stat.link"
                                        class="text-xs text-indigo-400 hover:text-indigo-300 transition-colors flex items-center gap-1"
                                    >
                                        View All
                                        <ArrowRight class="w-3 h-3" />
                                    </RouterLink>
                                </div>
                            </div>
                        </div>

                        <!-- System Updates -->
                        <div v-if="widget.id === 'system-updates'">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <p class="text-gray-300 font-medium">Current Version</p>
                                    <p class="text-green-400 font-semibold">
                                        {{ Settings.getSetting('debug_version') }}
                                    </p>
                                </div>
                                <div class="bg-green-500/10 text-green-400 rounded-full px-3 py-1 text-xs">
                                    Up to date
                                </div>
                            </div>
                            <p class="text-gray-400 text-sm">
                                Your system is running the latest version. The last update check was performed today.
                            </p>
                            <button
                                class="w-full mt-4 py-2.5 bg-gray-700/50 hover:bg-gray-700/70 border border-gray-700/30 rounded-lg transition-all duration-200 text-sm text-gray-300"
                            >
                                Check for Updates
                            </button>
                        </div>

                        <!-- Support & Resources -->
                        <div v-if="widget.id === 'support-resources'" class="divide-y divide-gray-700/30">
                            <a
                                v-for="(resource, index) in supportResources"
                                :key="index"
                                :href="resource.link"
                                target="_blank"
                                class="flex items-center justify-between py-3 first:pt-0 hover:bg-gray-700/20 transition-colors duration-200 px-2 rounded-lg"
                            >
                                <div class="flex items-center">
                                    <component :is="resource.icon" class="w-5 h-5 mr-3" :class="resource.iconColor" />
                                    <span class="text-sm text-gray-300">{{ resource.title }}</span>
                                </div>
                                <ExternalLink class="w-4 h-4 text-gray-500" />
                            </a>
                        </div>

                        <!-- Premium Upgrade -->
                        <div v-if="widget.id === 'premium-upgrade'">
                            <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between">
                                <div class="mb-4 md:mb-0">
                                    <h2 class="text-xl font-bold text-white mb-2">Upgrade to Premium Edition</h2>
                                    <p class="text-indigo-200/90 max-w-2xl">
                                        Get access to additional features, priority support, and advanced customization
                                        options.
                                    </p>
                                </div>
                                <div class="flex gap-3">
                                    <a
                                        href="https://www.mythical.systems/premium"
                                        target="_blank"
                                        class="flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow-lg shadow-indigo-900/30 transition-all duration-200"
                                    >
                                        <Sparkles class="w-4 h-4" />
                                        <span>Upgrade Now</span>
                                    </a>
                                    <button
                                        class="px-5 py-2.5 bg-transparent hover:bg-white/10 text-white border border-white/30 rounded-lg transition-all duration-200"
                                    >
                                        Learn More
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, reactive } from 'vue';
import {
    CheckCircle,
    Heart,
    Users,
    Ticket,
    Server,
    Bell,
    RefreshCcw,
    Loader,
    ArrowRight,
    ExternalLink,
    BookOpen,
    MessageCircle,
    Github,
    Sparkles,
    GripVertical,
    Maximize2,
    Minimize2,
    Settings as SettingsIcon2,
} from 'lucide-vue-next';
import LayoutDashboard from '@/components/admin/LayoutDashboard.vue';
import { useSettingsStore } from '@/stores/settings';
import Dashboard from '@/mythicaldash/admin/Dashboard';
import { RouterLink } from 'vue-router';

const Settings = useSettingsStore();
const isRefreshing = ref(false);
const dashboardData = ref({
    counts: {
        user_count: 0,
        locations_count: 0,
        tickets_count: 0,
        eggs_count: 0,
        departments_count: 0,
        announcements_count: 0,
        server_queue_count: 0,
        mail_templates_count: 0,
        settings_count: 0,
    },
});

// Widget interface
interface Widget {
    id: string;
    title: string;
    cols: number;
    rows: number;
    x: number;
    y: number;
    minRows: number;
    minCols: number;
    draggable: boolean;
    resizable: boolean;
    component: string;
    collapsed: boolean;
}

// Widgets state
const widgets = reactive<Widget[]>([
    {
        id: 'stats',
        title: 'Stats Overview',
        cols: 12,
        rows: 1,
        x: 0,
        y: 0,
        minRows: 1,
        minCols: 6,
        draggable: true,
        resizable: true,
        component: 'stats-overview',
        collapsed: false,
    },
    {
        id: 'system-updates',
        title: 'System Updates',
        cols: 4,
        rows: 2,
        x: 0,
        y: 1,
        minRows: 2,
        minCols: 3,
        draggable: true,
        resizable: true,
        component: 'system-updates',
        collapsed: false,
    },
    {
        id: 'support-resources',
        title: 'Support & Resources',
        cols: 4,
        rows: 3,
        x: 4,
        y: 1,
        minRows: 2,
        minCols: 3,
        draggable: true,
        resizable: true,
        component: 'support-resources',
        collapsed: false,
    },
    {
        id: 'premium-upgrade',
        title: 'Premium Upgrade',
        cols: 12,
        rows: 1,
        x: 0,
        y: 4,
        minRows: 1,
        minCols: 6,
        draggable: true,
        resizable: true,
        component: 'premium-upgrade',
        collapsed: false,
    },
]);

const layoutActive = ref(false);
const isDragging = ref(false);
const activeWidget = ref<string | null>(null);
const dragStart = reactive({
    x: 0,
    y: 0,
    gridX: 0,
    gridY: 0,
});

const toggleWidgetCollapse = (id: string): void => {
    const widget = widgets.find((w) => w.id === id);
    if (widget) {
        widget.collapsed = !widget.collapsed;
    }
};

const toggleLayoutMode = (): void => {
    layoutActive.value = !layoutActive.value;
};

const saveWidgetLayout = (): void => {
    // Here you would save the layout to user preferences/localStorage
    localStorage.setItem('dashboardLayout', JSON.stringify(widgets));
    layoutActive.value = false;
};

const resetWidgetLayout = (): void => {
    // Reset to default layout
    // In a real implementation, you would restore from defaults
    layoutActive.value = false;
    // Reload the page to restore defaults
    window.location.reload();
};

// Load dashboard data
onMounted(async () => {
    try {
        // Check if there's a saved layout
        const savedLayout = localStorage.getItem('dashboardLayout');
        if (savedLayout) {
            const parsedLayout = JSON.parse(savedLayout);
            // Update positions but keep the widget definitions
            widgets.forEach((widget) => {
                const saved = parsedLayout.find((w: Widget) => w.id === widget.id);
                if (saved) {
                    widget.x = saved.x;
                    widget.y = saved.y;
                    widget.cols = saved.cols;
                    widget.rows = saved.rows;
                    widget.collapsed = saved.collapsed || false;
                }
            });
        }

        const data = await Dashboard.get();
        dashboardData.value = {
            counts: data.count || {
                user_count: 0,
                locations_count: 0,
                tickets_count: 0,
                eggs_count: 0,
                departments_count: 0,
                announcements_count: 0,
                server_queue_count: 0,
                mail_templates_count: 0,
                settings_count: 0,
            },
        };
    } catch (error) {
        console.error('Failed to load dashboard data:', error);
    }
});

// Refresh data
const refreshData = async () => {
    isRefreshing.value = true;
    try {
        const data = await Dashboard.get();
        dashboardData.value = {
            counts: data.count || dashboardData.value.counts,
        };
    } catch (error) {
        console.error('Failed to refresh dashboard data:', error);
    } finally {
        isRefreshing.value = false;
    }
};

// Stats cards
const statsCards = computed(() => [
    {
        title: 'Total Users',
        value: dashboardData.value.counts.user_count,
        icon: Users,
        iconBg: 'bg-blue-500/20',
        iconColor: 'text-blue-400',
        valueColor: 'text-blue-400',
        description: 'Active users on the platform',
        link: '/mc-admin/users',
    },
    {
        title: 'Open Tickets',
        value: dashboardData.value.counts.tickets_count,
        icon: Ticket,
        iconBg: 'bg-amber-500/20',
        iconColor: 'text-amber-400',
        valueColor: 'text-amber-400',
        description: 'Tickets requiring attention',
        link: '/mc-admin/tickets',
    },
    {
        title: 'Servers',
        value: dashboardData.value.counts.locations_count,
        icon: Server,
        iconBg: 'bg-emerald-500/20',
        iconColor: 'text-emerald-400',
        valueColor: 'text-emerald-400',
        description: 'Active server locations',
        link: '/mc-admin/locations',
    },
    {
        title: 'Announcements',
        value: dashboardData.value.counts.announcements_count,
        icon: Bell,
        iconBg: 'bg-purple-500/20',
        iconColor: 'text-purple-400',
        valueColor: 'text-purple-400',
        description: 'Published announcements',
        link: '/mc-admin/announcements',
    },
]);

// Support resources
const supportResources = [
    {
        title: 'Documentation',
        icon: BookOpen,
        iconColor: 'text-indigo-400',
        link: 'https://www.mythical.systems/docs',
    },
    {
        title: 'Discord Community',
        icon: MessageCircle,
        iconColor: 'text-violet-400',
        link: 'https://discord.mythical.systems',
    },
    {
        title: 'GitHub Repository',
        icon: Github,
        iconColor: 'text-gray-400',
        link: 'https://github.com/mythicalltd',
    },
    {
        title: 'Support Team',
        icon: Heart,
        iconColor: 'text-pink-400',
        link: 'https://discord.mythical.systems',
    },
];

// Calculate grid position from mouse coordinates
const calculateGridPosition = (clientX: number, clientY: number): { x: number; y: number } => {
    // This is a simplified implementation
    // In a real application, you'd calculate based on the grid's actual dimensions and position
    const gridElement = document.querySelector('.dashboard-grid');
    if (!gridElement) return { x: 0, y: 0 };

    const rect = gridElement.getBoundingClientRect();
    const gridWidth = rect.width;
    const gridX = Math.floor(((clientX - rect.left) / gridWidth) * 12);
    const gridY = Math.floor((clientY - rect.top) / 50); // Approximate row height

    return {
        x: Math.max(0, Math.min(11, gridX)),
        y: Math.max(0, gridY),
    };
};

// Function to start dragging
const onDragStart = (id: string, event: MouseEvent): void => {
    if (!layoutActive.value) return;

    isDragging.value = true;
    activeWidget.value = id;

    // Remember start position
    dragStart.x = event.clientX;
    dragStart.y = event.clientY;

    const widget = widgets.find((w) => w.id === id);
    if (widget) {
        dragStart.gridX = widget.x;
        dragStart.gridY = widget.y;
    }

    // Add event listeners for document-level dragging
    document.addEventListener('mousemove', onDragMove);
    document.addEventListener('mouseup', onDragEnd);
};

// Function to handle dragging
const onDragMove = (event: MouseEvent): void => {
    if (!isDragging.value || !activeWidget.value) return;

    // Prevent default to avoid text selection during drag
    event.preventDefault();

    // Calculate grid position from mouse coordinates
    const { x, y } = calculateGridPosition(event.clientX, event.clientY);

    // Update widget position
    const widget = widgets.find((w) => w.id === activeWidget.value);
    if (widget) {
        // Calculate new position, clamping to grid boundaries
        const newX = Math.max(0, Math.min(12 - widget.cols, x));
        const newY = Math.max(0, y);

        // Update widget position if it's changed
        if (widget.x !== newX || widget.y !== newY) {
            widget.x = newX;
            widget.y = newY;
        }
    }
};

// Function to end dragging
const onDragEnd = (): void => {
    if (!isDragging.value) return;

    isDragging.value = false;
    activeWidget.value = null;

    // Remove document-level event listeners
    document.removeEventListener('mousemove', onDragMove);
    document.removeEventListener('mouseup', onDragEnd);

    // Save layout after dragging
    if (layoutActive.value) {
        saveWidgetLayout();
    }
};

// Function to handle widget resize
const onWidgetResize = (id: string, cols: number, rows: number): void => {
    const widget = widgets.find((w) => w.id === id);
    if (widget) {
        widget.cols = Math.max(widget.minCols, Math.min(12, cols));
        widget.rows = Math.max(widget.minRows, rows);

        // Make sure widget still fits in grid after resize
        if (widget.x + widget.cols > 12) {
            widget.x = 12 - widget.cols;
        }
    }
};
</script>

<style scoped>
/* Add custom animations and styles */
@keyframes pulse {
    0%,
    100% {
        opacity: 1;
    }
    50% {
        opacity: 0.7;
    }
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

.animate-spin {
    animation: spin 1s linear infinite;
}

.animate-fadeIn {
    animation: fadeIn 0.3s ease-in-out;
}

.bg-gradient-to-r {
    background-image: linear-gradient(to right, var(--tw-gradient-stops));
}

/* Dashboard Widget Styling */
.dashboard-widget {
    transition: all 0.3s ease;
}

.dashboard-widget-draggable {
    cursor: move;
}

.dashboard-widget-dragging {
    opacity: 0.7;
    transform: scale(1.02);
    z-index: 10;
}

.dashboard-widget-header {
    cursor: pointer;
}

/* For a more interactive drag experience, you would need JavaScript drag and drop implementation */
.dashboard-widget:hover .dashboard-widget-header {
    background-color: rgba(31, 41, 55, 0.9);
}

/* Mouse drag interaction helpers */
.dashboard-grid {
    position: relative;
}

.dashboard-grid::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    pointer-events: none;
    z-index: 0;
    background:
        linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
    background-size: 8.33% 30px;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.dashboard-grid.layout-active::after {
    opacity: 1;
}
</style>
