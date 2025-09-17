<template>
    <LayoutDashboard>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-400">Server List</h1>
            <div class="flex items-center space-x-3">
                <div class="flex items-center space-x-2 bg-gray-800/60 border border-gray-700 rounded-lg px-3 py-2">
                    <span class="text-gray-400 text-xs">Per page</span>
                    <select
                        class="bg-transparent text-gray-200 text-sm focus:outline-none"
                        :value="pagination.limit"
                        @change="changePageSize(($event.target as HTMLSelectElement).value)"
                    >
                        <option class="bg-gray-900" :value="10">10</option>
                        <option class="bg-gray-900" :value="20">20</option>
                        <option class="bg-gray-900" :value="30">30</option>
                        <option class="bg-gray-900" :value="50">50</option>
                    </select>
                </div>
                <button
                    @click="goToCreation()"
                    class="bg-gradient-to-r from-pink-500 to-violet-500 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:opacity-90 shadow ring-1 ring-pink-400/30 flex items-center"
                >
                    <PlusIcon class="w-4 h-4 mr-2" />
                    Create New Server
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div
                class="bg-gradient-to-br from-gray-800/70 to-gray-900/70 backdrop-blur-md rounded-xl p-4 shadow-md border border-gray-700/80"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Total Servers</p>
                        <h3 class="text-2xl font-bold text-white">{{ stats.total || 0 }}</h3>
                    </div>
                    <ServerIcon class="h-8 w-8 text-pink-400" />
                </div>
            </div>
            <div
                class="bg-gradient-to-br from-gray-800/70 to-gray-900/70 backdrop-blur-md rounded-xl p-4 shadow-md border border-gray-700/80"
            >
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

        <!-- Cards Grid -->
        <div v-else>
            <div v-if="servers.length === 0" class="text-center text-gray-400 py-10">
                No servers found for this page.
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5 mb-6">
                <div
                    v-for="item in servers"
                    :key="item.attributes.id"
                    class="group relative rounded-xl p-4 bg-gradient-to-br from-gray-800/70 to-gray-900/70 border border-gray-700/70 hover:border-pink-500/40 transition-all duration-200 shadow hover:shadow-pink-500/10"
                >
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="flex items-center space-x-2">
                                <h3 class="text-lg font-semibold text-white truncate max-w-[240px]">
                                    {{ item.attributes.name }}
                                </h3>
                                <span
                                    class="text-[10px] uppercase tracking-wide px-2 py-0.5 rounded-full border"
                                    :class="
                                        item.attributes.suspended
                                            ? 'bg-red-500/10 text-red-400 border-red-500/30'
                                            : 'bg-green-500/10 text-green-400 border-green-500/30'
                                    "
                                >
                                    {{ item.attributes.suspended ? 'Suspended' : 'Active' }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-400 mt-1">ID: {{ item.attributes.id }}</p>
                        </div>
                        <div class="flex items-center space-x-2 opacity-90">
                            <span
                                class="text-[10px] text-gray-300 bg-gray-900/60 border border-gray-700/70 rounded px-2 py-0.5"
                            >
                                {{ item.location?.name || 'Unknown location' }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-3 gap-3 text-xs">
                        <div class="bg-gray-900/50 rounded-lg p-2 border border-gray-700/60">
                            <p class="text-gray-400">RAM</p>
                            <p class="text-white font-medium">{{ item.attributes.limits.memory }} MB</p>
                        </div>
                        <div class="bg-gray-900/50 rounded-lg p-2 border border-gray-700/60">
                            <p class="text-gray-400">CPU</p>
                            <p class="text-white font-medium">{{ item.attributes.limits.cpu }}%</p>
                        </div>
                        <div class="bg-gray-900/50 rounded-lg p-2 border border-gray-700/60">
                            <p class="text-gray-400">Disk</p>
                            <p class="text-white font-medium">{{ item.attributes.limits.disk }} MB</p>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center justify-between text-xs text-gray-300">
                        <div>
                            <span class="text-gray-400">Created</span>
                            <span class="ml-2 text-white">{{
                                new Date(item.attributes.created_at).toLocaleString()
                            }}</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button
                                class="p-2 rounded-md bg-gray-800/60 border border-gray-700/60 text-blue-400 hover:bg-gray-800 hover:border-blue-400/40"
                                title="Go to Panel"
                                @click="goToPanel(item)"
                            >
                                <ExternalLinkIcon class="h-4 w-4" />
                            </button>
                            <button
                                class="p-2 rounded-md bg-gray-800/60 border border-gray-700/60 text-yellow-400 hover:bg-gray-800 hover:border-yellow-400/40"
                                :title="item.attributes.suspended ? 'Unsuspend' : 'Suspend'"
                                @click="toggleSuspend(item)"
                            >
                                <PauseIcon class="h-4 w-4" />
                            </button>
                            <button
                                class="p-2 rounded-md bg-gray-800/60 border border-gray-700/60 text-red-400 hover:bg-gray-800 hover:border-red-400/40"
                                title="Delete"
                                @click="confirmDelete(item)"
                            >
                                <TrashIcon class="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination Controls -->
            <div class="flex items-center justify-between border-t border-gray-700 pt-4">
                <div class="text-sm text-gray-400">
                    Page {{ pagination.page }} of {{ pagination.pages }} · {{ pagination.total }} total
                </div>
                <div class="flex items-center space-x-2">
                    <button
                        class="px-3 py-1 rounded bg-gray-800 text-gray-200 border border-gray-700 disabled:opacity-50"
                        :disabled="pagination.page <= 1"
                        @click="changePage(pagination.page - 1)"
                    >
                        Previous
                    </button>

                    <template v-for="p in visiblePages" :key="p">
                        <button
                            v-if="p !== '…'"
                            class="px-3 py-1 rounded border"
                            :class="
                                p === pagination.page
                                    ? 'bg-pink-600/80 text-white border-pink-400'
                                    : 'bg-gray-800 text-gray-200 border-gray-700 hover:border-pink-400/40'
                            "
                            @click="changePage(p as number)"
                        >
                            {{ p }}
                        </button>
                        <span v-else class="px-2 text-gray-500">…</span>
                    </template>

                    <button
                        class="px-3 py-1 rounded bg-gray-800 text-gray-200 border border-gray-700 disabled:opacity-50"
                        :disabled="!pagination.has_more"
                        @click="changePage(pagination.page + 1)"
                    >
                        Next
                    </button>
                </div>
            </div>
        </div>
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import LayoutDashboard from '@/components/admin/LayoutDashboard.vue';
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
        name: string;
        suspended: boolean;
        limits: {
            memory: number;
            cpu: number;
            disk: number;
        };
        created_at: string;
    };
    location?: {
        id: number;
        name: string;
    };
}

