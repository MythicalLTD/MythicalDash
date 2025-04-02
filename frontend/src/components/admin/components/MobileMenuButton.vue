<template>
    <button
        @click="$emit('toggle')"
        class="lg:hidden fixed top-4 left-4 z-50 p-2 bg-[#0F1322]/80 backdrop-blur-sm rounded-xl shadow-lg border border-gray-800/20"
    >
        <Menu v-if="!isSidebarOpen" class="w-6 h-6 text-indigo-400" />
        <X v-else class="w-6 h-6 text-indigo-400" />
    </button>
</template>

<script setup lang="ts">
import { Menu, X } from 'lucide-vue-next';

defineProps<{
    isSidebarOpen: boolean;
}>();

defineEmits<{
    (e: 'toggle'): void;
}>();
</script>
