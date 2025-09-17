<template>
    <LayoutDashboard>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-400">Tickets</h1>
            <div class="flex items-center space-x-3">
                <div class="relative">
                    <input
                        v-model="search"
                        @keyup.enter="applyFilters"
                        type="text"
                        placeholder="Search tickets..."
                        class="bg-gray-800/60 border border-gray-700 rounded-lg pl-9 pr-3 py-2 text-sm text-gray-200 placeholder-gray-400 focus:outline-none focus:border-pink-500/60"
                    />
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="currentColor"
                        class="w-4 h-4 text-gray-400 absolute left-2 top-2.5"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M10.5 3.75a6.75 6.75 0 0 0-4.774 11.524l-2.69 2.69a.75.75 0 1 0 1.06 1.06l2.69-2.69A6.75 6.75 0 1 0 10.5 3.75Zm0 1.5a5.25 5.25 0 1 1 0 10.5 5.25 5.25 0 0 1 0-10.5Z"
                            clip-rule="evenodd"
                        />
                    </svg>
                </div>
                <select
                    class="bg-gray-800/60 border border-gray-700 rounded-lg px-3 py-2 text-sm text-gray-200"
                    v-model="filters.status"
                    @change="applyFilters"
                >
                    <option class="bg-gray-900" value="">All Status</option>
                    <option class="bg-gray-900" value="open">Open</option>
                    <option class="bg-gray-900" value="inprogress">In Progress</option>
                    <option class="bg-gray-900" value="closed">Closed</option>
                </select>
                <select
                    class="bg-gray-800/60 border border-gray-700 rounded-lg px-3 py-2 text-sm text-gray-200"
                    v-model="filters.priority"
                    @change="applyFilters"
                >
                    <option class="bg-gray-900" value="">All Priority</option>
                    <option class="bg-gray-900" value="low">Low</option>
                    <option class="bg-gray-900" value="medium">Medium</option>
                    <option class="bg-gray-900" value="high">High</option>
                    <option class="bg-gray-900" value="urgent">Urgent</option>
                </select>
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
            </div>
        </div>

        <div v-if="loading" class="flex justify-center items-center py-10">
            <LoaderCircle class="h-8 w-8 animate-spin text-pink-400" />
        </div>

        <div v-else>
            <div v-if="tickets.length === 0" class="text-center text-gray-400 py-10">No tickets found.</div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5 mb-6">
                <div
                    v-for="t in tickets"
                    :key="t.id"
                    class="group relative rounded-xl p-4 bg-gradient-to-br from-gray-800/70 to-gray-900/70 border border-gray-700/70 hover:border-pink-500/40 transition-all duration-200 shadow hover:shadow-pink-500/10"
                >
                    <div class="flex items-start justify-between">
                        <div class="flex items-center space-x-3">
                            <img
                                :src="t.user_details?.avatar || fallbackAvatar"
                                alt="avatar"
                                class="w-10 h-10 rounded-full border border-gray-700"
                            />
                            <div>
                                <h3 class="text-white font-semibold truncate max-w-[220px]">
                                    #{{ t.id }} · {{ t.title }}
                                </h3>
                                <p class="text-xs text-gray-400 truncate">
                                    {{ t.user_details?.username || 'Unknown' }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span
                                class="px-2 py-0.5 rounded-full text-[10px] border"
                                :class="statusMap[t.status]?.cls || 'bg-gray-500/10 text-gray-300 border-gray-500/30'"
                                >{{ statusMap[t.status]?.name || t.status }}</span
                            >
                            <span
                                class="px-2 py-0.5 rounded-full text-[10px] border"
                                :class="
                                    priorityMap[t.priority]?.cls || 'bg-gray-500/10 text-gray-300 border-gray-500/30'
                                "
                                >{{ priorityMap[t.priority]?.name || t.priority }}</span
                            >
                        </div>
                    </div>
                    <p class="mt-3 text-sm text-gray-300 line-clamp-3">{{ t.description }}</p>
                    <div class="mt-4 flex items-center justify-end">
                        <button
                            class="p-2 rounded-md bg-gray-800/60 border border-gray-700/60 text-blue-400 hover:bg-gray-800 hover:border-blue-400/40"
                            title="View"
                            @click="viewTicket(t)"
                        >
                            <EyeIcon class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </div>

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

        <div
            v-if="notification.show"
            class="fixed bottom-4 right-4 py-2 px-4 rounded-lg shadow-lg transition-all duration-300"
            :class="{
                'bg-green-500/20 text-green-400 border border-green-500/30': notification.type === 'success',
                'bg-red-500/20 text-red-400 border border-red-500/30': notification.type === 'error',
            }"
        >
            {{ notification.message }}
        </div>
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, h } from 'vue';
import LayoutDashboard from '@/components/admin/LayoutDashboard.vue';
import { LoaderCircle, EyeIcon } from 'lucide-vue-next';
import { useRouter } from 'vue-router';

