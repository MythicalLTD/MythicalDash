<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import {
    Server,
    Plus as PlusIcon,
    Pencil as PencilIcon,
    Trash as TrashIcon,
    ExternalLink as ExternalLinkIcon,
    RefreshCcw as RefreshCcwIcon,
    LayoutGrid as GridIcon,
    Table as TableIcon,
    List as ListIcon,
    Search as SearchIcon,
    X as XIcon,
} from 'lucide-vue-next';
import CardComponent from '../../ui/Card/CardComponent.vue';
import Servers from '@/mythicaldash/Pterodactyl/Servers';
import Session from '@/mythicaldash/Session';
import Swal from 'sweetalert2';
import { useSettingsStore } from '@/stores/settings';
import { useI18n } from 'vue-i18n';

const Settings = useSettingsStore();
const pterodactylUrl = Settings.getSetting('pterodactyl_base_url');
const serverRenewEnabled = Settings.getSetting('server_renew_enabled');

// Add layout preference with localStorage
const preferredLayout = ref(localStorage.getItem('server_list_layout') || 'cards');

const toggleLayout = () => {
    const layouts = ['cards', 'table', 'compact'];
    const currentIndex = layouts.indexOf(preferredLayout.value);
    const nextIndex = (currentIndex + 1) % layouts.length;
    const nextLayout = layouts[nextIndex];
    if (nextLayout) {
        preferredLayout.value = nextLayout;
        localStorage.setItem('server_list_layout', preferredLayout.value);
    }
};

const isServersEnabled = computed(() => {
    return Settings.getSetting('allow_servers') === 'true';
});

const { t } = useI18n();

// Define server interface
interface ServerLimits {
    memory: number;
    cpu: number;
    disk: number;
}

interface ServerFeatureLimits {
    backups: number;
    databases: number;
    allocations: number;
}

interface Server {
    id: string;
    identifier: string;
    name: string;
    description: string;
    suspended: boolean;
    limits: ServerLimits;
    feature_limits: ServerFeatureLimits;
    node: string;
    allocation: number;
    created_at: string;
    updated_at: string;
    location?: {
        name: string;
    };
    service?: {
        name: string;
    };
    category?: {
        name: string;
    };
}

interface QueuedServer {
    id: string;
    name: string;
    description: string;
    status: 'pending' | 'failed' | 'building';
    limits: ServerLimits;
    feature_limits: ServerFeatureLimits;
    location?: {
        name: string;
    };
    service?: {
        name: string;
    };
    category?: {
        name: string;
    };
    created_at: string;
    updated_at: string;
}

const router = useRouter();
const loading = ref(true);
const servers = ref<Server[]>([]);
const queuedServers = ref<QueuedServer[]>([]);

// Add search functionality
const searchQuery = ref('');

// Computed filtered servers
const filteredServers = computed(() => {
    if (!searchQuery.value.trim()) {
        return [...servers.value, ...queuedServers.value];
    }

    const query = searchQuery.value.toLowerCase().trim();

    const allServers = [...servers.value, ...queuedServers.value];

    return allServers.filter((server) => {
        // Search by name
        if (server.name.toLowerCase().includes(query)) return true;

        // Search by identifier
        const identifier = getServerIdentifier(server);
        if (identifier.toLowerCase().includes(query)) return true;

        // Search by location
        if (server.location?.name?.toLowerCase().includes(query)) return true;

        // Search by service/egg
        if (server.service?.name?.toLowerCase().includes(query)) return true;

        // Search by category
        if (server.category?.name?.toLowerCase().includes(query)) return true;

        return false;
    });
});

// Clear search
const clearSearch = () => {
    searchQuery.value = '';
};