interface ServerStats {
    total: number;
    suspended: number;
}

const router = useRouter();
const servers = ref<Server[]>([]);
const loading = ref(true);
const stats = ref<ServerStats>({
    total: 0,
    suspended: 0,
});

const pagination = ref<{ page: number; limit: number; total: number; pages: number; has_more: boolean }>({
    page: 1,
    limit: 20,
    total: 0,
    pages: 0,
    has_more: false,
});

const visiblePages = computed<(number | '…')[]>(() => {
    const pages: (number | '…')[] = [];
    const total = pagination.value.pages;
    const current = pagination.value.page;
    if (total <= 7) {
        for (let i = 1; i <= total; i++) pages.push(i);
        return pages;
    }
    pages.push(1);
    if (current > 3) pages.push('…');
    const start = Math.max(2, current - 1);
    const end = Math.min(total - 1, current + 1);
    for (let i = start; i <= end; i++) pages.push(i);
    if (current < total - 2) pages.push('…');
    pages.push(total);
    return pages;
});

const { play: playError } = useSound(failedAlertSfx);
const { play: playSuccess } = useSound(successAlertSfx);

// Fetch servers from API
const fetchServers = async () => {
    loading.value = true;
    try {
        const response = await ServerList.getList(pagination.value.page, pagination.value.limit);
        if (response.success) {
            if (response.servers && response.servers.data) {
                servers.value = response.servers.data as Server[];
                stats.value = {
                    total: response.pagination?.total ?? response.servers.meta.pagination.total,
                    suspended: servers.value.filter((item) => item.attributes.suspended).length,
                };
                if (response.pagination) {
                    pagination.value = response.pagination;
                } else if (response.servers?.meta?.pagination) {
                    const p = response.servers.meta.pagination;
                    pagination.value = {
                        page: p.current_page,
                        limit: p.per_page,
                        total: p.total,
                        pages: p.total_pages,
                        has_more: p.current_page < p.total_pages,
                    };
                }
            } else {
                servers.value = [];
                stats.value = { total: 0, suspended: 0 };
            }
        } else {
            servers.value = [];
            stats.value = { total: 0, suspended: 0 };
        }
    } catch (error) {
        console.error('Error fetching servers:', error);
        servers.value = [];
        stats.value = { total: 0, suspended: 0 };
    } finally {
        loading.value = false;
    }
};

const changePage = async (newPage: number) => {
    if (newPage < 1 || newPage === pagination.value.page) return;
    pagination.value.page = newPage;
    await fetchServers();
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const changePageSize = async (val: string) => {
    const size = Number(val);
    if (!Number.isFinite(size) || size <= 0) return;
    pagination.value.limit = size;
    pagination.value.page = 1;
    await fetchServers();
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
            await fetchServers();
        } else {
            playError();
            Swal.fire({ title: 'Error', text: response.message || 'Failed to toggle suspend status', icon: 'error' });
        }
    } catch (error) {
        console.error('Error toggling suspend status:', error);
        playError();
        Swal.fire({ title: 'Error', text: 'An unexpected error occurred', icon: 'error' });
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
                await fetchServers();
            } else {
                playError();
                Swal.fire({ title: 'Error', text: response.message || 'Failed to delete server', icon: 'error' });
            }
        }
    });
};

onMounted(async () => {
    await fetchServers();
});
</script>
