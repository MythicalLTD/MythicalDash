<script setup lang="ts">
import { reactive, ref, onMounted, computed } from 'vue';
import TextInput from '@/components/client/ui/TextForms/TextInput.vue';
import Button from '@/components/client/ui/Button.vue';
import LoadingAnimation from '@/components/client/ui/LoadingAnimation.vue';
import Session from '@/mythicaldash/Session';
import { useI18n } from 'vue-i18n';
import Swal from 'sweetalert2';
import Auth from '@/mythicaldash/Auth';
import { MythicalDOM } from '@/mythicaldash/MythicalDOM';
import {
    User as UserIcon,
    Mail as MailIcon,
    Image as ImageIcon,
    Database as DatabaseIcon,
    Trash2 as TrashIcon,
    Save as SaveIcon,
    RefreshCw as RefreshIcon,
    AlertTriangle as AlertIcon,
} from 'lucide-vue-next';

const { t } = useI18n();
MythicalDOM.setPageTitle(t('account.pages.settings.page.title'));

const isLoading = ref(true);
const isSaving = ref(false);
const isResetting = ref(false);
const isClearing = ref(false);

const form = reactive({
    firstName: Session.getInfo('first_name'),
    lastName: Session.getInfo('last_name'),
    email: Session.getInfo('email'),
    avatar: Session.getInfo('avatar'),
    background: Session.getInfo('background'),
});

// Original values for comparison
const originalValues = {
    firstName: Session.getInfo('first_name'),
    lastName: Session.getInfo('last_name'),
    email: Session.getInfo('email'),
    avatar: Session.getInfo('avatar'),
    background: Session.getInfo('background'),
};

const hasChanges = computed(() => {
    return (
        form.firstName !== originalValues.firstName ||
        form.lastName !== originalValues.lastName ||
        form.email !== originalValues.email ||
        form.avatar !== originalValues.avatar ||
        form.background !== originalValues.background
    );
});

onMounted(() => {
    // Simulate loading delay
    setTimeout(() => {
        isLoading.value = false;
    }, 800);
});

const saveChanges = async () => {
    if (!hasChanges.value) return;

    isSaving.value = true;
    try {
        const response = await Auth.updateUserInfo(
            form.firstName,
            form.lastName,
            form.email,
            form.avatar,
            form.background,
        );

        if (response.success) {
            console.log('Account updated successfully');
            const title = t('account.pages.settings.alerts.success.title');
            const text = t('account.pages.settings.alerts.success.update_success');
            const footer = t('account.pages.settings.alerts.success.footer');
            Swal.fire({
                icon: 'success',
                title: title,
                text: text,
                footer: footer,
                showConfirmButton: true,
                background: '#12121f',
                color: '#e5e7eb',
                confirmButtonColor: '#6366f1',
            });

            // Update original values
            originalValues.firstName = form.firstName;
            originalValues.lastName = form.lastName;
            originalValues.email = form.email;
            originalValues.avatar = form.avatar;
            originalValues.background = form.background;
        } else {
            if (response.error_code == 'EMAIL_EXISTS') {
                const title = t('account.pages.settings.alerts.error.title');
                const text = t('account.pages.settings.alerts.error.email');
                const footer = t('account.pages.settings.alerts.error.footer');
                Swal.fire({
                    icon: 'error',
                    title: title,
                    text: text,
                    footer: footer,
                    showConfirmButton: true,
                    background: '#12121f',
                    color: '#e5e7eb',
                    confirmButtonColor: '#6366f1',
                });
                console.error('Error updating account:', response.error);
            } else {
                const title = t('account.pages.settings.alerts.error.title');
                const text = t('account.pages.settings.alerts.error.generic');
                const footer = t('account.pages.settings.alerts.error.footer');
                Swal.fire({
                    icon: 'error',
                    title: title,
                    text: text,
                    footer: footer,
                    showConfirmButton: true,
                    background: '#12121f',
                    color: '#e5e7eb',
                    confirmButtonColor: '#6366f1',
                });
                console.error('Error updating account:', response.error);
            }
        }
    } catch (error) {
        const title = t('account.pages.settings.alerts.error.title');
        const text = t('account.pages.settings.alerts.error.generic');
        const footer = t('account.pages.settings.alerts.error.footer');
        Swal.fire({
            icon: 'error',
            title: title,
            text: text,
            footer: footer,
            showConfirmButton: true,
            background: '#12121f',
            color: '#e5e7eb',
            confirmButtonColor: '#6366f1',
        });
        console.error('Error updating account:', error);
    } finally {
        isSaving.value = false;
    }
};

const resetFields = async () => {
    isResetting.value = true;
    setTimeout(() => {
        form.firstName = Session.getInfo('first_name');
        form.lastName = Session.getInfo('last_name');
        form.email = Session.getInfo('email');
        form.avatar = Session.getInfo('avatar');
        form.background = Session.getInfo('background');
        isResetting.value = false;
    }, 500);
};

