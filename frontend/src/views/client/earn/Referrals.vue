<template>
    <LayoutDashboard>
        <div class="p-6">
            <h1 class="text-2xl font-bold text-white mb-6">{{ t('referrals.pages.index.title') }}</h1>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Referrals Content -->
                <div class="lg:col-span-2">
                    <CardComponent
                        cardTitle="Your Referral Link"
                        cardDescription="Share and earn rewards when friends join"
                    >
                        <div class="relative overflow-hidden">
                            <!-- Background decorative elements -->
                            <div
                                class="absolute -top-20 -right-20 w-40 h-40 bg-indigo-500/5 rounded-full blur-2xl"
                            ></div>
                            <div
                                class="absolute -bottom-20 -left-20 w-40 h-40 bg-purple-500/5 rounded-full blur-2xl"
                            ></div>

                            <div class="relative z-10 p-6">
                                <div class="bg-gray-800/50 rounded-xl p-6 border border-gray-700/30 mb-6">
                                    <h3 class="text-lg font-semibold text-white mb-2">
                                        {{ t('referrals.pages.index.body.title') }}
                                    </h3>
                                    <p class="text-sm text-gray-400 mb-4">
                                        {{ t('referrals.pages.index.body.description') }}
                                    </p>

                                    <div class="flex items-center">
                                        <div
                                            class="flex-1 bg-gray-900/70 rounded-l-lg px-4 py-3 text-gray-300 break-all"
                                        >
                                            {{ referralCode }}
                                        </div>
                                        <button
                                            @click="copyReferralLink"
                                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-3 rounded-r-lg flex items-center transition-colors duration-200"
                                        >
                                            <span v-if="copied">{{ t('referrals.pages.index.body.copy.copied') }}</span>
                                            <div v-else class="flex items-center">
                                                <CopyIcon class="h-4 w-4 mr-2" />
                                                {{ t('referrals.pages.index.body.copy.title') }}
                                            </div>
                                        </button>
                                    </div>

                                    <div class="flex flex-wrap gap-3 mt-5">
                                        <button
                                            @click="shareVia('twitter')"
                                            class="flex items-center gap-2 bg-[#1DA1F2]/10 hover:bg-[#1DA1F2]/20 text-[#1DA1F2] px-4 py-2 rounded-lg text-sm transition-colors duration-200"
                                        >
                                            <TwitterIcon class="h-4 w-4" />
                                            {{ t('referrals.pages.index.social.twitter') }}
                                        </button>
                                        <button
                                            @click="shareVia('facebook')"
                                            class="flex items-center gap-2 bg-[#1877F2]/10 hover:bg-[#1877F2]/20 text-[#1877F2] px-4 py-2 rounded-lg text-sm transition-colors duration-200"
                                        >
                                            <FacebookIcon class="h-4 w-4" />
                                            {{ t('referrals.pages.index.social.facebook') }}
                                        </button>
                                        <button
                                            @click="shareVia('discord')"
                                            class="flex items-center gap-2 bg-[#5865F2]/10 hover:bg-[#5865F2]/20 text-[#5865F2] px-4 py-2 rounded-lg text-sm transition-colors duration-200"
                                        >
                                            <DiscordIcon class="h-4 w-4" />
                                            {{ t('referrals.pages.index.social.discord') }}
                                        </button>
                                        <button
                                            @click="shareVia('email')"
                                            class="flex items-center gap-2 bg-gray-700/30 hover:bg-gray-700/50 text-gray-300 px-4 py-2 rounded-lg text-sm transition-colors duration-200"
                                        >
                                            <MailIcon class="h-4 w-4" />
                                            {{ t('referrals.pages.index.social.email') }}
                                        </button>
                                    </div>
                                </div>

                                <!-- Referral Stats -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                    <div class="bg-gray-800/30 rounded-xl p-4 border border-gray-700/20">
                                        <h4 class="text-gray-400 text-sm mb-1">
                                            {{ t('referrals.pages.index.refferals.title') }}
                                        </h4>
                                        <div class="flex items-center">
                                            <UsersIcon class="h-5 w-5 text-indigo-400 mr-2" />
                                            <span class="text-2xl font-bold text-white">{{
                                                stats.totalReferrals
                                            }}</span>
                                        </div>
                                    </div>

                                    <div class="bg-gray-800/30 rounded-xl p-4 border border-gray-700/20">
                                        <h4 class="text-gray-400 text-sm mb-1">
                                            {{ t('referrals.pages.index.refferals.earned') }}
                                        </h4>
                                        <div class="flex items-center">
                                            <Coins class="h-5 w-5 text-yellow-500 mr-2" />
                                            <span class="text-2xl font-bold text-white">{{ stats.totalEarned }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Referrals List -->
                                <h3 class="text-lg font-semibold text-white mb-4">
                                    {{ t('referrals.pages.index.refferals.yourReferrals') }}
                                </h3>

                                <div v-if="isLoading" class="py-10 flex flex-col items-center justify-center">
                                    <LoaderIcon class="w-12 h-12 text-indigo-500 animate-spin mb-3" />
                                    <p class="text-gray-400">{{ t('referrals.pages.index.refferals.loading') }}</p>
                                </div>

                                <div
                                    v-else-if="referrals.length === 0"
                                    class="bg-gray-800/30 rounded-xl p-8 border border-gray-700/20 flex flex-col items-center justify-center"
                                >
                                    <UsersIcon class="h-12 w-12 text-gray-600 mb-3" />
                                    <h4 class="text-lg font-medium text-white mb-1">
                                        {{ t('referrals.pages.index.refferals.noReferrals') }}
                                    </h4>
                                    <p class="text-gray-400 text-center max-w-md">
                                        {{ t('referrals.pages.index.refferals.noReferralsDescription') }}
                                    </p>
                                </div>

                                <div v-else class="bg-gray-800/20 rounded-xl overflow-hidden border border-gray-700/20">
                                    <div class="overflow-x-auto">
                                        <table class="w-full">
                                            <thead>
                                                <tr class="bg-gray-800/50 border-b border-gray-700/30">
                                                    <th
                                                        class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider"
                                                    >
                                                        {{ t('referrals.pages.index.refferals.table.user') }}
                                                    </th>
                                                    <th
                                                        class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider"
                                                    >
                                                        {{ t('referrals.pages.index.refferals.table.date') }}
                                                    </th>
                                                    <th
                                                        class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider"
                                                    >
                                                        {{ t('referrals.pages.index.refferals.table.status') }}
                                                    </th>
                                                    <th
                                                        class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider"
                                                    >
                                                        {{ t('referrals.pages.index.refferals.table.reward') }}
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr
                                                    v-for="referral in referrals"
                                                    :key="referral.id"
                                                    class="border-b border-gray-800"
                                                >
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <div class="flex items-center">
                                                            <img
                                                                v-if="referral.user.avatar"
                                                                :src="referral.user.avatar"
                                                                :alt="referral.user.username"
                                                                class="h-8 w-8 rounded-full mr-3"
                                                            />
                                                            <div
                                                                v-else
                                                                class="h-8 w-8 rounded-full bg-indigo-500/20 flex items-center justify-center text-indigo-400 mr-3"
                                                            >
                                                                {{ referral.user.username.charAt(0).toUpperCase() }}
                                                            </div>
                                                            <div class="text-sm font-medium text-white">
                                                                {{ referral.user.username }}
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                                                        {{ formatDate(referral.created_at) }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <span
                                                            :class="[
                                                                'px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full',
                                                                referral.deleted === 'false'
                                                                    ? 'bg-green-900/30 text-green-400'
                                                                    : 'bg-red-900/30 text-red-400',
                                                            ]"
                                                        >
                                                            {{ referral.deleted === 'false' ? 'Active' : 'Deleted' }}
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <div class="flex items-center text-sm">
                                                            <Coins class="h-3 w-3 text-yellow-500 mr-1" />
                                                            <span class="text-white">
                                                                {{
                                                                    Settings.getSetting(
                                                                        'referrals_coins_per_referral',
                                                                    ) || '35'
                                                                }}
                                                            </span>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardComponent>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- How it Works Card -->
                    <CardComponent
                        :cardTitle="t('referrals.pages.index.refferals.sharelink.title')"
                        :cardDescription="t('referrals.pages.index.refferals.sharelink.description')"
                    >
                        <div class="p-4 space-y-4">
                            <div class="bg-gray-800/30 p-4 rounded-lg">
                                <div class="flex items-start mb-2">
                                    <div
                                        class="bg-indigo-900/50 text-indigo-400 w-5 h-5 rounded-full flex items-center justify-center mr-2 flex-shrink-0"
                                    >
                                        <span class="text-xs">{{
                                            t('referrals.pages.index.refferals.sharelink.steps.one.id')
                                        }}</span>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-white mb-1">
                                            {{ t('referrals.pages.index.refferals.sharelink.steps.one.title') }}
                                        </h4>
                                        <p class="text-xs text-gray-400">
                                            {{ t('referrals.pages.index.refferals.sharelink.steps.one.description') }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-800/30 p-4 rounded-lg">
                                <div class="flex items-start mb-2">
                                    <div
                                        class="bg-indigo-900/50 text-indigo-400 w-5 h-5 rounded-full flex items-center justify-center mr-2 flex-shrink-0"
                                    >
                                        <span class="text-xs">{{
                                            t('referrals.pages.index.refferals.sharelink.steps.two.id')
                                        }}</span>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-white mb-1">
                                            {{ t('referrals.pages.index.refferals.sharelink.steps.two.title') }}
                                        </h4>
                                        <p class="text-xs text-gray-400">
                                            {{ t('referrals.pages.index.refferals.sharelink.steps.two.description') }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-800/30 p-4 rounded-lg">
                                <div class="flex items-start mb-2">
                                    <div
                                        class="bg-indigo-900/50 text-indigo-400 w-5 h-5 rounded-full flex items-center justify-center mr-2 flex-shrink-0"
                                    >
                                        <span class="text-xs">{{
                                            t('referrals.pages.index.refferals.sharelink.steps.three.id')
                                        }}</span>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-white mb-1">
                                            {{ t('referrals.pages.index.refferals.sharelink.steps.three.title') }}
                                        </h4>
                                        <p class="text-xs text-gray-400">
                                            {{ t('referrals.pages.index.refferals.sharelink.steps.three.description') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardComponent>
                </div>
            </div>
        </div>
    </LayoutDashboard>
</template>

<script setup lang="ts">
import LayoutDashboard from '@/components/client/LayoutDashboard.vue';
import CardComponent from '@/components/client/ui/Card/CardComponent.vue';
import { ref, onMounted, computed } from 'vue';
import { Loader as LoaderIcon, Copy as CopyIcon, Users as UsersIcon, Mail as MailIcon, Coins } from 'lucide-vue-next';
import { useSettingsStore } from '@/stores/settings';
import router from '@/router';
import Swal from 'sweetalert2';
import { MythicalDOM } from '@/mythicaldash/MythicalDOM';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

MythicalDOM.setPageTitle(t('referrals.pages.index.title'));

// Custom icons for social platforms
const TwitterIcon = {
    template: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="1em" height="1em"><path d="M22 4s-.7 2.1-2 3.4c1.6 10-9.4 17.3-18 11.6 2.2.1 4.4-.6 6-2C3 15.5.5 9.6 3 5c2.2 2.6 5.6 4.1 9 4-.9-4.2 4-6.6 7-3.8 1.1 0 3-1.2 3-1.2z"></path></svg>`,
};

const FacebookIcon = {
    template: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="1em" height="1em"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>`,
};

const DiscordIcon = {
    template: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="1em" height="1em"><path d="M9 11.2a.8.8 0 1 0 0-1.6.8.8 0 0 0 0 1.6z"></path><path d="M15 11.2a.8.8 0 1 0 0-1.6.8.8 0 0 0 0 1.6z"></path><path d="M8.3 15c.3.7 1.1 1.5 2.7 1.5s2.4-.8 2.7-1.5"></path><path d="M20.1 10.7a16.3 16.3 0 0 0-3.8-7.2.3.3 0 0 0-.4-.1c-.7.3-1.4.8-2 1.3a18 18 0 0 0-10 0 12.3 12.3 0 0 0-2-1.3.3.3 0 0 0-.4.1 16.3 16.3 0 0 0-3.8 7.2l.1.2A16.5 16.5 0 0 0 5.5 19l.2.2a.3.3 0 0 0 .3 0c1.8-1.4 3.4-3 4.5-5a.3.3 0 0 0-.2-.4 9.5 9.5 0 0 1-1.8-1l.1-.2a13.3 13.3 0 0 0 5.5 0l.1.2a9.8 9.8 0 0 1-1.8 1 .3.3 0 0 0-.2.4 20.6 20.6 0 0 0 4.5 5 .3.3 0 0 0 .3 0l.2-.2a16.5 16.5 0 0 0 2.9-8.3l.1-.2z"></path></svg>`,
};

const Settings = useSettingsStore();

// Check if Referrals are enabled
if (Settings.getSetting('referrals_enabled') === 'false') {
    Swal.fire({
        title: t('referrals.notEnabled.title'),
        text: t('referrals.notEnabled.text'),
        icon: 'error',
        confirmButtonText: t('referrals.notEnabled.button'),
    });
    router.push('/dashboard');
}

// Define interfaces for our data structures
interface ReferralUser {
    username: string;
    avatar: string;
    uuid: string;
}

interface Referral {
    id: number;
    referral_code_id: number;
    deleted: string;
    updated_at: string;
    created_at: string;
    user: ReferralUser;
}

interface ReferralStats {
    totalReferrals: number;
    pendingReferrals: number;
    totalEarned: number;
}

interface ApiResponse {
    success: boolean;
    error: string | null;
    message: string;
    referrals: Referral[];
    referral_code: string;
}

// State
const isLoading = ref(true);
const copied = ref(false);
const referralCode = ref('');

// Generate referral link
const referralLink = computed(() => {
    const baseUrl = window.location.origin;
    return `${baseUrl}/auth/register?ref=${referralCode.value}`;
});

// Referral stats
const stats = ref<ReferralStats>({
    totalReferrals: 0,
    pendingReferrals: 0,
    totalEarned: 0,
});

// Referrals list
const referrals = ref<Referral[]>([]);

// Copy referral link to clipboard
const copyReferralLink = () => {
    const textArea = document.createElement('textarea');
    textArea.value = referralLink.value;
    document.body.appendChild(textArea);
    textArea.select();
    try {
        document.execCommand('copy');
        copied.value = true;
        setTimeout(() => {
            copied.value = false;
        }, 2000);
    } catch (err) {
        console.error('Failed to copy text: ', err);
    }
    document.body.removeChild(textArea);
};

// Share referral link via different platforms
const shareVia = (platform: string) => {
    const message = t('referrals.pages.index.share.shareMessage');
    let url = '';

    switch (platform) {
        case 'twitter':
            url = `https://twitter.com/intent/tweet?url=${encodeURIComponent(referralLink.value)}&text=${encodeURIComponent(message)}`;
            break;
        case 'facebook':
            url = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(referralLink.value)}&quote=${encodeURIComponent(message)}`;
            break;
        case 'discord':
            navigator.clipboard.writeText(`${message} ${referralLink.value}`);
            Swal.fire({
                title: t('referrals.pages.index.copy.title'),
                text: t('referrals.pages.index.copy.text'),
                icon: 'success',
                confirmButtonText: t('referrals.pages.index.copy.button'),
            });
            return;
        case 'email':
            url = `mailto:?subject=${encodeURIComponent(t('referrals.pages.index.share.mailTitle'))}&body=${encodeURIComponent(`${message}\n\n${referralLink.value}`)}`;
            break;
    }

    if (url) {
        window.open(url, '_blank');
    }
};

// Format date
const formatDate = (dateString: string): string => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

// Load referrals data
const loadReferrals = async () => {
    isLoading.value = true;

    try {
        const response = await fetch('/api/user/earn/referrals');
        const data = (await response.json()) as ApiResponse;

        if (data.success) {
            // Update referral code
            referralCode.value = data.referral_code;

            // Update referrals list
            referrals.value = data.referrals;
            // Calculate stats
            stats.value = {
                totalReferrals: data.referrals.length,
                pendingReferrals: data.referrals.filter((referral: Referral) => referral.deleted === 'false').length,
                totalEarned:
                    data.referrals.length * parseInt(Settings.getSetting('referrals_coins_per_referral') || '35'),
            };
        } else {
            Swal.fire({
                title: t('referrals.pages.index.error.title'),
                text: data.error || t('referrals.pages.index.error.text'),
                icon: 'error',
                confirmButtonText: t('referrals.pages.index.error.button'),
            });
        }
    } catch (error) {
        console.error('Error loading referrals:', error);
        Swal.fire({
            title: t('referrals.pages.index.error.title'),
            text: t('referrals.pages.index.error.text'),
            icon: 'error',
            confirmButtonText: t('referrals.pages.index.error.button'),
        });
    } finally {
        isLoading.value = false;
    }
};

onMounted(() => {
    loadReferrals();
});
</script>

<style scoped>
.animate-spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}
</style>
