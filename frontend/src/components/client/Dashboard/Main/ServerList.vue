<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import {
    Server,
    Plus as PlusIcon,
    Pencil as PencilIcon,
    Trash as TrashIcon,
    ExternalLink as ExternalLinkIcon,
    RefreshCcw as RefreshCcwIcon,
} from 'lucide-vue-next';
import CardComponent from '../../ui/Card/CardComponent.vue';
import Servers from '@/mythicaldash/Pterodactyl/Servers';
import Session from '@/mythicaldash/Session';
import Swal from 'sweetalert2';
import { useSettingsStore } from '@/stores/settings';
import { useI18n } from 'vue-i18n';
import { computed } from 'vue';

const Settings = useSettingsStore();
const pterodactylUrl = Settings.getSetting('pterodactyl_base_url');
const serverRenewEnabled = Settings.getSetting('server_renew_enabled');

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

const router = useRouter();
const loading = ref(true);
const servers = ref<Server[]>([]);

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
        servers.value = data as Server[];
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
            v-else-if="servers.length === 0 && isServersEnabled"
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
            <!-- Header with stats and actions -->
            <div class="flex justify-between items-center mb-4">
                <div class="text-sm text-gray-400">
                    {{ t('Components.ServerList.serverLimit', [servers.length, Session.getInfoInt('server_limit')]) }}
                </div>
                <button
                    @click="createServer"
                    v-if="isServersEnabled"
                    class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md transition-colors flex items-center gap-2"
                    :disabled="servers.length >= Session.getInfoInt('server_limit')"
                >
                    <PlusIcon class="w-4 h-4" />
                    {{ t('Components.ServerList.newServer') }}
                </button>
            </div>

            <!-- Servers Table -->
            <div class="relative overflow-x-auto rounded-lg border border-gray-800">
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
                            v-for="server in servers"
                            :key="server.identifier"
                            class="border-b border-gray-800 bg-gray-900/20 hover:bg-gray-800/30 transition-colors"
                        >
                            <td class="px-6 py-4">
                                <div class="font-medium text-white">{{ server.name }}</div>
                                <div class="text-xs text-gray-400">{{ server.identifier }}</div>
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
                                        class="p-1.5 rounded-md text-gray-400 hover:text-indigo-400 hover:bg-gray-800/50 transition-colors"
                                        title="Jump to Panel"
                                        @click="jumpToPanel(server.identifier)"
                                    >
                                        <ExternalLinkIcon class="w-4 h-4" />
                                    </button>
                                    <button
                                        class="p-1.5 rounded-md text-gray-400 hover:text-indigo-400 hover:bg-gray-800/50 transition-colors"
                                        title="Edit Server"
                                        @click="editServer(server.id)"
                                    >
                                        <PencilIcon class="w-4 h-4" />
                                    </button>
                                    <button
                                        class="p-1.5 rounded-md text-gray-400 hover:text-indigo-400 hover:bg-gray-800/50 transition-colors"
                                        title="Renew Server"
                                        v-if="serverRenewEnabled === 'true'"
                                        @click="renewServer(server.id)"
                                    >
                                        <RefreshCcwIcon class="w-4 h-4" />
                                    </button>
                                    <button
                                        class="p-1.5 rounded-md text-gray-400 hover:text-red-400 hover:bg-gray-800/50 transition-colors"
                                        title="Delete Server"
                                        @click="deleteServer(server.id)"
                                    >
                                        <TrashIcon class="w-4 h-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </CardComponent>
</template>
