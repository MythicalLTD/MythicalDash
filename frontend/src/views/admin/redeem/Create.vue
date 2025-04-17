<template>
    <LayoutDashboard>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-400">Create Redeem Code</h1>
            <button
                @click="router.push('/mc-admin/redeem-codes')"
                class="bg-gray-700 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:bg-gray-600 flex items-center"
            >
                <ArrowLeftIcon class="w-4 h-4 mr-2" />
                Back to Redeem Codes
            </button>
        </div>

        <div class="bg-gray-800/50 backdrop-blur-md rounded-lg p-6">
            <form @submit.prevent="saveRedeemCode" class="space-y-6">
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
                        <p class="text-xs text-gray-400 mt-1">The code users will enter to redeem coins</p>
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
                        <p class="text-xs text-gray-400 mt-1">Number of coins awarded when code is redeemed</p>
                    </div>

                    <div>
                        <label for="uses" class="block text-sm font-medium text-gray-400 mb-1">Uses</label>
                        <input
                            id="uses"
                            v-model="redeemForm.uses"
                            type="number"
                            required
                            min="1"
                            class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                            placeholder="e.g. 10"
                        />
                        <p class="text-xs text-gray-400 mt-1">Number of times this code can be used</p>
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
                        <p class="text-xs text-gray-400 mt-1">
                            Whether this code is currently active and can be redeemed
                        </p>
                    </div>
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
                        :disabled="loading"
                        class="px-4 py-2 bg-gradient-to-r from-pink-500 to-violet-500 rounded-lg text-white hover:opacity-90 transition-colors flex items-center"
                    >
                        <LoaderIcon v-if="loading" class="animate-spin w-4 h-4 mr-2" />
                        <SaveIcon v-else class="w-4 h-4 mr-2" />
                        Create Redeem Code
                    </button>
                </div>
            </form>
        </div>
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import LayoutDashboard from '@/components/admin/LayoutDashboard.vue';
import { ArrowLeftIcon, SaveIcon, LoaderIcon } from 'lucide-vue-next';
import Redeem from '@/mythicaldash/admin/Redeem';
import Swal from 'sweetalert2';
import { useSound } from '@vueuse/sound';
import failedAlertSfx from '@/assets/sounds/error.mp3';
import successAlertSfx from '@/assets/sounds/success.mp3';

const router = useRouter();
const loading = ref(false);
const { play: playError } = useSound(failedAlertSfx);
const { play: playSuccess } = useSound(successAlertSfx);

// Form state
const redeemForm = ref({
    code: '',
    coins: 100,
    uses: 1,
    enabled: false,
});

const saveRedeemCode = async () => {
    loading.value = true;

    try {
        const response = await Redeem.createRedeemCode(
            redeemForm.value.code,
            redeemForm.value.coins,
            redeemForm.value.uses,
            redeemForm.value.enabled,
        );

        if (response.success) {
            playSuccess();
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Redeem code created successfully',
                showConfirmButton: true,
            });

            // Navigate back to redeem codes list after a short delay
            setTimeout(() => {
                router.push('/mc-admin/redeem-codes');
            }, 1500);
        } else {
            const errorMessages = {
                CODE_REQUIRED: 'The redeem code is required',
                COINS_REQUIRED: 'The coins amount is required',
                CODE_ALREADY_EXISTS: 'A redeem code with this code already exists',
                CREATE_CODE_FAILED: 'Failed to create the redeem code',
                INVALID_SESSION: 'Your session has expired. Please log in again.',
            };

            const error_code = response.error_code as keyof typeof errorMessages;
            const errorMessage = errorMessages[error_code] || 'Failed to create redeem code';

            playError();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: errorMessage,
                footer: 'Please try again or contact support if the issue persists.',
                showConfirmButton: true,
            });

            // If session expired, redirect to login
            if (error_code === 'INVALID_SESSION') {
                setTimeout(() => {
                    router.push('/auth/login');
                }, 2000);
            }
        }
    } catch (error) {
        console.error('Error creating redeem code:', error);
        playError();
        Swal.fire({
            icon: 'error',
            title: 'Connection Error',
            text: 'Failed to connect to the server. Please check your internet connection and try again.',
            footer: 'If the problem persists, please contact support.',
            showConfirmButton: true,
        });
    } finally {
        loading.value = false;
    }
};
</script>
