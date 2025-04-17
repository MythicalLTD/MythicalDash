<template>
    <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
        <div class="bg-gray-900 rounded-lg w-full max-w-5xl overflow-hidden shadow-2xl">
            <div class="flex items-center justify-between p-4 border-b border-gray-800">
                <h3 class="text-lg font-medium text-white">Template Preview</h3>
                <div class="flex items-center space-x-2">
                    <button
                        @click="toggleFullscreen"
                        class="text-gray-400 hover:text-white p-1 rounded-full hover:bg-gray-800 transition"
                        title="Toggle fullscreen"
                    >
                        <MaximizeIcon v-if="!isFullscreen" class="w-5 h-5" />
                        <MinimizeIcon v-else class="w-5 h-5" />
                    </button>
                    <button
                        @click="close"
                        class="text-gray-400 hover:text-white p-1 rounded-full hover:bg-gray-800 transition"
                        title="Close"
                    >
                        <XIcon class="w-5 h-5" />
                    </button>
                </div>
            </div>

            <div class="p-4 border-b border-gray-800 bg-gray-800/50 flex items-center">
                <div class="flex-1">
                    <div class="text-sm text-gray-400 mb-1">Preview with sample data:</div>
                    <div class="flex flex-wrap gap-2">
                        <span
                            v-for="(value, key) in previewValues"
                            :key="key"
                            class="inline-flex items-center bg-gray-800 px-2 py-1 rounded-md text-xs"
                        >
                            <span class="text-pink-400">{{ key }}</span>
                            <span class="text-gray-500 mx-1">=</span>
                            <span class="text-white">{{ value }}</span>
                        </span>
                    </div>
                </div>
                <div>
                    <button
                        @click="refreshPreview"
                        title="Refresh Preview"
                        class="p-1 text-gray-400 hover:text-white hover:bg-gray-800 rounded-full transition"
                    >
                        <RefreshCcwIcon class="w-5 h-5" />
                    </button>
                </div>
            </div>

            <div :class="['overflow-auto', isFullscreen ? 'h-screen' : 'max-h-[60vh]']">
                <div class="bg-white rounded-sm m-4 shadow">
                    <iframe
                        ref="previewFrame"
                        title="Template Preview"
                        class="w-full border-0"
                        :style="{ height: previewHeight + 'px' }"
                    ></iframe>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, defineEmits } from 'vue';
import { XIcon, MaximizeIcon, MinimizeIcon, RefreshCcwIcon } from 'lucide-vue-next';

const props = defineProps({
    isOpen: {
        type: Boolean,
        default: false,
    },
    htmlContent: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['close']);

const previewFrame = ref<HTMLIFrameElement | null>(null);
const isFullscreen = ref(false);
const previewHeight = ref(400);

// Sample data for preview
const previewValues = {
    '{user_name}': 'John Doe',
    '{user_email}': 'john@example.com',
    '{server_name}': 'Main Server',
    '{company_name}': 'MythicalSystems',
};

// Replace template variables with sample data
const processContent = (content: string): string => {
    let processed = content;
    Object.entries(previewValues).forEach(([key, value]) => {
        processed = processed.replace(new RegExp(key, 'g'), value);
    });
    return processed;
};

// Render the preview
const renderPreview = () => {
    if (!previewFrame.value) return;

    const processed = processContent(props.htmlContent);

    // Get the iframe document
    const doc = previewFrame.value.contentDocument || previewFrame.value.contentWindow?.document;
    if (!doc) return;

    // Write content to iframe
    doc.open();
    doc.write(processed);
    doc.close();

    // Adjust iframe height to content
    setTimeout(() => {
        if (previewFrame.value && previewFrame.value.contentWindow) {
            const body = previewFrame.value.contentWindow.document.body;
            const html = previewFrame.value.contentWindow.document.documentElement;
            const height = Math.max(
                body.scrollHeight,
                body.offsetHeight,
                html.clientHeight,
                html.scrollHeight,
                html.offsetHeight,
            );
            previewHeight.value = Math.max(height, 400);
        }
    }, 100);
};

// Toggle fullscreen mode
const toggleFullscreen = () => {
    isFullscreen.value = !isFullscreen.value;
};

// Refresh the preview
const refreshPreview = () => {
    renderPreview();
};

// Close the modal
const close = () => {
    emit('close');
};

// Watch for changes to refresh the preview
watch(
    () => props.isOpen,
    (newVal) => {
        if (newVal) {
            // Slight delay to ensure the iframe is mounted
            setTimeout(renderPreview, 100);
        }
    },
);

watch(
    () => props.htmlContent,
    () => {
        if (props.isOpen) {
            renderPreview();
        }
    },
);

onMounted(() => {
    if (props.isOpen) {
        renderPreview();
    }
});
</script>
