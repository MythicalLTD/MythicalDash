<template>
    <CardComponent cardTitle="Resource Usage" cardDescription="View your resource usage and limits">
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
                    <span class="text-gray-400 text-sm font-medium">Ram</span>
                </div>
                <div class="flex items-center justify-between mb-3">
                    <div class="flex flex-col">
                        <span class="resource-value">{{ memoryUsage }}MB</span>
                        <span class="resource-limit">of {{ memoryLimit }}MB</span>
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
                    <span class="text-gray-400 text-sm font-medium">Disk</span>
                </div>
                <div class="flex items-center justify-between mb-3">
                    <div class="flex flex-col">
                        <span class="resource-value">{{ diskUsage }}MB</span>
                        <span class="resource-limit">of {{ diskLimit }}MB</span>
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
                    <span class="text-gray-400 text-sm font-medium">CPU</span>
                </div>
                <div class="flex items-center justify-between mb-3">
                    <div class="flex flex-col">
                        <span class="resource-value">{{ cpuUsage }}%</span>
                        <span class="resource-limit">of {{ cpuLimit }}%</span>
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
                    <span class="text-gray-400 text-sm font-medium">Server Slot(s)</span>
                </div>
                <div class="flex items-center justify-between mb-3">
                    <div class="flex flex-col">
                        <span class="resource-value">{{ serverSlots.used }}</span>
                        <span class="resource-limit">of {{ serverSlots.total }}</span>
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
                    <span class="text-gray-400 text-sm font-medium">Server Backup(s)</span>
                </div>
                <div class="flex items-center justify-between mb-3">
                    <div class="flex flex-col">
                        <span class="resource-value">{{ backups.used }}</span>
                        <span class="resource-limit">of {{ backups.total }}</span>
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
                    <span class="text-gray-400 text-sm font-medium">Server Allocation(s)</span>
                </div>
                <div class="flex items-center justify-between mb-3">
                    <div class="flex flex-col">
                        <span class="resource-value">{{ allocations.used }}</span>
                        <span class="resource-limit">of {{ allocations.total }}</span>
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
                    <span class="text-gray-400 text-sm font-medium">Server Database(s)</span>
                </div>
                <div class="flex items-center justify-between mb-3">
                    <div class="flex flex-col">
                        <span class="resource-value">{{ databases.used }}</span>
                        <span class="resource-limit">of {{ databases.total }}</span>
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
    </CardComponent>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { CircuitBoard, HardDrive, Cpu, Server, SaveAll, Network, Database } from 'lucide-vue-next';
import CardComponent from '../../ui/Card/CardComponent.vue';

const memoryUsage = ref(512);
const memoryLimit = ref(512);
const diskUsage = ref(1024);
const diskLimit = ref(1024);
const cpuUsage = ref(50);
const cpuLimit = ref(50);
const serverSlots = ref({ used: 1, total: 1 });
const backups = ref({ used: 1, total: 1 });
const allocations = ref({ used: 1, total: 1 });
const databases = ref({ used: 1, total: 1 });

const memoryUsagePercentage = computed(() => (memoryUsage.value / memoryLimit.value) * 100);
const diskUsagePercentage = computed(() => (diskUsage.value / diskLimit.value) * 100);
const cpuUsagePercentage = computed(() => (cpuUsage.value / cpuLimit.value) * 100);
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
