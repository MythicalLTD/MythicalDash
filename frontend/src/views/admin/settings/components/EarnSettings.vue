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

                <div v-if="referralsEnabled" class="space-y-4 mt-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label
                                for="referrals_coins_per_referral"
                                class="block text-sm font-medium text-gray-400 mb-1"
                            >
                                Coins for Referrer
                            </label>
                            <input
                                id="referrals_coins_per_referral"
                                type="number"
                                v-model="formData.referrals_coins_per_referral"
                                @change="
                                    updateSetting('referrals_coins_per_referral', formData.referrals_coins_per_referral)
                                "
                                class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                            />
                            <p class="mt-1 text-xs text-gray-500">
                                Number of coins the referrer gets when someone uses their code
                            </p>
                        </div>

                        <div>
                            <label
                                for="referrals_coins_per_referral_redeemer"
                                class="block text-sm font-medium text-gray-400 mb-1"
                            >
                                Coins for Redeemer
                            </label>
                            <input
                                id="referrals_coins_per_referral_redeemer"
                                type="number"
                                v-model="formData.referrals_coins_per_referral_redeemer"
                                @change="
                                    updateSetting(
                                        'referrals_coins_per_referral_redeemer',
                                        formData.referrals_coins_per_referral_redeemer,
                                    )
                                "
                                class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                            />
                            <p class="mt-1 text-xs text-gray-500">
                                Number of coins the redeemer gets when using a referral code
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Early Supporters -->
            <div class="bg-gray-800/30 p-5 rounded-lg border border-gray-700">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-lg font-medium text-white">Early Supporters</h3>
                        <p class="text-sm text-gray-400">Configure early supporter rewards and limits.</p>
                    </div>
                    <div class="ml-4 flex items-center">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input
                                type="checkbox"
                                v-model="earlySupportersEnabled"
                                class="sr-only peer"
                                @change="
                                    updateSetting('early_supporters_enabled', earlySupportersEnabled ? 'true' : 'false')
                                "
                            />
                            <div
                                class="w-11 h-6 bg-gray-700 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-pink-500 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-gradient-to-r peer-checked:from-pink-500 peer-checked:to-violet-500"
                            ></div>
                        </label>
                    </div>
                </div>

                <div v-if="earlySupportersEnabled" class="space-y-4 mt-4">
                    <div>
                        <label for="early_supporters_amount" class="block text-sm font-medium text-gray-400 mb-1">
                            Number of Early Supporters
                        </label>
                        <input
                            id="early_supporters_amount"
                            type="number"
                            v-model="formData.early_supporters_amount"
                            @change="updateSetting('early_supporters_amount', formData.early_supporters_amount)"
                            class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                        />
                        <p class="mt-1 text-xs text-gray-500">
                            The maximum number of users who can become early supporters.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Server Renewals -->
            <div class="bg-gray-800/30 p-5 rounded-lg border border-gray-700">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-lg font-medium text-white">Server Renewals</h3>
                        <p class="text-sm text-gray-400">Configure server renewal settings for users.</p>
                    </div>
                    <div class="ml-4 flex items-center">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input
                                type="checkbox"
                                v-model="serverRenewEnabled"
                                class="sr-only peer"
                                @change="updateSetting('server_renew_enabled', serverRenewEnabled ? 'true' : 'false')"
                            />
                            <div
                                class="w-11 h-6 bg-gray-700 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-pink-500 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-gradient-to-r peer-checked:from-pink-500 peer-checked:to-violet-500"
                            ></div>
                        </label>
                    </div>
                </div>

                <div v-if="serverRenewEnabled" class="space-y-4 mt-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="server_renew_cost" class="block text-sm font-medium text-gray-400 mb-1">
                                Renewal Cost (coins)
                            </label>
                            <input
                                id="server_renew_cost"
                                type="number"
                                v-model="formData.server_renew_cost"
                                @change="updateSetting('server_renew_cost', formData.server_renew_cost)"
                                class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                            />
                            <p class="mt-1 text-xs text-gray-500">Cost in coins to renew a server</p>
                        </div>

                        <div>
                            <label for="server_renew_days" class="block text-sm font-medium text-gray-400 mb-1">
                                Renewal Period (days)
                            </label>
                            <input
                                id="server_renew_days"
                                type="number"
                                v-model="formData.server_renew_days"
                                @change="updateSetting('server_renew_days', formData.server_renew_days)"
                                class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                            />
                            <p class="mt-1 text-xs text-gray-500">Number of days added when renewing a server</p>
                        </div>
                    </div>

                    <div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input
                                type="checkbox"
                                v-model="serverRenewSendMail"
                                class="sr-only peer"
                                @change="
                                    updateSetting('server_renew_send_mail', serverRenewSendMail ? 'true' : 'false')
                                "
                            />
                            <div
                                class="w-11 h-6 bg-gray-700 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-pink-500 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-gradient-to-r peer-checked:from-pink-500 peer-checked:to-violet-500"
                            ></div>
                            <span class="ml-3 text-sm font-medium text-gray-400">Send Email Notifications</span>
                        </label>
                        <p class="mt-1 text-xs text-gray-500">
                            Send email notifications when a server is about to expire
                        </p>
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

            <!-- Default Resources -->
            <div class="bg-gray-800/30 p-5 rounded-lg border border-gray-700">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-lg font-medium text-white">Default Resources</h3>
                        <p class="text-sm text-gray-400">Configure default resource limits for users.</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="default_ram" class="block text-sm font-medium text-gray-400 mb-1">RAM</label>
                        <input
                            id="default_ram"
                            type="number"
                            v-model="formData.default_ram"
                            @change="updateSetting('default_ram', formData.default_ram)"
                            class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                        />
                        <p class="mt-1 text-xs text-gray-500">The default amount of RAM for new users.</p>
                    </div>
                    <div>
                        <label for="default_disk" class="block text-sm font-medium text-gray-400 mb-1">Disk</label>
                        <input
                            id="default_disk"
                            type="number"
                            v-model="formData.default_disk"
                            @change="updateSetting('default_disk', formData.default_disk)"
                            class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                        />
                        <p class="mt-1 text-xs text-gray-500">The default amount of disk for new users.</p>
                    </div>
                    <div>
                        <label for="default_cpu" class="block text-sm font-medium text-gray-400 mb-1">CPU</label>
                        <input
                            id="default_cpu"
                            type="number"
                            v-model="formData.default_cpu"
                            @change="updateSetting('default_cpu', formData.default_cpu)"
                            class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                        />
                        <p class="mt-1 text-xs text-gray-500">The default amount of CPU for new users.</p>
                    </div>
                    <div>
                        <label for="default_ports" class="block text-sm font-medium text-gray-400 mb-1">Ports</label>
                        <input
                            id="default_ports"
                            type="number"
                            v-model="formData.default_ports"
                            @change="updateSetting('default_ports', formData.default_ports)"
                            class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                        />
                        <p class="mt-1 text-xs text-gray-500">The default number of ports for new users.</p>
                    </div>
                    <div>
                        <label for="default_databases" class="block text-sm font-medium text-gray-400 mb-1"
                            >Databases</label
                        >
                        <input
                            id="default_databases"
                            type="number"
                            v-model="formData.default_databases"
                            @change="updateSetting('default_databases', formData.default_databases)"
                            class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                        />
                        <p class="mt-1 text-xs text-gray-500">The default number of databases for new users.</p>
                    </div>
                    <div>
                        <label for="default_server_slots" class="block text-sm font-medium text-gray-400 mb-1"
                            >Server Slots</label
                        >
                        <input
                            id="default_server_slots"
                            type="number"
                            v-model="formData.default_server_slots"
                            @change="updateSetting('default_server_slots', formData.default_server_slots)"
                            class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                        />
                        <p class="mt-1 text-xs text-gray-500">The default number of server slots for new users.</p>
                    </div>
                    <div>
                        <label for="default_backups" class="block text-sm font-medium text-gray-400 mb-1"
                            >Backups</label
                        >
                        <input
                            id="default_backups"
                            type="number"
                            v-model="formData.default_backups"
                            @change="updateSetting('default_backups', formData.default_backups)"
                            class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                        />
                        <p class="mt-1 text-xs text-gray-500">The default number of backups for new users.</p>
                    </div>
                </div>
            </div>

            <!-- Block Resources -->
            <div class="bg-gray-800/30 p-5 rounded-lg border border-gray-700">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-lg font-medium text-white">Block Resources</h3>
                        <p class="text-sm text-gray-400">Configure resource limits for users.</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="block_ram" class="block text-sm font-medium text-gray-400 mb-1">RAM</label>
                        <input
                            id="block_ram"
                            type="checkbox"
                            v-model="formData.block_ram"
                            @change="updateSetting('block_ram', formData.block_ram)"
                        />
                        <p class="mt-1 text-xs text-gray-500">Block users from purchasing RAM.</p>
                    </div>
                    <div>
                        <label for="block_disk" class="block text-sm font-medium text-gray-400 mb-1">Disk</label>
                        <input
                            id="block_disk"
                            type="checkbox"
                            v-model="formData.block_disk"
                            @change="updateSetting('block_disk', formData.block_disk)"
                        />
                        <p class="mt-1 text-xs text-gray-500">Block users from purchasing disk.</p>
                    </div>
                    <div>
                        <label for="block_cpu" class="block text-sm font-medium text-gray-400 mb-1">CPU</label>
                        <input
                            id="block_cpu"
                            type="checkbox"
                            v-model="formData.block_cpu"
                            @change="updateSetting('block_cpu', formData.block_cpu)"
                        />
                        <p class="mt-1 text-xs text-gray-500">Block users from purchasing CPU.</p>
                    </div>
                    <div>
                        <label for="block_ports" class="block text-sm font-medium text-gray-400 mb-1">Ports</label>
                        <input
                            id="block_ports"
                            type="checkbox"
                            v-model="formData.block_ports"
                            @change="updateSetting('block_ports', formData.block_ports)"
                        />
                        <p class="mt-1 text-xs text-gray-500">Block users from purchasing ports.</p>
                    </div>
                    <div>
                        <label for="block_backups" class="block text-sm font-medium text-gray-400 mb-1">Backups</label>
                        <input
                            id="block_backups"
                            type="checkbox"
                            v-model="formData.block_backups"
                            @change="updateSetting('block_backups', formData.block_backups)"
                        />
                        <p class="mt-1 text-xs text-gray-500">Block users from purchasing backups.</p>
                    </div>
                    <div>
                        <label for="block_databases" class="block text-sm font-medium text-gray-400 mb-1"
                            >Databases</label
                        >
                        <input
                            id="block_databases"
                            type="checkbox"
                            v-model="formData.block_databases"
                            @change="updateSetting('block_databases', formData.block_databases)"
                        />
                        <p class="mt-1 text-xs text-gray-500">Block users from purchasing databases.</p>
                    </div>
                    <div>
                        <label for="block_server_slots" class="block text-sm font-medium text-gray-400 mb-1"
                            >Server Slots</label
                        >
                        <input
                            id="block_server_slots"
                            type="checkbox"
                            v-model="formData.block_server_slots"
                            @change="updateSetting('block_server_slots', formData.block_server_slots)"
                        />
                        <p class="mt-1 text-xs text-gray-500">Block users from purchasing server slots.</p>
                    </div>
                </div>
            </div>

            <!-- Max Resources -->
            <div class="bg-gray-800/30 p-5 rounded-lg border border-gray-700">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-lg font-medium text-white">Max Resources</h3>
                        <p class="text-sm text-gray-400">Configure maximum resource limits for users.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="max_ram" class="block text-sm font-medium text-gray-400 mb-1"> Max RAM (MB) </label>
                        <input
                            id="max_ram"
                            type="number"
                            v-model="formData.max_ram"
                            @change="updateSetting('max_ram', formData.max_ram)"
                            class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                        />
                    </div>

                    <div>
                        <label for="max_disk" class="block text-sm font-medium text-gray-400 mb-1">
                            Max Disk (MB)
                        </label>
                        <input
                            id="max_disk"
                            type="number"
                            v-model="formData.max_disk"
                            @change="updateSetting('max_disk', formData.max_disk)"
                            class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                        />
                    </div>

                    <div>
                        <label for="max_cpu" class="block text-sm font-medium text-gray-400 mb-1"> Max CPU (%) </label>
                        <input
                            id="max_cpu"
                            type="number"
                            v-model="formData.max_cpu"
                            @change="updateSetting('max_cpu', formData.max_cpu)"
                            class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                        />
                    </div>

                    <div>
                        <label for="max_ports" class="block text-sm font-medium text-gray-400 mb-1"> Max Ports </label>
                        <input
                            id="max_ports"
                            type="number"
                            v-model="formData.max_ports"
                            @change="updateSetting('max_ports', formData.max_ports)"
                            class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                        />
                    </div>

                    <div>
                        <label for="max_databases" class="block text-sm font-medium text-gray-400 mb-1">
                            Max Databases
                        </label>
                        <input
                            id="max_databases"
                            type="number"
                            v-model="formData.max_databases"
                            @change="updateSetting('max_databases', formData.max_databases)"
                            class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                        />
                    </div>

                    <div>
                        <label for="max_server_slots" class="block text-sm font-medium text-gray-400 mb-1">
                            Max Server Slots
                        </label>
                        <input
                            id="max_server_slots"
                            type="number"
                            v-model="formData.max_server_slots"
                            @change="updateSetting('max_server_slots', formData.max_server_slots)"
                            class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                        />
                    </div>

                    <div>
                        <label for="max_backups" class="block text-sm font-medium text-gray-400 mb-1">
                            Max Backups
                        </label>
                        <input
                            id="max_backups"
                            type="number"
                            v-model="formData.max_backups"
                            @change="updateSetting('max_backups', formData.max_backups)"
                            class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, defineEmits } from 'vue';

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
    referrals_enabled: 'true',
    store_enabled: 'false',
    early_supporters_enabled: 'true',
    early_supporters_amount: '10',
    max_ram: '4096',
    max_disk: '10240',
    max_cpu: '100',
    max_ports: '10',
    max_databases: '5',
    max_server_slots: '3',
    max_backups: '5',
    referrals_coins_per_referral: '45',
    referrals_coins_per_referral_redeemer: '25',
    default_ram: '1024',
    default_disk: '1024',
    default_cpu: '100',
    default_ports: '2',
    default_databases: '1',
    default_server_slots: '1',
    default_backups: '5',
    block_ram: 'false',
    block_disk: 'false',
    block_cpu: 'false',
    block_ports: 'false',
    block_databases: 'false',
    block_server_slots: 'false',
    block_backups: 'false',
    server_renew_enabled: 'false',
    server_renew_cost: '100',
    server_renew_days: '30',
    server_renew_send_mail: 'false',
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

