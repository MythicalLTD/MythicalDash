<script setup lang="ts">
import { ref, onMounted } from 'vue';
import Button from '@/components/client/ui/Button.vue';
import { useRouter } from 'vue-router';
import Session from '@/mythicaldash/Session';
import Auth from '@/mythicaldash/Auth';
import Swal from 'sweetalert2';
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
    X as XIcon,
    Eye as EyeIcon,
    EyeOff as EyeOffIcon,
} from 'lucide-vue-next';

const router = useRouter();
const { t } = useI18n();

const isLoading = ref(true);
const is2FAEnabled = Session.getInfo('2fa_enabled') === 'true' ? ref(true) : ref(false);
const lastPasswordChange = ref('2023-11-15T14:30:00Z'); // Mock data
const showPasswordModal = ref(false);
const isChangingPassword = ref(false);
const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

// Password change form
const passwordForm = ref({
    currentPassword: '',
    newPassword: '',
    confirmPassword: '',
});

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
    showPasswordModal.value = true;
};

const closePasswordModal = () => {
    showPasswordModal.value = false;
    passwordForm.value = {
        currentPassword: '',
        newPassword: '',
        confirmPassword: '',
    };
};

const handlePasswordChange = async () => {
    if (!passwordForm.value.currentPassword || !passwordForm.value.newPassword || !passwordForm.value.confirmPassword) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Please fill in all fields',
            showConfirmButton: true,
            background: '#12121f',
            color: '#e5e7eb',
            confirmButtonColor: '#6366f1',
        });
        return;
    }

    if (passwordForm.value.newPassword !== passwordForm.value.confirmPassword) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'New password and confirmation do not match',
            showConfirmButton: true,
            background: '#12121f',
            color: '#e5e7eb',
            confirmButtonColor: '#6366f1',
        });
        return;
    }

    if (passwordForm.value.newPassword.length < 8) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Password must be at least 8 characters long',
            showConfirmButton: true,
            background: '#12121f',
            color: '#e5e7eb',
            confirmButtonColor: '#6366f1',
        });
        return;
    }

    isChangingPassword.value = true;
    try {
        const response = await Auth.changePassword(
            passwordForm.value.currentPassword,
            passwordForm.value.newPassword,
            passwordForm.value.confirmPassword,
        );

        if (response.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Password changed successfully!',
                showConfirmButton: true,
                background: '#12121f',
                color: '#e5e7eb',
                confirmButtonColor: '#6366f1',
            });
            closePasswordModal();
        } else {
            let errorMessage = 'Failed to change password';
            if (response.error_code === 'INVALID_CURRENT_PASSWORD') {
                errorMessage = 'Current password is incorrect';
            } else if (response.error_code === 'PASSWORD_MISMATCH') {
                errorMessage = 'New password and confirmation do not match';
            } else if (response.error_code === 'PASSWORD_TOO_SHORT') {
                errorMessage = 'Password must be at least 8 characters long';
            } else if (response.message) {
                errorMessage = response.message;
            }

            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: errorMessage,
                showConfirmButton: true,
                background: '#12121f',
                color: '#e5e7eb',
                confirmButtonColor: '#6366f1',
            });
        }
    } catch (error) {
        console.error('Error changing password:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An unexpected error occurred',
            showConfirmButton: true,
            background: '#12121f',
            color: '#e5e7eb',
            confirmButtonColor: '#6366f1',
        });
    } finally {
        isChangingPassword.value = false;
    }
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

        <!-- Password Change Modal -->
        <div
            v-if="showPasswordModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
            @click.self="closePasswordModal"
        >
            <div
                class="bg-[#12121f] border border-[#2a2a3f]/30 rounded-xl p-6 w-full max-w-md mx-4 shadow-2xl"
                @click.stop
            >
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-semibold text-gray-100 flex items-center gap-2">
                        <KeyIcon class="h-5 w-5 text-indigo-400" />
                        Change Password
                    </h3>
                    <button
                        @click="closePasswordModal"
                        class="text-gray-400 hover:text-white transition-colors p-1 rounded-lg hover:bg-[#1a1a2e]"
                    >
                        <XIcon class="h-5 w-5" />
                    </button>
                </div>

                <form @submit.prevent="handlePasswordChange" class="space-y-4">
                    <!-- Current Password -->
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-1.5">Current Password</label>
                        <div class="relative">
                            <input
                                :type="showCurrentPassword ? 'text' : 'password'"
                                v-model="passwordForm.currentPassword"
                                name="currentPassword"
                                id="currentPassword"
                                placeholder="Enter your current password"
                                class="w-full bg-[#0a0a15]/50 border border-[#2a2a3f]/30 rounded-lg px-4 py-2.5 pl-10 pr-10 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors"
                            />
                            <LockIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                            <button
                                type="button"
                                @click="showCurrentPassword = !showCurrentPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-white transition-colors"
                            >
                                <EyeIcon v-if="showCurrentPassword" class="h-5 w-5" />
                                <EyeOffIcon v-else class="h-5 w-5" />
                            </button>
                        </div>
                    </div>

                    <!-- New Password -->
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-1.5">New Password</label>
                        <div class="relative">
                            <input
                                :type="showNewPassword ? 'text' : 'password'"
                                v-model="passwordForm.newPassword"
                                name="newPassword"
                                id="newPassword"
                                placeholder="Enter your new password"
                                class="w-full bg-[#0a0a15]/50 border border-[#2a2a3f]/30 rounded-lg px-4 py-2.5 pl-10 pr-10 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors"
                            />
                            <LockIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                            <button
                                type="button"
                                @click="showNewPassword = !showNewPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-white transition-colors"
                            >
                                <EyeIcon v-if="showNewPassword" class="h-5 w-5" />
                                <EyeOffIcon v-else class="h-5 w-5" />
                            </button>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Password must be at least 8 characters long</p>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-1.5">Confirm New Password</label>
                        <div class="relative">
                            <input
                                :type="showConfirmPassword ? 'text' : 'password'"
                                v-model="passwordForm.confirmPassword"
                                name="confirmPassword"
                                id="confirmPassword"
                                placeholder="Confirm your new password"
                                class="w-full bg-[#0a0a15]/50 border border-[#2a2a3f]/30 rounded-lg px-4 py-2.5 pl-10 pr-10 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors"
                            />
                            <LockIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                            <button
                                type="button"
                                @click="showConfirmPassword = !showConfirmPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-white transition-colors"
                            >
                                <EyeIcon v-if="showConfirmPassword" class="h-5 w-5" />
                                <EyeOffIcon v-else class="h-5 w-5" />
                            </button>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex gap-3 pt-4">
                        <Button
                            type="button"
                            @click="closePasswordModal"
                            variant="secondary"
                            class="flex-1"
                            :disabled="isChangingPassword"
                        >
                            Cancel
                        </Button>
                        <Button type="submit" variant="primary" class="flex-1" :loading="isChangingPassword">
                            Change Password
                        </Button>
                    </div>
                </form>
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
