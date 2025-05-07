<template>
    <LayoutDashboard>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-400">Delete Image</h1>
            <button
                @click="goBack()"
                class="bg-gray-500 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:opacity-80 flex items-center"
            >
                <ArrowLeftIcon class="w-4 h-4 mr-2" />
                Back
            </button>
        </div>

        <div v-if="loading" class="flex justify-center items-center py-10">
            <LoaderCircle class="h-8 w-8 animate-spin text-pink-400" />
        </div>

        <div v-else class="bg-gray-800 rounded-lg p-6">
            <div class="text-center">
                <h2 class="text-xl font-semibold text-white mb-4">Are you sure you want to delete this image?</h2>
                <p class="text-gray-400 mb-6">This action cannot be undone.</p>

                <div class="mb-6">
                    <p class="text-gray-300 mb-2">Image Name: {{ image.name }}</p>
                    <img :src="image.image" alt="Image to delete" class="max-w-xs mx-auto rounded-lg" />
                </div>

                <div class="flex justify-center space-x-4">
                    <button
                        @click="goBack()"
                        class="bg-gray-500 text-white px-6 py-2 rounded-lg transition-all duration-200 hover:opacity-80"
                    >
                        Cancel
                    </button>
                    <button
                        @click="handleDelete"
                        :disabled="deleting"
                        class="bg-red-500 text-white px-6 py-2 rounded-lg transition-all duration-200 hover:opacity-80 flex items-center disabled:opacity-50"
                    >
                        <LoaderCircle v-if="deleting" class="w-4 h-4 mr-2 animate-spin" />
                        <span>{{ deleting ? 'Deleting...' : 'Delete Image' }}</span>
                    </button>
                </div>
            </div>
        </div>
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import LayoutDashboard from '@/components/admin/LayoutDashboard.vue';
import { ArrowLeftIcon, LoaderCircle } from 'lucide-vue-next';

const router = useRouter();
const route = useRoute();
const loading = ref(false);
const deleting = ref(false);

const image = ref({
    id: 0,
    name: '',
    image: '',
});

const fetchImage = async () => {
    loading.value = true;
    try {
        const response = await fetch(`/api/admin/images/${route.params.id}`, {
            method: 'GET',
            headers: {
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error('Failed to fetch image');
        }

        const data = await response.json();

        if (data.success) {
            image.value = data.image;
        } else {
            alert(data.message || 'Failed to fetch image');
            router.push('/mc-admin/images');
        }
    } catch (error) {
        console.error('Error fetching image:', error);
        alert('Failed to fetch image');
        router.push('/mc-admin/images');
    } finally {
        loading.value = false;
    }
};

const handleDelete = async () => {
    if (!confirm('Are you absolutely sure you want to delete this image?')) {
        return;
    }

    deleting.value = true;
    try {
        const response = await fetch(`/api/admin/images/${route.params.id}/delete`, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
            },
        });

        const data = await response.json();

        if (data.success) {
            router.push('/mc-admin/images');
        } else {
            alert(data.message || 'Failed to delete image');
        }
    } catch (error) {
        console.error('Error deleting image:', error);
        alert('Failed to delete image');
    } finally {
        deleting.value = false;
    }
};

const goBack = () => {
    router.push('/mc-admin/images');
};

onMounted(() => {
    fetchImage();
});
</script>
