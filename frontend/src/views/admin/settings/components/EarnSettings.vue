<template>
    <div>
        <h2 class="text-xl font-semibold text-white mb-4">Earn & Rewards Settings</h2>

        <div class="space-y-6">
            <!-- AFK Earnings -->
            <div class="bg-gray-800/30 p-5 rounded-lg border border-gray-700">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-lg font-medium text-white">AFK Rewards</h3>
                        <p class="text-sm text-gray-400">
                            Allow users to earn coins by staying active on the dashboard.
                        </p>
                    </div>
                    <div class="ml-4 flex items-center">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input
                                type="checkbox"
                                v-model="afkEnabled"
                                class="sr-only peer"
                                @change="updateSetting('afk_enabled', afkEnabled ? 'true' : 'false')"
                            />
                            <div
                                class="w-11 h-6 bg-gray-700 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-pink-500 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-gradient-to-r peer-checked:from-pink-500 peer-checked:to-violet-500"
                            ></div>
                        </label>
                    </div>
                </div>

                <div v-if="afkEnabled" class="mb-4">
                    <label for="afk_min_per_coin" class="block text-sm font-medium text-gray-400 mb-1"
                        >Minutes per Coin</label
                    >
                    <div class="flex items-center gap-4">
                        <input
                            id="afk_min_per_coin"
                            type="number"
                            min="1"
                            max="60"
                            v-model="formData.afk_min_per_coin"
                            @change="updateSetting('afk_min_per_coin', formData.afk_min_per_coin)"
                            class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                        />
                        <span class="text-gray-400 whitespace-nowrap">minutes</span>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">
                        How many minutes a user needs to be active on the dashboard to earn 1 coin.
                    </p>
                </div>
            </div>

            <!-- Code Redemption -->
            <div class="bg-gray-800/30 p-5 rounded-lg border border-gray-700">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-lg font-medium text-white">Code Redemption</h3>
                        <p class="text-sm text-gray-400">Allow users to redeem promo codes for rewards.</p>
                    </div>
                    <div class="ml-4 flex items-center">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input
                                type="checkbox"
                                v-model="codeRedemptionEnabled"
                                class="sr-only peer"
                                @change="
                                    updateSetting('code_redemption_enabled', codeRedemptionEnabled ? 'true' : 'false')
                                "
                            />
                            <div
                                class="w-11 h-6 bg-gray-700 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-pink-500 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-gradient-to-r peer-checked:from-pink-500 peer-checked:to-violet-500"
                            ></div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Join for Rewards -->
            <div class="bg-gray-800/30 p-5 rounded-lg border border-gray-700">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-lg font-medium text-white">Join for Rewards (J4R)</h3>
                        <p class="text-sm text-gray-400">Allow users to join Discord servers to earn rewards.</p>
                    </div>
                    <div class="ml-4 flex items-center">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input
                                type="checkbox"
                                v-model="j4rEnabled"
                                class="sr-only peer"
                                @change="updateSetting('j4r_enabled', j4rEnabled ? 'true' : 'false')"
                            />
                            <div
                                class="w-11 h-6 bg-gray-700 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-pink-500 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-gradient-to-r peer-checked:from-pink-500 peer-checked:to-violet-500"
                            ></div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Link for Rewards -->
            <div class="bg-gray-800/30 p-5 rounded-lg border border-gray-700">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-lg font-medium text-white">Link for Rewards (L4R)</h3>
                        <p class="text-sm text-gray-400">Allow users to visit links to earn rewards.</p>
                    </div>
                    <div class="ml-4 flex items-center">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input
                                type="checkbox"
                                v-model="l4rEnabled"
                                class="sr-only peer"
                                @change="updateSetting('l4r_enabled', l4rEnabled ? 'true' : 'false')"
                            />
                            <div
                                class="w-11 h-6 bg-gray-700 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-pink-500 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-gradient-to-r peer-checked:from-pink-500 peer-checked:to-violet-500"
                            ></div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Referrals -->
            <div class="bg-gray-800/30 p-5 rounded-lg border border-gray-700">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-lg font-medium text-white">Referral System</h3>
                        <p class="text-sm text-gray-400">Allow users to refer others and earn rewards.</p>
                    </div>
                    <div class="ml-4 flex items-center">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input
                                type="checkbox"
                                v-model="referralsEnabled"
                                class="sr-only peer"
                                @change="updateSetting('referrals_enabled', referralsEnabled ? 'true' : 'false')"
                            />
                            <div
                                class="w-11 h-6 bg-gray-700 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-pink-500 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-gradient-to-r peer-checked:from-pink-500 peer-checked:to-violet-500"
                            ></div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Store -->
            <div class="bg-gray-800/30 p-5 rounded-lg border border-gray-700">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-lg font-medium text-white">Store</h3>
                        <p class="text-sm text-gray-400">
                            Enable the in-dashboard store for users to spend their earned coins.
                        </p>
                    </div>
                    <div class="ml-4 flex items-center">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input
                                type="checkbox"
                                v-model="storeEnabled"
                                class="sr-only peer"
                                @change="updateSetting('store_enabled', storeEnabled ? 'true' : 'false')"
                            />
                            <div
                                class="w-11 h-6 bg-gray-700 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-pink-500 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-gradient-to-r peer-checked:from-pink-500 peer-checked:to-violet-500"
                            ></div>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, defineProps, defineEmits } from 'vue';

interface Props {
    settings: Record<string, string>;
}

const props = defineProps<Props>();
const emit = defineEmits(['update']);

// Form state
const formData = ref({
    afk_min_per_coin: '5',
});

// Computed properties for toggles
const afkEnabled = computed({
    get: () => props.settings?.afk_enabled === 'true',
    set: (value) => {
        emit('update', 'afk_enabled', value ? 'true' : 'false');
    },
});

const codeRedemptionEnabled = computed({
    get: () => props.settings?.code_redemption_enabled === 'true',
    set: (value) => {
        emit('update', 'code_redemption_enabled', value ? 'true' : 'false');
    },
});

const j4rEnabled = computed({
    get: () => props.settings?.j4r_enabled === 'true',
    set: (value) => {
        emit('update', 'j4r_enabled', value ? 'true' : 'false');
    },
});

const l4rEnabled = computed({
    get: () => props.settings?.l4r_enabled === 'true',
    set: (value) => {
        emit('update', 'l4r_enabled', value ? 'true' : 'false');
    },
});

const referralsEnabled = computed({
    get: () => props.settings?.referrals_enabled === 'true',
    set: (value) => {
        emit('update', 'referrals_enabled', value ? 'true' : 'false');
    },
});

const storeEnabled = computed({
    get: () => props.settings?.store_enabled === 'true',
    set: (value) => {
        emit('update', 'store_enabled', value ? 'true' : 'false');
    },
});

// Initialize form with settings values
watch(
    () => props.settings,
    (newSettings) => {
        if (newSettings) {
            formData.value = {
                afk_min_per_coin: newSettings['afk_min_per_coin'] || '5',
            };
        }
    },
    { immediate: true },
);

// Update a setting
const updateSetting = (key: string, value: string) => {
    emit('update', key, value);
};
</script>
