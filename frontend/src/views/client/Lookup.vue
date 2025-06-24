<template>
    <LayoutDashboard>
        <div class="p-6 space-y-6">
            <!-- Header Section -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-white">{{ t('lookup.title') }}</h1>
                    <p class="text-gray-400 mt-1">{{ t('lookup.description') }}</p>
                </div>
                <div v-if="searchResults.length > 0" class="flex items-center space-x-2 text-sm text-gray-400">
                    <Users class="w-4 h-4" />
                    <span>{{ searchResults.length }} {{ t('lookup.results.found') }}</span>
                </div>
            </div>

            <!-- Search Section -->
            <CardComponent :cardTitle="t('lookup.search.title')" :cardDescription="t('lookup.search.subtitle')">
                <form @submit.prevent="searchUsers" class="space-y-4">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <Search class="h-5 w-5 text-gray-400" />
                        </div>
                        <input
                            type="text"
                            v-model="searchQuery"
                            class="block w-full pl-10 pr-4 py-3 border border-[#2a2a3f]/50 rounded-lg bg-[#1a1a2e]/50 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200"
                            :placeholder="t('lookup.search.placeholder')"
                            :disabled="isSearching"
                        />
                        <div
                            v-if="isSearching"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none"
                        >
                            <Loader class="h-5 w-5 text-indigo-400 animate-spin" />
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button
                            type="submit"
                            :disabled="isSearching || searchQuery.length < 3"
                            class="flex-1 bg-gradient-to-r from-indigo-500 to-purple-600 text-white font-medium py-3 px-4 rounded-lg transition-all duration-200 hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center"
                        >
                            <Search v-if="!isSearching" class="w-4 h-4 mr-2" />
                            <Loader v-else class="w-4 h-4 mr-2 animate-spin" />
                            {{ isSearching ? t('lookup.search.searching') : t('lookup.search.button') }}
                        </button>

                        <button
                            v-if="hasSearched"
                            type="button"
                            @click="clearSearch"
                            class="px-4 py-3 border border-[#2a2a3f]/50 text-gray-400 rounded-lg hover:bg-[#1a1a2e]/50 transition-all duration-200"
                        >
                            <X class="w-4 h-4" />
                        </button>
                    </div>
                </form>
            </CardComponent>

            <!-- Search Results -->
            <div v-if="searchResults.length > 0" class="space-y-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-white">{{ t('lookup.results.title') }}</h2>
                    <div class="text-sm text-gray-400">
                        {{ t('lookup.results.showing', { count: searchResults.length }) }}
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="user in searchResults"
                        :key="user.uuid"
                        class="bg-[#12121f]/50 border border-[#2a2a3f]/30 rounded-xl p-6 shadow-lg transition-all duration-200 hover:shadow-xl hover:border-indigo-500/20 hover:scale-[1.02]"
                    >
                        <!-- User Header -->
                        <div class="flex items-center space-x-4 mb-4">
                            <div class="relative">
                                <img
                                    :src="`${user.avatar}?height=60&width=60`"
                                    :alt="user.username"
                                    class="w-14 h-14 rounded-full border-2 border-indigo-500/50"
                                />
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <h3 class="text-lg font-semibold text-white truncate">
                                        {{ user.username }}
                                    </h3>
                                    <span
                                        v-if="user.banned"
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-500/20 text-red-400"
                                    >
                                        <AlertTriangle class="w-3 h-3 mr-1" />
                                        {{ t('profile.banned') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- User Details -->
                        <div class="space-y-3 mb-4">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-400 flex items-center">
                                    <Hash class="w-3 h-3 mr-1" />
                                    {{ t('lookup.user.uuid') }}
                                </span>
                                <span class="text-gray-300 font-mono text-xs">
                                    {{ user.uuid.substring(0, 8) }}...
                                </span>
                            </div>
                        </div>

                        <!-- Action Button -->
                        <div class="pt-4 border-t border-[#2a2a3f]/30">
                            <router-link
                                :to="'/profile/' + user.uuid"
                                class="w-full bg-indigo-500/20 hover:bg-indigo-500/30 text-indigo-400 font-medium py-2.5 px-4 rounded-lg transition-all duration-200 flex items-center justify-center group"
                            >
                                <User class="w-4 h-4 mr-2" />
                                {{ t('lookup.user.view_profile') }}
                                <ExternalLink
                                    class="w-3 h-3 ml-2 group-hover:translate-x-0.5 transition-transform duration-200"
                                />
                            </router-link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- No Results State -->
            <CardComponent
                v-else-if="hasSearched && searchResults.length === 0 && !isSearching"
                :cardTitle="t('lookup.no_results.title')"
                :cardDescription="t('lookup.no_results.description')"
            >
                <div class="text-center py-8">
                    <div class="relative mb-6">
                        <div class="w-16 h-16 bg-gray-700/30 rounded-full flex items-center justify-center mx-auto">
                            <UserX class="w-8 h-8 text-gray-500" />
                        </div>
                    </div>

                    <p class="text-gray-400 mb-4">{{ t('lookup.no_results.message') }}</p>

                    <div class="bg-[#1a1a2e]/50 rounded-lg p-4 border border-[#2a2a3f]/30">
                        <h4 class="text-sm font-medium text-gray-300 mb-2">{{ t('lookup.tips.title') }}</h4>
                        <ul class="text-xs text-gray-400 space-y-1 text-left">
                            <li>• {{ t('lookup.tips.exact_username') }}</li>
                            <li>• {{ t('lookup.tips.case_sensitive') }}</li>
                            <li>• {{ t('lookup.tips.minimum_chars') }}</li>
                        </ul>
                    </div>
                </div>
            </CardComponent>

            <!-- Welcome State -->
            <CardComponent
                v-else-if="!hasSearched"
                :cardTitle="t('lookup.welcome.title')"
                :cardDescription="t('lookup.welcome.description')"
            >
                <div class="text-center py-8">
                    <div class="relative mb-6">
                        <div class="w-16 h-16 bg-indigo-500/20 rounded-full flex items-center justify-center mx-auto">
                            <Search class="w-8 h-8 text-indigo-400" />
                        </div>
                        <div
                            class="absolute -top-2 -right-2 w-6 h-6 bg-purple-500/20 rounded-full flex items-center justify-center"
                        >
                            <Users class="w-3 h-3 text-purple-400" />
                        </div>
                    </div>

                    <p class="text-gray-400 mb-6">{{ t('lookup.welcome.message') }}</p>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-left">
                        <div class="bg-[#1a1a2e]/50 rounded-lg p-4 border border-[#2a2a3f]/30">
                            <div class="flex items-center mb-2">
                                <Search class="w-4 h-4 text-indigo-400 mr-2" />
                                <h4 class="text-sm font-medium text-gray-300">{{ t('lookup.features.search') }}</h4>
                            </div>
                            <p class="text-xs text-gray-400">{{ t('lookup.features.search_desc') }}</p>
                        </div>

                        <div class="bg-[#1a1a2e]/50 rounded-lg p-4 border border-[#2a2a3f]/30">
                            <div class="flex items-center mb-2">
                                <User class="w-4 h-4 text-green-400 mr-2" />
                                <h4 class="text-sm font-medium text-gray-300">{{ t('lookup.features.profiles') }}</h4>
                            </div>
                            <p class="text-xs text-gray-400">{{ t('lookup.features.profiles_desc') }}</p>
                        </div>

                        <div class="bg-[#1a1a2e]/50 rounded-lg p-4 border border-[#2a2a3f]/30">
                            <div class="flex items-center mb-2">
                                <Circle class="w-4 h-4 text-yellow-400 mr-2" />
                                <h4 class="text-sm font-medium text-gray-300">{{ t('lookup.features.status') }}</h4>
                            </div>
                            <p class="text-xs text-gray-400">{{ t('lookup.features.status_desc') }}</p>
                        </div>
                    </div>
                </div>
            </CardComponent>
        </div>
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import LayoutDashboard from '@/components/client/LayoutDashboard.vue';
import CardComponent from '@/components/client/ui/Card/CardComponent.vue';
import { Search, Loader, ExternalLink, UserX, X, Users, User, Hash, Circle, AlertTriangle } from 'lucide-vue-next';
import { MythicalDOM } from '@/mythicaldash/MythicalDOM';
import { useSettingsStore } from '@/stores/settings';
import { useRouter } from 'vue-router';

const router = useRouter();
const Settings = useSettingsStore();

if (Settings.getSetting('allow_public_profiles') === 'false') {
    router.push('/dashboard');
}

const { t } = useI18n();

// Set page title
MythicalDOM.setPageTitle(t('lookup.title'));

interface User {
    uuid: string;
    username: string;
    avatar: string;
    last_seen: string;
    banned: boolean;
}

const searchQuery = ref('');
const searchResults = ref<User[]>([]);
const isSearching = ref(false);
const hasSearched = ref(false);

const searchUsers = async () => {
    if (searchQuery.value.length < 3) return;

    isSearching.value = true;
    hasSearched.value = true;

    try {
        const response = await fetch(`/api/user/search?query=${encodeURIComponent(searchQuery.value)}`);
        const data = await response.json();

        if (data.success) {
            searchResults.value = data.users;
        } else {
            searchResults.value = [];
        }
    } catch (error) {
        console.error('Error searching users:', error);
        searchResults.value = [];
    } finally {
        isSearching.value = false;
    }
};

const clearSearch = () => {
    searchQuery.value = '';
    searchResults.value = [];
    hasSearched.value = false;
};
</script>

<style scoped>
.animate-spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

/* Smooth hover transitions */
.group:hover .group-hover\:translate-x-0\.5 {
    transform: translateX(0.125rem);
}
</style>
