<script setup lang="ts">
import { ref, onMounted } from 'vue';
import CardComponent from '@/components/client/ui/Card/CardComponent.vue';
import Button from '@/components/client/ui/Button.vue';
import {
    Github as GithubIcon,
    Mail as GoogleIcon,
    MessageSquare as DiscordIcon,
    Unlink as UnlinkIcon,
    Link as LinkIcon,
    AlertCircle as AlertIcon,
} from 'lucide-vue-next';
import Session from '@/mythicaldash/Session';

interface LinkedAccount {
    id: string;
    provider: 'discord' | 'github' | 'google';
    connected: boolean;
    username?: string;
    email?: string;
    avatar?: string;
    connectedAt?: string;
}

const linkedAccounts = ref<LinkedAccount[]>([
    {
        id: 'discord',
        provider: 'discord',
        connected: false,
    },
    {
        id: 'github',
        provider: 'github',
        connected: false,
    },
    {
        id: 'google',
        provider: 'google',
        connected: false,
    },
]);

const isLoading = ref(true);
const error = ref<string | null>(null);

// Get provider icon
const getProviderIcon = (provider: string) => {
    switch (provider) {
        case 'discord':
            return DiscordIcon;
        case 'github':
            return GithubIcon;
        case 'google':
            return GoogleIcon;
        default:
            return null;
    }
};

// Get provider name
const getProviderName = (provider: string) => {
    switch (provider) {
        case 'discord':
            return 'Discord';
        case 'github':
            return 'GitHub';
        case 'google':
            return 'Google';
        default:
            return provider;
    }
};

// Get provider color
const getProviderColor = (provider: string) => {
    switch (provider) {
        case 'discord':
            return 'bg-[#5865F2]/10 text-[#5865F2]';
        case 'github':
            return 'bg-gray-700/10 text-gray-300';
        case 'google':
            return 'bg-red-500/10 text-red-400';
        default:
            return 'bg-indigo-500/10 text-indigo-400';
    }
};

// Format date
const formatDate = (dateString: string | undefined): string => {
    if (!dateString) return 'N/A';

    const date = new Date(dateString);
    return new Intl.DateTimeFormat('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    }).format(date);
};

// Connect account
const connectAccount = (provider: string) => {
    // In a real implementation, this would redirect to the OAuth flow
    window.location.href = `/api/auth/${provider}/connect`;
};

// Disconnect account
const disconnectAccount = async (provider: string) => {
    const account = linkedAccounts.value.find((a) => a.provider === provider);
    if (!account) return;

    try {
        // In a real implementation, this would call an API endpoint
        // For now, we'll simulate the API call
        account.connected = false;
        account.username = undefined;
        account.email = undefined;
        account.avatar = undefined;
        account.connectedAt = undefined;

        // Simulate API call
        await fetch(`/api/auth/${provider}/disconnect`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
        });
    } catch (err) {
        console.error(`Failed to disconnect ${provider} account:`, err);
        error.value = `Failed to disconnect ${getProviderName(provider)} account. Please try again.`;
    }
};

// Fetch linked accounts
const fetchLinkedAccounts = async () => {
    isLoading.value = true;
    error.value = null;

    try {
        // In a real implementation, this would be an API call
        // For now, we'll simulate fetching the linked accounts
        setTimeout(() => {
            // Simulate some connected accounts
            const discordAccount = linkedAccounts.value.find((a) => a.provider === 'discord');
            if (discordAccount) {
                discordAccount.connected = true;
                discordAccount.username = 'mythicaldoggo';
                discordAccount.email = Session.getInfo('email');
                discordAccount.connectedAt = new Date().toISOString();
            }

            isLoading.value = false;
        }, 1000);
    } catch (err) {
        console.error('Error fetching linked accounts:', err);
        error.value = 'Failed to load linked accounts. Please refresh the page.';
        isLoading.value = false;
    }
};

onMounted(() => {
    fetchLinkedAccounts();
});
</script>

