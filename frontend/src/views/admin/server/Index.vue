<template>
    <LayoutDashboard>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-400">Server List</h1>
            <button
                @click="goToCreation()"
                class="bg-gradient-to-r from-pink-500 to-violet-500 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:opacity-80 flex items-center"
            >
                <PlusIcon class="w-4 h-4 mr-2" />
                Create New Server
            </button>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="bg-gray-800/50 backdrop-blur-md rounded-lg p-4 shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Total Servers</p>
                        <h3 class="text-2xl font-bold text-white">{{ stats.total || 0 }}</h3>
                    </div>
                    <ServerIcon class="h-8 w-8 text-pink-400" />
                </div>
            </div>
            <div class="bg-gray-800/50 backdrop-blur-md rounded-lg p-4 shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Suspended</p>
                        <h3 class="text-2xl font-bold text-red-400">{{ stats.suspended || 0 }}</h3>
                    </div>
                    <AlertTriangleIcon class="h-8 w-8 text-red-400" />
                </div>
            </div>
        </div>

        <div v-if="loading" class="flex justify-center items-center py-10">
            <LoaderCircle class="h-8 w-8 animate-spin text-pink-400" />
        </div>
        <TableTanstack v-else :data="servers" :columns="columns" tableName="Server List" />
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, onMounted, h } from 'vue';
import LayoutDashboard from '@/components/admin/LayoutDashboard.vue';
import TableTanstack from '@/components/client/ui/Table/TableTanstack.vue';
import {
    PlusIcon,
    TrashIcon,
    LoaderCircle,
    ServerIcon,
    AlertTriangleIcon,
    ExternalLinkIcon,
    PauseIcon,
} from 'lucide-vue-next';
import { useRouter } from 'vue-router';
import Swal from 'sweetalert2';
import { useSound } from '@vueuse/sound';
import failedAlertSfx from '@/assets/sounds/error.mp3';
import successAlertSfx from '@/assets/sounds/success.mp3';
import ServerList from '@/mythicaldash/admin/ServerList';
import { useSettingsStore } from '@/stores/settings';

const Settings = useSettingsStore();

// Server interface matching the API response
interface Server {
    object: string;
    attributes: {
        id: number;
        external_id: string;
        uuid: string;
        identifier: string;
        name: string;
        description: string;
        status: string | null;
        suspended: boolean;
        limits: {
            memory: number;
            swap: number;
            disk: number;
            io: number;
            cpu: number;
            threads: number | null;
            oom_disabled: boolean;
        };
        feature_limits: {
            databases: number;
            allocations: number;
            backups: number;
        };
        user: number;
        node: number;
        allocation: number;
        nest: number;
        egg: number;
        container: {
            startup_command: string;
            image: string;
            installed: number;
            environment: Record<string, string>;
        };
        updated_at: string;
        created_at: string;
    };
    location: {
        id: number;
        name: string;
        description: string;
        pterodactyl_location_id: number;
        node_ip: string;
        status: string;
        slots: number;
        deleted: string;
        locked: string;
        updated_at: string;
        created_at: string;
    };
    service: {
        id: number;
        name: string;
        description: string;
        category: number;
        pterodactyl_egg_id: number;
        enabled: string;
        deleted: string;
        locked: string;
        updated_at: string;
        created_at: string;
    };
    category: {
        id: number;
        name: string;
        description: string;
        pterodactyl_nest_id: number;
        enabled: string;
        deleted: string;
        locked: string;
        updated_at: string;
        created_at: string;
    };
}

interface ServerStats {
    total: number;
    suspended: number;
}

interface CellInfo {
    getValue: () => unknown;
    row: {
        original: Server;
    };
}

const router = useRouter();
const servers = ref<Server[]>([]);
const loading = ref(true);
const stats = ref<ServerStats>({
    total: 0,
    suspended: 0,
});

const { play: playError } = useSound(failedAlertSfx);
const { play: playSuccess } = useSound(successAlertSfx);

