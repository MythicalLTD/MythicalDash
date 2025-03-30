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
                            <span class="text-gray-400">Status: </span>
                            <span :class="location.status === 'online' ? 'text-green-400' : 'text-red-400'">
                                {{ location.status === 'online' ? 'Online' : 'Offline' }}
                            </span>
                        </div>
                        <div><span class="text-gray-400">Node IP:</span> {{ location.node_ip }}</div>
                        <div>
                            <span class="text-gray-400">Pterodactyl ID:</span>
                            {{ location.pterodactyl_location_id || 'N/A' }}
                        </div>
                    </div>
                </div>

                <div v-if="successMessage" class="bg-green-500/20 text-green-400 p-4 rounded-lg mb-6">
                    {{ successMessage }}
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
const successMessage = ref('');

// Interface for the location data from API
interface ApiLocation {
    id: number;
    name: string;
    description: string;
    pterodactyl_location_id: number | null;
    node_ip: string;
    status: string;
    updated_at: string;
    created_at: string;
}

const location = ref<ApiLocation>({
    id: 0,
    name: '',
    description: '',
    pterodactyl_location_id: null,
    node_ip: '',
    status: '',
    updated_at: '',
    created_at: '',
});

onMounted(async () => {
    try {
        // Fetch location data from API
        await fetchLocationData();
    } catch (err) {
        error.value = 'Failed to load location data';
        console.error(err);
    } finally {
        loading.value = false;
    }
});

// Fetch location data from API
const fetchLocationData = async () => {
    try {
        const response = await fetch(`/api/admin/locations`, {
            method: 'GET',
            headers: {
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error('Failed to fetch location data');
        }

        const data = await response.json();

        if (data.success) {
            // Find the location with the matching ID
            const foundLocation = data.locations.find((loc: ApiLocation) => loc.id === locationId);

            if (!foundLocation) {
                error.value = 'Location not found';
                return;
            }

            // Populate the location data
            location.value = foundLocation;
        } else {
            error.value = data.message || 'Failed to load location data';
        }
    } catch (err) {
        console.error('Error fetching location data:', err);
        throw err;
    }
};

const deleteLocation = async () => {
    deleting.value = true;
    successMessage.value = '';
    error.value = '';

    try {
        // Create FormData for the delete request
        const formData = new FormData();

        // Send delete request to API
        const response = await fetch(`/api/admin/locations/${locationId}/delete`, {
            method: 'POST',
            body: formData,
        });

        const data = await response.json();

        if (data.success) {
            successMessage.value = 'Location deleted successfully';
            // Wait a moment before redirecting
            setTimeout(() => {
                router.push('/mc-admin/locations');
            }, 1500);
        } else {
            error.value = data.message || 'Failed to delete location';
            deleting.value = false;
        }
    } catch (err) {
        console.error('Error deleting location:', err);
        error.value = 'An error occurred while deleting the location';
        deleting.value = false;
    }
};
</script>
