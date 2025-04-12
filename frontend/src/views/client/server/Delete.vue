<template>
    <LayoutDashboard>
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-100 mb-2">{{ t('delete.pages.index.title') }}</h1>
            <p class="text-gray-400">{{ t('delete.pages.index.subTitle') }}</p>
        </div>

        <div v-if="isLoading" class="flex justify-center my-8">
            <div class="animate-pulse text-gray-300">{{ t('delete.pages.index.loading') }}</div>
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
                                {{ serverDetails.attributes?.name || t('delete.pages.index.unknownError') }}
                            </h3>
                            <p class="text-gray-400 mt-1">
                                {{ serverDetails.attributes?.description || t('delete.pages.index.unknownError') }}
                            </p>
                        </div>
                        <div class="text-right">
                            <div class="text-sm text-gray-400">
                                <span>{{ t('delete.pages.index.type') }}: </span>
                                <span class="text-gray-300">{{
                                    serverDetails.category?.name || t('delete.pages.index.unknownError')
                                }}</span>
                            </div>
                            <div class="text-sm text-gray-400">
                                <span>{{ t('delete.pages.index.version') }}: </span>
                                <span class="text-gray-300">{{
                                    serverDetails.service?.name || t('delete.pages.index.unknownError')
                                }}</span>
                            </div>
                            <div class="text-sm text-gray-400">
                                <span>{{ t('delete.pages.index.location') }}: </span>
                                <span class="text-gray-300">{{
                                    serverDetails.location?.name || t('delete.pages.index.unknownError')
                                }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </CardComponent>

            <!-- Warning -->
            <CardComponent :card-title="t('delete.pages.index.warning')" class="mb-6">
                <div class="p-4 bg-red-900/20 border border-red-800/30 rounded-lg">
                    <div class="flex items-start">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-6 w-6 text-red-400 mr-3 flex-shrink-0"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                            />
                        </svg>
                        <div>
                            <h3 class="text-lg font-medium text-red-400">
                                {{ t('delete.pages.index.details.title') }}
                            </h3>
                            <p class="mt-2 text-gray-300">
                                {{ t('delete.pages.index.details.description') }}
                            </p>
                            <ul class="mt-2 space-y-1 text-gray-400 list-disc list-inside">
                                <li>{{ t('delete.pages.index.details.files') }}</li>
                                <li>{{ t('delete.pages.index.details.databases') }}</li>
                                <li>{{ t('delete.pages.index.details.backups') }}</li>
                                <li>{{ t('delete.pages.index.details.configuration') }}</li>
                            </ul>
                            <p class="mt-3 text-gray-300">
                                {{ t('delete.pages.index.details.cannotBeUndone') }}
                            </p>
                        </div>
                    </div>
                </div>
            </CardComponent>

            <!-- Confirmation -->
            <CardComponent :card-title="t('delete.pages.index.card.title')" class="mb-6">
                <div class="space-y-4">
                    <p class="text-gray-300">
                        {{ t('delete.pages.index.card.description') }}
                        <span class="font-medium text-white">{{ serverDetails.attributes?.name }}</span>
                    </p>
                    <TextInput v-model="confirmationText" :placeholder="t('delete.pages.index.card.placeholder')" />
                </div>
            </CardComponent>

            <!-- Actions -->
            <div class="flex justify-between">
                <Button
                    type="button"
                    :text="t('delete.pages.index.button.cancel')"
                    variant="secondary"
                    @click="router.push('/dashboard')"
                />
                <Button
                    type="button"
                    :text="t('delete.pages.index.button.delete')"
                    variant="danger"
                    :disabled="confirmationText !== serverDetails.attributes?.name || isSubmitting"
                    :loading="isSubmitting"
                    @click="deleteServer"
                />
            </div>
        </div>
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
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

const { t } = useI18n();
const { play: playError } = useSound(failedAlertSfx);
const { play: playSuccess } = useSound(successAlertSfx);

MythicalDOM.setPageTitle(t('delete.pages.index.title'));

const router = useRouter();
const route = useRoute();
const serverId = route.params.id as string;
const isLoading = ref(true);
const isSubmitting = ref(false);
const confirmationText = ref('');

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
}

const serverDetails = ref<ServerDetails>({
    id: 0,
});

// Load server data
onMounted(async () => {
    isLoading.value = true;
    try {
        const response = await fetch(`/api/user/server/${serverId}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error('Failed to load server data');
        }

        const data = await response.json();

        if (data.success) {
            serverDetails.value = data.server;
        } else {
            throw new Error(data.message || 'Failed to load server data');
        }
    } catch (error) {
        console.error('Error loading server data:', error);
        playError();
        Swal.fire({
            icon: 'error',
            title: t('delete.pages.alerts.error.title'),
            text: t('delete.pages.alerts.error.generic') + ' ' + error,
            footer: t('delete.pages.alerts.error.footer'),
            confirmButtonText: t('delete.pages.alerts.error.confirmButtonText'),
            showConfirmButton: true,
        }).then(() => {
            router.push('/dashboard');
        });
    } finally {
        isLoading.value = false;
    }
});

// Delete server
const deleteServer = async () => {
    if (confirmationText.value !== serverDetails.value.attributes?.name) {
        playError();
        Swal.fire({
            icon: 'error',
            title: t('delete.pages.alerts.error.title'),
            text: t('delete.pages.alerts.error.confirmation'),
            footer: t('delete.pages.alerts.error.footer'),
            confirmButtonText: t('delete.pages.alerts.error.confirmButtonText'),
            showConfirmButton: true,
        });
        return;
    }

    isSubmitting.value = true;

    try {
        const response = await fetch(`/api/user/server/${serverId}/delete`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
        });

        const data = await response.json();

        if (data.success) {
            playSuccess();
            Swal.fire({
                icon: 'success',
                title: t('delete.pages.alerts.success.title'),
                text: t('delete.pages.alerts.success.generic'),
                footer: t('delete.pages.alerts.success.footer'),
                confirmButtonText: t('delete.pages.alerts.success.confirmButtonText'),
                showConfirmButton: true,
            }).then(() => {
                router.push('/dashboard');
            });
        } else {
            playError();
            Swal.fire({
                icon: 'error',
                title: t('delete.pages.alerts.error.title'),
                text: data.message || t('delete.pages.alerts.error.generic'),
                footer: t('delete.pages.alerts.error.footer'),
                confirmButtonText: t('delete.pages.alerts.error.confirmButtonText'),
                showConfirmButton: true,
            });
        }
    } catch (error) {
        console.error('Error deleting server:', error);
        playError();
        Swal.fire({
            icon: 'error',
            title: t('delete.pages.alerts.error.title'),
            text: t('delete.pages.alerts.error.generic') + ' ' + error,
            footer: t('delete.pages.alerts.error.footer'),
            confirmButtonText: t('delete.pages.alerts.error.confirmButtonText'),
            showConfirmButton: true,
        });
    } finally {
        isSubmitting.value = false;
    }
};
</script>
