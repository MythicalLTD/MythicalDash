<template>
    <div
        class="fixed inset-0 flex items-center justify-center bg-gradient-to-br from-[#0a0a15] via-[#12121f] to-[#181825] overflow-hidden"
    >
        <!-- Subtle starry background using Tailwind utilities and inline style -->
        <div
            class="absolute inset-0 pointer-events-none z-0"
            style="
                background-image:
                    radial-gradient(1px 1px at 20% 30%, rgba(255, 255, 255, 0.08) 0, transparent 100%),
                    radial-gradient(1px 1px at 40% 70%, rgba(255, 255, 255, 0.06) 0, transparent 100%),
                    radial-gradient(1px 1px at 60% 40%, rgba(255, 255, 255, 0.08) 0, transparent 100%),
                    radial-gradient(2px 2px at 80% 10%, rgba(255, 255, 255, 0.06) 0, transparent 100%);
                background-size:
                    250px 250px,
                    200px 200px,
                    300px 300px,
                    350px 350px;
            "
        ></div>
        <div
            class="relative z-10 w-full max-w-md mx-auto bg-[#12121f]/80 border border-[#2a2a3f]/30 rounded-2xl shadow-2xl p-8 flex flex-col items-center backdrop-blur-md"
        >
            <AlertTriangleIcon class="w-16 h-16 text-red-500 drop-shadow-lg mb-6" />
            <h1 class="text-2xl font-bold text-white mb-2 text-center">{{ title }}</h1>
            <p class="text-base text-gray-300 mb-4 text-center">{{ message }}</p>
            <p v-if="errorCode" class="text-xs text-gray-400 bg-[#23233a]/60 rounded px-3 py-1 mb-6">
                Error Code: {{ errorCode }}
            </p>
            <div class="flex gap-3 w-full justify-center">
                <button
                    @click="retry"
                    class="px-5 py-2 rounded-lg font-medium text-white bg-indigo-600 hover:bg-indigo-700 transition-colors shadow focus:outline-none focus:ring-2 focus:ring-indigo-400"
                >
                    Retry
                </button>
                <button
                    @click="goHome"
                    class="px-5 py-2 rounded-lg font-medium text-gray-200 bg-[#23233a] hover:bg-[#23233a]/80 border border-[#2a2a3f]/40 transition-colors shadow focus:outline-none focus:ring-2 focus:ring-indigo-400"
                >
                    Go Home
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router';
import { AlertTriangle as AlertTriangleIcon } from 'lucide-vue-next';
import { onMounted } from 'vue';

defineProps<{
    title: string;
    message: string;
    errorCode?: string;
}>();

const router = useRouter();

onMounted(() => {
    // Stop all other JavaScript tasks
    window.stop();
    // Prevent any scrolling
    document.body.style.overflow = 'hidden';
});

const retry = () => {
    // Clear any existing timeouts/intervals
    const highestTimeoutId = window.setTimeout(() => {}, 0);
    for (let i = 0; i < highestTimeoutId; i++) {
        window.clearTimeout(i);
        window.clearInterval(i);
    }
    window.location.reload();
};

const goHome = () => {
    // Clear any existing timeouts/intervals
    const highestTimeoutId = window.setTimeout(() => {}, 0);
    for (let i = 0; i < highestTimeoutId; i++) {
        window.clearTimeout(i);
        window.clearInterval(i);
    }
    router.push('/');
};
</script>
