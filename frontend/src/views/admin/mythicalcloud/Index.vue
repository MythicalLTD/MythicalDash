<template>
    <LayoutDashboard>
        <div class="space-y-6">
            <!-- Hero Section with Premium Badge -->
            <div
                class="bg-gradient-to-r from-indigo-900/70 via-violet-900/50 to-purple-900/70 backdrop-blur-md rounded-xl p-6 border border-indigo-800/30"
            >
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between">
                    <div class="max-w-3xl">
                        <div class="flex items-center gap-2 mb-3">
                            <h1 class="text-3xl font-bold">MythicalCloud</h1>
                            <span
                                class="bg-gradient-to-r from-amber-400 to-amber-600 text-white text-xs px-2 py-1 rounded-full"
                            >
                                Premium
                            </span>
                        </div>
                        <p class="text-gray-300 mb-4">
                            Securely back up your MythicalDash configuration to the cloud and easily migrate between
                            instances. Access your settings and data anywhere, anytime.
                        </p>
                        <div class="flex flex-wrap items-center gap-3">
                            <button
                                class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors duration-200"
                            >
                                <CloudIcon class="w-4 h-4" />
                                <span>Backup Now</span>
                            </button>
                            <button
                                class="flex items-center gap-2 px-4 py-2 bg-transparent hover:bg-white/10 text-white border border-white/30 rounded-lg transition-colors duration-200"
                            >
                                <HistoryIcon class="w-4 h-4" />
                                <span>View Backup History</span>
                            </button>
                        </div>
                    </div>
                    <div class="mt-6 md:mt-0">
                        <div class="bg-indigo-800/30 backdrop-blur-md rounded-xl p-4 border border-indigo-700/30">
                            <h3 class="text-lg font-medium text-white mb-2">Backup Status</h3>
                            <div class="flex items-center gap-2 text-green-400 mb-1">
                                <CheckCircleIcon class="w-5 h-5" />
                                <span class="font-medium">Backup system active</span>
                            </div>
                            <p class="text-gray-300 text-sm">Last backup: Today, 09:45 AM</p>
                            <p class="text-gray-400 text-xs mt-2">Next automatic backup in 23 hours</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Backup Statistics Card -->
                <div class="bg-gray-800/50 backdrop-blur-md rounded-xl border border-gray-800/30 overflow-hidden">
                    <div class="p-5">
                        <h2 class="text-xl font-semibold mb-4">Backup Statistics</h2>
                        <div class="space-y-4">
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-sm text-gray-400">Storage Used</span>
                                    <span class="text-sm text-gray-300">2.4 GB / 10 GB</span>
                                </div>
                                <div class="w-full bg-gray-700/30 rounded-full h-2">
                                    <div class="bg-indigo-500 h-2 rounded-full" style="width: 24%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-sm text-gray-400">Backup Retention</span>
                                    <span class="text-sm text-gray-300">30 days</span>
                                </div>
                                <div class="w-full bg-gray-700/30 rounded-full h-2">
                                    <div class="bg-green-500 h-2 rounded-full" style="width: 100%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-sm text-gray-400">Backup Frequency</span>
                                    <span class="text-sm text-gray-300">Daily</span>
                                </div>
                                <div class="w-full bg-gray-700/30 rounded-full h-2">
                                    <div class="bg-blue-500 h-2 rounded-full" style="width: 60%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Backup Content Card -->
                <div class="bg-gray-800/50 backdrop-blur-md rounded-xl border border-gray-800/30 overflow-hidden">
                    <div class="p-5">
                        <h2 class="text-xl font-semibold mb-4">Backup Content</h2>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <ServerIcon class="w-5 h-5 text-indigo-400" />
                                    <span class="text-gray-300">System Settings</span>
                                </div>
                                <div class="bg-green-500/10 text-green-400 text-xs px-2 py-0.5 rounded-full">
                                    Included
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <UsersIcon class="w-5 h-5 text-indigo-400" />
                                    <span class="text-gray-300">User Accounts</span>
                                </div>
                                <div class="bg-green-500/10 text-green-400 text-xs px-2 py-0.5 rounded-full">
                                    Included
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <DatabaseIcon class="w-5 h-5 text-indigo-400" />
                                    <span class="text-gray-300">Database</span>
                                </div>
                                <div class="bg-green-500/10 text-green-400 text-xs px-2 py-0.5 rounded-full">
                                    Included
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <FileIcon class="w-5 h-5 text-indigo-400" />
                                    <span class="text-gray-300">Uploaded Files</span>
                                </div>
                                <div class="bg-green-500/10 text-green-400 text-xs px-2 py-0.5 rounded-full">
                                    Included
                                </div>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <BarChartIcon class="w-5 h-5 text-indigo-400" />
                                    <span class="text-gray-300">Analytics Data</span>
                                </div>
                                <div class="bg-amber-500/10 text-amber-400 text-xs px-2 py-0.5 rounded-full">
                                    Optional
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Restore Options Card -->
                <div class="bg-gray-800/50 backdrop-blur-md rounded-xl border border-gray-800/30 overflow-hidden">
                    <div class="p-5">
                        <h2 class="text-xl font-semibold mb-4">Restore Options</h2>
                        <div class="space-y-4">
                            <div
                                class="bg-gray-700/30 rounded-lg p-3 hover:bg-gray-700/50 transition-colors cursor-pointer"
                            >
                                <div class="flex items-center gap-2 mb-1">
                                    <RefreshCwIcon class="w-5 h-5 text-green-400" />
                                    <span class="font-medium text-gray-200">Complete Restore</span>
                                </div>
                                <p class="text-sm text-gray-400">
                                    Restore your entire MythicalDash configuration and data.
                                </p>
                            </div>
                            <div
                                class="bg-gray-700/30 rounded-lg p-3 hover:bg-gray-700/50 transition-colors cursor-pointer"
                            >
                                <div class="flex items-center gap-2 mb-1">
                                    <ArrowUpRightIcon class="w-5 h-5 text-amber-400" />
                                    <span class="font-medium text-gray-200">Migration</span>
                                </div>
                                <p class="text-sm text-gray-400">
                                    Move your configuration to a new MythicalDash instance.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="border-t border-gray-800/30 p-4">
                        <button
                            class="w-full py-2 text-sm text-indigo-400 hover:text-indigo-300 transition-colors flex items-center justify-center gap-1"
                        >
                            <PlayIcon class="w-4 h-4" />
                            <span>Start Restore Process</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Backup History Section -->
            <div class="bg-gray-800/50 backdrop-blur-md rounded-xl border border-gray-800/30 overflow-hidden">
                <div class="p-5">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-semibold">Recent Backups</h2>
                        <div class="flex gap-2">
                            <button
                                class="px-3 py-1.5 text-sm bg-gray-700/50 hover:bg-gray-700/70 rounded-lg transition-colors"
                            >
                                <RefreshIcon class="w-4 h-4" />
                            </button>
                            <button
                                class="px-3 py-1.5 text-sm bg-gray-700/50 hover:bg-gray-700/70 rounded-lg transition-colors"
                            >
                                <FilterIcon class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-full">
                            <thead>
                                <tr class="text-left text-xs text-gray-400 border-b border-gray-800/30">
                                    <th class="py-3 px-4">Backup ID</th>
                                    <th class="py-3 px-4">Date & Time</th>
                                    <th class="py-3 px-4">Size</th>
                                    <th class="py-3 px-4">Type</th>
                                    <th class="py-3 px-4">Status</th>
                                    <th class="py-3 px-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-800/30">
                                <tr class="text-sm hover:bg-gray-700/20 transition-colors">
                                    <td class="py-3 px-4 font-mono text-indigo-400">bkp_123456</td>
                                    <td class="py-3 px-4">Today, 09:45 AM</td>
                                    <td class="py-3 px-4">245 MB</td>
                                    <td class="py-3 px-4">
                                        <span class="bg-blue-500/10 text-blue-400 text-xs px-2 py-0.5 rounded-full">
                                            Automatic
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="flex items-center gap-1 text-green-400">
                                            <CheckCircleIcon class="w-4 h-4" />
                                            <span>Completed</span>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <button
                                                class="p-1.5 bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 rounded-lg transition-colors"
                                            >
                                                <DownloadIcon class="w-4 h-4" />
                                            </button>
                                            <button
                                                class="p-1.5 bg-green-500/10 hover:bg-green-500/20 text-green-400 rounded-lg transition-colors"
                                            >
                                                <RefreshCwIcon class="w-4 h-4" />
                                            </button>
                                            <button
                                                class="p-1.5 bg-gray-600/30 hover:bg-gray-600/50 text-gray-300 rounded-lg transition-colors"
                                            >
                                                <MoreHorizontalIcon class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="text-sm hover:bg-gray-700/20 transition-colors">
                                    <td class="py-3 px-4 font-mono text-indigo-400">bkp_123455</td>
                                    <td class="py-3 px-4">Yesterday, 09:45 AM</td>
                                    <td class="py-3 px-4">242 MB</td>
                                    <td class="py-3 px-4">
                                        <span class="bg-blue-500/10 text-blue-400 text-xs px-2 py-0.5 rounded-full">
                                            Automatic
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="flex items-center gap-1 text-green-400">
                                            <CheckCircleIcon class="w-4 h-4" />
                                            <span>Completed</span>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <button
                                                class="p-1.5 bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 rounded-lg transition-colors"
                                            >
                                                <DownloadIcon class="w-4 h-4" />
                                            </button>
                                            <button
                                                class="p-1.5 bg-green-500/10 hover:bg-green-500/20 text-green-400 rounded-lg transition-colors"
                                            >
                                                <RefreshCwIcon class="w-4 h-4" />
                                            </button>
                                            <button
                                                class="p-1.5 bg-gray-600/30 hover:bg-gray-600/50 text-gray-300 rounded-lg transition-colors"
                                            >
                                                <MoreHorizontalIcon class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="text-sm hover:bg-gray-700/20 transition-colors">
                                    <td class="py-3 px-4 font-mono text-indigo-400">bkp_123450</td>
                                    <td class="py-3 px-4">Mar 30, 2023, 10:15 AM</td>
                                    <td class="py-3 px-4">238 MB</td>
                                    <td class="py-3 px-4">
                                        <span class="bg-amber-500/10 text-amber-400 text-xs px-2 py-0.5 rounded-full">
                                            Manual
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="flex items-center gap-1 text-green-400">
                                            <CheckCircleIcon class="w-4 h-4" />
                                            <span>Completed</span>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <div class="flex justify-end gap-2">
                                            <button
                                                class="p-1.5 bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 rounded-lg transition-colors"
                                            >
                                                <DownloadIcon class="w-4 h-4" />
                                            </button>
                                            <button
                                                class="p-1.5 bg-green-500/10 hover:bg-green-500/20 text-green-400 rounded-lg transition-colors"
                                            >
                                                <RefreshCwIcon class="w-4 h-4" />
                                            </button>
                                            <button
                                                class="p-1.5 bg-gray-600/30 hover:bg-gray-600/50 text-gray-300 rounded-lg transition-colors"
                                            >
                                                <MoreHorizontalIcon class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="border-t border-gray-800/30 p-4 flex justify-between items-center">
                    <div class="text-sm text-gray-400">Showing 3 of 30 backups</div>
                    <div class="flex gap-2">
                        <button
                            class="px-3 py-1.5 text-sm bg-gray-700/50 hover:bg-gray-700/70 rounded-lg transition-colors"
                        >
                            Previous
                        </button>
                        <button
                            class="px-3 py-1.5 text-sm bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-colors"
                        >
                            Next
                        </button>
                    </div>
                </div>
            </div>

            <!-- Premium Feature Callout -->
            <div
                class="bg-gradient-to-r from-amber-700/30 to-amber-600/30 backdrop-blur-md rounded-xl p-6 border border-amber-700/30"
            >
                <div class="flex flex-col md:flex-row items-center justify-between">
                    <div class="mb-4 md:mb-0 md:mr-6">
                        <h2 class="text-xl font-bold text-white mb-2">Premium Feature</h2>
                        <p class="text-gray-300">
                            MythicalCloud is part of your premium subscription. Enjoy enhanced security, automated
                            backups, and priority restoration services. Your data is encrypted and securely stored in
                            our cloud.
                        </p>
                    </div>
                    <button
                        class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg transition-colors whitespace-nowrap"
                    >
                        Manage Subscription
                    </button>
                </div>
            </div>
        </div>
    </LayoutDashboard>
</template>

<script setup lang="ts">
import LayoutDashboard from '@/components/admin/LayoutDashboard.vue';
import {
    Cloud as CloudIcon,
    History as HistoryIcon,
    CheckCircle as CheckCircleIcon,
    Server as ServerIcon,
    Users as UsersIcon,
    Database as DatabaseIcon,
    File as FileIcon,
    BarChart as BarChartIcon,
    RefreshCw as RefreshCwIcon,
    Filter as FilterIcon,
    ArrowUpRight as ArrowUpRightIcon,
    Play as PlayIcon,
    RefreshCw as RefreshIcon,
    Download as DownloadIcon,
    MoreHorizontal as MoreHorizontalIcon,
} from 'lucide-vue-next';
</script>
