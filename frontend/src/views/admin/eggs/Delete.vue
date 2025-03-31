<template>
    <LayoutDashboard>
        <div class="flex items-center mb-6">
            <button @click="router.back()" class="mr-4 text-gray-400 hover:text-white transition-colors">
                <ArrowLeftIcon class="h-5 w-5" />
            </button>
            <h1 class="text-2xl font-bold text-pink-400">Delete Egg</h1>
        </div>

        <div v-if="loading" class="flex justify-center items-center py-10">
            <LoaderIcon class="h-8 w-8 animate-spin text-pink-400" />
        </div>
        <div v-else class="bg-gray-800 rounded-lg p-6 shadow-md">
            <div class="text-center mb-6">
                <AlertTriangleIcon class="h-16 w-16 text-red-500 mx-auto mb-4" />
                <h2 class="text-xl font-semibold text-white mb-2">Are you sure you want to delete this egg?</h2>
                <p class="text-gray-400">
                    This action cannot be undone. All data associated with this egg will be permanently removed.
                </p>
            </div>

            <div class="bg-gray-700 rounded-lg p-4 mb-6">
                <h3 class="text-lg font-medium text-white mb-2">{{ egg?.name }}</h3>
                <p class="text-gray-300 mb-2">{{ egg?.description }}</p>
                <div class="grid grid-cols-2 gap-2 text-sm">
                    <div>
                        <span class="text-gray-400">Category:</span>
                        <span class="text-white ml-2">{{ categoryName }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400">Pterodactyl Egg ID:</span>
                        <span class="text-white ml-2">{{ egg?.pterodactyl_egg_id }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400">Status:</span>
                        <span :class="egg?.enabled === 'true' ? 'text-green-500' : 'text-red-500'" class="ml-2">
                            {{ egg?.enabled === 'true' ? 'Enabled' : 'Disabled' }}
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-400">Created:</span>
                        <span class="text-white ml-2">{{ formatDate(egg?.created_at) }}</span>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-4">
                <button
                    @click="router.back()"
                    class="px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-600 transition-colors"
                >
                    Cancel
                </button>
                <button
                    @click="confirmDelete"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors flex items-center"
                    :disabled="deleting"
                >
                    <LoaderIcon v-if="deleting" class="h-4 w-4 mr-2 animate-spin" />
                    <TrashIcon v-else class="h-4 w-4 mr-2" />
                    Delete Egg
                </button>
            </div>
        </div>
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import LayoutDashboard from '@/components/admin/LayoutDashboard.vue';
import { ArrowLeftIcon, TrashIcon, LoaderIcon, AlertTriangleIcon } from 'lucide-vue-next';
import Eggs from '@/mythicaldash/admin/Eggs';
import EggCategories from '@/mythicaldash/admin/EggCategories';
import Swal from 'sweetalert2';
import { useSound } from '@vueuse/sound';
import failedAlertSfx from '@/assets/sounds/error.mp3';
import successAlertSfx from '@/assets/sounds/success.mp3';

interface Egg {
    id: number;
    name: string;
    description: string;
    category: string;
    pterodactyl_egg_id: string;
    enabled: string;
    created_at: string;
    updated_at: string;
}

interface Category {
    id: number;
    name: string;
    description: string;
    pterodactyl_nest_id: string;
    enabled: string;
    created_at: string;
    updated_at: string;
}

const router = useRouter();
const route = useRoute();
const loading = ref(true);
const deleting = ref(false);
const { play: playError } = useSound(failedAlertSfx);
const { play: playSuccess } = useSound(successAlertSfx);

const eggId = parseInt(route.params.id as string);
const egg = ref<Egg | null>(null);
const categories = ref<Category[]>([]);

const categoryName = computed(() => {
    if (!egg.value || !categories.value.length) return 'Unknown';
    const category = categories.value.find((c) => c.id === parseInt(egg.value?.category ?? ''));
    return category ? category.name : 'Unknown';
});

const formatDate = (dateString?: string) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString();
};

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

// Fetch egg data
const fetchEgg = async () => {
    loading.value = true;
    try {
        const response = await Eggs.getEgg(eggId);
        if (response.success) {
            egg.value = response.egg;
        } else {
            playError();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Egg not found',
                showConfirmButton: true,
            }).then(() => {
                router.push('/mc-admin/eggs');
            });
        }
    } catch (error) {
        console.error('Error fetching egg:', error);
        playError();
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to load egg details',
            showConfirmButton: true,
        }).then(() => {
            router.push('/mc-admin/eggs');
        });
    } finally {
        loading.value = false;
    }
};

const confirmDelete = async () => {
    deleting.value = true;
    try {
        const response = await Eggs.deleteEgg(eggId);
        if (response.success) {
            playSuccess();
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Egg deleted successfully',
                showConfirmButton: true,
            }).then(() => {
                router.push('/mc-admin/eggs');
            });
        } else {
            playError();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: response.message || 'Failed to delete egg',
                showConfirmButton: true,
            });
        }
    } catch (error) {
        console.error('Error deleting egg:', error);
        playError();
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An unexpected error occurred',
            showConfirmButton: true,
        });
    } finally {
        deleting.value = false;
    }
};

onMounted(async () => {
    await fetchCategories();
    await fetchEgg();
});
</script>
