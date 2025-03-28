<template>
    <LayoutDashboard>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-400">Delete Location</h1>
            <button
                @click="router.push('/mc-admin/locations')"
                class="bg-gray-700 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:bg-gray-600 flex items-center"
            >
                <ArrowLeftIcon class="w-4 h-4 mr-2" />
                Back to Locations
            </button>
        </div>

        <div v-if="loading" class="flex justify-center items-center py-12">
            <LoaderIcon class="w-8 h-8 animate-spin text-pink-400" />
        </div>

        <div v-else-if="error" class="bg-red-500/20 text-red-400 p-4 rounded-lg mb-6">
            {{ error }}
        </div>

        <div v-else class="bg-gray-800/50 backdrop-blur-md rounded-lg p-6">
            <div class="text-center">
                <AlertTriangleIcon class="w-16 h-16 mx-auto text-red-400 mb-4" />
                <h2 class="text-xl font-medium text-gray-100 mb-2">Confirm Deletion</h2>
                <p class="text-gray-300 mb-2">
                    Are you sure you want to delete the location
                    <span class="font-medium text-white">{{ location.name }}</span
                    >?
                </p>
                <p class="text-gray-400 mb-6">This action cannot be undone.</p>

                <div class="bg-gray-900/50 rounded-lg p-4 mb-6 text-left">
                    <h3 class="text-md font-medium text-gray-300 mb-2">Location Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div><span class="text-gray-400">ID:</span> {{ location.id }}</div>
                        <div><span class="text-gray-400">Name:</span> {{ location.name }}</div>
                        <div><span class="text-gray-400">Description:</span> {{ location.description }}</div>
                        <div>
                            <span class="text-gray-400">Status:</span>
                            <span :class="location.status === 'active' ? 'text-green-400' : 'text-red-400'">
                                {{ location.status === 'active' ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <div><span class="text-gray-400">Node IP:</span> {{ location.node_ip }}</div>
                        <div>
                            <span class="text-gray-400">Pterodactyl ID:</span>
                            {{ location.pterodactyl_location_id || 'N/A' }}
                        </div>
                    </div>
                </div>

                <div class="flex justify-center space-x-3">
                    <button
                        @click="router.push('/mc-admin/locations')"
                        class="px-4 py-2 border border-gray-600 rounded-lg text-gray-300 hover:bg-gray-700 transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        @click="deleteLocation"
                        :disabled="deleting"
                        class="px-4 py-2 bg-red-500 rounded-lg text-white hover:bg-red-600 transition-colors flex items-center"
                    >
                        <LoaderIcon v-if="deleting" class="animate-spin w-4 h-4 mr-2" />
                        <TrashIcon v-else class="w-4 h-4 mr-2" />
                        Delete Location
                    </button>
                </div>
            </div>
        </div>
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import LayoutDashboard from '@/components/admin/LayoutDashboard.vue';
import { ArrowLeftIcon, AlertTriangleIcon, TrashIcon, LoaderIcon } from 'lucide-vue-next';

const router = useRouter();
const route = useRoute();
const locationId = Number(route.params.id);

const loading = ref(true);
const deleting = ref(false);
const error = ref('');
const location = ref({
    id: 0,
    name: '',
    description: '',
    pterodactyl_location_id: null as number | null,
    node_ip: '',
    status: 'active' as 'active' | 'inactive',
    updated_at: '',
    created_at: '',
});

// Mock data for demonstration
const mockLocations = [
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
];

onMounted(async () => {
    try {
        // Simulate API call to fetch location data
        await new Promise((resolve) => setTimeout(resolve, 1000));

        // Find the location in our mock data
        const foundLocation = mockLocations.find((loc) => loc.id === locationId);

        if (!foundLocation) {
            error.value = 'Location not found';
            return;
        }

        // Set the location data with proper type assertion
        location.value = {
            ...foundLocation,
            status: foundLocation.status as 'active' | 'inactive',
        };
    } catch (err) {
        error.value = 'Failed to load location data';
        console.error(err);
    } finally {
        loading.value = false;
    }
});

const deleteLocation = async () => {
    deleting.value = true;

    try {
        // Simulate API call
        await new Promise((resolve) => setTimeout(resolve, 1000));

        // In a real app, you would send a delete request to your API here
        console.log('Deleting location:', location.value);

        // Redirect back to locations list
        router.push('/mc-admin/locations');
    } catch (error) {
        console.error('Error deleting location:', error);
    } finally {
        deleting.value = false;
    }
};
</script>
