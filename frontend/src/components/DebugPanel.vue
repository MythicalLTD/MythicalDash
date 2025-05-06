<template>
    <div class="fixed bottom-5 right-5 z-50">
        <div
            v-if="isDebugPanelOpen"
            class="bg-gray-900/95 text-white rounded-lg shadow-2xl w-[800px] max-h-[600px] transition-all duration-300 transform"
            :class="[
                isDebugPanelOpen ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0',
                'backdrop-blur-sm border border-gray-700/50',
            ]"
        >
            <div class="flex justify-between items-center p-4 bg-gray-800/50 rounded-t-lg border-b border-gray-700/50">
                <div class="flex items-center space-x-3">
                    <h3 class="text-lg font-semibold">Debug Panel</h3>
                    <span class="px-2 py-1 text-xs bg-blue-500/20 text-blue-400 rounded-full">Ctrl+I to toggle</span>
                </div>
                <div class="flex items-center space-x-2">
                    <button
                        @click="clearLogs"
                        class="p-2 text-gray-400 hover:text-white transition-colors rounded-md hover:bg-gray-700/50"
                        title="Clear Logs"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path
                                fill-rule="evenodd"
                                d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </button>
                    <button
                        @click="toggleDebugMode"
                        class="p-2 text-gray-400 hover:text-white transition-colors rounded-md hover:bg-gray-700/50"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path
                                fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="p-4">
                <div class="flex space-x-2 mb-4">
                    <button
                        v-for="tab in debugTabs"
                        :key="tab.id"
                        :class="[
                            'px-4 py-2 rounded-md text-sm font-medium transition-all duration-200',
                            activeTab === tab.id
                                ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/20'
                                : 'bg-gray-800 text-gray-300 hover:bg-gray-700 hover:shadow-md',
                        ]"
                        @click="activeTab = tab.id"
                    >
                        {{ tab.name }}
                        <span
                            v-if="getLogCount(tab.id)"
                            class="ml-2 px-2 py-0.5 text-xs rounded-full"
                            :class="activeTab === tab.id ? 'bg-blue-500/30' : 'bg-gray-700'"
                        >
                            {{ getLogCount(tab.id) }}
                        </span>
                    </button>
                </div>

                <div class="bg-gray-800/50 rounded-lg p-4 max-h-[400px] overflow-y-auto custom-scrollbar">
                    <!-- Network Tab -->
                    <div v-if="activeTab === 'network'" class="space-y-4">
                        <div
                            v-for="(log, index) in networkLogs"
                            :key="index"
                            class="bg-gray-900/50 rounded-lg p-3 space-y-2 border border-gray-700/50 hover:border-gray-600/50 transition-colors"
                        >
                            <div class="flex justify-between items-center text-xs text-gray-400">
                                <span>{{ log.time }}</span>
                                <div class="flex items-center space-x-2">
                                    <span
                                        :class="[
                                            'px-2 py-1 rounded text-xs font-medium',
                                            log.status >= 400
                                                ? 'bg-red-500/20 text-red-400'
                                                : 'bg-green-500/20 text-green-400',
                                        ]"
                                    >
                                        {{ log.status }} {{ log.statusText }}
                                    </span>
                                </div>
                            </div>
                            <div class="font-mono text-sm break-all">{{ log.url }}</div>
                            <div v-if="log.method" class="text-xs text-gray-400">
                                Method: <span class="text-blue-400">{{ log.method }}</span>
                            </div>
                            <div v-if="log.requestBody" class="mt-2">
                                <button
                                    @click="toggleRequestBody(index)"
                                    class="flex items-center text-xs text-gray-400 hover:text-white transition-colors"
                                >
                                    <span>Request Body</span>
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-4 w-4 ml-1 transition-transform"
                                        :class="{ 'rotate-180': expandedRequests[index] }"
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </button>
                                <div v-show="expandedRequests[index]" class="mt-1">
                                    <div class="flex justify-end">
                                        <button
                                            @click="copyJson(log.requestBody)"
                                            class="p-1 text-gray-400 hover:text-white transition-colors rounded hover:bg-gray-700/50"
                                            title="Copy JSON"
                                        >
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-4 w-4"
                                                viewBox="0 0 20 20"
                                                fill="currentColor"
                                            >
                                                <path d="M8 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" />
                                                <path
                                                    d="M6 3a2 2 0 00-2 2v11a2 2 0 002 2h8a2 2 0 002-2V5a2 2 0 00-2-2 3 3 0 01-3 3H9a3 3 0 01-3-3z"
                                                />
                                            </svg>
                                        </button>
                                    </div>
                                    <pre class="bg-gray-900 p-2 rounded text-xs overflow-x-auto custom-scrollbar">{{
                                        JSON.stringify(log.requestBody, null, 2)
                                    }}</pre>
                                </div>
                            </div>
                            <div v-if="log.responseBody" class="mt-2">
                                <button
                                    @click="toggleResponseBody(index)"
                                    class="flex items-center text-xs text-gray-400 hover:text-white transition-colors"
                                >
                                    <span>Response Body</span>
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-4 w-4 ml-1 transition-transform"
                                        :class="{ 'rotate-180': expandedResponses[index] }"
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </button>
                                <div v-show="expandedResponses[index]" class="mt-1">
                                    <div class="flex justify-end">
                                        <button
                                            @click="copyJson(log.responseBody)"
                                            class="p-1 text-gray-400 hover:text-white transition-colors rounded hover:bg-gray-700/50"
                                            title="Copy JSON"
                                        >
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-4 w-4"
                                                viewBox="0 0 20 20"
                                                fill="currentColor"
                                            >
                                                <path d="M8 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" />
                                                <path
                                                    d="M6 3a2 2 0 00-2 2v11a2 2 0 002 2h8a2 2 0 002-2V5a2 2 0 00-2-2 3 3 0 01-3 3H9a3 3 0 01-3-3z"
                                                />
                                            </svg>
                                        </button>
                                    </div>
                                    <pre class="bg-gray-900 p-2 rounded text-xs overflow-x-auto custom-scrollbar">{{
                                        JSON.stringify(log.responseBody, null, 2)
                                    }}</pre>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Errors Tab -->
                    <div v-if="activeTab === 'errors'" class="space-y-4">
                        <div
                            v-for="(log, index) in errorLogs"
                            :key="index"
                            class="bg-red-900/20 border border-red-500/20 rounded-lg p-3 hover:border-red-500/30 transition-colors"
                        >
                            <div class="text-xs text-gray-400 mb-1">{{ log.time }}</div>
                            <div class="text-red-400 font-mono text-sm break-all">{{ log.message }}</div>
                            <div v-if="log.stack" class="mt-2">
                                <pre class="text-xs text-gray-400 overflow-x-auto custom-scrollbar">{{
                                    log.stack
                                }}</pre>
                            </div>
                        </div>
                    </div>

                    <!-- Console Tab -->
                    <div v-if="activeTab === 'console'" class="space-y-4">
                        <div
                            v-for="(log, index) in consoleLogs"
                            :key="index"
                            class="bg-gray-900/50 rounded-lg p-3 border border-gray-700/50 hover:border-gray-600/50 transition-colors"
                        >
                            <div class="text-xs text-gray-400 mb-1">{{ log.time }}</div>
                            <div class="font-mono text-sm break-all">{{ log.message }}</div>
                        </div>
                    </div>

                    <!-- App Tab -->
                    <div v-if="activeTab === 'app'" class="space-y-4">
                        <div class="bg-gray-900/50 rounded-lg p-3 border border-gray-700/50">
                            <div class="flex justify-between items-center mb-2">
                                <h4 class="text-sm font-medium">JavaScript Terminal</h4>
                                <button
                                    @click="clearTerminal"
                                    class="p-1 text-gray-400 hover:text-white transition-colors rounded hover:bg-gray-700/50"
                                    title="Clear Terminal"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-4 w-4"
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </button>
                            </div>
                            <div
                                class="bg-gray-900 rounded-lg p-3 font-mono text-sm h-[300px] overflow-y-auto custom-scrollbar mb-2"
                            >
                                <div v-for="(entry, index) in terminalHistory" :key="index" class="mb-2">
                                    <div v-if="entry.type === 'input'" class="text-blue-400">
                                        <span class="text-gray-500">></span> {{ entry.content }}
                                    </div>
                                    <div v-else-if="entry.type === 'output'" class="text-gray-300">
                                        {{ entry.content }}
                                    </div>
                                    <div v-else-if="entry.type === 'error'" class="text-red-400">
                                        {{ entry.content }}
                                    </div>
                                </div>
                            </div>
                            <div class="flex space-x-2">
                                <input
                                    v-model="terminalInput"
                                    @keydown.enter="executeTerminalCommand"
                                    type="text"
                                    class="flex-1 bg-gray-800 text-white rounded px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="Enter JavaScript code..."
                                />
                                <button
                                    @click="executeTerminalCommand"
                                    class="px-4 py-2 bg-blue-600 text-white rounded text-sm font-medium hover:bg-blue-700 transition-colors"
                                >
                                    Execute
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Storage Tab -->
                    <div v-if="activeTab === 'storage'" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <!-- Cookies -->
                            <div class="bg-gray-900/50 rounded-lg p-3 border border-gray-700/50">
                                <div class="flex justify-between items-center mb-2">
                                    <h4 class="text-sm font-medium">Cookies</h4>
                                    <button
                                        @click="refreshCookies"
                                        class="p-1 text-gray-400 hover:text-white transition-colors rounded hover:bg-gray-700/50"
                                        title="Refresh"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-4 w-4"
                                            viewBox="0 0 20 20"
                                            fill="currentColor"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </button>
                                </div>
                                <div class="space-y-2">
                                    <div
                                        v-for="(value, name) in cookies"
                                        :key="name"
                                        class="bg-gray-800/50 p-2 rounded text-xs"
                                    >
                                        <div class="font-mono text-yellow-400">{{ name }}</div>
                                        <div class="text-gray-400 text-xs mt-1 break-all">{{ value }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- LocalStorage -->
                            <div class="bg-gray-900/50 rounded-lg p-3 border border-gray-700/50">
                                <div class="flex justify-between items-center mb-2">
                                    <h4 class="text-sm font-medium">LocalStorage</h4>
                                    <button
                                        @click="refreshLocalStorage"
                                        class="p-1 text-gray-400 hover:text-white transition-colors rounded hover:bg-gray-700/50"
                                        title="Refresh"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-4 w-4"
                                            viewBox="0 0 20 20"
                                            fill="currentColor"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </button>
                                </div>
                                <div class="space-y-2">
                                    <div
                                        v-for="(value, key) in localStorage"
                                        :key="key"
                                        class="bg-gray-800/50 p-2 rounded text-xs"
                                    >
                                        <div class="font-mono text-purple-400">{{ key }}</div>
                                        <div class="text-gray-400 text-xs mt-1 break-all">{{ value }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Settings Tab -->
                    <div v-if="activeTab === 'settings'" class="space-y-4">
                        <div class="bg-gray-900/50 rounded-lg p-3 border border-gray-700/50">
                            <div class="flex justify-between items-center mb-2">
                                <h4 class="text-sm font-medium">Application Settings</h4>
                                <button
                                    @click="refreshSettings"
                                    class="p-1 text-gray-400 hover:text-white transition-colors rounded hover:bg-gray-700/50"
                                    title="Refresh"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-4 w-4"
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </button>
                            </div>

                            <!-- Settings Categories -->
                            <div class="space-y-4">
                                <!-- General Settings -->
                                <div class="bg-gray-800/50 rounded-lg p-3">
                                    <h5 class="text-sm font-medium text-blue-400 mb-2">General Settings</h5>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div v-for="(value, key) in generalSettings" :key="key" class="text-xs">
                                            <span class="text-gray-400">{{ key }}:</span>
                                            <span class="text-white ml-1">{{ value }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Company Information -->
                                <div class="bg-gray-800/50 rounded-lg p-3">
                                    <h5 class="text-sm font-medium text-green-400 mb-2">Company Information</h5>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div v-for="(value, key) in companySettings" :key="key" class="text-xs">
                                            <span class="text-gray-400">{{ key }}:</span>
                                            <span class="text-white ml-1">{{ value }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Server Settings -->
                                <div class="bg-gray-800/50 rounded-lg p-3">
                                    <h5 class="text-sm font-medium text-yellow-400 mb-2">Server Settings</h5>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div v-for="(value, key) in serverSettings" :key="key" class="text-xs">
                                            <span class="text-gray-400">{{ key }}:</span>
                                            <span class="text-white ml-1">{{ value }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Feature Flags -->
                                <div class="bg-gray-800/50 rounded-lg p-3">
                                    <h5 class="text-sm font-medium text-purple-400 mb-2">Feature Flags</h5>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div v-for="(value, key) in featureFlags" :key="key" class="text-xs">
                                            <span class="text-gray-400">{{ key }}:</span>
                                            <span
                                                :class="value === 'true' ? 'text-green-400' : 'text-red-400'"
                                                class="ml-1"
                                                >{{ value }}</span
                                            >
                                        </div>
                                    </div>
                                </div>

                                <!-- Debug Information -->
                                <div class="bg-gray-800/50 rounded-lg p-3">
                                    <h5 class="text-sm font-medium text-red-400 mb-2">Debug Information</h5>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div v-for="(value, key) in debugSettings" :key="key" class="text-xs">
                                            <span class="text-gray-400">{{ key }}:</span>
                                            <span class="text-white ml-1">{{ value }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent, ref, onMounted, onUnmounted } from 'vue';
import type { NetworkLog, ErrorLog, ConsoleLog, AppSettings } from '../types/debug';

export default defineComponent({
    name: 'DebugPanel',
    setup() {
        const isDebugPanelOpen = ref(false);
        const activeTab = ref('network');
        const networkLogs = ref<NetworkLog[]>([]);
        const errorLogs = ref<ErrorLog[]>([]);
        const consoleLogs = ref<ConsoleLog[]>([]);
        const expandedRequests = ref<Record<number, boolean>>({});
        const expandedResponses = ref<Record<number, boolean>>({});
        const appFunctions = ref<Record<string, { description: string }>>({});
        const appClasses = ref<Record<string, { description: string }>>({});
        const cookies = ref<Record<string, string>>({});
        const localStorage = ref<Record<string, string>>({});
        const terminalInput = ref('');
        const terminalHistory = ref<Array<{ type: 'input' | 'output' | 'error'; content: string }>>([]);
        const settings = ref<AppSettings | null>(null);
        const generalSettings = ref<Record<string, string>>({});
        const companySettings = ref<Record<string, string>>({});
        const serverSettings = ref<Record<string, string>>({});
        const featureFlags = ref<Record<string, string>>({});
        const debugSettings = ref<Record<string, string>>({});

        const debugTabs = [
            { id: 'network', name: 'Network' },
            { id: 'errors', name: 'Errors' },
            { id: 'console', name: 'Console' },
            { id: 'app', name: 'App' },
            { id: 'storage', name: 'Storage' },
            { id: 'settings', name: 'Settings' },
        ];

        const getLogCount = (tabId: string): number => {
            switch (tabId) {
                case 'network':
                    return networkLogs.value.length;
                case 'errors':
                    return errorLogs.value.length;
                case 'console':
                    return consoleLogs.value.length;
                default:
                    return 0;
            }
        };

        const copyJson = (data: unknown): void => {
            navigator.clipboard.writeText(JSON.stringify(data, null, 2));
        };

        const toggleRequestBody = (index: number): void => {
            expandedRequests.value[index] = !expandedRequests.value[index];
        };

        const toggleResponseBody = (index: number): void => {
            expandedResponses.value[index] = !expandedResponses.value[index];
        };

        const addLog = (type: 'network' | 'error' | 'console', data: unknown): void => {
            const timestamp = new Date().toLocaleTimeString();
            if (type === 'network') {
                networkLogs.value.unshift({ ...(data as NetworkLog), time: timestamp });
            } else if (type === 'error') {
                errorLogs.value.unshift({ ...(data as ErrorLog), time: timestamp });
            } else {
                consoleLogs.value.unshift({ ...(data as ConsoleLog), time: timestamp });
            }
        };

        const clearLogs = (): void => {
            networkLogs.value = [];
            errorLogs.value = [];
            consoleLogs.value = [];
            expandedRequests.value = {};
            expandedResponses.value = {};
        };

        const toggleDebugMode = (): void => {
            isDebugPanelOpen.value = !isDebugPanelOpen.value;
        };

        const handleKeyDown = (event: KeyboardEvent): void => {
            // Check for Ctrl+I (key code 73)
            if (event.ctrlKey && event.key === 'i') {
                event.preventDefault(); // Prevent browser's default "Inspect" action
                toggleDebugMode();
            }
        };

        const refreshCookies = (): void => {
            const cookieString = document.cookie;
            const cookiePairs = cookieString.split(';');
            const cookieObj: Record<string, string> = {};

            cookiePairs.forEach((pair) => {
                const [name, value] = pair.trim().split('=');
                if (name && value) {
                    cookieObj[name] = decodeURIComponent(value);
                }
            });

            cookies.value = cookieObj;
        };

        const refreshLocalStorage = (): void => {
            const storageObj: Record<string, string> = {};
            for (let i = 0; i < window.localStorage.length; i++) {
                const key = window.localStorage.key(i);
                if (key) {
                    storageObj[key] = window.localStorage.getItem(key) || '';
                }
            }
            localStorage.value = storageObj;
        };

        const executeTerminalCommand = (): void => {
            if (!terminalInput.value.trim()) return;

            // Add input to history
            terminalHistory.value.push({
                type: 'input',
                content: terminalInput.value,
            });

            try {
                // Execute the code
                const result = new Function(terminalInput.value)();

                // Add output to history
                terminalHistory.value.push({
                    type: 'output',
                    content: result !== undefined ? String(result) : 'undefined',
                });
            } catch (error) {
                // Add error to history
                terminalHistory.value.push({
                    type: 'error',
                    content: error instanceof Error ? error.message : String(error),
                });
            }

            // Clear input
            terminalInput.value = '';
        };

        const clearTerminal = (): void => {
            terminalHistory.value = [];
        };

        const categorizeSettings = (settings: AppSettings): void => {
            const value = settings.value;

            // General Settings
            generalSettings.value = {
                'App Name': value.app_name,
                'App Version': value.app_version,
                'App Language': value.app_lang,
                'App Timezone': value.app_timezone,
                'App URL': value.app_url,
                Currency: value.currency,
                'Currency Symbol': value.currency_symbol,
            };

            // Company Settings
            companySettings.value = {
                'Company Name': value.company_name,
                'Company Address': value.company_address,
                'Company City': value.company_city,
                'Company State': value.company_state,
                'Company Country': value.company_country,
                'Company ZIP': value.company_zip,
                'Company VAT': value.company_vat,
            };

            // Server Settings
            serverSettings.value = {
                'Default CPU': value.default_cpu,
                'Default RAM': value.default_ram,
                'Default Disk': value.default_disk,
                'Default Ports': value.default_ports,
                'Default Databases': value.default_databases,
                'Default Backups': value.default_backups,
                'Default Server Slots': value.default_server_slots,
                'Server Renew Days': value.server_renew_days,
                'Server Renew Cost': value.server_renew_cost,
            };

            // Feature Flags
            featureFlags.value = {
                'AFK Enabled': value.afk_enabled,
                'Allow Coins Sharing': value.allow_coins_sharing,
                'Allow Public Profiles': value.allow_public_profiles,
                'Allow Servers': value.allow_servers,
                'Allow Tickets': value.allow_tickets,
                'Code Redemption': value.code_redemption_enabled,
                'Credits Recharge': value.credits_recharge_enabled,
                'Early Supporters': value.early_supporters_enabled,
                Leaderboard: value.leaderboard_enabled,
                Referrals: value.referrals_enabled,
                'Server Renew': value.server_renew_enabled,
                Store: value.store_enabled,
                'Zero Trust': value.zero_trust_enabled,
            };

            // Debug Settings
            debugSettings.value = {
                'Debug Mode': value.debug_debug ? 'true' : 'false',
                'Debug Version': value.debug_version,
                'Debug OS': value.debug_os,
                'Debug OS Kernel': value.debug_os_kernel,
                'Debug Name': value.debug_name,
                Telemetry: value.debug_telemetry ? 'true' : 'false',
                'Use Redis': value.debug?.useRedis ? 'true' : 'false',
                'Rate Limit': value.debug?.rateLimit?.enabled ? 'true' : 'false',
                'Rate Limit Amount': value.debug?.rateLimit?.limit?.toString() || 'N/A',
            };
        };

        const refreshSettings = (): void => {
            const settingsStr = window.localStorage.getItem('mythicaldash_settings_cache');
            if (settingsStr) {
                try {
                    settings.value = JSON.parse(settingsStr) as AppSettings;
                    categorizeSettings(settings.value);
                } catch (error) {
                    console.error('Failed to parse settings:', error);
                }
            }
        };

        onMounted(() => {
            window.addEventListener('keydown', handleKeyDown);
            refreshCookies();
            refreshLocalStorage();
            refreshSettings();
        });

        onUnmounted(() => {
            window.removeEventListener('keydown', handleKeyDown);
        });

        return {
            isDebugPanelOpen,
            activeTab,
            debugTabs,
            networkLogs,
            errorLogs,
            consoleLogs,
            expandedRequests,
            expandedResponses,
            appFunctions,
            appClasses,
            cookies,
            localStorage,
            terminalInput,
            terminalHistory,
            addLog,
            clearLogs,
            getLogCount,
            copyJson,
            toggleDebugMode,
            toggleRequestBody,
            toggleResponseBody,
            refreshCookies,
            refreshLocalStorage,
            executeTerminalCommand,
            clearTerminal,
            settings,
            generalSettings,
            companySettings,
            serverSettings,
            featureFlags,
            debugSettings,
            refreshSettings,
        };
    },
});
</script>

<style scoped>
.custom-scrollbar {
    scrollbar-width: thin;
    scrollbar-color: rgba(156, 163, 175, 0.5) transparent;
}

.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: rgba(156, 163, 175, 0.5);
    border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background-color: rgba(156, 163, 175, 0.7);
}

/* Animation for new logs */
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.log-entry {
    animation: slideIn 0.2s ease-out;
}

/* Button hover effect */
button {
    position: relative;
    overflow: hidden;
}

button::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 5px;
    height: 5px;
    background: rgba(255, 255, 255, 0.5);
    opacity: 0;
    border-radius: 100%;
    transform: scale(1, 1) translate(-50%);
    transform-origin: 50% 50%;
}

button:hover::after {
    animation: ripple 1s ease-out;
}

@keyframes ripple {
    0% {
        transform: scale(0, 0);
        opacity: 0.5;
    }
    100% {
        transform: scale(20, 20);
        opacity: 0;
    }
}
</style>