const formatBytes = (bytes: number): string => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const getTotalStorageSize = (): number => {
    try {
        let total = 0;

        // Calculate localStorage size
        try {
            for (let i = 0; i < localStorage.length; i++) {
                const key = localStorage.key(i);
                if (key) {
                    const value = localStorage.getItem(key);
                    total += new Blob([value || '']).size;
                }
            }
        } catch {
            return 0;
        }

        // Calculate sessionStorage size
        try {
            for (let i = 0; i < sessionStorage.length; i++) {
                const key = sessionStorage.key(i);
                if (key) {
                    const value = sessionStorage.getItem(key);
                    total += new Blob([value || '']).size;
                }
            }
        } catch {
            return 0;
        }

        return total;
    } catch {
        return 0;
    }
};

const clearAllData = async () => {
    const result = await Swal.fire({
        title: t('account.pages.settings.page.clear.cache.title'),
        text: t('account.pages.settings.page.clear.cache.description'),
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: t('account.pages.settings.page.clear.cache.confirm'),
        cancelButtonText: t('account.pages.settings.page.clear.cache.cancel'),
        confirmButtonColor: '#dc2626',
        background: '#12121f',
        color: '#e5e7eb',
    });

    if (result.isConfirmed) {
        isClearing.value = true;
        setTimeout(() => {
            localStorage.clear();
            sessionStorage.clear();
            window.location.reload();
        }, 1000);
    }
};

const totalSize = ref(getTotalStorageSize());

const previewAvatar = computed(() => {
    return (
        form.avatar ||
        'https://ui-avatars.com/api/?name=' + form.firstName + '+' + form.lastName + '&background=6366f1&color=fff'
    );
});

const previewBackground = computed(() => {
    return (
        form.background ||
        'https://images.unsplash.com/photo-1534972195531-d756b9bfa9f2?q=80&w=1000&auto=format&fit=crop'
    );
});
</script>

