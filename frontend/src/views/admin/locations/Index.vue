<template>
    <LayoutDashboard>
        <!-- Sponsor Banner -->
        <DismissibleBanner
            cookie-key="sponsor_banner_hidden"
            title="Support MythicalDash"
            subtitle="Help us continue providing free updates and patches"
            :icon="Heart"
            class="mb-4"
        >
            <p class="text-sm text-gray-300 mb-4 leading-relaxed">
                MythicalDash has been providing <strong class="text-white">free updates and patches since 2021</strong>.
                We're committed to keeping the platform free and open-source. Your support helps us maintain, improve,
                and add new features to MythicalDash.
            </p>

            <template #actions>
                <a
                    href="https://donate.stripe.com/00gcO2epX5yj2ysfYY"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="group flex-1 flex items-center justify-center gap-2 py-2.5 px-4 bg-black hover:bg-gray-900 border border-gray-800 hover:border-gray-700 rounded-lg text-sm font-medium text-white transition-all duration-200"
                >
                    <span>Donate via Stripe</span>
                    <ExternalLink class="w-4 h-4" />
                </a>
                <a
                    href="https://www.paypal.com/paypalme/nayskutzu"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="group flex-1 flex items-center justify-center gap-2 py-2.5 px-4 bg-gray-800 hover:bg-gray-700 border border-gray-700 hover:border-gray-600 rounded-lg text-sm font-medium text-gray-200 hover:text-white transition-all duration-200"
                >
                    <span>Donate via PayPal</span>
                    <ExternalLink class="w-4 h-4" />
                </a>
            </template>
        </DismissibleBanner>

        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-400">Locations</h1>
            <button
                @click="goToCreation()"
                class="bg-linear-to-r from-pink-500 to-violet-500 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:opacity-80 flex items-center"
            >
                <PlusIcon class="w-4 h-4 mr-2" />
                Add Location
            </button>
        </div>
        <!-- Locations Table using TableTanstack -->
        <div v-if="loading" class="flex justify-center items-center py-10">
            <LoaderCircle class="h-8 w-8 animate-spin text-pink-400" />
        </div>
        <TableTanstack v-else :data="locations" :columns="columns" tableName="Locations" />
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, onMounted, h } from 'vue';
import LayoutDashboard from '@/components/admin/LayoutDashboard.vue';
import DismissibleBanner from '@/components/admin/DismissibleBanner.vue';
import TableTanstack from '@/components/client/ui/Table/TableTanstack.vue';
import { PlusIcon, EditIcon, TrashIcon, LoaderCircle, Heart, ExternalLink } from 'lucide-vue-next';
import { useRouter } from 'vue-router';

// Updated Location interface with the requested fields from the API
interface Location {
    id: number;
    name: string;
    description: string;
    pterodactyl_location_id: number | null;
    node_ip: string;
    status: string;
    updated_at: string;
    created_at: string;
}

const router = useRouter();
const locations = ref<Location[]>([]);
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
                        'bg-green-500/20 text-green-400': status === 'online',
                        'bg-red-500/20 text-red-400': status === 'offline',
                        'bg-yellow-500/20 text-yellow-400': status !== 'online' && status !== 'offline',
                    },
                },
                status === 'online' ? 'Online' : status === 'offline' ? 'Offline' : status,
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

// Fetch locations from API
const fetchLocations = async () => {
    loading.value = true;
    try {
        const response = await fetch('/api/admin/locations', {
            method: 'GET',
            headers: {
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error('Failed to fetch locations');
        }

        const data = await response.json();

        if (data.success) {
            locations.value = data.locations;
        } else {
            console.error('Failed to load locations:', data.message);
        }
    } catch (error) {
        console.error('Error fetching locations:', error);
    } finally {
        loading.value = false;
    }
};

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
    fetchLocations();
});
</script>
