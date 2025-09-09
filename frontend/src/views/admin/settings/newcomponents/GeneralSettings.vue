<template>
    <div>
        <h2 class="text-xl font-semibold text-white mb-4">General Settings</h2>

        <div class="space-y-6">
            <!-- App Name -->
            <div>
                <label for="app_name" class="block text-sm font-medium text-gray-400 mb-1">App Name</label>
                <input
                    id="app_name"
                    type="text"
                    v-model="formData.app_name"
                    @input="markChanged('app_name')"
                    class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                />
                <p class="mt-1 text-xs text-gray-500">
                    The name of your application displayed in the browser title and throughout the dashboard.
                </p>
            </div>

            <!-- App Language -->
            <div>
                <label for="app_lang" class="block text-sm font-medium text-gray-400 mb-1">Default Language</label>
                <select
                    id="app_lang"
                    v-model="formData.app_lang"
                    disabled
                    class="bg-gray-700/50 border border-gray-600 rounded-lg px-4 py-2 w-full text-gray-400 cursor-not-allowed"
                >
                    <option value="en" selected>English</option>
                </select>
                <p class="mt-1 text-xs text-gray-500">
                    The default language for the dashboard. Users can change their individual language preference.
                </p>
            </div>

            <!-- App URL -->
            <div>
                <label for="app_url" class="block text-sm font-medium text-gray-400 mb-1">App URL</label>
                <input
                    id="app_url"
                    type="url"
                    v-model="formData.app_url"
                    @input="markChanged('app_url')"
                    class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                    placeholder="https://yourdomain.com"
                />
                <p class="mt-1 text-xs text-gray-500">
                    The URL of your application. Must NOT include http:// or https://.
                </p>
            </div>

            <!-- App Timezone -->
            <div>
                <label for="app_timezone" class="block text-sm font-medium text-gray-400 mb-1">Timezone</label>
                <select
                    id="app_timezone"
                    v-model="formData.app_timezone"
                    @change="markChanged('app_timezone')"
                    class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                >
                    <optgroup v-for="(zones, continent) in groupedTimezones" :key="continent" :label="continent">
                        <option v-for="zone in zones" :key="zone" :value="zone">
                            {{ formatTimezone(zone) }}
                        </option>
                    </optgroup>
                </select>
                <p class="mt-1 text-xs text-gray-500">
                    The timezone for your application. This affects how dates and times are displayed.
                </p>
            </div>

            <!-- App Logo -->
            <div>
                <label for="app_logo" class="block text-sm font-medium text-gray-400 mb-1">App Logo URL</label>
                <div class="flex gap-4">
                    <input
                        id="app_logo"
                        type="url"
                        v-model="formData.app_logo"
                        @input="markChanged('app_logo')"
                        class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                        placeholder="https://yourdomain.com/logo.png"
                    />
                    <div class="h-10 w-10 bg-gray-800 rounded-lg flex items-center justify-center">
                        <img
                            v-if="formData.app_logo"
                            :src="formData.app_logo"
                            alt="Logo Preview"
                            class="max-h-8 max-w-8"
                        />
                        <ImageIcon v-else class="h-5 w-5 text-gray-500" />
                    </div>
                </div>
                <p class="mt-1 text-xs text-gray-500">URL for your application logo. Recommended size: 512x512px.</p>
            </div>

            <!-- Default User Background -->
            <div>
                <label for="default_bg" class="block text-sm font-medium text-gray-400 mb-1"
                    >Default User Background</label
                >
                <div class="flex gap-4">
                    <input
                        id="default_bg"
                        type="url"
                        v-model="formData.default_bg"
                        @input="markChanged('default_bg')"
                        class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                        placeholder="https://yourdomain.com/default-bg.jpg"
                    />
                    <div class="h-10 w-32 bg-gray-800 rounded-lg flex items-center justify-center overflow-hidden">
                        <img
                            v-if="formData.default_bg"
                            :src="formData.default_bg"
                            alt="Background Preview"
                            class="h-full w-full object-cover"
                        />
                        <ImageIcon v-else class="h-5 w-5 text-gray-500" />
                    </div>
                </div>
                <p class="mt-1 text-xs text-gray-500">
                    Default background image for new user profiles. Recommended size: 1920x1080px.
                </p>
            </div>

            <!-- SEO Settings -->
            <div class="pt-4 border-t border-gray-700">
                <div class="mb-4 p-4 bg-yellow-500/10 border border-yellow-500/30 rounded-lg">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path
                                    fill-rule="evenodd"
                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-400">SEO Settings Disabled</h3>
                            <div class="mt-2 text-sm text-yellow-300">
                                <p class="mb-2">
                                    <strong>CSR vs SSR Explanation:</strong> This application uses Client-Side Rendering
                                    (CSR) instead of Server-Side Rendering (SSR). In CSR, the browser downloads a
                                    minimal HTML file and then JavaScript renders the content dynamically. This means
                                    search engines cannot see the SEO meta tags that are generated by JavaScript.
                                </p>
                                <p class="mb-2">
                                    <strong>Why SEO settings are disabled:</strong> Since the app is CSR-based, changing
                                    SEO settings through the admin panel won't affect search engine visibility. The meta
                                    tags are generated on the client-side and are not available to search engine
                                    crawlers.
                                </p>
                                <p class="font-medium">
                                    <strong>To change SEO settings:</strong> You must manually edit the SEO
                                    configuration on the server by running:
                                    <code class="bg-gray-800 px-2 py-1 rounded text-xs font-mono"
                                        >php mythicaldash ApplySEO</code
                                    >
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SEO Description -->
                <div class="mb-4">
                    <label for="seo_description" class="block text-sm font-medium text-gray-400 mb-1"
                        >SEO Description</label
                    >
                    <textarea
                        id="seo_description"
                        v-model="formData.seo_description"
                        disabled
                        rows="3"
                        class="bg-gray-700/50 border border-gray-600 rounded-lg px-4 py-2 w-full text-gray-400 cursor-not-allowed"
                        placeholder="SEO settings are disabled due to CSR architecture"
                    ></textarea>
                    <p class="mt-1 text-xs text-gray-500">
                        This field is disabled. Use
                        <code class="bg-gray-800 px-1 py-0.5 rounded text-xs">php mythicaldash ApplySEO</code> on the
                        server to modify SEO settings.
                    </p>
                </div>

                <!-- SEO Keywords -->
                <div>
                    <label for="seo_keywords" class="block text-sm font-medium text-gray-400 mb-1">SEO Keywords</label>
                    <input
                        id="seo_keywords"
                        type="text"
                        v-model="formData.seo_keywords"
                        disabled
                        class="bg-gray-700/50 border border-gray-600 rounded-lg px-4 py-2 w-full text-gray-400 cursor-not-allowed"
                        placeholder="SEO settings are disabled due to CSR architecture"
                    />
                    <p class="mt-1 text-xs text-gray-500">
                        This field is disabled. Use
                        <code class="bg-gray-800 px-1 py-0.5 rounded text-xs">php mythicaldash ApplySEO</code> on the
                        server to modify SEO settings.
                    </p>
                </div>
            </div>

            <!-- Feature Toggles -->
            <div class="pt-4 border-t border-gray-700">
                <h3 class="text-lg font-medium text-white mb-3">Feature Toggles</h3>

                <div class="space-y-4">
                    <!-- Leaderboard -->
                    <div class="flex items-center justify-between p-4 bg-gray-800/30 rounded-lg border border-gray-700">
                        <div class="flex-1">
                            <h4 class="font-medium text-white">Leaderboard</h4>
                            <p class="text-sm text-gray-400">Enable the public leaderboard feature</p>
                        </div>
                        <input
                            type="checkbox"
                            v-model="formData.leaderboard_enabled"
                            @change="markChanged('leaderboard_enabled')"
                            class="rounded border-gray-700 text-indigo-500 focus:ring-indigo-500"
                        />
                    </div>

                    <!-- Tickets -->
                    <div class="flex items-center justify-between p-4 bg-gray-800/30 rounded-lg border border-gray-700">
                        <div class="flex-1">
                            <h4 class="font-medium text-white">Support Tickets</h4>
                            <p class="text-sm text-gray-400">Allow users to create support tickets</p>
                        </div>
                        <input
                            type="checkbox"
                            v-model="formData.allow_tickets"
                            @change="markChanged('allow_tickets')"
                            class="rounded border-gray-700 text-indigo-500 focus:ring-indigo-500"
                        />
                    </div>

                    <!-- Servers -->
                    <div class="flex items-center justify-between p-4 bg-gray-800/30 rounded-lg border border-gray-700">
                        <div class="flex-1">
                            <h4 class="font-medium text-white">Server Management</h4>
                            <p class="text-sm text-gray-400">Allow users to manage their servers</p>
                        </div>
                        <input
                            type="checkbox"
                            v-model="formData.allow_servers"
                            @change="markChanged('allow_servers')"
                            class="rounded border-gray-700 text-indigo-500 focus:ring-indigo-500"
                        />
                    </div>

                    <!-- Public Profiles -->
                    <div class="flex items-center justify-between p-4 bg-gray-800/30 rounded-lg border border-gray-700">
                        <div class="flex-1">
                            <h4 class="font-medium text-white">Public Profiles</h4>
                            <p class="text-sm text-gray-400">Allow users to have public profiles</p>
                        </div>
                        <input
                            type="checkbox"
                            v-model="formData.allow_public_profiles"
                            @change="markChanged('allow_public_profiles')"
                            class="rounded border-gray-700 text-indigo-500 focus:ring-indigo-500"
                        />
                    </div>

                    <!-- Coins Sharing -->
                    <div class="flex items-center justify-between p-4 bg-gray-800/30 rounded-lg border border-gray-700">
                        <div class="flex-1">
                            <h4 class="font-medium text-white">Coins Sharing</h4>
                            <p class="text-sm text-gray-400">Allow users to share coins with each other</p>
                        </div>
                        <input
                            type="checkbox"
                            v-model="formData.allow_coins_sharing"
                            @change="markChanged('allow_coins_sharing')"
                            class="rounded border-gray-700 text-indigo-500 focus:ring-indigo-500"
                        />
                    </div>

                    <!-- Daily Backup -->
                    <div class="flex items-center justify-between p-4 bg-gray-800/30 rounded-lg border border-gray-700">
                        <div class="flex-1">
                            <h4 class="font-medium text-white">Daily Backup</h4>
                            <p class="text-sm text-gray-400">Enable automatic daily database backups</p>
                        </div>
                        <input
                            type="checkbox"
                            v-model="formData.daily_backup_enabled"
                            @change="markChanged('daily_backup_enabled')"
                            class="rounded border-gray-700 text-indigo-500 focus:ring-indigo-500"
                        />
                    </div>

                    <!-- Force 2FA -->
                    <div class="flex items-center justify-between p-4 bg-gray-800/30 rounded-lg border border-gray-700">
                        <div class="flex-1">
                            <h4 class="font-medium text-white">Force 2FA for Admin Users</h4>
                            <p class="text-sm text-gray-400">Require two-factor authentication for all admin users</p>
                        </div>
                        <input
                            type="checkbox"
                            v-model="formData.force_2fa"
                            @change="markChanged('force_2fa')"
                            class="rounded border-gray-700 text-indigo-500 focus:ring-indigo-500"
                        />
                    </div>
                </div>
            </div>

            <!-- Coins Sharing Settings -->
            <div v-if="formData.allow_coins_sharing" class="pt-4 border-t border-gray-700">
                <h3 class="text-lg font-medium text-white mb-3">Coins Sharing Configuration</h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Min Amount -->
                    <div>
                        <label for="coins_share_min_amount" class="block text-sm font-medium text-gray-400 mb-1"
                            >Minimum Amount</label
                        >
                        <input
                            id="coins_share_min_amount"
                            type="number"
                            v-model="formData.coins_share_min_amount"
                            @input="markChanged('coins_share_min_amount')"
                            class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                            min="1"
                        />
                        <p class="mt-1 text-xs text-gray-500">Minimum coins that can be shared</p>
                    </div>

                    <!-- Max Amount -->
                    <div>
                        <label for="coins_share_max_amount" class="block text-sm font-medium text-gray-400 mb-1"
                            >Maximum Amount</label
                        >
                        <input
                            id="coins_share_max_amount"
                            type="number"
                            v-model="formData.coins_share_max_amount"
                            @input="markChanged('coins_share_max_amount')"
                            class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                            min="1"
                        />
                        <p class="mt-1 text-xs text-gray-500">Maximum coins that can be shared</p>
                    </div>

                    <!-- Share Fee -->
                    <div>
                        <label for="coins_share_fee" class="block text-sm font-medium text-gray-400 mb-1"
                            >Share Fee (%)</label
                        >
                        <input
                            id="coins_share_fee"
                            type="number"
                            v-model="formData.coins_share_fee"
                            @input="markChanged('coins_share_fee')"
                            class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                            min="0"
                            max="100"
                            step="0.1"
                        />
                        <p class="mt-1 text-xs text-gray-500">Fee percentage for sharing coins</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { ImageIcon } from 'lucide-vue-next';

