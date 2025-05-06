<template>
    <router-view v-slot="{ Component }">
        <component :is="Component" />
    </router-view>
    <DebugPanel ref="debugPanel" />
</template>

<script lang="ts">
import { defineComponent, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import DebugPanel from './components/DebugPanel.vue';
import type { NetworkLog, ErrorLog, ConsoleLog } from './types/debug';

export default defineComponent({
    name: 'App',
    components: {
        DebugPanel,
    },
    setup() {
        const router = useRouter();
        const debugPanel = ref<InstanceType<typeof DebugPanel> | null>(null);

        const setupDebugMode = () => {
            // Monitor network requests
            const originalFetch = window.fetch;
            window.fetch = async (...args) => {
                const [url, options] = args;
                let requestBody: unknown = undefined;

                // Handle different request body types
                if (options?.body) {
                    if (options.body instanceof FormData) {
                        // Convert FormData to a plain object for logging
                        const formDataObj: Record<string, string> = {};
                        options.body.forEach((value, key) => {
                            formDataObj[key] = value.toString();
                        });
                        requestBody = formDataObj;
                    } else if (typeof options.body === 'string') {
                        try {
                            requestBody = JSON.parse(options.body);
                        } catch {
                            requestBody = options.body;
                        }
                    } else {
                        requestBody = options.body;
                    }
                }

                const response = await originalFetch(...args);
                const responseClone = response.clone();

                try {
                    const responseBody = await responseClone.json();
                    debugPanel.value?.addLog('network', {
                        url: url as string,
                        method: options?.method || 'GET',
                        status: response.status,
                        statusText: response.statusText,
                        requestBody,
                        responseBody,
                    } as NetworkLog);
                } catch {
                    // If response is not JSON, try to get text
                    try {
                        const responseText = await responseClone.text();
                        debugPanel.value?.addLog('network', {
                            url: url as string,
                            method: options?.method || 'GET',
                            status: response.status,
                            statusText: response.statusText,
                            requestBody,
                            responseBody: responseText,
                        } as NetworkLog);
                    } catch {
                        debugPanel.value?.addLog('network', {
                            url: url as string,
                            method: options?.method || 'GET',
                            status: response.status,
                            statusText: response.statusText,
                            requestBody,
                        } as NetworkLog);
                    }
                }

                return response;
            };

            // Monitor console errors
            const originalConsoleError = console.error;
            console.error = (...args) => {
                originalConsoleError.apply(console, args);
                debugPanel.value?.addLog('error', {
                    message: args.join(' '),
                    stack: new Error().stack,
                } as ErrorLog);
            };

            // Monitor unhandled promise rejections
            window.addEventListener('unhandledrejection', (event) => {
                debugPanel.value?.addLog('error', {
                    message: `Unhandled Promise Rejection: ${event.reason}`,
                    stack: event.reason?.stack,
                } as ErrorLog);
            });

            // Monitor global errors
            window.addEventListener('error', (event) => {
                debugPanel.value?.addLog('error', {
                    message: `Global Error: ${event.error}`,
                    stack: event.error?.stack,
                } as ErrorLog);
            });

            // Monitor console logs
            const originalConsoleLog = console.log;
            console.log = (...args) => {
                originalConsoleLog.apply(console, args);
                debugPanel.value?.addLog('console', {
                    message: args.join(' '),
                } as ConsoleLog);
            };
        };

        onMounted(() => {
            setupDebugMode();
        });

        router.beforeEach((to, from, next) => {
            window.scrollTo(0, 0);
            next();
        });

        return {
            debugPanel,
        };
    },
});
</script>

<style>
/* Remove unused debug styles */
</style>
