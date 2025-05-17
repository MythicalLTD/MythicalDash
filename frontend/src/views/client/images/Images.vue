<template>
    <LayoutDashboard>
        <div class="p-6">
            <h1 class="text-2xl font-bold text-gray-100 mb-6">Gallery</h1>

            <!-- Error Message -->
            <div v-if="error" class="mb-6 p-4 bg-red-500/10 border border-red-500/20 rounded-lg">
                <p class="text-red-400">{{ error }}</p>
            </div>

            <!-- Loading State -->
            <div v-if="loading && images.length === 0" class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="n in 6"
                    :key="n"
                    class="relative group bg-[#18182a] border border-[#2a2a3f]/30 rounded-xl shadow-lg overflow-hidden"
                >
                    <div class="aspect-video w-full bg-[#23234a] shimmer"></div>
                    <div
                        class="px-4 py-2 flex items-center justify-between bg-[#12121f]/70 border-t border-[#2a2a3f]/30"
                    >
                        <div class="h-4 w-20 bg-[#23234a] rounded shimmer"></div>
                        <div class="h-4 w-32 bg-[#23234a] rounded shimmer"></div>
                    </div>
                </div>
            </div>

            <!-- Images Grid -->
            <div v-else class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                <ImageCard
                    v-for="image in images"
                    :key="image.metadata.file_name"
                    :image="{
                        id: image.metadata.file_name,
                        name: image.original_name,
                        size: (image.metadata.file_size / 1024).toFixed(2),
                        url: image.metadata.file_url,
                        uploaded: new Date(image.uploaded_at * 1000).toLocaleDateString(),
                        embed: image.embed,
                        embedInfo: image.embed_info,
                    }"
                    @delete="handleDelete"
                    @copy-link="handleCopyLink"
                />
            </div>

            <!-- Loading More State -->
            <div v-if="loading && images.length > 0" class="mt-8 flex justify-center">
                <div class="flex items-center gap-3 text-gray-400">
                    <div
                        class="w-5 h-5 border-2 border-indigo-500 border-t-transparent rounded-full animate-spin"
                    ></div>
                    <span>Loading more images...</span>
                </div>
            </div>

            <!-- Load More Button -->
            <div v-if="hasMoreImages && !loading" class="mt-8 flex justify-center">
                <button
                    @click="loadMoreImages"
                    class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-all duration-200 flex items-center gap-2 hover:shadow-lg hover:shadow-indigo-500/20"
                >
                    <span>Load More</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
            </div>

            <!-- No More Images -->
            <div v-if="!hasMoreImages && images.length > 0" class="mt-8 text-center text-gray-400">
                No more images to load
            </div>
        </div>
    </LayoutDashboard>
</template>

<script setup lang="ts">
import LayoutDashboard from '@/components/client/LayoutDashboard.vue';
import ImageCard from './ImageCard.vue';
import { ref, onMounted } from 'vue';

interface ImageMetadata {
    file_size: number;
    file_type: string;
    file_name: string;
    file_url: string;
    uploaded_at: number;
}

interface EmbedInfo {
    title: string;
    description: string;
    color: string;
    author_name: string;
    image: string | null;
    thumbnail: string | null;
    url: string | null;
}

interface Image {
    original_name: string;
    uploaded_at: number;
    metadata: ImageMetadata;
    user_uuid: string;
    user_name: string;
    embed: boolean;
    embed_info: EmbedInfo;
}

const images = ref<Image[]>([]);
const loading = ref(false);
const error = ref<string | null>(null);
const currentPage = ref(1);
const hasMoreImages = ref(true);
const ITEMS_PER_PAGE = 35;

const fetchImages = async (page: number) => {
    try {
        loading.value = true;
        error.value = null;
        
        const response = await fetch('/api/user/images/list');
        if (!response.ok) {
            throw new Error('Failed to fetch images');
        }
        
        const data = await response.json();
        if (!data.success) {
            throw new Error(data.message || 'Failed to fetch images');
        }

        const imagePaths = data.data.images;
        const startIndex = (page - 1) * ITEMS_PER_PAGE;
        const endIndex = startIndex + ITEMS_PER_PAGE;
        const pageImagePaths = imagePaths.slice(startIndex, endIndex);

        // Fetch metadata for each image
        const imagePromises = pageImagePaths.map(async (path: string) => {
            try {
                const metadataResponse = await fetch(path);
                if (!metadataResponse.ok) {
                    throw new Error(`Failed to fetch metadata for ${path}`);
                }
                return metadataResponse.json();
            } catch (err) {
                console.error(`Error fetching metadata for ${path}:`, err);
                return null;
            }
        });

        const newImages = (await Promise.all(imagePromises)).filter(Boolean);
        images.value = [...images.value, ...newImages];
        hasMoreImages.value = endIndex < imagePaths.length;
    } catch (err) {
        error.value = err instanceof Error ? err.message : 'An error occurred while fetching images';
        console.error('Error fetching images:', err);
    } finally {
        loading.value = false;
    }
};

const loadMoreImages = () => {
    if (!loading.value && hasMoreImages.value) {
        currentPage.value++;
        fetchImages(currentPage.value);
    }
};

const handleDelete = async (imageId: string) => {
    try {
        const response = await fetch(`/api/user/images/delete/${imageId}`, {
            method: 'DELETE',
        });
        
        if (!response.ok) {
            throw new Error('Failed to delete image');
        }
        
        images.value = images.value.filter((img) => img.metadata.file_name !== imageId);
    } catch (err) {
        error.value = err instanceof Error ? err.message : 'Failed to delete image';
        console.error('Error deleting image:', err);
    }
};

const handleCopyLink = async (imageUrl: string) => {
    try {
        await navigator.clipboard.writeText(imageUrl);
        // You might want to show a toast notification here
    } catch (err) {
        error.value = err instanceof Error ? err.message : 'Failed to copy link';
        console.error('Error copying link:', err);
    }
};

onMounted(() => {
    fetchImages(1);
});
</script>

<style scoped>
.aspect-video {
    aspect-ratio: 16/9;
}

@keyframes shimmer {
    0% {
        background-position: -1000px 0;
    }
    100% {
        background-position: 1000px 0;
    }
}

.shimmer {
    background: linear-gradient(90deg, #23234a 0%, #2a2a3f 50%, #23234a 100%);
    background-size: 1000px 100%;
    animation: shimmer 2s infinite linear;
}
</style>
