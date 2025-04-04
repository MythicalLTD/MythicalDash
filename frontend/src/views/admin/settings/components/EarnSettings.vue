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
                        <h3 class="text-lg font-medium text-white">Join for Rewards (J4R) [Disabled]</h3>
                        <p class="text-sm text-gray-400">Allow users to join Discord servers to earn rewards.</p>
                    </div>
                    <div class="ml-4 flex items-center">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input
                                type="checkbox"
                                v-model="j4rEnabled"
                                class="sr-only peer"
                                @change="updateSetting('j4r_enabled', j4rEnabled ? 'true' : 'false')"
                                disabled
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

                <div v-if="l4rEnabled" class="space-y-6 mt-4">
                    <!-- LinkAdvertise Section -->
                    <div class="border border-gray-700 rounded-lg p-4">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h4 class="font-medium text-white">LinkAdvertise</h4>
                                <p class="text-xs text-gray-400">Enable earning from LinkAdvertise shortlinks</p>
                            </div>
                            <div class="ml-4 flex items-center">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input
                                        type="checkbox"
                                        v-model="l4rLinkAdvertiseEnabled"
                                        class="sr-only peer"
                                        @change="
                                            updateSetting(
                                                'l4r_linkadvertise_enabled',
                                                l4rLinkAdvertiseEnabled ? 'true' : 'false',
                                            )
                                        "
                                    />
                                    <div
                                        class="w-9 h-5 bg-gray-700 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-pink-500 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-gradient-to-r peer-checked:from-pink-500 peer-checked:to-violet-500"
                                    ></div>
                                </label>
                            </div>
                        </div>

                        <div v-if="l4rLinkAdvertiseEnabled" class="space-y-3">
                            <div>
                                <label
                                    for="l4r_linkadvertise_user_id"
                                    class="block text-sm font-medium text-gray-400 mb-1"
                                >
                                    User ID
                                </label>
                                <input
                                    id="l4r_linkadvertise_user_id"
                                    type="text"
                                    v-model="formData.l4r_linkadvertise_user_id"
                                    @change="
                                        updateSetting('l4r_linkadvertise_user_id', formData.l4r_linkadvertise_user_id)
                                    "
                                    class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                                />
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label
                                        for="l4r_linkadvertise_coins_per_link"
                                        class="block text-sm font-medium text-gray-400 mb-1"
                                    >
                                        Coins Per Link
                                    </label>
                                    <input
                                        id="l4r_linkadvertise_coins_per_link"
                                        type="number"
                                        v-model="formData.l4r_linkadvertise_coins_per_link"
                                        @change="
                                            updateSetting(
                                                'l4r_linkadvertise_coins_per_link',
                                                formData.l4r_linkadvertise_coins_per_link,
                                            )
                                        "
                                        class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                                    />
                                </div>

                                <div>
                                    <label
                                        for="l4r_linkadvertise_daily_limit"
                                        class="block text-sm font-medium text-gray-400 mb-1"
                                    >
                                        Daily Limit
                                    </label>
                                    <input
                                        id="l4r_linkadvertise_daily_limit"
                                        type="number"
                                        v-model="formData.l4r_linkadvertise_daily_limit"
                                        @change="
                                            updateSetting(
                                                'l4r_linkadvertise_daily_limit',
                                                formData.l4r_linkadvertise_daily_limit,
                                            )
                                        "
                                        class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                                    />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label
                                        for="l4r_linkadvertise_min_time_to_complete"
                                        class="block text-sm font-medium text-gray-400 mb-1"
                                    >
                                        Min Time to Complete (sec)
                                    </label>
                                    <input
                                        id="l4r_linkadvertise_min_time_to_complete"
                                        type="number"
                                        v-model="formData.l4r_linkadvertise_min_time_to_complete"
                                        @change="
                                            updateSetting(
                                                'l4r_linkadvertise_min_time_to_complete',
                                                formData.l4r_linkadvertise_min_time_to_complete,
                                            )
                                        "
                                        class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                                    />
                                </div>

                                <div>
                                    <label
                                        for="l4r_linkadvertise_time_to_expire"
                                        class="block text-sm font-medium text-gray-400 mb-1"
                                    >
                                        Time to Expire (sec)
                                    </label>
                                    <input
                                        id="l4r_linkadvertise_time_to_expire"
                                        type="number"
                                        v-model="formData.l4r_linkadvertise_time_to_expire"
                                        @change="
                                            updateSetting(
                                                'l4r_linkadvertise_time_to_expire',
                                                formData.l4r_linkadvertise_time_to_expire,
                                            )
                                        "
                                        class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                                    />
                                </div>
                            </div>

                            <div>
                                <label
                                    for="l4r_linkadvertise_cooldown_time"
                                    class="block text-sm font-medium text-gray-400 mb-1"
                                >
                                    Cooldown Time (sec)
                                </label>
                                <input
                                    id="l4r_linkadvertise_cooldown_time"
                                    type="number"
                                    v-model="formData.l4r_linkadvertise_cooldown_time"
                                    @change="
                                        updateSetting(
                                            'l4r_linkadvertise_cooldown_time',
                                            formData.l4r_linkadvertise_cooldown_time,
                                        )
                                    "
                                    class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- ShareUs Section -->
                    <div class="border border-gray-700 rounded-lg p-4">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h4 class="font-medium text-white">ShareUs</h4>
                                <p class="text-xs text-gray-400">Enable earning from ShareUs shortlinks</p>
                            </div>
                            <div class="ml-4 flex items-center">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input
                                        type="checkbox"
                                        v-model="l4rShareUsEnabled"
                                        class="sr-only peer"
                                        @change="
                                            updateSetting('l4r_shareus_enabled', l4rShareUsEnabled ? 'true' : 'false')
                                        "
                                    />
                                    <div
                                        class="w-9 h-5 bg-gray-700 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-pink-500 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-gradient-to-r peer-checked:from-pink-500 peer-checked:to-violet-500"
                                    ></div>
                                </label>
                            </div>
                        </div>

                        <div v-if="l4rShareUsEnabled" class="space-y-3">
                            <div>
                                <label for="l4r_shareus_api_key" class="block text-sm font-medium text-gray-400 mb-1">
                                    API Key
                                </label>
                                <input
                                    id="l4r_shareus_api_key"
                                    type="text"
                                    v-model="formData.l4r_shareus_api_key"
                                    @change="updateSetting('l4r_shareus_api_key', formData.l4r_shareus_api_key)"
                                    class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                                />
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label
                                        for="l4r_shareus_coins_per_link"
                                        class="block text-sm font-medium text-gray-400 mb-1"
                                    >
                                        Coins Per Link
                                    </label>
                                    <input
                                        id="l4r_shareus_coins_per_link"
                                        type="number"
                                        v-model="formData.l4r_shareus_coins_per_link"
                                        @change="
                                            updateSetting(
                                                'l4r_shareus_coins_per_link',
                                                formData.l4r_shareus_coins_per_link,
                                            )
                                        "
                                        class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                                    />
                                </div>

                                <div>
                                    <label
                                        for="l4r_shareus_daily_limit"
                                        class="block text-sm font-medium text-gray-400 mb-1"
                                    >
                                        Daily Limit
                                    </label>
                                    <input
                                        id="l4r_shareus_daily_limit"
                                        type="number"
                                        v-model="formData.l4r_shareus_daily_limit"
                                        @change="
                                            updateSetting('l4r_shareus_daily_limit', formData.l4r_shareus_daily_limit)
                                        "
                                        class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                                    />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label
                                        for="l4r_shareus_min_time_to_complete"
                                        class="block text-sm font-medium text-gray-400 mb-1"
                                    >
                                        Min Time to Complete (sec)
                                    </label>
                                    <input
                                        id="l4r_shareus_min_time_to_complete"
                                        type="number"
                                        v-model="formData.l4r_shareus_min_time_to_complete"
                                        @change="
                                            updateSetting(
                                                'l4r_shareus_min_time_to_complete',
                                                formData.l4r_shareus_min_time_to_complete,
                                            )
                                        "
                                        class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                                    />
                                </div>

                                <div>
                                    <label
                                        for="l4r_shareus_time_to_expire"
                                        class="block text-sm font-medium text-gray-400 mb-1"
                                    >
                                        Time to Expire (sec)
                                    </label>
                                    <input
                                        id="l4r_shareus_time_to_expire"
                                        type="number"
                                        v-model="formData.l4r_shareus_time_to_expire"
                                        @change="
                                            updateSetting(
                                                'l4r_shareus_time_to_expire',
                                                formData.l4r_shareus_time_to_expire,
                                            )
                                        "
                                        class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                                    />
                                </div>
                            </div>

                            <div>
                                <label
                                    for="l4r_shareus_cooldown_time"
                                    class="block text-sm font-medium text-gray-400 mb-1"
                                >
                                    Cooldown Time (sec)
                                </label>
                                <input
                                    id="l4r_shareus_cooldown_time"
                                    type="number"
                                    v-model="formData.l4r_shareus_cooldown_time"
                                    @change="
                                        updateSetting('l4r_shareus_cooldown_time', formData.l4r_shareus_cooldown_time)
                                    "
                                    class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- LinkPays Section -->
                    <div class="border border-gray-700 rounded-lg p-4">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h4 class="font-medium text-white">LinkPays</h4>
                                <p class="text-xs text-gray-400">Enable earning from LinkPays shortlinks</p>
                            </div>
                            <div class="ml-4 flex items-center">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input
                                        type="checkbox"
                                        v-model="l4rLinkPaysEnabled"
                                        class="sr-only peer"
                                        @change="
                                            updateSetting('l4r_linkpays_enabled', l4rLinkPaysEnabled ? 'true' : 'false')
                                        "
                                    />
                                    <div
                                        class="w-9 h-5 bg-gray-700 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-pink-500 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-gradient-to-r peer-checked:from-pink-500 peer-checked:to-violet-500"
                                    ></div>
                                </label>
                            </div>
                        </div>

                        <div v-if="l4rLinkPaysEnabled" class="space-y-3">
                            <div>
                                <label for="l4r_linkpays_api_key" class="block text-sm font-medium text-gray-400 mb-1">
                                    API Key
                                </label>
                                <input
                                    id="l4r_linkpays_api_key"
                                    type="text"
                                    v-model="formData.l4r_linkpays_api_key"
                                    @change="updateSetting('l4r_linkpays_api_key', formData.l4r_linkpays_api_key)"
                                    class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                                />
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label
                                        for="l4r_linkpays_coins_per_link"
                                        class="block text-sm font-medium text-gray-400 mb-1"
                                    >
                                        Coins Per Link
                                    </label>
                                    <input
                                        id="l4r_linkpays_coins_per_link"
                                        type="number"
                                        v-model="formData.l4r_linkpays_coins_per_link"
                                        @change="
                                            updateSetting(
                                                'l4r_linkpays_coins_per_link',
                                                formData.l4r_linkpays_coins_per_link,
                                            )
                                        "
                                        class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                                    />
                                </div>

                                <div>
                                    <label
                                        for="l4r_linkpays_daily_limit"
                                        class="block text-sm font-medium text-gray-400 mb-1"
                                    >
                                        Daily Limit
                                    </label>
                                    <input
                                        id="l4r_linkpays_daily_limit"
                                        type="number"
                                        v-model="formData.l4r_linkpays_daily_limit"
                                        @change="
                                            updateSetting('l4r_linkpays_daily_limit', formData.l4r_linkpays_daily_limit)
                                        "
                                        class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                                    />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label
                                        for="l4r_linkpays_min_time_to_complete"
                                        class="block text-sm font-medium text-gray-400 mb-1"
                                    >
                                        Min Time to Complete (sec)
                                    </label>
                                    <input
                                        id="l4r_linkpays_min_time_to_complete"
                                        type="number"
                                        v-model="formData.l4r_linkpays_min_time_to_complete"
                                        @change="
                                            updateSetting(
                                                'l4r_linkpays_min_time_to_complete',
                                                formData.l4r_linkpays_min_time_to_complete,
                                            )
                                        "
                                        class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                                    />
                                </div>

                                <div>
                                    <label
                                        for="l4r_linkpays_time_to_expire"
                                        class="block text-sm font-medium text-gray-400 mb-1"
                                    >
                                        Time to Expire (sec)
                                    </label>
                                    <input
                                        id="l4r_linkpays_time_to_expire"
                                        type="number"
                                        v-model="formData.l4r_linkpays_time_to_expire"
                                        @change="
                                            updateSetting(
                                                'l4r_linkpays_time_to_expire',
                                                formData.l4r_linkpays_time_to_expire,
                                            )
                                        "
                                        class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                                    />
                                </div>
                            </div>

                            <div>
                                <label
                                    for="l4r_linkpays_cooldown_time"
                                    class="block text-sm font-medium text-gray-400 mb-1"
                                >
                                    Cooldown Time (sec)
                                </label>
                                <input
                                    id="l4r_linkpays_cooldown_time"
                                    type="number"
                                    v-model="formData.l4r_linkpays_cooldown_time"
                                    @change="
                                        updateSetting('l4r_linkpays_cooldown_time', formData.l4r_linkpays_cooldown_time)
                                    "
                                    class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- GyaniLinks Section -->
                    <div class="border border-gray-700 rounded-lg p-4">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h4 class="font-medium text-white">GyaniLinks</h4>
                                <p class="text-xs text-gray-400">Enable earning from GyaniLinks shortlinks</p>
                            </div>
                            <div class="ml-4 flex items-center">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input
                                        type="checkbox"
                                        v-model="l4rGyaniLinksEnabled"
                                        class="sr-only peer"
                                        @change="
                                            updateSetting(
                                                'l4r_gyanilinks_enabled',
                                                l4rGyaniLinksEnabled ? 'true' : 'false',
                                            )
                                        "
                                    />
                                    <div
                                        class="w-9 h-5 bg-gray-700 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-pink-500 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-gradient-to-r peer-checked:from-pink-500 peer-checked:to-violet-500"
                                    ></div>
                                </label>
                            </div>
                        </div>

                        <div v-if="l4rGyaniLinksEnabled" class="space-y-3">
                            <div>
                                <label
                                    for="l4r_gyanilinks_api_key"
                                    class="block text-sm font-medium text-gray-400 mb-1"
                                >
                                    API Key
                                </label>
                                <input
                                    id="l4r_gyanilinks_api_key"
                                    type="text"
                                    v-model="formData.l4r_gyanilinks_api_key"
                                    @change="updateSetting('l4r_gyanilinks_api_key', formData.l4r_gyanilinks_api_key)"
                                    class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                                />
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label
                                        for="l4r_gyanilinks_coins_per_link"
                                        class="block text-sm font-medium text-gray-400 mb-1"
                                    >
                                        Coins Per Link
                                    </label>
                                    <input
                                        id="l4r_gyanilinks_coins_per_link"
                                        type="number"
                                        v-model="formData.l4r_gyanilinks_coins_per_link"
                                        @change="
                                            updateSetting(
                                                'l4r_gyanilinks_coins_per_link',
                                                formData.l4r_gyanilinks_coins_per_link,
                                            )
                                        "
                                        class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                                    />
                                </div>

                                <div>
                                    <label
                                        for="l4r_gyanilinks_daily_limit"
                                        class="block text-sm font-medium text-gray-400 mb-1"
                                    >
                                        Daily Limit
                                    </label>
                                    <input
                                        id="l4r_gyanilinks_daily_limit"
                                        type="number"
                                        v-model="formData.l4r_gyanilinks_daily_limit"
                                        @change="
                                            updateSetting(
                                                'l4r_gyanilinks_daily_limit',
                                                formData.l4r_gyanilinks_daily_limit,
                                            )
                                        "
                                        class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                                    />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label
                                        for="l4r_gyanilinks_min_time_to_complete"
                                        class="block text-sm font-medium text-gray-400 mb-1"
                                    >
                                        Min Time to Complete (sec)
                                    </label>
                                    <input
                                        id="l4r_gyanilinks_min_time_to_complete"
                                        type="number"
                                        v-model="formData.l4r_gyanilinks_min_time_to_complete"
                                        @change="
                                            updateSetting(
                                                'l4r_gyanilinks_min_time_to_complete',
                                                formData.l4r_gyanilinks_min_time_to_complete,
                                            )
                                        "
                                        class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                                    />
                                </div>

                                <div>
                                    <label
                                        for="l4r_gyanilinks_time_to_expire"
                                        class="block text-sm font-medium text-gray-400 mb-1"
                                    >
                                        Time to Expire (sec)
                                    </label>
                                    <input
                                        id="l4r_gyanilinks_time_to_expire"
                                        type="number"
                                        v-model="formData.l4r_gyanilinks_time_to_expire"
                                        @change="
                                            updateSetting(
                                                'l4r_gyanilinks_time_to_expire',
                                                formData.l4r_gyanilinks_time_to_expire,
                                            )
                                        "
                                        class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                                    />
                                </div>
                            </div>

                            <div>
                                <label
                                    for="l4r_gyanilinks_cooldown_time"
                                    class="block text-sm font-medium text-gray-400 mb-1"
                                >
                                    Cooldown Time (sec)
                                </label>
                                <input
                                    id="l4r_gyanilinks_cooldown_time"
                                    type="number"
                                    v-model="formData.l4r_gyanilinks_cooldown_time"
                                    @change="
                                        updateSetting(
                                            'l4r_gyanilinks_cooldown_time',
                                            formData.l4r_gyanilinks_cooldown_time,
                                        )
                                    "
                                    class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                                />
                            </div>
                        </div>
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

                <div v-if="storeEnabled" class="space-y-4 mt-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="store_ram_price" class="block text-sm font-medium text-gray-400 mb-1">
                                RAM Price
                            </label>
                            <input
                                id="store_ram_price"
                                type="number"
                                v-model="formData.store_ram_price"
                                @change="updateSetting('store_ram_price', formData.store_ram_price)"
                                class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                            />
                        </div>

                        <div>
                            <label for="store_disk_price" class="block text-sm font-medium text-gray-400 mb-1">
                                Disk Price
                            </label>
                            <input
                                id="store_disk_price"
                                type="number"
                                v-model="formData.store_disk_price"
                                @change="updateSetting('store_disk_price', formData.store_disk_price)"
                                class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                            />
                        </div>

                        <div>
                            <label for="store_cpu_price" class="block text-sm font-medium text-gray-400 mb-1">
                                CPU Price
                            </label>
                            <input
                                id="store_cpu_price"
                                type="number"
                                v-model="formData.store_cpu_price"
                                @change="updateSetting('store_cpu_price', formData.store_cpu_price)"
                                class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                            />
                        </div>

                        <div>
                            <label for="store_ports_price" class="block text-sm font-medium text-gray-400 mb-1">
                                Ports Price
                            </label>
                            <input
                                id="store_ports_price"
                                type="number"
                                v-model="formData.store_ports_price"
                                @change="updateSetting('store_ports_price', formData.store_ports_price)"
                                class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                            />
                        </div>

                        <div>
                            <label for="store_databases_price" class="block text-sm font-medium text-gray-400 mb-1">
                                Databases Price
                            </label>
                            <input
                                id="store_databases_price"
                                type="number"
                                v-model="formData.store_databases_price"
                                @change="updateSetting('store_databases_price', formData.store_databases_price)"
                                class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                            />
                        </div>

                        <div>
                            <label for="store_backups_price" class="block text-sm font-medium text-gray-400 mb-1">
                                Backups Price
                            </label>
                            <input
                                id="store_backups_price"
                                type="number"
                                v-model="formData.store_backups_price"
                                @change="updateSetting('store_backups_price', formData.store_backups_price)"
                                class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                            />
                        </div>

                        <div>
                            <label for="store_server_slot_price" class="block text-sm font-medium text-gray-400 mb-1">
                                Server Slot Price
                            </label>
                            <input
                                id="store_server_slot_price"
                                type="number"
                                v-model="formData.store_server_slot_price"
                                @change="updateSetting('store_server_slot_price', formData.store_server_slot_price)"
                                class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                            />
                        </div>
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
    store_ram_price: '100',
    store_disk_price: '100',
    store_cpu_price: '100',
    store_ports_price: '100',
    store_databases_price: '100',
    store_backups_price: '100',
    store_server_slot_price: '100',
    l4r_linkadvertise_user_id: '',
    l4r_linkadvertise_coins_per_link: '100',
    l4r_linkadvertise_daily_limit: '5',
    l4r_linkadvertise_min_time_to_complete: '60',
    l4r_linkadvertise_time_to_expire: '3600',
    l4r_linkadvertise_cooldown_time: '3600',
    l4r_shareus_api_key: '',
    l4r_shareus_coins_per_link: '100',
    l4r_shareus_daily_limit: '5',
    l4r_shareus_min_time_to_complete: '60',
    l4r_shareus_time_to_expire: '3600',
    l4r_shareus_cooldown_time: '3600',
    l4r_linkpays_api_key: '',
    l4r_linkpays_coins_per_link: '100',
    l4r_linkpays_daily_limit: '5',
    l4r_linkpays_min_time_to_complete: '60',
    l4r_linkpays_time_to_expire: '3600',
    l4r_linkpays_cooldown_time: '3600',
    l4r_gyanilinks_api_key: '',
    l4r_gyanilinks_coins_per_link: '100',
    l4r_gyanilinks_daily_limit: '5',
    l4r_gyanilinks_min_time_to_complete: '60',
    l4r_gyanilinks_time_to_expire: '3600',
    l4r_gyanilinks_cooldown_time: '3600',
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

