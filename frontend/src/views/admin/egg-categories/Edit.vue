<template>
    <LayoutDashboard>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-400">Edit Egg Category</h1>
            <button
                @click="router.push('/mc-admin/egg-categories')"
                class="bg-gray-700 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:bg-gray-600 flex items-center"
            >
                <ArrowLeftIcon class="w-4 h-4 mr-2" />
                Back to Egg Categories
            </button>
        </div>

        <div v-if="loading" class="flex justify-center items-center py-12">
            <LoaderIcon class="w-8 h-8 animate-spin text-pink-400" />
        </div>

        <div v-else class="bg-gray-800/50 backdrop-blur-md rounded-lg p-6">
            <form @submit.prevent="updateCategory" class="space-y-6">
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
                            Pterodactyl Nest ID
                        </label>
                        <input
                            id="pterodactyl_nest_id"
                            v-model="categoryForm.pterodactyl_nest_id"
                            type="text"
                            disabled
                            class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500 opacity-60"
                        />
                        <p class="text-xs text-gray-400 mt-1">Nest ID cannot be changed after creation</p>
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
                        :disabled="saving"
                        class="px-4 py-2 bg-gradient-to-r from-pink-500 to-violet-500 rounded-lg text-white hover:opacity-90 transition-colors flex items-center"
                    >
                        <LoaderIcon v-if="saving" class="animate-spin w-4 h-4 mr-2" />
                        <SaveIcon v-else class="w-4 h-4 mr-2" />
                        Save Changes
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

const categoryId = parseInt(route.params.id as string);

interface Category {
    id: number;
    name: string;
    description: string;
    pterodactyl_nest_id: string;
    enabled: string;
    created_at: string;
    updated_at: string;
}

// Form state
const categoryForm = ref({
    name: '',
    description: '',
    pterodactyl_nest_id: '',
    enabled: false,
});

const fetchCategory = async () => {
    loading.value = true;
    try {
        const response = await EggCategories.getCategories();
        if (response.success) {
            const foundCategory = response.categories.find((c: Category) => c.id === categoryId);
            if (foundCategory) {
                categoryForm.value = {
                    name: foundCategory.name,
                    description: foundCategory.description,
                    pterodactyl_nest_id: foundCategory.pterodactyl_nest_id,
                    enabled: foundCategory.enabled === 'true',
                };
            } else {
                router.push('/mc-admin/egg-categories');
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Category not found',
                    showConfirmButton: true,
                });
            }
        } else {
            console.error('Failed to fetch category:', response);
        }
    } catch (error) {
        console.error('Error fetching category:', error);
    } finally {
        loading.value = false;
    }
};

const updateCategory = async () => {
    saving.value = true;

    try {
        const response = await EggCategories.updateCategory(
            categoryId,
            categoryForm.value.name,
            categoryForm.value.description,
            parseInt(categoryForm.value.pterodactyl_nest_id),
            categoryForm.value.enabled,
        );

        if (response.success) {
            playSuccess();
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Egg category updated successfully',
                showConfirmButton: true,
            });

            // Navigate back to egg categories list after a short delay
            setTimeout(() => {
                router.push('/mc-admin/egg-categories');
            }, 1500);
        } else {
            const errorMessages = {
                MISSING_REQUIRED_FIELDS: 'Please fill in all required fields',
                ERROR_CATEGORY_NOT_FOUND: 'Category not found',
                ERROR_FAILED_TO_UPDATE_CATEGORY: 'Failed to update the category',
                INVALID_SESSION: 'Your session has expired. Please log in again.',
            };

            const error_code = response.error_code as keyof typeof errorMessages;
            const errorMessage = errorMessages[error_code] || 'Failed to update category';

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
        console.error('Error updating category:', error);
        playError();
        Swal.fire({
            icon: 'error',
            title: 'Connection Error',
            text: 'Failed to connect to the server. Please check your internet connection and try again.',
            footer: 'If the problem persists, please contact support.',
            showConfirmButton: true,
        });
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    fetchCategory();
});
</script>