// Props
const props = defineProps<{
    settings: Record<string, string>;
}>();

// Emits
const emit = defineEmits<{
    update: [key: string, value: string];
    'bulk-update': [updates: Record<string, string>];
}>();

// Local form data
const formData = ref({
    app_name: '',
    app_lang: 'en',
    app_url: '',
    app_timezone: 'UTC',
    app_logo: '',
    default_bg: '',
    seo_description: '',
    seo_keywords: '',
    leaderboard_enabled: false,
    leaderboard_limit: '15',
    allow_tickets: false,
    allow_servers: false,
    allow_public_profiles: false,
    allow_coins_sharing: false,
    daily_backup_enabled: false,
    force_2fa: false,
    coins_share_max_amount: '100',
    coins_share_min_amount: '1',
    coins_share_fee: '10',
});

// Track changed fields
const changedFields = ref<Set<string>>(new Set());

// Mark a field as changed
const markChanged = (field: string) => {
    changedFields.value.add(field);

    // Emit the change to parent
    const value = formData.value[field as keyof typeof formData.value];
    emit('update', field, typeof value === 'boolean' ? value.toString() : value);
};

// Timezone data
const timezones = [
    'Africa/Abidjan',
    'Africa/Accra',
    'Africa/Addis_Ababa',
    'Africa/Algiers',
    'Africa/Asmara',
    'Africa/Bamako',
    'Africa/Bangui',
    'Africa/Banjul',
    'Africa/Bissau',
    'Africa/Blantyre',
    'Africa/Brazzaville',
    'Africa/Bujumbura',
    'Africa/Cairo',
    'Africa/Casablanca',
    'Africa/Ceuta',
    'Africa/Conakry',
    'Africa/Dakar',
    'Africa/Dar_es_Salaam',
    'Africa/Djibouti',
    'Africa/Douala',
    'Africa/El_Aaiun',
    'Africa/Freetown',
    'Africa/Gaborone',
    'Africa/Harare',
    'Africa/Johannesburg',
    'Africa/Juba',
    'Africa/Kampala',
    'Africa/Khartoum',
    'Africa/Kigali',
    'Africa/Kinshasa',
    'Africa/Lagos',
    'Africa/Libreville',
    'Africa/Lome',
    'Africa/Luanda',
    'Africa/Lubumbashi',
    'Africa/Lusaka',
    'Africa/Malabo',
    'Africa/Maputo',
    'Africa/Maseru',
    'Africa/Mbabane',
    'Africa/Mogadishu',
    'Africa/Monrovia',
    'Africa/Nairobi',
    'Africa/Ndjamena',
    'Africa/Niamey',
    'Africa/Nouakchott',
    'Africa/Ouagadougou',
    'Africa/Porto-Novo',
    'Africa/Sao_Tome',
    'Africa/Tripoli',
    'Africa/Tunis',
    'Africa/Windhoek',
    'America/Adak',
    'America/Anchorage',
    'America/Anguilla',
    'America/Antigua',
    'America/Araguaina',
    'America/Argentina/Buenos_Aires',
    'America/Argentina/Catamarca',
    'America/Argentina/Cordoba',
    'America/Argentina/Jujuy',
    'America/Argentina/La_Rioja',
    'America/Argentina/Mendoza',
    'America/Argentina/Rio_Gallegos',
    'America/Argentina/Salta',
    'America/Argentina/San_Juan',
    'America/Argentina/San_Luis',
    'America/Argentina/Tucuman',
    'America/Argentina/Ushuaia',
    'America/Aruba',
    'America/Asuncion',
    'America/Atikokan',
    'America/Bahia',
    'America/Bahia_Banderas',
    'America/Barbados',
    'America/Belem',
    'America/Belize',
    'America/Blanc-Sablon',
    'America/Boa_Vista',
    'America/Bogota',
    'America/Boise',
    'America/Cambridge_Bay',
    'America/Campo_Grande',
    'America/Cancun',
    'America/Caracas',
    'America/Cayenne',
    'America/Cayman',
    'America/Chicago',
    'America/Chihuahua',
    'America/Costa_Rica',
    'America/Creston',
    'America/Cuiaba',
    'America/Curacao',
    'America/Danmarkshavn',
    'America/Dawson',
    'America/Dawson_Creek',
    'America/Denver',
    'America/Detroit',
    'America/Dominica',
    'America/Edmonton',
    'America/Eirunepe',
    'America/El_Salvador',
    'America/Fort_Nelson',
    'America/Fortaleza',
    'America/Glace_Bay',
    'America/Goose_Bay',
    'America/Grand_Turk',
    'America/Grenada',
    'America/Guadeloupe',
    'America/Guatemala',
    'America/Guayaquil',
    'America/Guyana',
    'America/Halifax',
    'America/Havana',
    'America/Hermosillo',
    'America/Indiana/Indianapolis',
    'America/Indiana/Knox',
    'America/Indiana/Marengo',
    'America/Indiana/Petersburg',
    'America/Indiana/Tell_City',
    'America/Indiana/Vevay',
    'America/Indiana/Vincennes',
    'America/Indiana/Winamac',
    'America/Inuvik',
    'America/Iqaluit',
    'America/Jamaica',
    'America/Juneau',
    'America/Kentucky/Louisville',
    'America/Kentucky/Monticello',
    'America/Kralendijk',
    'America/La_Paz',
    'America/Lima',
    'America/Los_Angeles',
    'America/Lower_Princes',
    'America/Maceio',
    'America/Managua',
    'America/Manaus',
    'America/Marigot',
    'America/Martinique',
    'America/Matamoros',
    'America/Mazatlan',
    'America/Menominee',
    'America/Merida',
    'America/Metlakatla',
    'America/Mexico_City',
    'America/Miquelon',
    'America/Moncton',
    'America/Monterrey',
    'America/Montevideo',
    'America/Montserrat',
    'America/Nassau',
    'America/New_York',
    'America/Nipigon',
    'America/Nome',
    'America/Noronha',
    'America/North_Dakota/Beulah',
    'America/North_Dakota/Center',
    'America/North_Dakota/New_Salem',
    'America/Nuuk',
    'America/Ojinaga',
    'America/Panama',
    'America/Pangnirtung',
    'America/Paramaribo',
    'America/Phoenix',
    'America/Port-au-Prince',
    'America/Port_of_Spain',
    'America/Porto_Velho',
    'America/Puerto_Rico',
    'America/Punta_Arenas',
    'America/Rainy_River',
    'America/Rankin_Inlet',
    'America/Recife',
    'America/Regina',
    'America/Resolute',
    'America/Rio_Branco',
    'America/Santarem',
    'America/Santiago',
    'America/Santo_Domingo',
    'America/Sao_Paulo',
    'America/Scoresbysund',
    'America/Sitka',
    'America/St_Barthelemy',
    'America/St_Johns',
    'America/St_Kitts',
    'America/St_Lucia',
    'America/St_Thomas',
    'America/St_Vincent',
    'America/Swift_Current',
    'America/Tegucigalpa',
    'America/Thule',
    'America/Thunder_Bay',
    'America/Tijuana',
    'America/Toronto',
    'America/Tortola',
    'America/Vancouver',
    'America/Whitehorse',
    'America/Winnipeg',
    'America/Yakutat',
    'America/Yellowknife',
    'Antarctica/Casey',
    'Antarctica/Davis',
    'Antarctica/DumontDUrville',
    'Antarctica/Macquarie',
    'Antarctica/Mawson',
    'Antarctica/McMurdo',
    'Antarctica/Palmer',
    'Antarctica/Rothera',
    'Antarctica/Syowa',
    'Antarctica/Troll',
    'Antarctica/Vostok',
    'Arctic/Longyearbyen',
    'Asia/Aden',
    'Asia/Almaty',
    'Asia/Amman',
    'Asia/Anadyr',
    'Asia/Aqtau',
    'Asia/Aqtobe',
    'Asia/Ashgabat',
    'Asia/Atyrau',
    'Asia/Baghdad',
    'Asia/Bahrain',
    'Asia/Baku',
    'Asia/Bangkok',
    'Asia/Barnaul',
    'Asia/Beirut',
    'Asia/Bishkek',
    'Asia/Brunei',
    'Asia/Chita',
    'Asia/Choibalsan',
    'Asia/Colombo',
    'Asia/Damascus',
    'Asia/Dhaka',
    'Asia/Dili',
    'Asia/Dubai',
    'Asia/Dushanbe',
    'Asia/Famagusta',
    'Asia/Gaza',
    'Asia/Hebron',
    'Asia/Ho_Chi_Minh',
    'Asia/Hong_Kong',
    'Asia/Hovd',
    'Asia/Irkutsk',
    'Asia/Jakarta',
    'Asia/Jayapura',
    'Asia/Jerusalem',
    'Asia/Kabul',
    'Asia/Kamchatka',
    'Asia/Karachi',
    'Asia/Kathmandu',
    'Asia/Khandyga',
    'Asia/Kolkata',
    'Asia/Krasnoyarsk',
    'Asia/Kuala_Lumpur',
    'Asia/Kuching',
    'Asia/Kuwait',
    'Asia/Macau',
    'Asia/Magadan',
    'Asia/Makassar',
    'Asia/Manila',
    'Asia/Muscat',
    'Asia/Nicosia',
    'Asia/Novokuznetsk',
    'Asia/Novosibirsk',
    'Asia/Omsk',
    'Asia/Oral',
    'Asia/Phnom_Penh',
    'Asia/Pontianak',
    'Asia/Pyongyang',
    'Asia/Qatar',
    'Asia/Qostanay',
    'Asia/Qyzylorda',
    'Asia/Riyadh',
    'Asia/Sakhalin',
    'Asia/Samarkand',
    'Asia/Seoul',
    'Asia/Shanghai',
    'Asia/Singapore',
    'Asia/Srednekolymsk',
    'Asia/Taipei',
    'Asia/Tashkent',
    'Asia/Tbilisi',
    'Asia/Tehran',
    'Asia/Thimphu',
    'Asia/Tokyo',
    'Asia/Tomsk',
    'Asia/Ulaanbaatar',
    'Asia/Urumqi',
    'Asia/Ust-Nera',
    'Asia/Vientiane',
    'Asia/Vladivostok',
    'Asia/Yakutsk',
    'Asia/Yangon',
    'Asia/Yekaterinburg',
    'Asia/Yerevan',
    'Atlantic/Azores',
    'Atlantic/Bermuda',
    'Atlantic/Canary',
    'Atlantic/Cape_Verde',
    'Atlantic/Faroe',
    'Atlantic/Madeira',
    'Atlantic/Reykjavik',
    'Atlantic/South_Georgia',
    'Atlantic/St_Helena',
    'Atlantic/Stanley',
    'Australia/Adelaide',
    'Australia/Brisbane',
    'Australia/Broken_Hill',
    'Australia/Darwin',
    'Australia/Eucla',
    'Australia/Hobart',
    'Australia/Lindeman',
    'Australia/Lord_Howe',
    'Australia/Melbourne',
    'Australia/Perth',
    'Australia/Sydney',
    'Europe/Amsterdam',
    'Europe/Andorra',
    'Europe/Astrakhan',
    'Europe/Athens',
    'Europe/Belgrade',
    'Europe/Berlin',
    'Europe/Bratislava',
    'Europe/Brussels',
    'Europe/Bucharest',
    'Europe/Budapest',
    'Europe/Chisinau',
    'Europe/Copenhagen',
    'Europe/Dublin',
    'Europe/Gibraltar',
    'Europe/Guernsey',
    'Europe/Helsinki',
    'Europe/Isle_of_Man',
    'Europe/Istanbul',
    'Europe/Jersey',
    'Europe/Kaliningrad',
    'Europe/Kiev',
    'Europe/Kirov',
    'Europe/Lisbon',
    'Europe/Ljubljana',
    'Europe/London',
    'Europe/Luxembourg',
    'Europe/Madrid',
    'Europe/Malta',
    'Europe/Mariehamn',
    'Europe/Minsk',
    'Europe/Monaco',
    'Europe/Moscow',
    'Europe/Oslo',
    'Europe/Paris',
    'Europe/Podgorica',
    'Europe/Prague',
    'Europe/Riga',
    'Europe/Rome',
    'Europe/Samara',
    'Europe/San_Marino',
    'Europe/Sarajevo',
    'Europe/Saratov',
    'Europe/Simferopol',
    'Europe/Skopje',
    'Europe/Sofia',
    'Europe/Stockholm',
    'Europe/Tallinn',
    'Europe/Tirane',
    'Europe/Ulyanovsk',
    'Europe/Uzhgorod',
    'Europe/Vaduz',
    'Europe/Vatican',
    'Europe/Vienna',
    'Europe/Vilnius',
    'Europe/Volgograd',
    'Europe/Warsaw',
    'Europe/Zagreb',
    'Europe/Zaporozhye',
    'Europe/Zurich',
    'Indian/Antananarivo',
    'Indian/Chagos',
    'Indian/Christmas',
    'Indian/Cocos',
    'Indian/Comoro',
    'Indian/Kerguelen',
    'Indian/Mahe',
    'Indian/Maldives',
    'Indian/Mauritius',
    'Indian/Mayotte',
    'Indian/Reunion',
    'Pacific/Apia',
    'Pacific/Auckland',
    'Pacific/Bougainville',
    'Pacific/Chatham',
    'Pacific/Chuuk',
    'Pacific/Easter',
    'Pacific/Efate',
    'Pacific/Enderbury',
    'Pacific/Fakaofo',
    'Pacific/Fiji',
    'Pacific/Funafuti',
    'Pacific/Galapagos',
    'Pacific/Gambier',
    'Pacific/Guadalcanal',
    'Pacific/Guam',
    'Pacific/Honolulu',
    'Pacific/Kiritimati',
    'Pacific/Kosrae',
    'Pacific/Kwajalein',
    'Pacific/Majuro',
    'Pacific/Marquesas',
    'Pacific/Midway',
    'Pacific/Nauru',
    'Pacific/Niue',
    'Pacific/Norfolk',
    'Pacific/Noumea',
    'Pacific/Pago_Pago',
    'Pacific/Palau',
    'Pacific/Pitcairn',
    'Pacific/Pohnpei',
    'Pacific/Port_Moresby',
    'Pacific/Rarotonga',
    'Pacific/Saipan',
    'Pacific/Tahiti',
    'Pacific/Tarawa',
    'Pacific/Tongatapu',
    'Pacific/Wake',
    'Pacific/Wallis',
    'UTC',
];

