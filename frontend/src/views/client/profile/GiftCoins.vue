<template>
    <LayoutDashboard>
        <div v-if="loading" class="flex justify-center items-center min-h-[60vh]">
            <LoadingAnimation />
        </div>
        <div v-else-if="error" class="text-center p-8 bg-[#0a0a15]/50 rounded-xl border border-red-500/20">
            <div class="inline-flex items-center justify-center p-3 bg-red-500/10 text-red-400 rounded-full mb-4">
                <AlertCircle class="h-6 w-6" />
            </div>
            <h3 class="text-lg font-medium text-gray-200 mb-2">{{ t('profile.error.title') }}</h3>
            <p class="text-gray-400 mb-4">{{ error }}</p>
            <Button @click="fetchRecipientData" variant="secondary"> {{ t('profile.error.retry') }} </Button>
        </div>
        <div v-else class="max-w-2xl mx-auto">
            <!-- Page Header -->
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-100 mb-2">{{ t('profile.gift.title') }}</h1>
                <p class="text-gray-400">{{ t('profile.gift.description') }}</p>
            </div>

            <!-- Gift Card -->
            <div class="bg-[#0a0a15]/50 border border-[#1a1a2f]/50 rounded-xl overflow-hidden shadow-lg">
                <!-- Card Header -->
                <div class="bg-gradient-to-r from-[#1a1a2e]/50 to-[#12121f]/50 py-4 px-5 border-b border-[#1a1a2f]/30">
                    <h2 class="text-lg font-medium text-white flex items-center">
                        <Coins class="w-5 h-5 text-amber-400 mr-2" />
                        {{ t('profile.gift.recipient', { username: recipientUser.username }) }}
                    </h2>
                </div>

                <!-- Card Body -->
                <div class="p-6">
                    <div v-if="success" class="mb-6 bg-green-900/20 text-green-400 p-3 rounded-lg text-sm">
                        {{ success }}
                    </div>

                    <div class="space-y-6">
                        <!-- User Info -->
                        <div class="flex items-center p-4 bg-[#0a0a15]/70 rounded-lg border border-[#1a1a2f]/30">
                            <img
                                :src="recipientUser.avatar"
                                :alt="recipientUser.username"
                                class="w-12 h-12 rounded-full border-2 border-[#1a1a2f] mr-3"
                            />
                            <div class="flex-1">
                                <h4 class="text-white font-medium">{{ recipientUser.username }}</h4>
                                <p v-if="recipientUser.uuid" class="text-gray-400 text-sm">
                                    UUID: {{ recipientUser.uuid.substring(0, 8) }}...
                                </p>
                            </div>
                        </div>

                        <!-- Current Balance -->
                        <div
                            class="flex items-center justify-between bg-[#0a0a15]/70 rounded-lg p-4 border border-[#1a1a2f]/30"
                        >
                            <span class="text-gray-400">{{ t('profile.gift.your_balance') }}</span>
                            <span class="text-amber-400 font-medium text-lg flex items-center">
                                <Coins class="w-5 h-5 mr-1" />
                                {{ userBalance }}
                            </span>
                        </div>

                        <!-- Amount Input -->
                        <div class="space-y-2">
                            <label for="amount" class="block text-sm font-medium text-gray-300">
                                {{ t('profile.gift.amount') }}
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <Coins class="h-5 w-5 text-gray-500" />
                                </div>
                                <input
                                    type="number"
                                    name="amount"
                                    id="amount"
                                    v-model="amount"
                                    :min="minAmount"
                                    :max="maxAmount"
                                    class="block w-full pl-10 pr-12 py-3 bg-[#12121f]/90 border border-[#1a1a2f] rounded-md focus:ring-indigo-500 focus:border-indigo-500 text-gray-200"
                                    :placeholder="t('profile.gift.amount_placeholder')"
                                />
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <button
                                        type="button"
                                        @click="amount = maxAmount"
                                        class="text-xs text-indigo-400 hover:text-indigo-300 font-medium"
                                    >
                                        {{ t('profile.gift.max') }}
                                    </button>
                                </div>
                            </div>
                            <div class="flex items-center justify-between text-xs text-gray-500">
                                <span>{{ t('profile.gift.min_amount') }}: {{ minAmount }}</span>
                                <span>{{ t('profile.gift.max_amount') }}: {{ maxAmount }}</span>
                            </div>

                            <!-- Fee Information -->
                            <div class="mt-3 p-3 bg-[#0a0a15]/70 rounded-lg border border-[#1a1a2f]/30 text-sm">
                                <div class="flex justify-between items-center text-gray-400 mb-2">
                                    <span>{{ t('profile.gift.fee_label') }}:</span>
                                    <span class="font-medium">{{ fee }}%</span>
                                </div>
                                <div v-if="amount > 0" class="flex justify-between items-center text-gray-400 mb-2">
                                    <span>{{ t('profile.gift.fee_amount') }}:</span>
                                    <span class="font-medium text-amber-400">+{{ feeAmount }}</span>
                                </div>
                                <div
                                    v-if="amount > 0"
                                    class="flex justify-between items-center border-t border-[#1a1a2f]/30 pt-2 mt-2"
                                >
                                    <span class="text-gray-300">{{ t('profile.gift.total_cost') }}:</span>
                                    <span class="font-medium text-red-400">{{ totalCost }}</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-2">{{ t('profile.gift.fee_explanation') }}</p>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex pt-4">
                            <Button @click="goBack" variant="secondary" class="mr-3">
                                {{ t('profile.gift.cancel') }}
                            </Button>
                            <Button
                                @click="sendGift"
                                variant="primary"
                                :disabled="isLoading || !isValidAmount"
                                class="flex-1"
                            >
                                <template v-if="isLoading">
                                    <Loader class="animate-spin w-4 h-4 mr-2" />
                                    {{ t('profile.gift.processing') }}
                                </template>
                                <template v-else>
                                    <Coins class="w-4 h-4 mr-2" />
                                    {{ t('profile.gift.send') }}
                                </template>
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
import { Coins, Loader, AlertCircle } from 'lucide-vue-next';
import LayoutDashboard from '@/components/client/LayoutDashboard.vue';
import LoadingAnimation from '@/components/client/ui/LoadingAnimation.vue';
import Button from '@/components/client/ui/Button.vue';
import { MythicalDOM } from '@/mythicaldash/MythicalDOM';
import { useSettingsStore } from '@/stores/settings';
import Session from '@/mythicaldash/Session';

