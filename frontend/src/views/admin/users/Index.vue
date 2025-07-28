<template>
    <LayoutDashboard>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-400">Users</h1>
            <button
                @click="supportPinModal()"
                class="bg-gradient-to-r from-pink-500 to-violet-500 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:opacity-80 flex items-center"
            >
                <SearchIcon class="w-4 h-4 mr-2" />
                Enter Support Pin
            </button>
        </div>
        <!-- Users Table using TableTanstack -->
        <div v-if="loading" class="flex justify-center items-center py-10">
            <LoaderCircle class="h-8 w-8 animate-spin text-pink-400" />
        </div>
        <TableTanstack v-else :data="users" :columns="columns" tableName="Users" />
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, onMounted, h } from 'vue';
import LayoutDashboard from '@/components/admin/LayoutDashboard.vue';
import TableTanstack from '@/components/client/ui/Table/TableTanstack.vue';
import { EditIcon, TrashIcon, LoaderCircle, ClockIcon, SearchIcon } from 'lucide-vue-next';
import { useRouter } from 'vue-router';
import Swal from 'sweetalert2';
import Roles from '@/mythicaldash/admin/Roles';

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

// Get role info by ID
const getRoleInfo = (roleId: number) => {
    const role = rolesData.value.find((r) => r.id === roleId);
    if (role) {
        // Convert hex color to rgba for background with opacity
        const hex = role.color.replace('#', '');
        const r = parseInt(hex.substr(0, 2), 16);
        const g = parseInt(hex.substr(2, 2), 16);
        const b = parseInt(hex.substr(4, 2), 16);

        return {
            name: role.name,
            color: role.color,
            backgroundColor: `rgba(${r}, ${g}, ${b}, 0.2)`,
        };
    }
    return {
        name: `Role ${roleId}`,
        color: '#9CA3AF',
        backgroundColor: 'rgba(107, 114, 128, 0.2)',
    };
};

// Define columns for TableTanstack
const columns = [
    {
        accessorKey: 'id',
        header: 'ID',
        cell: (info: { getValue: () => number }) => info.getValue(),
    },
    {
        accessorKey: 'avatar',
        header: 'Avatar',
        cell: (info: { getValue: () => string }) => {
            const avatar = info.getValue();
            return h('img', {
                src: avatar,
                alt: 'User Avatar',
                class: 'w-8 h-8 rounded-full',
            });
        },
    },
    {
        accessorKey: 'username',
        header: 'Username',
        cell: (info: { getValue: () => string }) => info.getValue(),
    },
    {
        accessorKey: 'email',
        header: 'Email',
        cell: (info: { getValue: () => string }) => info.getValue(),
    },
    {
        accessorKey: 'role',
        header: 'Role',
        cell: (info: { getValue: () => number }) => {
            const roleId = info.getValue();
            const roleInfo = getRoleInfo(roleId);

            return h(
                'span',
                {
                    class: 'px-2 py-1 rounded-full text-xs font-medium',
                    style: {
                        backgroundColor: roleInfo.backgroundColor,
                        color: roleInfo.color,
                    },
                },
                roleInfo.name,
            );
        },
    },
    {
        accessorKey: 'last_seen',
        header: 'Last Seen',
        cell: (info: { getValue: () => string }) => {
            const lastSeen = info.getValue();
            return h('div', { class: 'flex items-center' }, [
                h(ClockIcon, { class: 'w-3 h-3 mr-1 text-gray-400' }),
                h('span', {}, new Date(lastSeen).toLocaleString()),
            ]);
        },
    },
    {
        id: 'actions',
        header: 'Actions',
        cell: (info: { row: { original: User } }) => {
            const user = info.row.original;
            return h('div', { class: 'flex space-x-2' }, [
                h(
                    'button',
                    {
                        class: 'p-1 text-gray-400 hover:text-pink-400 transition-colors',
                        title: 'Edit',
                        onClick: () => editUser(user),
                    },
                    [h(EditIcon, { class: 'w-4 h-4' })],
                ),
                h(
                    'button',
                    {
                        class: 'p-1 text-gray-400 hover:text-red-400 transition-colors',
                        title: 'Delete',
                        onClick: () => confirmDelete(user),
                    },
                    [h(TrashIcon, { class: 'w-4 h-4' })],
                ),
            ]);
        },
    },
];

// Fetch users from API
const fetchUsers = async () => {
    loading.value = true;
    try {
        const response = await fetch('/api/admin/users', {
            method: 'GET',
            headers: {
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error('Failed to fetch users');
        }

        const data = await response.json();

        if (data.success) {
            users.value = data.users;
        } else {
            console.error('Failed to load users:', data.message);
        }
    } catch (error) {
        console.error('Error fetching users:', error);
    } finally {
        loading.value = false;
    }
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
                    headers: {
                        Accept: 'application/json',
                    },
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
                // Redirect to the user edit page with the UUID
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
