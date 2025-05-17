<template>
    <div
        class="relative group bg-[#18182a] border border-[#2a2a3f]/30 rounded-xl shadow-lg overflow-hidden transition-all duration-300 hover:shadow-2xl hover:border-indigo-500/40"
    >
        <div class="aspect-video w-full flex items-center justify-center overflow-hidden relative">
            <img
                :src="image.url"
                :alt="image.name"
                class="object-contain w-full h-full transition-all duration-500 group-hover:scale-105"
                loading="lazy"
            />
            <!-- Overlay on hover -->
            <div
                class="absolute inset-0 bg-gradient-to-t from-[#18182a]/90 via-[#18182a]/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col justify-end"
            >
                <div class="p-4 flex flex-col gap-2">
                    <div class="flex items-center gap-2">
                        <span class="text-white text-base font-semibold truncate">{{ image.name }}</span>
                        <span class="ml-auto bg-[#23234a]/80 text-indigo-300 text-xs px-2 py-0.5 rounded-full"
                            >{{ image.size }} {{ $t('images.card.kb') }}</span
                        >
                    </div>
                    <div class="flex gap-2 mt-2">
                        <button
                            @click="$emit('delete', image.id)"
                            class="px-3 py-1.5 bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded-lg text-sm transition-colors duration-200 flex items-center gap-1.5"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                />
                            </svg>
                            {{ $t('images.card.delete') }}
                        </button>
                        <button
                            @click="$emit('copy-link', image.url)"
                            class="px-3 py-1.5 bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-400 rounded-lg text-sm transition-colors duration-200 flex items-center gap-1.5"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"
                                />
                            </svg>
                            {{ $t('images.card.copy_link') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- File info below image -->
        <div class="px-4 py-2 flex items-center justify-between bg-[#12121f]/70 border-t border-[#2a2a3f]/30">
            <span class="text-xs text-gray-400">{{ image.size }} {{ $t('images.card.kb') }}</span>
            <span class="text-xs text-gray-500">{{ $t('images.card.uploaded') }} {{ image.uploaded }}</span>
        </div>
    </div>
</template>

<script setup lang="ts">
interface EmbedInfo {
    title: string;
    description: string;
    color: string;
    author_name: string;
    image: string | null;
    thumbnail: string | null;
    url: string | null;
}

defineProps<{
    image: {
        id: string;
        name: string;
        size: string;
        url: string;
        uploaded: string;
        embed: boolean;
        embedInfo: EmbedInfo;
    };
}>();

defineEmits<{
    (e: 'delete', id: string): void;
    (e: 'copy-link', url: string): void;
}>();
</script>

<style scoped>
.group:hover {
    box-shadow:
        0 8px 32px 0 rgba(99, 102, 241, 0.15),
        0 1.5px 6px 0 rgba(0, 0, 0, 0.1);
}

.group:hover .aspect-video {
    border-color: #6366f1;
}

.aspect-video {
    aspect-ratio: 16/9;
    background: linear-gradient(135deg, #23234a 0%, #18182a 100%);
}
</style>
