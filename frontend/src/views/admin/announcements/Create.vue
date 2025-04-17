<template>
    <LayoutDashboard>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-400">Create Announcement</h1>
            <button
                @click="router.push('/mc-admin/announcements')"
                class="bg-gray-700 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:bg-gray-600 flex items-center"
            >
                <ArrowLeftIcon class="w-4 h-4 mr-2" />
                Back to Announcements
            </button>
        </div>

        <div class="bg-gray-800/50 backdrop-blur-md rounded-lg p-6">
            <form @submit.prevent="createAnnouncement" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label for="title" class="block text-sm font-medium text-gray-400 mb-1">Title</label>
                        <input
                            id="title"
                            v-model="announcementForm.title"
                            type="text"
                            required
                            class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                            placeholder="Announcement Title"
                        />
                    </div>

                    <div class="md:col-span-2">
                        <label for="shortDescription" class="block text-sm font-medium text-gray-400 mb-1"
                            >Short Description</label
                        >
                        <input
                            id="shortDescription"
                            v-model="announcementForm.shortDescription"
                            type="text"
                            required
                            class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                            placeholder="A brief summary of the announcement"
                        />
                    </div>

                    <div class="md:col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-400 mb-1"
                            >Full Description</label
                        >
                        <textarea
                            id="description"
                            v-model="announcementForm.description"
                            rows="6"
                            required
                            class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                            placeholder="Detailed announcement content"
                        ></textarea>
                    </div>
                </div>

                <div v-if="errorMessage" class="bg-red-500/20 text-red-400 p-4 rounded-lg mb-6">
                    {{ errorMessage }}
                </div>

                <div v-if="successMessage" class="bg-green-500/20 text-green-400 p-4 rounded-lg mb-6">
                    {{ successMessage }}
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-700">
                    <button
                        type="button"
                        @click="router.push('/mc-admin/announcements')"
                        class="px-4 py-2 border border-gray-600 rounded-lg text-gray-300 hover:bg-gray-700 transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        :disabled="saving"
                        class="px-4 py-2 bg-gradient-to-r from-pink-500 to-violet-500 rounded-lg text-white hover:opacity-90 transition-colors flex items-center"
                    >
                        <LoaderIcon v-if="saving" class="animate-spin w-4 h-4 mr-2" />
                        <SaveIcon v-else class="w-4 h-4 mr-2" />
                        Create Announcement
                    </button>
                </div>
            </form>
        </div>
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import LayoutDashboard from '@/components/admin/LayoutDashboard.vue';
import { ArrowLeftIcon, SaveIcon, LoaderIcon } from 'lucide-vue-next';

interface AnnouncementForm {
    title: string;
    shortDescription: string;
    description: string;
}

interface ApiResponse {
    success: boolean;
    message?: string;
    announcement?: {
        id: number;
        title: string;
        shortDescription: string;
        description: string;
    };
}

const router = useRouter();
const saving = ref<boolean>(false);
const errorMessage = ref<string>('');
const successMessage = ref<string>('');

// Form state with default values
const announcementForm = ref<AnnouncementForm>({
    title: '',
    shortDescription: '',
    description: '',
});

// Create announcement function
const createAnnouncement = async (): Promise<void> => {
    saving.value = true;
    errorMessage.value = '';
    successMessage.value = '';

    try {
        // Create FormData object
        const formData = new FormData();
        formData.append('title', announcementForm.value.title);
        formData.append('shortDescription', announcementForm.value.shortDescription);
        formData.append('description', announcementForm.value.description);

        // Send create request to API
        const response = await fetch('/api/admin/announcements/create', {
            method: 'POST',
            body: formData,
        });

        const data = (await response.json()) as ApiResponse;

        if (data.success && data.announcement) {
            successMessage.value = 'Announcement created successfully';

            // Wait a moment before redirecting
            setTimeout(() => {
                // Redirect to edit page to add tags and images
                const announcementId = data.announcement?.id || 0;
                router.push(`/mc-admin/announcements/${announcementId}/edit`);
            }, 1500);
        } else {
            errorMessage.value = data.message || 'Failed to create announcement';
        }
    } catch (err) {
        console.error('Error creating announcement:', err);
        errorMessage.value = 'An error occurred while creating the announcement';
    } finally {
        saving.value = false;
    }
};
</script>
