<template>
    <LayoutDashboard>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-400">Server Queue</h1>
            <div class="flex space-x-4">
                <button
                    @click="goToCreation()"
                    class="bg-gradient-to-r from-pink-500 to-violet-500 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:opacity-80 flex items-center"
                >
                    <PlusIcon class="w-4 h-4 mr-2" />
                    Add Server to Queue
                </button>
                <router-link
                    to="/mc-admin/server-queue/logs"
                    class="bg-gray-800 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:opacity-80 flex items-center"
                >
                    <FileTextIcon class="w-4 h-4 mr-2" />
                    View Logs
                </router-link>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
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
                        <p class="text-gray-400 text-sm">Pending</p>
                        <h3 class="text-2xl font-bold text-yellow-400">{{ stats.pending || 0 }}</h3>
                    </div>
                    <ClockIcon class="h-8 w-8 text-yellow-400" />
                </div>
            </div>
            <div class="bg-gray-800/50 backdrop-blur-md rounded-lg p-4 shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Building</p>
                        <h3 class="text-2xl font-bold text-blue-400">{{ stats.building || 0 }}</h3>
                    </div>
                    <LoaderIcon class="h-8 w-8 text-blue-400" />
                </div>
            </div>
            <div class="bg-gray-800/50 backdrop-blur-md rounded-lg p-4 shadow-md">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Failed</p>
                        <h3 class="text-2xl font-bold text-red-400">{{ stats.failed || 0 }}</h3>
                    </div>
                    <AlertTriangleIcon class="h-8 w-8 text-red-400" />
                </div>
            </div>
        </div>

        <div v-if="loading" class="flex justify-center items-center py-10">
            <LoaderCircle class="h-8 w-8 animate-spin text-pink-400" />
        </div>
        <TableTanstack v-else :data="queueItems" :columns="columns" tableName="Server Queue" />
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, onMounted, h } from 'vue';
import LayoutDashboard from '@/components/admin/LayoutDashboard.vue';
import TableTanstack from '@/components/client/ui/Table/TableTanstack.vue';
import {
    PlusIcon,
    PlayIcon,
    TrashIcon,
    LoaderCircle,
    ServerIcon,
    ClockIcon,
    LoaderIcon,
    AlertTriangleIcon,
    FileTextIcon,
} from 'lucide-vue-next';
import { useRouter } from 'vue-router';
import ServerQueue from '@/mythicaldash/admin/ServerQueue';
import Swal from 'sweetalert2';
import { useSound } from '@vueuse/sound';
import failedAlertSfx from '@/assets/sounds/error.mp3';
import successAlertSfx from '@/assets/sounds/success.mp3';

