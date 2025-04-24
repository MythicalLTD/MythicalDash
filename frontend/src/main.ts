import './assets/main.css';

import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import router from './router';
import VueSweetalert2 from 'vue-sweetalert2';
import { createI18n } from 'vue-i18n';
import './assets/sweetalert2.css';

const app = createApp(App);
const pinia = createPinia();
const locale = localStorage.getItem('locale') || 'EN';

const i18n = createI18n({
    legacy: false,
    locale: locale,
    fallbackLocale: 'EN',
    messages: {
        EN: {},
        RO: {},
        FR: {},
        DE: {},
        ES: {},
        MD: {},
    },
});

// Lazy load translations
const loadLocaleMessages = async (locale: string) => {
    try {
        const messages = await import(`@/locale/${locale.toLowerCase()}.yml`);
        i18n.global.setLocaleMessage(locale, messages.default);
    } catch (error) {
        console.error(`Failed to load locale messages for ${locale}:`, error);
    }
};

// Load initial locale
loadLocaleMessages(locale);
// Error handling for Vue app
app.config.errorHandler = (err, instance, info) => {
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
};

// Performance optimization: Disable devtools in production
if (import.meta.env.PROD) {
    // @ts-expect-error - devtools is a valid property but not in types
    app.config.devtools = false;
}

// Register plugins with proper error handling
try {
    app.use(i18n);
    app.use(pinia);
    app.use(router);
    app.use(VueSweetalert2);
} catch (error) {
    console.error('Failed to initialize Vue plugins:', error);
}

// Mount the app with error boundary
try {
    app.mount('#app');
} catch (error) {
    console.error('Failed to mount Vue app:', error);
    // You might want to show a user-friendly error message here
    document.getElementById('app')!.innerHTML = `
        <div style="text-align: center; padding: 20px;">
            <h2>Application Error</h2>
            <p>Sorry, something went wrong. Please try refreshing the page.</p>
        </div>
    `;
}
