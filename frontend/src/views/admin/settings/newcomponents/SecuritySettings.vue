<template>
    <div>
        <h2 class="text-xl font-semibold text-white mb-4">Security Settings</h2>
        <div class="bg-yellow-500/10 border border-yellow-500/20 rounded-lg p-4 mb-6">
            <div class="flex items-center text-yellow-500">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5 mr-2"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <path
                        d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"
                    />
                    <line x1="12" y1="9" x2="12" y2="13" />
                    <line x1="12" y1="17" x2="12.01" y2="17" />
                </svg>
                <span class="text-sm">
                    For security reasons, existing security settings cannot be viewed. Any changes you make will
                    override the current settings.
                </span>
            </div>
        </div>
        <div class="space-y-6">
            <!-- Cloudflare Turnstile Section -->
            <div class="bg-gray-800/30 p-5 rounded-lg border border-gray-700">
                <div class="flex items-center mb-4">
                    <div class="flex-1">
                        <h3 class="text-lg font-medium text-white">Cloudflare Turnstile</h3>
                        <p class="text-sm text-gray-400">
                            Protect your forms from spam and abuse with Cloudflare Turnstile, a friendly CAPTCHA
                            alternative.
                        </p>
                    </div>
                    <div class="ml-4">
                        <a
                            href="https://www.cloudflare.com/products/turnstile/"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="text-pink-400 hover:text-pink-300 text-sm flex items-center"
                        >
                            <ExternalLinkIcon class="w-3.5 h-3.5 mr-1" />
                            Learn More
                        </a>
                    </div>
                </div>

                <!-- Turnstile Enabled -->
                <div class="flex items-center space-x-2 mb-4">
                    <input
                        type="checkbox"
                        id="turnstile_enabled"
                        v-model="turnstileEnabled"
                        @change="markChanged('turnstile_enabled')"
                        class="rounded border-gray-700 text-pink-500 focus:ring-pink-500 bg-gray-800/30"
                    />
                    <label for="turnstile_enabled" class="text-sm font-medium text-gray-400"
                        >Enable Turnstile Protection</label
                    >
                </div>

                <div v-if="turnstileEnabled">
                    <!-- Turnstile Site Key -->
                    <div class="mb-4">
                        <label for="turnstile_key_pub" class="block text-sm font-medium text-gray-400 mb-1"
                            >Site Key (Public)</label
                        >
                        <input
                            id="turnstile_key_pub"
                            type="text"
                            v-model="formData.turnstile_key_pub"
                            @input="markChanged('turnstile_key_pub')"
                            class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                            placeholder="1x00000000000000000000AA"
                        />
                        <p class="mt-1 text-xs text-gray-500">Your Cloudflare Turnstile site key (public key).</p>
                    </div>

                    <!-- Turnstile Secret Key -->
                    <div>
                        <label for="turnstile_key_priv" class="block text-sm font-medium text-gray-400 mb-1"
                            >Secret Key</label
                        >
                        <div class="relative">
                            <input
                                id="turnstile_key_priv"
                                :type="showSecretKey ? 'text' : 'password'"
                                autocomplete="new-password"
                                v-model="formData.turnstile_key_priv"
                                @input="markChanged('turnstile_key_priv')"
                                class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 pr-10 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                                placeholder="1x0000000000000000000000000000000AA"
                            />
                            <button
                                type="button"
                                @click="showSecretKey = !showSecretKey"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center"
                            >
                                <EyeIcon v-if="showSecretKey" class="h-5 w-5 text-gray-400" />
                                <EyeOffIcon v-else class="h-5 w-5 text-gray-400" />
                            </button>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">
                            Your Cloudflare Turnstile secret key. Keep this confidential.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Firewall Section -->
            <div class="bg-gray-800/30 p-5 rounded-lg border border-gray-700">
                <div class="flex items-center mb-4">
                    <div class="flex-1">
                        <h3 class="text-lg font-medium text-white">Firewall Protection</h3>
                        <p class="text-sm text-gray-400">
                            Configure firewall settings to protect your application from abuse and unauthorized access.
                        </p>
                    </div>
                </div>

                <!-- Enable Firewall -->
                <div class="flex items-center space-x-2 mb-4">
                    <input
                        type="checkbox"
                        id="firewall_enabled"
                        v-model="firewallEnabled"
                        @change="markChanged('firewall_enabled')"
                        class="rounded border-gray-700 text-pink-500 focus:ring-pink-500 bg-gray-800/30"
                    />
                    <label for="firewall_enabled" class="text-sm font-medium text-gray-400"
                        >Enable Firewall Protection</label
                    >
                </div>

                <div v-if="firewallEnabled" class="space-y-4">
                    <!-- Rate Limit -->
                    <div>
                        <label for="firewall_rate_limit" class="block text-sm font-medium text-gray-400 mb-1"
                            >Rate Limit (requests per minute)</label
                        >
                        <input
                            id="firewall_rate_limit"
                            type="number"
                            v-model="formData.firewall_rate_limit"
                            @input="markChanged('firewall_rate_limit')"
                            class="bg-gray-800/50 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                            placeholder="60"
                            min="1"
                        />
                        <p class="mt-1 text-xs text-gray-500">
                            Maximum number of requests allowed per minute per IP address.
                        </p>
                    </div>

                    <!-- Block VPN -->
                    <div class="flex items-center space-x-2">
                        <input
                            type="checkbox"
                            id="firewall_block_vpn"
                            v-model="firewallBlockVPN"
                            @change="markChanged('firewall_block_vpn')"
                            class="rounded border-gray-700 text-pink-500 focus:ring-pink-500 bg-gray-800/30"
                        />
                        <label for="firewall_block_vpn" class="text-sm font-medium text-gray-400"
                            >Block VPN Connections</label
                        >
                    </div>
                    <div class="flex items-center space-x-2">
                        <input
                            type="checkbox"
                            id="firewall_block_alts"
                            v-model="firewallBlockAlts"
                            @change="markChanged('firewall_block_alts')"
                        />
                        <label for="firewall_block_alts" class="text-sm font-medium text-gray-400"
                            >Block Alt Accounts</label
                        >
                    </div>
                </div>
            </div>

            <!-- Node Ping Visibility Section -->
            <div class="bg-gray-800/30 p-5 rounded-lg border border-gray-700">
                <div class="flex items-center mb-4">
                    <div class="flex-1">
                        <h3 class="text-lg font-medium text-white">Node Ping Visibility</h3>
                        <p class="text-sm text-gray-400">
                            Control whether users can see node ping information. When enabled, users will be able to see
                            the ping/latency to each node.
                        </p>
                    </div>
                </div>

                <!-- Show Node Ping -->
                <div class="flex items-center space-x-2">
                    <input
                        type="checkbox"
                        id="show_node_ping"
                        v-model="showNodePing"
                        @change="markChanged('show_node_ping')"
                        class="rounded border-gray-700 text-pink-500 focus:ring-pink-500 bg-gray-800/30"
                    />
                    <label for="show_node_ping" class="text-sm font-medium text-gray-400"
                        >Show Node Ping Information to Users</label
                    >
                </div>
            </div>

            <!-- Anti-Adblocker Section -->
            <div class="bg-gray-800/30 p-5 rounded-lg border border-gray-700">
                <div class="flex items-center mb-4">
                    <div class="flex-1">
                        <h3 class="text-lg font-medium text-white">Anti-Adblocker Protection</h3>
                        <p class="text-sm text-gray-400">
                            Detect and handle users with ad blockers enabled. This can help ensure users see important
                            content and comply with your site's requirements.
                        </p>
                    </div>
                </div>

                <!-- Enable Anti-Adblocker -->
                <div class="flex items-center space-x-2">
                    <input
                        type="checkbox"
                        id="anti_adblocker_enabled"
                        v-model="antiAdblockerEnabled"
                        @change="markChanged('anti_adblocker_enabled')"
                        class="rounded border-gray-700 text-pink-500 focus:ring-pink-500 bg-gray-800/30"
                    />
                    <label for="anti_adblocker_enabled" class="text-sm font-medium text-gray-400"
                        >Enable Anti-Adblocker Detection</label
                    >
                </div>

                <div
                    v-if="antiAdblockerEnabled"
                    class="mt-3 p-3 bg-yellow-500/10 border border-yellow-500/20 rounded-lg"
                >
                    <div class="flex items-start text-yellow-500">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4 mr-2 flex-shrink-0 mt-0.5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path
                                d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"
                            ></path>
                            <line x1="12" y1="9" x2="12" y2="13"></line>
                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                        </svg>
                        <span class="text-xs">
                            When enabled, users with ad blockers may be prompted to disable them or may have limited
                            access to certain features. This helps ensure users see important content and comply with
                            site requirements.
                        </span>
                    </div>
                </div>
            </div>

            <!-- Zero Trust Section -->
            <div
                class="bg-gray-800/30 p-5 rounded-lg border border-gray-700/80 shadow-lg relative overflow-hidden group transition-all duration-300 hover:shadow-pink-500/20 hover:border-pink-500/40"
            >
                <div
                    class="absolute -top-12 -right-12 w-36 h-36 bg-gradient-to-br from-pink-500/10 to-purple-500/10 rounded-full blur-xl transform group-hover:scale-110 transition-all duration-500"
                ></div>
                <div
                    class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full h-[200%] bg-gradient-to-t from-transparent via-pink-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-700 animate-pulse"
                ></div>
                <div class="relative z-10">
                    <div class="flex items-center mb-3">
                        <div class="flex-1">
                            <div class="flex items-center">
                                <h3
                                    class="text-lg font-medium text-white group-hover:text-pink-300 transition-colors duration-300"
                                >
                                    Advanced Abuse Protection (Mythical Zero Trust)
                                </h3>
                                <span
                                    class="ml-2 px-2 py-0.5 text-xs bg-pink-500/20 text-pink-400 rounded-full group-hover:bg-pink-500/30 transition-all duration-300"
                                >
                                    Freemium
                                </span>
                            </div>
                            <p
                                class="text-sm text-gray-400 mt-1 group-hover:text-gray-300 transition-colors duration-300"
                            >
                                Powerful cross-platform security that scans user data during signup and compares it with
                                a global abuse database. Automatically identifies and blocks users previously reported
                                for abuse on other MythicalDash installations.
                            </p>
                        </div>
                    </div>

                    <div
                        class="bg-gray-900/50 p-3 rounded-lg mb-4 border border-pink-500/20 transform transition-all duration-300 group-hover:border-pink-500/40 group-hover:bg-gray-900/70"
                    >
                        <div class="flex items-start">
                            <div class="mr-3 mt-1 text-pink-400 animate-pulse">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-xs text-gray-300">
                                    This feature creates a secure network across all MythicalDash instances to protect
                                    against known abusers. Data is encrypted and only used for abuse detection. Data
                                    shared:
                                    <span class="text-pink-400">
                                        IP Address, Email, Username, First Name, Last Name, Discord ID, Github ID,
                                        Server Files (MD5, SHA1, SHA256)
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- License Warning -->
                    <div class="mb-4">
                        <div
                            class="flex items-start text-yellow-500 bg-yellow-500/10 p-3 rounded-lg border border-yellow-500/20"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4 mr-2 flex-shrink-0 mt-0.5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path
                                    d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"
                                ></path>
                                <line x1="12" y1="9" x2="12" y2="13"></line>
                                <line x1="12" y1="17" x2="12.01" y2="17"></line>
                            </svg>
                            <span class="text-xs">
                                Without a valid MythicalDash license key, Zero Trust features are limited to 5 actions
                                per day.
                                <a
                                    href="https://discord.mythical.systems"
                                    class="text-yellow-400 hover:text-yellow-300 underline"
                                    >Get a license</a
                                >
                                to remove this limitation.
                            </span>
                        </div>
                    </div>

                    <!-- Zero Trust Features -->
                    <div class="mb-5 ml-6 border-l-2 border-pink-500/20 pl-4 py-2">
                        <h4 class="text-sm text-pink-400 mb-2 font-medium">Zero Trust Features</h4>

                        <div class="grid grid-cols-2 gap-x-4 gap-y-2 text-xs mb-4">
                            <div class="flex items-center text-gray-300">
                                <svg
                                    class="h-3 w-3 mr-2 text-pink-500"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="3"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                <span>Cross-Platform Abuse Detection</span>
                            </div>
                            <div class="flex items-center text-gray-300">
                                <svg
                                    class="h-3 w-3 mr-2 text-pink-500"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="3"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                <span>IP Reputation Checking</span>
                            </div>
                            <div class="flex items-center text-gray-300">
                                <svg
                                    class="h-3 w-3 mr-2 text-pink-500"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="3"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                <span>Email Fraud Detection</span>
                            </div>
                            <div class="flex items-center text-gray-300">
                                <svg
                                    class="h-3 w-3 mr-2 text-pink-500"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="3"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                <span>Behavioral Analysis</span>
                            </div>
                            <div class="flex items-center text-gray-300">
                                <svg
                                    class="h-3 w-3 mr-2 text-pink-500"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="3"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                <span>Automated Threat Response</span>
                            </div>
                            <div class="flex items-center text-gray-300">
                                <svg
                                    class="h-3 w-3 mr-2 text-pink-500"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="3"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                <span>Distributed Threat Intelligence</span>
                            </div>
                        </div>
                    </div>

                    <!-- Server Scan Tool -->
                    <div class="ml-6 mt-3 border-l-2 border-pink-500/20 pl-4 py-2 animate-fadeIn">
                        <div class="mb-2">
                            <p class="text-xs text-gray-400 mb-3">
                                Enhanced server protection that scans for malicious software and activities:
                            </p>

                            <div class="grid grid-cols-2 gap-2 text-xs text-gray-300 mb-3">
                                <div class="flex items-center">
                                    <span class="w-2 h-2 bg-pink-500 rounded-full mr-2"></span>
                                    <span>Cryptocurrency miners</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="w-2 h-2 bg-pink-500 rounded-full mr-2"></span>
                                    <span>Unauthorized VM software</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="w-2 h-2 bg-pink-500 rounded-full mr-2"></span>
                                    <span>Malicious scripts</span>
                                </div>
                                <div class="flex items-center">
                                    <span class="w-2 h-2 bg-pink-500 rounded-full mr-2"></span>
                                    <span>Botnet connections</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center space-x-2">
                            <input
                                type="checkbox"
                                id="zero_trust_server_scan_tool_enabled"
                                v-model="zeroTrustServerScanEnabled"
                                @change="markChanged('zero_trust_server_scan_tool_enabled')"
                                class="rounded border-gray-700 text-pink-500 focus:ring-pink-500 bg-gray-800/30"
                            />
                            <label
                                for="zero_trust_server_scan_tool_enabled"
                                class="text-sm font-medium text-gray-300 hover:text-pink-300 transition-colors duration-300"
                                >Enable Server Security Scanning</label
                            >
                        </div>
                    </div>

                    <!-- Advanced Security Settings -->
                    <div class="mt-4 pt-3 border-t border-gray-700/50">
                        <h4 class="text-sm text-pink-400 mb-3 font-medium flex items-center">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4 mr-1"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                                <circle cx="12" cy="11" r="3"></circle>
                            </svg>
                            Advanced Security Settings
                        </h4>

                        <div class="space-y-3">
                            <!-- Whitelist Key IPs -->
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <input
                                        type="checkbox"
                                        id="zero_trust_whitelist_ips"
                                        v-model="zeroTrustWhitelistIPsEnabled"
                                        @change="markChanged('zero_trust_whitelist_ips_enabled')"
                                        class="rounded border-gray-700 text-pink-500 focus:ring-pink-500 bg-gray-800/30"
                                    />
                                    <label for="zero_trust_whitelist_ips" class="text-sm text-gray-300">
                                        Whitelist Admin IPs
                                    </label>
                                </div>
                                <span class="text-xs px-1.5 py-0.5 bg-pink-500/20 text-pink-400 rounded"
                                    >Recommended</span
                                >
                            </div>

                            <!-- Block Tor Exit Nodes -->
                            <div class="flex items-center">
                                <input
                                    type="checkbox"
                                    id="zero_trust_block_tor"
                                    v-model="zeroTrustBlockTorEnabled"
                                    @change="markChanged('zero_trust_block_tor_enabled')"
                                    class="rounded border-gray-700 text-pink-500 focus:ring-pink-500 bg-gray-800/30"
                                />
                                <label for="zero_trust_block_tor" class="text-sm text-gray-300 ml-2">
                                    Block Tor Exit Nodes
                                </label>
                            </div>

                            <!-- Enhanced Logging -->
                            <div class="flex items-center">
                                <input
                                    type="checkbox"
                                    id="zero_trust_enhanced_logging"
                                    v-model="zeroTrustEnhancedLoggingEnabled"
                                    @change="markChanged('zero_trust_enhanced_logging_enabled')"
                                    class="rounded border-gray-700 text-pink-500 focus:ring-pink-500 bg-gray-800/30"
                                />
                                <label for="zero_trust_enhanced_logging" class="text-sm text-gray-300 ml-2">
                                    Enhanced Security Logging
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { ExternalLinkIcon, EyeIcon, EyeOffIcon } from 'lucide-vue-next';

