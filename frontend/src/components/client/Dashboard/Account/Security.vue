<script setup lang="ts">
import { ref, onMounted } from 'vue';
import Button from '@/components/client/ui/Button.vue';
import { useRouter } from 'vue-router';
import Session from '@/mythicaldash/Session';
import { useI18n } from 'vue-i18n';
import { MythicalDOM } from '@/mythicaldash/MythicalDOM';
import {
    Shield as ShieldIcon,
    Key as KeyIcon,
    Smartphone as SmartphoneIcon,
    AlertTriangle as AlertTriangleIcon,
    CheckCircle as CheckCircleIcon,
    XCircle as XCircleIcon,
    Lock as LockIcon,
    LogOut as LogOutIcon,
} from 'lucide-vue-next';

const router = useRouter();
const { t } = useI18n();

const isLoading = ref(true);
const is2FAEnabled = Session.getInfo('2fa_enabled') === 'true' ? ref(true) : ref(false);
const lastPasswordChange = ref('2023-11-15T14:30:00Z'); // Mock data

MythicalDOM.setPageTitle(t('account.pages.security.page.title'));

onMounted(() => {
    // Simulate loading
    setTimeout(() => {
        isLoading.value = false;
    }, 1000);
});

const enable2FA = () => {
    // Add logic to enable 2FA
    is2FAEnabled.value = true;
    router.push('/auth/2fa/setup');
};

const disable2FA = () => {
    // Add logic to disable 2FA
    is2FAEnabled.value = false;
    router.push('/auth/2fa/setup/disband');
};

const changePassword = () => {
    router.push('/auth/forgot-password');
};

const logoutAllDevices = () => {
    router.push('/auth/logout');
};

const formatDate = (dateString: string): string => {
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(date);
};
</script>

<style scoped>
/* Smooth transitions */
.transition-colors {
    transition-property: background-color, border-color, color, fill, stroke;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 200ms;
}

