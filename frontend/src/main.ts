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