// Format bytes to human readable
const formatBytes = (bytes: number) => {
    if (bytes === 0) return '0 MB';
    const k = 1024;
    const sizes = ['MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

// Navigation
const createServer = () => {
    router.push('/server/create');
};
// Fetch servers
const fetchServers = async () => {
    try {
        const data = await Servers.getPterodactylServers();
        const dataQueued = await Servers.getPterodactylQueuedServers();
        servers.value = data as Server[];
        queuedServers.value = dataQueued as QueuedServer[];
    } catch (error) {
        console.error('Failed to fetch servers:', error);
    } finally {
        loading.value = false;
    }
};

// Add new methods
const editServer = (identifier: string) => {
    router.push(`/server/${identifier}/update`);
};

const renewServer = (identifier: string) => {
    router.push(`/server/${identifier}/renew`);
};

const deleteServer = async (identifier: string) => {
    const result = await Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this! All server data will be permanently deleted.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#4f46e5',
        cancelButtonColor: '#dc2626',
        confirmButtonText: 'Yes, delete it!',
        background: '#1f2937',
        color: '#fff',
        customClass: {
            popup: 'rounded-lg border border-gray-700',
            confirmButton: 'px-4 py-2 rounded-md text-sm font-medium',
            cancelButton: 'px-4 py-2 rounded-md text-sm font-medium',
        },
    });

    if (result.isConfirmed) {
        router.push(`/server/${identifier}/delete`);
    }
};

const jumpToPanel = (identifier: string) => {
    if (!pterodactylUrl) {
        console.error('Pterodactyl URL not found in settings');
        return;
    }
    window.open(`${pterodactylUrl}/server/${identifier}`, '_blank');
};

// Add new deleteQueuedServer function
const deleteQueuedServer = async (id: string) => {
    const result = await Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this! The queued server will be removed from the queue.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#4f46e5',
        cancelButtonColor: '#dc2626',
        confirmButtonText: 'Yes, delete it!',
        background: '#1f2937',
        color: '#fff',
        customClass: {
            popup: 'rounded-lg border border-gray-700',
            confirmButton: 'px-4 py-2 rounded-md text-sm font-medium',
            cancelButton: 'px-4 py-2 rounded-md text-sm font-medium',
        },
    });

    if (result.isConfirmed) {
        await fetch(`/api/user/queue/${id}/delete`, {
            method: 'POST',
            headers: {
                Authorization: `Bearer ${Session.getInfo('token')}`,
            },
        });
        fetchServers();
        Swal.fire({
            title: 'Success',
            text: 'Server deleted successfully',
            icon: 'success',
            confirmButtonColor: '#4f46e5',
            background: '#1f2937',
        });
    }
};

// Update the delete button click handler
const handleDelete = (server: Server | QueuedServer) => {
    if ('identifier' in server) {
        // For regular servers (they have an identifier)
        deleteServer(server.id);
    } else {
        // For queued servers (they only have an id)
        deleteQueuedServer(server.id);
    }
};

// Update the getServerStatus function
const getServerStatus = (server: Server | QueuedServer) => {
    if ('suspended' in server) {
        return {
            color: server.suspended ? 'bg-red-500' : 'bg-green-500',
            text: server.suspended ? 'Suspended' : 'Active',
        };
    } else {
        switch (server.status) {
            case 'pending':
                return {
                    color: 'bg-yellow-500',
                    text: 'Pending',
                };
            case 'building':
                return {
                    color: 'bg-blue-500',
                    text: 'Building',
                };
            case 'failed':
                return {
                    color: 'bg-red-500',
                    text: 'Failed',
                };
            default:
                return {
                    color: 'bg-gray-500',
                    text: 'Unknown',
                };
        }
    }
};

// Fix the isActionDisabled function
const isActionDisabled = (server: Server | QueuedServer, action: 'panel' | 'edit' | 'renew' | 'delete'): boolean => {
    if ('suspended' in server) {
        // This is a regular server, never disable actions
        return false;
    } else {
        // This is a queued server
        switch (server.status) {
            case 'pending':
            case 'building':
                // Pending and building servers have all actions disabled
                return true;
            case 'failed':
                // Failed servers only allow delete action
                return action !== 'delete';
            default:
                return true;
        }
    }
};

// Add helper function to get server identifier
const getServerIdentifier = (server: Server | QueuedServer): string => {
    return 'identifier' in server ? server.identifier : `Queue #${server.id}`;
};

// Add helper function to get server ID
const getServerId = (server: Server | QueuedServer): string => {
    return 'identifier' in server ? server.id : server.id;
};