const earlySupportersEnabled = computed({
    get: () => props.settings?.early_supporters_enabled === 'true',
    set: (value) => {
        emit('update', 'early_supporters_enabled', value ? 'true' : 'false');
    },
});

// Add server renewal computed properties
const serverRenewEnabled = computed({
    get: () => props.settings?.server_renew_enabled === 'true',
    set: (value) => {
        emit('update', 'server_renew_enabled', value ? 'true' : 'false');
    },
});

const serverRenewSendMail = computed({
    get: () => props.settings?.server_renew_send_mail === 'true',
    set: (value) => {
        emit('update', 'server_renew_send_mail', value ? 'true' : 'false');
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
                referrals_enabled: newSettings['referrals_enabled'] || 'true',
                store_enabled: newSettings['store_enabled'] || 'false',
                early_supporters_enabled: newSettings['early_supporters_enabled'] || 'true',
                early_supporters_amount: newSettings['early_supporters_amount'] || '10',
                max_ram: newSettings['max_ram'] || '4096',
                max_disk: newSettings['max_disk'] || '10240',
                max_cpu: newSettings['max_cpu'] || '100',
                max_ports: newSettings['max_ports'] || '10',
                max_databases: newSettings['max_databases'] || '5',
                max_server_slots: newSettings['max_server_slots'] || '3',
                max_backups: newSettings['max_backups'] || '5',
                referrals_coins_per_referral: newSettings['referrals_coins_per_referral'] || '45',
                referrals_coins_per_referral_redeemer: newSettings['referrals_coins_per_referral_redeemer'] || '25',
                default_ram: newSettings['default_ram'] || '1024',
                default_disk: newSettings['default_disk'] || '1024',
                default_cpu: newSettings['default_cpu'] || '100',
                default_ports: newSettings['default_ports'] || '2',
                default_databases: newSettings['default_databases'] || '1',
                default_server_slots: newSettings['default_server_slots'] || '1',
                default_backups: newSettings['default_backups'] || '5',
                block_ram: newSettings['block_ram'] || 'false',
                block_disk: newSettings['block_disk'] || 'false',
                block_cpu: newSettings['block_cpu'] || 'false',
                block_ports: newSettings['block_ports'] || 'false',
                block_databases: newSettings['block_databases'] || 'false',
                block_server_slots: newSettings['block_server_slots'] || 'false',
                block_backups: newSettings['block_backups'] || 'false',
                server_renew_enabled: newSettings['server_renew_enabled'] || 'false',
                server_renew_cost: newSettings['server_renew_cost'] || '100',
                server_renew_days: newSettings['server_renew_days'] || '30',
                server_renew_send_mail: newSettings['server_renew_send_mail'] || 'false',
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
