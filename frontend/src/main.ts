import './assets/main.css';

import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import router from './router';
import VueSweetalert2 from 'vue-sweetalert2';
import { createI18n } from 'vue-i18n';
import './assets/sweetalert2.css';

// Import performance optimizations
import { initializePerformanceOptimizations, updatePerformanceSettings } from '@/utils/performance';

// Load custom CSS and JS
const loadCustomResources = () => {
    // Load custom CSS
    const cssLink = document.createElement('link');
    cssLink.rel = 'stylesheet';
    cssLink.href = '/api/system/custom.css';
    document.head.appendChild(cssLink);

    // Load custom JS
    const script = document.createElement('script');
    script.src = '/api/system/custom.js';
    document.head.appendChild(script);
};

// Call the function to load resources
loadCustomResources();

// Security check: Warn if accessing via HTTP or IP instead of domain
const checkAccessMethod = () => {
    const protocol = window.location.protocol;
    const hostname = window.location.hostname;
    const port = window.location.port;

    // Helper function to validate if hostname is a proper domain
    const isValidDomain = (host: string): boolean => {
        // Must contain at least one dot and not start with a number
        if (!host.includes('.') || /^[0-9]/.test(host)) return false;

        // Must not be localhost or IP-like patterns
        if (
            host === 'localhost' ||
            /^(\d{1,3}\.){3}\d{1,3}$/.test(host) ||
            /^([0-9a-fA-F]{1,4}:){7}[0-9a-fA-F]{1,4}$/.test(host)
        )
            return false;

        // Must have valid TLD (at least 2 characters)
        const parts = host.split('.');
        if (parts.length < 2) return false;

        const tld = parts[parts.length - 1] ?? '';
        if (tld.length < 2) return false;

        // Must not be private IP ranges
        if (host.startsWith('192.168.') || host.startsWith('10.') || host.startsWith('172.')) return false;

        return true;
    };

    // Check if accessing via HTTP (not HTTPS)
    const isHttp = protocol === 'http:';

    // Check if accessing via invalid domain (IP, localhost, or malformed domain)
    const isInvalidDomain = !isValidDomain(hostname);

    // Check if accessing via port (not standard 80/443)
    const isNonStandardPort = port && port !== '80' && port !== '443' && port !== '';

    if (isHttp || isInvalidDomain || isNonStandardPort) {
        const currentUrl = window.location.href;
        const detectedIssues = [];

        if (isHttp) detectedIssues.push('HTTP connection (use HTTPS)');
        if (isInvalidDomain) detectedIssues.push('Invalid domain access (use proper domain)');
        if (isNonStandardPort) detectedIssues.push(`Port ${port} (use reverse proxy)`);

        // Allow Dev access
        if (window.location.hostname === '212.87.213.116' && window.location.port === '5173') {
            return;
        }

        // Create warning modal
        const warningModal = document.createElement('div');
        warningModal.innerHTML = `
            <div class="fixed inset-0 bg-black/90 z-[999999] flex items-center justify-center p-4 animate-in fade-in duration-300">
                <div class="bg-gray-900 border-2 border-red-500 rounded-xl p-8 max-w-md w-full text-white shadow-2xl animate-in zoom-in-95 duration-300">
                    <div class="text-center mb-6">
                        <div class="text-red-500 text-4xl mb-4">⚠️</div>
                        <h1 class="text-2xl font-bold text-red-500 mb-2">
                            MythicalDash Access Denied
                        </h1>
                        <p class="text-gray-300 text-sm">
                            You can't use MythicalDash on a port or local connection
                        </p>
                    </div>
                    
                    <div class="bg-gray-800 rounded-lg p-4 mb-6">
                        <h3 class="text-red-400 font-semibold mb-3">Detected Issues:</h3>
                        <ul class="text-gray-300 text-sm space-y-1">
                            ${detectedIssues.map((issue) => `<li class="flex items-center"><span class="text-red-400 mr-2">•</span>${issue}</li>`).join('')}
                        </ul>
                        <div class="mt-3 pt-3 border-t border-gray-700">
                            <p class="text-gray-400 text-xs">
                                Current URL: <code class="bg-gray-700 px-2 py-1 rounded text-xs">${currentUrl}</code>
                            </p>
                        </div>
                    </div>
                    
                    <div class="bg-blue-900/30 rounded-lg p-4 mb-6">
                        <h3 class="text-blue-400 font-semibold mb-2">Required Setup:</h3>
                        <p class="text-gray-300 text-sm mb-3">
                            You need to reverse proxy the port MythicalDash is running on${port ? ` (detected port: ${port})` : ''} using a reverse proxy.
                        </p>
                        <p class="text-gray-400 text-xs">
                            For detailed setup instructions, visit our documentation:
                        </p>
                        <a href="https://docs.mythical.systems" target="_blank" class="inline-flex items-center text-blue-400 hover:text-blue-300 text-sm mt-2 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                            <span class="font-medium">docs.mythical.systems</span>
                        </a>
                    </div>
                    
                    <div class="flex gap-3">
                        <button onclick="window.location.reload()" class="flex-1 bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors">
                            Refresh Page
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(warningModal);

        // Prevent further execution
        throw new Error('Access denied: MythicalDash cannot be used on HTTP, IP, or port access');
    }
};

// Run security check before app initialization
checkAccessMethod();

// Performance optimization: Create app with production tip disabled
const app = createApp(App, {
    // Disable production tip
    productionTip: false,
});

const pinia = createPinia();
const locale = localStorage.getItem('locale') || 'EN';

// Performance optimization: Configure i18n with runtimeOnly
const i18n = createI18n({
    legacy: false,
    locale: locale,
    fallbackLocale: 'EN',
    runtimeOnly: true,
    messages: {
        EN: {},
        RO: {},
        FR: {},
        DE: {},
        ES: {},
        MD: {},
    },
});

// Performance optimization: Lazy load translations with caching
const messageCache = new Map();
const FALLBACK_LOCALE = 'EN';

const loadLocaleMessages = async (locale: string) => {
    // Always load fallback first if not loaded
    if (!messageCache.has(FALLBACK_LOCALE)) {
        try {
            const fallbackMessages = await import(`@/locale/${FALLBACK_LOCALE.toLowerCase()}.yml`);
            messageCache.set(FALLBACK_LOCALE, fallbackMessages.default);
            i18n.global.setLocaleMessage(FALLBACK_LOCALE, fallbackMessages.default);
        } catch (error) {
            console.error(`Failed to load fallback locale messages for ${FALLBACK_LOCALE}:`, error);
        }
    }

    // Then load the requested locale if different
    if (locale !== FALLBACK_LOCALE && !messageCache.has(locale)) {
        try {
            const messages = await import(`@/locale/${locale.toLowerCase()}.yml`);
            messageCache.set(locale, messages.default);
            i18n.global.setLocaleMessage(locale, messages.default);
        } catch (error) {
            console.error(`Failed to load locale messages for ${locale}:`, error);
        }
    }
};

// Load initial locale
await loadLocaleMessages(locale);
i18n.global.locale.value = locale as 'EN' | 'RO' | 'FR' | 'DE' | 'ES' | 'MD';

// Performance optimization: Disable devtools in production
if (import.meta.env.PROD) {
    // @ts-expect-error - devtools is a valid property but not in types
    app.config.devtools = false;

    // Performance optimization: Disable warnings in production
    app.config.warnHandler = () => null;
}

// Performance optimization: Register plugins with proper error handling and lazy loading
const registerPlugins = async () => {
    try {
        app.use(i18n);
        app.use(pinia);
        app.use(router);
        app.use(VueSweetalert2);
    } catch (error) {
        console.error('Failed to initialize Vue plugins:', error);
    }
};

// Initialize performance optimizations early
const initializePerformance = () => {
    try {
        // Initialize performance optimizations
        initializePerformanceOptimizations();

        // Set up performance settings watcher
        if (typeof window !== 'undefined') {
            // Watch for settings changes in localStorage
            window.addEventListener('storage', (e) => {
                if (e.key === 'ui-settings') {
                    updatePerformanceSettings();
                }
            });

            // Also watch for direct settings updates
            const originalSetItem = localStorage.setItem;
            localStorage.setItem = function (key: string, value: string) {
                const result = originalSetItem.call(this, key, value);
                if (key === 'ui-settings') {
                    // Small delay to allow the new settings to be processed
                    setTimeout(() => {
                        updatePerformanceSettings();
                    }, 10);
                }
                return result;
            };
        }
    } catch (error) {
        console.warn('Failed to initialize performance optimizations:', error);
    }
};

// Mount the app with error boundary and performance monitoring
const mountApp = async () => {
    try {
        // Initialize performance optimizations first
        initializePerformance();

        await registerPlugins();

        // Performance optimization: Use requestAnimationFrame for mounting
        requestAnimationFrame(() => {
            app.mount('#app');
        });
    } catch (error) {
        console.error('Failed to mount Vue app:', error);
        document.getElementById('app')!.innerHTML = `
            <div style="text-align: center; padding: 20px;">
                <h2>Application Error</h2>
                <p>Sorry, something went wrong. Please try refreshing the page.</p>
            </div>
        `;
    }
};

mountApp();
