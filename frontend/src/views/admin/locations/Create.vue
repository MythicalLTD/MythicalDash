<template>
    <LayoutDashboard>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-400">Create Location</h1>
            <button
                @click="router.push('/mc-admin/locations')"
                class="bg-gray-700 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:bg-gray-600 flex items-center"
            >
                <ArrowLeftIcon class="w-4 h-4 mr-2" />
                Back to Locations
            </button>
        </div>

        <div class="bg-gray-800/50 backdrop-blur-md rounded-lg p-6">
            <form @submit.prevent="saveLocation" class="space-y-6">
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
                        <p class="text-xs text-gray-400 mt-1">This is used to identify the location in the UI</p>
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
                            <option value="maintenance">Maintenance</option>
                        </select>
                        <p class="text-xs text-gray-400 mt-1">This is used to identify the status of the location</p>
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
                        <p class="text-xs text-gray-400 mt-1">
                            This is used to identify the description of the location
                        </p>
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
                        <p class="text-xs text-gray-400 mt-1">This is used to identify the node IP of the location</p>
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
                        :disabled="loading"
                        class="px-4 py-2 bg-gradient-to-r from-pink-500 to-violet-500 rounded-lg text-white hover:opacity-90 transition-colors flex items-center"
                    >
                        <LoaderIcon v-if="loading" class="animate-spin w-4 h-4 mr-2" />
                        <SaveIcon v-else class="w-4 h-4 mr-2" />
                        Create Location
                    </button>
                </div>
            </form>
        </div>
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import LayoutDashboard from '@/components/admin/LayoutDashboard.vue';
import { ArrowLeftIcon, SaveIcon, LoaderIcon } from 'lucide-vue-next';
import Locations from '@/mythicaldash/admin/Locations';
import Swal from 'sweetalert2';
import { useSound } from '@vueuse/sound';
import failedAlertSfx from '@/assets/sounds/error.mp3';
import successAlertSfx from '@/assets/sounds/success.mp3';

const router = useRouter();
const loading = ref(false);
const { play: playError } = useSound(failedAlertSfx);
const { play: playSuccess } = useSound(successAlertSfx);

// Input type toggle
const useDropdown = ref(true);
const manualLocationId = ref<number | null>(null);

// Form state
const locationForm = ref({
    name: '',
    description: '',
    pterodactyl_location_id: 0,
    node_ip: '',
    status: 'active' as 'active' | 'inactive' | 'maintenance',
    slots: 15,
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

Locations.getPterodactylLocations().then((locations) => {
    pterodactylLocations.value = locations.locations;
});

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

onMounted(() => {
    fetchImages();
});

const saveLocation = async () => {
    loading.value = true;

    try {
        // Determine which location ID to use
        const pterodactylLocationId = useDropdown.value
            ? locationForm.value.pterodactyl_location_id
            : manualLocationId.value;

        // Validate location ID
        if (!pterodactylLocationId || pterodactylLocationId <= 0) {
            playError();
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please select a Pterodactyl location or enter a valid location ID',
                showConfirmButton: true,
            });
            loading.value = false;
            return;
        }

        const response = await Locations.createLocation(
            locationForm.value.name,
            locationForm.value.description,
            pterodactylLocationId,
            locationForm.value.node_ip,
            locationForm.value.status,
            locationForm.value.slots,
            locationForm.value.image_id,
        );

        if (response.success) {
            playSuccess();
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Location created successfully',
                showConfirmButton: true,
            });

            // Navigate back to locations list after a short delay
            setTimeout(() => {
                router.push('/mc-admin/locations');
            }, 1500);
        } else {
            const errorMessages = {
                ERROR_LOCATION_ALREADY_EXISTS: 'A location with this Pterodactyl ID already exists',
                MISSING_REQUIRED_FIELDS: 'Please fill in all required fields',
                ERROR_INVALID_STATUS: 'Invalid status selected',
                ERROR_FAILED_TO_CREATE_LOCATION: 'Server failed to create the location',
                INVALID_SESSION: 'Your session has expired. Please log in again.',
                ERROR_INVALID_PTERODACTYL_LOCATION_ID: 'Invalid Pterodactyl location ID',
            };

            const error_code = response.error_code as keyof typeof errorMessages;
            const errorMessage = errorMessages[error_code] || 'Failed to create location';

            playError();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: errorMessage,
                footer: 'Please try again or contact support if the issue persists.',
                showConfirmButton: true,
            });

            // If session expired, redirect to login
            if (error_code === 'INVALID_SESSION') {
                setTimeout(() => {
                    router.push('/auth/login');
                }, 2000);
            }
        }
    } catch (error) {
        console.error('Error creating location:', error);
        playError();
        Swal.fire({
            icon: 'error',
            title: 'Connection Error',
            text: 'Failed to connect to the server. Please check your internet connection and try again.',
            footer: 'If the problem persists, please contact support.',
            showConfirmButton: true,
        });
    } finally {
        loading.value = false;
    }
};
</script>
