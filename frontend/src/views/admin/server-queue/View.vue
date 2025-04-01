<template>
    <LayoutDashboard>
        <div class="flex items-center mb-6">
            <button @click="router.back()" class="mr-4 text-gray-400 hover:text-white transition-colors">
                <ArrowLeftIcon class="h-5 w-5" />
            </button>
            <h1 class="text-2xl font-bold text-pink-400">Server Queue Details</h1>
        </div>

        <div v-if="loading" class="flex justify-center items-center py-10">
            <LoaderIcon class="h-8 w-8 animate-spin text-pink-400" />
        </div>
        <div v-else-if="!queueItem" class="bg-red-500/20 text-red-400 p-4 rounded-lg mb-6">
            Server not found in queue
        </div>
        <div v-else class="space-y-6">
            <!-- Server Status Card -->
            <div class="bg-gray-800/50 backdrop-blur-md rounded-lg p-6 shadow-md">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-xl font-bold text-white">{{ queueItem.name }}</h2>
                        <p class="text-gray-400">{{ queueItem.description }}</p>
                    </div>
                    <div>
                        <span
                            :class="{
                                'bg-yellow-500/20 text-yellow-400': queueItem.status === 'pending',
                                'bg-blue-500/20 text-blue-400': queueItem.status === 'building',
                                'bg-red-500/20 text-red-400': queueItem.status === 'failed',
                            }"
                            class="px-3 py-1 rounded-full text-sm font-medium"
                        >
                            {{ queueItem.status.charAt(0).toUpperCase() + queueItem.status.slice(1) }}
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <span class="text-gray-400">Created:</span>
                        <span class="text-white ml-2">{{ formatDate(queueItem.created_at) }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400">Last Updated:</span>
                        <span class="text-white ml-2">{{ formatDate(queueItem.updated_at) }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400">User:</span>
                        <span class="text-white ml-2">{{ queueItem.user ? queueItem.user.username : 'Unknown' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400">Location:</span>
                        <span class="text-white ml-2">{{
                            queueItem.location ? queueItem.location.name : 'Unknown'
                        }}</span>
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <button
                        @click="confirmDelete"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors flex items-center"
                    >
                        <TrashIcon class="h-4 w-4 mr-2" />
                        Delete
                    </button>
                </div>
            </div>

            <!-- Server Resources -->
            <div class="bg-gray-800/50 backdrop-blur-md rounded-lg p-6 shadow-md">
                <h3 class="text-lg font-medium text-white mb-4">Server Resources</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <div class="bg-gray-700/50 rounded-lg p-3">
                        <div class="text-sm text-gray-400 mb-1">RAM</div>
                        <div class="text-lg font-semibold text-white">{{ queueItem.ram }} MB</div>
                    </div>
                    <div class="bg-gray-700/50 rounded-lg p-3">
                        <div class="text-sm text-gray-400 mb-1">CPU</div>
                        <div class="text-lg font-semibold text-white">{{ queueItem.cpu }}%</div>
                    </div>
                    <div class="bg-gray-700/50 rounded-lg p-3">
                        <div class="text-sm text-gray-400 mb-1">Disk</div>
                        <div class="text-lg font-semibold text-white">{{ queueItem.disk }} MB</div>
                    </div>
                    <div class="bg-gray-700/50 rounded-lg p-3">
                        <div class="text-sm text-gray-400 mb-1">Ports</div>
                        <div class="text-lg font-semibold text-white">{{ queueItem.ports }}</div>
                    </div>
                    <div class="bg-gray-700/50 rounded-lg p-3">
                        <div class="text-sm text-gray-400 mb-1">Databases</div>
                        <div class="text-lg font-semibold text-white">{{ queueItem.databases }}</div>
                    </div>
                    <div class="bg-gray-700/50 rounded-lg p-3">
                        <div class="text-sm text-gray-400 mb-1">Backups</div>
                        <div class="text-lg font-semibold text-white">{{ queueItem.backups }}</div>
                    </div>
                </div>
            </div>

            <!-- Server Type -->
            <div class="bg-gray-800/50 backdrop-blur-md rounded-lg p-6 shadow-md">
                <h3 class="text-lg font-medium text-white mb-4">Server Type</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-gray-700/50 rounded-lg p-3">
                        <div class="text-sm text-gray-400 mb-1">Nest</div>
                        <div class="text-lg font-semibold text-white">
                            {{ queueItem.nest ? queueItem.nest.name : 'Unknown' }}
                        </div>
                    </div>
                    <div class="bg-gray-700/50 rounded-lg p-3">
                        <div class="text-sm text-gray-400 mb-1">Egg</div>
                        <div class="text-lg font-semibold text-white">
                            {{ queueItem.egg ? queueItem.egg.name : 'Unknown' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import LayoutDashboard from '@/components/admin/LayoutDashboard.vue';
import { ArrowLeftIcon, TrashIcon, LoaderIcon } from 'lucide-vue-next';
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
    location: {
        id: number;
        name: string;
        description: string;
        pterodactyl_location_id: number;
        node_ip: string;
        status: string;
        deleted: string;
        locked: string;
        updated_at: string;
        created_at: string;
    };
    user: {
        uuid: string;
        username: string;
        email: string;
        role: number;
        first_name: string;
        last_name: string;
        avatar: string;
    };
    nest: {
        id: number;
        name: string;
        description: string;
        pterodactyl_nest_id: number;
        enabled: string;
        deleted: string;
        locked: string;
        updated_at: string;
        created_at: string;
    };
    egg: {
        id: number;
        name: string;
        description: string;
        category: number;
        pterodactyl_egg_id: number;
        enabled: string;
        deleted: string;
        locked: string;
        updated_at: string;
        created_at: string;
    };
    deleted: string;
    locked: string;
    created_at: string;
    updated_at: string;
}

const router = useRouter();
const route = useRoute();
const loading = ref(true);
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
        if (response.success) {
            queueItem.value = response.server_queue;
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
const confirmDelete = () => {
    Swal.fire({
        title: 'Delete Server',
        text: `Are you sure you want to delete "${queueItem.value?.name}" from the queue?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it',
        confirmButtonColor: '#ef4444',
        cancelButtonText: 'Cancel',
    }).then(async (result) => {
        if (result.isConfirmed) {
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
                    });
                }
            } catch (error) {
                console.error('Error deleting queue item:', error);
                playError();
                Swal.fire({
                    title: 'Error',
                    text: 'An unexpected error occurred',
                    icon: 'error',
                });
            }
        }
    });
};

onMounted(() => {
    fetchQueueItem();
});
</script>
