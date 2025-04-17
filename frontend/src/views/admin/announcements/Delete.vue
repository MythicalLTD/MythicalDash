<template>
    <LayoutDashboard>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-400">Delete Announcement</h1>
            <button
                @click="router.push('/mc-admin/announcements')"
                class="bg-gray-700 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:bg-gray-600 flex items-center"
            >
                <ArrowLeftIcon class="w-4 h-4 mr-2" />
                Back to Announcements
            </button>
        </div>

        <div v-if="loading" class="flex justify-center items-center py-12">
            <LoaderIcon class="w-8 h-8 animate-spin text-pink-400" />
        </div>

        <div v-else-if="error" class="bg-red-500/20 text-red-400 p-4 rounded-lg mb-6">
            {{ error }}
        </div>

        <div v-else class="bg-gray-800/50 backdrop-blur-md rounded-lg p-6">
            <div class="text-center">
                <AlertTriangleIcon class="w-16 h-16 mx-auto text-red-400 mb-4" />
                <h2 class="text-xl font-medium text-gray-100 mb-2">Confirm Deletion</h2>
                <p class="text-gray-300 mb-2">
                    Are you sure you want to delete the announcement
                    <span class="font-medium text-white">{{ announcement.title }}</span
                    >?
                </p>
                <p class="text-gray-400 mb-6">This action cannot be undone.</p>

                <div class="bg-gray-900/50 rounded-lg p-4 mb-6 text-left">
                    <h3 class="text-md font-medium text-gray-300 mb-2">Announcement Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div><span class="text-gray-400">ID:</span> {{ announcement.id }}</div>
                        <div><span class="text-gray-400">Title:</span> {{ announcement.title }}</div>
                        <div class="md:col-span-2">
                            <span class="text-gray-400">Short Description:</span> {{ announcement.shortDescription }}
                        </div>
                        <div class="md:col-span-2">
                            <span class="text-gray-400">Created:</span> {{ formatDate(announcement.date) }}
                        </div>
                        <div class="md:col-span-2">
                            <span class="text-gray-400">Tags:</span>
                            <div class="flex flex-wrap gap-2 mt-1">
                                <span
                                    v-for="tag in announcement.tags"
                                    :key="tag.id"
                                    class="bg-gray-700 text-gray-300 px-2 py-1 rounded-full text-xs flex items-center"
                                >
                                    <TagIcon class="w-3 h-3 mr-1" />
                                    {{ tag.tag }}
                                </span>
                                <span
                                    v-if="!announcement.tags || announcement.tags.length === 0"
                                    class="text-gray-500 text-sm"
                                >
                                    No tags
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="successMessage" class="bg-green-500/20 text-green-400 p-4 rounded-lg mb-6">
                    {{ successMessage }}
                </div>

                <div class="flex justify-center space-x-3">
                    <button
                        @click="router.push('/mc-admin/announcements')"
                        class="px-4 py-2 border border-gray-600 rounded-lg text-gray-300 hover:bg-gray-700 transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        @click="deleteAnnouncement"
                        :disabled="deleting"
                        class="px-4 py-2 bg-red-500 rounded-lg text-white hover:bg-red-600 transition-colors flex items-center"
                    >
                        <LoaderIcon v-if="deleting" class="animate-spin w-4 h-4 mr-2" />
                        <TrashIcon v-else class="w-4 h-4 mr-2" />
                        Delete Announcement
                    </button>
                </div>
            </div>
        </div>
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import LayoutDashboard from '@/components/admin/LayoutDashboard.vue';
import { ArrowLeftIcon, AlertTriangleIcon, TrashIcon, LoaderIcon, TagIcon } from 'lucide-vue-next';

const router = useRouter();
const route = useRoute();
const announcementId = Number(route.params.id);

const loading = ref<boolean>(true);
const deleting = ref<boolean>(false);
const error = ref<string>('');
const successMessage = ref<string>('');

// Interface for tag data
interface Tag {
    id: number;
    tag: string;
}

// Interface for asset data
interface Asset {
    id: number;
    images: string;
}

// Interface for announcement data
interface AnnouncementData {
    id: number;
    title: string;
    shortDescription: string;
    description: string;
    date: string;
    deleted: string;
    tags: Tag[];
    assets: Asset[];
}

interface ApiResponse {
    success: boolean;
    message?: string;
    announcements?: AnnouncementData[];
    id?: number;
}

const announcement = ref<AnnouncementData>({
    id: 0,
    title: '',
    shortDescription: '',
    description: '',
    date: '',
    deleted: 'false',
    tags: [],
    assets: [],
});

// Format date function
const formatDate = (dateString: string): string => {
    if (!dateString) return 'Unknown';
    return new Date(dateString).toLocaleString();
};

// Fetch announcement data
const fetchAnnouncementData = async (): Promise<void> => {
    try {
        const response = await fetch('/api/admin/announcements', {
            method: 'GET',
            headers: {
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error('Failed to fetch announcement data');
        }

        const data = (await response.json()) as ApiResponse;

        if (data.success && data.announcements) {
            // Find the announcement with the matching ID
            const foundAnnouncement = data.announcements.find(
                (announcement: AnnouncementData) => announcement.id === announcementId,
            );

            if (!foundAnnouncement) {
                error.value = 'Announcement not found';
                return;
            }

            // Populate the announcement data
            announcement.value = foundAnnouncement;
        } else {
            error.value = data.message || 'Failed to load announcement data';
        }
    } catch (err) {
        console.error('Error fetching announcement data:', err);
        error.value = 'An error occurred while fetching announcement details';
    } finally {
        loading.value = false;
    }
};

// Delete announcement function
const deleteAnnouncement = async (): Promise<void> => {
    deleting.value = true;
    successMessage.value = '';
    error.value = '';

    try {
        // Create FormData for the delete request
        const formData = new FormData();

        // Send delete request to API
        const response = await fetch(`/api/admin/announcements/${announcementId}/delete`, {
            method: 'POST',
            body: formData,
        });

        const data = (await response.json()) as ApiResponse;

        if (data.success) {
            successMessage.value = 'Announcement deleted successfully';
            // Wait a moment before redirecting
            setTimeout(() => {
                router.push('/mc-admin/announcements');
            }, 1500);
        } else {
            error.value = data.message || 'Failed to delete announcement';
            deleting.value = false;
        }
    } catch (err) {
        console.error('Error deleting announcement:', err);
        error.value = 'An error occurred while deleting the announcement';
        deleting.value = false;
    }
};

onMounted(() => {
    fetchAnnouncementData();
});
</script>
