<template>
    <div class="min-h-screen bg-gray-900 flex flex-col">
        <!-- Header -->
        <GuestHeader :title="t('privacy_policy.title')" back-link="/dashboard">
            <template #metadata>
                <span>{{ t('common.last_updated') }}: {{ formatDate(lastUpdated) }}</span>
            </template>
        </GuestHeader>

        <!-- Main Content -->
        <main class="flex-1 w-full">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="bg-gray-800/50 rounded-lg p-8 prose prose-invert max-w-none min-h-[300px]">
                    <template v-if="loading">
                        <div class="flex justify-center items-center h-40">
                            <div
                                class="animate-spin rounded-full h-8 w-8 border-2 border-purple-500/20 border-t-purple-500"
                            ></div>
                        </div>
                    </template>
                    <template v-else-if="error">
                        <div class="text-red-400 text-center">{{ error }}</div>
                    </template>
                    <template v-else>
                        <h2 class="text-2xl font-bold text-white mb-6">{{ t('privacy_policy.title') }}</h2>
                        <div v-html="privacyHtml"></div>
                    </template>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <GuestFooter :left-text="t('privacy_policy.title')" right-text="© 2024 McCloudAdmin" />
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useI18n } from 'vue-i18n';
import GuestHeader from '@/components/guest/GuestHeader.vue';
import GuestFooter from '@/components/guest/GuestFooter.vue';

const { t } = useI18n();
const lastUpdated = ref('2024-01-01');
const privacyHtml = ref('');
const loading = ref(true);
const error = ref('');

function formatDate(date: string) {
    return new Date(date).toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
}

onMounted(async () => {
    loading.value = true;
    error.value = '';
    try {
        const res = await fetch('/api/system/settings');
        const data = await res.json();
        if (data.success && data.settings && typeof data.settings.legal_privacy === 'string') {
            privacyHtml.value = data.settings.legal_privacy;
        } else {
            error.value = t('errors.servererror.message');
        }
    } catch {
        error.value = t('errors.servererror.message');
    } finally {
        loading.value = false;
    }
});
</script>