// Props
const props = defineProps<{
    settings: Record<string, string>;
}>();

// Emits
const emit = defineEmits<{
    update: [key: string, value: string];
    'bulk-update': [updates: Record<string, string>];
}>();

// Local form data
const formData = ref({
    turnstile_key_pub: '',
    turnstile_key_priv: '',
    firewall_rate_limit: '',
});

// Boolean states
const turnstileEnabled = ref(false);
const firewallEnabled = ref(false);
const firewallBlockVPN = ref(false);
const firewallBlockAlts = ref(false);
const showNodePing = ref(false);
const antiAdblockerEnabled = ref(false);
const zeroTrustServerScanEnabled = ref(false);
const zeroTrustWhitelistIPsEnabled = ref(false);
const zeroTrustBlockTorEnabled = ref(false);
const zeroTrustEnhancedLoggingEnabled = ref(false);

// Show/hide sensitive data
const showSecretKey = ref(false);

// Track changed fields
const changedFields = ref<Set<string>>(new Set());

// Mark a field as changed
const markChanged = (field: string) => {
    changedFields.value.add(field);

    // Emit the change to parent
    let value: string;

    switch (field) {
        case 'turnstile_enabled':
            value = turnstileEnabled.value ? 'true' : 'false';
            break;
        case 'firewall_enabled':
            value = firewallEnabled.value ? 'true' : 'false';
            break;
        case 'firewall_block_vpn':
            value = firewallBlockVPN.value ? 'true' : 'false';
            break;
        case 'firewall_block_alts':
            value = firewallBlockAlts.value ? 'true' : 'false';
            break;
        case 'show_node_ping':
            value = showNodePing.value ? 'true' : 'false';
            break;
        case 'anti_adblocker_enabled':
            value = antiAdblockerEnabled.value ? 'true' : 'false';
            break;
        case 'zero_trust_server_scan_tool_enabled':
            value = zeroTrustServerScanEnabled.value ? 'true' : 'false';
            break;
        case 'zero_trust_whitelist_ips_enabled':
            value = zeroTrustWhitelistIPsEnabled.value ? 'true' : 'false';
            break;
        case 'zero_trust_block_tor_enabled':
            value = zeroTrustBlockTorEnabled.value ? 'true' : 'false';
            break;
        case 'zero_trust_enhanced_logging_enabled':
            value = zeroTrustEnhancedLoggingEnabled.value ? 'true' : 'false';
            break;
        default:
            const formValue = formData.value[field as keyof typeof formData.value];
            value = typeof formValue === 'boolean' ? String(formValue) : (formValue as string);
    }

    emit('update', field, value);
};