<template>
    <div class="space-y-6">
        <!-- Linked Accounts Header -->
        <div>
            <h2 class="text-xl font-bold text-gray-100">Linked Accounts</h2>
            <p class="text-gray-400 mt-1">
                Connect your MythicalDash account to external services for easier login and enhanced features.
            </p>
        </div>

        <!-- Error Alert -->
        <div v-if="error" class="bg-red-500/10 border border-red-500/20 rounded-lg p-4 flex items-start gap-3">
            <AlertIcon class="h-5 w-5 text-red-400 mt-0.5 flex-shrink-0" />
            <div>
                <h3 class="text-sm font-medium text-red-400">Error</h3>
                <p class="text-xs text-gray-400 mt-1">{{ error }}</p>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="isLoading" class="space-y-4">
            <div v-for="i in 4" :key="i" class="bg-[#1a1a2e]/30 rounded-lg p-6 animate-pulse">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-lg bg-[#1a1a2e]/50"></div>
                    <div class="flex-1">
                        <div class="h-5 w-24 bg-[#1a1a2e]/50 rounded mb-2"></div>
                        <div class="h-4 w-40 bg-[#1a1a2e]/50 rounded"></div>
                    </div>
                    <div class="w-24 h-8 bg-[#1a1a2e]/50 rounded-lg"></div>
                </div>
            </div>
        </div>

        <!-- Linked Accounts List -->
        <div v-else class="space-y-4">
            <CardComponent v-for="account in linkedAccounts" :key="account.id" class="overflow-hidden">
                <div class="flex flex-col sm:flex-row sm:items-center gap-4 p-1">
                    <!-- Provider Icon -->
                    <div :class="['p-3 rounded-lg', getProviderColor(account.provider)]">
                        <component :is="getProviderIcon(account.provider)" class="h-6 w-6" />
                    </div>

                    <!-- Account Info -->
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-medium text-gray-200">{{ getProviderName(account.provider) }}</h3>
                        <div v-if="account.connected" class="mt-1">
                            <p class="text-sm text-gray-400">
                                Connected as <span class="text-indigo-400 font-medium">{{ account.username }}</span>
                            </p>
                            <p class="text-xs text-gray-500 mt-1">Connected on {{ formatDate(account.connectedAt) }}</p>
                        </div>
                        <p v-else class="text-sm text-gray-500">Not connected</p>
                    </div>

                    <!-- Action Button -->
                    <div>
                        <Button
                            v-if="account.connected"
                            @click="disconnectAccount(account.provider)"
                            variant="danger"
                            small
                        >
                            <template #icon>
                                <UnlinkIcon class="h-4 w-4" />
                            </template>
                            Disconnect
                        </Button>
                        <Button v-else @click="connectAccount(account.provider)" variant="secondary" small>
                            <template #icon>
                                <LinkIcon class="h-4 w-4" />
                            </template>
                            Connect
                        </Button>
                    </div>
                </div>
            </CardComponent>
        </div>

        <!-- Benefits Section -->
        <CardComponent cardTitle="Benefits of Linking Accounts" class="mt-8">
            <div class="space-y-4">
                <div class="flex items-start gap-3">
                    <div class="p-2 rounded-lg bg-indigo-500/10">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5 text-indigo-400"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-300">Simplified Login</h3>
                        <p class="text-xs text-gray-500 mt-1">
                            Sign in quickly using your existing accounts without having to remember another password.
                        </p>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div class="p-2 rounded-lg bg-indigo-500/10">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5 text-indigo-400"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-300">Enhanced Security</h3>
                        <p class="text-xs text-gray-500 mt-1">
                            Add an extra layer of security to your account with trusted providers' authentication
                            systems.
                        </p>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div class="p-2 rounded-lg bg-indigo-500/10">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5 text-indigo-400"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM15.657 5.757a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM5.05 6.464A1 1 0 106.464 5.05l-.707-.707a1 1 0 00-1.414 1.414l.707.707zM5 10a1 1 0 01-1 1H3a1 1 0 110-2h1a1 1 0 011 1zM8 16v-1h4v1a2 2 0 11-4 0zM12 14c.015-.34.208-.646.477-.859a4 4 0 10-4.954 0c.27.213.462.519.476.859h4.002z"
                            />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-300">Seamless Integration</h3>
                        <p class="text-xs text-gray-500 mt-1">
                            Access integrated features specific to each platform, enhancing your MythicalDash
                            experience.
                        </p>
                    </div>
                </div>
            </div>
        </CardComponent>
    </div>
</template>

<style scoped>
/* Animation for loading skeleton */
@keyframes pulse {
    0%,
    100% {
        opacity: 0.5;
    }

    50% {
        opacity: 0.8;
    }
}

.animate-pulse {
    animation: pulse 1.5s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Staggered animation delay for loading items */
.animate-pulse:nth-child(1) {
    animation-delay: 0s;
}

.animate-pulse:nth-child(2) {
    animation-delay: 0.1s;
}

.animate-pulse:nth-child(3) {
    animation-delay: 0.2s;
}

.animate-pulse:nth-child(4) {
    animation-delay: 0.3s;
}
</style>
