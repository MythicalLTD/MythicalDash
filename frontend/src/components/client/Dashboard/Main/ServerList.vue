<template>
    <CardComponent cardTitle="My Servers" cardDescription="Manage your gaming infrastructure">
        <div v-if="loading" class="flex justify-center items-center py-12">
            <div class="w-8 h-8 border-4 border-gray-700 border-t-indigo-500 rounded-full"></div>
        </div>

        <div v-else-if="servers.length === 0" class="flex flex-col items-center justify-center py-12 text-center">
            <Server class="w-12 h-12 text-gray-600 mb-3" />
            <h3 class="text-gray-300 font-medium mb-1">No Servers Found</h3>
            <p class="text-gray-500 text-sm mb-4">You don't have any servers yet.</p>
            <button
                @click="createServer"
                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors"
            >
                Create Your First Server
            </button>
        </div>

        <div v-else>
            <!-- Create server button -->
            <div class="flex justify-between items-center mb-4">
                <div class="text-sm text-gray-400">
                    {{ servers.length }} server{{ servers.length !== 1 ? 's' : '' }} running
                </div>
                <button
                    @click="createServer"
                    class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md transition-colors flex items-center"
                >
                    <PlusIcon class="w-4 h-4 mr-1.5" />
                    New Server
                </button>
            </div>

            <!-- Server Cards Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div
                    v-for="server in servers"
                    :key="server.id"
                    class="server-card group rounded-xl overflow-hidden border border-gray-800 bg-gray-900/40 hover:border-indigo-500/30 transition-all duration-200"
                >
                    <!-- Server Header -->
                    <div class="p-4 bg-gray-800/50 border-b border-gray-800">
                        <div class="flex justify-between items-start">
                            <div>
                                <div
                                    class="text-lg font-semibold text-white group-hover:text-indigo-400 transition-colors"
                                >
                                    {{ server.name }}
                                </div>
                                <div class="text-gray-400 text-sm mt-0.5">{{ server.type }}</div>
                            </div>

                            <div
                                class="px-2 py-1 rounded-md text-xs font-medium"
                                :class="getStatusClass(server.status)"
                            >
                                {{ server.status }}
                            </div>
                        </div>
                    </div>

                    <!-- Server Body -->
                    <div class="p-4">
                        <!-- Server Node & IP -->
                        <div class="flex justify-between items-center mb-4">
                            <div class="flex items-center text-sm text-gray-400">
                                <ServerIcon class="w-4 h-4 mr-1.5 text-gray-500" />
                                {{ server.node }}
                            </div>
                            <div class="text-xs bg-gray-800 px-2 py-1 rounded text-gray-400">
                                {{ server.ipAddress }}
                            </div>
                        </div>

                        <!-- Resource Meters -->
                        <div class="space-y-3 mb-4">
                            <div>
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="text-gray-400">CPU</span>
                                    <span class="text-gray-300">{{ server.cpu }}</span>
                                </div>
                                <div class="h-1.5 bg-gray-800 rounded-full overflow-hidden">
                                    <div
                                        class="h-full bg-gradient-to-r from-blue-600 to-indigo-600 rounded-full"
                                        :style="`width: ${server.resources.cpuUsage}%`"
                                    ></div>
                                </div>
                            </div>

                            <div>
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="text-gray-400">RAM</span>
                                    <span class="text-gray-300">{{ server.ram }}</span>
                                </div>
                                <div class="h-1.5 bg-gray-800 rounded-full overflow-hidden">
                                    <div
                                        class="h-full bg-gradient-to-r from-violet-600 to-purple-600 rounded-full"
                                        :style="`width: ${(server.resources.memoryUsed / server.resources.memoryTotal) * 100}%`"
                                    ></div>
                                </div>
                            </div>

                            <div>
                                <div class="flex justify-between text-xs mb-1">
                                    <span class="text-gray-400">DISK</span>
                                    <span class="text-gray-300">{{ server.disk }}</span>
                                </div>
                                <div class="h-1.5 bg-gray-800 rounded-full overflow-hidden">
                                    <div
                                        class="h-full bg-gradient-to-r from-emerald-600 to-teal-600 rounded-full"
                                        :style="`width: ${(server.resources.diskUsed / server.resources.diskTotal) * 100}%`"
                                    ></div>
                                </div>
                            </div>
                        </div>

                        <!-- Expandable Details Toggle -->
                        <div class="border-t border-gray-800 pt-3 mt-2">
                            <button
                                @click="toggleExpand(server.id)"
                                class="w-full flex items-center justify-between text-xs font-medium text-gray-400 hover:text-indigo-400 transition-colors"
                            >
                                <span>{{ expandedServer === server.id ? 'Hide Details' : 'View Details' }}</span>
                                <ChevronDownIcon
                                    class="w-4 h-4 transition-transform duration-200"
                                    :class="{ 'transform rotate-180': expandedServer === server.id }"
                                />
                            </button>
                        </div>

                        <!-- Expanded Content -->
                        <div v-if="expandedServer === server.id" class="mt-3 pt-3 border-t border-gray-800 space-y-3">
                            <!-- Additional Server Details -->
                            <div class="bg-gray-800/30 rounded-lg p-3">
                                <h4 class="text-xs font-medium text-indigo-400 mb-2">Server Details</h4>
                                <div class="grid grid-cols-2 gap-2 text-xs">
                                    <div>
                                        <div class="text-gray-500">Server ID</div>
                                        <div class="text-gray-300">{{ server.id }}</div>
                                    </div>
                                    <div>
                                        <div class="text-gray-500">Created</div>
                                        <div class="text-gray-300">{{ server.created }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Activity Logs -->
                            <div class="bg-gray-800/30 rounded-lg p-3">
                                <h4 class="text-xs font-medium text-indigo-400 mb-2">Recent Activity</h4>
                                <div class="space-y-2">
                                    <div class="text-xs flex items-start">
                                        <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 mt-1 mr-2"></div>
                                        <div>
                                            <div class="text-gray-300">Server started</div>
                                            <div class="text-gray-500">2 hours ago</div>
                                        </div>
                                    </div>
                                    <div class="text-xs flex items-start">
                                        <div class="w-1.5 h-1.5 rounded-full bg-amber-500 mt-1 mr-2"></div>
                                        <div>
                                            <div class="text-gray-300">Configuration updated</div>
                                            <div class="text-gray-500">Yesterday</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Server Actions -->
                    <div class="px-4 py-3 bg-gray-800/30 border-t border-gray-800 flex justify-between">
                        <div class="flex space-x-1">
                            <button
                                title="Console"
                                class="p-1.5 rounded bg-gray-800 hover:bg-gray-700 text-gray-400 hover:text-white transition-colors"
                            >
                                <Terminal class="w-4 h-4" />
                            </button>
                            <button
                                title="Restart"
                                class="p-1.5 rounded bg-gray-800 hover:bg-gray-700 text-gray-400 hover:text-white transition-colors"
                            >
                                <RefreshCw class="w-4 h-4" />
                            </button>
                            <button
                                title="Stop"
                                class="p-1.5 rounded bg-gray-800 hover:bg-gray-700 text-gray-400 hover:text-white transition-colors"
                            >
                                <Power class="w-4 h-4" />
                            </button>
                        </div>

                        <button
                            @click.stop="goToServer(server.id)"
                            class="px-3 py-1 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium rounded transition-colors flex items-center"
                        >
                            Manage
                            <ArrowRightIcon class="w-3.5 h-3.5 ml-1" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </CardComponent>
</template>

<script setup lang="ts">
import CardComponent from '@/components/client/ui/Card/CardComponent.vue';
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import {
    Server as ServerIcon,
    Terminal,
    Power,
    RefreshCw,
    ChevronDown as ChevronDownIcon,
    Plus as PlusIcon,
    ArrowRight as ArrowRightIcon,
} from 'lucide-vue-next';

interface ServerResources {
    cpuUsage: number;
    memoryUsed: number;
    memoryTotal: number;
    diskUsed: number;
    diskTotal: number;
}

interface ServerData {
    id: string;
    name: string;
    node: string;
    status: string;
    type: string;
    cpu: string;
    ram: string;
    disk: string;
    ipAddress: string;
    created: string;
    resources: ServerResources;
}

const router = useRouter();
const loading = ref(true);
const servers = ref<ServerData[]>([]);
const expandedServer = ref<string | null>(null);

// Toggle expanded server details
const toggleExpand = (serverId: string) => {
    if (expandedServer.value === serverId) {
        expandedServer.value = null;
    } else {
        expandedServer.value = serverId;
    }
};

// Get status class for styling
const getStatusClass = (status: string) => {
    switch (status.toLowerCase()) {
        case 'installed':
            return 'bg-emerald-500/20 text-emerald-400';
        case 'installing':
            return 'bg-blue-500/20 text-blue-400';
        case 'starting':
            return 'bg-amber-500/20 text-amber-400';
        case 'offline':
            return 'bg-gray-500/20 text-gray-400';
        case 'error':
            return 'bg-red-500/20 text-red-400';
        default:
            return 'bg-gray-500/20 text-gray-400';
    }
};

// Create new server
const createServer = () => {
    router.push('/servers/create');
};

// Navigate to server details
const goToServer = (serverId: string) => {
    router.push(`/server/${serverId}`);
};

onMounted(async () => {
    // Simulating API call
    setTimeout(() => {
        // Mock data
        servers.value = [
            {
                id: 's-abc123',
                name: 'Minecraft Survival',
                node: 'Node-2 (Oracle Cloud)',
                status: 'Installed',
                type: 'Paper 1.19.2',
                cpu: '50%',
                ram: '512MB',
                disk: '1024MB',
                ipAddress: '192.168.1.101:25565',
                created: '2023-05-15',
                resources: {
                    cpuUsage: 35,
                    memoryUsed: 256,
                    memoryTotal: 512,
                    diskUsed: 450,
                    diskTotal: 1024,
                },
            },
            {
                id: 's-def456',
                name: 'Lobby Server',
                node: 'Node-1 (AWS)',
                status: 'Installing',
                type: 'Velocity',
                cpu: '100%',
                ram: '1024MB',
                disk: '2048MB',
                ipAddress: '192.168.1.102:25566',
                created: '2023-06-20',
                resources: {
                    cpuUsage: 15,
                    memoryUsed: 512,
                    memoryTotal: 1024,
                    diskUsed: 1200,
                    diskTotal: 2048,
                },
            },
            {
                id: 's-ghi789',
                name: 'Creative World',
                node: 'Node-3 (GCP)',
                status: 'Offline',
                type: 'Paper 1.20.1',
                cpu: '25%',
                ram: '256MB',
                disk: '512MB',
                ipAddress: '192.168.1.103:25567',
                created: '2023-07-05',
                resources: {
                    cpuUsage: 0,
                    memoryUsed: 0,
                    memoryTotal: 256,
                    diskUsed: 350,
                    diskTotal: 512,
                },
            },
        ];
        loading.value = false;
    }, 1000);
});
</script>

<style scoped>
/* Simple loader animation */
@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.w-8.h-8.border-4 {
    animation: spin 1s linear infinite;
}

.server-card {
    box-shadow:
        0 4px 6px -1px rgba(0, 0, 0, 0.1),
        0 2px 4px -1px rgba(0, 0, 0, 0.06);
    transition: all 0.2s ease;
}

.server-card:hover {
    box-shadow:
        0 10px 15px -3px rgba(0, 0, 0, 0.1),
        0 4px 6px -2px rgba(0, 0, 0, 0.05);
    transform: translateY(-2px);
}
</style>
