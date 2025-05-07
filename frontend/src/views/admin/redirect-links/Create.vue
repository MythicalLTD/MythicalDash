<template>
    <LayoutDashboard>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-400">Create Redirect Link</h1>
            <button
                @click="goBack()"
                class="bg-gray-600 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:opacity-80 flex items-center"
            >
                <ArrowLeftIcon class="w-4 h-4 mr-2" />
                Back
            </button>
        </div>

        <div class="bg-gray-800 rounded-lg p-6">
            <form @submit.prevent="createRedirectLink" class="space-y-6">
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
                        :disabled="loading"
                    >
                        <LoaderCircle v-if="loading" class="w-4 h-4 mr-2 animate-spin" />
                        <PlusIcon v-else class="w-4 h-4 mr-2" />
                        {{ loading ? 'Creating...' : 'Create Redirect Link' }}
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
import { PlusIcon, ArrowLeftIcon, LoaderCircle } from 'lucide-vue-next';

const router = useRouter();
const loading = ref(false);

const form = ref({
    name: '',
    link: '',
});

const createRedirectLink = async () => {
    loading.value = true;
    try {
        const formData = new FormData();
        formData.append('name', form.value.name);
        formData.append('link', form.value.link);

        const response = await fetch('/api/admin/redirect-links/create', {
            method: 'POST',
            body: formData,
        });

        if (!response.ok) {
            const data = await response.json();
            throw new Error(data.message || 'Failed to create redirect link');
        }

        const data = await response.json();

        if (data.success) {
            router.push('/mc-admin/redirect-links');
        } else {
            console.error('Failed to create redirect link:', data.message);
        }
    } catch (error) {
        console.error('Error creating redirect link:', error);
    } finally {
        loading.value = false;
    }
};

const goBack = () => {
    router.push('/mc-admin/redirect-links');
};
</script>
