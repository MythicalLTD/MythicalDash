import { defineStore } from 'pinia';
import { ref } from 'vue';
import { createApp } from 'vue';
import ErrorPage from '../components/ErrorPage.vue';

interface SettingsResponse {
    success: boolean;
    message?: string;
    settings: Record<string, string>;
    core: Record<string, string>;
}

interface CachedSettings {
    value: Record<string, string>;
    timestamp: number;
}

export const useSettingsStore = defineStore('settings', () => {
    const settings = ref<Record<string, string>>({});
    const isInitialized = ref(false);
    const CACHE_DURATION = 30 * 60 * 1000; // 30 minutes in milliseconds
    const CACHE_KEY = 'mythicaldash_settings_cache';

    function loadFromCache(): boolean {
        const cached = localStorage.getItem(CACHE_KEY);
        if (!cached) return false;

        try {
            const { value, timestamp }: CachedSettings = JSON.parse(cached);
            const now = Date.now();

            if (now - timestamp < CACHE_DURATION) {
                settings.value = value;
                isInitialized.value = true;
                return true;
            }

            // Clear expired cache
            localStorage.removeItem(CACHE_KEY);
            return false;
        } catch {
            return false;
        }
    }

    function saveToCache(settingsData: Record<string, string>) {
        const cache: CachedSettings = {
            value: settingsData,
            timestamp: Date.now(),
        };
        localStorage.setItem(CACHE_KEY, JSON.stringify(cache));
    }

    function showErrorPage(title: string, message: string, errorCode?: string) {
        const app = createApp(ErrorPage, {
            title,
            message,
            errorCode,
        });
        document.body.innerHTML = '';
        app.mount(document.body);
    }
    async function fetchSettings(): Promise<SettingsResponse> {
        const response = await fetch('/api/system/settings');

        if (!response.ok) {
            if (response.status === 500) {
                showErrorPage('Error', 'An unexpected server error occurred. Please try again later.', 'SERVER_ERROR');
                throw new Error('Server error');
            }
            // Handle non-JSON responses
            const text = await response.text();
            showErrorPage('Error', 'Invalid response from server. Please try again later.', 'INVALID_RESPONSE');
            throw new Error(`Invalid response: ${text}`);
        }

        // Check if the response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            showErrorPage('Error', 'Server returned non-JSON response. Please try again later.', 'INVALID_RESPONSE');
            throw new Error(`Invalid response type: ${contentType}, content: ${text}`);
        }

        let data;
        try {
            data = await response.json();
        } catch (error) {
            console.error('Failed to fetch settings:', error);
            showErrorPage('Error', 'Failed to parse server response. Please try again later.', 'PARSE_ERROR');
            throw error;
        }

        if (!data.success) {
            if (data.error_code === 'RATE_LIMITED') {
                showErrorPage('Rate Limited', 'You have been rate limited. Please try again later.', data.error_code);
            } else {
                showErrorPage(
                    'Error',
                    data.message || 'An unexpected error occurred. Please try again later.',
                    data.error_code,
                );
            }
            throw new Error(data.message || 'Failed to fetch settings');
        }
        return data;
    }

    async function initialize() {
        if (isInitialized.value) return;

        // Try loading from cache first
        if (loadFromCache()) {
            return;
        }
        try {
            const data = await fetchSettings();
            settings.value = { ...data.settings, ...data.core };

            // Store in localStorage for persistence
            saveToCache(settings.value);

            Object.entries(settings.value).forEach(([key, value]) => {
                localStorage.setItem(key, JSON.stringify(value));
            });

            isInitialized.value = true;
        } catch (error) {
            console.error('Failed to initialize settings:', error);
            throw error;
        }
    }

    function getSetting(key: string): string {
        // Try to get from store first
        if (settings.value[key] !== undefined) {
            return settings.value[key];
        }

        // Try to get from localStorage
        const item = localStorage.getItem(key);
        if (item) {
            try {
                const value = JSON.parse(item);
                settings.value[key] = value; // Cache in store
                return value;
            } catch {
                return item;
            }
        }

        // If not found and not initialized, trigger initialization
        if (!isInitialized.value) {
            const loadingElement = document.createElement('div');
            loadingElement.id = 'loading-animation';
            loadingElement.innerHTML = '<p>Loading...</p>';
            document.body.appendChild(loadingElement);

            initialize().finally(() => {
                document.body.removeChild(loadingElement);
            });
        }

        return 'Fetching settings...';
    }

    // Helper function to get boolean settings consistently
    function getBooleanSetting(key: string): boolean {
        const value = getSetting(key);
        return value === 'true';
    }

    // Function to force refresh settings
    async function refreshSettings() {
        try {
            const data = await fetchSettings();
            settings.value = { ...data.settings, ...data.core };
            saveToCache(settings.value);

            // Update individual localStorage items
            Object.entries(settings.value).forEach(([key, value]) => {
                localStorage.setItem(key, JSON.stringify(value));
            });
        } catch (error) {
            console.error('Failed to refresh settings:', error);
            throw error;
        }
    }

    return {
        settings,
        isInitialized,
        initialize,
        getSetting,
        getBooleanSetting,
        refreshSettings,
    };
});
