<template>
    <LayoutDashboard>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-400">Delete Redeem Code</h1>
            <button
                @click="router.push('/mc-admin/redeem-codes')"
                class="bg-gray-700 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:bg-gray-600 flex items-center"
            >
                <ArrowLeftIcon class="w-4 h-4 mr-2" />
                Back to Redeem Codes
            </button>
        </div>

        <div v-if="loading" class="flex justify-center items-center py-12">
            <LoaderIcon class="w-8 h-8 animate-spin text-pink-400" />
        </div>

        <div v-else-if="error" class="bg-red-500/20 text-red-400 p-4 rounded-lg mb-6">
            {{ error }}
        </div>

        <div v-else class="bg-gray-800/50 backdrop-blur-md rounded-lg p-6">
            <div class="text-center">
                <AlertTriangleIcon class="w-16 h-16 mx-auto text-red-400 mb-4" />
                <h2 class="text-xl font-medium text-gray-100 mb-2">Confirm Deletion</h2>
                <p class="text-gray-300 mb-2">
                    Are you sure you want to delete the redeem code
                    <span class="font-medium text-white">{{ redeemCode.code }}</span
                    >?
                </p>
                <p class="text-gray-400 mb-6">This action cannot be undone.</p>

                <div class="bg-gray-900/50 rounded-lg p-4 mb-6 text-left">
                    <h3 class="text-md font-medium text-gray-300 mb-2">Redeem Code Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div><span class="text-gray-400">ID:</span> {{ redeemCode.id }}</div>
                        <div><span class="text-gray-400">Code:</span> {{ redeemCode.code }}</div>
                        <div><span class="text-gray-400">Coins:</span> {{ redeemCode.coins }}</div>
                        <div><span class="text-gray-400">Uses Left:</span> {{ redeemCode.uses }}</div>
                        <div>
                            <span class="text-gray-400">Status: </span>
                            <span :class="redeemCode.enabled === 'true' ? 'text-green-400' : 'text-red-400'">
                                {{ redeemCode.enabled === 'true' ? 'Enabled' : 'Disabled' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-gray-400">Created:</span>
                            {{ new Date(redeemCode.created_at).toLocaleString() }}
                        </div>
                    </div>
                </div>

                <div v-if="successMessage" class="bg-green-500/20 text-green-400 p-4 rounded-lg mb-6">
                    {{ successMessage }}
                </div>

                <div class="flex justify-center space-x-3">
                    <button
                        @click="router.push('/mc-admin/redeem-codes')"
                        class="px-4 py-2 border border-gray-600 rounded-lg text-gray-300 hover:bg-gray-700 transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        @click="deleteRedeemCode"
                        :disabled="deleting"
                        class="px-4 py-2 bg-red-500 rounded-lg text-white hover:bg-red-600 transition-colors flex items-center"
                    >
                        <LoaderIcon v-if="deleting" class="animate-spin w-4 h-4 mr-2" />
                        <TrashIcon v-else class="w-4 h-4 mr-2" />
                        Delete Redeem Code
                    </button>
                </div>
            </div>
        </div>
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import LayoutDashboard from '@/components/admin/LayoutDashboard.vue';
import { ArrowLeftIcon, AlertTriangleIcon, TrashIcon, LoaderIcon } from 'lucide-vue-next';
import Redeem from '@/mythicaldash/admin/Redeem';
import { useSound } from '@vueuse/sound';
import failedAlertSfx from '@/assets/sounds/error.mp3';
import successAlertSfx from '@/assets/sounds/success.mp3';

const router = useRouter();
const route = useRoute();
const codeId = Number(route.params.id);

const loading = ref(true);
const deleting = ref(false);
const error = ref('');
const successMessage = ref('');
const { play: playError } = useSound(failedAlertSfx);
const { play: playSuccess } = useSound(successAlertSfx);

// Interface for the redeem code data from API
interface ApiRedeemCode {
    id: number;
    code: string;
    coins: number;
    uses: number;
    enabled: string;
    deleted: string;
    created_at: string;
    updated_at: string;
}

const redeemCode = ref<ApiRedeemCode>({
    id: 0,
    code: '',
    coins: 0,
    uses: 0,
    enabled: 'false',
    deleted: 'false',
    created_at: '',
    updated_at: '',
});

onMounted(async () => {
    try {
        // Fetch redeem code data from API
        await fetchRedeemCodeData();
    } catch (err) {
        error.value = 'Failed to load redeem code data';
        console.error(err);
    } finally {
        loading.value = false;
    }
});

// Fetch redeem code data from API
const fetchRedeemCodeData = async () => {
    try {
        const response = await Redeem.getRedeemCode(codeId);

        if (response.success) {
            redeemCode.value = response.code;
        } else {
            error.value = response.message || 'Failed to load redeem code data';
        }
    } catch (err) {
        console.error('Error fetching redeem code data:', err);
        throw err;
    }
};

const deleteRedeemCode = async () => {
    deleting.value = true;
    successMessage.value = '';
    error.value = '';

    try {
        const response = await Redeem.deleteRedeemCode(codeId);

        if (response.success) {
            successMessage.value = 'Redeem code deleted successfully';
            playSuccess();

            // Wait a moment before redirecting
            setTimeout(() => {
                router.push('/mc-admin/redeem-codes');
            }, 1500);
        } else {
            const errorMessages = {
                CODE_ID_REQUIRED: 'Redeem code ID is required',
                CODE_NOT_FOUND: 'Redeem code not found',
                DELETE_CODE_FAILED: 'Failed to delete redeem code',
                INVALID_SESSION: 'Your session has expired. Please log in again.',
            };

            const error_code = response.error_code as keyof typeof errorMessages;
            const errorMessage = errorMessages[error_code] || 'Failed to delete redeem code';

            error.value = errorMessage;
            playError();

            // If session expired, redirect to login
            if (error_code === 'INVALID_SESSION') {
                setTimeout(() => {
                    router.push('/auth/login');
                }, 2000);
            }
        }
    } catch (err) {
        console.error('Error deleting redeem code:', err);
        error.value = 'An error occurred while deleting the redeem code';
        playError();
    } finally {
        deleting.value = false;
    }
};
</script>
