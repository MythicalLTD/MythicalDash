<template>
    <div class="relative w-full max-w-md">
        <div class="relative">
            <input
                :value="searchQuery"
                @input="$emit('update:searchQuery', ($event.target as HTMLInputElement).value)"
                type="search"
                placeholder="Search anything... (Ctrl + K)"
                class="w-full bg-gray-800/50 text-gray-100 placeholder-gray-500 rounded-xl py-2.5 pl-10 pr-4 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all"
                @focus="$emit('update:isSearchFocused', true)"
                @blur="$emit('handleSearchBlur')"
            />
            <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500 w-5 h-5" />
            <kbd
                class="absolute right-3 top-1/2 transform -translate-y-1/2 hidden md:flex items-center justify-center h-5 px-1.5 text-[10px] font-mono text-gray-500 bg-gray-700/50 rounded border border-gray-600"
            >
                Ctrl+K
            </kbd>
        </div>

        <!-- Search Results Dropdown -->
        <div
            v-if="isSearchFocused && filteredResults.length > 0"
            class="absolute z-50 w-full mt-2 bg-[#0F1322] rounded-xl shadow-2xl border border-gray-800/30 overflow-hidden animate-fadeIn"
        >
            <div class="py-2">
                <div class="px-4 py-2 text-xs text-gray-500 uppercase font-semibold border-b border-gray-800/30">
                    Search Results
                </div>
                <div class="max-h-64 overflow-y-auto">
                    <RouterLink
                        v-for="result in filteredResults"
                        :key="result.id"
                        :to="result.path"
                        class="flex items-center px-4 py-3 text-sm hover:bg-white/5 transition-colors border-b border-gray-800/10 last:border-0"
                        @mousedown.prevent="$emit('handleResultClick', result)"
                    >
                        <div class="p-1.5 rounded-lg bg-indigo-500/10 mr-3">
                            <Search class="w-3.5 h-3.5 text-indigo-400" />
                        </div>
                        <div>
                            <span class="font-medium text-gray-300">{{ result.name }}</span>
                            <p class="text-xs text-gray-500 mt-0.5">{{ result.path }}</p>
                        </div>
                    </RouterLink>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Search } from 'lucide-vue-next';
import type { SearchResult } from '../types';

defineProps<{
    searchQuery: string;
    isSearchFocused: boolean;
    filteredResults: SearchResult[];
}>();

defineEmits<{
    (e: 'update:searchQuery', value: string): void;
    (e: 'update:isSearchFocused', value: boolean): void;
    (e: 'handleSearchBlur'): void;
    (e: 'handleResultClick', result: SearchResult): void;
}>();
</script>

<style scoped>
.animate-fadeIn {
    animation: fadeIn 0.2s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
