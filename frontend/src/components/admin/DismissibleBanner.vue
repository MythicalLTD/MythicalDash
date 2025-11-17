<template>
    <div v-if="isVisible" class="bg-gray-900/50 rounded-lg border border-gray-800 overflow-hidden">
        <div class="p-5 border-b border-gray-800 relative">
            <button
                v-if="dismissible"
                @click="dismiss"
                class="absolute top-4 right-4 text-gray-400 hover:text-gray-200 transition-colors duration-200"
                :title="dismissTitle || 'Hide this banner'"
            >
                <X class="w-4 h-4" />
            </button>
            <div class="flex items-center gap-3" :class="{ 'pr-8': dismissible }">
                <slot name="icon">
                    <div
                        v-if="icon"
                        class="w-10 h-10 rounded-full bg-gray-800 border border-gray-700 flex items-center justify-center"
                    >
                        <component :is="icon" class="w-5 h-5 text-gray-400" />
                    </div>
                </slot>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-white">{{ title }}</h3>
                    <p v-if="subtitle" class="text-sm text-gray-400">{{ subtitle }}</p>
                </div>
            </div>
        </div>
        <div class="p-5">
            <slot>
                <p v-if="description" class="text-sm text-gray-300 mb-4 leading-relaxed" v-html="description"></p>
            </slot>
            <div v-if="$slots.actions" class="flex flex-col sm:flex-row gap-3">
                <slot name="actions"></slot>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { X } from 'lucide-vue-next';

interface Props {
    cookieKey: string;
    title: string;
    subtitle?: string;
    description?: string;
    icon?: unknown;
    dismissible?: boolean;
    dismissTitle?: string;
    cookieExpiryDays?: number;
}

const props = withDefaults(defineProps<Props>(), {
    dismissible: true,
    cookieExpiryDays: 365,
});

const isVisible = ref(true);

// Cookie helper functions
const setCookie = (name: string, value: string, days: number) => {
    const date = new Date();
    date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
    const expires = `expires=${date.toUTCString()}`;
    document.cookie = `${name}=${value};${expires};path=/`;
};

const getCookie = (name: string): string | null => {
    const nameEQ = `${name}=`;
    const ca = document.cookie.split(';');
    for (let i = 0; i < ca.length; i++) {
        const cookie = ca[i];
        if (!cookie) continue;
        const c = cookie.trim();
        if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
};

const dismiss = () => {
    isVisible.value = false;
    setCookie(props.cookieKey, 'true', props.cookieExpiryDays);
};

// Check if banner was previously hidden
const checkCookie = () => {
    if (!props.dismissible) {
        isVisible.value = true;
        return;
    }
    const hidden = getCookie(props.cookieKey);
    if (hidden === 'true') {
        isVisible.value = false;
    }
};

onMounted(() => {
    checkCookie();
});
</script>
