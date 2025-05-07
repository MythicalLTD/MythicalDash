<template>
    <LayoutDashboard>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-400">Delete Redirect Link</h1>
            <button
                @click="goBack()"
                class="bg-gray-600 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:opacity-80 flex items-center"
            >
                <ArrowLeftIcon class="w-4 h-4 mr-2" />
                Back
            </button>
        </div>

        <div v-if="loading" class="flex justify-center items-center py-10">
            <LoaderCircle class="h-8 w-8 animate-spin text-pink-400" />
        </div>

        <div v-else-if="error" class="bg-red-500/20 text-red-400 p-4 rounded-lg">
            {{ error }}
        </div>

        <div v-else class="bg-gray-800 rounded-lg p-6">
            <div class="space-y-6">
                <div class="text-gray-300">
                    <p class="mb-4">Are you sure you want to delete this redirect link?</p>
                    <div class="bg-gray-700 p-4 rounded-lg">
                        <p><span class="font-medium">Name:</span> {{ redirectLink.name }}</p>
                        <p><span class="font-medium">Link:</span> {{ redirectLink.link }}</p>
                    </div>
                </div>

                <div class="flex justify-end space-x-4">
                    <button
                        @click="goBack"
                        class="bg-gray-600 text-white px-6 py-2 rounded-lg transition-all duration-200 hover:opacity-80"
                    >
                        Cancel
                    </button>
                    <button
                        @click="deleteRedirectLink"
                        class="bg-red-500 text-white px-6 py-2 rounded-lg transition-all duration-200 hover:opacity-80 flex items-center"
                        :disabled="deleting"
                    >
                        <LoaderCircle v-if="deleting" class="w-4 h-4 mr-2 animate-spin" />
                        <TrashIcon v-else class="w-4 h-4 mr-2" />
                        {{ deleting ? 'Deleting...' : 'Delete Redirect Link' }}
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
import { TrashIcon, ArrowLeftIcon, LoaderCircle } from 'lucide-vue-next';

const router = useRouter();
const route = useRoute();
const loading = ref(true);
const deleting = ref(false);
const error = ref('');

const redirectLink = ref({
    id: 0,
    name: '',
    link: '',
});

const fetchRedirectLink = async () => {
    loading.value = true;
    error.value = '';
    try {
        const response = await fetch(`/api/admin/redirect-links/${route.params.id}`, {
            method: 'GET',
            headers: {
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error('Failed to fetch redirect link');
        }

        const data = await response.json();

        if (data.success) {
            redirectLink.value = data.redirect_link;
        } else {
            error.value = data.message || 'Failed to load redirect link';
        }
    } catch (error) {
        console.error('Error fetching redirect link:', error);
    } finally {
        loading.value = false;
    }
};

const deleteRedirectLink = async () => {
    deleting.value = true;
    error.value = '';
    try {
        const response = await fetch(`/api/admin/redirect-links/${route.params.id}/delete`, {
            method: 'POST',
        });

        if (!response.ok) {
            const data = await response.json();
            throw new Error(data.message || 'Failed to delete redirect link');
        }

        const data = await response.json();

        if (data.success) {
            router.push('/mc-admin/redirect-links');
        } else {
            error.value = data.message || 'Failed to delete redirect link';
        }
    } catch (error) {
        console.error('Error deleting redirect link:', error);
    } finally {
        deleting.value = false;
    }
};

const goBack = () => {
    router.push('/mc-admin/redirect-links');
};

onMounted(() => {
    fetchRedirectLink();
});
</script>
