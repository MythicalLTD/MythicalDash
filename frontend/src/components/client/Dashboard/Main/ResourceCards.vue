<template>
    <CardComponent
        :cardTitle="t('Components.ResourceCards.title')"
        :cardDescription="t('Components.ResourceCards.description')"
    >
        <!-- Layout Switcher -->
        <div class="flex justify-end mb-4">
            <button
                @click="toggleLayout"
                class="p-2 rounded-lg bg-gray-800/50 hover:bg-gray-800 text-gray-400 hover:text-white transition-colors"
                :title="
                    preferredLayout === 'cards'
                        ? 'Switch to Table View'
                        : preferredLayout === 'table'
                          ? 'Switch to Compact View'
                          : 'Switch to Card View'
                "
            >
                <GridIcon v-if="preferredLayout === 'table'" class="w-5 h-5" />
                <TableIcon v-else-if="preferredLayout === 'compact'" class="w-5 h-5" />
                <ListIcon v-else class="w-5 h-5" />
            </button>
        </div>

        <!-- Cards Layout -->
        <div v-if="preferredLayout === 'cards'">
            <!-- Background decorative elements -->
            <div class="absolute -top-20 -right-20 w-40 h-40 bg-blue-500/5 rounded-full blur-2xl"></div>
            <div class="absolute -bottom-20 -left-20 w-40 h-40 bg-indigo-500/5 rounded-full blur-2xl"></div>

            <!-- First row with 4 cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 relative z-10 mb-4">
                <!-- RAM -->
                <div
                    class="resource-card flex flex-col bg-dark-800/30 backdrop-blur-sm rounded-xl p-4 hover:bg-dark-800/50 transition-all duration-300 border border-gray-700/30 hover:border-indigo-500/30"
                >
                    <div class="flex items-center gap-3 mb-3">
                        <div class="icon-container">
                            <CircuitBoard class="resource-icon" />
                        </div>
                        <span class="text-gray-400 text-sm font-medium">
                            {{ t('Components.ResourceCards.RAM.title') }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex flex-col">
                            <span class="resource-value">
                                {{ resources.memory }}{{ t('Components.ResourceCards.mb') }}
                            </span>
                            <span class="resource-limit">
                                {{ t('Components.ResourceCards.of') }} {{ Session.getInfoInt('memory_limit')
                                }}{{ t('Components.ResourceCards.mb') }}
                            </span>
                        </div>
                        <div
                            class="percentage-badge"
                            :class="[
                                memoryUsagePercentage >= 80
                                    ? 'high-usage'
                                    : memoryUsagePercentage >= 50
                                      ? 'medium-usage'
                                      : 'low-usage',
                            ]"
                        >
                            {{ Math.round(memoryUsagePercentage) }}%
                        </div>
                    </div>
                    <div class="progress-container">
                        <div
                            class="progress-bar"
                            :class="[
                                memoryUsagePercentage >= 80
                                    ? 'high-usage'
                                    : memoryUsagePercentage >= 50
                                      ? 'medium-usage'
                                      : 'low-usage',
                            ]"
                            :style="{ width: memoryUsagePercentage + '%' }"
                        ></div>
                    </div>
                </div>

                <!-- Disk -->
                <div
                    class="resource-card flex flex-col bg-dark-800/30 backdrop-blur-sm rounded-xl p-4 hover:bg-dark-800/50 transition-all duration-300 border border-gray-700/30 hover:border-indigo-500/30"
                >
                    <div class="flex items-center gap-3 mb-3">
                        <div class="icon-container">
                            <HardDrive class="resource-icon" />
                        </div>
                        <span class="text-gray-400 text-sm font-medium">{{
                            t('Components.ResourceCards.DISK.title')
                        }}</span>
                    </div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex flex-col">
                            <span class="resource-value"
                                >{{ resources.disk }}{{ t('Components.ResourceCards.mb') }}</span
                            >
                            <span class="resource-limit"
                                >{{ t('Components.ResourceCards.of') }} {{ Session.getInfoInt('disk_limit')
                                }}{{ t('Components.ResourceCards.mb') }}</span
                            >
                        </div>
                        <div
                            class="percentage-badge"
                            :class="[
                                diskUsagePercentage >= 80
                                    ? 'high-usage'
                                    : diskUsagePercentage >= 50
                                      ? 'medium-usage'
                                      : 'low-usage',
                            ]"
                        >
                            {{ Math.round(diskUsagePercentage) }}%
                        </div>
                    </div>
                    <div class="progress-container">
                        <div
                            class="progress-bar"
                            :class="[
                                diskUsagePercentage >= 80
                                    ? 'high-usage'
                                    : diskUsagePercentage >= 50
                                      ? 'medium-usage'
                                      : 'low-usage',
                            ]"
                            :style="{ width: diskUsagePercentage + '%' }"
                        ></div>
                    </div>
                </div>

                <!-- CPU -->
                <div
                    class="resource-card flex flex-col bg-dark-800/30 backdrop-blur-sm rounded-xl p-4 hover:bg-dark-800/50 transition-all duration-300 border border-gray-700/30 hover:border-indigo-500/30"
                >
                    <div class="flex items-center gap-3 mb-3">
                        <div class="icon-container">
                            <Cpu class="resource-icon" />
                        </div>
                        <span class="text-gray-400 text-sm font-medium">{{
                            t('Components.ResourceCards.CPU.title')
                        }}</span>
                    </div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex flex-col">
                            <span class="resource-value">{{ resources.cpu }}%</span>
                            <span class="resource-limit"
                                >{{ t('Components.ResourceCards.of') }} {{ Session.getInfoInt('cpu_limit') }}%</span
                            >
                        </div>
                        <div
                            class="percentage-badge"
                            :class="[
                                cpuUsagePercentage >= 80
                                    ? 'high-usage'
                                    : cpuUsagePercentage >= 50
                                      ? 'medium-usage'
                                      : 'low-usage',
                            ]"
                        >
                            {{ Math.round(cpuUsagePercentage) }}%
                        </div>
                    </div>
                    <div class="progress-container">
                        <div
                            class="progress-bar"
                            :class="[
                                cpuUsagePercentage >= 80
                                    ? 'high-usage'
                                    : cpuUsagePercentage >= 50
                                      ? 'medium-usage'
                                      : 'low-usage',
                            ]"
                            :style="{ width: cpuUsagePercentage + '%' }"
                        ></div>
                    </div>
                </div>

                <!-- Server Slots -->
                <div
                    class="resource-card flex flex-col bg-dark-800/30 backdrop-blur-sm rounded-xl p-4 hover:bg-dark-800/50 transition-all duration-300 border border-gray-700/30 hover:border-indigo-500/30"
                >
                    <div class="flex items-center gap-3 mb-3">
                        <div class="icon-container">
                            <Server class="resource-icon" />
                        </div>
                        <span class="text-gray-400 text-sm font-medium">{{
                            t('Components.ResourceCards.SLOT.title')
                        }}</span>
                    </div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex flex-col">
                            <span class="resource-value">{{ serverSlots.used }}</span>
                            <span class="resource-limit"
                                >{{ t('Components.ResourceCards.of') }} {{ Session.getInfoInt('server_limit') }}</span
                            >
                        </div>
                        <div
                            class="percentage-badge"
                            :class="[
                                (serverSlots.used / serverSlots.total) * 100 >= 80
                                    ? 'high-usage'
                                    : (serverSlots.used / serverSlots.total) * 100 >= 50
                                      ? 'medium-usage'
                                      : 'low-usage',
                            ]"
                        >
                            {{ Math.round((serverSlots.used / serverSlots.total) * 100) }}%
                        </div>
                    </div>
                    <div class="progress-container">
                        <div
                            class="progress-bar"
                            :class="[
                                (serverSlots.used / serverSlots.total) * 100 >= 80
                                    ? 'high-usage'
                                    : (serverSlots.used / serverSlots.total) * 100 >= 50
                                      ? 'medium-usage'
                                      : 'low-usage',
                            ]"
                            :style="{ width: (serverSlots.used / serverSlots.total) * 100 + '%' }"
                        ></div>
                    </div>
                </div>
            </div>

            <!-- Second row with 3 cards centered -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 relative z-10 lg:mx-auto">
                <!-- Server Backups -->
                <div
                    class="resource-card flex flex-col bg-dark-800/30 backdrop-blur-sm rounded-xl p-4 hover:bg-dark-800/50 transition-all duration-300 border border-gray-700/30 hover:border-indigo-500/30"
                >
                    <div class="flex items-center gap-3 mb-3">
                        <div class="icon-container">
                            <SaveAll class="resource-icon" />
                        </div>
                        <span class="text-gray-400 text-sm font-medium">{{
                            t('Components.ResourceCards.BACKUP.title')
                        }}</span>
                    </div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex flex-col">
                            <span class="resource-value">{{ backups.used }}</span>
                            <span class="resource-limit"
                                >{{ t('Components.ResourceCards.of') }} {{ backups.total }}</span
                            >
                        </div>
                        <div
                            class="percentage-badge"
                            :class="[
                                (backups.used / backups.total) * 100 >= 80
                                    ? 'high-usage'
                                    : (backups.used / backups.total) * 100 >= 50
                                      ? 'medium-usage'
                                      : 'low-usage',
                            ]"
                        >
                            {{ Math.round((backups.used / backups.total) * 100) }}%
                        </div>
                    </div>
                    <div class="progress-container">
                        <div
                            class="progress-bar"
                            :class="[
                                (backups.used / backups.total) * 100 >= 80
                                    ? 'high-usage'
                                    : (backups.used / backups.total) * 100 >= 50
                                      ? 'medium-usage'
                                      : 'low-usage',
                            ]"
                            :style="{ width: (backups.used / backups.total) * 100 + '%' }"
                        ></div>
                    </div>
                </div>

                <!-- Server Allocations -->
                <div
                    class="resource-card flex flex-col bg-dark-800/30 backdrop-blur-sm rounded-xl p-4 hover:bg-dark-800/50 transition-all duration-300 border border-gray-700/30 hover:border-indigo-500/30"
                >
                    <div class="flex items-center gap-3 mb-3">
                        <div class="icon-container">
                            <Network class="resource-icon" />
                        </div>
                        <span class="text-gray-400 text-sm font-medium">{{
                            t('Components.ResourceCards.ALLOCATION.title')
                        }}</span>
                    </div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex flex-col">
                            <span class="resource-value">{{ allocations.used }}</span>
                            <span class="resource-limit"
                                >{{ t('Components.ResourceCards.of') }} {{ allocations.total }}</span
                            >
                        </div>
                        <div
                            class="percentage-badge"
                            :class="[
                                (allocations.used / allocations.total) * 100 >= 80
                                    ? 'high-usage'
                                    : (allocations.used / allocations.total) * 100 >= 50
                                      ? 'medium-usage'
                                      : 'low-usage',
                            ]"
                        >
                            {{ Math.round((allocations.used / allocations.total) * 100) }}%
                        </div>
                    </div>
                    <div class="progress-container">
                        <div
                            class="progress-bar"
                            :class="[
                                (allocations.used / allocations.total) * 100 >= 80
                                    ? 'high-usage'
                                    : (allocations.used / allocations.total) * 100 >= 50
                                      ? 'medium-usage'
                                      : 'low-usage',
                            ]"
                            :style="{ width: (allocations.used / allocations.total) * 100 + '%' }"
                        ></div>
                    </div>
                </div>

                <!-- Server Databases -->
                <div
                    class="resource-card flex flex-col bg-dark-800/30 backdrop-blur-sm rounded-xl p-4 hover:bg-dark-800/50 transition-all duration-300 border border-gray-700/30 hover:border-indigo-500/30"
                >
                    <div class="flex items-center gap-3 mb-3">
                        <div class="icon-container">
                            <Database class="resource-icon" />
                        </div>
                        <span class="text-gray-400 text-sm font-medium">{{
                            t('Components.ResourceCards.DATABASE.title')
                        }}</span>
                    </div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex flex-col">
                            <span class="resource-value">{{ databases.used }}</span>
                            <span class="resource-limit"
                                >{{ t('Components.ResourceCards.of') }} {{ databases.total }}</span
                            >
                        </div>
                        <div
                            class="percentage-badge"
                            :class="[
                                (databases.used / databases.total) * 100 >= 80
                                    ? 'high-usage'
                                    : (databases.used / databases.total) * 100 >= 50
                                      ? 'medium-usage'
                                      : 'low-usage',
                            ]"
                        >
                            {{ Math.round((databases.used / databases.total) * 100) }}%
                        </div>
                    </div>
                    <div class="progress-container">
                        <div
                            class="progress-bar"
                            :class="[
                                (databases.used / databases.total) * 100 >= 80
                                    ? 'high-usage'
                                    : (databases.used / databases.total) * 100 >= 50
                                      ? 'medium-usage'
                                      : 'low-usage',
                            ]"
                            :style="{ width: (databases.used / databases.total) * 100 + '%' }"
                        ></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Layout -->
        <div
            v-else-if="preferredLayout === 'table'"
            class="relative overflow-x-auto rounded-lg border border-gray-800 bg-gray-900/30"
        >
            <table class="w-full text-sm text-left text-gray-300">
                <thead class="text-xs uppercase bg-gray-800/50">
                    <tr>
                        <th class="px-6 py-3">Resource</th>
                        <th class="px-6 py-3">Used</th>
                        <th class="px-6 py-3">Limit</th>
                        <th class="px-6 py-3">Usage</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="px-6 py-4">{{ t('Components.ResourceCards.RAM.title') }}</td>
                        <td class="px-6 py-4">{{ resources.memory }}{{ t('Components.ResourceCards.mb') }}</td>
                        <td class="px-6 py-4">
                            {{ Session.getInfoInt('memory_limit') }}{{ t('Components.ResourceCards.mb') }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="w-32 h-2 bg-gray-700 rounded-full overflow-hidden">
                                <div
                                    class="h-full bg-indigo-500 rounded-full"
                                    :style="{ width: Math.min(memoryUsagePercentage, 100) + '%' }"
                                ></div>
                            </div>
                            <span class="ml-2 text-xs">{{ Math.round(memoryUsagePercentage) }}%</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4">{{ t('Components.ResourceCards.DISK.title') }}</td>
                        <td class="px-6 py-4">{{ resources.disk }}{{ t('Components.ResourceCards.mb') }}</td>
                        <td class="px-6 py-4">
                            {{ Session.getInfoInt('disk_limit') }}{{ t('Components.ResourceCards.mb') }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="w-32 h-2 bg-gray-700 rounded-full overflow-hidden">
                                <div
                                    class="h-full bg-indigo-500 rounded-full"
                                    :style="{ width: Math.min(diskUsagePercentage, 100) + '%' }"
                                ></div>
                            </div>
                            <span class="ml-2 text-xs">{{ Math.round(diskUsagePercentage) }}%</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4">{{ t('Components.ResourceCards.CPU.title') }}</td>
                        <td class="px-6 py-4">{{ resources.cpu }}%</td>
                        <td class="px-6 py-4">{{ Session.getInfoInt('cpu_limit') }}%</td>
                        <td class="px-6 py-4">
                            <div class="w-32 h-2 bg-gray-700 rounded-full overflow-hidden">
                                <div
                                    class="h-full bg-indigo-500 rounded-full"
                                    :style="{ width: Math.min(cpuUsagePercentage, 100) + '%' }"
                                ></div>
                            </div>
                            <span class="ml-2 text-xs">{{ Math.round(cpuUsagePercentage) }}%</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4">{{ t('Components.ResourceCards.SLOT.title') }}</td>
                        <td class="px-6 py-4">{{ serverSlots.used }}</td>
                        <td class="px-6 py-4">{{ serverSlots.total }}</td>
                        <td class="px-6 py-4">
                            <div class="w-32 h-2 bg-gray-700 rounded-full overflow-hidden">
                                <div
                                    class="h-full bg-indigo-500 rounded-full"
                                    :style="{
                                        width: Math.min((serverSlots.used / serverSlots.total) * 100, 100) + '%',
                                    }"
                                ></div>
                            </div>
                            <span class="ml-2 text-xs"
                                >{{ Math.round((serverSlots.used / serverSlots.total) * 100) }}%</span
                            >
                        </td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4">{{ t('Components.ResourceCards.BACKUP.title') }}</td>
                        <td class="px-6 py-4">{{ backups.used }}</td>
                        <td class="px-6 py-4">{{ t('Components.ResourceCards.of') }} {{ backups.total }}</td>
                        <td class="px-6 py-4">
                            <div class="w-32 h-2 bg-gray-700 rounded-full overflow-hidden">
                                <div
                                    class="h-full bg-indigo-500 rounded-full"
                                    :style="{ width: Math.min((backups.used / backups.total) * 100, 100) + '%' }"
                                ></div>
                            </div>
                            <span class="ml-2 text-xs">{{ Math.round((backups.used / backups.total) * 100) }}%</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4">{{ t('Components.ResourceCards.ALLOCATION.title') }}</td>
                        <td class="px-6 py-4">{{ allocations.used }}</td>
                        <td class="px-6 py-4">{{ allocations.total }}</td>
                        <td class="px-6 py-4">
                            <div class="w-32 h-2 bg-gray-700 rounded-full overflow-hidden">
                                <div
                                    class="h-full bg-indigo-500 rounded-full"
                                    :style="{
                                        width: Math.min((allocations.used / allocations.total) * 100, 100) + '%',
                                    }"
                                ></div>
                            </div>
                            <span class="ml-2 text-xs"
                                >{{ Math.round((allocations.used / allocations.total) * 100) }}%</span
                            >
                        </td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4">{{ t('Components.ResourceCards.DATABASE.title') }}</td>
                        <td class="px-6 py-4">{{ databases.used }}</td>
                        <td class="px-6 py-4">{{ t('Components.ResourceCards.of') }} {{ databases.total }}</td>
                        <td class="px-6 py-4">
                            <div class="w-32 h-2 bg-gray-700 rounded-full overflow-hidden">
                                <div
                                    class="h-full bg-indigo-500 rounded-full"
                                    :style="{ width: Math.min((databases.used / databases.total) * 100, 100) + '%' }"
                                ></div>
                            </div>
                            <span class="ml-2 text-xs"
                                >{{ Math.round((databases.used / databases.total) * 100) }}%</span
                            >
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Compact Layout -->
        <div v-else-if="preferredLayout === 'compact'" class="space-y-2">
            <div class="flex flex-col gap-2">
                <div class="flex items-center justify-between bg-gray-900/40 border border-gray-800 rounded-lg p-3">
                    <div class="flex items-center gap-2">
                        <CircuitBoard class="w-5 h-5 text-indigo-400" />
                        <span class="text-gray-300">{{ t('Components.ResourceCards.RAM.title') }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-white font-semibold"
                            >{{ resources.memory }}{{ t('Components.ResourceCards.mb') }}</span
                        >
                        <span class="text-xs text-gray-400"
                            >/ {{ Session.getInfoInt('memory_limit') }}{{ t('Components.ResourceCards.mb') }}</span
                        >
                        <span class="ml-2 text-xs text-indigo-400">{{ Math.round(memoryUsagePercentage) }}%</span>
                    </div>
                </div>
                <div class="flex items-center justify-between bg-gray-900/40 border border-gray-800 rounded-lg p-3">
                    <div class="flex items-center gap-2">
                        <HardDrive class="w-5 h-5 text-indigo-400" />
                        <span class="text-gray-300">{{ t('Components.ResourceCards.DISK.title') }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-white font-semibold"
                            >{{ resources.disk }}{{ t('Components.ResourceCards.mb') }}</span
                        >
                        <span class="text-xs text-gray-400"
                            >/ {{ Session.getInfoInt('disk_limit') }}{{ t('Components.ResourceCards.mb') }}</span
                        >
                        <span class="ml-2 text-xs text-indigo-400">{{ Math.round(diskUsagePercentage) }}%</span>
                    </div>
                </div>
                <div class="flex items-center justify-between bg-gray-900/40 border border-gray-800 rounded-lg p-3">
                    <div class="flex items-center gap-2">
                        <Cpu class="w-5 h-5 text-indigo-400" />
                        <span class="text-gray-300">{{ t('Components.ResourceCards.CPU.title') }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-white font-semibold">{{ resources.cpu }}%</span>
                        <span class="text-xs text-gray-400">/ {{ Session.getInfoInt('cpu_limit') }}%</span>
                        <span class="ml-2 text-xs text-indigo-400">{{ Math.round(cpuUsagePercentage) }}%</span>
                    </div>
                </div>
                <div class="flex items-center justify-between bg-gray-900/40 border border-gray-800 rounded-lg p-3">
                    <div class="flex items-center gap-2">
                        <Server class="w-5 h-5 text-indigo-400" />
                        <span class="text-gray-300">{{ t('Components.ResourceCards.SLOT.title') }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-white font-semibold">{{ serverSlots.used }}</span>
                        <span class="text-xs text-gray-400">/ {{ serverSlots.total }}</span>
                        <span class="ml-2 text-xs text-indigo-400"
                            >{{ Math.round((serverSlots.used / serverSlots.total) * 100) }}%</span
                        >
                    </div>
                </div>
                <div class="flex items-center justify-between bg-gray-900/40 border border-gray-800 rounded-lg p-3">
                    <div class="flex items-center gap-2">
                        <SaveAll class="w-5 h-5 text-indigo-400" />
                        <span class="text-gray-300">{{ t('Components.ResourceCards.BACKUP.title') }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-white font-semibold">{{ backups.used }}</span>
                        <span class="text-xs text-gray-400">/ {{ backups.total }}</span>
                        <span class="ml-2 text-xs text-indigo-400"
                            >{{ Math.round((backups.used / backups.total) * 100) }}%</span
                        >
                    </div>
                </div>
                <div class="flex items-center justify-between bg-gray-900/40 border border-gray-800 rounded-lg p-3">
                    <div class="flex items-center gap-2">
                        <Network class="w-5 h-5 text-indigo-400" />
                        <span class="text-gray-300">{{ t('Components.ResourceCards.ALLOCATION.title') }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-white font-semibold">{{ allocations.used }}</span>
                        <span class="text-xs text-gray-400">/ {{ allocations.total }}</span>
                        <span class="ml-2 text-xs text-indigo-400"
                            >{{ Math.round((allocations.used / allocations.total) * 100) }}%</span
                        >
                    </div>
                </div>
                <div class="flex items-center justify-between bg-gray-900/40 border border-gray-800 rounded-lg p-3">
                    <div class="flex items-center gap-2">
                        <Database class="w-5 h-5 text-indigo-400" />
                        <span class="text-gray-300">{{ t('Components.ResourceCards.DATABASE.title') }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-white font-semibold">{{ databases.used }}</span>
                        <span class="text-xs text-gray-400">/ {{ databases.total }}</span>
                        <span class="ml-2 text-xs text-indigo-400"
                            >{{ Math.round((databases.used / databases.total) * 100) }}%</span
                        >
                    </div>
                </div>
            </div>
        </div>
    </CardComponent>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import {
    CircuitBoard,
    HardDrive,
    Cpu,
    Server,
    SaveAll,
    Network,
    Database,
    LayoutGrid as GridIcon,
    Table as TableIcon,
    List as ListIcon,
} from 'lucide-vue-next';
import CardComponent from '../../ui/Card/CardComponent.vue';
import Servers from '@/mythicaldash/Pterodactyl/Servers';
import Session from '@/mythicaldash/Session';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
// Initialize resource states
const resources = ref({
    memory: 0,
    cpu: 0,
    disk: 0,
    backups: 0,
    databases: 0,
    allocations: 0,
    servers: 0,
});

// Layout switcher logic
const preferredLayout = ref(localStorage.getItem('resource_cards_layout') || 'cards');
const toggleLayout = () => {
    const layouts = ['cards', 'table', 'compact'];
    const currentIndex = layouts.indexOf(preferredLayout.value);
    const nextIndex = (currentIndex + 1) % layouts.length;
    const nextLayout = layouts[nextIndex];
    if (nextLayout) {
        preferredLayout.value = nextLayout;
        localStorage.setItem('resource_cards_layout', preferredLayout.value);
    }
};

// Computed properties for usage percentages
const memoryUsagePercentage = computed(() => (resources.value.memory / Session.getInfoInt('memory_limit')) * 100 || 0);
const diskUsagePercentage = computed(() => (resources.value.disk / Session.getInfoInt('disk_limit')) * 100 || 0);
const cpuUsagePercentage = computed(() => (resources.value.cpu / Session.getInfoInt('cpu_limit')) * 100 || 0);

// Computed properties for feature limits
const serverSlots = computed(() => ({
    used: resources.value.servers,
    total: Session.getInfoInt('server_limit'),
}));

const backups = computed(() => ({
    used: resources.value.backups,
    total: Session.getInfoInt('backup_limit'),
}));

const allocations = computed(() => ({
    used: resources.value.allocations,
    total: Session.getInfoInt('allocation_limit'),
}));

const databases = computed(() => ({
    used: resources.value.databases,
    total: Session.getInfoInt('database_limit'),
}));

// Function to fetch data
const fetchData = async () => {
    try {
        const [resourcesData] = await Promise.all([Servers.getPterodactylResources()]);
        if (resourcesData) {
            resources.value = resourcesData;
        }
    } catch (error) {
        console.error('Failed to fetch data:', error);
    }
};

// Fetch data when component mounts
onMounted(() => {
    fetchData();
});
</script>

<style scoped>
.resource-card {
    position: relative;
    overflow: hidden;
    transition:
        transform 0.3s ease,
        box-shadow 0.3s ease;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.resource-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
}

.icon-container {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.2), rgba(79, 70, 229, 0.1));
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    transition: transform 0.3s ease;
}

.resource-card:hover .icon-container {
    transform: scale(1.05);
}

.resource-icon {
    width: 18px;
    height: 18px;
    color: #818cf8;
}

.resource-value {
    font-size: 1.125rem;
    font-weight: 600;
    color: white;
    transition: color 0.3s ease;
}

.resource-card:hover .resource-value {
    color: #818cf8;
}

.resource-limit {
    font-size: 0.75rem;
    color: #6b7280;
}

.percentage-badge {
    font-size: 0.75rem;
    font-weight: 500;
    padding: 0.25rem 0.625rem;
    border-radius: 9999px;
    backdrop-filter: blur(4px);
}

.percentage-badge.low-usage {
    background-color: rgba(59, 130, 246, 0.1);
    color: #60a5fa;
    border: 1px solid rgba(59, 130, 246, 0.2);
}

.percentage-badge.medium-usage {
    background-color: rgba(234, 179, 8, 0.1);
    color: #fcd34d;
    border: 1px solid rgba(234, 179, 8, 0.2);
}

.percentage-badge.high-usage {
    background-color: rgba(239, 68, 68, 0.1);
    color: #f87171;
    border: 1px solid rgba(239, 68, 68, 0.2);
}

.progress-container {
    height: 6px;
    background-color: rgba(55, 65, 81, 0.5);
    border-radius: 3px;
    overflow: hidden;
    position: relative;
}

.progress-bar {
    height: 100%;
    border-radius: 3px;
    position: relative;
    overflow: hidden;
    transition: width 0.5s ease-out;
}

.progress-bar.low-usage {
    background: linear-gradient(90deg, #3b82f6, #60a5fa);
}

.progress-bar.medium-usage {
    background: linear-gradient(90deg, #eab308, #fcd34d);
}

.progress-bar.high-usage {
    background: linear-gradient(90deg, #ef4444, #f87171);
}

@media (min-width: 1024px) {
    .lg\:grid-cols-3 {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}
</style>
