<template>
    <LayoutDashboard>
        <div class="p-6">
            <!-- Header Section -->
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-4">
                    <h1 class="text-2xl font-bold text-gray-100">{{ $t('images.upload.title') }}</h1>
                    <button
                        @click="showDescription = !showDescription"
                        class="p-2 text-gray-400 hover:text-gray-200 transition-colors duration-200"
                        :class="{ 'text-indigo-400': showDescription }"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                    </button>
                </div>
                <router-link
                    to="/images"
                    class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-all duration-200 flex items-center gap-2"
                >
                    {{ $t('images.upload.back_to_gallery') }}
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"
                        />
                    </svg>
                </router-link>
            </div>

            <!-- Description Panel -->
            <div
                v-if="showDescription"
                class="mb-6 p-4 bg-[#18182a] border border-[#2a2a3f]/30 rounded-lg transition-all duration-300"
            >
                <p class="text-gray-300">
                    {{ $t('images.upload.description') }}
                </p>
            </div>

            <!-- Error Message -->
            <div v-if="error" class="mb-6 p-4 bg-red-500/10 border border-red-500/20 rounded-lg animate-fade-in">
                <p class="text-red-400">{{ error }}</p>
            </div>

            <!-- Success Message -->
            <div
                v-if="uploadSuccess"
                class="mb-6 p-4 bg-green-500/10 border border-green-500/20 rounded-lg animate-fade-in"
            >
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <p class="text-green-400">{{ $t('images.upload.success_message') }}</p>
                </div>
                <div v-if="uploadedImage" class="mt-3 p-3 bg-[#18182a] rounded-lg">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-sm text-gray-400">{{ $t('images.upload.image_url') }}:</span>
                        <button
                            @click="copyToClipboard(uploadedImage.url)"
                            class="text-indigo-400 hover:text-indigo-300 text-sm"
                        >
                            {{ $t('images.upload.copy_url') }}
                        </button>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-400">{{ $t('images.upload.embed_url') }}:</span>
                        <span class="text-green-400 text-sm">✓ {{ $t('images.upload.auto_copied') }}</span>
                    </div>
                </div>
            </div>

            <!-- Upload Area -->
            <div class="max-w-2xl mx-auto">
                <div
                    class="border-2 border-dashed border-gray-600 rounded-lg p-8 text-center transition-all duration-200"
                    :class="{
                        'border-indigo-500 bg-indigo-500/10': isDragOver,
                        'border-gray-600 bg-[#18182a]': !isDragOver,
                    }"
                    @drop="handleDrop"
                    @dragover.prevent="isDragOver = true"
                    @dragleave.prevent="isDragOver = false"
                    @dragenter.prevent="isDragOver = true"
                >
                    <div class="mb-4">
                        <svg
                            class="w-16 h-16 mx-auto text-gray-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.5"
                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                            />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-200 mb-2">{{ $t('images.upload.drag_drop_title') }}</h3>
                    <p class="text-gray-400 mb-4">{{ $t('images.upload.drag_drop_description') }}</p>

                    <input ref="fileInput" type="file" accept="image/*" @change="handleFileSelect" class="hidden" />

                    <button
                        @click="(fileInput as HTMLInputElement)?.click()"
                        class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-all duration-200 flex items-center gap-2 mx-auto"
                        :disabled="uploading"
                    >
                        <svg v-if="!uploading" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"
                            />
                        </svg>
                        <svg v-else class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                            />
                        </svg>
                        {{ uploading ? $t('images.upload.uploading') : $t('images.upload.select_file') }}
                    </button>
                </div>

                <!-- File Info -->
                <div v-if="selectedFile" class="mt-6 p-4 bg-[#18182a] border border-[#2a2a3f]/30 rounded-lg">
                    <h4 class="text-lg font-medium text-gray-200 mb-3">{{ $t('images.upload.selected_file') }}</h4>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-400">{{ $t('images.upload.file_name') }}:</span>
                            <span class="text-gray-200">{{ selectedFile.name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">{{ $t('images.upload.file_size') }}:</span>
                            <span class="text-gray-200">{{ formatFileSize(selectedFile.size) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">{{ $t('images.upload.file_type') }}:</span>
                            <span class="text-gray-200">{{ selectedFile.type }}</span>
                        </div>
                    </div>

                    <button
                        @click="uploadFile"
                        :disabled="uploading"
                        class="mt-4 w-full px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-all duration-200 flex items-center justify-center gap-2"
                    >
                        <svg v-if="!uploading" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                            />
                        </svg>
                        <svg v-else class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                            />
                        </svg>
                        {{ uploading ? $t('images.upload.uploading') : $t('images.upload.upload_file') }}
                    </button>
                </div>

                <!-- Upload Limits -->
                <div class="mt-6 p-4 bg-blue-500/10 border border-blue-500/20 rounded-lg">
                    <h4 class="text-lg font-medium text-blue-400 mb-2">{{ $t('images.upload.limits.title') }}</h4>
                    <div class="space-y-1 text-sm text-blue-300">
                        <p>{{ $t('images.upload.limits.max_size', { size: maxFileSize }) }}</p>
                        <p>{{ $t('images.upload.limits.allowed_types') }}</p>
                        <p v-if="coinsPerImageEnabled">
                            {{ $t('images.upload.limits.coins_per_image', { coins: coinsPerImage }) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import LayoutDashboard from '@/components/client/LayoutDashboard.vue';
import Swal from 'sweetalert2';

interface UploadedImage {
    url: string;
    embedUrl: string;
    deleteUrl: string;
}

interface UploadResponse {
    success: boolean;
    image_url: string;
    embed_url: string;
    delete_url: string;
    filename: string;
    size: number;
    type: string;
}

interface ApiResponse {
    success?: boolean;
    data?: UploadResponse;
    message?: string;
}

const router = useRouter();
const fileInput = ref<HTMLInputElement>();
const selectedFile = ref<File | null>(null);
const uploading = ref(false);
const error = ref('');
const uploadSuccess = ref(false);
const uploadedImage = ref<UploadedImage | null>(null);
const isDragOver = ref(false);
const showDescription = ref(false);

// Configuration
const maxFileSize = ref(10); // MB
const allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
const coinsPerImageEnabled = ref(false);
const coinsPerImage = ref(1);

// Get configuration on mount
onMounted(async () => {
    try {
        const response = await fetch('/api/user/images/upload/config');
        const data = await response.json();
        if (data.success) {
            maxFileSize.value = data.max_file_size || 10;
            coinsPerImageEnabled.value = data.coins_per_image_enabled || false;
            coinsPerImage.value = data.coins_per_image || 1;
        }
    } catch (error) {
        console.error('Failed to load upload configuration:', error);
    }
});

const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        const file = target.files[0];
        if (file) {
            validateAndSetFile(file);
        }
    }
};

const handleDrop = (event: DragEvent) => {
    isDragOver.value = false;
    event.preventDefault();

    if (event.dataTransfer?.files && event.dataTransfer.files.length > 0) {
        const file = event.dataTransfer.files[0];
        if (file) {
            validateAndSetFile(file);
        }
    }
};

const validateAndSetFile = (file: File) => {
    error.value = '';

    // Check file type
    const fileExtension = file.name.split('.').pop()?.toLowerCase();
    if (!fileExtension || !allowedTypes.includes(fileExtension)) {
        error.value = 'Invalid file type. Allowed types: ' + allowedTypes.join(', ');
        return;
    }

    // Check file size
    const maxSizeBytes = maxFileSize.value * 1024 * 1024;
    if (file.size > maxSizeBytes) {
        error.value = `File is too large. Maximum size: ${maxFileSize.value}MB`;
        return;
    }

    selectedFile.value = file;
    uploadSuccess.value = false;
    uploadedImage.value = null;
};

const uploadFile = async () => {
    if (!selectedFile.value) return;

    uploading.value = true;
    error.value = '';

    try {
        const formData = new FormData();
        formData.append('file', selectedFile.value);

        const response = await fetch('/api/user/images/upload/web', {
            method: 'POST',
            body: formData,
        });

        const data: ApiResponse = await response.json();

        if (data.success || data.data) {
            const responseData: UploadResponse = data.data || (data as UploadResponse);
            uploadSuccess.value = true;
            uploadedImage.value = {
                url: responseData.image_url,
                embedUrl: responseData.embed_url,
                deleteUrl: responseData.delete_url,
            };

            // Reset form
            selectedFile.value = null;
            if (fileInput.value) {
                fileInput.value.value = '';
            }

            // Automatically copy embed URL to clipboard
            try {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    await navigator.clipboard.writeText(responseData.embed_url);
                } else {
                    // Fallback for browsers that don't support clipboard API
                    const textArea = document.createElement('textarea');
                    textArea.value = responseData.embed_url;
                    textArea.style.position = 'fixed';
                    textArea.style.left = '-999999px';
                    textArea.style.top = '-999999px';
                    document.body.appendChild(textArea);
                    textArea.focus();
                    textArea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textArea);
                }
            } catch (err) {
                console.error('Failed to copy embed URL to clipboard:', err);
                // Fallback: show the URL in an alert so user can copy manually
                Swal.fire({
                    icon: 'info',
                    title: 'Copy Embed URL',
                    text: 'Please copy this URL manually: ' + responseData.embed_url,
                    confirmButtonText: 'OK',
                });
            }

            // Show success message
            Swal.fire({
                icon: 'success',
                title: 'Upload Successful!',
                text: 'Your image has been uploaded successfully and the embed URL has been copied to your clipboard.',
                confirmButtonText: 'OK',
            });
        } else {
            throw new Error(data.message || 'Upload failed');
        }
    } catch (err) {
        error.value = err instanceof Error ? err.message : 'Upload failed';
        Swal.fire({
            icon: 'error',
            title: 'Upload Failed',
            text: error.value,
            confirmButtonText: 'OK',
        });
    } finally {
        uploading.value = false;
    }
};

const formatFileSize = (bytes: number): string => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const copyToClipboard = async (text: string) => {
    try {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            await navigator.clipboard.writeText(text);
        } else {
            // Fallback for browsers that don't support clipboard API
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            textArea.style.top = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
        }

        Swal.fire({
            icon: 'success',
            title: 'Copied!',
            text: 'URL copied to clipboard',
            timer: 1500,
            showConfirmButton: false,
        });
    } catch (err) {
        console.error('Failed to copy to clipboard:', err);
        // Fallback: show the URL in an alert so user can copy manually
        Swal.fire({
            icon: 'info',
            title: 'Copy URL',
            text: 'Please copy this URL manually: ' + text,
            confirmButtonText: 'OK',
        });
    }
};
</script>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.shimmer {
    background: linear-gradient(90deg, #23234a 25%, #2a2a3f 50%, #23234a 75%);
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
}

@keyframes shimmer {
    0% {
        background-position: -200% 0;
    }
    100% {
        background-position: 200% 0;
    }
}
</style>
