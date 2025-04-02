<template>
    <footer class="bg-[#0F1322]/80 backdrop-blur-sm border-t border-gray-800/20">
        <div class="max-w-7xl mx-auto px-6 py-4">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-3">
                    <div class="bg-gradient-to-r from-indigo-500 to-purple-600 p-1.5 rounded-lg shadow-lg">
                        <img src="https://github.com/mythicalltd.png" alt="Logo" class="h-5 w-5" />
                    </div>
                    <div class="text-sm text-gray-400">
                        <span>© {{ currentYear }} MythicalSystems. All rights reserved.</span>
                    </div>
                </div>
                <div class="flex items-center gap-6">
                    <a
                        v-for="link in footerLinks"
                        :key="link.name"
                        :href="link.path"
                        class="text-sm text-gray-500 hover:text-indigo-400 transition-colors"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        {{ link.name }}
                    </a>
                </div>
                <div class="flex items-center gap-2 text-sm text-gray-500">
                    <span class="px-2 py-0.5 bg-gray-800/50 rounded-lg"
                        >v{{ settings.getSetting('debug_version') }}</span
                    >
                </div>
            </div>
        </div>
    </footer>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { FooterLink, SettingsStore } from '../types';

defineProps<{
    footerLinks: FooterLink[];
    settings: SettingsStore;
}>();

const currentYear = computed(() => new Date().getFullYear());
</script>
