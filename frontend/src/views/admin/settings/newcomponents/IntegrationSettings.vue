<template>
    <div>
        <h2 class="text-xl font-semibold text-white mb-4">Integration Settings</h2>

        <div class="space-y-6">
            <!-- Pterodactyl Integration -->
            <div class="bg-gray-800/30 p-5 rounded-lg border border-gray-700">
                <div class="flex items-center mb-4">
                    <div class="flex-1">
                        <h3 class="text-lg font-medium text-white flex items-center">
                            <FeatherIcon class="w-5 h-5 mr-2 text-pink-400" />
                            Pterodactyl Panel
                        </h3>
                        <p class="text-sm text-gray-400">Connect to your Pterodactyl Panel for server management.</p>
                    </div>
                    <div>
                        <span
                            :class="[
                                'px-2 py-1 text-xs rounded-md',
                                pterodactylConnected ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400',
                            ]"
                        >
                            {{ pterodactylConnected ? 'Connected' : 'Not Connected' }}
                        </span>
                    </div>
                </div>

                <!-- Panel URL -->
                <div class="mb-4">
                    <label for="pterodactyl_base_url" class="block text-sm font-medium text-gray-400 mb-1"
                        >Panel URL</label
                    >
                    <input
                        id="pterodactyl_base_url"
                        type="url"
                        v-model="formData.pterodactyl_base_url"
                        @input="markChanged('pterodactyl_base_url')"
                        class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                        placeholder="https://panel.yourdomain.com"
                    />
                    <p class="mt-1 text-xs text-gray-500">
                        The base URL of your Pterodactyl Panel. Must include http:// or https://.
                    </p>
                </div>

                <!-- API Key -->
                <div class="mb-4">
                    <label for="pterodactyl_api_key" class="block text-sm font-medium text-gray-400 mb-1"
                        >API Key</label
                    >
                    <div class="relative">
                        <input
                            id="pterodactyl_api_key"
                            :type="showApiKey ? 'text' : 'password'"
                            autocomplete="new-password"
                            v-model="formData.pterodactyl_api_key"
                            @input="markChanged('pterodactyl_api_key')"
                            class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 pr-10 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                            placeholder="ptlc_••••••••••••••••••••••••••••••"
                        />
                        <button
                            type="button"
                            @click="showApiKey = !showApiKey"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center"
                        >
                            <EyeIcon v-if="showApiKey" class="h-5 w-5 text-gray-400" />
                            <EyeOffIcon v-else class="h-5 w-5 text-gray-400" />
                        </button>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">
                        Enter your Pterodactyl API key. This should be a full access Application API key, not a Client
                        API key.
                    </p>
                </div>
            </div>

            <!-- Pelican Integration -->
            <div class="bg-red-900/20 border border-red-500/30 p-6 rounded-lg">
                <div class="flex items-start mb-4">
                    <div class="flex-shrink-0">
                        <AlertTriangleIcon class="w-6 h-6 text-red-400" />
                    </div>
                    <div class="ml-3 flex-1">
                        <h3 class="text-lg font-medium text-red-400 flex items-center">
                            <FeatherIcon class="w-5 h-5 mr-2 text-red-400" />
                            Pelican Panel - Discontinued
                        </h3>
                        <div class="mt-2 text-sm text-red-300">
                            <p class="mb-2">
                                <strong>Pelican Panel support has been permanently discontinued.</strong>
                            </p>
                            <p class="mb-2">This decision was made due to several critical issues:</p>
                            <ul class="list-disc list-inside space-y-1 text-xs">
                                <li>Pelican Panel is not production-ready and frequently changes API endpoints</li>
                                <li>Constant API breaking changes cause MythicalDash integration failures</li>
                                <li>Unstable development cycle makes reliable integration impossible</li>
                                <li>Lack of proper API versioning and backward compatibility</li>
                            </ul>
                            <p class="mt-3 text-xs">
                                <strong>Recommendation:</strong> Consider migrating to Pterodactyl Panel, which provides
                                stable APIs and is production-ready for server management.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-red-800/30 border border-red-600/50 rounded-lg p-4">
                    <div class="flex items-center">
                        <InfoIcon class="w-5 h-5 text-red-300 mr-2" />
                        <span class="text-sm text-red-200 font-medium">
                            If you were using Pelican Panel, please migrate to Pterodactyl Panel above.
                        </span>
                    </div>
                </div>
            </div>
            <!-- GitHub Integration -->
            <div class="bg-gray-800/30 p-5 rounded-lg border border-gray-700">
                <div class="flex items-center mb-4">
                    <div class="flex-1">
                        <h3 class="text-lg font-medium text-white flex items-center">
                            <GithubIcon class="w-5 h-5 mr-2 text-gray-400" />
                            GitHub Integration
                        </h3>
                        <p class="text-sm text-gray-400">
                            Connect your GitHub account for user authentication and repository access.
                        </p>
                        <div class="mt-2 p-3 bg-gray-900/50 rounded-lg border border-gray-700/50">
                            <p class="text-sm text-gray-400 mb-1">OAuth2 Callback URL:</p>
                            <code class="text-sm text-indigo-400 break-all">
                                {{
                                    Settings.getSetting('app_url') &&
                                    !Settings.getSetting('app_url').startsWith('https://')
                                        ? 'https://' +
                                          Settings.getSetting('app_url')
                                              .replace(/^http:\/\//, '')
                                              .replace(/^https:\/\//, '')
                                        : Settings.getSetting('app_url')
                                }}/api/user/auth/callback/github
                            </code>
                        </div>
                    </div>
                </div>
                <button @click="goToGithub" class="text-sm text-indigo-400 hover:text-indigo-300 flex items-center">
                    <SettingsIcon class="w-4 h-4 mr-1" />
                    GitHub Developer Settings
                    <ChevronRightIcon class="w-4 h-4 ml-auto" /></button
                ><br />
                <!-- Enable GitHub -->
                <div class="mb-4">
                    <label class="flex items-center space-x-2">
                        <input
                            type="checkbox"
                            v-model="formData.github_enabled"
                            @change="markChanged('github_enabled')"
                            class="rounded border-gray-700 text-indigo-500 focus:ring-indigo-500"
                        />
                        <span class="text-sm text-gray-400">Enable GitHub Integration</span>
                    </label>
                </div>

                <!-- Client ID -->
                <div class="mb-4">
                    <label for="github_client_id" class="block text-sm font-medium text-gray-400 mb-1">Client ID</label>
                    <input
                        id="github_client_id"
                        type="text"
                        v-model="formData.github_client_id"
                        @change="markChanged('github_client_id')"
                        class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                        placeholder="Enter GitHub Client ID"
                    />
                </div>

                <!-- Client Secret -->
                <div class="mb-4">
                    <label for="github_client_secret" class="block text-sm font-medium text-gray-400 mb-1"
                        >Client Secret</label
                    >
                    <div class="relative">
                        <input
                            id="github_client_secret"
                            v-model="formData.github_client_secret"
                            @change="markChanged('github_client_secret')"
                            class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 pr-10 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                            placeholder="Enter GitHub Client Secret"
                        />
                    </div>
                </div>

                <!-- Allow Account Linking -->
                <div class="mb-4">
                    <label class="flex items-center space-x-2">
                        <input
                            type="checkbox"
                            v-model="formData.github_link_allowed"
                            @change="markChanged('github_link_allowed')"
                            class="rounded border-gray-700 text-indigo-500 focus:ring-indigo-500"
                        />
                        <span class="text-sm text-gray-400">Allow users to link their GitHub accounts</span>
                    </label>
                </div>

                <!-- Force GitHub Link -->
                <div class="mb-4">
                    <label class="flex items-center space-x-2">
                        <input
                            type="checkbox"
                            v-model="formData.force_github_link"
                            @change="markChanged('force_github_link')"
                            class="rounded border-gray-700 text-indigo-500 focus:ring-indigo-500"
                        />
                        <span class="text-sm text-gray-400"
                            >Force users to link their GitHub account (required for registration)</span
                        >
                    </label>
                </div>
            </div>
            <!-- Discord Integration -->
            <div class="bg-gray-800/30 p-5 rounded-lg border border-gray-700">
                <div class="flex items-center mb-4">
                    <div class="flex-1">
                        <h3 class="text-lg font-medium text-white flex items-center">
                            <FeatherIcon class="w-5 h-5 mr-2 text-indigo-400" />
                            Discord Integration
                        </h3>
                        <p class="text-sm text-gray-400">
                            Connect your Discord server for user authentication and notifications.
                        </p>
                        <div class="mt-2 p-3 bg-gray-900/50 rounded-lg border border-gray-700/50">
                            <p class="text-sm text-gray-400 mb-1">OAuth2 Callback URL:</p>
                            <code class="text-sm text-indigo-400 break-all">
                                {{
                                    Settings.getSetting('app_url') &&
                                    !Settings.getSetting('app_url').startsWith('https://')
                                        ? 'https://' +
                                          Settings.getSetting('app_url')
                                              .replace(/^https?:\/\//, '')
                                              .replace(/^\/+/, '')
                                        : Settings.getSetting('app_url')
                                }}/api/user/auth/callback/discord/link
                            </code>
                            <br />
                            <code class="text-sm text-indigo-400 break-all">
                                {{
                                    Settings.getSetting('app_url') &&
                                    !Settings.getSetting('app_url').startsWith('https://')
                                        ? 'https://' +
                                          Settings.getSetting('app_url')
                                              .replace(/^https?:\/\//, '')
                                              .replace(/^\/+/, '')
                                        : Settings.getSetting('app_url')
                                }}/api/user/auth/callback/discord/login </code
                            ><br />
                            <code class="text-sm text-indigo-400 break-all">
                                {{
                                    Settings.getSetting('app_url') &&
                                    !Settings.getSetting('app_url').startsWith('https://')
                                        ? 'https://' +
                                          Settings.getSetting('app_url')
                                              .replace(/^https?:\/\//, '')
                                              .replace(/^\/+/, '')
                                        : Settings.getSetting('app_url')
                                }}/api/user/auth/callback/discord/j4r
                            </code>
                        </div>
                    </div>
                </div>

                <button @click="goToDiscord" class="text-sm text-indigo-400 hover:text-indigo-300 flex items-center">
                    <SettingsIcon class="w-4 h-4 mr-1" />
                    Discord Developer Portal
                    <ChevronRightIcon class="w-4 h-4 ml-auto" /></button
                ><br />

                <!-- Enable Discord -->
                <div class="mb-4">
                    <label class="flex items-center space-x-2">
                        <input
                            type="checkbox"
                            v-model="formData.discord_enabled"
                            @change="markChanged('discord_enabled')"
                            class="rounded border-gray-700 text-indigo-500 focus:ring-indigo-500"
                        />
                        <span class="text-sm text-gray-400">Enable Discord Integration</span>
                    </label>
                </div>

                <!-- Server ID -->
                <div class="mb-4">
                    <label for="discord_server_id" class="block text-sm font-medium text-gray-400 mb-1"
                        >Server ID</label
                    >
                    <input
                        id="discord_server_id"
                        type="text"
                        v-model="formData.discord_server_id"
                        @change="markChanged('discord_server_id')"
                        class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="123456789012345678"
                    />
                    <p class="mt-1 text-xs text-gray-500">
                        Your Discord server ID. You can get this by enabling Developer Mode in Discord and
                        right-clicking your server.
                    </p>
                </div>

                <!-- Client ID -->
                <div class="mb-4">
                    <label for="discord_client_id" class="block text-sm font-medium text-gray-400 mb-1"
                        >Client ID</label
                    >
                    <input
                        id="discord_client_id"
                        type="text"
                        v-model="formData.discord_client_id"
                        @change="markChanged('discord_client_id')"
                        class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="123456789012345678"
                    />
                    <p class="mt-1 text-xs text-gray-500">
                        Your Discord application's Client ID from the Discord Developer Portal.
                    </p>
                </div>

                <!-- Client Secret -->
                <div class="mb-4">
                    <label for="discord_client_secret" class="block text-sm font-medium text-gray-400 mb-1"
                        >Client Secret</label
                    >
                    <input
                        id="discord_client_secret"
                        type="text"
                        v-model="formData.discord_client_secret"
                        @change="markChanged('discord_client_secret')"
                        class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="123456789012345678"
                    />
                    <p class="mt-1 text-xs text-gray-500">
                        Your Discord application's Client Secret from the Discord Developer Portal.
                    </p>
                </div>

                <div class="mb-4">
                    <label for="discord_bot_token" class="block text-sm font-medium text-gray-400 mb-1"
                        >Bot Token</label
                    >
                    <input
                        id="discord_bot_token"
                        type="password"
                        v-model="formData.discord_bot_token"
                        autocomplete="new-password"
                        @change="markChanged('discord_bot_token')"
                        class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Bot Token (keep this secret)"
                    />
                    <p class="mt-1 text-xs text-gray-500">
                        Your Discord bot token. <span class="text-red-400 font-semibold">Keep this secret!</span> This
                        is required for bot-based integrations.
                    </p>
                </div>

                <!-- Link Allowed -->
                <div class="mb-4">
                    <label class="flex items-center space-x-2">
                        <input
                            type="checkbox"
                            v-model="formData.discord_link_allowed"
                            @change="markChanged('discord_link_allowed')"
                            class="rounded border-gray-700 text-indigo-500 focus:ring-indigo-500"
                        />
                        <span class="text-sm text-gray-400">Allow users to link their Discord account</span>
                    </label>
                </div>

                <!-- Force Discord Link -->
                <div class="mb-4">
                    <label class="flex items-center space-x-2">
                        <input
                            type="checkbox"
                            v-model="formData.force_discord_link"
                            @change="markChanged('force_discord_link')"
                            class="rounded border-gray-700 text-indigo-500 focus:ring-indigo-500"
                        />
                        <span class="text-sm text-gray-400"
                            >Force users to link their Discord account (required for registration)</span
                        >
                    </label>
                </div>

                <!-- Force Join Server -->
                <div class="mb-4">
                    <label class="flex items-center space-x-2">
                        <input
                            type="checkbox"
                            :checked="formData.discord_force_join_server === 'true'"
                            @change="updateForceJoinServer"
                            class="rounded border-gray-700 text-indigo-500 focus:ring-indigo-500"
                        />
                        <span class="text-sm text-gray-400">Force users to join Discord server</span>
                    </label>

                    <!-- Alert when force join is enabled -->
                    <div
                        v-if="formData.discord_force_join_server === 'true'"
                        class="mt-3 p-3 bg-amber-900/20 border border-amber-500/30 rounded-lg"
                    >
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <AlertTriangleIcon class="w-5 h-5 text-amber-400" />
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-medium text-amber-400">Important Requirement</h4>
                                <div class="mt-1 text-sm text-amber-300">
                                    <p class="mb-1">
                                        <strong
                                            >Your Discord bot MUST be added to the server you want users to
                                            join.</strong
                                        >
                                    </p>
                                    <p class="text-xs">
                                        The bot needs the "Manage Server" permission to add users. Make sure your bot is
                                        in the server specified in the Server ID field above.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Social Integration -->
            <div class="bg-gray-800/30 p-5 rounded-lg border border-gray-700">
                <div class="flex items-center mb-4">
                    <div class="flex-1">
                        <h3 class="text-lg font-medium text-white flex items-center">
                            <FeatherIcon class="w-5 h-5 mr-2 text-amber-400" />
                            Social Integration
                        </h3>
                        <p class="text-sm text-gray-400">
                            Configure your social media links to be displayed on your website.
                        </p>
                    </div>
                </div>

                <!-- Discord Invite -->
                <div class="mb-4">
                    <label for="discord_invite_url" class="block text-sm font-medium text-gray-400 mb-1"
                        >Discord Invite URL</label
                    >
                    <input
                        id="discord_invite_url"
                        type="url"
                        v-model="formData.discord_invite_url"
                        @change="markChanged('discord_invite_url')"
                        class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-amber-500"
                        placeholder="https://discord.gg/your-invite"
                    />
                </div>

                <!-- Twitter -->
                <div class="mb-4">
                    <label for="twitter_url" class="block text-sm font-medium text-gray-400 mb-1">Twitter URL</label>
                    <input
                        id="twitter_url"
                        type="url"
                        v-model="formData.twitter_url"
                        @change="markChanged('twitter_url')"
                        class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-amber-500"
                        placeholder="https://twitter.com/your-username"
                    />
                </div>

                <!-- GitHub -->
                <div class="mb-4">
                    <label for="github_url" class="block text-sm font-medium text-gray-400 mb-1">GitHub URL</label>
                    <input
                        id="github_url"
                        type="url"
                        v-model="formData.github_url"
                        @change="markChanged('github_url')"
                        class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-amber-500"
                        placeholder="https://github.com/your-username"
                    />
                </div>

                <!-- LinkedIn -->
                <div class="mb-4">
                    <label for="linkedin_url" class="block text-sm font-medium text-gray-400 mb-1">LinkedIn URL</label>
                    <input
                        id="linkedin_url"
                        type="url"
                        v-model="formData.linkedin_url"
                        @change="markChanged('linkedin_url')"
                        class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-amber-500"
                        placeholder="https://linkedin.com/in/your-username"
                    />
                </div>

                <!-- Instagram -->
                <div class="mb-4">
                    <label for="instagram_url" class="block text-sm font-medium text-gray-400 mb-1"
                        >Instagram URL</label
                    >
                    <input
                        id="instagram_url"
                        type="url"
                        v-model="formData.instagram_url"
                        @change="markChanged('instagram_url')"
                        class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-amber-500"
                        placeholder="https://instagram.com/your-username"
                    />
                </div>

                <!-- YouTube -->
                <div class="mb-4">
                    <label for="youtube_url" class="block text-sm font-medium text-gray-400 mb-1">YouTube URL</label>
                    <input
                        id="youtube_url"
                        type="url"
                        v-model="formData.youtube_url"
                        @change="markChanged('youtube_url')"
                        class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-amber-500"
                        placeholder="https://youtube.com/your-channel"
                    />
                </div>

                <!-- TikTok -->
                <div class="mb-4">
                    <label for="tiktok_url" class="block text-sm font-medium text-gray-400 mb-1">TikTok URL</label>
                    <input
                        id="tiktok_url"
                        type="url"
                        v-model="formData.tiktok_url"
                        @change="markChanged('tiktok_url')"
                        class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-amber-500"
                        placeholder="https://tiktok.com/@your-username"
                    />
                </div>

                <!-- Facebook -->
                <div class="mb-4">
                    <label for="facebook_url" class="block text-sm font-medium text-gray-400 mb-1">Facebook URL</label>
                    <input
                        id="facebook_url"
                        type="url"
                        v-model="formData.facebook_url"
                        @change="markChanged('facebook_url')"
                        class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-amber-500"
                        placeholder="https://facebook.com/your-page"
                    />
                </div>

                <!-- Reddit -->
                <div class="mb-4">
                    <label for="reddit_url" class="block text-sm font-medium text-gray-400 mb-1">Reddit URL</label>
                    <input
                        id="reddit_url"
                        type="url"
                        v-model="formData.reddit_url"
                        @change="markChanged('reddit_url')"
                        class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-amber-500"
                        placeholder="https://reddit.com/r/your-subreddit"
                    />
                </div>

                <!-- Telegram -->
                <div class="mb-4">
                    <label for="telegram_url" class="block text-sm font-medium text-gray-400 mb-1">Telegram URL</label>
                    <input
                        id="telegram_url"
                        type="url"
                        v-model="formData.telegram_url"
                        @change="markChanged('telegram_url')"
                        class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-amber-500"
                        placeholder="https://t.me/your-channel"
                    />
                </div>

                <!-- WhatsApp -->
                <div class="mb-4">
                    <label for="whatsapp_url" class="block text-sm font-medium text-gray-400 mb-1">WhatsApp URL</label>
                    <input
                        id="whatsapp_url"
                        type="url"
                        v-model="formData.whatsapp_url"
                        @change="markChanged('whatsapp_url')"
                        class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-amber-500"
                        placeholder="https://wa.me/your-number"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import {
    Feather as FeatherIcon,
    Eye as EyeIcon,
    EyeOff as EyeOffIcon,
    ChevronRight as ChevronRightIcon,
    Settings as SettingsIcon,
    Github as GithubIcon,
    AlertTriangle as AlertTriangleIcon,
    Info as InfoIcon,
} from 'lucide-vue-next';
import { useSettingsStore } from '@/stores/settings';

const Settings = useSettingsStore();

// Props
const props = defineProps<{
    settings: Record<string, string>;
}>();

// Emits
const emit = defineEmits<{
    update: [key: string, value: string];
    'bulk-update': [updates: Record<string, string>];
}>();

// Form state
const formData = ref({
    pterodactyl_base_url: '',
    pterodactyl_api_key: '',
    pelican_base_url: '',
    pelican_api_key: '',
    discord_enabled: 'false',
    discord_server_id: '',
    discord_client_id: '',
    discord_client_secret: '',
    discord_bot_token: '',
    discord_link_allowed: 'false',
    discord_force_join_server: 'false',
    force_discord_link: 'false',
    discord_invite_url: '',
    twitter_url: '',
    github_url: '',
    linkedin_url: '',
    instagram_url: '',
    youtube_url: '',
    tiktok_url: '',
    facebook_url: '',
    reddit_url: '',
    telegram_url: '',
    whatsapp_url: '',
    github_enabled: 'false',
    github_client_id: '',
    github_client_secret: '',
    github_link_allowed: 'false',
    force_github_link: 'false',
});

// UI state
const showApiKey = ref(false);
const showPelicanApiKey = ref(false);

// Track changed fields
const changedFields = ref<Set<string>>(new Set());

// Mark a field as changed
const markChanged = (field: string) => {
    changedFields.value.add(field);

    // Emit the change to parent
    const value = formData.value[field as keyof typeof formData.value];
    emit('update', field, value);
};

// Computed property to check if Pterodactyl is connected
const pterodactylConnected = computed(() => {
    return formData.value.pterodactyl_base_url !== '';
});

// Computed property to check if Pelican is connected
const pelicanConnected = computed(() => {
    return formData.value.pelican_base_url !== '';
});

// Initialize form with settings values
watch(
    () => props.settings,
    (newSettings) => {
        if (newSettings) {
            formData.value = {
                pterodactyl_base_url: newSettings['pterodactyl_base_url'] || '',
                pterodactyl_api_key: newSettings['pterodactyl_api_key'] || '',
                pelican_base_url: newSettings['pelican_base_url'] || '',
                pelican_api_key: newSettings['pelican_api_key'] || '',
                discord_enabled: newSettings['discord_enabled'] || 'false',
                discord_server_id: newSettings['discord_server_id'] || '',
                discord_client_id: newSettings['discord_client_id'] || '',
                discord_client_secret: newSettings['discord_client_secret'] || '',
                discord_link_allowed: newSettings['discord_link_allowed'] || 'false',
                discord_force_join_server: newSettings['discord_force_join_server'] || 'false',
                force_discord_link: newSettings['force_discord_link'] || 'false',
                discord_invite_url: newSettings['discord_invite_url'] || '',
                twitter_url: newSettings['twitter_url'] || '',
                github_url: newSettings['github_url'] || '',
                linkedin_url: newSettings['linkedin_url'] || '',
                instagram_url: newSettings['instagram_url'] || '',
                youtube_url: newSettings['youtube_url'] || '',
                tiktok_url: newSettings['tiktok_url'] || '',
                facebook_url: newSettings['facebook_url'] || '',
                reddit_url: newSettings['reddit_url'] || '',
                telegram_url: newSettings['telegram_url'] || '',
                whatsapp_url: newSettings['whatsapp_url'] || '',
                github_enabled: newSettings['github_enabled'] || 'false',
                github_client_id: newSettings['github_client_id'] || '',
                github_client_secret: newSettings['github_client_secret'] || '',
                github_link_allowed: newSettings['github_link_allowed'] || 'false',
                force_github_link: newSettings['force_github_link'] || 'false',
                discord_bot_token: newSettings['discord_bot_token'] || '',
            };

            // Clear changed fields when settings are loaded
            changedFields.value.clear();
        }
    },
    { immediate: true },
);

const goToGithub = () => {
    window.open('https://github.com/settings/developers', '_blank');
};

const goToDiscord = () => {
    window.open('https://discord.com/developers/applications', '_blank');
};

// Handle force join server checkbox change
const updateForceJoinServer = (event: Event) => {
    const target = event.target as HTMLInputElement;
    formData.value.discord_force_join_server = target.checked ? 'true' : 'false';
    markChanged('discord_force_join_server');
};
</script>
