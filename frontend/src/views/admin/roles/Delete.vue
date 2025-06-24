<template>
    <LayoutDashboard>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-400">Delete Role</h1>
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
                    <p class="mb-4">Are you sure you want to delete this role?</p>
                    <div class="bg-gray-700 p-4 rounded-lg">
                        <p><span class="font-medium">Name:</span> {{ role.name }}</p>
                        <p><span class="font-medium">Display Name:</span> {{ role.real_name }}</p>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="font-medium">Color:</span>
                            <div
                                class="w-4 h-4 rounded-full border border-gray-600"
                                :style="{ backgroundColor: role.color }"
                            ></div>
                            <span>{{ role.color }}</span>
                        </div>
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
                        @click="deleteRole"
                        class="bg-red-500 text-white px-6 py-2 rounded-lg transition-all duration-200 hover:opacity-80 flex items-center"
                        :disabled="deleting"
                    >
                        <LoaderCircle v-if="deleting" class="w-4 h-4 mr-2 animate-spin" />
                        <TrashIcon v-else class="w-4 h-4 mr-2" />
                        {{ deleting ? 'Deleting...' : 'Delete Role' }}
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

const role = ref({
    id: 0,
    name: '',
    real_name: '',
    color: '',
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
            role.value = data.role;
        } else {
            error.value = data.message || 'Failed to load role';
        }
    } catch (error) {
        console.error('Error fetching role:', error);
    } finally {
        loading.value = false;
    }
};

const deleteRole = async () => {
    deleting.value = true;
    error.value = '';
    try {
        const formData = new FormData();
        formData.append('id', route.params.id as string);

        const response = await fetch(`/api/admin/roles/delete`, {
            method: 'POST',
            body: formData,
        });

        if (!response.ok) {
            const data = await response.json();
            throw new Error(data.message || 'Failed to delete role');
        }

        const data = await response.json();

        if (data.success) {
            router.push('/mc-admin/roles');
        } else {
            error.value = data.message || 'Failed to delete role';
        }
    } catch (error) {
        console.error('Error deleting role:', error);
    } finally {
        deleting.value = false;
    }
};

const goBack = () => {
    router.push('/mc-admin/roles');
};

onMounted(() => {
    fetchRole();
});
</script>
