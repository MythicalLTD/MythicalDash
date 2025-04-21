<template>
    <LayoutDashboard>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-400">Server Queue Logs</h1>
        </div>

        <div v-if="loading" class="flex justify-center items-center py-10">
            <LoaderCircle class="h-8 w-8 animate-spin text-pink-400" />
        </div>

        <div v-else class="space-y-4">
            <div v-for="log in logs" :key="log.id" class="bg-gray-800/50 backdrop-blur-md rounded-lg p-4 shadow-md">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <h3 class="text-lg font-semibold text-white">Build #{{ log.build }}</h3>
                        <p class="text-sm text-gray-400">Created: {{ new Date(log.created_at).toLocaleString() }}</p>
                    </div>
                    <div class="flex space-x-2">
                        <span
                            v-if="log.deleted === 'true'"
                            class="px-2 py-1 rounded-full text-xs bg-red-500/20 text-red-400"
                        >
                            Deleted
                        </span>
                        <span
                            v-if="log.locked === 'true'"
                            class="px-2 py-1 rounded-full text-xs bg-yellow-500/20 text-yellow-400"
                        >
                            Locked
                        </span>
                        <span
                            v-if="log.purge === 'true'"
                            class="px-2 py-1 rounded-full text-xs bg-purple-500/20 text-purple-400"
                        >
                            Purge
                        </span>
                    </div>
                </div>
                <div class="bg-black/20 rounded p-3 font-mono text-sm whitespace-pre-wrap break-all">
                    <div v-html="formatLog(log.log)"></div>
                </div>
            </div>
        </div>
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import LayoutDashboard from '@/components/admin/LayoutDashboard.vue';
import { LoaderCircle } from 'lucide-vue-next';
import ServerQueue from '@/mythicaldash/admin/ServerQueue';

interface Log {
    id: number;
    build: number;
    log: string;
    deleted: string;
    locked: string;
    purge: string;
    created_at: string;
    expires_at: string;
    updated_at: string;
}

const logs = ref<Log[]>([]);
const loading = ref(true);

const formatLog = (log: string) => {
    // Replace color codes with HTML spans
    return log
        .replace(/&([0-9a-fk-or])/gi, (match, code) => {
            const colors: { [key: string]: string } = {
                '0': 'text-gray-400', // Black
                '1': 'text-blue-400', // Dark Blue
                '2': 'text-green-400', // Dark Green
                '3': 'text-cyan-400', // Dark Aqua
                '4': 'text-red-400', // Dark Red
                '5': 'text-purple-400', // Dark Purple
                '6': 'text-yellow-400', // Gold
                '7': 'text-gray-300', // Gray
                '8': 'text-gray-500', // Dark Gray
                '9': 'text-blue-300', // Blue
                a: 'text-green-300', // Green
                b: 'text-cyan-300', // Aqua
                c: 'text-red-300', // Red
                d: 'text-pink-300', // Light Purple
                e: 'text-yellow-300', // Yellow
                f: 'text-white', // White
                k: 'text-transparent', // Obfuscated
                l: 'font-bold', // Bold
                m: 'line-through', // Strikethrough
                n: 'underline', // Underline
                o: 'italic', // Italic
                r: 'text-white', // Reset
            };
            return `<span class="${colors[code] || ''}">`;
        })
        .replace(/\n/g, '<br>');
};

const fetchLogs = async () => {
    loading.value = true;
    try {
        const response = await ServerQueue.getServerQueueLogs();
        if (response.success) {
            logs.value = response.logs;
        } else {
            console.error('Failed to fetch logs:', response);
        }
    } catch (error) {
        console.error('Error fetching logs:', error);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchLogs();
});
</script>
