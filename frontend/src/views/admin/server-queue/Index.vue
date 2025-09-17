<template>
    <LayoutDashboard>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-400">Server Queue</h1>
            <div class="flex space-x-3 items-center">
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
                <div class="flex space-x-3">
                    <button
                        @click="goToCreation()"
                        class="bg-gradient-to-r from-pink-500 to-violet-500 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:opacity-90 shadow ring-1 ring-pink-400/30 flex items-center"
                    >
                        <PlusIcon class="w-4 h-4 mr-2" />
                        Add Server to Queue
                    </button>
                    <router-link
                        to="/mc-admin/server-queue/logs"
                        class="bg-gray-800 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:opacity-80 flex items-center border border-gray-700"
                    >
                        <FileTextIcon class="w-4 h-4 mr-2" />
                        View Logs
                    </router-link>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div
                class="bg-gradient-to-br from-gray-800/70 to-gray-900/70 backdrop-blur-md rounded-xl p-4 shadow-md border border-gray-700/80"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Total</p>
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
                        <p class="text-gray-400 text-sm">Pending</p>
                        <h3 class="text-2xl font-bold text-yellow-400">{{ stats.pending || 0 }}</h3>
                    </div>
                    <ClockIcon class="h-8 w-8 text-yellow-400" />
                </div>
            </div>
            <div
                class="bg-gradient-to-br from-gray-800/70 to-gray-900/70 backdrop-blur-md rounded-xl p-4 shadow-md border border-gray-700/80"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Building</p>
                        <h3 class="text-2xl font-bold text-blue-400">{{ stats.building || 0 }}</h3>
                    </div>
                    <LoaderIcon class="h-8 w-8 text-blue-400" />
                </div>
            </div>
            <div
                class="bg-gradient-to-br from-gray-800/70 to-gray-900/70 backdrop-blur-md rounded-xl p-4 shadow-md border border-gray-700/80"
            >
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

        <div v-else>
            <div v-if="queueItems.length === 0" class="text-center text-gray-400 py-10">
                No queue items for this page.
            </div>

            <!-- Cards Grid -->
            <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5 mb-6">
                <div
                    v-for="item in queueItems"
                    :key="item.id"
                    class="group relative rounded-xl p-4 bg-gradient-to-br from-gray-800/70 to-gray-900/70 border border-gray-700/70 hover:border-pink-500/40 transition-all duration-200 shadow hover:shadow-pink-500/10"
                >
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="flex items-center space-x-2">
                                <h3 class="text-lg font-semibold text-white truncate max-w-[240px]">{{ item.name }}</h3>
                                <span
                                    class="text-[10px] uppercase tracking-wide px-2 py-0.5 rounded-full border"
                                    :class="statusBadge(item.status)"
                                >
                                    {{ item.status }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-400 mt-1">ID: {{ item.id }}</p>
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
                            <p class="text-white font-medium">{{ item.ram }} MB</p>
                        </div>
                        <div class="bg-gray-900/50 rounded-lg p-2 border border-gray-700/60">
                            <p class="text-gray-400">CPU</p>
                            <p class="text-white font-medium">{{ item.cpu }}%</p>
                        </div>
                        <div class="bg-gray-900/50 rounded-lg p-2 border border-gray-700/60">
                            <p class="text-gray-400">Disk</p>
                            <p class="text-white font-medium">{{ item.disk }} MB</p>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center justify-between text-xs text-gray-300">
                        <div>
                            <span class="text-gray-400">Created</span>
                            <span class="ml-2 text-white">{{ new Date(item.created_at).toLocaleString() }}</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button
                                v-if="item.status === 'pending'"
                                class="p-2 rounded-md bg-gray-800/60 border border-gray-700/60 text-blue-400 hover:bg-gray-800 hover:border-blue-400/40"
                                title="Process"
                                @click="router.push(`/mc-admin/server-queue/${item.id}`)"
                            >
                                <PlayIcon class="h-4 w-4" />
                            </button>
                            <button
                                v-if="item.status !== 'completed'"
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
    status: 'pending' | 'building' | 'failed' | 'completed';
    ram: number;
    disk: number;
    cpu: number;
    ports: number;
    databases: number;
    backups: number;
    location?: { id: number; name: string };
    user: { uuid: string; username: string; email: string };
    created_at: string;
}

interface QueueStats {
    total: number;
    pending: number;
    building: number;
    failed: number;
}

const router = useRouter();
const queueItems = ref<QueueItem[]>([]);
const loading = ref(true);
const stats = ref<QueueStats>({ total: 0, pending: 0, building: 0, failed: 0 });

const pagination = ref<{ page: number; limit: number; total: number; pages: number; has_more: boolean }>({
    page: 1,
    limit: 20,
    total: 0,
    pages: 0,
    has_more: false,
});

const visiblePages = computed<(number | '…')[]>(() => {
    const total = pagination.value.pages;
    const current = pagination.value.page;
    const pages: (number | '…')[] = [];
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

const statusBadge = (status: QueueItem['status']) => {
    switch (status) {
        case 'pending':
            return 'bg-yellow-500/10 text-yellow-400 border-yellow-500/30';
        case 'building':
            return 'bg-blue-500/10 text-blue-400 border-blue-500/30';
        case 'failed':
            return 'bg-red-500/10 text-red-400 border-red-500/30';
        case 'completed':
            return 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30';
        default:
            return 'bg-gray-500/10 text-gray-300 border-gray-500/30';
    }
};

// Fetch queue items from API
const fetchQueueItems = async () => {
    loading.value = true;
    try {
        const response = await ServerQueue.getServerQueue(pagination.value.page, pagination.value.limit);
        if (response.success) {
            queueItems.value = response.server_queue as QueueItem[];
            if (response.pagination) {
                pagination.value = response.pagination;
            }
        } else {
            queueItems.value = [];
        }
    } catch (error) {
        console.error('Error fetching queue items:', error);
        queueItems.value = [];
    } finally {
        loading.value = false;
    }
};

// Fetch queue stats
const fetchQueueStats = async () => {
    try {
        const response = await ServerQueue.getServerQueueStats();
        if (response.success) {
            stats.value = response.stats as QueueStats;
        }
    } catch (error) {
        console.error('Error fetching queue stats:', error);
    }
};

const changePage = async (newPage: number) => {
    if (newPage < 1 || newPage === pagination.value.page) return;
    pagination.value.page = newPage;
    await fetchQueueItems();
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const changePageSize = async (val: string) => {
    const size = Number(val);
    if (!Number.isFinite(size) || size <= 0) return;
    pagination.value.limit = size;
    pagination.value.page = 1;
    await fetchQueueItems();
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
                Swal.fire({ title: 'Error', text: 'An unexpected error occurred', icon: 'error' });
            }
        }
    });
};

onMounted(async () => {
    await fetchQueueStats();
    await fetchQueueItems();
});
</script>
