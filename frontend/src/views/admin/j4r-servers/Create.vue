<template>
    <LayoutDashboard>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-400">Create J4R Server</h1>
            <button
                @click="router.push('/mc-admin/j4r-servers')"
                class="bg-gray-700 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:bg-gray-600 flex items-center"
            >
                <ArrowLeftIcon class="w-4 h-4 mr-2" />
                Back to J4R Servers
            </button>
        </div>

        <div class="bg-gray-800/50 backdrop-blur-md rounded-lg p-6">
            <form @submit.prevent="createServer" class="space-y-6">
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
                        <input
                            id="invite_code"
                            v-model="serverForm.invite_code"
                            type="text"
                            required
                            class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                            placeholder="e.g., abc123xyz"
                        />
                        <p class="mt-1 text-xs text-gray-500">
                            Just the invite code, not the full URL. Example: If the invite is discord.gg/abc123, enter
                            "abc123".
                        </p>
                    </div>

                    <div>
                        <label for="server_id" class="block text-sm font-medium text-gray-400 mb-1"
                            >Discord Server ID (Required)</label
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
                                    <p class="text-blue-400 font-medium mb-1">Important Notes:</p>
                                    <ul class="text-blue-300 space-y-1 text-xs">
                                        <li>• The Discord invite code must be valid and not expired</li>
                                        <li>• Users will automatically receive coins when they join the server</li>
                                        <li>• You can lock/unlock servers to temporarily disable them</li>
                                        <li>• Locked servers won't appear to users but remain in your list</li>
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
                        :disabled="saving"
                        class="px-4 py-2 bg-gradient-to-r from-pink-500 to-violet-500 rounded-lg text-white hover:opacity-90 transition-colors flex items-center disabled:opacity-50"
                    >
                        <LoaderIcon v-if="saving" class="animate-spin w-4 h-4 mr-2" />
                        <SaveIcon v-else class="w-4 h-4 mr-2" />
                        Create J4R Server
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
import {
    ArrowLeftIcon,
    SaveIcon,
    LoaderIcon,
    CoinsIcon,
    InfoIcon,
    AlertCircleIcon,
    CheckCircleIcon,
} from 'lucide-vue-next';

interface ServerForm {
    name: string;
    invite_code: string;
    server_id: string;
    description: string;
    icon_url: string;
    coins: number;
}

const router = useRouter();
const saving = ref<boolean>(false);
const errorMessage = ref<string>('');
const successMessage = ref<string>('');

const serverForm = ref<ServerForm>({
    name: '',
    invite_code: '',
    server_id: '',
    description: '',
    icon_url: '',
    coins: 100,
});

const createServer = async (): Promise<void> => {
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

        const response = await fetch('/api/admin/j4r/servers/create', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
            },
            body: formData,
        });

        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || 'Failed to create J4R server');
        }

        const data = await response.json();

        if (data.success) {
            successMessage.value = 'J4R server created successfully!';

            // Redirect after a short delay
            setTimeout(() => {
                router.push('/mc-admin/j4r-servers');
            }, 1500);
        } else {
            throw new Error(data.message || 'Failed to create J4R server');
        }
    } catch (error) {
        errorMessage.value = error instanceof Error ? error.message : 'An unexpected error occurred';
        console.error('Error creating J4R server:', error);
    } finally {
        saving.value = false;
    }
};
</script>
