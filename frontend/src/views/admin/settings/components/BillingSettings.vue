<template>
    <div>
        <h2 class="text-xl font-semibold text-white mb-4">Billing Settings</h2>

        <!-- Credits Recharge -->
        <div class="pt-4 border-t border-gray-700">
            <h3 class="text-lg font-medium text-white mb-3">Credits Recharge</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="credits_recharge_enabled" class="block text-sm font-medium text-gray-400 mb-1"
                        >Enable Credits Recharge</label
                    >
                    <input
                        id="credits_recharge_enabled"
                        type="checkbox"
                        v-model="formData.credits_recharge_enabled"
                        @change="updateSetting('credits_recharge_enabled', formData.credits_recharge_enabled)"
                        class="rounded border-gray-700 text-indigo-500 focus:ring-indigo-500"
                    />
                    <p class="mt-1 text-xs text-gray-500">Enable or disable the credits recharge feature.</p>
                </div>
            </div>
        </div>
        <div class="space-y-6">
            <!-- Company Information -->
            <div class="pt-2 border-t border-gray-700">
                <h3 class="text-lg font-medium text-white mb-3">Company Information</h3>

                <!-- Company Name -->
                <div class="mb-4">
                    <label for="company_name" class="block text-sm font-medium text-gray-400 mb-1">Company Name</label>
                    <input
                        id="company_name"
                        type="text"
                        v-model="formData.company_name"
                        @change="updateSetting('company_name', formData.company_name)"
                        class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                    />
                    <p class="mt-1 text-xs text-gray-500">
                        Your company name displayed on invoices and payment receipts.
                    </p>
                </div>

                <!-- Company Address -->
                <div class="mb-4">
                    <label for="company_address" class="block text-sm font-medium text-gray-400 mb-1"
                        >Street Address</label
                    >
                    <input
                        id="company_address"
                        type="text"
                        v-model="formData.company_address"
                        @change="updateSetting('company_address', formData.company_address)"
                        class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                    />
                </div>

                <!-- City, State, ZIP -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label for="company_city" class="block text-sm font-medium text-gray-400 mb-1">City</label>
                        <input
                            id="company_city"
                            type="text"
                            v-model="formData.company_city"
                            @change="updateSetting('company_city', formData.company_city)"
                            class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                        />
                    </div>
                    <div>
                        <label for="company_state" class="block text-sm font-medium text-gray-400 mb-1"
                            >State/Province</label
                        >
                        <input
                            id="company_state"
                            type="text"
                            v-model="formData.company_state"
                            @change="updateSetting('company_state', formData.company_state)"
                            class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                        />
                    </div>
                    <div>
                        <label for="company_zip" class="block text-sm font-medium text-gray-400 mb-1"
                            >ZIP/Postal Code</label
                        >
                        <input
                            id="company_zip"
                            type="text"
                            v-model="formData.company_zip"
                            @change="updateSetting('company_zip', formData.company_zip)"
                            class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                        />
                    </div>
                </div>

                <!-- Country & VAT -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="company_country" class="block text-sm font-medium text-gray-400 mb-1"
                            >Country</label
                        >
                        <input
                            id="company_country"
                            type="text"
                            v-model="formData.company_country"
                            @change="updateSetting('company_country', formData.company_country)"
                            class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                        />
                    </div>
                    <div>
                        <label for="company_vat" class="block text-sm font-medium text-gray-400 mb-1">VAT/Tax ID</label>
                        <input
                            id="company_vat"
                            type="text"
                            v-model="formData.company_vat"
                            @change="updateSetting('company_vat', formData.company_vat)"
                            class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                        />
                        <p class="mt-1 text-xs text-gray-500">
                            Your company's VAT or tax identification number for invoices.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Currency Settings -->
            <div class="pt-4 border-t border-gray-700">
                <h3 class="text-lg font-medium text-white mb-3">Currency Settings</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="currency" class="block text-sm font-medium text-gray-400 mb-1">Currency</label>
                        <select
                            id="currency"
                            v-model="formData.currency"
                            @change="updateSetting('currency', formData.currency)"
                            class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                        >
                            <option value="USD">USD - US Dollar</option>
                            <option value="EUR">EUR - Euro</option>
                            <option value="GBP">GBP - British Pound</option>
                            <option value="CAD">CAD - Canadian Dollar</option>
                            <option value="AUD">AUD - Australian Dollar</option>
                            <option value="JPY">JPY - Japanese Yen</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">The primary currency for transactions and invoices.</p>
                    </div>
                    <div>
                        <label for="currency_symbol" class="block text-sm font-medium text-gray-400 mb-1"
                            >Currency Symbol</label
                        >
                        <input
                            id="currency_symbol"
                            type="text"
                            v-model="formData.currency_symbol"
                            @change="updateSetting('currency_symbol', formData.currency_symbol)"
                            class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                            maxlength="3"
                            placeholder="$"
                        />
                        <p class="mt-1 text-xs text-gray-500">Symbol displayed alongside prices (e.g., $, €, £).</p>
                    </div>
                </div>
            </div>

            <!-- Payment Gateways -->
            <div class="pt-4 border-t border-gray-700">
                <h3 class="text-lg font-medium text-white mb-3">Payment Gateways</h3>

                <!-- PayPal Settings -->
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h4 class="font-medium text-white">PayPal</h4>
                            <p class="text-sm text-gray-400">Accept payments via PayPal</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input
                                type="checkbox"
                                class="sr-only peer"
                                v-model="formData.enable_paypal"
                                :true-value="'true'"
                                :false-value="'false'"
                                @change="updateSetting('enable_paypal', formData.enable_paypal)"
                            />
                            <div
                                class="w-11 h-6 bg-gray-700 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-pink-500 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-pink-600"
                            ></div>
                        </label>
                    </div>

                    <div v-if="formData.enable_paypal === 'true'" class="pl-1 border-l-2 border-pink-500/50 space-y-4">
                        <div>
                            <label for="paypal_email" class="block text-sm font-medium text-gray-400 mb-1"
                                >PayPal Email</label
                            >
                            <input
                                id="paypal_email"
                                type="text"
                                v-model="formData.paypal_email"
                                @change="updateSetting('paypal_email', formData.paypal_email)"
                                class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                                placeholder="Your PayPal Email"
                            />
                        </div>
                        <div>
                            <div class="flex items-center mb-1">
                                <label class="relative inline-flex items-center cursor-pointer mr-3">
                                    <input
                                        type="checkbox"
                                        class="sr-only peer"
                                        v-model="formData.paypal_is_sandbox"
                                        :true-value="'true'"
                                        :false-value="'false'"
                                        @change="updateSetting('paypal_is_sandbox', formData.paypal_is_sandbox)"
                                    />
                                    <div
                                        class="w-9 h-5 bg-gray-700 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-pink-600"
                                    ></div>
                                </label>
                                <span class="text-sm font-medium text-gray-400">Use Sandbox Mode</span>
                            </div>
                            <p class="text-xs text-gray-500 ml-12">Enable for testing. Disable for live payments.</p>
                        </div>
                    </div>
                </div>

                <!-- Stripe Settings -->
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h4 class="font-medium text-white">Stripe</h4>
                            <p class="text-sm text-gray-400">Accept credit card payments via Stripe</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input
                                type="checkbox"
                                class="sr-only peer"
                                v-model="formData.enable_stripe"
                                :true-value="'true'"
                                :false-value="'false'"
                                @change="updateSetting('enable_stripe', formData.enable_stripe)"
                            />
                            <div
                                class="w-11 h-6 bg-gray-700 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-pink-500 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-pink-600"
                            ></div>
                        </label>
                    </div>

                    <div v-if="formData.enable_stripe === 'true'" class="pl-1 border-l-2 border-pink-500/50 space-y-4">
                        <div>
                            <label for="stripe_publishable_key" class="block text-sm font-medium text-gray-400 mb-1"
                                >Stripe Publishable Key</label
                            >
                            <input
                                id="stripe_publishable_key"
                                type="text"
                                v-model="formData.stripe_publishable_key"
                                @change="updateSetting('stripe_publishable_key', formData.stripe_publishable_key)"
                                class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                                placeholder="pk_..."
                            />

                            <div>
                                <label for="stripe_secret_key" class="block text-sm font-medium text-gray-400 mb-1"
                                    >Stripe Secret Key</label
                                >
                                <input
                                    id="stripe_secret_key"
                                    type="text"
                                    v-model="formData.stripe_secret_key"
                                    @change="updateSetting('stripe_secret_key', formData.stripe_secret_key)"
                                    class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                                    placeholder="sk_..."
                                />
                            </div>

                            <div>
                                <label for="stripe_webhook_id" class="block text-sm font-medium text-gray-400 mb-1"
                                    >Stripe Webhook ID</label
                                >
                                <input
                                    id="stripe_webhook_id"
                                    type="text"
                                    v-model="formData.stripe_webhook_id"
                                    @change="updateSetting('stripe_webhook_id', formData.stripe_webhook_id)"
                                    class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                                    placeholder="we_..."
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, watch, defineEmits } from 'vue';

