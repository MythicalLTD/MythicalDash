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
                            <option value="online">Online</option>
                            <option value="offline">Offline</option>
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
                        <label for="slots" class="block text-sm font-medium text-gray-400 mb-1">Slots</label>
                        <input
                            type="number"
                            id="slots"
                            v-model="locationForm.slots"
                            class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                        />
                    </div>

                    <div v-if="locationForm.created_at">
                        <label class="block text-sm font-medium text-gray-400 mb-1">Created At</label>
                        <div class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 text-gray-400">
                            {{ new Date(locationForm.created_at).toLocaleString() }}
                        </div>
                    </div>

                    <div v-if="locationForm.updated_at">
                        <label class="block text-sm font-medium text-gray-400 mb-1">Last Updated</label>
                        <div class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 text-gray-400">
                            {{ new Date(locationForm.updated_at).toLocaleString() }}
                        </div>
                    </div>
                </div>

                <div v-if="successMessage" class="bg-green-500/20 text-green-400 p-4 rounded-lg mb-6">
                    {{ successMessage }}
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
const successMessage = ref('');

// Form state with default values
const locationForm = ref({
    id: locationId,
    name: '',
    description: '',
    pterodactyl_location_id: null as number | null,
    node_ip: '',
    status: 'online',
    deleted: 'false',
    locked: 'false',
    created_at: '',
    updated_at: '',
    slots: 15,
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
            // Define a type for the location object from API
            interface ApiLocation {
                id: number;
                name: string;
                description: string;
                pterodactyl_location_id: number | null;
                node_ip: string;
                status: string;
                deleted: string;
                locked: string;
                created_at: string;
                updated_at: string;
                slots: number;
            }

            // Find the location with the matching ID
            const location = data.locations.find((loc: ApiLocation) => loc.id === locationId);

            if (!location) {
                error.value = 'Location not found';
                return;
            }

            // Populate the form with location data
            locationForm.value = {
                id: location.id,
                name: location.name,
                description: location.description,
                pterodactyl_location_id: location.pterodactyl_location_id,
                node_ip: location.node_ip,
                status: location.status,
                deleted: location.deleted,
                locked: location.locked,
                created_at: location.created_at,
                updated_at: location.updated_at,
                slots: location.slots,
            };
        } else {
            error.value = data.message || 'Failed to load location data';
        }
    } catch (err) {
        console.error('Error fetching location data:', err);
        throw err;
    }
};

const updateLocation = async () => {
    saving.value = true;
    successMessage.value = '';
    error.value = '';

    try {
        // Create FormData object
        const formData = new FormData();
        formData.append('name', locationForm.value.name);
        formData.append('description', locationForm.value.description);
        formData.append('node_ip', locationForm.value.node_ip);
        formData.append('status', locationForm.value.status);
        formData.append('slots', locationForm.value.slots.toString());
        if (locationForm.value.pterodactyl_location_id) {
            formData.append('pterodactyl_location_id', locationForm.value.pterodactyl_location_id.toString());
        }

        // Send update request to API
        const response = await fetch(`/api/admin/locations/${locationId}/update`, {
            method: 'POST',
            body: formData,
        });

        const data = await response.json();

        if (data.success) {
            successMessage.value = 'Location updated successfully';
            // Wait a moment before redirecting
            setTimeout(() => {
                router.push('/mc-admin/locations');
            }, 1500);
        } else {
            error.value = data.message || 'Failed to update location';
        }
    } catch (err) {
        console.error('Error updating location:', err);
        error.value = 'An error occurred while updating the location';
    } finally {
        saving.value = false;
    }
};
</script>
