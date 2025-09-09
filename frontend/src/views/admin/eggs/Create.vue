<template>
    <LayoutDashboard>
        <div class="flex items-center mb-6">
            <button @click="router.back()" class="mr-4 text-gray-400 hover:text-white transition-colors">
                <ArrowLeftIcon class="h-5 w-5" />
            </button>
            <h1 class="text-2xl font-bold text-pink-400">Create Egg</h1>
        </div>

        <div class="bg-gray-800 rounded-lg p-6 shadow-md">
            <form @submit.prevent="saveEgg" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-300 mb-1">Name</label>
                        <input
                            id="name"
                            v-model="eggForm.name"
                            type="text"
                            class="w-full bg-gray-700 border border-gray-600 rounded-md py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-pink-500"
                            placeholder="Enter egg name"
                            required
                        />
                    </div>

                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-300 mb-1">Category</label>
                        <select
                            id="category"
                            v-model="eggForm.category"
                            class="w-full bg-gray-700 border border-gray-600 rounded-md py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-pink-500"
                            required
                        >
                            <option value="0" disabled>Select a category</option>
                            <option v-for="category in categories" :key="category.id" :value="category.id">
                                {{ category.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label for="pterodactyl_egg_id" class="block text-sm font-medium text-gray-300 mb-1"
                            >Pterodactyl Egg</label
                        >

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
                            id="pterodactyl_egg_id"
                            v-model="eggForm.pterodactyl_egg_id"
                            class="w-full bg-gray-700 border border-gray-600 rounded-md py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-pink-500"
                            required
                        >
                            <option value="0" disabled>Select a Pterodactyl egg</option>
                            <option v-for="egg in pterodactylEggs" :key="egg.id" :value="egg.id">
                                {{ egg.name }}
                            </option>
                        </select>

                        <!-- Manual input -->
                        <input
                            v-else
                            id="pterodactyl_egg_id_manual"
                            v-model="manualEggId"
                            type="number"
                            min="1"
                            class="w-full bg-gray-700 border border-gray-600 rounded-md py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-pink-500"
                            placeholder="Enter Pterodactyl egg ID"
                            required
                        />

                        <p class="text-xs text-gray-400 mt-1">
                            {{
                                useDropdown
                                    ? 'Select from available Pterodactyl eggs'
                                    : 'Enter the numeric ID of the Pterodactyl egg'
                            }}
                        </p>
                    </div>

                    <div>
                        <label for="enabled" class="block text-sm font-medium text-gray-300 mb-1">Status</label>
                        <select
                            id="enabled"
                            v-model="eggForm.enabled"
                            class="w-full bg-gray-700 border border-gray-600 rounded-md py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-pink-500"
                        >
                            <option value="true">Enabled</option>
                            <option value="false">Disabled</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-300 mb-1">Description</label>
                    <textarea
                        id="description"
                        v-model="eggForm.description"
                        rows="4"
                        class="w-full bg-gray-700 border border-gray-600 rounded-md py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-pink-500"
                        placeholder="Enter egg description"
                        required
                    ></textarea>
                </div>
                <div>
                    <label for="vip_only" class="block text-sm font-medium text-gray-300 mb-1">VIP Only</label>
                    <select
                        id="vip_only"
                        v-model="eggForm.vip"
                        class="w-full bg-gray-700 border border-gray-600 rounded-md py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-pink-500"
                    >
                        <option value="true">Yes</option>
                        <option value="false">No</option>
                    </select>
                </div>
                <div>
                    <label for="image" class="block text-sm font-medium text-gray-300 mb-1">Egg Image</label>
                    <div class="flex flex-col space-y-4">
                        <select
                            id="image"
                            v-model="eggForm.image_id"
                            class="w-full bg-gray-700 border border-gray-600 rounded-md py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-pink-500"
                        >
                            <option :value="null">Select an image</option>
                            <option v-for="image in images" :key="image.id" :value="image.id">
                                {{ image.name }}
                            </option>
                        </select>
                        <div v-if="loadingImages" class="text-gray-400">Loading images...</div>
                        <div v-if="eggForm.image_id" class="mt-2">
                            <img
                                :src="images.find((img) => img.id === eggForm.image_id)?.image"
                                alt="Egg preview"
                                class="w-32 h-32 object-cover rounded-lg"
                            />
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button
                        type="submit"
                        class="bg-gradient-to-r from-pink-500 to-violet-500 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:opacity-80 flex items-center"
                        :disabled="loading"
                    >
                        <LoaderIcon v-if="loading" class="h-4 w-4 mr-2 animate-spin" />
                        <SaveIcon v-else class="h-4 w-4 mr-2" />
                        Save Egg
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
import Eggs from '@/mythicaldash/admin/Eggs';
import EggCategories from '@/mythicaldash/admin/EggCategories';
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
const manualEggId = ref<number | null>(null);

// Form state
const eggForm = ref({
    name: '',
    description: '',
    category: 0,
    pterodactyl_egg_id: 0,
    enabled: 'false',
    image_id: null as number | null,
    vip: 'false',
});

interface Category {
    id: number;
    name: string;
    description: string;
    pterodactyl_nest_id: string;
    enabled: string;
    created_at: string;
    updated_at: string;
}

interface PterodactylEgg {
    id: number;
    name: string;
    nest: number;
    description: string;
}

interface Image {
    id: number;
    name: string;
    image: string;
    created_at: string;
    updated_at: string;
}

const categories = ref<Category[]>([]);
const pterodactylEggs = ref<PterodactylEgg[]>([]);
const images = ref<Image[]>([]);
const loadingImages = ref(true);
const selectedNestId = ref<number | null>(null);

// Fetch categories
const fetchCategories = async () => {
    try {
        const response = await EggCategories.getCategories();
        if (response.success) {
            categories.value = response.categories;
        }
    } catch (error) {
        console.error('Error fetching categories:', error);
    }
};

// Fetch Pterodactyl eggs when a category is selected
const fetchPterodactylEggs = async (nestId: number) => {
    try {
        const response = await EggCategories.getPterodactylEggs(nestId);
        if (response.success) {
            pterodactylEggs.value = response.eggs;
        }
    } catch (error) {
        console.error('Error fetching Pterodactyl eggs:', error);
    }
};

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
        if (manualEggId.value) {
            eggForm.value.pterodactyl_egg_id = manualEggId.value;
        }
    } else {
        // Switching to manual input - sync dropdown to manual input
        if (eggForm.value.pterodactyl_egg_id) {
            manualEggId.value = eggForm.value.pterodactyl_egg_id;
        }
    }
});

