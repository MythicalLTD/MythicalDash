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
                            Pterodactyl Location
                        </label>

                        <!-- Toggle between dropdown and manual input -->
                        <div class="flex space-x-2 mb-2">
                            <button
                                type="button"
                                @click="useDropdown = true"
                                :class="[
                                    'px-3 py-1 text-xs rounded transition-colors',
                                    useDropdown
                                        ? 'bg-pink-500 text-white'
                                        : 'bg-gray-700 text-gray-300 hover:bg-gray-600',
                                ]"
                            >
                                Select from List
                            </button>
                            <button
                                type="button"
                                @click="useDropdown = false"
                                :class="[
                                    'px-3 py-1 text-xs rounded transition-colors',
                                    !useDropdown
                                        ? 'bg-pink-500 text-white'
                                        : 'bg-gray-700 text-gray-300 hover:bg-gray-600',
                                ]"
                            >
                                Enter Manually
                            </button>
                        </div>

                        <!-- Dropdown input -->
                        <select
                            v-if="useDropdown"
                            id="pterodactyl_location_id"
                            v-model="locationForm.pterodactyl_location_id"
                            class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                        >
                            <option value="0">Select a location</option>
                            <option v-for="location in pterodactylLocations" :key="location.id" :value="location.id">
                                {{ location.short }} - {{ location.long }}
                            </option>
                        </select>

                        <!-- Manual input -->
                        <input
                            v-else
                            id="pterodactyl_location_id_manual"
                            v-model="manualLocationId"
                            type="number"
                            min="1"
                            class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                            placeholder="Enter Pterodactyl location ID"
                        />

                        <p class="text-xs text-gray-400 mt-1">
                            {{
                                useDropdown
                                    ? 'Select from available Pterodactyl locations'
                                    : 'Enter the numeric ID of the Pterodactyl location'
                            }}
                        </p>
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

                    <div>
                        <label for="vip_only" class="block text-sm font-medium text-gray-400 mb-1">VIP Only</label>
                        <select
                            id="vip_only"
                            v-model="locationForm.vip_only"
                            class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                        >
                            <option :value="false">No</option>
                            <option :value="true">Yes</option>
                        </select>
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

                    <div>
                        <label for="image" class="block text-sm font-medium text-gray-400 mb-1">Location Image</label>
                        <div class="flex flex-col space-y-4">
                            <select
                                id="image"
                                v-model="locationForm.image_id"
                                class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                            >
                                <option :value="null">Select an image</option>
                                <option v-for="image in images" :key="image.id" :value="image.id">
                                    {{ image.name }}
                                </option>
                            </select>
                            <div v-if="loadingImages" class="text-gray-400">Loading images...</div>
                            <div v-if="locationForm.image_id" class="mt-2">
                                <img
                                    :src="images.find((img) => img.id === locationForm.image_id)?.image"
                                    alt="Location preview"
                                    class="w-32 h-32 object-cover rounded-lg"
                                />
                            </div>
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
import { ref, onMounted, watch } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import LayoutDashboard from '@/components/admin/LayoutDashboard.vue';
import { ArrowLeftIcon, SaveIcon, LoaderIcon } from 'lucide-vue-next';
import Locations from '@/mythicaldash/admin/Locations';

const router = useRouter();
const route = useRoute();
const locationId = Number(route.params.id);

const loading = ref(true);
const saving = ref(false);
const error = ref('');
const successMessage = ref('');

// Input type toggle
const useDropdown = ref(true);
const manualLocationId = ref<number | null>(null);

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
    vip_only: false,
    image_id: null as number | null,
});

interface PterodactylLocation {
    id: number;
    short: string;
    long: string;
    created_at: string;
    updated_at: string;
}

const pterodactylLocations = ref<PterodactylLocation[]>([]);

interface Image {
    id: number;
    name: string;
    image: string;
    created_at: string;
    updated_at: string;
}

const images = ref<Image[]>([]);
const loadingImages = ref(true);

// Fetch images from API
const fetchImages = async () => {
    loadingImages.value = true;
    try {
        const response = await fetch('/api/admin/images', {
            method: 'GET',
            headers: {
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error('Failed to fetch images');
        }

        const data = await response.json();

        if (data.success) {
            images.value = data.images;
        } else {
            console.error('Failed to load images:', data.message);
        }
    } catch (error) {
        console.error('Error fetching images:', error);
    } finally {
        loadingImages.value = false;
    }
};

// Sync values between dropdown and manual input
watch(useDropdown, (newValue) => {
    if (newValue) {
        // Switching to dropdown - sync manual input to dropdown
        if (manualLocationId.value) {
            locationForm.value.pterodactyl_location_id = manualLocationId.value;
        }
    } else {
        // Switching to manual input - sync dropdown to manual input
        if (locationForm.value.pterodactyl_location_id) {
            manualLocationId.value = locationForm.value.pterodactyl_location_id;
        }
    }
});

// Watch for changes in manual input to sync to dropdown
watch(manualLocationId, (newValue) => {
    if (!useDropdown.value && newValue) {
        locationForm.value.pterodactyl_location_id = newValue;
    }
});

// Watch for changes in dropdown to sync to manual input
watch(
    () => locationForm.value.pterodactyl_location_id,
    (newValue) => {
        if (useDropdown.value && newValue) {
            manualLocationId.value = newValue;
        }
    },
);

onMounted(async () => {
    try {
        // Fetch Pterodactyl locations
        const locationsResponse = await Locations.getPterodactylLocations();
        pterodactylLocations.value = locationsResponse.locations;

        // Fetch images
        await fetchImages();

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
                image_id: number | null;
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
                vip_only: typeof location.vip_only !== 'undefined' ? location.vip_only : false,
                image_id: location.image_id,
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
        // Determine which location ID to use
        const pterodactylLocationId = useDropdown.value
            ? locationForm.value.pterodactyl_location_id
            : manualLocationId.value;

        // Validate location ID
        if (!pterodactylLocationId || pterodactylLocationId <= 0) {
            error.value = 'Please select a Pterodactyl location or enter a valid location ID';
            saving.value = false;
            return;
        }

        // Create FormData object
        const formData = new FormData();
        formData.append('name', locationForm.value.name);
        formData.append('description', locationForm.value.description);
        formData.append('node_ip', locationForm.value.node_ip);
        formData.append('status', locationForm.value.status);
        formData.append('slots', locationForm.value.slots.toString());
        formData.append('vip_only', locationForm.value.vip_only.toString());

        if (pterodactylLocationId) {
            formData.append('pterodactyl_location_id', pterodactylLocationId.toString());
        }
        if (locationForm.value.image_id) {
            formData.append('image_id', locationForm.value.image_id.toString());
        } else {
            formData.append('image_id', 'null');
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
