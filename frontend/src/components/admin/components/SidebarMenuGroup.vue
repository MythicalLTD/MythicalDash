<template>
    <div class="mb-6">
        <h3 class="text-xs font-semibold uppercase tracking-wider text-indigo-400/80 mb-3 px-3">
            {{ menuGroup.title }}
        </h3>
        <ul class="space-y-1">
            <SidebarMenuItem
                v-for="item in menuGroup.items"
                :key="item.name"
                :item="item"
                @toggle-submenu="$emit('toggleSubmenu', $event)"
            />
        </ul>
    </div>
</template>

<script setup lang="ts">
import SidebarMenuItem from './SidebarMenuItem.vue';
import type { MenuItem, MenuGroup } from '../types';

defineProps<{
    menuGroup: MenuGroup;
}>();

defineEmits<{
    (e: 'toggleSubmenu', item: MenuItem): void;
}>();
</script>