// Initialize form with settings values
watch(
    () => props.settings,
    (newSettings) => {
        if (newSettings) {
            formData.value = {
                turnstile_key_pub: newSettings['turnstile_key_pub'] || '',
                turnstile_key_priv: newSettings['turnstile_key_priv'] || '',
                firewall_rate_limit: newSettings['firewall_rate_limit'] || '',
            };

            // Set boolean values
            turnstileEnabled.value = newSettings['turnstile_enabled'] === 'true';
            firewallEnabled.value = newSettings['firewall_enabled'] === 'true';
            firewallBlockVPN.value = newSettings['firewall_block_vpn'] === 'true';
            firewallBlockAlts.value = newSettings['firewall_block_alts'] === 'true';
            showNodePing.value = newSettings['show_node_ping'] === 'true';
            antiAdblockerEnabled.value = newSettings['anti_adblocker_enabled'] === 'true';
            zeroTrustServerScanEnabled.value = newSettings['zero_trust_server_scan_tool_enabled'] === 'true';
            zeroTrustWhitelistIPsEnabled.value = newSettings['zero_trust_whitelist_ips_enabled'] === 'true';
            zeroTrustBlockTorEnabled.value = newSettings['zero_trust_block_tor_enabled'] === 'true';
            zeroTrustEnhancedLoggingEnabled.value = newSettings['zero_trust_enhanced_logging_enabled'] === 'true';

            // Clear changed fields when settings are loaded
            changedFields.value.clear();
        }
    },
    { immediate: true },
);
</script>
