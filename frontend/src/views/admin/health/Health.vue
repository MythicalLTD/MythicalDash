<template>
    <LayoutDashboard>
        <div class="space-y-6">
            <!-- Header -->
            <div
                class="flex flex-col md:flex-row md:items-center md:justify-between bg-gradient-to-r from-gray-900/70 to-gray-800/50 backdrop-blur-md rounded-xl p-6 border border-gray-800/30"
            >
                <div>
                    <h1 class="text-3xl font-bold mb-2">System Health</h1>
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
                        @click="uploadLogs"
                        class="flex items-center gap-2 px-4 py-2 bg-indigo-600/20 hover:bg-indigo-600/30 border border-indigo-700/30 rounded-lg transition-all duration-200"
                        :class="{ 'opacity-50 cursor-wait': isUploading }"
                    >
                        <Upload v-if="!isUploading" class="w-4 h-4 text-indigo-400" />
                        <Loader v-else class="w-4 h-4 text-indigo-400 animate-spin" />
                        <span class="text-sm text-gray-300">Upload Logs</span>
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

            <!-- Overall Status -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-gray-800/50 backdrop-blur-md rounded-xl p-6 border border-gray-800/30">
                    <h2 class="text-xl font-semibold mb-4">System Status</h2>
                    <div class="flex items-center gap-3">
                        <div
                            class="w-12 h-12 rounded-full flex items-center justify-center"
                            :class="{
                                'bg-green-500/20': healthStore.systemStatus === 'healthy',
                                'bg-yellow-500/20': healthStore.systemStatus === 'warning',
                                'bg-red-500/20': healthStore.systemStatus === 'unhealthy',
                            }"
                        >
                            <CheckCircle v-if="healthStore.systemStatus === 'healthy'" class="w-6 h-6 text-green-400" />
                            <AlertTriangle
                                v-else-if="healthStore.systemStatus === 'warning'"
                                class="w-6 h-6 text-yellow-400"
                            />
                            <XCircle v-else class="w-6 h-6 text-red-400" />
                        </div>
                        <div>
                            <p class="text-sm text-gray-400">Current Status</p>
                            <span class="text-lg font-medium">
                                {{
                                    healthStore.systemStatus
                                        ? healthStore.systemStatus.charAt(0).toUpperCase() +
                                          healthStore.systemStatus.slice(1)
                                        : 'Unknown'
                                }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- System Health Sections -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <!-- System Resources -->
                <div class="bg-gray-800/50 backdrop-blur-md rounded-xl p-6 border border-gray-800/30">
                    <h2 class="text-xl font-semibold mb-4">System Resources</h2>
                    <div class="space-y-4">
                        <!-- Memory Usage -->
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-gray-400">Memory Usage</span>
                                <span
                                    class="text-sm"
                                    :class="{
                                        'text-green-400': memoryUsage?.status === 'ok',
                                        'text-yellow-400': memoryUsage?.status === 'warning',
                                        'text-red-400': memoryUsage?.status === 'error',
                                    }"
                                >
                                    {{ formatBytes(memoryUsage?.current) }} /
                                    {{ formatBytes(parseMemoryLimit(memoryUsage?.limit)) }}
                                </span>
                            </div>
                            <div class="w-full bg-gray-700/50 rounded-full h-2">
                                <div
                                    class="h-2 rounded-full"
                                    :class="{
                                        'bg-green-500': memoryUsage?.status === 'ok',
                                        'bg-yellow-500': memoryUsage?.status === 'warning',
                                        'bg-red-500': memoryUsage?.status === 'error',
                                    }"
                                    :style="{
                                        width: `${calculatePercentage(memoryUsage?.current, parseMemoryLimit(memoryUsage?.limit))}%`,
                                    }"
                                ></div>
                            </div>
                        </div>

                        <!-- Disk Space -->
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-gray-400">Disk Space</span>
                                <span
                                    class="text-sm"
                                    :class="{
                                        'text-green-400': diskSpace?.status === 'ok',
                                        'text-yellow-400': diskSpace?.status === 'warning',
                                        'text-red-400': diskSpace?.status === 'error',
                                    }"
                                >
                                    {{ formatBytes(diskSpace?.used) }} /
                                    {{ formatBytes(diskSpace?.total) }}
                                </span>
                            </div>
                            <div class="w-full bg-gray-700/50 rounded-full h-2">
                                <div
                                    class="h-2 rounded-full"
                                    :class="{
                                        'bg-green-500': diskSpace?.status === 'ok',
                                        'bg-yellow-500': diskSpace?.status === 'warning',
                                        'bg-red-500': diskSpace?.status === 'error',
                                    }"
                                    :style="{
                                        width: `${calculatePercentage(diskSpace?.used, diskSpace?.total)}%`,
                                    }"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PHP Configuration -->
                <div class="bg-gray-800/50 backdrop-blur-md rounded-xl p-6 border border-gray-800/30">
                    <h2 class="text-xl font-semibold mb-4">PHP Configuration</h2>
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-400">PHP Version</p>
                                <p
                                    class="text-sm font-medium"
                                    :class="{
                                        'text-green-400': healthData?.system?.php_version?.status === 'ok',
                                        'text-yellow-400': healthData?.system?.php_version?.status === 'warning',
                                        'text-red-400': healthData?.system?.php_version?.status === 'error',
                                    }"
                                >
                                    {{ healthData?.system?.php_version?.current }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-400">Max Execution Time</p>
                                <p
                                    class="text-sm font-medium"
                                    :class="{
                                        'text-green-400': healthData?.system?.max_execution_time?.status === 'ok',
                                        'text-yellow-400': healthData?.system?.max_execution_time?.status === 'warning',
                                        'text-red-400': healthData?.system?.max_execution_time?.status === 'error',
                                    }"
                                >
                                    {{ healthData?.system?.max_execution_time?.current }}s
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-400">Upload Max Filesize</p>
                                <p
                                    class="text-sm font-medium"
                                    :class="{
                                        'text-green-400': healthData?.system?.upload_max_filesize?.status === 'ok',
                                        'text-yellow-400':
                                            healthData?.system?.upload_max_filesize?.status === 'warning',
                                        'text-red-400': healthData?.system?.upload_max_filesize?.status === 'error',
                                    }"
                                >
                                    {{ healthData?.system?.upload_max_filesize?.current }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-400">Post Max Size</p>
                                <p
                                    class="text-sm font-medium"
                                    :class="{
                                        'text-green-400': healthData?.system?.post_max_size?.status === 'ok',
                                        'text-yellow-400': healthData?.system?.post_max_size?.status === 'warning',
                                        'text-red-400': healthData?.system?.post_max_size?.status === 'error',
                                    }"
                                >
                                    {{ healthData?.system?.post_max_size?.current }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Database & Extensions -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <!-- Database Status -->
                <div class="bg-gray-800/50 backdrop-blur-md rounded-xl p-6 border border-gray-800/30">
                    <h2 class="text-xl font-semibold mb-4">Database Status</h2>
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-400">Database Size</p>
                                <p class="text-sm font-medium text-gray-200">
                                    {{ healthData?.database?.size?.formatted }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-400">Active Connections</p>
                                <p class="text-sm font-medium text-gray-200">
                                    {{ healthData?.database?.connections?.active }} /
                                    {{ healthData?.database?.connections?.max }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-400">Total Tables</p>
                                <p class="text-sm font-medium text-gray-200">
                                    {{ healthData?.database?.tables?.count }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-400">Slow Queries</p>
                                <p class="text-sm font-medium text-gray-200">
                                    {{ healthData?.database?.slow_queries?.count }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Required Extensions -->
                <div class="bg-gray-800/50 backdrop-blur-md rounded-xl p-6 border border-gray-800/30">
                    <h2 class="text-xl font-semibold mb-4">Required Extensions</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div
                            v-for="(extension, name) in healthData?.extensions?.required"
                            :key="name"
                            class="flex items-center gap-2"
                        >
                            <div
                                class="w-2 h-2 rounded-full"
                                :class="{
                                    'bg-green-500': extension.status === 'ok',
                                    'bg-yellow-500': extension.status === 'warning',
                                    'bg-red-500': extension.status === 'error',
                                }"
                            ></div>
                            <span class="text-sm text-gray-200">{{ name }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Permissions -->
            <div class="bg-gray-800/50 backdrop-blur-md rounded-xl p-6 border border-gray-800/30">
                <h2 class="text-xl font-semibold mb-4">Directory Permissions</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div
                        v-for="(permission, name) in healthData?.permissions"
                        :key="name"
                        class="flex items-center gap-2"
                    >
                        <div
                            class="w-2 h-2 rounded-full"
                            :class="{
                                'bg-green-500': permission.status === 'ok',
                                'bg-yellow-500': permission.status === 'warning',
                                'bg-red-500': permission.status === 'error',
                            }"
                        ></div>
                        <span class="text-sm text-gray-200">{{ name }}</span>
                    </div>
                </div>
            </div>

            <!-- Cron Jobs -->
            <div class="bg-gray-800/50 backdrop-blur-md rounded-xl p-6 border border-gray-800/30">
                <h2 class="text-xl font-semibold mb-4">Cron Jobs</h2>
                <div v-if="cronData?.summary" class="text-sm text-gray-400 mb-4">
                    {{ cronData.summary }}
                </div>
                <div v-else class="space-y-3">
                    <div
                        v-for="cron in cronData?.recent"
                        :key="cron.id"
                        class="flex items-center justify-between p-4 bg-gray-700/30 rounded-lg border border-gray-600/30"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="w-3 h-3 rounded-full"
                                :class="{
                                    'bg-green-500': !cron.late && cron.last_run_success,
                                    'bg-yellow-500': cron.late && cron.last_run_success,
                                    'bg-red-500': !cron.last_run_success,
                                }"
                            ></div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-200">
                                    {{ cron.task_name.replace(/-/g, ' ').replace(/\b\w/g, (l) => l.toUpperCase()) }}
                                </h3>
                                <p class="text-xs text-gray-400">
                                    Expected: {{ formatInterval(cron.expected_interval_seconds) }}
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="flex items-center gap-2 mb-1">
                                <span
                                    class="text-xs px-2 py-1 rounded-full"
                                    :class="{
                                        'bg-green-500/20 text-green-400': !cron.late && cron.last_run_success,
                                        'bg-yellow-500/20 text-yellow-400': cron.late && cron.last_run_success,
                                        'bg-red-500/20 text-red-400': !cron.last_run_success,
                                    }"
                                >
                                    {{ cron.late ? 'Late' : cron.last_run_success ? 'Success' : 'Failed' }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-400">
                                {{ formatTimeAgo(cron.last_run_at) }}
                            </p>
                            <p
                                v-if="cron.last_run_message"
                                class="text-xs text-gray-500 mt-1 max-w-xs truncate"
                                :title="cron.last_run_message"
                            >
                                {{ cron.last_run_message }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import {
    RefreshCcw,
    Loader,
    CheckCircle,
    AlertTriangle,
    XCircle,
    Settings as SettingsIcon2,
    Upload,
} from 'lucide-vue-next';
import LayoutDashboard from '@/components/admin/LayoutDashboard.vue';
import { RouterLink } from 'vue-router';
import { useSettingsStore } from '@/stores/settings';
import { useHealthStore } from '@/stores/health';
import type { HealthData, MemoryUsage, DiskSpace, GithubData, CronData } from '@/types/health';
import Swal from 'sweetalert2';

const Settings = useSettingsStore();
const healthStore = useHealthStore();
const isRefreshing = ref(false);
const isUploading = ref(false);
const healthData = ref<HealthData | null>(null);
const githubData = ref<GithubData | null>(null);
const memoryUsage = ref<MemoryUsage | null>(null);
const diskSpace = ref<DiskSpace | null>(null);
const cronData = ref<CronData | null>(null);

// Format bytes to human readable format
const formatBytes = (bytes: number | undefined): string => {
    if (!bytes) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return `${parseFloat((bytes / Math.pow(k, i)).toFixed(2))} ${sizes[i]}`;
};

// Parse memory limit string to bytes
const parseMemoryLimit = (limit: string | undefined): number => {
    if (!limit) return 0;
    const units: Record<string, number> = { K: 1024, M: 1024 * 1024, G: 1024 * 1024 * 1024 };
    const value = parseInt(limit);
    const unit = limit.replace(/[0-9]/g, '').toUpperCase();
    return value * (units[unit] || 1);
};

// Calculate percentage safely
const calculatePercentage = (current: number | undefined, total: number | undefined): number => {
    if (!current || !total) return 0;
    return (current / total) * 100;
};

// Format time ago
const formatTimeAgo = (dateString: string | null): string => {
    if (!dateString) return 'Never';
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now.getTime() - date.getTime();
    const diffSeconds = Math.floor(diffMs / 1000);
    const diffMinutes = Math.floor(diffSeconds / 60);
    const diffHours = Math.floor(diffMinutes / 60);
    const diffDays = Math.floor(diffHours / 24);

    if (diffSeconds < 60) return `${diffSeconds}s ago`;
    if (diffMinutes < 60) return `${diffMinutes}m ago`;
    if (diffHours < 24) return `${diffHours}h ago`;
    return `${diffDays}d ago`;
};

// Format interval to human readable
const formatInterval = (seconds: number): string => {
    if (seconds < 60) return `${seconds}s`;
    if (seconds < 3600) return `${Math.floor(seconds / 60)}m`;
    if (seconds < 86400) return `${Math.floor(seconds / 3600)}h`;
    return `${Math.floor(seconds / 86400)}d`;
};

// Load health data
const loadData = async () => {
    try {
        await healthStore.fetchHealthData();
        healthData.value = healthStore.healthData;
        memoryUsage.value = healthStore.memoryUsage;
        diskSpace.value = healthStore.diskSpace;
        githubData.value = healthStore.healthData?.github_data || null;
        cronData.value = healthStore.cronData;
    } catch (error) {
        console.error('Error loading health data:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to load health data. Please try again.',
        });
    }
};

// Upload logs to support
const uploadLogs = async () => {
    isUploading.value = true;
    try {
        const response = await fetch('/api/admin/logs/upload', {
            method: 'POST',
        });
        const data = await response.json();
        if (data.success) {
            await Swal.fire({
                icon: 'success',
                title: 'Success',
                html: `
                    <p>Logs uploaded successfully!</p>
                    <div class="mt-4">
                        <p class="text-sm text-gray-600">Dashboard Logs:</p>
                        <a href="${data.dashboard_logs_url}" target="_blank" class="text-blue-500 hover:underline">${data.dashboard_logs_url}</a>
                    </div>
                    <div class="mt-2">
                        <p class="text-sm text-gray-600">Web Server Logs:</p>
                        <a href="${data.web_server_logs_url}" target="_blank" class="text-blue-500 hover:underline">${data.web_server_logs_url}</a>
                    </div>
                `,
            });
        } else {
            await Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to upload logs: ' + data.message,
            });
        }
    } catch (error) {
        console.error('Failed to upload logs:', error);
        await Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to upload logs. Please try again later.',
        });
    } finally {
        isUploading.value = false;
    }
};

// Refresh data
const refreshData = async () => {
    isRefreshing.value = true;
    try {
        await loadData();
        await Swal.fire({
            icon: 'success',
            title: 'Success',
            text: 'Health data refreshed successfully!',
            timer: 1500,
            showConfirmButton: false,
        });
    } catch (error) {
        console.error('Error refreshing health data:', error);
        await Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to refresh health data. Please try again.',
        });
    } finally {
        isRefreshing.value = false;
    }
};

onMounted(() => {
    loadData();
});
</script>

<style scoped>
@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

.animate-spin {
    animation: spin 1s linear infinite;
}

.bg-gradient-to-r {
    background-image: linear-gradient(to right, var(--tw-gradient-stops));
}
</style>
