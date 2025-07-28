<template>
    <LayoutDashboard>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-red-400">Delete J4R Server</h1>
            <button
                @click="router.push('/mc-admin/j4r-servers')"
                class="bg-gray-700 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:bg-gray-600 flex items-center"
            >
                <ArrowLeftIcon class="w-4 h-4 mr-2" />
                Back to J4R Servers
            </button>
        </div>

        <div v-if="loading" class="flex justify-center items-center py-10">
            <LoaderCircle class="h-8 w-8 animate-spin text-red-400" />
        </div>

        <div v-else class="bg-gray-800/50 backdrop-blur-md rounded-lg p-6">
            <!-- Warning Alert -->
            <div class="bg-red-500/20 border border-red-500/30 rounded-lg p-6 mb-6">
                <div class="flex items-start">
                    <AlertTriangleIcon class="w-6 h-6 text-red-400 mt-1 mr-4 flex-shrink-0" />
                    <div>
                        <h3 class="text-lg font-medium text-red-400 mb-2">Confirm Server Deletion</h3>
                        <p class="text-red-300 text-sm mb-4">
                            You are about to permanently delete this J4R server. This action cannot be undone.
                        </p>
                        <div class="text-red-200 text-xs">
                            <p class="mb-1">⚠️ This will remove the server from all user interfaces</p>
                            <p class="mb-1">⚠️ Users will no longer be able to join this server for rewards</p>
                            <p>⚠️ Historical data will be preserved but the server will be marked as deleted</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Server Details -->
            <div v-if="server" class="bg-gray-700/30 rounded-lg p-6 mb-6">
                <h3 class="text-lg font-medium text-white mb-4">Server Details</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-1">Server Name</label>
                        <p class="text-white bg-gray-800/50 rounded px-3 py-2">{{ server.name }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-1">Server ID</label>
                        <p class="text-white bg-gray-800/50 rounded px-3 py-2">{{ server.id }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-1">Discord Invite Code</label>
                        <div class="flex items-center space-x-2">
                            <code class="text-white bg-gray-800/50 rounded px-3 py-2 font-mono flex-1">
                                {{ server.invite_code }}
                            </code>
                            <a
                                :href="`https://discord.gg/${server.invite_code}`"
                                target="_blank"
                                class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded transition-colors flex items-center"
                                title="Open Discord invite"
                            >
                                <ExternalLinkIcon class="w-4 h-4" />
                            </a>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-1">Coins Reward</label>
                        <div class="flex items-center space-x-2 bg-gray-800/50 rounded px-3 py-2">
                            <CoinsIcon class="w-4 h-4 text-yellow-400" />
                            <span class="text-white font-medium">{{ server.coins.toLocaleString() }}</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-1">Status</label>
                        <span
                            :class="[
                                'inline-flex items-center px-3 py-2 rounded text-sm font-medium',
                                server.locked === 'true'
                                    ? 'bg-red-500/20 text-red-400'
                                    : 'bg-green-500/20 text-green-400',
                            ]"
                        >
                            <component
                                :is="server.locked === 'true' ? LockIcon : CheckCircleIcon"
                                class="w-4 h-4 mr-1"
                            />
                            {{ server.locked === 'true' ? 'Locked' : 'Available' }}
                        </span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-1">Created</label>
                        <p class="text-white bg-gray-800/50 rounded px-3 py-2">
                            {{ new Date(server.created_at).toLocaleString() }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Confirmation Form -->
            <form @submit.prevent="deleteServer" class="space-y-6">
                <div>
                    <label for="confirmText" class="block text-sm font-medium text-gray-400 mb-2">
                        Type <span class="text-red-400 font-mono">"DELETE"</span> to confirm deletion:
                    </label>
                    <input
                        id="confirmText"
                        v-model="confirmText"
                        type="text"
                        required
                        class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-red-500"
                        placeholder="Type DELETE to confirm"
                    />
                </div>

                <div v-if="errorMessage" class="bg-red-500/20 text-red-400 p-4 rounded-lg">
                    <div class="flex items-start">
                        <AlertCircleIcon class="w-5 h-5 mr-2 mt-0.5 flex-shrink-0" />
                        <div>
                            <p class="font-medium">Error</p>
                            <p class="text-sm">{{ errorMessage }}</p>
                        </div>
                    </div>
                </div>

                <div v-if="successMessage" class="bg-green-500/20 text-green-400 p-4 rounded-lg">
                    <div class="flex items-start">
                        <CheckCircleIcon class="w-5 h-5 mr-2 mt-0.5 flex-shrink-0" />
                        <div>
                            <p class="font-medium">Success</p>
                            <p class="text-sm">{{ successMessage }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-700">
                    <button
                        type="button"
                        @click="router.push('/mc-admin/j4r-servers')"
                        class="px-4 py-2 border border-gray-600 rounded-lg text-gray-300 hover:bg-gray-700 transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        :disabled="deleting || confirmText !== 'DELETE'"
                        class="px-4 py-2 bg-gradient-to-r from-red-500 to-red-600 rounded-lg text-white hover:opacity-90 transition-colors flex items-center disabled:opacity-50"
                    >
                        <LoaderIcon v-if="deleting" class="animate-spin w-4 h-4 mr-2" />
                        <TrashIcon v-else class="w-4 h-4 mr-2" />
                        Delete Server
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
import {
    ArrowLeftIcon,
    TrashIcon,
    LoaderIcon,
    LoaderCircle,
    CoinsIcon,
    AlertTriangleIcon,
    AlertCircleIcon,
    CheckCircleIcon,
    LockIcon,
    ExternalLinkIcon,
} from 'lucide-vue-next';

interface J4RServer {
    id: number;
    name: string;
    invite_code: string;
    coins: number;
    created_at: string;
    updated_at: string;
    deleted: string;
    locked: string;
}

const router = useRouter();
const route = useRoute();
const serverId = parseInt(route.params.id as string);

const loading = ref<boolean>(true);
const deleting = ref<boolean>(false);
const errorMessage = ref<string>('');
const successMessage = ref<string>('');
const server = ref<J4RServer | null>(null);
const confirmText = ref<string>('');

const fetchServer = async (): Promise<void> => {
    loading.value = true;
    try {
        const response = await fetch(`/api/admin/j4r/servers/${serverId}`, {
            method: 'GET',
            headers: {
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error('Failed to fetch J4R server');
        }

        const data = await response.json();

        if (data.success) {
            server.value = data.server;
        } else {
            throw new Error(data.message || 'Failed to load J4R server');
        }
    } catch (error) {
        errorMessage.value = error instanceof Error ? error.message : 'Failed to load server';
        console.error('Error fetching J4R server:', error);
    } finally {
        loading.value = false;
    }
};

const deleteServer = async (): Promise<void> => {
    if (confirmText.value !== 'DELETE') {
        errorMessage.value = 'Please type "DELETE" to confirm deletion';
        return;
    }

    deleting.value = true;
    errorMessage.value = '';
    successMessage.value = '';

    try {
        const response = await fetch(`/api/admin/j4r/servers/${serverId}/delete`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || 'Failed to delete J4R server');
        }

        const data = await response.json();

        if (data.success) {
            successMessage.value = 'J4R server deleted successfully!';

            // Redirect after a short delay
            setTimeout(() => {
                router.push('/mc-admin/j4r-servers');
            }, 1500);
        } else {
            throw new Error(data.message || 'Failed to delete J4R server');
        }
    } catch (error) {
        errorMessage.value = error instanceof Error ? error.message : 'An unexpected error occurred';
        console.error('Error deleting J4R server:', error);
    } finally {
        deleting.value = false;
    }
};

onMounted(() => {
    if (!serverId || isNaN(serverId)) {
        errorMessage.value = 'Invalid server ID';
        return;
    }

    fetchServer();
});
</script>
