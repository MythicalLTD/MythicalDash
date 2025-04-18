<script setup lang="ts">
import { ref, onMounted } from 'vue';
import CardComponent from '@/components/client/ui/Card/CardComponent.vue';
import Button from '@/components/client/ui/Button.vue';
import {
    Github as GithubIcon,
    MessageSquare as DiscordIcon,
    Unlink as UnlinkIcon,
    Link as LinkIcon,
    AlertCircle as AlertIcon,
} from 'lucide-vue-next';
import Session from '@/mythicaldash/Session';
import { useI18n } from 'vue-i18n';
import { useSettingsStore } from '@/stores/settings';

const Settings = useSettingsStore();
const { t } = useI18n();

// Types
type Provider = 'discord' | 'github';

interface LinkedAccount {
    id: string;
    provider: Provider;
    connected: boolean;
    username?: string;
    email?: string;
    avatar?: string;
    connectedAt?: string;
}

interface ProviderConfig {
    name: string;
    icon: unknown;
    color: string;
    description: string;
}

// State
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
]);

const isLoading = ref(true);
const error = ref<string | null>(null);

// Provider configurations
const providerConfigs: Record<Provider, ProviderConfig> = {
    discord: {
        name: t('account.pages.linked_accounts.discord.title'),
        icon: DiscordIcon,
        color: 'bg-[#5865F2]/10 text-[#5865F2]',
        description: t('account.pages.linked_accounts.discord.description'),
    },
    github: {
        name: t('account.pages.linked_accounts.github.title'),
        icon: GithubIcon,
        color: 'bg-gray-700/10 text-gray-300',
        description: t('account.pages.linked_accounts.github.description'),
    },
};