onMounted(() => {
    fetchServers();
});
</script>
<template>
    <CardComponent
        :cardTitle="t('Components.ServerList.title')"
        :cardDescription="t('Components.ServerList.description')"
    >
        <div v-if="loading" class="flex justify-center items-center py-12">
            <div class="w-8 h-8 border-4 border-gray-700 border-t-indigo-500 rounded-full animate-spin"></div>
        </div>

        <div
            v-else-if="servers.length === 0 && queuedServers.length === 0 && isServersEnabled"
            class="flex flex-col items-center justify-center py-12 text-center"
        >
            <Server class="w-12 h-12 text-gray-600 mb-3" />
            <h3 class="text-gray-300 font-medium mb-1">{{ t('Components.ServerList.noServers') }}</h3>
            <p class="text-gray-500 text-sm mb-4">{{ t('Components.ServerList.noServersDescription') }}</p>
            <button
                @click="createServer"
                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors flex items-center gap-2"
            >
                <PlusIcon class="w-4 h-4" />
                {{ t('Components.ServerList.createServer') }}
            </button>
        </div>

        <div v-else class="space-y-4">
            <!-- Header with stats, actions, and layout toggle -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-indigo-500/10 rounded-lg">
                        <Server class="w-5 h-5 text-indigo-400" />
                    </div>
                    <div>
                        <h3 class="text-white font-medium">{{ t('Components.ServerList.title') }}</h3>
                        <p class="text-sm text-gray-400">
                            {{
                                t('Components.ServerList.serverLimit', [
                                    filteredServers.length,
                                    Session.getInfoInt('server_limit'),
                                ])
                            }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
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
                    <button
                        @click="createServer"
                        v-if="isServersEnabled"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors flex items-center gap-2"
                        :disabled="servers.length + queuedServers.length >= Session.getInfoInt('server_limit')"
                    >
                        <PlusIcon class="w-4 h-4" />
                        {{ t('Components.ServerList.newServer') }}
                    </button>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="relative mb-6">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <SearchIcon class="h-5 w-5 text-gray-400" />
                    </div>
                    <input
                        v-model="searchQuery"
                        type="text"
                        class="block w-full pl-10 pr-10 py-3 border border-gray-700 rounded-lg bg-gray-800/50 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors"
                        :placeholder="t('Components.ServerList.searchPlaceholder')"
                    />
                    <button
                        v-if="searchQuery"
                        @click="clearSearch"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-300 transition-colors"
                    >
                        <XIcon class="h-5 w-5" />
                    </button>
                </div>
                <div v-if="searchQuery" class="mt-2 text-sm text-gray-400">
                    {{
                        t('Components.ServerList.searchResults', [
                            filteredServers.length,
                            servers.length + queuedServers.length,
                        ])
                    }}
                </div>
            </div>

            <!-- No Search Results -->
            <div
                v-if="searchQuery && filteredServers.length === 0"
                class="flex flex-col items-center justify-center py-12 text-center"
            >
                <SearchIcon class="w-12 h-12 text-gray-600 mb-3" />
                <h3 class="text-gray-300 font-medium mb-1">{{ t('Components.ServerList.noSearchResults') }}</h3>
                <p class="text-gray-500 text-sm mb-4">{{ t('Components.ServerList.noSearchResultsDescription') }}</p>
                <button
                    @click="clearSearch"
                    class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white text-sm font-medium rounded-lg transition-colors flex items-center gap-2"
                >
                    <XIcon class="w-4 h-4" />
                    {{ t('Components.ServerList.clearSearch') }}
                </button>
            </div>

            <!-- Card Layout -->
            <div
                v-if="preferredLayout === 'cards' && filteredServers.length > 0"
                class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4"
            >
                <template v-for="server in filteredServers" :key="getServerId(server)">
                    <div
                        class="group relative bg-gray-900/40 border border-gray-800 rounded-xl p-5 hover:bg-gray-800/40 transition-all duration-200 hover:border-gray-700"
                    >
                        <!-- Server Status Indicator -->
                        <div class="absolute top-4 right-4">
                            <div class="flex items-center gap-2">
                                <div class="flex items-center gap-1.5">
                                    <div class="w-2 h-2 rounded-full" :class="getServerStatus(server).color"></div>
                                    <span class="text-xs text-gray-400">{{ getServerStatus(server).text }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Server Header -->
                        <div class="mb-4">
                            <h3 class="text-lg font-medium text-white mb-1">{{ server.name }}</h3>
                            <p class="text-sm text-gray-400">{{ getServerIdentifier(server) }}</p>
                        </div>

                        <!-- Server Details -->
                        <div class="space-y-3">
                            <div class="grid grid-cols-2 gap-3">
                                <div class="bg-gray-800/50 rounded-lg p-3">
                                    <div class="text-xs text-gray-400 mb-1">
                                        {{ t('Components.ServerList.table.location') }}
                                    </div>
                                    <div class="text-sm text-white">{{ server.location?.name || 'Unknown' }}</div>
                                </div>
                                <div class="bg-gray-800/50 rounded-lg p-3">
                                    <div class="text-xs text-gray-400 mb-1">
                                        {{ t('Components.ServerList.table.egg') }}
                                    </div>
                                    <div class="text-sm text-white">{{ server.service?.name || 'Unknown' }}</div>
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-3">
                                <div class="bg-gray-800/50 rounded-lg p-3">
                                    <div class="text-xs text-gray-400 mb-1">
                                        {{ t('Components.ServerList.table.memory') }}
                                    </div>
                                    <div class="text-sm text-white">{{ formatBytes(server.limits.memory) }}</div>
                                </div>
                                <div class="bg-gray-800/50 rounded-lg p-3">
                                    <div class="text-xs text-gray-400 mb-1">
                                        {{ t('Components.ServerList.table.cpu') }}
                                    </div>
                                    <div class="text-sm text-white">{{ server.limits.cpu }}%</div>
                                </div>
                                <div class="bg-gray-800/50 rounded-lg p-3">
                                    <div class="text-xs text-gray-400 mb-1">
                                        {{ t('Components.ServerList.table.disk') }}
                                    </div>
                                    <div class="text-sm text-white">{{ formatBytes(server.limits.disk) }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-4 pt-4 border-t border-gray-800 flex items-center justify-between">
                            <button
                                class="px-3 py-1.5"
                                :class="
                                    isActionDisabled(server, 'panel')
                                        ? 'bg-gray-700/10 text-gray-500 cursor-not-allowed'
                                        : 'bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-400'
                                "
                                @click="!isActionDisabled(server, 'panel') && jumpToPanel(getServerIdentifier(server))"
                                :disabled="isActionDisabled(server, 'panel')"
                            >
                                <ExternalLinkIcon class="w-4 h-4" />
                                Panel
                            </button>
                            <div class="flex items-center gap-2">
                                <button
                                    class="p-1.5 rounded-lg"
                                    :class="
                                        isActionDisabled(server, 'edit')
                                            ? 'text-gray-500 cursor-not-allowed'
                                            : 'text-gray-400 hover:text-indigo-400 hover:bg-gray-800/50'
                                    "
                                    title="Edit Server"
                                    @click="!isActionDisabled(server, 'edit') && editServer(getServerId(server))"
                                    :disabled="isActionDisabled(server, 'edit')"
                                >
                                    <PencilIcon class="w-4 h-4" />
                                </button>
                                <button
                                    v-if="serverRenewEnabled === 'true'"
                                    class="p-1.5 rounded-lg"
                                    :class="
                                        isActionDisabled(server, 'renew')
                                            ? 'text-gray-500 cursor-not-allowed'
                                            : 'text-gray-400 hover:text-indigo-400 hover:bg-gray-800/50'
                                    "
                                    title="Renew Server"
                                    @click="!isActionDisabled(server, 'renew') && renewServer(getServerId(server))"
                                    :disabled="isActionDisabled(server, 'renew')"
                                >
                                    <RefreshCcwIcon class="w-4 h-4" />
                                </button>
                                <button
                                    class="p-1.5 rounded-lg"
                                    :class="
                                        isActionDisabled(server, 'delete')
                                            ? 'text-gray-500 cursor-not-allowed'
                                            : 'text-gray-400 hover:text-red-400 hover:bg-gray-800/50'
                                    "
                                    title="Delete Server"
                                    @click="handleDelete(server)"
                                    :disabled="isActionDisabled(server, 'delete')"
                                >
                                    <TrashIcon class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Table Layout -->
            <div
                v-else-if="preferredLayout === 'table' && filteredServers.length > 0"
                class="relative overflow-x-auto rounded-lg border border-gray-800"
            >
                <table class="w-full text-sm text-left text-gray-300">
                    <thead class="text-xs uppercase bg-gray-800/50">
                        <tr>
                            <th scope="col" class="px-6 py-3">{{ t('Components.ServerList.table.name') }}</th>
                            <th scope="col" class="px-6 py-3">{{ t('Components.ServerList.table.location') }}</th>
                            <th scope="col" class="px-6 py-3">{{ t('Components.ServerList.table.egg') }}</th>
                            <th scope="col" class="px-6 py-3">{{ t('Components.ServerList.table.memory') }}</th>
                            <th scope="col" class="px-6 py-3">{{ t('Components.ServerList.table.cpu') }}</th>
                            <th scope="col" class="px-6 py-3">{{ t('Components.ServerList.table.disk') }}</th>
                            <th scope="col" class="px-6 py-3">{{ t('Components.ServerList.table.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="server in filteredServers"
                            :key="getServerId(server)"
                            class="border-b border-gray-800 bg-gray-900/20 hover:bg-gray-800/30 transition-colors"
                        >
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 rounded-full" :class="getServerStatus(server).color"></div>
                                    <div>
                                        <div class="font-medium text-white">{{ server.name }}</div>
                                        <div class="text-xs text-gray-400">{{ getServerIdentifier(server) }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-400">
                                {{ server.location?.name || 'Unknown' }}
                            </td>
                            <td class="px-6 py-4 text-gray-400">
                                {{ server.service?.name || 'Unknown' }}
                            </td>
                            <td class="px-6 py-4">
                                {{ formatBytes(server.limits.memory) }}
                            </td>
                            <td class="px-6 py-4">{{ server.limits.cpu }}%</td>
                            <td class="px-6 py-4">
                                {{ formatBytes(server.limits.disk) }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <button
                                        class="p-1.5 rounded-md"
                                        :class="
                                            isActionDisabled(server, 'panel')
                                                ? 'text-gray-500 cursor-not-allowed'
                                                : 'text-gray-400 hover:text-indigo-400 hover:bg-gray-800/50'
                                        "
                                        title="Jump to Panel"
                                        @click="
                                            !isActionDisabled(server, 'panel') &&
                                            jumpToPanel(getServerIdentifier(server))
                                        "
                                        :disabled="isActionDisabled(server, 'panel')"
                                    >
                                        <ExternalLinkIcon class="w-4 h-4" />
                                    </button>
                                    <button
                                        class="p-1.5 rounded-md"
                                        :class="
                                            isActionDisabled(server, 'edit')
                                                ? 'text-gray-500 cursor-not-allowed'
                                                : 'text-gray-400 hover:text-indigo-400 hover:bg-gray-800/50'
                                        "
                                        title="Edit Server"
                                        @click="!isActionDisabled(server, 'edit') && editServer(getServerId(server))"
                                        :disabled="isActionDisabled(server, 'edit')"
                                    >
                                        <PencilIcon class="w-4 h-4" />
                                    </button>
                                    <button
                                        class="p-1.5 rounded-md"
                                        :class="
                                            isActionDisabled(server, 'renew')
                                                ? 'text-gray-500 cursor-not-allowed'
                                                : 'text-gray-400 hover:text-indigo-400 hover:bg-gray-800/50'
                                        "
                                        title="Renew Server"
                                        v-if="serverRenewEnabled === 'true'"
                                        @click="!isActionDisabled(server, 'renew') && renewServer(getServerId(server))"
                                        :disabled="isActionDisabled(server, 'renew')"
                                    >
                                        <RefreshCcwIcon class="w-4 h-4" />
                                    </button>
                                    <button
                                        class="p-1.5 rounded-md"
                                        :class="
                                            isActionDisabled(server, 'delete')
                                                ? 'text-gray-500 cursor-not-allowed'
                                                : 'text-gray-400 hover:text-red-400 hover:bg-gray-800/50'
                                        "
                                        title="Delete Server"
                                        @click="handleDelete(server)"
                                        :disabled="isActionDisabled(server, 'delete')"
                                    >
                                        <TrashIcon class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Compact List Layout -->
            <div v-else-if="preferredLayout === 'compact' && filteredServers.length > 0" class="space-y-2">
                <div
                    v-for="server in filteredServers"
                    :key="getServerId(server)"
                    class="group bg-gray-900/40 border border-gray-800 rounded-lg hover:bg-gray-800/40 transition-all duration-200 hover:border-gray-700"
                >
                    <div class="flex items-center justify-between p-4">
                        <!-- Left side: Server info -->
                        <div class="flex items-center gap-4 flex-1 min-w-0">
                            <div class="flex items-center gap-2 min-w-0">
                                <div
                                    class="w-2 h-2 rounded-full flex-shrink-0"
                                    :class="getServerStatus(server).color"
                                ></div>
                                <div class="min-w-0">
                                    <div class="font-medium text-white truncate">{{ server.name }}</div>
                                    <div class="text-xs text-gray-400 truncate">{{ getServerIdentifier(server) }}</div>
                                </div>
                            </div>
                            <div class="hidden md:flex items-center gap-4 text-sm text-gray-400">
                                <div class="flex items-center gap-1">
                                    <span class="text-xs text-gray-500"
                                        >{{ t('Components.ServerList.table.location') }}:</span
                                    >
                                    <span class="truncate">{{ server.location?.name || 'Unknown' }}</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <span class="text-xs text-gray-500"
                                        >{{ t('Components.ServerList.table.egg') }}:</span
                                    >
                                    <span class="truncate">{{ server.service?.name || 'Unknown' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Middle: Resource usage -->
                        <div class="hidden lg:flex items-center gap-6 text-sm">
                            <div class="flex items-center gap-2">
                                <span class="text-gray-400">{{ formatBytes(server.limits.memory) }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-gray-400">{{ server.limits.cpu }}%</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-gray-400">{{ formatBytes(server.limits.disk) }}</span>
                            </div>
                        </div>

                        <!-- Right side: Actions -->
                        <div class="flex items-center gap-2 ml-4">
                            <button
                                class="p-1.5 rounded-lg"
                                :class="
                                    isActionDisabled(server, 'panel')
                                        ? 'text-gray-500 cursor-not-allowed'
                                        : 'text-gray-400 hover:text-indigo-400 hover:bg-gray-800/50'
                                "
                                title="Jump to Panel"
                                @click="!isActionDisabled(server, 'panel') && jumpToPanel(getServerIdentifier(server))"
                                :disabled="isActionDisabled(server, 'panel')"
                            >
                                <ExternalLinkIcon class="w-4 h-4" />
                            </button>
                            <button
                                class="p-1.5 rounded-lg"
                                :class="
                                    isActionDisabled(server, 'edit')
                                        ? 'text-gray-500 cursor-not-allowed'
                                        : 'text-gray-400 hover:text-indigo-400 hover:bg-gray-800/50'
                                "
                                title="Edit Server"
                                @click="!isActionDisabled(server, 'edit') && editServer(getServerId(server))"
                                :disabled="isActionDisabled(server, 'edit')"
                            >
                                <PencilIcon class="w-4 h-4" />
                            </button>
                            <button
                                v-if="serverRenewEnabled === 'true'"
                                class="p-1.5 rounded-lg"
                                :class="
                                    isActionDisabled(server, 'renew')
                                        ? 'text-gray-500 cursor-not-allowed'
                                        : 'text-gray-400 hover:text-indigo-400 hover:bg-gray-800/50'
                                "
                                title="Renew Server"
                                @click="!isActionDisabled(server, 'renew') && renewServer(getServerId(server))"
                                :disabled="isActionDisabled(server, 'renew')"
                            >
                                <RefreshCcwIcon class="w-4 h-4" />
                            </button>
                            <button
                                class="p-1.5 rounded-lg"
                                :class="
                                    isActionDisabled(server, 'delete')
                                        ? 'text-gray-500 cursor-not-allowed'
                                        : 'text-gray-400 hover:text-red-400 hover:bg-gray-800/50'
                                "
                                title="Delete Server"
                                @click="handleDelete(server)"
                                :disabled="isActionDisabled(server, 'delete')"
                            >
                                <TrashIcon class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </CardComponent>
</template>
