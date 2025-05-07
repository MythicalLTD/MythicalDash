<template>
    <LayoutDashboard>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-400">Create Image</h1>
            <button
                @click="goBack()"
                class="bg-gray-500 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:opacity-80 flex items-center"
            >
                <ArrowLeftIcon class="w-4 h-4 mr-2" />
                Back
            </button>
        </div>

        <div class="bg-gray-800 rounded-lg p-6">
            <form @submit.prevent="handleSubmit" class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-300 mb-2">Name</label>
                    <input
                        type="text"
                        id="name"
                        v-model="form.name"
                        class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-pink-500"
                        required
                    />
                </div>

                <div>
                    <label for="image" class="block text-sm font-medium text-gray-300 mb-2">Image</label>
                    <input
                        type="file"
                        id="image"
                        @change="handleFileChange"
                        accept="image/png,image/jpeg,image/gif"
                        class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-pink-500"
                        required
                    />
                    <p class="mt-1 text-sm text-gray-400">Max file size: 2MB. Supported formats: PNG, JPG, GIF</p>
                </div>

                <div v-if="preview" class="mt-4">
                    <p class="text-sm font-medium text-gray-300 mb-2">Preview:</p>
                    <img :src="preview" alt="Preview" class="max-w-xs rounded-lg" />
                </div>

                <div class="flex justify-end">
                    <button
                        type="submit"
                        :disabled="loading"
                        class="bg-gradient-to-r from-pink-500 to-violet-500 text-white px-6 py-2 rounded-lg transition-all duration-200 hover:opacity-80 flex items-center disabled:opacity-50"
                    >
                        <LoaderCircle v-if="loading" class="w-4 h-4 mr-2 animate-spin" />
                        <span>{{ loading ? 'Creating...' : 'Create Image' }}</span>
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
import { ArrowLeftIcon, LoaderCircle } from 'lucide-vue-next';

const router = useRouter();
const loading = ref(false);
const preview = ref<string | null>(null);

const form = ref({
    name: '',
    image: null as File | null,
});

const handleFileChange = (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files[0]) {
        const file = input.files[0];

        // Validate file size (2MB max)
        if (file.size > 2 * 1024 * 1024) {
            alert('File size must be less than 2MB');
            input.value = '';
            return;
        }

        // Validate file type
        const allowedTypes = ['image/png', 'image/jpeg', 'image/gif'];
        if (!allowedTypes.includes(file.type)) {
            alert('Only PNG, JPG and GIF files are allowed');
            input.value = '';
            return;
        }

        form.value.image = file;

        // Create preview
        const reader = new FileReader();
        reader.onload = (e) => {
            preview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

const handleSubmit = async () => {
    if (!form.value.image) {
        alert('Please select an image');
        return;
    }

    loading.value = true;
    try {
        const formData = new FormData();
        formData.append('name', form.value.name);
        formData.append('image', form.value.image);

        const response = await fetch('/api/admin/images/create', {
            method: 'POST',
            body: formData,
        });

        const data = await response.json();

        if (data.success) {
            router.push('/mc-admin/images');
        } else {
            alert(data.message || 'Failed to create image');
        }
    } catch (error) {
        console.error('Error creating image:', error);
        alert('Failed to create image');
    } finally {
        loading.value = false;
    }
};

const goBack = () => {
    router.push('/mc-admin/images');
};
</script>
