<template>
    <LayoutDashboard>
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-100 mb-2">{{ t('renew.pages.index.title') }}</h1>
            <p class="text-gray-400">{{ t('renew.pages.index.subTitle') }}</p>
        </div>

        <div v-if="isLoading" class="flex justify-center my-8">
            <div class="animate-pulse text-gray-300">{{ t('renew.pages.index.loading') }}</div>
        </div>

        <div v-else>
            <!-- Server Info -->
            <CardComponent card-title="Server Information" class="mb-6">
                <div class="space-y-4">
                    <div
                        class="flex items-center justify-between p-4 bg-[#0a0a15]/50 rounded-lg border border-[#1a1a2f]/30"
                    >
                        <div>
                            <h3 class="text-lg font-medium text-gray-100">
                                {{ serverDetails.attributes?.name || t('renew.pages.index.unknownError') }}
                            </h3>
                            <p class="text-gray-400 mt-1">
                                {{ serverDetails.attributes?.description || t('renew.pages.index.unknownError') }}
                            </p>
                            <p class="text-sm text-gray-400 mt-2">
                                <span class="font-medium">{{ t('renew.pages.index.expiresAt') }}: </span>
                                <span :class="isExpired ? 'text-red-400' : 'text-gray-300'">
                                    {{ formatDate(serverDetails.mythicaldash?.expires_at) }}
                                </span>
                                <span v-if="isExpired" class="ml-2 text-red-400">
                                    ({{ t('renew.pages.index.expired') }})
                                </span>
                            </p>
                        </div>
                        <div class="text-right">
                            <div class="text-sm text-gray-400">
                                <span>{{ t('renew.pages.index.type') }}: </span>
                                <span class="text-gray-300">{{
                                    serverDetails.category?.name || t('renew.pages.index.unknownError')
                                }}</span>
                            </div>
                            <div class="text-sm text-gray-400">
                                <span>{{ t('renew.pages.index.version') }}: </span>
                                <span class="text-gray-300">{{
                                    serverDetails.service?.name || t('renew.pages.index.unknownError')
                                }}</span>
                            </div>
                            <div class="text-sm text-gray-400">
                                <span>{{ t('renew.pages.index.location') }}: </span>
                                <span class="text-gray-300">{{
                                    serverDetails.location?.name || t('renew.pages.index.unknownError')
                                }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </CardComponent>

            <!-- Renewal Info -->
            <CardComponent :card-title="t('renew.pages.index.renewalInfo')" class="mb-6">
                <div class="p-4 bg-blue-900/20 border border-blue-800/30 rounded-lg">
                    <div class="flex items-start">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-6 w-6 text-blue-400 mr-3 flex-shrink-0"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                        <div>
                            <h3 class="text-lg font-medium text-blue-400">
                                {{ t('renew.pages.index.details.title') }}
                            </h3>
                            <p class="mt-2 text-gray-300">
                                {{ t('renew.pages.index.details.description') }}
                            </p>
                            <ul class="mt-2 space-y-1 text-gray-400 list-disc list-inside">
                                <li>{{ t('renew.pages.index.details.keepData') }}</li>
                                <li>{{ t('renew.pages.index.details.keepSettings') }}</li>
                                <li>{{ t('renew.pages.index.details.keepBackups') }}</li>
                                <li>{{ t('renew.pages.index.details.keepDatabases') }}</li>
                            </ul>
                            <div class="mt-4 p-4 bg-blue-900/30 rounded-lg">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="text-sm text-gray-400">{{ t('renew.pages.index.cost.title') }}</p>
                                        <p class="text-lg font-medium text-white">
                                            {{ renewalCost }} {{ t('renew.pages.index.cost.coins') }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm text-gray-400">{{ t('renew.pages.index.duration.title') }}</p>
                                        <p class="text-lg font-medium text-white">
                                            {{ server_renew_days }} {{ t('renew.pages.index.duration.days') }}
                                        </p>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-400">{{ t('renew.pages.index.yourBalance') }}</p>
                                    <p
                                        class="text-lg font-medium"
                                        :class="hasEnoughCoins ? 'text-green-400' : 'text-red-400'"
                                    >
                                        {{ userBalance }} {{ t('renew.pages.index.cost.coins') }}
                                    </p>
                                </div>
                            </div>
                            <p class="mt-3 text-gray-300">
                                {{ t('renew.pages.index.details.confirmRenewal') }}
                            </p>
                        </div>
                    </div>
                </div>
            </CardComponent>

            <!-- Confirmation -->
            <CardComponent :card-title="t('renew.pages.index.card.title')" class="mb-6">
                <div class="space-y-4">
                    <p class="text-gray-300">
                        {{ t('renew.pages.index.card.description') }}
                        <span class="font-medium text-white">{{ serverDetails.attributes?.name }}</span>
                    </p>
                    <TextInput v-model="confirmationText" :placeholder="t('renew.pages.index.card.placeholder')" />
                </div>
            </CardComponent>

            <!-- Actions -->
            <div class="flex justify-between">
                <Button
                    type="button"
                    :text="t('renew.pages.index.button.cancel')"
                    variant="secondary"
                    @click="router.push('/dashboard')"
                />
                <Button
                    type="button"
                    :text="t('renew.pages.index.button.renew')"
                    variant="primary"
                    :disabled="!canRenew"
                    :loading="isSubmitting"
                    @click="renewServer"
                />
            </div>
        </div>
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import LayoutDashboard from '@/components/client/LayoutDashboard.vue';
import CardComponent from '@/components/client/ui/Card/CardComponent.vue';
import { TextInput } from '@/components/client/ui/TextForms';
import Button from '@/components/client/ui/Button.vue';
import Swal from 'sweetalert2';
import { MythicalDOM } from '@/mythicaldash/MythicalDOM';
import { useSound } from '@vueuse/sound';
import failedAlertSfx from '@/assets/sounds/error.mp3';
import successAlertSfx from '@/assets/sounds/success.mp3';
import { useI18n } from 'vue-i18n';
import { useSettingsStore } from '@/stores/settings';
import Session from '@/mythicaldash/Session';

const Settings = useSettingsStore();
const { t } = useI18n();
const { play: playError } = useSound(failedAlertSfx);
const { play: playSuccess } = useSound(successAlertSfx);

const router = useRouter();
const route = useRoute();
const serverId = route.params.id as string;
const isLoading = ref(true);
const isSubmitting = ref(false);
const confirmationText = ref('');
const userBalance = ref(Session.getInfoInt('credits'));

const server_renew_cost = Settings.getSetting('server_renew_cost');
const server_renew_days = Settings.getSetting('server_renew_days');
const server_renew_enabled = Settings.getSetting('server_renew_enabled');

if (server_renew_enabled == 'false') {
    router.push('/dashboard');
}

MythicalDOM.setPageTitle(t('renew.pages.index.title'));

interface ServerDetails {
    id: number;
    attributes?: {
        name: string;
        description: string;
    };
    location?: {
        name: string;
    };
    category?: {
        name: string;
    };
    service?: {
        name: string;
    };
    mythicaldash?: {
        expires_at: string;
    };
}

const serverDetails = ref<ServerDetails>({
    id: 0,
});

const isExpired = computed(() => {
    if (!serverDetails.value.mythicaldash?.expires_at) return false;
    const expiresAt = new Date(serverDetails.value.mythicaldash.expires_at);
    return expiresAt < new Date();
});

const renewalCost = computed(() => {
    if (isExpired.value) {
        return Math.ceil(parseInt(server_renew_cost) * 1.5); // 50% more expensive if expired
    }
    return parseInt(server_renew_cost);
});

const hasEnoughCoins = computed(() => {
    return userBalance.value >= renewalCost.value;
});

const canRenew = computed(() => {
    return (
        confirmationText.value === serverDetails.value.attributes?.name && !isSubmitting.value && hasEnoughCoins.value
    );
});

const formatDate = (dateString?: string) => {
    if (!dateString) return t('renew.pages.index.unknownError');
    return new Date(dateString).toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

// Load server data and user balance
onMounted(async () => {
    isLoading.value = true;
    try {
        // Load server data
        const serverResponse = await fetch(`/api/user/server/${serverId}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            },
        });

        if (!serverResponse.ok) {
            throw new Error('Failed to load server data');
        }

        const serverData = await serverResponse.json();

        if (serverData.success) {
            serverDetails.value = serverData.server;
        } else {
            throw new Error(serverData.message || 'Failed to load server data');
        }
    } catch (error) {
        console.error('Error loading data:', error);
        playError();
        Swal.fire({
            icon: 'error',
            title: t('renew.pages.alerts.error.title'),
            text: t('renew.pages.alerts.error.generic') + ' ' + error,
            footer: t('renew.pages.alerts.error.footer'),
            confirmButtonText: t('renew.pages.alerts.error.confirmButtonText'),
            showConfirmButton: true,
        }).then(() => {
            router.push('/dashboard');
        });
    } finally {
        isLoading.value = false;
    }
});

// Renew server
const renewServer = async () => {
    if (!canRenew.value) {
        playError();
        Swal.fire({
            icon: 'error',
            title: t('renew.pages.alerts.error.title'),
            text: t('renew.pages.alerts.error.confirmation'),
            footer: t('renew.pages.alerts.error.footer'),
            confirmButtonText: t('renew.pages.alerts.error.confirmButtonText'),
            showConfirmButton: true,
        });
        return;
    }

    isSubmitting.value = true;

    try {
        const response = await fetch(`/api/user/server/${serverId}/renew`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                cost: renewalCost.value,
                days: parseInt(server_renew_days),
            }),
        });

        const data = await response.json();

        if (data.success) {
            playSuccess();
            Swal.fire({
                icon: 'success',
                title: t('renew.pages.alerts.success.title'),
                text: t('renew.pages.alerts.success.generic'),
                footer: t('renew.pages.alerts.success.footer'),
                confirmButtonText: t('renew.pages.alerts.success.confirmButtonText'),
                showConfirmButton: true,
            }).then(() => {
                router.push('/dashboard');
            });
        } else {
            playError();
            Swal.fire({
                icon: 'error',
                title: t('renew.pages.alerts.error.title'),
                text: data.message || t('renew.pages.alerts.error.generic'),
                footer: t('renew.pages.alerts.error.footer'),
                confirmButtonText: t('renew.pages.alerts.error.confirmButtonText'),
                showConfirmButton: true,
            });
        }
    } catch (error) {
        console.error('Error renewing server:', error);
        playError();
        Swal.fire({
            icon: 'error',
            title: t('renew.pages.alerts.error.title'),
            text: t('renew.pages.alerts.error.generic') + ' ' + error,
            footer: t('renew.pages.alerts.error.footer'),
            confirmButtonText: t('renew.pages.alerts.error.confirmButtonText'),
            showConfirmButton: true,
        });
    } finally {
        isSubmitting.value = false;
    }
};
</script>
