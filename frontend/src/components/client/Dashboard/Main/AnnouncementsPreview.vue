<template>
    <CardComponent
        :cardTitle="t('Components.Announcements.Card.Info')"
        :cardDescription="t('Components.Announcements.Card.InfoFull')"
    >
        <div v-if="loading" class="flex justify-center items-center h-40">
            <div class="loader w-10 h-10 relative">
                <div class="loader-ring"></div>
                <div class="loader-ring" style="animation-delay: 0.1s"></div>
                <div class="loader-ring" style="animation-delay: 0.2s"></div>
            </div>
        </div>

        <div v-else-if="latestAnnouncement" class="announcement-card group space-y-4 relative overflow-hidden">
            <!-- Image container with overlay and effects -->
            <div class="aspect-video relative overflow-hidden rounded-lg">
                <img
                    :src="latestAnnouncement.images[0]"
                    :alt="latestAnnouncement.title"
                    class="w-full h-full object-cover transition-all duration-700 group-hover:scale-105"
                />
                <div class="absolute inset-0 bg-gradient-to-t from-gray-900/80 via-gray-900/40 to-transparent"></div>
                <div
                    class="absolute bottom-3 left-3 text-xs text-white bg-indigo-500/50 backdrop-blur-sm px-2 py-1 rounded-md"
                >
                    {{ formatDate(latestAnnouncement.date) }}
                </div>
            </div>

            <!-- Content with improved typography and spacing -->
            <div class="space-y-3 relative">
                <h3 class="text-lg font-semibold text-white group-hover:text-indigo-400 transition-colors duration-300">
                    {{ latestAnnouncement.title }}
                </h3>

                <p class="text-gray-400 text-sm line-clamp-2 leading-relaxed">
                    {{ latestAnnouncement.shortDescription }}
                </p>

                <!-- Tags with improved styling -->
                <div class="flex flex-wrap gap-1.5 mt-2">
                    <span
                        v-for="tag in latestAnnouncement.tags.slice(0, 2)"
                        :key="tag"
                        class="px-2 py-0.5 rounded-md text-xs bg-gray-800/80 text-gray-300 border border-gray-700/50 backdrop-blur-sm"
                    >
                        {{ tag }}
                    </span>
                    <span
                        v-if="latestAnnouncement.tags.length > 2"
                        class="px-2 py-0.5 rounded-md text-xs bg-indigo-500/20 text-indigo-400 border border-indigo-500/20 backdrop-blur-sm"
                    >
                        +{{ latestAnnouncement.tags.length - 2 }}
                    </span>
                </div>
            </div>
        </div>

        <div v-else class="flex flex-col items-center justify-center py-10 opacity-80">
            <div class="empty-state-animation mb-3">
                <BellOffIcon class="w-12 h-12 text-gray-600" />
            </div>
            <p class="text-gray-400 text-sm">{{ t('Components.Announcements.Card.NoAnnouncements') }}</p>
        </div>

        <div class="mt-4 text-center">
            <button
                @click="goToAnnouncements"
                class="view-all-btn px-4 py-2.5 bg-gradient-to-r from-indigo-500/20 to-blue-500/20 hover:from-indigo-500/30 hover:to-blue-500/30 text-indigo-400 rounded-lg text-sm transition-all duration-300 w-full relative overflow-hidden border border-indigo-500/10"
            >
                <span class="relative z-10">{{ t('Components.Announcements.Card.ReadMore') }}</span>
            </button>
        </div>
    </CardComponent>
</template>

<script setup lang="ts">
import CardComponent from '@/components/client/ui/Card/CardComponent.vue';
import Announcements from '@/mythicaldash/Announcements';
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { BellOff as BellOffIcon } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

const router = useRouter();
const loading = ref(true);
const { t } = useI18n();

interface Announcement {
    id: number;
    title: string;
    shortDescription: string;
    description: string;
    images: string[];
    date: Date;
    tags: string[];
}

const latestAnnouncement = ref<Announcement | null>(null);

const formatDate = (date: Date | null): string => {
    if (!date) return '';

    const now = new Date();
    const diff = now.getTime() - date.getTime();
    const minutes = Math.floor(diff / (1000 * 60));
    const hours = Math.floor(diff / (1000 * 60 * 60));
    const days = Math.floor(diff / (1000 * 60 * 60 * 24));

    if (minutes < 60) return `${minutes}m ago`;
    if (hours < 24) return `${hours}h ago`;
    if (days === 0) return 'Today';
    if (days === 1) return 'Yesterday';
    if (days < 7) return `${days} days ago`;

    return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
    });
};

const goToAnnouncements = () => {
    router.push('/announcements');
};

const fetchAnnouncements = async () => {
    try {
        const data = await Announcements.fetchAnnouncements();
        if (data && data.length > 0) {
            const firstAnnouncement = data[0];
            latestAnnouncement.value = {
                id: firstAnnouncement.id,
                title: firstAnnouncement.title,
                shortDescription: firstAnnouncement.shortDescription,
                description: firstAnnouncement.description,
                images: firstAnnouncement.assets.map((asset: { images: string[] }) => asset.images).flat(),
                date: new Date(firstAnnouncement.date),
                tags: firstAnnouncement.tags.map((tag: { tag: string }) => tag.tag),
            };
        }
    } catch (err) {
        console.error('Error fetching announcements:', err);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchAnnouncements();
});
</script>

<style scoped>
.announcement-card::after {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(45deg, transparent, rgba(99, 102, 241, 0.03), transparent);
    z-index: -1;
    transition: transform 0.5s ease;
    transform: translateX(-100%);
}

.announcement-card:hover::after {
    transform: translateX(100%);
}

.view-all-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
    transform: translateX(-100%);
    transition: transform 0.6s ease;
}

.view-all-btn:hover::before {
    transform: translateX(100%);
}

.empty-state-animation {
    animation: float 3s ease-in-out infinite;
}

.loader {
    display: flex;
    align-items: center;
    justify-content: center;
}

.loader-ring {
    position: absolute;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    border: 2px solid transparent;
    border-top-color: #818cf8;
    animation: loader-spin 1.2s cubic-bezier(0.68, -0.55, 0.27, 1.55) infinite;
}

.loader-ring:nth-child(2) {
    width: 80%;
    height: 80%;
    border-top-color: #6366f1;
}

.loader-ring:nth-child(3) {
    width: 60%;
    height: 60%;
    border-top-color: #4f46e5;
}

@keyframes loader-spin {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}

@keyframes float {
    0%,
    100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-10px);
    }
}
</style>
