<template>
    <LayoutDashboard>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-400">Edit Redirect Link</h1>
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
            <form @submit.prevent="updateRedirectLink" class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-300 mb-2">Name</label>
                    <input
                        type="text"
                        id="name"
                        v-model="form.name"
                        class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-pink-500"
                        required
                    />
                </div>

                <div>
                    <label for="link" class="block text-sm font-medium text-gray-300 mb-2">Link</label>
                    <input
                        type="url"
                        id="link"
                        v-model="form.link"
                        class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-pink-500"
                        required
                    />
                </div>

                <div class="flex justify-end">
                    <button
                        type="submit"
                        class="bg-gradient-to-r from-pink-500 to-violet-500 text-white px-6 py-2 rounded-lg transition-all duration-200 hover:opacity-80 flex items-center"
                        :disabled="saving"
                    >
                        <LoaderCircle v-if="saving" class="w-4 h-4 mr-2 animate-spin" />
                        <SaveIcon v-else class="w-4 h-4 mr-2" />
                        {{ saving ? 'Saving...' : 'Save Changes' }}
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
import { SaveIcon, ArrowLeftIcon, LoaderCircle } from 'lucide-vue-next';

const router = useRouter();
const route = useRoute();
const loading = ref(true);
const saving = ref(false);
const error = ref('');

const form = ref({
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
            form.value = {
                name: data.redirect_link.name,
                link: data.redirect_link.link,
            };
        } else {
            error.value = data.message || 'Failed to load redirect link';
        }
    } catch (error) {
        console.error('Error fetching redirect link:', error);
    } finally {
        loading.value = false;
    }
};

const updateRedirectLink = async () => {
    saving.value = true;
    error.value = '';
    try {
        const formData = new FormData();
        formData.append('name', form.value.name);
        formData.append('link', form.value.link);

        const response = await fetch(`/api/admin/redirect-links/${route.params.id}/update`, {
            method: 'POST',
            body: formData,
        });

        if (!response.ok) {
            const data = await response.json();
            throw new Error(data.message || 'Failed to update redirect link');
        }

        const data = await response.json();

        if (data.success) {
            router.push('/mc-admin/redirect-links');
        } else {
            error.value = data.message || 'Failed to update redirect link';
        }
    } catch (error) {
        console.error('Error updating redirect link:', error);
    } finally {
        saving.value = false;
    }
};

const goBack = () => {
    router.push('/mc-admin/redirect-links');
};

onMounted(() => {
    fetchRedirectLink();
});
</script>