// Define a proper interface for the recipient user
interface RecipientUser {
    uuid: string;
    username: string;
    avatar: string;
    credits?: number;
}

const { t } = useI18n();
const route = useRoute();
const router = useRouter();
const Settings = useSettingsStore();

const recipientUuid = computed(() => route.params.uuid as string);
const recipientUser = ref<RecipientUser>({
    uuid: '',
    username: '',
    avatar: '',
});

const amount = ref<number>(0);
const userBalance = ref<number>(0);
const error = ref<string>('');
const success = ref<string>('');
const loading = ref<boolean>(true);
const isLoading = ref<boolean>(false);

const fee = computed(() => Settings.getSetting('coins_share_fee'));
const minAmount = computed(() => Settings.getSetting('coins_share_min_amount'));

// Check if coin sharing is enabled
if (Settings.getSetting('allow_coins_sharing') !== 'true') {
    router.push('/dashboard');
}
// Maximum amount the user can gift (their balance)
const maxAmount = computed(() => {
    // The max a user can gift is limited by their balance and the fee
    // Since sending amount x costs (amount + fee)
    const maxWithoutFee = userBalance.value / (1 + Number(fee.value) / 100);
    return Math.floor(maxWithoutFee);
});

// Calculate fee amount
const feeAmount = computed(() => {
    if (amount.value <= 0) return 0;
    return Math.ceil((amount.value * Number(fee.value)) / 100);
});

// Calculate total cost (amount + fee)
const totalCost = computed(() => {
    return Number(amount.value) + Number(feeAmount.value);
});

// Validate the amount
const isValidAmount = computed(
    () =>
        amount.value >= Number(minAmount.value) &&
        amount.value <= Number(userBalance.value) &&
        totalCost.value <= userBalance.value,
);

// Go back to profile page
const goBack = () => {
    router.back();
};

// Fetch recipient user data
const fetchRecipientData = async () => {
    loading.value = true;
    error.value = '';

    if (!recipientUuid.value) {
        error.value = t('profile.gift.error.recipient_required');
        loading.value = false;
        return;
    }

    try {
        const response = await fetch(`/api/user/profile/${recipientUuid.value}`);

        if (!response.ok) {
            throw new Error(t('profile.gift.error.failed_fetch'));
        }

        const data = await response.json();

        if (data.success && data.user) {
            recipientUser.value = data.user;
            MythicalDOM.setPageTitle(t('profile.gift.page_title', { username: recipientUser.value.username }));
        } else {
            error.value = t('profile.gift.error.user_not_found');
        }
    } catch (err) {
        error.value = t('profile.gift.error.failed_profile');
        console.error('Failed to fetch recipient profile:', err);
    } finally {
        loading.value = false;
    }
};

// Fetch current user balance
const fetchUserBalance = async () => {
    try {
        userBalance.value = Session.getInfoInt('credits');
    } catch (err) {
        error.value = t('profile.gift.error.failed_balance');
        console.error('Failed to fetch user balance:', err);
    }
};

// Send gift to recipient
const sendGift = async () => {
    // Reset error and success messages
    error.value = '';
    success.value = '';

    // Validate the amount
    if (!isValidAmount.value) {
        error.value = t('profile.gift.error.invalid_amount');
        return;
    }

    isLoading.value = true;

    try {
        const formData = new FormData();
        formData.append('coins', amount.value.toString());
        formData.append('recipient_uuid', recipientUuid.value);

        const response = await fetch('/api/user/gift', {
            method: 'POST',
            body: formData,
        });

        const data = await response.json();
        const successResponse = data.success;

        if (successResponse) {
            success.value = t('profile.gift.success_message', {
                amount: amount.value,
                username: recipientUser.value.username,
            });

            // Update user balance (sender is charged amount + fee)
            userBalance.value -= totalCost.value;
            // Reset form
            amount.value = 0;

            // Navigate back to profile after 2 seconds
            setTimeout(() => {
                router.back();
            }, 2000);
        } else {
            error.value = data.message || t('profile.gift.error.failed_send');
        }
    } catch (err) {
        error.value = t('profile.gift.error.generic');
        console.error('Failed to send gift:', err);
    } finally {
        isLoading.value = false;
    }
};

// Initialize
onMounted(async () => {
    await Promise.all([fetchRecipientData(), fetchUserBalance()]);
});
</script>

<style scoped>
.shadow-lg {
    box-shadow:
        0 10px 15px -3px rgba(0, 0, 0, 0.1),
        0 4px 6px -2px rgba(0, 0, 0, 0.05);
}
</style>