// Utility functions
const formatDate = (dateString: string | undefined): string => {
    if (!dateString) return 'N/A';

    const date = new Date(dateString);
    return new Intl.DateTimeFormat('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    }).format(date);
};

// API functions
const connectAccount = async (provider: Provider) => {
    try {
        if (
            provider === 'discord' &&
            (Settings.getSetting('discord_enabled') !== 'true' ||
                Settings.getSetting('discord_link_allowed') !== 'true')
        ) {
            throw new Error('Discord linking is not enabled');
        }
        if (
            provider === 'github' &&
            (Settings.getSetting('github_enabled') !== 'true' || Settings.getSetting('github_link_allowed') !== 'true')
        ) {
            throw new Error('GitHub linking is not enabled');
        }
        window.location.href = `/api/user/auth/callback/${provider}/link`;
    } catch (err) {
        console.error(`Error connecting ${provider} account:`, err);
        error.value = t('account.pages.linked_accounts.errors.connect', {
            provider: providerConfigs[provider].name,
        });
    }
};

const unlinkAccount = async (provider: Provider) => {
    try {
        window.location.href = `/api/user/auth/callback/${provider}/unlink`;
    } catch (err) {
        console.error(`Error unlinking ${provider} account:`, err);
        error.value = t('account.pages.linked_accounts.errors.unlink', {
            provider: providerConfigs[provider].name,
        });
    }
};

const fetchLinkedAccounts = async () => {
    isLoading.value = true;
    error.value = null;

    try {
        // Simulate API call
        setTimeout(() => {
            if (Session.getInfo('discord_linked') === 'true') {
                const discordAccount = linkedAccounts.value.find((a) => a.provider === 'discord');
                if (discordAccount) {
                    discordAccount.connected = true;
                    discordAccount.username = Session.getInfo('discord_username');
                    discordAccount.email = Session.getInfo('discord_email');
                    discordAccount.connectedAt = new Date().toISOString();
                }
            }
            if (Session.getInfo('github_linked') === 'true') {
                const githubAccount = linkedAccounts.value.find((a) => a.provider === 'github');
                if (githubAccount) {
                    githubAccount.connected = true;
                    githubAccount.username = Session.getInfo('github_username');
                    githubAccount.email = Session.getInfo('github_email');
                    githubAccount.connectedAt = new Date().toISOString();
                }
            }
            isLoading.value = false;
        }, 1000);
    } catch (err) {
        console.error('Error fetching linked accounts:', err);
        error.value = t('account.pages.linked_accounts.errors.fetch');
        isLoading.value = false;
    }
};

// Lifecycle hooks
onMounted(() => {
    fetchLinkedAccounts();
});
</script>

<template>
    <div class="space-y-6">
        <!-- Header -->
        <div>
            <h2 class="text-xl font-bold text-gray-100">{{ t('account.pages.linked_accounts.title') }}</h2>
            <p class="text-gray-400 mt-1">
                {{ t('account.pages.linked_accounts.description') }}
            </p>
        </div>

        <!-- Error Alert -->
        <div v-if="error" class="bg-red-500/10 border border-red-500/20 rounded-lg p-4 flex items-start gap-3">
            <AlertIcon class="h-5 w-5 text-red-400 mt-0.5 flex-shrink-0" />
            <div>
                <h3 class="text-sm font-medium text-red-400">
                    {{ t('account.pages.linked_accounts.errors.title') }}
                </h3>
                <p class="text-xs text-gray-400 mt-1">{{ error }}</p>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="isLoading" class="space-y-4">
            <div v-for="i in 3" :key="i" class="bg-[#1a1a2e]/30 rounded-lg p-6 animate-pulse">
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
                    <div :class="['p-3 rounded-lg', providerConfigs[account.provider].color]">
                        <component :is="providerConfigs[account.provider].icon" class="h-6 w-6" />
                    </div>

                    <!-- Account Info -->
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-medium text-gray-200">
                            {{ providerConfigs[account.provider].name }}
                        </h3>
                        <p class="text-xs text-gray-500">{{ providerConfigs[account.provider].description }}</p>
                        <div v-if="account.connected" class="mt-2">
                            <p class="text-sm text-gray-400">
                                {{ t('account.pages.linked_accounts.connected_as') }}
                                <span class="text-indigo-400 font-medium">{{ account.username }}</span>
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ t('account.pages.linked_accounts.connected_on') }}
                                {{ formatDate(account.connectedAt) }}
                            </p>
                        </div>
                        <p v-else class="text-sm text-gray-500 mt-2">
                            {{ t('account.pages.linked_accounts.not_connected') }}
                        </p>
                    </div>

                    <!-- Action Button -->
                    <div>
                        <Button
                            v-if="account.connected"
                            @click="unlinkAccount(account.provider)"
                            variant="danger"
                            small
                        >
                            <template #icon>
                                <UnlinkIcon class="h-4 w-4" />
                            </template>
                            {{ t('account.pages.linked_accounts.disconnect') }}
                        </Button>
                        <Button v-else @click="connectAccount(account.provider)" variant="secondary" small>
                            <template #icon>
                                <LinkIcon class="h-4 w-4" />
                            </template>
                            {{ t('account.pages.linked_accounts.connect') }}
                        </Button>
                    </div>
                </div>
            </CardComponent>
        </div>

        <!-- Benefits Section -->
        <CardComponent :cardTitle="t('account.pages.linked_accounts.benefits.title')" class="mt-8">
            <div class="space-y-4">
                <div
                    v-for="(benefit, index) in [
                        {
                            title: t('account.pages.linked_accounts.benefits.simplified_login.title'),
                            description: t('account.pages.linked_accounts.benefits.simplified_login.description'),
                            icon: 'check-circle',
                        },
                        {
                            title: t('account.pages.linked_accounts.benefits.enhanced_security.title'),
                            description: t('account.pages.linked_accounts.benefits.enhanced_security.description'),
                            icon: 'shield',
                        },
                        {
                            title: t('account.pages.linked_accounts.benefits.seamless_integration.title'),
                            description: t('account.pages.linked_accounts.benefits.seamless_integration.description'),
                            icon: 'integration',
                        },
                    ]"
                    :key="index"
                    class="flex items-start gap-3"
                >
                    <div class="p-2 rounded-lg bg-indigo-500/10">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5 text-indigo-400"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                v-if="benefit.icon === 'check-circle'"
                                fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd"
                            />
                            <path
                                v-else-if="benefit.icon === 'shield'"
                                fill-rule="evenodd"
                                d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd"
                            />
                            <path
                                v-else
                                d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM15.657 5.757a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM5.05 6.464A1 1 0 106.464 5.05l-.707-.707a1 1 0 00-1.414 1.414l.707.707zM5 10a1 1 0 01-1 1H3a1 1 0 110-2h1a1 1 0 011 1zM8 16v-1h4v1a2 2 0 11-4 0zM12 14c.015-.34.208-.646.477-.859a4 4 0 10-4.954 0c.27.213.462.519.476.859h4.002z"
                            />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-300">{{ benefit.title }}</h3>
                        <p class="text-xs text-gray-500 mt-1">{{ benefit.description }}</p>
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
</style>
