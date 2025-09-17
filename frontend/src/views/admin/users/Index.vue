<template>
    <LayoutDashboard>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-400">Users</h1>
            <div class="flex items-center space-x-3">
                <div class="relative">
                    <input
                        v-model="search"
                        @keyup.enter="onSearch"
                        type="text"
                        placeholder="Search users..."
                        class="bg-gray-800/60 border border-gray-700 rounded-lg pl-9 pr-3 py-2 text-sm text-gray-200 placeholder-gray-400 focus:outline-none focus:border-pink-500/60"
                    />
                    <SearchIcon class="w-4 h-4 text-gray-400 absolute left-2 top-2.5" />
                </div>
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
                    @click="supportPinModal()"
                    class="bg-gradient-to-r from-pink-500 to-violet-500 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:opacity-90 shadow ring-1 ring-pink-400/30 flex items-center"
                >
                    <SearchIcon class="w-4 h-4 mr-2" />
                    Enter Support Pin
                </button>
            </div>
        </div>

        <div v-if="loading" class="flex justify-center items-center py-10">
            <LoaderCircle class="h-8 w-8 animate-spin text-pink-400" />
        </div>

        <div v-else>
            <div v-if="users.length === 0" class="text-center text-gray-400 py-10">No users found.</div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5 mb-6">
                <div
                    v-for="u in users"
                    :key="u.uuid"
                    class="group relative rounded-xl p-4 bg-gradient-to-br from-gray-800/70 to-gray-900/70 border border-gray-700/70 hover:border-pink-500/40 transition-all duration-200 shadow hover:shadow-pink-500/10"
                >
                    <div class="flex items-center space-x-3">
                        <img :src="u.avatar" alt="avatar" class="w-10 h-10 rounded-full border border-gray-700" />
                        <div class="min-w-0">
                            <div class="flex items-center space-x-2">
                                <h3 class="text-white font-semibold truncate max-w-[220px]">{{ u.username }}</h3>
                                <span
                                    v-if="rolesData.length"
                                    class="px-2 py-0.5 rounded-full text-[10px] font-medium border"
                                    :style="roleStyle(u.role)"
                                >
                                    {{ roleName(u.role) }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-400 truncate">{{ u.email }}</p>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center justify-between text-xs text-gray-300">
                        <div class="flex items-center">
                            <ClockIcon class="w-3 h-3 mr-1 text-gray-400" />
                            <span>Last seen: {{ new Date(u.last_seen).toLocaleString() }}</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button
                                class="p-2 rounded-md bg-gray-800/60 border border-gray-700/60 text-gray-300 hover:bg-gray-800 hover:border-pink-400/40"
                                title="Edit"
                                @click="editUser(u)"
                            >
                                <EditIcon class="w-4 h-4" />
                            </button>
                            <button
                                class="p-2 rounded-md bg-gray-800/60 border border-gray-700/60 text-red-400 hover:bg-gray-800 hover:border-red-400/40"
                                title="Delete"
                                @click="confirmDelete(u)"
                            >
                                <TrashIcon class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
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
import { ref, onMounted, computed, h } from 'vue';
import LayoutDashboard from '@/components/admin/LayoutDashboard.vue';
import { EditIcon, TrashIcon, LoaderCircle, ClockIcon, SearchIcon } from 'lucide-vue-next';
import { useRouter } from 'vue-router';
import Swal from 'sweetalert2';
import Roles from '@/mythicaldash/admin/Roles';
import Users from '@/mythicaldash/admin/Users';

// User interface based on the API response
interface User {
    id: number;
    username: string;
    uuid: string;
    email: string;
    avatar: string;
    role: number;
    last_seen: string;
}

const router = useRouter();
const users = ref<User[]>([]);
const loading = ref(true);
const rolesData = ref<Array<{ id: number; name: string; color: string }>>([]);

const search = ref('');
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

// Fetch roles for dynamic color support
const fetchRoles = async () => {
    try {
        const response = await Roles.getRoles();
        if (response.success) {
            rolesData.value = response.roles;
        }
    } catch (error) {
        console.error('Error fetching roles:', error);
    }
};

const roleInfo = (roleId: number) => rolesData.value.find((r) => r.id === roleId);
const roleStyle = (roleId: number) => {
    const role = roleInfo(roleId);
    if (!role) return {};
    const hex = role.color.replace('#', '');
    const r = parseInt(hex.substr(0, 2), 16);
    const g = parseInt(hex.substr(2, 2), 16);
    const b = parseInt(hex.substr(4, 2), 16);
    return {
        color: role.color,
        borderColor: role.color + '33',
        backgroundColor: `rgba(${r}, ${g}, ${b}, 0.15)`,
    } as Record<string, string>;
};
const roleName = (roleId: number) => roleInfo(roleId)?.name || `Role ${roleId}`;

// Fetch users from API
const fetchUsers = async () => {
    loading.value = true;
    try {
        const data = await Users.getUsers(pagination.value.page, pagination.value.limit, search.value);
        if (data.success) {
            users.value = data.users as User[];
            if (data.pagination) pagination.value = data.pagination;
        } else {
            users.value = [];
        }
    } catch (error) {
        console.error('Error fetching users:', error);
        users.value = [];
    } finally {
        loading.value = false;
    }
};

const changePage = async (newPage: number) => {
    if (newPage < 1 || newPage === pagination.value.page) return;
    pagination.value.page = newPage;
    await fetchUsers();
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const changePageSize = async (val: string) => {
    const size = Number(val);
    if (!Number.isFinite(size) || size <= 0) return;
    pagination.value.limit = size;
    pagination.value.page = 1;
    await fetchUsers();
};

const onSearch = async () => {
    pagination.value.page = 1;
    await fetchUsers();
};

const editUser = (user: User) => {
    router.push(`/mc-admin/users/${user.uuid}/edit`);
};

const confirmDelete = (user: User) => {
    router.push(`/mc-admin/users/${user.uuid}/delete`);
};

const supportPinModal = () => {
    Swal.fire({
        title: 'Enter Support Pin',
        text: 'Enter the support pin to enter support mode',
        input: 'text',
        inputPlaceholder: 'e.g. 204375',
        showCancelButton: true,
        confirmButtonText: 'Enter',
        showLoaderOnConfirm: true,
        allowOutsideClick: true,
        preConfirm: async (pin) => {
            if (!pin) {
                return Swal.showValidationMessage('Please enter a valid pin');
            }

            try {
                const response = await fetch(`/api/admin/user/support-pin/${pin}`, {
                    method: 'GET',
                    headers: { Accept: 'application/json' },
                });
                const data = await response.json();
                if (!data.success) {
                    throw new Error(data.message || 'Invalid support pin');
                }
                return data;
            } catch (error) {
                console.error('Error checking support pin:', error);
                return Swal.showValidationMessage(
                    `Request failed: ${error instanceof Error ? error.message : 'Unknown error'}`,
                );
            }
        },
    }).then((result) => {
        if (result.isConfirmed && result.value.success) {
            const uuid = result.value.uuid;
            Swal.fire({
                title: 'Success!',
                text: 'Support pin validated successfully. Redirecting to user profile...',
                icon: 'success',
                timer: 1500,
                showConfirmButton: false,
            }).then(() => {
                router.push(`/mc-admin/users/${uuid}/edit`);
            });
        }
    });
};

onMounted(() => {
    fetchUsers();
    fetchRoles();
});
</script>