// Add the computed properties for the L4R shortener services
const l4rLinkAdvertiseEnabled = computed({
    get: () => props.settings?.l4r_linkadvertise_enabled === 'true',
    set: (value) => {
        emit('update', 'l4r_linkadvertise_enabled', value ? 'true' : 'false');
    },
});

const l4rShareUsEnabled = computed({
    get: () => props.settings?.l4r_shareus_enabled === 'true',
    set: (value) => {
        emit('update', 'l4r_shareus_enabled', value ? 'true' : 'false');
    },
});

const l4rLinkPaysEnabled = computed({
    get: () => props.settings?.l4r_linkpays_enabled === 'true',
    set: (value) => {
        emit('update', 'l4r_linkpays_enabled', value ? 'true' : 'false');
    },
});

const l4rGyaniLinksEnabled = computed({
    get: () => props.settings?.l4r_gyanilinks_enabled === 'true',
    set: (value) => {
        emit('update', 'l4r_gyanilinks_enabled', value ? 'true' : 'false');
    },
});

// Initialize form with settings values
watch(
    () => props.settings,
    (newSettings) => {
        if (newSettings) {
            formData.value = {
                afk_min_per_coin: newSettings['afk_min_per_coin'] || '5',
                store_ram_price: newSettings['store_ram_price'] || '100',
                store_disk_price: newSettings['store_disk_price'] || '100',
                store_cpu_price: newSettings['store_cpu_price'] || '100',
                store_ports_price: newSettings['store_ports_price'] || '100',
                store_databases_price: newSettings['store_databases_price'] || '100',
                store_backups_price: newSettings['store_backups_price'] || '100',
                store_server_slot_price: newSettings['store_server_slot_price'] || '100',
                l4r_linkadvertise_user_id: newSettings['l4r_linkadvertise_user_id'] || '',
                l4r_linkadvertise_coins_per_link: newSettings['l4r_linkadvertise_coins_per_link'] || '100',
                l4r_linkadvertise_daily_limit: newSettings['l4r_linkadvertise_daily_limit'] || '5',
                l4r_linkadvertise_min_time_to_complete: newSettings['l4r_linkadvertise_min_time_to_complete'] || '60',
                l4r_linkadvertise_time_to_expire: newSettings['l4r_linkadvertise_time_to_expire'] || '3600',
                l4r_linkadvertise_cooldown_time: newSettings['l4r_linkadvertise_cooldown_time'] || '3600',
                l4r_shareus_api_key: newSettings['l4r_shareus_api_key'] || '',
                l4r_shareus_coins_per_link: newSettings['l4r_shareus_coins_per_link'] || '100',
                l4r_shareus_daily_limit: newSettings['l4r_shareus_daily_limit'] || '5',
                l4r_shareus_min_time_to_complete: newSettings['l4r_shareus_min_time_to_complete'] || '60',
                l4r_shareus_time_to_expire: newSettings['l4r_shareus_time_to_expire'] || '3600',
                l4r_shareus_cooldown_time: newSettings['l4r_shareus_cooldown_time'] || '3600',
                l4r_linkpays_api_key: newSettings['l4r_linkpays_api_key'] || '',
                l4r_linkpays_coins_per_link: newSettings['l4r_linkpays_coins_per_link'] || '100',
                l4r_linkpays_daily_limit: newSettings['l4r_linkpays_daily_limit'] || '5',
                l4r_linkpays_min_time_to_complete: newSettings['l4r_linkpays_min_time_to_complete'] || '60',
                l4r_linkpays_time_to_expire: newSettings['l4r_linkpays_time_to_expire'] || '3600',
                l4r_linkpays_cooldown_time: newSettings['l4r_linkpays_cooldown_time'] || '3600',
                l4r_gyanilinks_api_key: newSettings['l4r_gyanilinks_api_key'] || '',
                l4r_gyanilinks_coins_per_link: newSettings['l4r_gyanilinks_coins_per_link'] || '100',
                l4r_gyanilinks_daily_limit: newSettings['l4r_gyanilinks_daily_limit'] || '5',
                l4r_gyanilinks_min_time_to_complete: newSettings['l4r_gyanilinks_min_time_to_complete'] || '60',
                l4r_gyanilinks_time_to_expire: newSettings['l4r_gyanilinks_time_to_expire'] || '3600',
                l4r_gyanilinks_cooldown_time: newSettings['l4r_gyanilinks_cooldown_time'] || '3600',
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
