import './assets/main.css';

import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import router from './router';
import VueSweetalert2 from 'vue-sweetalert2';
import { createI18n } from 'vue-i18n';
import EN from '@/locale/en.yml';
import './assets/sweetalert2.css';

const app = createApp(App);
const pinia = createPinia();
const locale = localStorage.getItem('locale') || 'EN';
const i18n = createI18n({
    legacy: false,
    locale: locale,
    messages: {
        EN: EN,
    },
});

app.use(i18n);
app.use(pinia);
app.use(router);
app.use(VueSweetalert2);

app.mount('#app');