// Define columns for TableTanstack
const columns = [
    {
        header: 'ID',
        accessorKey: 'attributes.id',
        cell: (info: CellInfo) => info.getValue(),
    },
    {
        header: 'Name',
        accessorKey: 'attributes.name',
        cell: (info: CellInfo) => info.getValue(),
    },
    {
        header: 'Service',
        id: 'service',
        cell: (info: CellInfo) => {
            const service = info.row.original.service;
            return service ? service.name : 'Unknown';
        },
    },
    {
        header: 'Location',
        id: 'location',
        cell: (info: CellInfo) => {
            const location = info.row.original.location;
            return location ? location.name : 'Unknown';
        },
    },
    {
        header: 'Resources',
        id: 'resources',
        cell: (info: CellInfo) => {
            const item = info.row.original.attributes;
            return h('div', { class: 'text-xs' }, [
                h('div', { class: 'flex items-center space-x-2' }, [
                    h('span', { class: 'text-gray-400' }, 'RAM:'),
                    h('span', { class: 'text-white' }, `${item.limits.memory} MB`),
                    h('span', { class: 'text-gray-400 ml-2' }, 'CPU:'),
                    h('span', { class: 'text-white' }, `${item.limits.cpu}%`),
                    h('span', { class: 'text-gray-400 ml-2' }, 'Disk:'),
                    h('span', { class: 'text-white' }, `${item.limits.disk} MB`),
                ]),
            ]);
        },
    },
    {
        header: 'Status',
        id: 'status',
        cell: (info: CellInfo) => {
            const suspended = info.row.original.attributes.suspended;
            let statusClass = '';
            let statusText = '';

            if (suspended) {
                statusClass = 'bg-red-500/20 text-red-400';
                statusText = 'Suspended';
            } else {
                statusClass = 'bg-green-500/20 text-green-400';
                statusText = 'Active';
            }

            return h('span', { class: `px-2 py-1 rounded-full text-xs ${statusClass}` }, statusText);
        },
    },
    {
        header: 'Created',
        accessorKey: 'attributes.created_at',
        cell: (info: CellInfo) => {
            const date = new Date(info.getValue() as string);
            return date.toLocaleString();
        },
    },
    {
        header: 'Actions',
        id: 'actions',
        cell: (info: CellInfo) => {
            const item = info.row.original;
            const actions = [];

            // Go to Panel button
            actions.push(
                h(
                    'button',
                    {
                        class: 'p-1 text-blue-400 hover:text-blue-300 transition-colors',
                        title: 'Go to Panel',
                        onClick: () => goToPanel(item),
                    },
                    [h(ExternalLinkIcon, { class: 'h-4 w-4' })],
                ),
            );

            // Suspend/Unsuspend button
            actions.push(
                h(
                    'button',
                    {
                        class: 'p-1 text-yellow-400 hover:text-yellow-300 transition-colors',
                        title: item.attributes.suspended ? 'Unsuspend' : 'Suspend',
                        onClick: () => toggleSuspend(item),
                    },
                    [h(PauseIcon, { class: 'h-4 w-4' })],
                ),
            );

            // Delete button
            actions.push(
                h(
                    'button',
                    {
                        class: 'p-1 text-red-400 hover:text-red-300 transition-colors',
                        title: 'Delete',
                        onClick: () => confirmDelete(item),
                    },
                    [h(TrashIcon, { class: 'h-4 w-4' })],
                ),
            );

            return h('div', { class: 'flex space-x-2' }, actions);
        },
    },
];

// Fetch servers from API
const fetchServers = async () => {
    loading.value = true;
    try {
        const response = await ServerList.getList();
        if (response.success) {
            // Check if response has the expected structure
            if (response.servers && response.servers.data) {
                servers.value = response.servers.data;
                // Update stats based on server status
                stats.value = {
                    total: response.servers.meta.pagination.total,
                    suspended: servers.value.filter((item) => item.attributes.suspended).length,
                };
            } else {
                console.error('Invalid server data structure:', response);
                servers.value = [];
                stats.value = {
                    total: 0,
                    suspended: 0,
                };
            }
        } else {
            console.error('Failed to fetch servers:', response);
            servers.value = [];
            stats.value = {
                total: 0,
                suspended: 0,
            };
        }
    } catch (error) {
        console.error('Error fetching servers:', error);
        servers.value = [];
        stats.value = {
            total: 0,
            suspended: 0,
        };
    } finally {
        loading.value = false;
    }
};

const goToCreation = () => {
    router.push('/mc-admin/server-queue/create');
};

const goToPanel = (item: Server) => {
    window.open(Settings.getSetting('pterodactyl_base_url') + `/admin/servers/view/${item.attributes.id}`, '_blank');
};

const toggleSuspend = async (item: Server) => {
    try {
        const response = await ServerList.toggleSuspend(item.attributes.id);
        if (response.success) {
            playSuccess();
            Swal.fire({
                title: 'Success',
                text: `Server has been ${item.attributes.suspended ? 'unsuspended' : 'suspended'}`,
                icon: 'success',
                timer: 2000,
                showConfirmButton: false,
            });
            await fetchServers();
        } else {
            playError();
            Swal.fire({
                title: 'Error',
                text: response.message || 'Failed to toggle suspend status',
                icon: 'error',
            });
        }
    } catch (error) {
        console.error('Error toggling suspend status:', error);
        playError();
        Swal.fire({
            title: 'Error',
            text: 'An unexpected error occurred',
            icon: 'error',
        });
    }
};

const confirmDelete = (item: Server) => {
    Swal.fire({
        title: 'Delete Server',
        text: `Are you sure you want to delete "${item.attributes.name}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it',
        confirmButtonColor: '#ef4444',
        cancelButtonText: 'Cancel',
    }).then(async (result) => {
        if (result.isConfirmed) {
            const response = await ServerList.deleteServer(item.attributes.id);
            if (response.success) {
                playSuccess();
                Swal.fire({
                    title: 'Success',
                    text: 'Server deleted successfully',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false,
                });
                await fetchServers();
            } else {
                playError();
                Swal.fire({
                    title: 'Error',
                    text: response.message || 'Failed to delete server',
                    icon: 'error',
                });
            }
        }
    });
};

onMounted(async () => {
    await fetchServers();
});
</script>
