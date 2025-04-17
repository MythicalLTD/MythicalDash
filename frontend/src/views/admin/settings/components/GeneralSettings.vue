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
                    @change="updateSetting('app_name', formData.app_name)"
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
                    @change="updateSetting('app_lang', formData.app_lang)"
                    class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
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
                    @change="updateSetting('app_url', formData.app_url)"
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
                    @change="updateSetting('app_timezone', formData.app_timezone)"
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
                        @change="updateSetting('app_logo', formData.app_logo)"
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

            <!-- SEO Settings -->
            <div class="pt-4 border-t border-gray-700">
                <h3 class="text-lg font-medium text-white mb-3">SEO Settings</h3>

                <!-- SEO Description -->
                <div class="mb-4">
                    <label for="seo_description" class="block text-sm font-medium text-gray-400 mb-1"
                        >Meta Description</label
                    >
                    <textarea
                        id="seo_description"
                        v-model="formData.seo_description"
                        @change="updateSetting('seo_description', formData.seo_description)"
                        rows="2"
                        class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                        placeholder="A brief description of your application"
                    ></textarea>
                    <p class="mt-1 text-xs text-gray-500">
                        A short description of your application for search engines. Recommended: 150-160 characters.
                    </p>
                </div>

                <!-- SEO Keywords -->
                <div>
                    <label for="seo_keywords" class="block text-sm font-medium text-gray-400 mb-1">Meta Keywords</label>
                    <input
                        id="seo_keywords"
                        type="text"
                        v-model="formData.seo_keywords"
                        @change="updateSetting('seo_keywords', formData.seo_keywords)"
                        class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                        placeholder="dashboard, game, hosting, etc."
                    />
                    <p class="mt-1 text-xs text-gray-500">
                        Comma-separated keywords to help search engines categorize your site.
                    </p>
                </div>
            </div>

            <!-- Leaderboard Settings -->
            <div class="pt-4 border-t border-gray-700">
                <h3 class="text-lg font-medium text-white mb-3">Leaderboard Settings</h3>
            </div>

            <!-- Leaderboard Settings -->
            <div class="space-y-4">
                <!-- Leaderboard Enabled -->
                <div class="flex items-center justify-between p-4 bg-gray-800/30 border border-gray-700 rounded-lg">
                    <div>
                        <label for="leaderboard_enabled" class="block text-sm font-medium text-white">
                            Leaderboard Enabled
                        </label>
                        <p class="mt-1 text-xs text-gray-400">Enable or disable the leaderboard feature globally</p>
                    </div>
                    <div class="flex items-center">
                        <input
                            id="leaderboard_enabled"
                            type="checkbox"
                            v-model="formData.leaderboard_enabled"
                            @change="updateSetting('leaderboard_enabled', formData.leaderboard_enabled)"
                            class="w-4 h-4 text-pink-500 border-gray-600 rounded focus:ring-pink-500 focus:ring-offset-gray-800 bg-gray-700"
                        />
                    </div>
                </div>

                <!-- Leaderboard Limit -->
                <div class="p-4 bg-gray-800/30 border border-gray-700 rounded-lg">
                    <div class="flex flex-col">
                        <label for="leaderboard_limit" class="block text-sm font-medium text-white mb-1">
                            Leaderboard Limit
                        </label>
                        <p class="text-xs text-gray-400 mb-3">Maximum number of entries to show on leaderboards</p>
                        <input
                            id="leaderboard_limit"
                            type="number"
                            min="5"
                            max="100"
                            v-model="formData.leaderboard_limit"
                            @change="updateSetting('leaderboard_limit', formData.leaderboard_limit)"
                            class="bg-gray-700/50 border border-gray-600 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500 text-white"
                        />
                    </div>
                </div>
            </div>
            <!-- Feature Settings -->
            <div class="pt-4 border-t border-gray-700">
                <h3 class="text-lg font-medium text-white mb-3">Feature Settings</h3>
            </div>

            <div class="space-y-4">
                <!-- Allow Tickets -->
                <div class="flex items-center justify-between p-4 bg-gray-800/30 border border-gray-700 rounded-lg">
                    <div>
                        <label for="allow_tickets" class="block text-sm font-medium text-white"> Allow Tickets </label>
                        <p class="mt-1 text-xs text-gray-400">Enable or disable the ticket system globally</p>
                    </div>
                    <div class="flex items-center">
                        <input
                            id="allow_tickets"
                            type="checkbox"
                            v-model="formData.allow_tickets"
                            @change="updateSetting('allow_tickets', formData.allow_tickets)"
                            class="w-4 h-4 text-pink-500 border-gray-600 rounded focus:ring-pink-500 focus:ring-offset-gray-800 bg-gray-700"
                        />
                    </div>
                </div>

                <!-- Allow Servers -->
                <div class="flex items-center justify-between p-4 bg-gray-800/30 border border-gray-700 rounded-lg">
                    <div>
                        <label for="allow_servers" class="block text-sm font-medium text-white"> Allow Servers </label>
                        <p class="mt-1 text-xs text-gray-400">Enable or disable server creation globally</p>
                    </div>
                    <div class="flex items-center">
                        <input
                            id="allow_servers"
                            type="checkbox"
                            v-model="formData.allow_servers"
                            @change="updateSetting('allow_servers', formData.allow_servers)"
                            class="w-4 h-4 text-pink-500 border-gray-600 rounded focus:ring-pink-500 focus:ring-offset-gray-800 bg-gray-700"
                        />
                    </div>
                </div>

                <!-- Allow Public Profiles -->
                <div class="flex items-center justify-between p-4 bg-gray-800/30 border border-gray-700 rounded-lg">
                    <div>
                        <label for="allow_public_profiles" class="block text-sm font-medium text-white">
                            Allow Public Profiles
                        </label>
                        <p class="mt-1 text-xs text-gray-400">Enable or disable public profiles globally</p>
                    </div>
                    <div class="flex items-center">
                        <input
                            id="allow_public_profiles"
                            type="checkbox"
                            v-model="formData.allow_public_profiles"
                            @change="updateSetting('allow_public_profiles', formData.allow_public_profiles)"
                            class="w-4 h-4 text-pink-500 border-gray-600 rounded focus:ring-pink-500 focus:ring-offset-gray-800 bg-gray-700"
                        />
                    </div>
                </div>
                <!-- Allow Coins Sharing -->
                <div class="flex flex-col p-4 bg-gray-800/30 border border-gray-700 rounded-lg space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <label for="allow_coins_sharing" class="block text-sm font-medium text-white">
                                Allow Coins Sharing
                            </label>
                            <p class="mt-1 text-xs text-gray-400">Enable or disable coins sharing globally</p>
                        </div>
                        <div class="flex items-center">
                            <input
                                id="allow_coins_sharing"
                                type="checkbox"
                                v-model="formData.allow_coins_sharing"
                                @change="updateSetting('allow_coins_sharing', formData.allow_coins_sharing)"
                                class="w-4 h-4 text-pink-500 border-gray-600 rounded focus:ring-pink-500 focus:ring-offset-gray-800 bg-gray-700"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label for="coins_share_max_amount" class="block text-sm font-medium text-gray-400">
                                Maximum Amount
                            </label>
                            <input
                                id="coins_share_max_amount"
                                type="number"
                                v-model="formData.coins_share_max_amount"
                                @change="updateSetting('coins_share_max_amount', formData.coins_share_max_amount)"
                                class="mt-1 block w-full rounded-md border-gray-600 bg-gray-700 text-white focus:border-pink-500 focus:ring-pink-500 sm:text-sm"
                            />
                        </div>

                        <div>
                            <label for="coins_share_min_amount" class="block text-sm font-medium text-gray-400">
                                Minimum Amount
                            </label>
                            <input
                                id="coins_share_min_amount"
                                type="number"
                                v-model="formData.coins_share_min_amount"
                                @change="updateSetting('coins_share_min_amount', formData.coins_share_min_amount)"
                                class="mt-1 block w-full rounded-md border-gray-600 bg-gray-700 text-white focus:border-pink-500 focus:ring-pink-500 sm:text-sm"
                            />
                        </div>

                        <div>
                            <label for="coins_share_fee" class="block text-sm font-medium text-gray-400">
                                Share Fee (%)
                            </label>
                            <input
                                id="coins_share_fee"
                                type="number"
                                v-model="formData.coins_share_fee"
                                @change="updateSetting('coins_share_fee', formData.coins_share_fee)"
                                class="mt-1 block w-full rounded-md border-gray-600 bg-gray-700 text-white focus:border-pink-500 focus:ring-pink-500 sm:text-sm"
                            />
                        </div>
                    </div>
                </div>
            </div>
            <!-- App Version (Display Only) -->
            <div class="pt-4 border-t border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-medium text-white">App Version</h3>
                        <p class="text-sm text-gray-400">Current version of the dashboard</p>
                    </div>
                    <div class="text-gray-300 bg-gray-800/50 px-3 py-1.5 rounded-lg border border-gray-700">
                        {{ formData.app_version || 'Unknown' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, watch, defineEmits, computed } from 'vue';
import { ImageIcon } from 'lucide-vue-next';

interface Props {
    settings: Record<string, string>;
}

const props = defineProps<Props>();
const emit = defineEmits(['update']);

// Form state with default values
const formData = ref({
    app_name: '',
    app_lang: 'en',
    app_url: '',
    app_timezone: 'UTC',
    app_logo: '',
    app_version: '',
    seo_description: '',
    seo_keywords: '',
    leaderboard_enabled: 'false',
    leaderboard_limit: '15',
    allow_tickets: 'false',
    allow_servers: 'false',
    allow_public_profiles: 'false',
    allow_coins_sharing: 'false',
    coins_share_max_amount: '100',
    coins_share_min_amount: '1',
    coins_share_fee: '10',
});

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
        if (!grouped[continent]) {
            grouped[continent] = [];
        }
        grouped[continent].push(timezone);
    });

    return grouped;
});

// Format timezone name for display
const formatTimezone = (timezone: string) => {
    if (timezone === 'UTC') return 'UTC';

    const parts = timezone.split('/');
    const location = parts[parts.length - 1].replace('_', ' ');

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
                app_version: newSettings['app_version'] || '',
                seo_description: newSettings['seo_description'] || '',
                seo_keywords: newSettings['seo_keywords'] || '',
                leaderboard_enabled: newSettings['leaderboard_enabled'] || 'false',
                leaderboard_limit: newSettings['leaderboard_limit'] || '15',
                allow_tickets: newSettings['allow_tickets'] || 'false',
                allow_servers: newSettings['allow_servers'] || 'false',
                allow_public_profiles: newSettings['allow_public_profiles'] || 'false',
                allow_coins_sharing: newSettings['allow_coins_sharing'] || 'false',
                coins_share_max_amount: newSettings['coins_share_max_amount'] || '100',
                coins_share_min_amount: newSettings['coins_share_min_amount'] || '1',
                coins_share_fee: newSettings['coins_share_fee'] || '10',
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
