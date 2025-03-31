<template>
    <LayoutDashboard>
        <div class="flex items-center mb-6">
            <button @click="router.back()" class="mr-4 text-gray-400 hover:text-white transition-colors">
                <ArrowLeftIcon class="h-5 w-5" />
            </button>
            <h1 class="text-2xl font-bold text-pink-400">Edit Egg</h1>
        </div>

        <div v-if="loading" class="flex justify-center items-center py-10">
            <LoaderIcon class="h-8 w-8 animate-spin text-pink-400" />
        </div>
        <div v-else class="bg-gray-800 rounded-lg p-6 shadow-md">
            <form @submit.prevent="updateEgg" class="space-y-6">
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
                        <select
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

                <div class="flex justify-end">
                    <button
                        type="submit"
                        class="bg-gradient-to-r from-pink-500 to-violet-500 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:opacity-80 flex items-center"
                        :disabled="saving"
                    >
                        <LoaderIcon v-if="saving" class="h-4 w-4 mr-2 animate-spin" />
                        <SaveIcon v-else class="h-4 w-4 mr-2" />
                        Update Egg
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
import Eggs from '@/mythicaldash/admin/Eggs';
import EggCategories from '@/mythicaldash/admin/EggCategories';
import Swal from 'sweetalert2';
import { useSound } from '@vueuse/sound';
import failedAlertSfx from '@/assets/sounds/error.mp3';
import successAlertSfx from '@/assets/sounds/success.mp3';

const router = useRouter();
const route = useRoute();
const loading = ref(true);
const saving = ref(false);
const { play: playError } = useSound(failedAlertSfx);
const { play: playSuccess } = useSound(successAlertSfx);

const eggId = parseInt(route.params.id as string);

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

// Form state
const eggForm = ref({
    name: '',
    description: '',
    category: 0,
    pterodactyl_egg_id: 0,
    enabled: 'false',
});

const categories = ref<Category[]>([]);
const pterodactylEggs = ref<PterodactylEgg[]>([]);
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

// Fetch egg data
const fetchEgg = async () => {
    loading.value = true;
    try {
        const response = await Eggs.getEgg(eggId);
        if (response.success) {
            const foundEgg = response.egg;
            eggForm.value = {
                name: foundEgg.name,
                description: foundEgg.description,
                category: parseInt(foundEgg.category),
                pterodactyl_egg_id: parseInt(foundEgg.pterodactyl_egg_id),
                enabled: foundEgg.enabled,
            };

            // Load Pterodactyl eggs for the selected category
            const selectedCategory = categories.value.find((c) => c.id === parseInt(foundEgg.category));
            if (selectedCategory) {
                const nestId = parseInt(selectedCategory.pterodactyl_nest_id);
                selectedNestId.value = nestId;
                await fetchPterodactylEggs(nestId);
            }
        } else {
            playError();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Egg not found',
                showConfirmButton: true,
            });
        }
    } catch (error) {
        console.error('Error fetching egg:', error);
    } finally {
        loading.value = false;
    }
};

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

const updateEgg = async () => {
    saving.value = true;

    try {
        const response = await Eggs.updateEgg(
            eggId,
            eggForm.value.name,
            eggForm.value.description,
            eggForm.value.category,
            eggForm.value.pterodactyl_egg_id,
            eggForm.value.enabled,
        );

        if (response.success) {
            playSuccess();
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Egg updated successfully',
                showConfirmButton: true,
            }).then(() => {
                router.push('/mc-admin/eggs');
            });
        } else {
            playError();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: response.message || 'Failed to update egg',
                showConfirmButton: true,
            });
        }
    } catch (error) {
        console.error('Error updating egg:', error);
        playError();
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An unexpected error occurred',
            showConfirmButton: true,
        });
    } finally {
        saving.value = false;
    }
};

onMounted(async () => {
    await fetchCategories();
    await fetchEgg();
});

// Watch for category changes
watch(() => eggForm.value.category, watchCategory);
</script>
