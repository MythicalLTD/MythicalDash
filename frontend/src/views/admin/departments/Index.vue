<template>
    <LayoutDashboard>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-400">Departments</h1>
            <button
                @click="goToCreation()"
                class="bg-gradient-to-r from-pink-500 to-violet-500 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:opacity-80 flex items-center"
            >
                <PlusIcon class="w-4 h-4 mr-2" />
                Add Department
            </button>
        </div>
        <!-- Departments Table using TableTanstack -->
        <div v-if="loading" class="flex justify-center items-center py-10">
            <LoaderCircle class="h-8 w-8 animate-spin text-pink-400" />
        </div>
        <TableTanstack v-else :data="departments" :columns="columns" tableName="Departments" />
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, onMounted, h } from 'vue';
import LayoutDashboard from '@/components/admin/LayoutDashboard.vue';
import TableTanstack from '@/components/client/ui/Table/TableTanstack.vue';
import { PlusIcon, EditIcon, TrashIcon, LoaderCircle } from 'lucide-vue-next';
import { useRouter } from 'vue-router';

// Department interface with the fields from the API
interface Department {
    id: number;
    name: string;
    description: string;
    time_open: string;
    time_close: string;
    enabled: string;
    created_at: string;
}

const router = useRouter();
const departments = ref<Department[]>([]);
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
        accessorKey: 'description',
        header: 'Description',
        cell: (info: { getValue: () => string }) => info.getValue(),
    },
    {
        accessorKey: 'time_open',
        header: 'Open Time',
        cell: (info: { getValue: () => string }) => info.getValue(),
    },
    {
        accessorKey: 'time_close',
        header: 'Close Time',
        cell: (info: { getValue: () => string }) => info.getValue(),
    },
    {
        accessorKey: 'enabled',
        header: 'Status',
        cell: (info: { getValue: () => string }) => {
            const enabled = info.getValue();
            return h(
                'span',
                {
                    class: {
                        'px-2 py-1 rounded-full text-xs font-medium': true,
                        'bg-green-500/20 text-green-400': enabled === 'true',
                        'bg-red-500/20 text-red-400': enabled === 'false',
                    },
                },
                enabled === 'true' ? 'Enabled' : 'Disabled',
            );
        },
    },
    {
        accessorKey: 'created_at',
        header: 'Created At',
        cell: (info: { getValue: () => string }) =>
            info.getValue() ? new Date(info.getValue()).toLocaleString() : 'N/A',
    },
    {
        id: 'actions',
        header: 'Actions',
        cell: (info: { row: { original: Department } }) => {
            const department = info.row.original;
            return h('div', { class: 'flex space-x-2' }, [
                h(
                    'button',
                    {
                        class: 'p-1 text-gray-400 hover:text-pink-400 transition-colors',
                        title: 'Edit',
                        onClick: () => editDepartment(department),
                    },
                    [h(EditIcon, { class: 'w-4 h-4' })],
                ),
                h(
                    'button',
                    {
                        class: 'p-1 text-gray-400 hover:text-red-400 transition-colors',
                        title: 'Delete',
                        onClick: () => confirmDelete(department),
                    },
                    [h(TrashIcon, { class: 'w-4 h-4' })],
                ),
            ]);
        },
    },
];

// Fetch departments from API
const fetchDepartments = async () => {
    loading.value = true;
    try {
        const response = await fetch('/api/admin/ticket/departments', {
            method: 'GET',
            headers: {
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error('Failed to fetch departments');
        }

        const data = await response.json();

        if (data.success) {
            departments.value = data.departments;
        } else {
            console.error('Failed to load departments:', data.message);
        }
    } catch (error) {
        console.error('Error fetching departments:', error);
    } finally {
        loading.value = false;
    }
};

const goToCreation = () => {
    router.push('/mc-admin/departments/create');
};

const editDepartment = (department: Department) => {
    router.push(`/mc-admin/departments/${department.id}/edit`);
};

const confirmDelete = (department: Department) => {
    router.push(`/mc-admin/departments/${department.id}/delete`);
};

onMounted(() => {
    fetchDepartments();
});
</script>