// Group timezones by continent
const groupedTimezones = computed(() => {
    const grouped: Record<string, string[]> = {};

    // Add UTC separately as a special case
    grouped['UTC'] = ['UTC'];

    timezones.forEach((timezone) => {
        if (timezone === 'UTC') return; // Skip UTC as we already added it

        const [continent] = timezone.split('/');
        if (continent) {
            if (!grouped[continent]) {
                grouped[continent] = [];
            }
            grouped[continent].push(timezone);
        }
    });

    return grouped;
});

// Format timezone name for display
const formatTimezone = (timezone: string) => {
    if (timezone === 'UTC') return 'UTC';

    const parts = timezone.split('/');
    const lastPart = parts[parts.length - 1];
    const location = lastPart ? lastPart.replace('_', ' ') : timezone;

    return location;
};

// Initialize form with settings values
watch(
    () => props.settings,
    (newSettings) => {
        if (newSettings) {
            formData.value = {
                app_name: newSettings['app_name'] || '',
                app_lang: newSettings['app_lang'] || 'en',
                app_url: newSettings['app_url'] || '',
                app_timezone: newSettings['app_timezone'] || 'UTC',
                app_logo: newSettings['app_logo'] || '',
                default_bg: newSettings['default_bg'] || '',
                seo_description: newSettings['seo_description'] || '',
                seo_keywords: newSettings['seo_keywords'] || '',
                leaderboard_enabled: newSettings['leaderboard_enabled'] === 'true',
                leaderboard_limit: newSettings['leaderboard_limit'] || '15',
                allow_tickets: newSettings['allow_tickets'] === 'true',
                allow_servers: newSettings['allow_servers'] === 'true',
                allow_public_profiles: newSettings['allow_public_profiles'] === 'true',
                allow_coins_sharing: newSettings['allow_coins_sharing'] === 'true',
                daily_backup_enabled: newSettings['daily_backup_enabled'] === 'true',
                force_2fa: newSettings['force_2fa'] === 'true',
                coins_share_max_amount: newSettings['coins_share_max_amount'] || '100',
                coins_share_min_amount: newSettings['coins_share_min_amount'] || '1',
                coins_share_fee: newSettings['coins_share_fee'] || '10',
            };

            // Clear changed fields when settings are loaded
            changedFields.value.clear();
        }
    },
    { immediate: true },
);

// Bulk update settings
const bulkUpdateSettings = () => {
    if (changedFields.value.size === 0) {
        return;
    }

    const updates: Record<string, string> = {};
    changedFields.value.forEach((field) => {
        const value = formData.value[field as keyof typeof formData.value];
        updates[field] = typeof value === 'boolean' ? value.toString() : value;
    });

    emit('bulk-update', updates);
    changedFields.value.clear(); // Clear changed fields after bulk update
};

// Watch for changes to trigger bulk update
watch(
    changedFields,
    (newSet) => {
        if (newSet.size > 0) {
            bulkUpdateSettings();
        }
    },
    { deep: true },
);
</script>