/* Animation for status indicators */
@keyframes pulse {
    0%,
    100% {
        opacity: 0.8;
    }
    50% {
        opacity: 0.5;
    }
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>

<template>
    <div>
        <!-- Title and Description -->
        <div class="mb-6">
            <h2 class="text-xl font-semibold text-gray-100 mb-2">{{ t('account.pages.security.page.title') }}</h2>
            <p class="text-gray-400 text-sm">{{ t('account.pages.security.page.subTitle') }}</p>
        </div>

        <!-- Loading State -->
        <div v-if="isLoading" class="space-y-4">
            <div v-for="i in 5" :key="i" class="bg-[#1a1a2e]/30 rounded-lg p-4 animate-pulse">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-lg bg-[#1a1a2e]/50"></div>
                    <div class="flex-1">
                        <div class="h-5 w-32 bg-[#1a1a2e]/50 rounded mb-2"></div>
                        <div class="h-4 w-24 bg-[#1a1a2e]/50 rounded"></div>
                    </div>
                    <div class="w-20 h-8 bg-[#1a1a2e]/50 rounded-lg"></div>
                </div>
            </div>
        </div>

        <div v-else class="space-y-6">
            <!-- Security Status Overview -->
            <div class="bg-[#12121f]/50 border border-[#2a2a3f]/30 rounded-xl p-5 shadow-lg">
                <div class="mb-4">
                    <h3 class="text-lg font-medium text-gray-200 flex items-center gap-2">
                        <ShieldIcon class="h-5 w-5 text-indigo-400" />
                        {{ t('account.pages.security.page.securityStatus.title') }}
                    </h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Password Status -->
                    <div class="bg-[#0a0a15]/50 border border-[#2a2a3f]/30 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <KeyIcon class="h-4 w-4 text-indigo-400" />
                                <span class="text-sm font-medium text-gray-300">{{
                                    t('account.pages.security.page.securityStatus.password.title')
                                }}</span>
                            </div>
                            <div class="flex items-center gap-1 text-green-400">
                                <CheckCircleIcon class="h-4 w-4" />
                                <span class="text-xs">{{
                                    t('account.pages.security.page.securityStatus.password.active')
                                }}</span>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mb-3">
                            {{ t('account.pages.security.page.securityStatus.password.lastChanged') }}
                            {{ formatDate(lastPasswordChange) }}
                        </p>
                        <Button @click="changePassword" variant="secondary" small class="w-full">
                            {{ t('account.pages.security.page.securityStatus.password.changeButton.label') }}
                        </Button>
                    </div>

                    <!-- 2FA Status -->
                    <div class="bg-[#0a0a15]/50 border border-[#2a2a3f]/30 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <SmartphoneIcon class="h-4 w-4 text-indigo-400" />
                                <span class="text-sm font-medium text-gray-300">{{
                                    t('account.pages.security.page.securityStatus.twofactor.title')
                                }}</span>
                            </div>
                            <div v-if="is2FAEnabled" class="flex items-center gap-1 text-green-400">
                                <CheckCircleIcon class="h-4 w-4" />
                                <span class="text-xs">{{
                                    t('account.pages.security.page.securityStatus.twofactor.active')
                                }}</span>
                            </div>
                            <div v-else class="flex items-center gap-1 text-red-400">
                                <XCircleIcon class="h-4 w-4" />
                                <span class="text-xs">{{
                                    t('account.pages.security.page.securityStatus.twofactor.disabled')
                                }}</span>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mb-3">
                            {{
                                is2FAEnabled
                                    ? t('account.pages.security.page.securityStatus.twofactor.description.enabled')
                                    : t('account.pages.security.page.securityStatus.twofactor.description.disabled')
                            }}
                        </p>
                        <Button v-if="is2FAEnabled" @click="disable2FA" variant="danger" small class="w-full">
                            {{ t('account.pages.security.page.securityStatus.twofactor.button.disable') }}
                        </Button>
                        <Button v-else @click="enable2FA" variant="primary" small class="w-full">
                            {{ t('account.pages.security.page.securityStatus.twofactor.button.enable') }}
                        </Button>
                    </div>

                    <!-- Session Status -->
                    <div class="bg-[#0a0a15]/50 border border-[#2a2a3f]/30 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <LockIcon class="h-4 w-4 text-indigo-400" />
                                <span class="text-sm font-medium text-gray-300">{{
                                    t('account.pages.security.page.securityStatus.activeSessions.title')
                                }}</span>
                            </div>
                            <div class="flex items-center gap-1 text-indigo-400">
                                <span class="text-xs font-medium">{{
                                    t('account.pages.security.page.securityStatus.activeSessions.active')
                                }}</span>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mb-3">
                            {{ t('account.pages.security.page.securityStatus.activeSessions.description') }}
                        </p>
                        <Button
                            @click="logoutAllDevices"
                            variant="danger"
                            small
                            class="w-full flex items-center justify-center gap-1"
                        >
                            <LogOutIcon class="h-3 w-3" />
                            {{ t('account.pages.security.page.securityStatus.activeSessions.button.label') }}
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Security Recommendations -->
            <div class="bg-[#12121f]/50 border border-[#2a2a3f]/30 rounded-xl p-5 shadow-lg">
                <div class="mb-4">
                    <h3 class="text-lg font-medium text-gray-200 flex items-center gap-2">
                        <ShieldIcon class="h-5 w-5 text-indigo-400" />
                        {{ t('account.pages.security.page.recommendations.title') }}
                    </h3>
                    <p class="text-sm text-gray-400 mt-1">
                        {{ t('account.pages.security.page.recommendations.description') }}
                    </p>
                </div>

                <div class="space-y-3">
                    <div class="flex items-start gap-3 p-3 rounded-lg" :class="{ 'bg-[#0a0a15]/50': !is2FAEnabled }">
                        <div class="w-8 h-8 rounded-lg bg-indigo-500/10 flex items-center justify-center shrink-0">
                            <SmartphoneIcon class="h-4 w-4 text-indigo-400" />
                        </div>
                        <div class="flex-1">
                            <h4 class="text-sm font-medium text-gray-300">
                                {{ t('account.pages.security.page.recommendations.twofactor.enabled.title') }}
                            </h4>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ t('account.pages.security.page.recommendations.twofactor.enabled.description') }}
                            </p>
                            <Button v-if="!is2FAEnabled" @click="enable2FA" variant="primary" small class="mt-2">
                                {{ t('account.pages.security.page.recommendations.twofactor.enabled.button.label') }}
                            </Button>
                        </div>
                        <div v-if="is2FAEnabled" class="shrink-0">
                            <CheckCircleIcon class="h-5 w-5 text-green-400" />
                        </div>
                    </div>

                    <div class="flex items-start gap-3 p-3 rounded-lg">
                        <div class="w-8 h-8 rounded-lg bg-indigo-500/10 flex items-center justify-center shrink-0">
                            <KeyIcon class="h-4 w-4 text-indigo-400" />
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-300">
                                {{ t('account.pages.security.page.recommendations.password.title') }}
                            </h4>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ t('account.pages.security.page.recommendations.password.description') }}
                            </p>
                        </div>
                        <div class="shrink-0">
                            <CheckCircleIcon class="h-5 w-5 text-green-400" />
                        </div>
                    </div>

                    <div class="flex items-start gap-3 p-3 rounded-lg">
                        <div class="w-8 h-8 rounded-lg bg-indigo-500/10 flex items-center justify-center shrink-0">
                            <AlertTriangleIcon class="h-4 w-4 text-indigo-400" />
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-gray-300">
                                {{ t('account.pages.security.page.recommendations.monitor.title') }}
                            </h4>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ t('account.pages.security.page.recommendations.monitor.description') }}
                            </p>
                        </div>
                        <div class="shrink-0">
                            <CheckCircleIcon class="h-5 w-5 text-green-400" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Smooth transitions */
.transition-colors {
    transition-property: background-color, border-color, color, fill, stroke;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 200ms;
}

/* Animation for status indicators */
@keyframes pulse {
    0%,
    100% {
        opacity: 0.8;
    }
    50% {
        opacity: 0.5;
    }
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
