<template>
    <LayoutDashboard>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-400">J4R Servers</h1>
            <button
                @click="goToCreation()"
                class="bg-gradient-to-r from-pink-500 to-violet-500 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:opacity-80 flex items-center"
            >
                <PlusIcon class="w-4 h-4 mr-2" />
                Add J4R Server
            </button>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-gray-800/50 backdrop-blur-md rounded-lg p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-500/20 rounded-lg">
                        <ServerIcon class="w-6 h-6 text-blue-400" />
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-400 text-sm">Total Servers</p>
                        <p class="text-2xl font-bold text-white">{{ stats.total }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-gray-800/50 backdrop-blur-md rounded-lg p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-green-500/20 rounded-lg">
                        <CheckCircleIcon class="w-6 h-6 text-green-400" />
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-400 text-sm">Available</p>
                        <p class="text-2xl font-bold text-white">{{ stats.available }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-gray-800/50 backdrop-blur-md rounded-lg p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-red-500/20 rounded-lg">
                        <LockIcon class="w-6 h-6 text-red-400" />
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-400 text-sm">Locked</p>
                        <p class="text-2xl font-bold text-white">{{ stats.locked }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- J4R Servers Table -->
        <div v-if="loading" class="flex justify-center items-center py-10">
            <LoaderCircle class="h-8 w-8 animate-spin text-pink-400" />
        </div>
        <TableTanstack v-else :data="servers" :columns="columns" tableName="J4R Servers" />
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, onMounted, h } from 'vue';
import LayoutDashboard from '@/components/admin/LayoutDashboard.vue';
import TableTanstack from '@/components/client/ui/Table/TableTanstack.vue';
import {
    PlusIcon,
    EditIcon,
    TrashIcon,
    LoaderCircle,
    ServerIcon,
    CheckCircleIcon,
    LockIcon,
    UnlockIcon,
    CoinsIcon,
    ExternalLinkIcon,
} from 'lucide-vue-next';
import { useRouter } from 'vue-router';

// J4R Server interface based on the API response
interface J4RServer {
    id: number;
    name: string;
    invite_code: string;
    server_id: string | null;
    description: string | null;
    icon_url: string | null;
    coins: number;
    created_at: string;
    updated_at: string;
    deleted: string;
    locked: string;
}

interface Stats {
    total: number;
    available: number;
    locked: number;
}

const router = useRouter();
const servers = ref<J4RServer[]>([]);
const stats = ref<Stats>({ total: 0, available: 0, locked: 0 });
const loading = ref<boolean>(true);

// Define columns for TableTanstack
const columns = [
    {
        accessorKey: 'id',
        header: 'ID',
        cell: (info: { getValue: () => number }) => info.getValue(),
    },
    {
        accessorKey: 'name',
        header: 'Server Name',
        cell: (info: { getValue: () => string; row: { original: J4RServer } }) => {
            const server = info.row.original;
            return h('div', { class: 'flex items-center space-x-3' }, [
                server.icon_url
                    ? h('img', {
                          src: server.icon_url,
                          alt: server.name,
                          class: 'w-8 h-8 rounded-full object-cover',
                      })
                    : h(
                          'div',
                          {
                              class: 'w-8 h-8 rounded-full bg-gray-600 flex items-center justify-center',
                          },
                          [h('span', { class: 'text-white text-xs font-medium' }, server.name.charAt(0).toUpperCase())],
                      ),
                h('div', { class: 'flex-1' }, [
                    h('p', { class: 'font-medium text-white' }, server.name),
                    server.description ? h('p', { class: 'text-sm text-gray-400 truncate' }, server.description) : null,
                ]),
            ]);
        },
    },
    {
        accessorKey: 'invite_code',
        header: 'Invite Code',
        cell: (info: { getValue: () => string; row: { original: J4RServer } }) => {
            const code = info.getValue();
            const server = info.row.original;

            return h('div', { class: 'flex items-center space-x-2' }, [
                h(
                    'code',
                    {
                        class: 'bg-gray-700 text-gray-300 px-2 py-1 rounded text-sm font-mono',
                    },
                    code,
                ),
                h(
                    'a',
                    {
                        href: `https://discord.gg/${code}`,
                        target: '_blank',
                        class: 'text-blue-400 hover:text-blue-300 transition-colors',
                        title: 'Open Discord invite',
                    },
                    [h(ExternalLinkIcon, { class: 'w-4 h-4' })],
                ),
            ]);
        },
    },
    {
        accessorKey: 'server_id',
        header: 'Discord Server ID',
        cell: (info: { getValue: () => string | null }) => {
            const serverId = info.getValue();
            return serverId
                ? h(
                      'code',
                      {
                          class: 'bg-gray-700 text-gray-300 px-2 py-1 rounded text-sm font-mono',
                      },
                      serverId,
                  )
                : h('span', { class: 'text-gray-500 text-sm' }, 'Not set');
        },
    },
    {
        accessorKey: 'coins',
        header: 'Coins Reward',
        cell: (info: { getValue: () => number }) => {
            const coins = info.getValue();
            return h('div', { class: 'flex items-center space-x-1' }, [
                h(CoinsIcon, { class: 'w-4 h-4 text-yellow-400' }),
                h('span', { class: 'font-medium' }, coins.toLocaleString()),
            ]);
        },
    },
    {
        accessorKey: 'locked',
        header: 'Status',
        cell: (info: { getValue: () => string }) => {
            const isLocked = info.getValue() === 'true';
            return h(
                'span',
                {
                    class: isLocked
                        ? 'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-500/20 text-red-400'
                        : 'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-500/20 text-green-400',
                },
                [
                    h(isLocked ? LockIcon : CheckCircleIcon, { class: 'w-3 h-3 mr-1' }),
                    isLocked ? 'Locked' : 'Available',
                ],
            );
        },
    },
    {
        accessorKey: 'created_at',
        header: 'Created',
        cell: (info: { getValue: () => string }) => new Date(info.getValue()).toLocaleDateString(),
    },
    {
        id: 'actions',
        header: 'Actions',
        cell: (info: { row: { original: J4RServer } }) => {
            const server = info.row.original;
            const isLocked = server.locked === 'true';

            return h('div', { class: 'flex space-x-2' }, [
                h(
                    'button',
                    {
                        class: 'p-1 text-gray-400 hover:text-pink-400 transition-colors',
                        title: 'Edit',
                        onClick: () => editServer(server),
                    },
                    [h(EditIcon, { class: 'w-4 h-4' })],
                ),
                h(
                    'button',
                    {
                        class: isLocked
                            ? 'p-1 text-gray-400 hover:text-green-400 transition-colors'
                            : 'p-1 text-gray-400 hover:text-yellow-400 transition-colors',
                        title: isLocked ? 'Unlock' : 'Lock',
                        onClick: () => toggleLock(server),
                    },
                    [h(isLocked ? UnlockIcon : LockIcon, { class: 'w-4 h-4' })],
                ),
                h(
                    'button',
                    {
                        class: 'p-1 text-gray-400 hover:text-red-400 transition-colors',
                        title: 'Delete',
                        onClick: () => confirmDelete(server),
                    },
                    [h(TrashIcon, { class: 'w-4 h-4' })],
                ),
            ]);
        },
    },
];

// Fetch J4R servers from API
const fetchServers = async (): Promise<void> => {
    loading.value = true;
    try {
        const response = await fetch('/api/admin/j4r/servers', {
            method: 'GET',
            headers: {
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error('Failed to fetch J4R servers');
        }

        const data = await response.json();

        if (data.success) {
            servers.value = data.servers;
            stats.value = {
                total: data.count,
                available: data.available_count,
                locked: data.count - data.available_count,
            };
        } else {
            console.error('Failed to load J4R servers:', data.message);
        }
    } catch (error) {
        console.error('Error fetching J4R servers:', error);
    } finally {
        loading.value = false;
    }
};

const goToCreation = (): void => {
    router.push('/mc-admin/j4r-servers/create');
};

const editServer = (server: J4RServer): void => {
    router.push(`/mc-admin/j4r-servers/${server.id}/edit`);
};

const confirmDelete = (server: J4RServer): void => {
    router.push(`/mc-admin/j4r-servers/${server.id}/delete`);
};

const toggleLock = async (server: J4RServer): Promise<void> => {
    const isLocked = server.locked === 'true';
    const action = isLocked ? 'unlock' : 'lock';

    try {
        const response = await fetch(`/api/admin/j4r/servers/${server.id}/${action}`, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error(`Failed to ${action} server`);
        }

        const data = await response.json();

        if (data.success) {
            // Refresh the data
            await fetchServers();
        } else {
            console.error(`Failed to ${action} server:`, data.message);
        }
    } catch (error) {
        console.error(`Error ${action}ing server:`, error);
    }
};

onMounted(() => {
    fetchServers();
});
</script>
