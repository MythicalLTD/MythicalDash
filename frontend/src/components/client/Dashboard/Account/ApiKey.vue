<script setup lang="ts">
import { ref, onMounted } from 'vue';
import {
    Copy as CopyIcon,
    RefreshCw as RefreshIcon,
    ExternalLink as ExternalLinkIcon,
    Eye as EyeIcon,
    EyeOff as EyeOffIcon,
    Check as CheckIcon,
} from 'lucide-vue-next';
import { TextInput } from '@/components/client/ui/TextForms';
import Button from '@/components/client/ui/Button.vue';
import CardComponent from '@/components/client/ui/Card/CardComponent.vue';
import Session from '@/mythicaldash/Session';
import { useI18n } from 'vue-i18n';

const apiKey = ref<string>('');
const isLoading = ref(false);
const isCopied = ref(false);
const isRevealed = ref(false);
const isResetting = ref(false);

// Get token from cookies
const getTokenFromCookies = (): string | null => {
    const cookies = document.cookie.split(';');
    for (let i = 0; i < cookies.length; i++) {
        const cookie = cookies[i].trim();
        if (cookie.startsWith('user_token=')) {
            return cookie.substring('user_token='.length, cookie.length);
        }
    }
    return null;
};

// Set token in cookies
const setTokenInCookies = (token: string): void => {
    // Set cookie to expire in 1 year
    const expiryDate = new Date();
    expiryDate.setFullYear(expiryDate.getFullYear() + 1);
    document.cookie = `token=${token}; expires=${expiryDate.toUTCString()}; path=/; secure; samesite=strict`;
};

// Fetch API key on component mount
onMounted(async () => {
    isLoading.value = true;
    try {
        // Get token from cookies
        setTimeout(() => {
            const token = getTokenFromCookies();
            apiKey.value = token || 'md_' + generateRandomString(32);

            // If no token was found, set the generated one in cookies
            if (!token) {
                setTokenInCookies(apiKey.value);
            }

            isLoading.value = false;
        }, 800);
    } catch (error) {
        console.error('Error fetching API key:', error);
        isLoading.value = false;
    }
});

// Copy API key to clipboard
const copyApiKey = () => {
    if (!apiKey.value) return;

    try {
        // Create a temporary textarea element
        const textArea = document.createElement('textarea');
        textArea.value = apiKey.value;

        // Make the textarea out of viewport
        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        textArea.style.top = '-999999px';
        document.body.appendChild(textArea);

        // Select and copy the text
        textArea.focus();
        textArea.select();

        const successful = document.execCommand('copy');

        // Clean up
        document.body.removeChild(textArea);

        if (successful) {
            isCopied.value = true;
            setTimeout(() => {
                isCopied.value = false;
            }, 2000);
        } else {
            console.error('Failed to copy API key: execCommand returned false');
        }
    } catch (err) {
        console.error('Failed to copy API key:', err);

        // Fallback to clipboard API if available
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard
                .writeText(apiKey.value)
                .then(() => {
                    isCopied.value = true;
                    setTimeout(() => {
                        isCopied.value = false;
                    }, 2000);
                })
                .catch((err) => {
                    console.error('Fallback clipboard API failed:', err);
                });
        }
    }
};

// Reset API key
const resetApiKeyLocal = () => {
    isResetting.value = true;

    // Generate a new API key and update the cookie
    setTimeout(() => {
        const token = getTokenFromCookies();

        apiKey.value = token || 'md_' + generateRandomString(32);
        if (!token) {
            setTokenInCookies(apiKey.value);
        }
        isResetting.value = false;
    }, 1000);
};

// Toggle API key visibility
const toggleReveal = () => {
    isRevealed.value = !isRevealed.value;
};

// Helper function to generate a random string
const generateRandomString = (length: number): string => {
    const chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
    let result = '';
    for (let i = 0; i < length; i++) {
        result += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return result;
};

// Mask API key for display
const maskedApiKey = (key: string): string => {
    if (!key) return '';
    const prefix = key.substring(0, 3);
    const suffix = key.substring(key.length - 4);
    return `${prefix}${'•'.repeat(key.length - 7)}${suffix}`;
};

const resetApiKey = () => {
    fetch('/api/user/session/apiKey/reset', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
    });
    resetApiKeyLocal();
    localStorage.clear();
    Session.cleanup();
    document.cookie = 'user_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
    window.location.href = '/auth/login';
};

const { t } = useI18n();
</script>

<template>
    <div class="space-y-6">
        <!-- API Key Information -->
        <CardComponent
            :cardTitle="t('account.pages.apikey.page.card.title')"
            :cardDescription="t('account.pages.apikey.page.card.description')"
        >
            <div class="space-y-6">
                <!-- API Key Display -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-300">{{
                        t('account.pages.apikey.page.card.yourKey')
                    }}</label>
                    <div class="relative">
                        <TextInput
                            :model-value="isRevealed ? apiKey : maskedApiKey(apiKey)"
                            @update:model-value="apiKey = $event"
                            :placeholder="
                                isLoading
                                    ? t('account.pages.apikey.page.card.loading')
                                    : t('account.pages.apikey.page.card.noApiKey')
                            "
                            :disabled="true"
                            inputClass="pr-24 font-mono text-xs"
                        />
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 gap-2">
                            <button
                                @click="toggleReveal"
                                class="text-gray-400 hover:text-indigo-400 transition-colors duration-200"
                                :disabled="isLoading || !apiKey"
                            >
                                <EyeIcon v-if="!isRevealed" class="h-4 w-4" />
                                <EyeOffIcon v-else class="h-4 w-4" />
                            </button>
                            <button
                                @click="copyApiKey"
                                class="text-gray-400 hover:text-indigo-400 transition-colors duration-200"
                                :disabled="isLoading || !apiKey"
                            >
                                <CopyIcon v-if="!isCopied" class="h-4 w-4" />
                                <CheckIcon v-else class="h-4 w-4 text-green-400" />
                            </button>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                        {{ t('account.pages.apikey.page.card.info') }}
                    </p>
                </div>

                <!-- API Key Actions -->
                <div class="flex flex-col sm:flex-row gap-4">
                    <Button
                        @click="resetApiKey"
                        variant="danger"
                        :loading="isResetting"
                        :disabled="isLoading"
                        class="sm:flex-1"
                    >
                        <template #icon>
                            <RefreshIcon class="h-4 w-4" />
                        </template>
                        {{ t('account.pages.apikey.page.card.resetKey') }}
                    </Button>

                    <a
                        href="https://www.postman.com/mythicalsystems/mythicaldash-v3/overview"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="sm:flex-1"
                    >
                        <Button variant="secondary" fullWidth>
                            <template #icon>
                                <ExternalLinkIcon class="h-4 w-4" />
                            </template>
                            {{ t('account.pages.apikey.page.card.apiDocumentation') }}
                        </Button>
                    </a>
                </div>
            </div>
        </CardComponent>
    </div>
</template>

<style scoped>
/* Custom scrollbar for code blocks */
pre::-webkit-scrollbar {
    height: 4px;
}

pre::-webkit-scrollbar-track {
    background: rgba(5, 5, 8, 0.2);
    border-radius: 2px;
}

pre::-webkit-scrollbar-thumb {
    background: rgba(99, 102, 241, 0.3);
    border-radius: 2px;
}

pre::-webkit-scrollbar-thumb:hover {
    background: rgba(99, 102, 241, 0.5);
}

/* Animation for copy button */
@keyframes pulse {
    0%,
    100% {
        opacity: 1;
    }

    50% {
        opacity: 0.6;
    }
}

.animate-pulse {
    animation: pulse 1.5s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
