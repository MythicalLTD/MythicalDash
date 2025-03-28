<template>
    <LayoutDashboard>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-400">Edit Location</h1>
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
            <form @submit.prevent="updateLocation" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-400 mb-1">Name</label>
                        <input
                            id="name"
                            v-model="locationForm.name"
                            type="text"
                            required
                            class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                            placeholder="e.g. New York, US East"
                        />
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-400 mb-1">Status</label>
                        <select
                            id="status"
                            v-model="locationForm.status"
                            class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                        >
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-400 mb-1"
                            >Description</label
                        >
                        <input
                            id="description"
                            v-model="locationForm.description"
                            type="text"
                            required
                            class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                            placeholder="e.g. East Coast Data Center"
                        />
                    </div>

                    <div>
                        <label for="node_ip" class="block text-sm font-medium text-gray-400 mb-1">Node IP</label>
                        <input
                            id="node_ip"
                            v-model="locationForm.node_ip"
                            type="text"
                            class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                            placeholder="e.g. 192.168.1.1"
                        />
                    </div>

                    <div>
                        <label for="pterodactyl_location_id" class="block text-sm font-medium text-gray-400 mb-1">
                            Pterodactyl Location ID
                        </label>
                        <input
                            id="pterodactyl_location_id"
                            v-model="locationForm.pterodactyl_location_id"
                            type="number"
                            class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                            placeholder="e.g. 1"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-1">Created At</label>
                        <div class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 text-gray-400">
                            {{ new Date(locationForm.created_at).toLocaleString() }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-1">Last Updated</label>
                        <div class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 text-gray-400">
                            {{ new Date(locationForm.updated_at).toLocaleString() }}
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-700">
                    <button
                        type="button"
                        @click="router.push('/mc-admin/locations')"
                        class="px-4 py-2 border border-gray-600 rounded-lg text-gray-300 hover:bg-gray-700 transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        :disabled="saving"
                        class="px-4 py-2 bg-gradient-to-r from-pink-500 to-violet-500 rounded-lg text-white hover:opacity-90 transition-colors flex items-center"
                    >
                        <LoaderIcon v-if="saving" class="animate-spin w-4 h-4 mr-2" />
                        <SaveIcon v-else class="w-4 h-4 mr-2" />
                        Update Location
                    </button>
                </div>
            </form>
        </div>
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import LayoutDashboard from '@/components/admin/LayoutDashboard.vue';
import { ArrowLeftIcon, SaveIcon, LoaderIcon } from 'lucide-vue-next';

const router = useRouter();
const route = useRoute();
const locationId = Number(route.params.id);

const loading = ref(true);
const saving = ref(false);
const error = ref('');

// Form state with default values
const locationForm = ref({
    id: locationId,
    name: '',
    description: '',
    pterodactyl_location_id: null as number | null,
    node_ip: '',
    status: 'active' as 'active' | 'inactive',
    created_at: '',
    updated_at: '',
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
        const location = mockLocations.find((loc) => loc.id === locationId);

        if (!location) {
            error.value = 'Location not found';
            return;
        }

        // Populate the form with location data
    } catch (err) {
        error.value = 'Failed to load location data';
        console.error(err);
    } finally {
        loading.value = false;
    }
});

const updateLocation = async () => {
    saving.value = true;

    try {
        // Simulate API call
        await new Promise((resolve) => setTimeout(resolve, 1000));

        // Update the updated_at timestamp
        locationForm.value.updated_at = new Date().toISOString();

        // In a real app, you would send the data to your API here
        console.log('Updating location:', locationForm.value);

        // Redirect back to locations list
        router.push('/mc-admin/locations');
    } catch (error) {
        console.error('Error updating location:', error);
    } finally {
        saving.value = false;
    }
};
</script>