<template>
    <div>
        <!-- Title and Description -->
        <div class="mb-6">
            <h2 class="text-xl font-semibold text-gray-100 mb-2">{{ $t('account.pages.settings.page.title') }}</h2>
            <p class="text-gray-400 text-sm">{{ $t('account.pages.settings.page.subTitle') }}</p>
        </div>

        <!-- Loading State -->
        <div v-if="isLoading" class="py-8">
            <LoadingAnimation
                loadingText="Loading account settings"
                description="Please wait while we fetch your account information"
            />
        </div>

        <div v-else class="space-y-6">
            <!-- Profile Information -->
            <div class="bg-[#12121f]/50 border border-[#2a2a3f]/30 rounded-xl p-5 shadow-lg">
                <div class="mb-4">
                    <h3 class="text-lg font-medium text-gray-200 flex items-center gap-2">
                        <UserIcon class="h-5 w-5 text-indigo-400" />
                        {{ $t('account.pages.settings.page.form.profile.title') }}
                    </h3>
                    <p class="text-sm text-gray-400 mt-1">
                        {{ $t('account.pages.settings.page.form.profile.subTitle') }}
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Left Column: Form Fields -->
                    <div class="space-y-4">
                        <!-- First Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1.5">
                                {{ t('account.pages.settings.page.form.firstName.label') }}
                            </label>
                            <TextInput v-model="form.firstName" name="firstName" id="firstName" :icon="UserIcon" />
                        </div>

                        <!-- Last Name -->
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1.5">
                                {{ t('account.pages.settings.page.form.lastName.label') }}
                            </label>
                            <TextInput v-model="form.lastName" name="lastName" id="lastName" :icon="UserIcon" />
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1.5">
                                {{ t('account.pages.settings.page.form.email.label') }}
                            </label>
                            <TextInput type="email" v-model="form.email" name="email" id="email" :icon="MailIcon" />
                        </div>
                    </div>

                    <!-- Right Column: Avatar & Background -->
                    <div class="space-y-4">
                        <!-- Avatar Preview -->
                        <div
                            class="flex flex-col items-center p-4 bg-[#0a0a15]/50 border border-[#2a2a3f]/30 rounded-lg"
                        >
                            <div class="relative mb-3">
                                <img
                                    :src="previewAvatar"
                                    alt="Avatar Preview"
                                    class="w-20 h-20 rounded-lg object-cover border-2 border-indigo-500/20"
                                />
                            </div>

                            <div class="w-full">
                                <label class="block text-sm font-medium text-gray-400 mb-1.5">
                                    {{ t('account.pages.settings.page.form.avatar.label') }}
                                </label>
                                <TextInput
                                    type="url"
                                    v-model="form.avatar"
                                    name="avatar"
                                    id="avatar"
                                    :icon="ImageIcon"
                                    placeholder="https://example.com/avatar.jpg"
                                />
                            </div>
                        </div>

                        <!-- Background URL -->
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-1.5">
                                {{ t('account.pages.settings.page.form.background.label') }}
                            </label>
                            <TextInput
                                type="url"
                                v-model="form.background"
                                name="background"
                                id="background"
                                :icon="ImageIcon"
                                placeholder="https://example.com/background.jpg"
                            />

                            <!-- Background Preview -->
                            <div class="mt-2 h-16 rounded-lg overflow-hidden">
                                <img
                                    :src="previewBackground"
                                    alt="Background Preview"
                                    class="w-full h-full object-cover"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex flex-wrap gap-3 mt-6">
                    <Button
                        @click="saveChanges"
                        variant="primary"
                        :disabled="!hasChanges"
                        :loading="isSaving"
                        class="flex items-center gap-2"
                    >
                        <SaveIcon class="h-4 w-4" />
                        {{ t('account.pages.settings.page.form.update_button.label') }}
                    </Button>

                    <Button
                        @click="resetFields"
                        variant="secondary"
                        :disabled="!hasChanges"
                        :loading="isResetting"
                        class="flex items-center gap-2"
                    >
                        <RefreshIcon class="h-4 w-4" />
                        {{ t('account.pages.settings.page.form.update_button.reset') }}
                    </Button>
                </div>
            </div>

            <!-- Browser Storage -->
            <div class="bg-[#12121f]/50 border border-[#2a2a3f]/30 rounded-xl p-5 shadow-lg">
                <div class="mb-4">
                    <h3 class="text-lg font-medium text-gray-200 flex items-center gap-2">
                        <DatabaseIcon class="h-5 w-5 text-indigo-400" />
                        {{ $t('account.pages.settings.page.form.browserStorage.title') }}
                    </h3>
                    <p class="text-sm text-gray-400 mt-1">
                        {{ $t('account.pages.settings.page.form.browserStorage.subTitle') }}
                    </p>
                </div>

                <div class="bg-[#0a0a15]/50 border border-[#2a2a3f]/30 rounded-lg p-4 mb-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-medium text-gray-300">
                                {{ $t('account.pages.settings.page.form.browserStorage.currentStorageUsage') }}
                            </h4>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $t('account.pages.settings.page.form.browserStorage.totalBrowserStorage') }}
                            </p>
                        </div>
                        <div class="text-right">
                            <span class="text-lg font-semibold text-indigo-400">{{ formatBytes(totalSize) }}</span>
                        </div>
                    </div>

                    <!-- Storage Bar -->
                    <div class="mt-3 h-2 bg-[#1a1a2e]/50 rounded-full overflow-hidden">
                        <div
                            class="h-full bg-indigo-500 rounded-full"
                            :style="{ width: `${Math.min((totalSize / 5242880) * 100, 100)}%` }"
                        ></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                        {{ $t('account.pages.settings.page.form.browserStorage.browserStorageDescription') }}
                    </p>
                </div>

                <div class="flex items-start gap-3 p-4 bg-[#0a0a15]/50 border border-red-500/20 rounded-lg">
                    <div class="p-2 rounded-lg bg-red-500/10 shrink-0">
                        <AlertIcon class="h-5 w-5 text-red-400" />
                    </div>
                    <div class="flex-1">
                        <h4 class="text-sm font-medium text-gray-300">
                            {{ $t('account.pages.settings.page.form.browserStorage.clearAllBrowserData') }}
                        </h4>
                        <p class="text-xs text-gray-500 mt-1 mb-3">
                            {{ $t('account.pages.settings.page.form.browserStorage.clearAllBrowserDataDescription') }}
                        </p>
                        <Button
                            @click="clearAllData"
                            variant="danger"
                            small
                            :loading="isClearing"
                            class="flex items-center gap-2"
                        >
                            <TrashIcon class="h-3.5 w-3.5" />
                            {{ t('account.pages.settings.page.form.browserStorage.clearAllBrowserDataButton') }}
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Account Deletion -->
            <div class="bg-[#12121f]/50 border border-red-500/20 rounded-xl p-5 shadow-lg">
                <div class="mb-4">
                    <h3 class="text-lg font-medium text-red-400 flex items-center gap-2">
                        <TrashIcon class="h-5 w-5" />
                        {{ t('account.pages.settings.page.delete.title') }}
                    </h3>
                    <p class="text-sm text-gray-400 mt-1">
                        {{ t('account.pages.settings.page.delete.description') }}
                    </p>
                </div>

                <div class="bg-red-500/10 border border-red-500/20 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <div class="p-2 rounded-lg bg-red-500/10 shrink-0">
                            <AlertIcon class="h-5 w-5 text-red-400" />
                        </div>
                        <div>
                            <h4 class="text-sm font-medium text-red-400">
                                {{ t('account.pages.settings.page.delete.warning.title') }}
                            </h4>
                            <p class="text-xs text-gray-400 mt-1 mb-3">
                                {{ t('account.pages.settings.page.delete.warning.description') }}
                            </p>
                            <Button variant="danger" small>
                                {{ t('account.pages.settings.page.delete.button') }}
                            </Button>
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
