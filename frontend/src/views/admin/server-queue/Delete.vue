<template>
    <LayoutDashboard>
        <div class="flex items-center mb-6">
            <button @click="router.back()" class="mr-4 text-gray-400 hover:text-white transition-colors">
                <ArrowLeftIcon class="h-5 w-5" />
            </button>
            <h1 class="text-2xl font-bold text-pink-400">Delete Server from Queue</h1>
        </div>

        <div v-if="loading" class="flex justify-center items-center py-10">
            <LoaderIcon class="h-8 w-8 animate-spin text-pink-400" />
        </div>
        <div v-else-if="!queueItem" class="bg-red-500/20 text-red-400 p-4 rounded-lg mb-6">
            Server not found in queue
        </div>
        <div v-else class="bg-gray-800/50 backdrop-blur-md rounded-lg p-6 shadow-md">
            <div class="text-center mb-8">
                <AlertTriangleIcon class="h-16 w-16 text-red-500 mx-auto mb-4" />
                <h2 class="text-xl font-semibold text-white mb-2">Are you sure you want to delete this server?</h2>
                <p class="text-gray-400">
                    This action will remove the server from the queue. This action cannot be undone.
                </p>
            </div>

            <div class="bg-gray-700/50 rounded-lg p-4 mb-6">
                <h3 class="text-lg font-medium text-white mb-4">Server Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <span class="text-gray-400">Name:</span>
                        <span class="text-white ml-2">{{ queueItem.name }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400">Status:</span>
                        <span
                            :class="{
                                'text-yellow-400': queueItem.status === 'pending',
                                'text-blue-400': queueItem.status === 'building',
                                'text-red-400': queueItem.status === 'failed',
                            }"
                            class="ml-2"
                        >
                            {{ queueItem.status.charAt(0).toUpperCase() + queueItem.status.slice(1) }}
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-400">User:</span>
                        <span class="text-white ml-2">{{ queueItem.user_name || `User #${queueItem.user}` }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400">Location:</span>
                        <span class="text-white ml-2">{{
                            queueItem.location_name || `Location #${queueItem.location}`
                        }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400">Resources:</span>
                        <span class="text-white ml-2">
                            {{ queueItem.ram }}MB RAM, {{ queueItem.cpu }}% CPU, {{ queueItem.disk }}MB Disk
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-400">Created:</span>
                        <span class="text-white ml-2">{{ formatDate(queueItem.created_at) }}</span>
                    </div>
                </div>
                <div class="mt-4">
                    <span class="text-gray-400">Description:</span>
                    <p class="text-white mt-1">{{ queueItem.description }}</p>
                </div>
            </div>

            <div class="flex justify-end space-x-4">
                <button
                    @click="router.push('/mc-admin/server-queue')"
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
                    Delete Server
                </button>
            </div>
        </div>
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import LayoutDashboard from '@/components/admin/LayoutDashboard.vue';
import { ArrowLeftIcon, TrashIcon, LoaderIcon, AlertTriangleIcon } from 'lucide-vue-next';
import ServerQueue from '@/mythicaldash/admin/ServerQueue';
import Swal from 'sweetalert2';
import { useSound } from '@vueuse/sound';
import failedAlertSfx from '@/assets/sounds/error.mp3';
import successAlertSfx from '@/assets/sounds/success.mp3';

interface QueueItem {
    id: number;
    name: string;
    description: string;
    status: 'pending' | 'building' | 'failed';
    ram: number;
    disk: number;
    cpu: number;
    ports: number;
    databases: number;
    backups: number;
    location: number;
    user: number;
    nest: number;
    egg: number;
    deleted: string;
    locked: string;
    created_at: string;
    updated_at: string;
    location_name?: string;
    user_name?: string;
    nest_name?: string;
    egg_name?: string;
}

const router = useRouter();
const route = useRoute();
const loading = ref(true);
const deleting = ref(false);
const queueItem = ref<QueueItem | null>(null);
const { play: playError } = useSound(failedAlertSfx);
const { play: playSuccess } = useSound(successAlertSfx);

const queueItemId = parseInt(route.params.id as string);

const formatDate = (dateString: string): string => {
    const date = new Date(dateString);
    return date.toLocaleString();
};

// Fetch queue item
const fetchQueueItem = async () => {
    loading.value = true;
    try {
        const response = await ServerQueue.getServerQueueItem(queueItemId);

        if (response.success && response.item) {
            queueItem.value = response.item;
        } else {
            playError();
            Swal.fire({
                title: 'Error',
                text: 'Server not found in queue',
                icon: 'error',
                showConfirmButton: true,
            }).then(() => {
                router.push('/mc-admin/server-queue');
            });
        }
    } catch (error) {
        console.error('Error fetching queue item:', error);
        playError();
        Swal.fire({
            title: 'Error',
            text: 'An unexpected error occurred',
            icon: 'error',
            showConfirmButton: true,
        });
    } finally {
        loading.value = false;
    }
};

// Delete server from queue
const confirmDelete = async () => {
    deleting.value = true;
    try {
        const response = await ServerQueue.deleteServerQueueItem(queueItemId);

        if (response.success) {
            playSuccess();
            Swal.fire({
                title: 'Deleted',
                text: 'Server has been removed from the queue',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false,
            }).then(() => {
                router.push('/mc-admin/server-queue');
            });
        } else {
            playError();
            Swal.fire({
                title: 'Error',
                text: response.message || 'Failed to delete server from queue',
                icon: 'error',
                showConfirmButton: true,
            });
        }
    } catch (error) {
        console.error('Error deleting queue item:', error);
        playError();
        Swal.fire({
            title: 'Error',
            text: 'An unexpected error occurred',
            icon: 'error',
            showConfirmButton: true,
        });
    } finally {
        deleting.value = false;
    }
};

onMounted(() => {
    fetchQueueItem();
});
</script>