interface Props {
    settings: Record<string, string>;
}

const props = defineProps<Props>();
const emit = defineEmits(['update']);

// Form state with default values
const formData = ref({
    // Company info
    company_name: 'Mythical Systems',
    company_address: '',
    company_city: '',
    company_state: '',
    company_zip: '',
    company_country: '',
    company_vat: '',

    // Currency settings
    currency: 'USD',
    currency_symbol: '$',

    // Payment gateways
    enable_stripe: 'false',
    enable_paypal: 'false',
    paypal_email: '',
    paypal_is_sandbox: 'false',

    stripe_publishable_key: '',
    stripe_secret_key: '',
    stripe_webhook_id: '',

    // Credits Recharge
    credits_recharge_enabled: 'false',
});

// Initialize form with settings values
watch(
    () => props.settings,
    (newSettings) => {
        if (newSettings) {
            formData.value = {
                // Company info
                company_name: newSettings['company_name'] || 'Mythical Systems',
                company_address: newSettings['company_address'] || '',
                company_city: newSettings['company_city'] || '',
                company_state: newSettings['company_state'] || '',
                company_zip: newSettings['company_zip'] || '',
                company_country: newSettings['company_country'] || '',
                company_vat: newSettings['company_vat'] || '',

                // Currency settings
                currency: newSettings['currency'] || 'USD',
                currency_symbol: newSettings['currency_symbol'] || '$',

                // Payment gateways
                enable_stripe: newSettings['enable_stripe'] || 'false',
                enable_paypal: newSettings['enable_paypal'] || 'false',
                paypal_email: newSettings['paypal_email'] || '',
                paypal_is_sandbox: newSettings['paypal_is_sandbox'] || 'false',
                stripe_publishable_key: newSettings['stripe_publishable_key'] || '',
                stripe_secret_key: newSettings['stripe_secret_key'] || '',
                stripe_webhook_id: newSettings['stripe_webhook_id'] || '',

                // Credits Recharge
                credits_recharge_enabled: newSettings['credits_recharge_enabled'] || 'false',
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
