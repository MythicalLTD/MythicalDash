import './assets/main.css';

import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import router from './router';
import VueSweetalert2 from 'vue-sweetalert2';
import { createI18n } from 'vue-i18n';
import './assets/sweetalert2.css';

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
const loadLocaleMessages = async (locale: string) => {
    if (messageCache.has(locale)) {
        i18n.global.setLocaleMessage(locale, messageCache.get(locale));
        return;
    }

    try {
        const messages = await import(`@/locale/${locale.toLowerCase()}.yml`);
        messageCache.set(locale, messages.default);
        i18n.global.setLocaleMessage(locale, messages.default);
    } catch (error) {
        console.error(`Failed to load locale messages for ${locale}:`, error);
    }
};

// Load initial locale
loadLocaleMessages(locale);

// Performance optimization: Debounced error handler
let errorTimeout: number | null = null;
app.config.errorHandler = (err, instance, info) => {
    if (errorTimeout) {
        clearTimeout(errorTimeout);
    }

    errorTimeout = window.setTimeout(() => {
        console.error('🚨 Vue Application Error 🚨\n', {
            error: err,
            message: err instanceof Error ? err.message : 'Unknown error',
            stack: err instanceof Error ? err.stack : undefined,
        });
        console.error('🔍 Component Details:', {
            name: instance?.$options?.name || 'Anonymous Component',
            props: instance?.$props || {},
            data: instance?.$data || {},
        });
        console.error('📋 Error Context:', {
            info,
            timestamp: new Date().toISOString(),
            environment: import.meta.env.MODE,
            vueVersion: instance?.$root?.$options?.version,
        });
    }, 100);
};

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

// Mount the app with error boundary and performance monitoring
const mountApp = async () => {
    try {
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

if (import.meta.env.PROD) {
    import('web-vitals').then((webVitals) => {
        const { onCLS, onFID, onFCP, onLCP, onTTFB } = webVitals;
        onCLS((metric) => console.log('CLS:', metric));
        onFID((metric) => console.log('FID:', metric));
        onFCP((metric) => console.log('FCP:', metric));
        onLCP((metric) => console.log('LCP:', metric));
        onTTFB((metric) => console.log('TTFB:', metric));
    });
}
