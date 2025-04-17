<template>
    <LayoutDashboard>
        <div v-if="loading" class="flex justify-center items-center min-h-[60vh]">
            <LoadingAnimation />
        </div>
        <div v-else-if="error" class="text-center p-8">
            <div class="inline-flex items-center justify-center p-3 bg-red-500/10 text-red-400 rounded-full mb-4">
                <AlertCircle class="h-6 w-6" />
            </div>
            <h3 class="text-lg font-medium text-gray-200 mb-2">{{ t('profile.error.title') }}</h3>
            <p class="text-gray-400 mb-4">{{ error }}</p>
            <Button @click="fetchProfile" variant="secondary">
                {{ t('profile.error.retry') }}
            </Button>
        </div>
        <div v-else>
            <!-- Profile Header with Background -->
            <div class="relative rounded-xl overflow-hidden mb-8">
                <div class="h-48 w-full overflow-hidden">
                    <img
                        v-if="userProfile.background"
                        :src="userProfile.background"
                        alt="Profile Background"
                        class="w-full h-full object-cover"
                    />
                    <div v-else class="w-full h-full bg-gradient-to-r from-indigo-900/30 to-purple-900/30"></div>
                </div>

                <div class="absolute inset-0 bg-gradient-to-t from-[#030305]/80 to-transparent"></div>

                <!-- Profile Info Overlay -->
                <div
                    class="absolute bottom-0 left-0 right-0 p-6 flex flex-col md:flex-row items-center md:items-end gap-4"
                >
                    <div class="relative">
                        <img
                            :src="userProfile.avatar"
                            :alt="userProfile.username"
                            class="w-24 h-24 rounded-full border-4 border-[#030305] object-cover"
                        />
                        <div
                            v-if="userProfile.role"
                            class="absolute -bottom-1 -right-1 bg-indigo-600 text-white text-xs font-bold px-2 py-1 rounded-full"
                        >
                            {{ getRoleName(userProfile.role) }}
                        </div>
                    </div>
                    <div class="text-center md:text-left">
                        <h1 class="text-2xl font-bold text-white mb-1">{{ userProfile.username }}</h1>
                        <div class="flex flex-wrap gap-2 justify-center md:justify-start">
                            <span
                                v-if="userProfile.verified === 'true'"
                                class="px-2 py-0.5 bg-green-900/30 text-green-400 text-xs rounded-full flex items-center gap-1"
                            >
                                <CheckCircle2 class="w-3 h-3" />
                                {{ t('profile.verified') }}
                            </span>
                            <span
                                v-if="userProfile.banned === 'YES'"
                                class="px-2 py-0.5 bg-red-900/30 text-red-400 text-xs rounded-full flex items-center gap-1"
                            >
                                <Ban class="w-3 h-3" />
                                {{ t('profile.banned') }}
                            </span>
                            <span class="px-2 py-0.5 bg-[#1a1a2e]/50 text-gray-300 text-xs rounded-full">
                                UUID: {{ userProfile.uuid.substring(0, 8) }}...
                            </span>
                        </div>
                    </div>
                    <div class="md:ml-auto flex items-center gap-2">
                        <a
                            v-if="userProfile.discord_linked === 'true'"
                            :href="`https://discordapp.com/users/${userProfile.discord_id}`"
                            target="_blank"
                            class="p-2 rounded-full bg-[#5865F2]/20 text-[#5865F2] hover:bg-[#5865F2]/30 transition-colors"
                        >
                            <svg
                                class="w-5 h-5"
                                viewBox="0 0 24 24"
                                fill="currentColor"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    d="M20.317 4.3698a19.7913 19.7913 0 00-4.8851-1.5152.0741.0741 0 00-.0785.0371c-.211.3753-.4447.8648-.6083 1.2495-1.8447-.2762-3.68-.2762-5.4868 0-.1636-.3933-.4058-.8742-.6177-1.2495a.077.077 0 00-.0785-.037 19.7363 19.7363 0 00-4.8852 1.515.0699.0699 0 00-.0321.0277C.5334 9.0458-.319 13.5799.0992 18.0578a.0824.0824 0 00.0312.0561c2.0528 1.5076 4.0413 2.4228 5.9929 3.0294a.0777.0777 0 00.0842-.0276c.4616-.6304.8731-1.2952 1.226-1.9942a.076.076 0 00-.0416-.1057c-.6528-.2476-1.2743-.5495-1.8722-.8923a.077.077 0 01-.0076-.1277c.1258-.0943.2517-.1923.3718-.2914a.0743.0743 0 01.0776-.0105c3.9278 1.7933 8.18 1.7933 12.0614 0a.0739.0739 0 01.0785.0095c.1202.099.246.1981.3728.2924a.077.077 0 01-.0066.1276 12.2986 12.2986 0 01-1.873.8914.0766.0766 0 00-.0407.1067c.3604.698.7719 1.3628 1.225 1.9932a.076.076 0 00.0842.0286c1.961-.6067 3.9495-1.5219 6.0023-3.0294a.077.077 0 00.0313-.0552c.5004-5.177-.8382-9.6739-3.5485-13.6604a.061.061 0 00-.0312-.0286zM8.02 15.3312c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9555-2.4189 2.157-2.4189 1.2108 0 2.1757 1.0952 2.1568 2.419 0 1.3332-.9555 2.4189-2.1569 2.4189zm7.9748 0c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9554-2.4189 2.1569-2.4189 1.2108 0 2.1757 1.0952 2.1568 2.419 0 1.3332-.946 2.4189-2.1568 2.4189Z"
                                />
                            </svg>
                        </a>
                        <a
                            v-if="userProfile.github_linked === 'true'"
                            :href="`https://github.com/${userProfile.github_username}`"
                            target="_blank"
                            class="p-2 rounded-full bg-gray-800/30 text-white hover:bg-gray-800/50 transition-colors"
                        >
                            <svg
                                class="w-5 h-5"
                                viewBox="0 0 24 24"
                                fill="currentColor"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    fill-rule="evenodd"
                                    clip-rule="evenodd"
                                    d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"
                                />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            <!-- Gift Coins Button -->
            <div class="mt-4 mb-6">
                <Button
                    v-if="userProfile.uuid !== Session.getInfo('uuid') && canGiveCoins"
                    variant="primary"
                    class="w-full"
                    @click="handleGiftCoins"
                >
                    <Coins class="w-4 h-4 mr-2" />
                    {{ t('profile.gift_coins') }}
                </Button>
            </div>

            <!-- Profile Content -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Left Column: Stats & Activity -->
                <div class="space-y-6">
                    <!-- Resource Stats -->
                    <CardComponent :card-title="t('profile.stats.title')">
                        <div class="grid grid-cols-2 gap-4">
                            <div
                                class="flex flex-col items-center p-3 bg-[#0a0a15]/50 rounded-lg border border-[#1a1a2f]/30"
                            >
                                <Coins class="text-amber-400 mb-2 h-5 w-5" />
                                <span class="text-lg font-medium text-amber-400">{{ userProfile.credits }}</span>
                                <span class="text-xs text-gray-400">{{ t('profile.stats.coins') }}</span>
                            </div>
                            <div
                                class="flex flex-col items-center p-3 bg-[#0a0a15]/50 rounded-lg border border-[#1a1a2f]/30"
                            >
                                <Clock class="text-green-400 mb-2 h-5 w-5" />
                                <span class="text-lg font-medium text-green-400">{{ userProfile.minutes_afk }}</span>
                                <span class="text-xs text-gray-400">{{ t('profile.stats.minutes_afk') }}</span>
                            </div>
                        </div>
                    </CardComponent>

                    <!-- Activity Info -->
                    <CardComponent :card-title="t('profile.activity.title')">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-400">{{ t('profile.activity.first_seen') }}:</span>
                                <span class="text-sm text-gray-200">{{ formatDate(userProfile.first_seen) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-400">{{ t('profile.activity.last_seen') }}:</span>
                                <span class="text-sm text-gray-200">{{ formatDate(userProfile.last_seen) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-400">{{ t('profile.activity.last_seen_afk') }}:</span>
                                <span class="text-sm text-gray-200">{{
                                    formatTimeAgo(userProfile.last_seen_afk)
                                }}</span>
                            </div>
                        </div>
                    </CardComponent>

                    <!-- Social Connections -->
                    <CardComponent
                        :card-title="t('profile.social.title')"
                        v-if="userProfile.discord_linked === 'true' || userProfile.github_linked === 'true'"
                    >
                        <div class="space-y-3">
                            <div
                                v-if="userProfile.discord_linked === 'true'"
                                class="flex items-center p-3 bg-[#0a0a15]/50 rounded-lg border border-[#1a1a2f]/30"
                            >
                                <div class="p-2 rounded-full bg-[#5865F2]/20 text-[#5865F2] mr-3">
                                    <svg
                                        class="w-5 h-5"
                                        viewBox="0 0 24 24"
                                        fill="currentColor"
                                        xmlns="http://www.w3.org/2000/svg"
                                    >
                                        <path
                                            d="M20.317 4.3698a19.7913 19.7913 0 00-4.8851-1.5152.0741.0741 0 00-.0785.0371c-.211.3753-.4447.8648-.6083 1.2495-1.8447-.2762-3.68-.2762-5.4868 0-.1636-.3933-.4058-.8742-.6177-1.2495a.077.077 0 00-.0785-.037 19.7363 19.7363 0 00-4.8852 1.515.0699.0699 0 00-.0321.0277C.5334 9.0458-.319 13.5799.0992 18.0578a.0824.0824 0 00.0312.0561c2.0528 1.5076 4.0413 2.4228 5.9929 3.0294a.0777.0777 0 00.0842-.0276c.4616-.6304.8731-1.2952 1.226-1.9942a.076.076 0 00-.0416-.1057c-.6528-.2476-1.2743-.5495-1.8722-.8923a.077.077 0 01-.0076-.1277c.1258-.0943.2517-.1923.3718-.2914a.0743.0743 0 01.0776-.0105c3.9278 1.7933 8.18 1.7933 12.0614 0a.0739.0739 0 01.0785.0095c.1202.099.246.1981.3728.2924a.077.077 0 01-.0066.1276 12.2986 12.2986 0 01-1.873.8914.0766.0766 0 00-.0407.1067c.3604.698.7719 1.3628 1.225 1.9932a.076.076 0 00.0842.0286c1.961-.6067 3.9495-1.5219 6.0023-3.0294a.077.077 0 00.0313-.0552c.5004-5.177-.8382-9.6739-3.5485-13.6604a.061.061 0 00-.0312-.0286zM8.02 15.3312c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9555-2.4189 2.157-2.4189 1.2108 0 2.1757 1.0952 2.1568 2.419 0 1.3332-.9555 2.4189-2.1569 2.4189zm7.9748 0c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9554-2.4189 2.1569-2.4189 1.2108 0 2.1757 1.0952 2.1568 2.419 0 1.3332-.946 2.4189-2.1568 2.4189Z"
                                        />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <span class="block text-sm font-medium text-gray-200">{{
                                        t('profile.social.discord')
                                    }}</span>
                                    <span class="block text-xs text-gray-400">{{ userProfile.discord_username }}</span>
                                </div>
                                <a
                                    :href="`https://discord.com/users/${userProfile.discord_username}`"
                                    target="_blank"
                                    class="text-indigo-400 hover:text-indigo-300 transition-colors"
                                >
                                    <ExternalLink class="w-4 h-4" />
                                </a>
                            </div>

                            <div
                                v-if="userProfile.github_linked === 'true'"
                                class="flex items-center p-3 bg-[#0a0a15]/50 rounded-lg border border-[#1a1a2f]/30"
                            >
                                <div class="p-2 rounded-full bg-gray-800/30 text-white mr-3">
                                    <svg
                                        class="w-5 h-5"
                                        viewBox="0 0 24 24"
                                        fill="currentColor"
                                        xmlns="http://www.w3.org/2000/svg"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            clip-rule="evenodd"
                                            d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"
                                        />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <span class="block text-sm font-medium text-gray-200">{{
                                        t('profile.social.github')
                                    }}</span>
                                    <span class="block text-xs text-gray-400">{{ userProfile.github_username }}</span>
                                </div>
                                <a
                                    :href="`https://github.com/${userProfile.github_username}`"
                                    target="_blank"
                                    class="text-indigo-400 hover:text-indigo-300 transition-colors"
                                >
                                    <ExternalLink class="w-4 h-4" />
                                </a>
                            </div>
                        </div>
                    </CardComponent>
                </div>

                <!-- Right Column: Resource Limits and Server Info -->
                <div class="md:col-span-2 space-y-6">
                    <!-- Server Resource Limits -->
                    <CardComponent :card-title="t('profile.resources.title')">
                        <div class="space-y-6">
                            <!-- Memory Limit -->
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-sm text-gray-400">{{ t('profile.resources.memory') }}</span>
                                    <span class="text-sm text-gray-300">{{
                                        formatMemory(userProfile.memory_limit)
                                    }}</span>
                                </div>
                                <div class="w-full bg-[#0a0a15]/70 rounded-full h-2.5">
                                    <div class="bg-indigo-600 h-2.5 rounded-full" style="width: 100%"></div>
                                </div>
                            </div>

                            <!-- CPU Limit -->
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-sm text-gray-400">{{ t('profile.resources.cpu') }}</span>
                                    <span class="text-sm text-gray-300">{{ formatCPU(userProfile.cpu_limit) }}</span>
                                </div>
                                <div class="w-full bg-[#0a0a15]/70 rounded-full h-2.5">
                                    <div class="bg-green-600 h-2.5 rounded-full" style="width: 100%"></div>
                                </div>
                            </div>

                            <!-- Disk Limit -->
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-sm text-gray-400">{{ t('profile.resources.disk') }}</span>
                                    <span class="text-sm text-gray-300">{{
                                        formatMemory(userProfile.disk_limit)
                                    }}</span>
                                </div>
                                <div class="w-full bg-[#0a0a15]/70 rounded-full h-2.5">
                                    <div class="bg-amber-600 h-2.5 rounded-full" style="width: 100%"></div>
                                </div>
                            </div>

                            <!-- Resource Allocation Grid -->
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                                <div class="p-3 bg-[#0a0a15]/50 rounded-lg border border-[#1a1a2f]/30">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-xs text-gray-400">{{ t('profile.resources.servers') }}</span>
                                        <span class="text-sm text-gray-200">{{ userProfile.server_limit }}</span>
                                    </div>
                                    <div class="w-full bg-[#0a0a15]/70 rounded-full h-1.5">
                                        <div class="bg-purple-600 h-1.5 rounded-full" style="width: 100%"></div>
                                    </div>
                                </div>

                                <div class="p-3 bg-[#0a0a15]/50 rounded-lg border border-[#1a1a2f]/30">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-xs text-gray-400">{{ t('profile.resources.backups') }}</span>
                                        <span class="text-sm text-gray-200">{{ userProfile.backup_limit }}</span>
                                    </div>
                                    <div class="w-full bg-[#0a0a15]/70 rounded-full h-1.5">
                                        <div class="bg-blue-600 h-1.5 rounded-full" style="width: 100%"></div>
                                    </div>
                                </div>

                                <div class="p-3 bg-[#0a0a15]/50 rounded-lg border border-[#1a1a2f]/30">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-xs text-gray-400">{{
                                            t('profile.resources.databases')
                                        }}</span>
                                        <span class="text-sm text-gray-200">{{ userProfile.database_limit }}</span>
                                    </div>
                                    <div class="w-full bg-[#0a0a15]/70 rounded-full h-1.5">
                                        <div class="bg-teal-600 h-1.5 rounded-full" style="width: 100%"></div>
                                    </div>
                                </div>

                                <div class="p-3 bg-[#0a0a15]/50 rounded-lg border border-[#1a1a2f]/30">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-xs text-gray-400">{{
                                            t('profile.resources.allocations')
                                        }}</span>
                                        <span class="text-sm text-gray-200">{{ userProfile.allocation_limit }}</span>
                                    </div>
                                    <div class="w-full bg-[#0a0a15]/70 rounded-full h-1.5">
                                        <div class="bg-red-600 h-1.5 rounded-full" style="width: 100%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardComponent>

                    <!-- Account Status Info -->
                    <CardComponent :card-title="t('profile.account.title')">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between py-2 border-b border-[#1a1a2f]/30">
                                <span class="text-sm text-gray-400">{{ t('profile.account.status') }}:</span>
                                <span
                                    v-if="userProfile.banned === 'NO' && userProfile.deleted === 'false'"
                                    class="px-2 py-0.5 bg-green-900/30 text-green-400 text-xs rounded-full"
                                >
                                    {{ t('profile.account.active') }}
                                </span>
                                <span
                                    v-else-if="userProfile.banned === 'YES'"
                                    class="px-2 py-0.5 bg-red-900/30 text-red-400 text-xs rounded-full"
                                >
                                    {{ t('profile.account.banned') }}
                                </span>
                                <span
                                    v-else-if="userProfile.deleted === 'true'"
                                    class="px-2 py-0.5 bg-gray-700/30 text-gray-400 text-xs rounded-full"
                                >
                                    {{ t('profile.account.deleted') }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between py-2 border-b border-[#1a1a2f]/30">
                                <span class="text-sm text-gray-400">{{ t('profile.account.verification') }}:</span>
                                <span
                                    v-if="userProfile.verified === 'true'"
                                    class="px-2 py-0.5 bg-green-900/30 text-green-400 text-xs rounded-full flex items-center gap-1"
                                >
                                    <CheckCircle2 class="w-3 h-3" />
                                    {{ t('profile.account.verified') }}
                                </span>
                                <span
                                    v-else
                                    class="px-2 py-0.5 bg-yellow-900/30 text-yellow-400 text-xs rounded-full flex items-center gap-1"
                                >
                                    <AlertCircle class="w-3 h-3" />
                                    {{ t('profile.account.not_verified') }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between py-2 border-b border-[#1a1a2f]/30">
                                <span class="text-sm text-gray-400">{{ t('profile.account.uuid') }}:</span>
                                <span class="text-sm text-gray-200 font-mono">{{ userProfile.uuid }}</span>
                            </div>
                        </div>
                    </CardComponent>
                </div>
            </div>
        </div>
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import { useI18n } from 'vue-i18n';
import LayoutDashboard from '@/components/client/LayoutDashboard.vue';
import CardComponent from '@/components/client/ui/Card/CardComponent.vue';
import LoadingAnimation from '@/components/client/ui/LoadingAnimation.vue';
import Button from '@/components/client/ui/Button.vue';
import { AlertCircle, Coins, Clock, ExternalLink, CheckCircle2, Ban } from 'lucide-vue-next';
import { useSettingsStore } from '@/stores/settings';
import { MythicalDOM } from '@/mythicaldash/MythicalDOM';
import { useRouter } from 'vue-router';
import Session from '@/mythicaldash/Session';

const { t } = useI18n();
const route = useRoute();
const router = useRouter();
const Settings = useSettingsStore();

if (Settings.getSetting('allow_public_profile') === 'false') {
    router.push('/dashboard');
}
const canGiveCoins = computed(() => {
    return Settings.getSetting('allow_coins_sharing') === 'true';
});

interface UserProfile {
    username: string;
    verified: string;
    banned: string;
    avatar: string;
    credits: number;
    uuid: string;
    role: number;
    deleted: string;
    last_seen: string;
    first_seen: string;
    background: string;
    minutes_afk: number;
    last_seen_afk: number;
    disk_limit: number;
    memory_limit: number;
    cpu_limit: number;
    server_limit: number;
    backup_limit: number;
    database_limit: number;
    allocation_limit: number;
    discord_linked: string;
    discord_username?: string;
    github_linked: string;
    github_username?: string;
    github_id?: number;
    discord_id?: number;
}

const userProfile = ref<UserProfile>({} as UserProfile);
const loading = ref<boolean>(true);
const error = ref<string | null>(null);

// Helper function to get role name
const getRoleName = (roleId: number): string => {
    // This would ideally come from a role mapping or translation
    const roles: Record<number, string> = {
        1: 'Default',
        2: 'VIP',
        3: 'Support Buddy',
        4: 'Support',
        5: 'Support LVL 3',
        6: 'Support LVL 4',
        7: 'Admin',
        8: 'Administrator',
    };
    return roles[roleId] || 'User';
};

const handleGiftCoins = () => {
    router.push(`/profile/${userProfile.value.uuid}/gift-coins`);
};

// Format date
const formatDate = (dateStr: string): string => {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    return new Intl.DateTimeFormat(undefined, {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(date);
};

// Format time ago
const formatTimeAgo = (timestamp: number): string => {
    if (!timestamp) return '';

    const now = Math.floor(Date.now() / 1000);
    const seconds = now - timestamp;

    if (seconds < 60) return `${seconds} seconds ago`;
    if (seconds < 3600) return `${Math.floor(seconds / 60)} minutes ago`;
    if (seconds < 86400) return `${Math.floor(seconds / 3600)} hours ago`;
    if (seconds < 2592000) return `${Math.floor(seconds / 86400)} days ago`;

    // If more than 30 days, show actual date
    return formatDate(new Date(timestamp * 1000).toISOString());
};

// Format memory to MB/GB
const formatMemory = (memoryInMB: number): string => {
    if (memoryInMB >= 1024) {
        return `${(memoryInMB / 1024).toFixed(2)} GB`;
    }
    return `${memoryInMB} MB`;
};

// Format CPU percentage
const formatCPU = (cpuLimit: number): string => {
    return `${cpuLimit / 100}%`;
};

// Fetch user profile data
const fetchProfile = async (): Promise<void> => {
    loading.value = true;
    error.value = null;

    try {
        const uuid = route.params.uuid;
        const response = await fetch(`/api/user/profile/${uuid}`);
        const data = await response.json();

        if (data.success && data.user) {
            userProfile.value = data.user;
            MythicalDOM.setPageTitle(`${t('profile.title')} - ${userProfile.value.username}`);
        } else {
            throw new Error(data.error || t('profile.error.failed_fetch'));
        }
    } catch (err) {
        error.value = err instanceof Error ? err.message : t('profile.error.generic');
        console.error('Failed to fetch profile data:', err);
    } finally {
        loading.value = false;
    }
};

// Initial data fetch
onMounted(() => {
    fetchProfile();
});
</script>

<style scoped>
/* Smooth transitions */
.transition-all {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 200ms;
}

/* Shadow effects */
.shadow-lg {
    box-shadow:
        0 10px 15px -3px rgba(0, 0, 0, 0.1),
        0 4px 6px -2px rgba(0, 0, 0, 0.05);
}
</style>