interface UserDetails {
    uuid: string;
    username: string;
    name: string;
    email: string;
    avatar: string;
}
interface Ticket {
    id: number;
    user: string;
    department: number;
    priority: string;
    status: string;
    title: string;
    description: string;
    deleted: string;
    locked: string;
    user_details: UserDetails;
}

const router = useRouter();
const tickets = ref<Ticket[]>([]);
const loading = ref(true);
const notification = ref({ show: false, message: '', type: 'success' as 'success' | 'error' });

const search = ref('');
const filters = ref<{ status: string; priority: string }>({ status: '', priority: '' });
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

const priorityMap: Record<string, { name: string; cls: string }> = {
    low: { name: 'Low', cls: 'bg-blue-500/10 text-blue-400 border-blue-500/30' },
    medium: { name: 'Medium', cls: 'bg-yellow-500/10 text-yellow-400 border-yellow-500/30' },
    high: { name: 'High', cls: 'bg-orange-500/10 text-orange-400 border-orange-500/30' },
    urgent: { name: 'Urgent', cls: 'bg-red-500/10 text-red-400 border-red-500/30' },
};

const statusMap: Record<string, { name: string; cls: string }> = {
    open: { name: 'Open', cls: 'bg-green-500/10 text-green-400 border-green-500/30' },
    inprogress: { name: 'In Progress', cls: 'bg-blue-500/10 text-blue-400 border-blue-500/30' },
    closed: { name: 'Closed', cls: 'bg-gray-500/10 text-gray-400 border-gray-500/30' },
};

const fallbackAvatar = 'https://www.gravatar.com/avatar/00000000000000000000000000000000?d=mp&f=y';

const fetchTickets = async () => {
    loading.value = true;
    try {
        const params = new URLSearchParams({
            page: String(pagination.value.page),
            limit: String(pagination.value.limit),
        });
        if (search.value.trim()) params.set('search', search.value.trim());
        if (filters.value.status) params.set('status', filters.value.status);
        if (filters.value.priority) params.set('priority', filters.value.priority);
        const response = await fetch(`/api/admin/tickets?${params.toString()}`, {
            method: 'GET',
            headers: { Accept: 'application/json' },
        });
        const data = await response.json();
        if (data.success) {
            tickets.value = data.tickets as Ticket[];
            if (data.pagination) pagination.value = data.pagination;
        } else {
            tickets.value = [];
            show('Failed to load tickets', 'error');
        }
    } catch (e) {
        console.error('Error fetching tickets:', e);
        tickets.value = [];
        show('Error fetching tickets', 'error');
    } finally {
        loading.value = false;
    }
};

const changePage = async (newPage: number) => {
    if (newPage < 1 || newPage === pagination.value.page) return;
    pagination.value.page = newPage;
    await fetchTickets();
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const changePageSize = async (val: string) => {
    const size = Number(val);
    if (!Number.isFinite(size) || size <= 0) return;
    pagination.value.limit = size;
    pagination.value.page = 1;
    await fetchTickets();
};

const applyFilters = async () => {
    pagination.value.page = 1;
    await fetchTickets();
};

const viewTicket = (t: Ticket) => router.push(`/mc-admin/tickets/${t.id}`);

const show = (message: string, type: 'success' | 'error') => {
    notification.value = { show: true, message, type };
    setTimeout(() => (notification.value.show = false), 3000);
};

onMounted(() => {
    fetchTickets();
});
</script>
