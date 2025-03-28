<template>
    <LayoutDashboard>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-400">Locations</h1>
            <button
                @click="goToCreation()"
                class="bg-gradient-to-r from-pink-500 to-violet-500 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:opacity-80 flex items-center"
            >
                <PlusIcon class="w-4 h-4 mr-2" />
                Add Location
            </button>
        </div>
        <!-- Locations Table using TableTanstack -->
        <TableTanstack :data="locations" :columns="columns" tableName="Locations" />
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, onMounted, h } from 'vue';
import LayoutDashboard from '@/components/admin/LayoutDashboard.vue';
import TableTanstack from '@/components/client/ui/Table/TableTanstack.vue';
import { PlusIcon, EditIcon, TrashIcon } from 'lucide-vue-next';
import { useRouter } from 'vue-router';

// Updated Location interface with the requested fields
interface Location {
    id: number;
    name: string;
    description: string;
    pterodactyl_location_id: number | null;
    node_ip: string;
    status: 'active' | 'inactive';
    updated_at: string;
    created_at: string;
}

const router = useRouter();

// Mock data with the new structure
const locations = ref<Location[]>([
    {
        id: 1,
        name: 'US East',
        description: 'East Coast Data Center',
        pterodactyl_location_id: 1,
        node_ip: '192.168.1.10',
        status: 'active',
        updated_at: '2023-05-15T12:30:00Z',
        created_at: '2023-05-15T10:30:00Z',
    },
    {
        id: 2,
        name: 'EU West',
        description: 'Frankfurt Data Center',
        pterodactyl_location_id: 2,
        node_ip: '192.168.2.10',
        status: 'active',
        updated_at: '2023-06-20T15:45:00Z',
        created_at: '2023-06-20T14:45:00Z',
    },
    {
        id: 3,
        name: 'Asia Pacific',
        description: 'Singapore Data Center',
        pterodactyl_location_id: 3,
        node_ip: '192.168.3.10',
        status: 'inactive',
        updated_at: '2023-07-10T09:15:00Z',
        created_at: '2023-07-10T08:15:00Z',
    },
]);

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
        accessorKey: 'pterodactyl_location_id',
        header: 'Pterodactyl ID',
        cell: (info: { getValue: () => number | null }) => info.getValue() || 'N/A',
    },
    {
        accessorKey: 'node_ip',
        header: 'Node IP',
        cell: (info: { getValue: () => string }) => info.getValue(),
    },
    {
        accessorKey: 'status',
        header: 'Status',
        cell: (info: { getValue: () => string }) => {
            const status = info.getValue();
            return h(
                'span',
                {
                    class: {
                        'px-2 py-1 rounded-full text-xs font-medium': true,
                        'bg-green-500/20 text-green-400': status === 'active',
                        'bg-red-500/20 text-red-400': status === 'inactive',
                    },
                },
                status === 'active' ? 'Active' : 'Inactive',
            );
        },
    },
    {
        accessorKey: 'updated_at',
        header: 'Updated At',
        cell: (info: { getValue: () => string }) => new Date(info.getValue()).toLocaleString(),
    },
    {
        accessorKey: 'created_at',
        header: 'Created At',
        cell: (info: { getValue: () => string }) => new Date(info.getValue()).toLocaleString(),
    },
    {
        id: 'actions',
        header: 'Actions',
        cell: (info: { row: { original: Location } }) => {
            const location = info.row.original;
            return h('div', { class: 'flex space-x-2' }, [
                h(
                    'button',
                    {
                        class: 'p-1 text-gray-400 hover:text-pink-400 transition-colors',
                        title: 'Edit',
                        onClick: () => editLocation(location),
                    },
                    [h(EditIcon, { class: 'w-4 h-4' })],
                ),
                h(
                    'button',
                    {
                        class: 'p-1 text-gray-400 hover:text-red-400 transition-colors',
                        title: 'Delete',
                        onClick: () => confirmDelete(location),
                    },
                    [h(TrashIcon, { class: 'w-4 h-4' })],
                ),
            ]);
        },
    },
];

// Pagination
const loading = ref(false);

const goToCreation = () => {
    router.push('/mc-admin/locations/create');
};

const editLocation = (location: Location) => {
    router.push(`/mc-admin/locations/${location.id}/edit`);
};

const confirmDelete = (location: Location) => {
    router.push(`/mc-admin/locations/${location.id}/delete`);
};

onMounted(() => {
    // Simulate loading data from API
    loading.value = true;
    setTimeout(() => {
        loading.value = false;
    }, 500);
});
</script>
