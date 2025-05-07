<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { FileQuestionIcon, ExternalLinkIcon } from 'lucide-vue-next';
import ErrorPage from '@/components/client/Errors/ErrorPage.vue';
import Translation from '@/mythicaldash/Translation';
import { MythicalDOM } from '@/mythicaldash/MythicalDOM';
import { useI18n } from 'vue-i18n';
import { useRoute } from 'vue-router';
import { LoaderCircle } from 'lucide-vue-next';

const { t } = useI18n();
const route = useRoute();
const loading = ref(true);
const redirecting = ref(false);
const countdown = ref(5);
const targetUrl = ref('');

MythicalDOM.setPageTitle(t('errors.notfound.title'));

const startCountdown = (url: string) => {
    targetUrl.value = url;
    redirecting.value = true;
    const timer = setInterval(() => {
        countdown.value--;
        if (countdown.value <= 0) {
            clearInterval(timer);
            window.location.href = url;
        }
    }, 1000);
};

const checkRedirectLink = async () => {
    try {
        const response = await fetch('/api/system/redirect-links', {
            method: 'GET',
            headers: {
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error('Failed to fetch redirect links');
        }

        const data = await response.json();

        if (data.success) {
            const path = route.path.substring(1);
            const redirectLink = data.redirect_links.find((link: { name: string }) => link.name === path);

            if (redirectLink) {
                startCountdown(redirectLink.link);
                return;
            }
        }
    } catch (err) {
        console.error('Error checking redirect link:', err);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    checkRedirectLink();
});
</script>

<template>
    <div v-if="loading" class="flex justify-center items-center min-h-screen bg-gray-900">
        <div class="text-center space-y-4">
            <LoaderCircle class="h-12 w-12 animate-spin text-pink-400 mx-auto" />
            <p class="text-gray-400">{{ Translation.getTranslation('common.loading') }}</p>
        </div>
    </div>
    <div v-else-if="redirecting" class="min-h-screen flex items-center justify-center bg-gray-900">
        <div
            class="text-center space-y-8 p-8 rounded-2xl bg-gray-800/50 backdrop-blur-sm max-w-md w-full mx-4 border border-gray-700/50 shadow-2xl"
        >
            <div class="relative">
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="w-32 h-32 border-4 border-pink-500/20 rounded-full animate-pulse"></div>
                </div>
                <div class="relative">
                    <ExternalLinkIcon class="w-16 h-16 text-pink-400 mx-auto animate-bounce" />
                </div>
            </div>
            <div class="space-y-4">
                <h2
                    class="text-3xl font-bold text-white bg-gradient-to-r from-pink-400 to-violet-400 bg-clip-text text-transparent"
                >
                    {{ Translation.getTranslation('redirect.title') }}
                </h2>
                <p class="text-gray-400">{{ Translation.getTranslation('redirect.message') }}</p>
                <a
                    :href="targetUrl"
                    class="text-pink-400 hover:text-pink-300 transition-colors break-all block bg-gray-700/50 p-4 rounded-lg hover:bg-gray-700/70"
                    target="_blank"
                >
                    {{ targetUrl }}
                </a>
                <div class="text-gray-400">
                    {{ Translation.getTranslation('redirect.countdown') + ' ' + countdown }}
                </div>
            </div>
            <div class="pt-4">
                <a
                    :href="targetUrl"
                    class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-pink-500 to-violet-500 text-white rounded-lg hover:opacity-90 transition-all duration-200 transform hover:scale-105 shadow-lg shadow-pink-500/20"
                >
                    <ExternalLinkIcon class="w-5 h-5 mr-2" />
                    {{ Translation.getTranslation('redirect.go_now') }}
                </a>
            </div>
        </div>
    </div>
    <ErrorPage
        v-else
        :icon="FileQuestionIcon"
        :title="Translation.getTranslation('errors.notfound.title')"
        :message="Translation.getTranslation('errors.notfound.message')"
    />
</template>