// Watch for changes in manual input to sync to dropdown
watch(manualEggId, (newValue) => {
    if (!useDropdown.value && newValue) {
        eggForm.value.pterodactyl_egg_id = newValue;
    }
});

// Watch for changes in dropdown to sync to manual input
watch(
    () => eggForm.value.pterodactyl_egg_id,
    (newValue) => {
        if (useDropdown.value && newValue) {
            manualEggId.value = newValue;
        }
    },
);

// Watch for category changes to load the appropriate Pterodactyl eggs
const watchCategory = () => {
    if (eggForm.value.category > 0) {
        const selectedCategory = categories.value.find((c) => c.id === eggForm.value.category);
        if (selectedCategory) {
            const nestId = parseInt(selectedCategory.pterodactyl_nest_id);
            if (nestId !== selectedNestId.value) {
                selectedNestId.value = nestId;
                fetchPterodactylEggs(nestId);
            }
        }
    }
};

const saveEgg = async () => {
    loading.value = true;

    try {
        // Determine which egg ID to use
        const pterodactylEggId = useDropdown.value ? eggForm.value.pterodactyl_egg_id : manualEggId.value;

        // Validate egg ID
        if (!pterodactylEggId || pterodactylEggId <= 0) {
            playError();
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please select a Pterodactyl egg or enter a valid egg ID',
                showConfirmButton: true,
            });
            loading.value = false;
            return;
        }

        const response = await Eggs.createEgg(
            eggForm.value.name,
            eggForm.value.description,
            eggForm.value.category,
            pterodactylEggId,
            eggForm.value.enabled,
            eggForm.value.image_id,
            eggForm.value.vip,
        );

        if (response.success) {
            playSuccess();
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Egg created successfully',
                showConfirmButton: true,
            }).then(() => {
                router.push('/mc-admin/eggs');
            });
        } else {
            playError();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: response.message || 'Failed to create egg',
                showConfirmButton: true,
            });
        }
    } catch (error) {
        console.error('Error creating egg:', error);
        playError();
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An unexpected error occurred',
            showConfirmButton: true,
        });
    } finally {
        loading.value = false;
    }
};

onMounted(async () => {
    await fetchCategories();
    await fetchImages();
});

// Watch for category changes
watch(() => eggForm.value.category, watchCategory);
</script>
