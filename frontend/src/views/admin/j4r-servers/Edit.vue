<template>
    <LayoutDashboard>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-400">Edit J4R Server</h1>
            <button
                @click="router.push('/mc-admin/j4r-servers')"
                class="bg-gray-700 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:bg-gray-600 flex items-center"
            >
                <ArrowLeftIcon class="w-4 h-4 mr-2" />
                Back to J4R Servers
            </button>
        </div>

        <div v-if="loading" class="flex justify-center items-center py-10">
            <LoaderCircle class="h-8 w-8 animate-spin text-pink-400" />
        </div>

        <div v-else class="bg-gray-800/50 backdrop-blur-md rounded-lg p-6">
            <!-- Server Info Header -->
            <div class="mb-6 pb-4 border-b border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-medium text-white">{{ originalServer?.name || 'Loading...' }}</h2>
                        <p class="text-sm text-gray-400">ID: {{ serverId }}</p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span
                            :class="[
                                'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium',
                                originalServer?.locked === 'true'
                                    ? 'bg-red-500/20 text-red-400'
                                    : 'bg-green-500/20 text-green-400',
                            ]"
                        >
                            <component
                                :is="originalServer?.locked === 'true' ? LockIcon : CheckCircleIcon"
                                class="w-4 h-4 mr-1"
                            />
                            {{ originalServer?.locked === 'true' ? 'Locked' : 'Available' }}
                        </span>
                    </div>
                </div>
            </div>

            <form @submit.prevent="updateServer" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label for="name" class="block text-sm font-medium text-gray-400 mb-1">Server Name</label>
                        <input
                            id="name"
                            v-model="serverForm.name"
                            type="text"
                            required
                            class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                            placeholder="e.g., MythicalSystems Community"
                        />
                        <p class="mt-1 text-xs text-gray-500">
                            A friendly name for the Discord server users will join.
                        </p>
                    </div>

                    <div>
                        <label for="invite_code" class="block text-sm font-medium text-gray-400 mb-1"
                            >Discord Invite Code</label
                        >
                        <div class="flex space-x-2">
                            <input
                                id="invite_code"
                                v-model="serverForm.invite_code"
                                type="text"
                                required
                                class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 flex-1 focus:outline-none focus:ring-2 focus:ring-pink-500"
                                placeholder="e.g., abc123xyz"
                            />
                            <a
                                :href="`https://discord.gg/${serverForm.invite_code}`"
                                target="_blank"
                                class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors flex items-center"
                                title="Test invite link"
                            >
                                <ExternalLinkIcon class="w-4 h-4" />
                            </a>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">
                            Just the invite code, not the full URL. Example: If the invite is discord.gg/abc123, enter
                            "abc123".
                        </p>
                    </div>

                    <div>
                        <label for="server_id" class="block text-sm font-medium text-gray-400 mb-1"
                            >Discord Server ID</label
                        >
                        <input
                            id="server_id"
                            v-model="serverForm.server_id"
                            type="text"
                            required
                            class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                            placeholder="e.g., 123456789012345678"
                        />
                        <p class="mt-1 text-xs text-gray-500">
                            Discord server ID for verification. This is required to check if users actually joined the
                            server.
                        </p>
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-400 mb-1"
                            >Description (Optional)</label
                        >
                        <textarea
                            id="description"
                            v-model="serverForm.description"
                            rows="3"
                            class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                            placeholder="Brief description of the server..."
                        ></textarea>
                        <p class="mt-1 text-xs text-gray-500">
                            A short description to help users understand what this server is about.
                        </p>
                    </div>

                    <div>
                        <label for="icon_url" class="block text-sm font-medium text-gray-400 mb-1"
                            >Icon URL (Optional)</label
                        >
                        <input
                            id="icon_url"
                            v-model="serverForm.icon_url"
                            type="url"
                            class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                            placeholder="https://example.com/icon.png"
                        />
                        <p class="mt-1 text-xs text-gray-500">
                            URL to the server icon image. Should be a direct link to an image file.
                        </p>
                    </div>

                    <div>
                        <label for="coins" class="block text-sm font-medium text-gray-400 mb-1">Coins Reward</label>
                        <div class="relative">
                            <input
                                id="coins"
                                v-model.number="serverForm.coins"
                                type="number"
                                min="0"
                                required
                                class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 pl-10 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                                placeholder="100"
                            />
                            <CoinsIcon class="w-5 h-5 text-yellow-400 absolute left-3 top-2.5" />
                        </div>
                        <p class="mt-1 text-xs text-gray-500">
                            Amount of coins users will receive for joining this server.
                        </p>
                    </div>

                    <div class="md:col-span-2">
                        <div class="bg-blue-500/10 border border-blue-500/30 rounded-lg p-4">
                            <div class="flex items-start">
                                <InfoIcon class="w-5 h-5 text-blue-400 mt-0.5 mr-3 flex-shrink-0" />
                                <div class="text-sm">
                                    <p class="text-blue-400 font-medium mb-1">Server Management:</p>
                                    <ul class="text-blue-300 space-y-1 text-xs">
                                        <li>• Changes will affect how users interact with this server</li>
                                        <li>• Existing users who already joined won't be affected by coin changes</li>
                                        <li>• Use the lock/unlock toggle in the actions to control availability</li>
                                        <li>• Test the Discord invite link before saving changes</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="errorMessage" class="bg-red-500/20 text-red-400 p-4 rounded-lg mb-6">
                    <div class="flex items-start">
                        <AlertCircleIcon class="w-5 h-5 mr-2 mt-0.5 flex-shrink-0" />
                        <div>
                            <p class="font-medium">Error</p>
                            <p class="text-sm">{{ errorMessage }}</p>
                        </div>
                    </div>
                </div>

                <div v-if="successMessage" class="bg-green-500/20 text-green-400 p-4 rounded-lg mb-6">
                    <div class="flex items-start">
                        <CheckCircleIcon class="w-5 h-5 mr-2 mt-0.5 flex-shrink-0" />
                        <div>
                            <p class="font-medium">Success</p>
                            <p class="text-sm">{{ successMessage }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between pt-4 border-t border-gray-700">
                    <div class="flex space-x-3">
                        <button
                            type="button"
                            @click="toggleLock"
                            :disabled="saving"
                            :class="[
                                'px-4 py-2 rounded-lg transition-colors flex items-center',
                                originalServer?.locked === 'true'
                                    ? 'bg-green-600 hover:bg-green-700 text-white'
                                    : 'bg-yellow-600 hover:bg-yellow-700 text-white',
                            ]"
                        >
                            <component
                                :is="originalServer?.locked === 'true' ? UnlockIcon : LockIcon"
                                class="w-4 h-4 mr-2"
                            />
                            {{ originalServer?.locked === 'true' ? 'Unlock Server' : 'Lock Server' }}
                        </button>
                    </div>

                    <div class="flex space-x-3">
                        <button
                            type="button"
                            @click="router.push('/mc-admin/j4r-servers')"
                            class="px-4 py-2 border border-gray-600 rounded-lg text-gray-300 hover:bg-gray-700 transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="saving || !hasChanges"
                            class="px-4 py-2 bg-gradient-to-r from-pink-500 to-violet-500 rounded-lg text-white hover:opacity-90 transition-colors flex items-center disabled:opacity-50"
                        >
                            <LoaderIcon v-if="saving" class="animate-spin w-4 h-4 mr-2" />
                            <SaveIcon v-else class="w-4 h-4 mr-2" />
                            Update Server
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import LayoutDashboard from '@/components/admin/LayoutDashboard.vue';
import {
    ArrowLeftIcon,
    SaveIcon,
    LoaderIcon,
    LoaderCircle,
    CoinsIcon,
    InfoIcon,
    AlertCircleIcon,
    CheckCircleIcon,
    LockIcon,
    UnlockIcon,
    ExternalLinkIcon,
} from 'lucide-vue-next';

