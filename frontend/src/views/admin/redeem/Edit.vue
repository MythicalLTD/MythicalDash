<template>
    <LayoutDashboard>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-400">Edit Redeem Code</h1>
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
            <form @submit.prevent="updateRedeemCode" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-400 mb-1">Code</label>
                        <input
                            id="code"
                            v-model="redeemForm.code"
                            type="text"
                            required
                            class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                            placeholder="e.g. WELCOME2024"
                        />
                    </div>

                    <div>
                        <label for="coins" class="block text-sm font-medium text-gray-400 mb-1">Coins</label>
                        <input
                            id="coins"
                            v-model="redeemForm.coins"
                            type="number"
                            required
                            min="1"
                            class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                            placeholder="e.g. 100"
                        />
                    </div>

                    <div>
                        <label for="uses" class="block text-sm font-medium text-gray-400 mb-1">Uses Left</label>
                        <input
                            id="uses"
                            v-model="redeemForm.uses"
                            type="number"
                            required
                            min="0"
                            class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                            placeholder="e.g. 10"
                        />
                    </div>

                    <div>
                        <label for="enabled" class="block text-sm font-medium text-gray-400 mb-1">Status</label>
                        <select
                            id="enabled"
                            v-model="redeemForm.enabled"
                            class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                        >
                            <option :value="true">Enabled</option>
                            <option :value="false">Disabled</option>
                        </select>
                    </div>

                    <div v-if="redeemForm.created_at">
                        <label class="block text-sm font-medium text-gray-400 mb-1">Created At</label>
                        <div class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 text-gray-400">
                            {{ new Date(redeemForm.created_at).toLocaleString() }}
                        </div>
                    </div>

                    <div v-if="redeemForm.updated_at">
                        <label class="block text-sm font-medium text-gray-400 mb-1">Last Updated</label>
                        <div class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 text-gray-400">
                            {{ new Date(redeemForm.updated_at).toLocaleString() }}
                        </div>
                    </div>
                </div>

                <div v-if="successMessage" class="bg-green-500/20 text-green-400 p-4 rounded-lg mb-6">
                    {{ successMessage }}
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-700">
                    <button
                        type="button"
                        @click="router.push('/mc-admin/redeem-codes')"
                        class="px-4 py-2 border border-gray-600 rounded-lg text-gray-300 hover:bg-gray-700 transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        :disabled="saving"
                        class="px-4 py-2 bg-gradient-to-r from-pink-500 to-violet-500 rounded-lg text-white hover:opacity-90 transition-colors flex items-center"
                    >
                        <LoaderIcon v-if="saving" class="animate-spin w-4 h-4 mr-2" />
                        <SaveIcon v-else class="w-4 h-4 mr-2" />
                        Update Redeem Code
                    </button>
                </div>
            </form>
        </div>
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import LayoutDashboard from '@/components/admin/LayoutDashboard.vue';
import { ArrowLeftIcon, SaveIcon, LoaderIcon } from 'lucide-vue-next';
import Redeem from '@/mythicaldash/admin/Redeem';
import { useSound } from '@vueuse/sound';
import failedAlertSfx from '@/assets/sounds/error.mp3';
import successAlertSfx from '@/assets/sounds/success.mp3';

const router = useRouter();
const route = useRoute();
const codeId = Number(route.params.id);

const loading = ref(true);
const saving = ref(false);
const error = ref('');
const successMessage = ref('');

// Form state with default values
const redeemForm = ref({
    id: codeId,
    code: '',
    coins: 0,
    uses: 0,
    enabled: false,
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
            const code = response.code;

            // Populate the form with redeem code data
            redeemForm.value = {
                id: code.id,
                code: code.code,
                coins: parseInt(code.coins),
                uses: parseInt(code.uses),
                enabled: code.enabled === 'true',
                deleted: code.deleted,
                created_at: code.created_at,
                updated_at: code.updated_at,
            };
        } else {
            error.value = response.message || 'Failed to load redeem code data';
        }
    } catch (err) {
        console.error('Error fetching redeem code data:', err);
        throw err;
    }
};

const updateRedeemCode = async () => {
    saving.value = true;
    successMessage.value = '';
    error.value = '';

    try {
        const response = await Redeem.updateRedeemCode(
            codeId,
            redeemForm.value.code,
            redeemForm.value.coins,
            redeemForm.value.uses,
            redeemForm.value.enabled,
        );

        if (response.success) {
            // Play success sound
            successMessage.value = 'Redeem code updated successfully';
            const { play: playSuccess } = useSound(successAlertSfx);
            playSuccess();

            // Refresh data to show updated values
            await fetchRedeemCodeData();
        } else {
            const errorMessages = {
                CODE_REQUIRED: 'The redeem code is required',
                COINS_REQUIRED: 'The coins amount is required',
                CODE_ALREADY_EXISTS: 'A redeem code with this code already exists',
                CODE_NOT_FOUND: 'Redeem code not found',
                UPDATE_CODE_FAILED: 'Failed to update the redeem code',
                INVALID_SESSION: 'Your session has expired. Please log in again.',
            };

            const error_code = response.error_code as keyof typeof errorMessages;
            const errorMessage = errorMessages[error_code] || 'Failed to update redeem code';

            error.value = errorMessage;
            const { play: playError } = useSound(failedAlertSfx);
            playError();

            // If session expired, redirect to login
            if (error_code === 'INVALID_SESSION') {
                setTimeout(() => {
                    router.push('/auth/login');
                }, 2000);
            }
        }
    } catch (err) {
        console.error('Error updating redeem code:', err);
        error.value = 'An error occurred while updating the redeem code';
        const { play: playError } = useSound(failedAlertSfx);
        playError();
    } finally {
        saving.value = false;
    }
};
</script>
