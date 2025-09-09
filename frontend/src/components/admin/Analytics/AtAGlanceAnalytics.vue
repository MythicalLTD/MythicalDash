<template>
    <div class="bg-gray-800/50 rounded-xl border border-gray-800/30">
        <div class="p-4 border-b border-gray-800/30 flex justify-between items-center">
            <h2 class="text-lg font-medium text-white">At-a-Glance Analytics</h2>
            <div>
                <button
                    @click="refreshAnalytics"
                    class="flex items-center gap-2 px-4 py-2 bg-gray-800/50 hover:bg-gray-700/50 border border-gray-700/30 rounded-lg transition-all duration-200"
                    :class="{ 'opacity-50 cursor-wait': isRefreshing }"
                >
                    <RefreshCcw v-if="!isRefreshing" class="w-4 h-4 text-gray-400" />
                    <Loader v-else class="w-4 h-4 text-gray-400 animate-spin" />
                    <span class="text-sm text-gray-300">Refresh</span>
                </button>
            </div>
        </div>
        <div class="p-4">
            <!-- Quick stats -->
            <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
                <!-- New Users -->
                <div
                    class="bg-gray-800/80 backdrop-blur-sm rounded-xl p-4 border border-gray-800/50 hover:bg-gray-700/50 transition-all duration-300"
                >
                    <div class="flex flex-col items-center text-center">
                        <div class="mb-2 w-10 h-10 rounded-full flex items-center justify-center bg-blue-500/20">
                            <Users class="w-5 h-5 text-blue-400" />
                        </div>
                        <h3 class="text-sm font-medium text-gray-400 mb-1">New Users</h3>
                        <p class="text-2xl font-bold text-white">{{ analytics.new_users_24h || 0 }}</p>
                        <span class="text-xs text-gray-500 mt-1">Last 24 hours</span>
                    </div>
                </div>

                <!-- New Servers -->
                <div
                    class="bg-gray-800/80 backdrop-blur-sm rounded-xl p-4 border border-gray-800/50 hover:bg-gray-700/50 transition-all duration-300"
                >
                    <div class="flex flex-col items-center text-center">
                        <div class="mb-2 w-10 h-10 rounded-full flex items-center justify-center bg-green-500/20">
                            <Server class="w-5 h-5 text-green-400" />
                        </div>
                        <h3 class="text-sm font-medium text-gray-400 mb-1">New Servers</h3>
                        <p class="text-2xl font-bold text-white">{{ analytics.new_servers_24h || 0 }}</p>
                        <span class="text-xs text-gray-500 mt-1">Last 24 hours</span>
                    </div>
                </div>

                <!-- Total Servers -->
                <div
                    class="bg-gray-800/80 backdrop-blur-sm rounded-xl p-4 border border-gray-800/50 hover:bg-gray-700/50 transition-all duration-300"
                >
                    <div class="flex flex-col items-center text-center">
                        <div class="mb-2 w-10 h-10 rounded-full flex items-center justify-center bg-indigo-500/20">
                            <Server class="w-5 h-5 text-indigo-400" />
                        </div>
                        <h3 class="text-sm font-medium text-gray-400 mb-1">Total Servers</h3>
                        <p class="text-2xl font-bold text-white">{{ analytics.total_servers || 0 }}</p>
                        <span class="text-xs text-gray-500 mt-1">Active instances</span>
                    </div>
                </div>

                <!-- New Tickets -->
                <div
                    class="bg-gray-800/80 backdrop-blur-sm rounded-xl p-4 border border-gray-800/50 hover:bg-gray-700/50 transition-all duration-300"
                >
                    <div class="flex flex-col items-center text-center">
                        <div class="mb-2 w-10 h-10 rounded-full flex items-center justify-center bg-amber-500/20">
                            <TicketIcon class="w-5 h-5 text-amber-400" />
                        </div>
                        <h3 class="text-sm font-medium text-gray-400 mb-1">New Tickets</h3>
                        <p class="text-2xl font-bold text-white">{{ analytics.new_tickets_24h || 0 }}</p>
                        <span class="text-xs text-gray-500 mt-1">Last 24 hours</span>
                    </div>
                </div>

                <!-- Pending Deployments -->
                <div
                    class="bg-gray-800/80 backdrop-blur-sm rounded-xl p-4 border border-gray-800/50 hover:bg-gray-700/50 transition-all duration-300"
                >
                    <div class="flex flex-col items-center text-center">
                        <div class="mb-2 w-10 h-10 rounded-full flex items-center justify-center bg-rose-500/20">
                            <ServerCrash class="w-5 h-5 text-rose-400" />
                        </div>
                        <h3 class="text-sm font-medium text-gray-400 mb-1">Pending Servers</h3>
                        <p class="text-2xl font-bold text-white">{{ analytics.pending_servers || 0 }}</p>
                        <span class="text-xs text-gray-500 mt-1">In deployment queue</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Resource allocation -->

                <!-- Activity by hour -->
                <div class="md:col-span-3 bg-gray-800/80 backdrop-blur-sm rounded-xl p-4 border border-gray-800/50">
                    <h3 class="text-sm font-medium text-gray-400 mb-4">User Activity (Last 24h)</h3>
                    <div class="h-60">
                        <canvas ref="activityChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import { RefreshCcw, Loader, Users, Server, TicketIcon, ServerCrash } from 'lucide-vue-next';
