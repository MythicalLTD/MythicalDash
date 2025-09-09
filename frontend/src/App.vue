<script lang="ts">
import { defineComponent, onMounted, ref, computed, watch } from 'vue';
import { useRouter } from 'vue-router';
import DebugPanel from './components/DebugPanel.vue';
import SettingsPanel from './components/SettingsPanel.vue';
import type { NetworkLog, ErrorLog, ConsoleLog } from './types/debug';
import type { TransitionType } from './types/transitions.ts';
import { useSkinSettings } from '@/composables/useSkinSettings';
import { updatePerformanceSettings } from '@/utils/performance';
export default defineComponent({
    name: 'App',
    components: {
        DebugPanel,
        SettingsPanel,
    },
    setup() {
        const router = useRouter();
        const debugPanel = ref<InstanceType<typeof DebugPanel> | null>(null);
        const isPageTransitioning = ref(false);
        const { performanceSettings } = useSkinSettings();

        // Transition settings
        const selectedTransition = ref<TransitionType>('fade');
        const showTransitionSelector = ref(false);

        // Compute the transition class based on the selected transition
        const transitionName = computed(() => `page-transition-${selectedTransition.value}`);

        // Watch for performance settings changes
        watch(
            () => performanceSettings,
            () => {
                updatePerformanceSettings();
            },
            { deep: true },
        );

        const toggleTransitionSelector = () => {
            showTransitionSelector.value = !showTransitionSelector.value;
        };

        const setTransition = (type: TransitionType) => {
            selectedTransition.value = type;
            // Save preference to localStorage
            localStorage.setItem('preferredTransition', type);
        };

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

            // Load saved transition preference
            const savedTransition = localStorage.getItem('preferredTransition') as TransitionType | null;
            if (savedTransition) {
                selectedTransition.value = savedTransition;
            }
        });

        router.beforeEach((to, from, next) => {
            isPageTransitioning.value = true;
            window.scrollTo(0, 0);
            next();
        });

        return {
            debugPanel,
            isPageTransitioning,
            selectedTransition,
            transitionName,
            showTransitionSelector,
            toggleTransitionSelector,
            setTransition,
        };
    },
});
</script>

<template>
    <div class="app-container">
        <!-- Transition settings button -->
        <button @click="toggleTransitionSelector" class="transition-settings-button" title="Page Transition Settings">
            <svg
                xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
            >
                <path
                    d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"
                ></path>
                <circle cx="12" cy="12" r="3"></circle>
            </svg>
        </button>

        <!-- Transition selector menu -->
        <SettingsPanel
            v-if="showTransitionSelector"
            :selectedTransition="selectedTransition"
            @select="setTransition"
            @close="showTransitionSelector = false"
        />

        <!-- Router view with dynamic transitions -->
        <router-view v-slot="{ Component }">
            <transition
                :name="transitionName"
                mode="out-in"
                @after-leave="isPageTransitioning = false"
                @enter="isPageTransitioning = true"
                @after-enter="isPageTransitioning = false"
            >
                <component :is="Component" :key="$route.fullPath" />
            </transition>
        </router-view>

        <DebugPanel ref="debugPanel" />
    </div>
</template>

<style>
.app-container {
    position: relative;
    min-height: 100vh;
}

/* Transition settings button */
.transition-settings-button {
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background-color: #333;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 1000;
    border: none;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
    transition: all 0.3s ease;
}

.transition-settings-button:hover {
    background-color: #555;
    transform: scale(1.05);
}

/* Fade transition */
.page-transition-fade-enter-active,
.page-transition-fade-leave-active {
    transition: opacity 0.3s ease;
}

.page-transition-fade-enter-from,
.page-transition-fade-leave-to {
    opacity: 0;
}

/* Slide right transition */
.page-transition-slide-right-enter-active,
.page-transition-slide-right-leave-active {
    transition:
        transform 0.3s ease,
        opacity 0.3s ease;
}

.page-transition-slide-right-enter-from {
    opacity: 0;
    transform: translateX(-30px);
}

.page-transition-slide-right-leave-to {
    opacity: 0;
    transform: translateX(30px);
}

/* Slide left transition */
.page-transition-slide-left-enter-active,
.page-transition-slide-left-leave-active {
    transition:
        transform 0.3s ease,
        opacity 0.3s ease;
}

.page-transition-slide-left-enter-from {
    opacity: 0;
    transform: translateX(30px);
}

.page-transition-slide-left-leave-to {
    opacity: 0;
    transform: translateX(-30px);
}

/* Slide up transition */
.page-transition-slide-up-enter-active,
.page-transition-slide-up-leave-active {
    transition:
        transform 0.3s ease,
        opacity 0.3s ease;
}

.page-transition-slide-up-enter-from {
    opacity: 0;
    transform: translateY(30px);
}

.page-transition-slide-up-leave-to {
    opacity: 0;
    transform: translateY(-30px);
}

/* Slide down transition */
.page-transition-slide-down-enter-active,
.page-transition-slide-down-leave-active {
    transition:
        transform 0.3s ease,
        opacity 0.3s ease;
}

.page-transition-slide-down-enter-from {
    opacity: 0;
    transform: translateY(-30px);
}

.page-transition-slide-down-leave-to {
    opacity: 0;
    transform: translateY(30px);
}

/* Scale transition */
.page-transition-scale-enter-active,
.page-transition-scale-leave-active {
    transition:
        transform 0.3s ease,
        opacity 0.3s ease;
}

.page-transition-scale-enter-from {
    opacity: 0;
    transform: scale(0.9);
}

.page-transition-scale-leave-to {
    opacity: 0;
    transform: scale(1.1);
}

/* Flip transition */
.page-transition-flip-enter-active,
.page-transition-flip-leave-active {
    transition:
        transform 0.6s ease,
        opacity 0.3s ease;
    transform-style: preserve-3d;
}

.page-transition-flip-enter-from {
    opacity: 0;
    transform: rotateY(-90deg);
}

.page-transition-flip-leave-to {
    opacity: 0;
    transform: rotateY(90deg);
}

/* Rotate transition */
.page-transition-rotate-enter-active,
.page-transition-rotate-leave-active {
    transition:
        transform 0.5s ease,
        opacity 0.3s ease;
}

.page-transition-rotate-enter-from {
    opacity: 0;
    transform: rotate(-5deg) scale(0.95);
}

.page-transition-rotate-leave-to {
    opacity: 0;
    transform: rotate(5deg) scale(0.95);
}
</style>
