<template>
    <div class="flex flex-col items-center justify-center min-h-screen bg-gray-900">
        <div class="text-center">
            <div class="mb-4">
                <svg
                    class="animate-spin h-12 w-12 text-purple-500 mx-auto"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                >
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path
                        class="opacity-75"
                        fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                    ></path>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">Logging out...</h1>
            <p class="text-gray-400">Please wait while we securely log you out</p>
        </div>
    </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();

const clearData = async () => {
    // Explicitly remove user_token
    localStorage.removeItem('user_token');

    // Clear localStorage
    localStorage.clear();

    // Clear sessionStorage
    sessionStorage.removeItem('user_token');
    sessionStorage.clear();
    document.cookie = 'user_token=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/';
    // Clear all cookies
    const cookies = document.cookie.split(';');
    for (let i = 0; i < cookies.length; i++) {
        const cookie = cookies[i];
        const eqPos = cookie.indexOf('=');
        const name = eqPos > -1 ? cookie.substr(0, eqPos) : cookie;
        document.cookie = name + '=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/';
    }

    // Array to collect all async operations
    const promises = [];

    // Clear IndexedDB
    const indexedDBPromise = window.indexedDB.databases().then((dbs) => {
        return Promise.all(
            dbs.map((db) => {
                if (db.name) {
                    return window.indexedDB.deleteDatabase(db.name);
                }
                return Promise.resolve();
            }),
        );
    });
    promises.push(indexedDBPromise);

    // Clear cache storage
    if ('caches' in window) {
        const cachePromise = caches.keys().then((names) => {
            return Promise.all(names.map((name) => caches.delete(name)));
        });
        promises.push(cachePromise);
    }

    // Clear service workers
    if ('serviceWorker' in navigator) {
        const serviceWorkerPromise = navigator.serviceWorker.getRegistrations().then((registrations) => {
            return Promise.all(registrations.map((registration) => registration.unregister()));
        });
        promises.push(serviceWorkerPromise);
    }

    // Wait for all async operations to complete
    await Promise.all(promises);
};

onMounted(async () => {
    // Wait for clearData to complete before redirecting
    await clearData();
    router.push('/auth/login');
});
</script>
