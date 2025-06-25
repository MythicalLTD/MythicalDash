<template>
    <LayoutDashboard>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-400">Roles</h1>
            <button
                @click="goToCreation()"
                class="bg-gradient-to-r from-pink-500 to-violet-500 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:opacity-80 flex items-center"
            >
                <PlusIcon class="w-4 h-4 mr-2" />
                Add Role
            </button>
        </div>
        <!-- Roles Table using TableTanstack -->
        <div v-if="loading" class="flex justify-center items-center py-10">
            <LoaderCircle class="h-8 w-8 animate-spin text-pink-400" />
        </div>
        <TableTanstack v-else :data="roles" :columns="columns" tableName="Roles" />
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, onMounted, h } from 'vue';
import LayoutDashboard from '@/components/admin/LayoutDashboard.vue';
import TableTanstack from '@/components/client/ui/Table/TableTanstack.vue';
import { PlusIcon, EditIcon, TrashIcon, LoaderCircle, ShieldIcon } from 'lucide-vue-next';
import { useRouter } from 'vue-router';
import Roles from '@/mythicaldash/admin/Roles';

interface Role {
    id: number;
    name: string;
    real_name: string;
    color: string;
    deleted: string;
    locked: string;
    date: string;
}

const router = useRouter();
const roles = ref<Role[]>([]);
const loading = ref(true);

// Define columns for TableTanstack
const columns = [
    {
        accessorKey: 'id',
        header: 'ID',
        cell: (info: { getValue: () => number }) => info.getValue(),
    },
    {
        accessorKey: 'name',
        header: 'Name',
        cell: (info: { getValue: () => string }) => info.getValue(),
    },
    {
        accessorKey: 'real_name',
        header: 'Display Name',
        cell: (info: { getValue: () => string }) => info.getValue(),
    },
    {
        accessorKey: 'color',
        header: 'Color',
        cell: (info: { getValue: () => string }) => {
            const color = info.getValue();
            return h('div', { class: 'flex items-center gap-3' }, [
                h('div', {
                    class: 'w-8 h-8 rounded-lg border-2 border-gray-600 shadow-md',
                    style: { backgroundColor: color },
                }),
                h('span', { class: 'text-sm font-mono bg-gray-800 px-2 py-1 rounded' }, color.toUpperCase()),
            ]);
        },
    },
    {
        accessorKey: 'date',
        header: 'Created',
        cell: (info: { getValue: () => string }) => {
            const date = new Date(info.getValue());
            return h('span', { class: 'text-sm text-gray-400' }, date.toLocaleDateString());
        },
    },
    {
        id: 'actions',
        header: 'Actions',
        cell: (info: { row: { original: Role } }) => {
            const role = info.row.original;
            return h('div', { class: 'flex space-x-2' }, [
                h(
                    'button',
                    {
                        class: 'p-1 text-gray-400 hover:text-blue-400 transition-colors',
                        title: 'View Permissions',
                        onClick: () => viewPermissions(role),
                    },
                    [h(ShieldIcon, { class: 'w-4 h-4' })],
                ),
                h(
                    'button',
                    {
                        class: 'p-1 text-gray-400 hover:text-pink-400 transition-colors',
                        title: 'Edit',
                        onClick: () => editRole(role),
                    },
                    [h(EditIcon, { class: 'w-4 h-4' })],
                ),
                h(
                    'button',
                    {
                        class: 'p-1 text-gray-400 hover:text-red-400 transition-colors',
                        title: 'Delete',
                        onClick: () => confirmDelete(role),
                    },
                    [h(TrashIcon, { class: 'w-4 h-4' })],
                ),
            ]);
        },
    },
];

// Fetch roles from API
const fetchRoles = async () => {
    loading.value = true;
    try {
        const response = await Roles.getRoles();

        if (response.success) {
            roles.value = response.roles;
        } else {
            console.error('Failed to load roles:', response.message);
        }
    } catch (error) {
        console.error('Error fetching roles:', error);
    } finally {
        loading.value = false;
    }
};

const goToCreation = () => {
    router.push('/mc-admin/roles/create');
};

const editRole = (role: Role) => {
    router.push(`/mc-admin/roles/${role.id}/edit`);
};

const confirmDelete = (role: Role) => {
    router.push(`/mc-admin/roles/${role.id}/delete`);
};

const viewPermissions = (role: Role) => {
    router.push(`/mc-admin/roles/${role.id}/permissions`);
};

onMounted(() => {
    fetchRoles();
});
</script>
