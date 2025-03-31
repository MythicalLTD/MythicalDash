<template>
    <LayoutDashboard>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-400">Delete Egg Category</h1>
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

        <div v-else-if="!category" class="bg-gray-800/50 backdrop-blur-md rounded-lg p-6 text-center">
            <AlertTriangleIcon class="w-16 h-16 mx-auto text-yellow-500 mb-4" />
            <h3 class="text-xl font-medium text-gray-200 mb-2">Category Not Found</h3>
            <p class="text-gray-400 mb-6">The category you're trying to delete could not be found.</p>
            <button
                @click="router.push('/mc-admin/egg-categories')"
                class="px-4 py-2 bg-gray-700 rounded-lg text-white hover:bg-gray-600 transition-colors"
            >
                Return to Categories
            </button>
        </div>

        <div v-else class="bg-gray-800/50 backdrop-blur-md rounded-lg p-6">
            <div class="text-center mb-8">
                <AlertTriangleIcon class="w-16 h-16 mx-auto text-red-500 mb-4" />
                <h3 class="text-xl font-medium text-gray-200 mb-2">Confirm Deletion</h3>
                <p class="text-gray-400">
                    Are you sure you want to delete the category
                    <span class="font-semibold text-white">{{ category.name }}</span
                    >? This action cannot be undone.
                </p>
            </div>

            <div class="bg-gray-700/50 rounded-lg p-4 mb-6">
                <h4 class="text-sm font-medium text-gray-300 mb-2">Category Details:</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-400">ID:</span>
                        <span class="text-white ml-2">{{ category.id }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400">Name:</span>
                        <span class="text-white ml-2">{{ category.name }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400">Description:</span>
                        <span class="text-white ml-2">{{ category.description }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400">Pterodactyl Nest ID:</span>
                        <span class="text-white ml-2">{{ category.pterodactyl_nest_id }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400">Status:</span>
                        <span
                            :class="{
                                'text-green-400': category.enabled === '1',
                                'text-red-400': category.enabled === '0',
                            }"
                            class="ml-2"
                        >
                            {{ category.enabled === '1' ? 'Enabled' : 'Disabled' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3">
                <button
                    type="button"
                    @click="router.push('/mc-admin/egg-categories')"
                    class="px-4 py-2 border border-gray-600 rounded-lg text-gray-300 hover:bg-gray-700 transition-colors"
                >
                    Cancel
                </button>
                <button
                    type="button"
                    @click="confirmDelete"
                    :disabled="deleting"
                    class="px-4 py-2 bg-red-600 rounded-lg text-white hover:bg-red-700 transition-colors flex items-center"
                >
                    <LoaderIcon v-if="deleting" class="animate-spin w-4 h-4 mr-2" />
                    <TrashIcon v-else class="w-4 h-4 mr-2" />
                    Delete Category
                </button>
            </div>
        </div>
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import LayoutDashboard from '@/components/admin/LayoutDashboard.vue';
import { ArrowLeftIcon, AlertTriangleIcon, TrashIcon, LoaderIcon } from 'lucide-vue-next';
import EggCategories from '@/mythicaldash/admin/EggCategories';
import Swal from 'sweetalert2';
import { useSound } from '@vueuse/sound';
import failedAlertSfx from '@/assets/sounds/error.mp3';
import successAlertSfx from '@/assets/sounds/success.mp3';

const router = useRouter();
const route = useRoute();
const loading = ref(true);
const deleting = ref(false);
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

const category = ref<Category | null>(null);

const fetchCategory = async () => {
    loading.value = true;
    try {
        const response = await EggCategories.getCategories();
        if (response.success) {
            const foundCategory = response.categories.find((c: Category) => c.id === categoryId);
            if (foundCategory) {
                category.value = foundCategory;
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

const confirmDelete = async () => {
    deleting.value = true;

    try {
        const response = await EggCategories.deleteCategory(categoryId);
        if (response.success) {
            playSuccess();
            Swal.fire({
                icon: 'success',
                title: 'Deleted!',
                text: 'The category has been deleted successfully.',
                showConfirmButton: false,
                timer: 1500,
            });

            setTimeout(() => {
                router.push('/mc-admin/egg-categories');
            }, 1500);
        } else {
            const errorMessages = {
                ERROR_CATEGORY_NOT_FOUND: 'Category not found',
                ERROR_FAILED_TO_DELETE_CATEGORY: 'Failed to delete the category',
                INVALID_SESSION: 'Your session has expired. Please log in again.',
            };

            const error_code = response.error_code as keyof typeof errorMessages;
            const errorMessage = errorMessages[error_code] || 'Failed to delete category';

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
        console.error('Error deleting category:', error);
        playError();
        Swal.fire({
            icon: 'error',
            title: 'Connection Error',
            text: 'Failed to connect to the server. Please check your internet connection and try again.',
            footer: 'If the problem persists, please contact support.',
            showConfirmButton: true,
        });
    } finally {
        deleting.value = false;
    }
};

onMounted(() => {
    fetchCategory();
});
</script>
