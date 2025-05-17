<template>
    <LayoutDashboard>
        <div class="p-6 max-w-7xl mx-auto">
            <!-- Header Section -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-100 mb-2">{{ $t('images.config.title') }}</h1>
                <p class="text-gray-400">{{ $t('images.config.description') }}</p>
            </div>

            <!-- Main Content Grid -->
            <div class="space-y-8">
                <!-- API Key Card -->
                <CardComponent class="relative overflow-hidden group">
                    <template #header-action>
                        <Button
                            :text="$t('images.config.api_key.copy')"
                            variant="ghost"
                            small
                            @click="copyApiKey"
                            class="opacity-0 group-hover:opacity-100 transition-opacity"
                        >
                            <template #icon>
                                <Copy class="w-4 h-4" />
                            </template>
                        </Button>
                    </template>
                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-indigo-500/10 rounded-lg">
                                <Key class="w-5 h-5 text-indigo-400" />
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-400">
                                    {{ $t('images.config.api_key.title') }}
                                </h3>
                                <p class="text-sm text-gray-300 mt-1">{{ $t('images.config.api_key.description') }}</p>
                            </div>
                        </div>
                        <div class="relative">
                            <div
                                class="bg-[#1a1a2e] rounded-lg p-3 pr-12 group/key"
                                @mouseenter="showKey = true"
                                @mouseleave="showKey = false"
                            >
                                <code
                                    class="text-sm text-gray-300 font-mono select-all transition-all duration-300"
                                    :class="{ 'blur-sm': !showKey }"
                                    ref="apiKeyElement"
                                    >{{ apiKey }}</code
                                >
                            </div>
                            <div class="absolute right-3 top-1/2 -translate-y-1/2">
                                <Button
                                    variant="ghost"
                                    small
                                    @click="copyApiKey"
                                    class="opacity-0 group-hover:opacity-100 transition-opacity"
                                >
                                    <template #icon>
                                        <Copy class="w-4 h-4" />
                                    </template>
                                </Button>
                            </div>
                        </div>
                    </div>
                </CardComponent>

                <!-- ShareX Config Card -->
                <CardComponent class="relative overflow-hidden group">
                    <div class="space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-blue-500/10 rounded-lg">
                                <Download class="w-5 h-5 text-blue-400" />
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-400">
                                    {{ $t('images.config.sharex.title') }}
                                </h3>
                                <p class="text-sm text-gray-300 mt-1">{{ $t('images.config.sharex.description') }}</p>
                            </div>
                        </div>
                        <div class="bg-[#1a1a2e] rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <FileJson class="w-5 h-5 text-blue-400" />
                                    <span class="text-sm text-gray-300">{{ $t('images.config.sharex.filename') }}</span>
                                </div>
                                <Button
                                    :text="$t('images.config.sharex.download')"
                                    variant="secondary"
                                    small
                                    @click="downloadShareXConfig"
                                    class="group-hover:bg-blue-500/20 group-hover:text-blue-400 transition-colors"
                                >
                                    <template #icon>
                                        <Download class="w-4 h-4" />
                                    </template>
                                </Button>
                            </div>
                        </div>
                    </div>
                </CardComponent>
            </div>
        </div>
    </LayoutDashboard>
</template>

<script setup lang="ts">
import LayoutDashboard from '@/components/client/LayoutDashboard.vue';
import CardComponent from '@/components/client/ui/Card/CardComponent.vue';
import Button from '@/components/client/ui/Button.vue';
import { ref } from 'vue';
import { Copy, Key, Download, FileJson } from 'lucide-vue-next';
import Session from '@/mythicaldash/Session';

// Mock API key for demonstration
const apiKey = Session.getInfo('image_hosting_upload_key');
const showKey = ref(false);
const apiKeyElement = ref<HTMLElement | null>(null);

const copyApiKey = () => {
    if (!apiKeyElement.value) return;

    // Create a temporary input element
    const tempInput = document.createElement('input');
    tempInput.value = apiKey;
    document.body.appendChild(tempInput);

    // Select and copy the text
    tempInput.select();
    document.execCommand('copy');

    // Clean up
    document.body.removeChild(tempInput);

    // Show copied state
    showKey.value = true;
    setTimeout(() => {
        showKey.value = false;
    }, 2000);
};

const downloadShareXConfig = () => {
    // Mock ShareX config download
    window.open('/api/user/images/sharex/download', '_blank');
};
</script>

<style scoped>
.group:hover .group-hover\:opacity-100 {
    opacity: 1;
}

.group-hover\:opacity-100 {
    opacity: 0;
    transition: opacity 0.2s ease-in-out;
}
</style>