// Queue item interface matching the API response
interface QueueItem {
    id: number;
    name: string;
    description: string;
    status: 'pending' | 'building' | 'failed';
    ram: number;
    disk: number;
    cpu: number;
    ports: number;
    databases: number;
    backups: number;
    location: {
        id: number;
        name: string;
        description: string;
        pterodactyl_location_id: number;
        node_ip: string;
        status: string;
        deleted: string;
        locked: string;
        updated_at: string;
        created_at: string;
    };
    user: {
        uuid: string;
        username: string;
        email: string;
        role: number;
        first_name: string;
        last_name: string;
        avatar: string;
    };
    nest: {
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
    egg: {
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
    deleted: string;
    locked: string;
    created_at: string;
    updated_at: string;
}

interface QueueStats {
    total: number;
    pending: number;
    building: number;
    failed: number;
}

interface CellInfo {
    getValue: () => unknown;
    row: {
        original: QueueItem;
    };
}

const router = useRouter();
const queueItems = ref<QueueItem[]>([]);
const loading = ref(true);
const stats = ref<QueueStats>({
    total: 0,
    pending: 0,
    building: 0,
    failed: 0,
});
const { play: playError } = useSound(failedAlertSfx);
const { play: playSuccess } = useSound(successAlertSfx);

// Define columns for TableTanstack
const columns = [
    {
        header: 'ID',
        accessorKey: 'id',
        cell: (info: CellInfo) => info.getValue(),
    },
    {
        header: 'Name',
        accessorKey: 'name',
        cell: (info: CellInfo) => info.getValue(),
    },
    {
        header: 'User',
        id: 'user',
        cell: (info: CellInfo) => {
            const user = info.row.original.user;
            return user ? `${user.username} (${user.email})` : 'Unknown';
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
            const item = info.row.original;
            return h('div', { class: 'text-xs' }, [
                h('div', { class: 'flex items-center space-x-2' }, [
                    h('span', { class: 'text-gray-400' }, 'RAM:'),
                    h('span', { class: 'text-white' }, `${item.ram} MB`),
                    h('span', { class: 'text-gray-400 ml-2' }, 'CPU:'),
                    h('span', { class: 'text-white' }, `${item.cpu}%`),
                    h('span', { class: 'text-gray-400 ml-2' }, 'Disk:'),
                    h('span', { class: 'text-white' }, `${item.disk} MB`),
                ]),
            ]);
        },
    },
    {
        header: 'Status',
        accessorKey: 'status',
        cell: (info: CellInfo) => {
            const status = info.getValue();
            let statusClass = '';
            let statusText = '';

            switch (status) {
                case 'pending':
                    statusClass = 'bg-yellow-500/20 text-yellow-400';
                    statusText = 'Pending';
                    break;
                case 'building':
                    statusClass = 'bg-blue-500/20 text-blue-400';
                    statusText = 'Building';
                    break;
                case 'failed':
                    statusClass = 'bg-red-500/20 text-red-400';
                    statusText = 'Failed';
                    break;
                default:
                    statusClass = 'bg-gray-500/20 text-gray-400';
                    statusText = status as string;
            }

            return h('span', { class: `px-2 py-1 rounded-full text-xs ${statusClass}` }, statusText);
        },
    },
    {
        header: 'Created',
        accessorKey: 'created_at',
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

            // Process button (only for pending items)
            if (item.status === 'pending') {
                actions.push(
                    h(
                        'button',
                        {
                            class: 'p-1 text-blue-400 hover:text-blue-300 transition-colors',
                            title: 'Process',
                            onClick: () => router.push(`/mc-admin/server-queue/${item.id}`),
                        },
                        [h(PlayIcon, { class: 'h-4 w-4' })],
                    ),
                );
            }

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

// Fetch queue items from API
const fetchQueueItems = async () => {
    loading.value = true;
    try {
        const response = await ServerQueue.getServerQueue();
        if (response.success) {
            queueItems.value = response.server_queue;
        } else {
            console.error('Failed to fetch queue items:', response);
        }
    } catch (error) {
        console.error('Error fetching queue items:', error);
    } finally {
        loading.value = false;
    }
};

// Fetch queue stats
const fetchQueueStats = async () => {
    try {
        const response = await ServerQueue.getServerQueueStats();
        if (response.success) {
            stats.value = response.stats;
        } else {
            console.error('Failed to fetch queue stats:', response);
        }
    } catch (error) {
        console.error('Error fetching queue stats:', error);
    }
};

const goToCreation = () => {
    router.push('/mc-admin/server-queue/create');
};

const confirmDelete = (item: QueueItem) => {
    Swal.fire({
        title: 'Delete Server',
        text: `Are you sure you want to delete "${item.name}" from the queue?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it',
        confirmButtonColor: '#ef4444',
        cancelButtonText: 'Cancel',
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                const response = await ServerQueue.deleteServerQueueItem(item.id);

                if (response.success) {
                    playSuccess();
                    Swal.fire({
                        title: 'Deleted',
                        text: 'Server has been removed from the queue',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false,
                    });

                    // Refresh the data
                    await fetchQueueItems();
                    await fetchQueueStats();
                } else {
                    playError();
                    Swal.fire({
                        title: 'Error',
                        text: response.message || 'Failed to delete server from queue',
                        icon: 'error',
                    });
                }
            } catch (error) {
                console.error('Error deleting queue item:', error);
                playError();
                Swal.fire({
                    title: 'Error',
                    text: 'An unexpected error occurred',
                    icon: 'error',
                });
            }
        }
    });
};

onMounted(async () => {
    await fetchQueueStats();
    await fetchQueueItems();
});
</script>
