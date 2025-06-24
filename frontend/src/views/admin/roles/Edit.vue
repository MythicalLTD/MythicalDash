<template>
    <LayoutDashboard>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-400">Edit Role</h1>
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
            <form @submit.prevent="updateRole" class="space-y-6">
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
                    <label for="real_name" class="block text-sm font-medium text-gray-300 mb-2">Display Name</label>
                    <input
                        type="text"
                        id="real_name"
                        v-model="form.real_name"
                        class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-pink-500"
                        required
                    />
                </div>

                <div>
                    <label for="color" class="block text-sm font-medium text-gray-300 mb-2">Color</label>
                    <div class="flex items-center gap-4">
                        <input
                            type="color"
                            id="color"
                            v-model="form.color"
                            class="w-16 h-10 bg-gray-700 border border-gray-600 rounded-lg cursor-pointer"
                            required
                        />
                        <input
                            type="text"
                            v-model="form.color"
                            class="flex-1 px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-pink-500"
                            placeholder="#000000"
                            required
                        />
                    </div>
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
    real_name: '',
    color: '#000000',
});

const fetchRole = async () => {
    loading.value = true;
    error.value = '';
    try {
        const response = await fetch(`/api/admin/roles/${route.params.id}`, {
            method: 'GET',
            headers: {
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error('Failed to fetch role');
        }

        const data = await response.json();

        if (data.success) {
            form.value = {
                name: data.role.name,
                real_name: data.role.real_name,
                color: data.role.color,
            };
        } else {
            error.value = data.message || 'Failed to load role';
        }
    } catch (error) {
        console.error('Error fetching role:', error);
    } finally {
        loading.value = false;
    }
};

const updateRole = async () => {
    saving.value = true;
    error.value = '';
    try {
        const formData = new FormData();
        formData.append('id', route.params.id as string);
        formData.append('name', form.value.name);
        formData.append('real_name', form.value.real_name);
        formData.append('color', form.value.color);

        const response = await fetch(`/api/admin/roles/update`, {
            method: 'POST',
            body: formData,
        });

        if (!response.ok) {
            const data = await response.json();
            throw new Error(data.message || 'Failed to update role');
        }

        const data = await response.json();

        if (data.success) {
            router.push('/mc-admin/roles');
        } else {
            error.value = data.message || 'Failed to update role';
        }
    } catch (error) {
        console.error('Error updating role:', error);
    } finally {
        saving.value = false;
    }
};

const goBack = () => {
    router.push('/mc-admin/roles');
};

onMounted(() => {
    fetchRole();
});
</script>