import Dashboard from '@/mythicaldash/admin/Dashboard';
import Chart from 'chart.js/auto';

const isRefreshing = ref(false);
const activityChart = ref<HTMLCanvasElement | null>(null);
let chart: Chart | null = null;

const analytics = ref({
    new_users_24h: 0,
    new_servers_24h: 0,
    total_servers: 0,
    pending_servers: 0,
    new_tickets_24h: 0,
    open_tickets: 0,
    resource_usage: {
        memory: 0,
        disk: 0,
        server_count: 0,
    },
    hourly_activity: Array(24).fill(0),
});

// Format memory to a user-friendly format (MB, GB, etc.)
const formatMemory = (memory: number): string => {
    if (memory < 1024) {
        return `${memory} MB`;
    } else {
        return `${(memory / 1024).toFixed(2)} GB`;
    }
};

// Format disk space to a user-friendly format (MB, GB, etc.)
const formatDisk = (disk: number): string => {
    if (disk < 1024) {
        return `${disk} MB`;
    } else {
        return `${(disk / 1024).toFixed(2)} GB`;
    }
};

// Initialize chart with data
const initChart = () => {
    if (!activityChart.value) return;

    // Prepare labels for 24 hours
    const labels = Array.from({ length: 24 }, (_, i) => {
        const hour = i % 12 || 12;
        const ampm = i < 12 ? 'AM' : 'PM';
        return `${hour}${ampm}`;
    });

    // Create chart instance
    chart = new Chart(activityChart.value, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Activity',
                    data: analytics.value.hourly_activity,
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    tension: 0.3,
                    fill: true,
                    borderWidth: 2,
                    pointRadius: 3,
                    pointBackgroundColor: '#6366f1',
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    grid: {
                        color: 'rgba(255, 255, 255, 0.05)',
                    },
                    ticks: {
                        color: 'rgba(255, 255, 255, 0.5)',
                        maxRotation: 0,
                    },
                    border: {
                        display: false,
                    },
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(255, 255, 255, 0.05)',
                    },
                    ticks: {
                        color: 'rgba(255, 255, 255, 0.5)',
                        precision: 0,
                    },
                    border: {
                        display: false,
                    },
                },
            },
            plugins: {
                legend: {
                    display: false,
                },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.9)',
                    titleColor: 'rgba(255, 255, 255, 0.8)',
                    bodyColor: 'rgba(255, 255, 255, 0.8)',
                    borderColor: 'rgba(148, 163, 184, 0.2)',
                    borderWidth: 1,
                    cornerRadius: 8,
                    displayColors: false,
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        title: (items: Array<{ label?: string }>) => {
                            if (items && items.length > 0 && items[0]?.label !== undefined) {
                                return `Hour: ${items[0].label}`;
                            }
                            return '';
                        },
                        label: (context) => {
                            return `Activity: ${context.raw as number} actions`;
                        },
                    },
                },
            },
        },
    });
};

// Update chart with new data
const updateChart = () => {
    if (!chart) return;
    if (!chart.data?.datasets || !chart.data.datasets[0]) return;
    if (!analytics.value || !analytics.value.hourly_activity) return;

    chart.data.datasets[0].data = analytics.value.hourly_activity;
    chart.update();
};

// Fetch analytics data
const fetchAnalytics = async () => {
    isRefreshing.value = true;
    try {
        const data = await Dashboard.get();
        if (data.analytics) {
            analytics.value = data.analytics;
            updateChart();
        }
    } catch (error) {
        console.error('Failed to fetch analytics data:', error);
    } finally {
        isRefreshing.value = false;
    }
};

// Refresh analytics manually
const refreshAnalytics = () => {
    fetchAnalytics();
};

// Watch for changes in analytics data
watch(
    () => analytics.value.hourly_activity,
    () => {
        if (chart) {
            updateChart();
        }
    },
    { deep: true },
);

onMounted(() => {
    fetchAnalytics();
    // Initialize chart after analytics data is loaded
    setTimeout(() => {
        initChart();
    }, 500);
});
</script>