interface ServerForm {
    name: string;
    invite_code: string;
    server_id: string;
    description: string;
    icon_url: string;
    coins: number;
}

interface J4RServer {
    id: number;
    name: string;
    invite_code: string;
    server_id: string | null;
    description: string | null;
    icon_url: string | null;
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
const saving = ref<boolean>(false);
const errorMessage = ref<string>('');
const successMessage = ref<string>('');
const originalServer = ref<J4RServer | null>(null);

const serverForm = ref<ServerForm>({
    name: '',
    invite_code: '',
    server_id: '',
    description: '',
    icon_url: '',
    coins: 0,
});

// Check if form has changes
const hasChanges = computed(() => {
    if (!originalServer.value) return false;

    return (
        serverForm.value.name !== originalServer.value.name ||
        serverForm.value.invite_code !== originalServer.value.invite_code ||
        serverForm.value.coins !== originalServer.value.coins ||
        serverForm.value.server_id !== (originalServer.value.server_id || '') ||
        serverForm.value.description !== (originalServer.value.description || '') ||
        serverForm.value.icon_url !== (originalServer.value.icon_url || '')
    );
});

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
            originalServer.value = data.server;
            serverForm.value = {
                name: data.server.name,
                invite_code: data.server.invite_code,
                server_id: data.server.server_id,
                description: data.server.description || '',
                icon_url: data.server.icon_url || '',
                coins: data.server.coins,
            };
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

const updateServer = async (): Promise<void> => {
    saving.value = true;
    errorMessage.value = '';
    successMessage.value = '';

    try {
        // Validate form
        if (!serverForm.value.name.trim()) {
            throw new Error('Server name is required');
        }
        if (!serverForm.value.invite_code.trim()) {
            throw new Error('Discord invite code is required');
        }
        if (!serverForm.value.server_id.trim()) {
            throw new Error('Discord server ID is required');
        }
        if (serverForm.value.coins < 0) {
            throw new Error('Coins must be a positive number');
        }

        // Clean invite code (remove discord.gg/ if present)
        let inviteCode = serverForm.value.invite_code.trim();
        if (inviteCode.includes('discord.gg/')) {
            const splitResult = inviteCode.split('discord.gg/')[1];
            if (splitResult) {
                inviteCode = splitResult;
            }
        }
        if (inviteCode.includes('discordapp.com/invite/')) {
            const splitResult = inviteCode.split('discordapp.com/invite/')[1];
            if (splitResult) {
                inviteCode = splitResult;
            }
        }

        const formData = new FormData();
        formData.append('name', serverForm.value.name.trim());
        formData.append('invite_code', inviteCode);
        formData.append('server_id', serverForm.value.server_id.trim() || '');
        formData.append('description', serverForm.value.description.trim() || '');
        formData.append('icon_url', serverForm.value.icon_url.trim() || '');
        formData.append('coins', serverForm.value.coins.toString());

        const response = await fetch(`/api/admin/j4r/servers/${serverId}/update`, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
            },
            body: formData,
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || 'Failed to update J4R server');
        }

