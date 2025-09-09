<template>
    <header
        class="bg-gradient-to-r from-[#0F1322]/95 via-[#0D0F1A]/95 to-[#0A0E1A]/95 backdrop-blur-xl border-b border-gray-800/30 px-6 py-4 flex items-center justify-between sticky top-0 z-30 shadow-lg"
    >
        <div class="flex items-center gap-6 flex-1">
            <!-- Breadcrumb -->
            <div class="hidden md:flex items-center text-sm">
                <span class="text-indigo-400 font-medium">Dashboard</span>
                <ChevronRight class="w-4 h-4 mx-2 text-gray-500" />
                <span class="text-gray-200 font-medium">{{ currentSection }}</span>
            </div>

            <!-- Search -->
            <SearchBar
                :search-query="searchQuery"
                :is-search-focused="isSearchFocused"
                :filtered-results="filteredResults"
                @update:search-query="$emit('update:searchQuery', $event)"
                @update:is-search-focused="$emit('update:isSearchFocused', $event)"
                @handle-search-blur="$emit('handleSearchBlur')"
                @handle-result-click="$emit('handleResultClick', $event)"
            />
        </div>

        <!-- Right side actions -->
        <div class="flex items-center gap-4">
            <!-- System Status -->
            <SystemStatus />
            <!-- User Profile -->
            <UserProfile
                :is-profile-open="isProfileOpen"
                :profile-menu="profileMenu"
                :session="session"
                @update:is-profile-open="$emit('update:isProfileOpen', $event)"
            />
        </div>
    </header>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import { ChevronRight } from 'lucide-vue-next';
import SearchBar from './SearchBar.vue';
import SystemStatus from './SystemStatus.vue';
import UserProfile from './UserProfile.vue';
import type { SearchResult, ProfileMenuItem, SessionInfo } from '../types';

const route = useRoute();

const currentSection = computed(() => {
    const path = route.path;
    const parts = path.split('/');
    const lastPart = parts[parts.length - 1];

    // Convert kebab-case to Title Case, handle undefined lastPart
    if (!lastPart) {
        return 'Overview';
    }
    return lastPart
        .split('-')
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
});

defineProps<{
    searchQuery: string;
    isSearchFocused: boolean;
    filteredResults: SearchResult[];
    isProfileOpen: boolean;
    profileMenu: ProfileMenuItem[];
    session: SessionInfo;
}>();

defineEmits<{
    (e: 'update:searchQuery', value: string): void;
    (e: 'update:isSearchFocused', value: boolean): void;
    (e: 'update:isProfileOpen', value: boolean): void;
    (e: 'handleSearchBlur'): void;
    (e: 'handleResultClick', result: SearchResult): void;
}>();
</script>
