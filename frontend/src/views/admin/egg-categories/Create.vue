<template>
    <LayoutDashboard>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-400">Create Egg Category</h1>
            <button
                @click="router.push('/mc-admin/egg-categories')"
                class="bg-gray-700 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:bg-gray-600 flex items-center"
            >
                <ArrowLeftIcon class="w-4 h-4 mr-2" />
                Back to Egg Categories
            </button>
        </div>

        <div class="bg-gray-800/50 backdrop-blur-md rounded-lg p-6">
            <form @submit.prevent="saveCategory" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-400 mb-1">Name</label>
                        <input
                            id="name"
                            v-model="categoryForm.name"
                            type="text"
                            required
                            class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                            placeholder="e.g. Minecraft Servers"
                        />
                        <p class="text-xs text-gray-400 mt-1">This is used to identify the category in the UI</p>
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-400 mb-1"
                            >Description</label
                        >
                        <input
                            id="description"
                            v-model="categoryForm.description"
                            type="text"
                            required
                            class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                            placeholder="e.g. All Minecraft server types"
                        />
                        <p class="text-xs text-gray-400 mt-1">This is used to describe the purpose of the category</p>
                    </div>

                    <div>
                        <label for="pterodactyl_nest_id" class="block text-sm font-medium text-gray-400 mb-1">
                            Pterodactyl Nest
                        </label>
                        <select
                            id="pterodactyl_nest_id"
                            v-model="categoryForm.pterodactyl_nest_id"
                            class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                        >
                            <option v-for="nest in pterodactylNests" :key="nest.id" :value="nest.id">
                                {{ nest.name }}
                            </option>
                        </select>
                        <p class="text-xs text-gray-400 mt-1">
                            This is used to link the category to a Pterodactyl nest
                        </p>
                    </div>

                    <div class="flex flex-col space-y-4">
                        <div class="flex items-center">
                            <input
                                id="enabled"
                                v-model="categoryForm.enabled"
                                type="checkbox"
                                class="w-4 h-4 text-pink-500 bg-gray-800 border-gray-700 rounded focus:ring-pink-500"
                            />
                            <label for="enabled" class="ml-2 text-sm font-medium text-gray-400">Enabled</label>
                            <p class="text-xs text-gray-400 ml-6">If enabled, this category will be visible to users</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-700">
                    <button
                        type="button"
                        @click="router.push('/mc-admin/egg-categories')"
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
                        Create Category
                    </button>
                </div>
            </form>
        </div>
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import LayoutDashboard from '@/components/admin/LayoutDashboard.vue';
import { ArrowLeftIcon, SaveIcon, LoaderIcon } from 'lucide-vue-next';
import EggCategories from '@/mythicaldash/admin/EggCategories';
import Swal from 'sweetalert2';
import { useSound } from '@vueuse/sound';
import failedAlertSfx from '@/assets/sounds/error.mp3';
import successAlertSfx from '@/assets/sounds/success.mp3';

const router = useRouter();
const loading = ref(false);
const { play: playError } = useSound(failedAlertSfx);
const { play: playSuccess } = useSound(successAlertSfx);

// Form state
const categoryForm = ref({
    name: '',
    description: '',
    pterodactyl_nest_id: 0,
    enabled: 'true',
});

interface PterodactylNest {
    id: number;
    name: string;
    description: string;
    created_at: string;
    updated_at: string;
}

const pterodactylNests = ref<PterodactylNest[]>([]);

EggCategories.getPterodactylNests().then((response) => {
    if (response.success) {
        pterodactylNests.value = response.nests;
    }
});

const saveCategory = async () => {
    loading.value = true;

    try {
        const response = await EggCategories.createCategory(
            categoryForm.value.name,
            categoryForm.value.description,
            categoryForm.value.pterodactyl_nest_id,
            categoryForm.value.enabled,
        );

        if (response.success) {
            playSuccess();
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Egg category created successfully',
                showConfirmButton: true,
            });

            // Navigate back to egg categories list after a short delay
            setTimeout(() => {
                router.push('/mc-admin/egg-categories');
            }, 1500);
        } else {
            const errorMessages = {
                ERROR_CATEGORY_ALREADY_EXISTS: 'A category with this Pterodactyl nest ID already exists',
                MISSING_REQUIRED_FIELDS: 'Please fill in all required fields',
                ERROR_INVALID_PTERODACTYL_NEST_ID: 'Invalid Pterodactyl nest ID',
                ERROR_FAILED_TO_CREATE_CATEGORY: 'Failed to create the category',
                INVALID_SESSION: 'Your session has expired. Please log in again.',
            };

            const error_code = response.error_code as keyof typeof errorMessages;
            const errorMessage = errorMessages[error_code] || 'Failed to create category';

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
        console.error('Error creating category:', error);
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