        const data = await response.json();

        if (data.success) {
            successMessage.value = 'J4R server updated successfully!';
            originalServer.value = data.server;

            // Update form with the response data
            serverForm.value = {
                name: data.server.name,
                invite_code: data.server.invite_code,
                server_id: data.server.server_id,
                description: data.server.description || '',
                icon_url: data.server.icon_url || '',
                coins: data.server.coins,
            };
        } else {
            throw new Error(data.message || 'Failed to update J4R server');
        }
    } catch (error) {
        errorMessage.value = error instanceof Error ? error.message : 'An unexpected error occurred';
        console.error('Error updating J4R server:', error);
    } finally {
        saving.value = false;
    }
};

const toggleLock = async (): Promise<void> => {
    if (!originalServer.value) return;

    const isLocked = originalServer.value.locked === 'true';
    const action = isLocked ? 'unlock' : 'lock';

    saving.value = true;
    errorMessage.value = '';
    successMessage.value = '';

    try {
        const response = await fetch(`/api/admin/j4r/servers/${serverId}/${action}`, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error(`Failed to ${action} server`);
        }

        const data = await response.json();

        if (data.success) {
            successMessage.value = `Server ${action}ed successfully!`;
            originalServer.value = data.server;
        } else {
            throw new Error(data.message || `Failed to ${action} server`);
        }
    } catch (error) {
        errorMessage.value = error instanceof Error ? error.message : `Failed to ${action} server`;
        console.error(`Error ${action}ing server:`, error);
    } finally {
        saving.value = false;
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
