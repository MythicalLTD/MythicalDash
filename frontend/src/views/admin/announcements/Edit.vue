<template>
    <LayoutDashboard>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-400">Edit Announcement</h1>
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

        <div v-else class="space-y-6">
            <!-- Main Announcement Information -->
            <div class="bg-gray-800/50 backdrop-blur-md rounded-lg p-6">
                <h2 class="text-xl font-semibold text-white mb-6">Announcement Details</h2>
                <form @submit.prevent="updateAnnouncement" class="space-y-6">
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

                        <div v-if="announcementForm.date">
                            <label class="block text-sm font-medium text-gray-400 mb-1">Created At</label>
                            <div class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 text-gray-400">
                                {{ new Date(announcementForm.date).toLocaleString() }}
                            </div>
                        </div>
                    </div>

                    <div v-if="updateSuccess" class="bg-green-500/20 text-green-400 p-4 rounded-lg mb-6">
                        Announcement updated successfully
                    </div>

                    <div v-if="updateError" class="bg-red-500/20 text-red-400 p-4 rounded-lg mb-6">
                        {{ updateError }}
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
                            Update Announcement
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tags Management Section -->
            <div class="bg-gray-800/50 backdrop-blur-md rounded-lg p-6">
                <h2 class="text-xl font-semibold text-white mb-6">Tags</h2>
                <div class="flex flex-wrap gap-2 mb-4">
                    <span
                        v-for="tag in tags"
                        :key="tag.id"
                        class="bg-gray-700 text-gray-300 px-3 py-1 rounded-full text-sm flex items-center"
                    >
                        <TagIcon class="w-3 h-3 mr-1" />
                        {{ tag.tag }}
                        <button
                            @click="deleteTag(tag.id)"
                            class="ml-2 text-gray-400 hover:text-red-400"
                            :disabled="tagDeleting"
                        >
                            <XIcon class="w-3 h-3" />
                        </button>
                    </span>
                    <span v-if="tags.length === 0" class="text-gray-500">No tags added yet</span>
                </div>

                <form @submit.prevent="addTag" class="mt-4 flex space-x-2">
                    <input
                        v-model="newTag"
                        type="text"
                        placeholder="Add a new tag"
                        class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 flex-grow focus:outline-none focus:ring-2 focus:ring-pink-500"
                    />
                    <button
                        type="submit"
                        :disabled="tagAdding || !newTag.trim()"
                        class="px-4 py-2 bg-pink-500 rounded-lg text-white hover:bg-pink-600 transition-colors flex items-center"
                    >
                        <LoaderIcon v-if="tagAdding" class="animate-spin w-4 h-4 mr-2" />
                        <PlusIcon v-else class="w-4 h-4 mr-2" />
                        Add
                    </button>
                </form>

                <div v-if="tagError" class="mt-4 bg-red-500/20 text-red-400 p-4 rounded-lg">
                    {{ tagError }}
                </div>
            </div>

            <!-- Image Upload Section -->
            <div class="bg-gray-800/50 backdrop-blur-md rounded-lg p-6">
                <h2 class="text-xl font-semibold text-white mb-6">Images</h2>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div v-for="asset in assets" :key="asset.id" class="relative group">
                        <img
                            :src="asset.images"
                            alt="Announcement Image"
                            class="w-full aspect-square object-cover rounded-lg"
                        />
                        <div
                            class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg flex items-center justify-center"
                        >
                            <button
                                @click="deleteImage(asset.id)"
                                class="bg-red-500 hover:bg-red-600 text-white rounded-full p-2"
                                :disabled="imageDeleting"
                            >
                                <TrashIcon class="w-5 h-5" />
                            </button>
                        </div>
                    </div>
                    <div v-if="assets.length === 0" class="text-gray-500 col-span-full">No images uploaded yet</div>
                </div>

                <form @submit.prevent="uploadImage" class="space-y-4">
                    <div class="border-2 border-dashed border-gray-700 rounded-lg p-6 text-center">
                        <input
                            type="file"
                            id="imageUpload"
                            ref="fileInput"
                            @change="handleFileChange"
                            accept="image/png,image/jpeg,image/gif"
                            class="hidden"
                        />
                        <label for="imageUpload" class="cursor-pointer flex flex-col items-center justify-center">
                            <UploadIcon class="w-10 h-10 text-gray-500 mb-2" />
                            <p class="text-gray-400">Drag & drop an image or click to browse</p>
                            <p class="text-gray-500 text-sm mt-2">PNG, JPG, GIF up to 2MB</p>
                        </label>
                        <div v-if="selectedFile" class="mt-4 text-left">
                            <div class="flex items-center">
                                <span class="text-gray-300">Selected: {{ selectedFile.name }}</span>
                                <button
                                    type="button"
                                    @click="clearSelectedFile"
                                    class="ml-2 text-gray-400 hover:text-red-400"
                                >
                                    <XIcon class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button
                            type="submit"
                            :disabled="imageUploading || !selectedFile"
                            class="px-4 py-2 bg-pink-500 rounded-lg text-white hover:bg-pink-600 transition-colors flex items-center"
                        >
                            <LoaderIcon v-if="imageUploading" class="animate-spin w-4 h-4 mr-2" />
                            <UploadIcon v-else class="w-4 h-4 mr-2" />
                            Upload Image
                        </button>
                    </div>
                </form>

                <div v-if="imageError" class="mt-4 bg-red-500/20 text-red-400 p-4 rounded-lg">
                    {{ imageError }}
                </div>
            </div>
        </div>
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import LayoutDashboard from '@/components/admin/LayoutDashboard.vue';
import { ArrowLeftIcon, SaveIcon, LoaderIcon, TagIcon, XIcon, PlusIcon, TrashIcon, UploadIcon } from 'lucide-vue-next';

// Define interfaces for the data structures
interface Tag {
    id: number;
    tag: string;
}

interface Asset {
    id: number;
    images: string;
}

interface Announcement {
    id: number;
    title: string;
    shortDescription: string;
    description: string;
    date: string;
    tags: Tag[];
    assets: Asset[];
}

const router = useRouter();
const route = useRoute();
const announcementId = Number(route.params.id);

// Main state
const loading = ref<boolean>(true);
const error = ref<string>('');
const saving = ref<boolean>(false);
const updateSuccess = ref<boolean>(false);
const updateError = ref<string>('');

// Tags state
const tags = ref<Tag[]>([]);
const newTag = ref<string>('');
const tagAdding = ref<boolean>(false);
const tagDeleting = ref<boolean>(false);
const tagError = ref<string>('');

// Images state
const assets = ref<Asset[]>([]);
const selectedFile = ref<File | null>(null);
const imageUploading = ref<boolean>(false);
const imageDeleting = ref<boolean>(false);
const imageError = ref<string>('');
const fileInput = ref<HTMLInputElement | null>(null);

// Form state
const announcementForm = ref<{
    id: number;
    title: string;
    shortDescription: string;
    description: string;
    date: string;
}>({
    id: announcementId,
    title: '',
    shortDescription: '',
    description: '',
    date: '',
});

// Fetch announcement data
const fetchAnnouncement = async (): Promise<void> => {
    try {
        const response = await fetch(`/api/admin/announcements`, {
            method: 'GET',
            headers: {
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error('Failed to fetch announcement data');
        }

        const data = await response.json();

        if (data.success) {
            // Find the announcement with matching ID
            const announcement = data.announcements.find((item: Announcement) => item.id === announcementId);

            if (!announcement) {
                error.value = 'Announcement not found';
                return;
            }

            // Populate form with announcement data
            announcementForm.value = {
                id: announcement.id,
                title: announcement.title,
                shortDescription: announcement.shortDescription,
                description: announcement.description,
                date: announcement.date,
            };

            // Load tags and assets
            tags.value = announcement.tags || [];
            assets.value = announcement.assets || [];
        } else {
            error.value = data.message || 'Failed to load announcement data';
        }
    } catch (err) {
        console.error('Error fetching announcement:', err);
        error.value = 'An error occurred while loading the announcement';
    } finally {
        loading.value = false;
    }
};

// Update announcement
const updateAnnouncement = async (): Promise<void> => {
    saving.value = true;
    updateSuccess.value = false;
    updateError.value = '';

    try {
        const formData = new FormData();
        formData.append('title', announcementForm.value.title);
        formData.append('shortDescription', announcementForm.value.shortDescription);
        formData.append('description', announcementForm.value.description);

        const response = await fetch(`/api/admin/announcements/${announcementId}/update`, {
            method: 'POST',
            body: formData,
        });

        const data = await response.json();

        if (data.success) {
            updateSuccess.value = true;
            // Refresh announcement data
            await fetchAnnouncement();
        } else {
            updateError.value = data.message || 'Failed to update announcement';
        }
    } catch (err) {
        console.error('Error updating announcement:', err);
        updateError.value = 'An error occurred while updating the announcement';
    } finally {
        saving.value = false;
    }
};

// Tags management
const addTag = async (): Promise<void> => {
    if (!newTag.value.trim()) return;

    tagAdding.value = true;
    tagError.value = '';

    try {
        const formData = new FormData();
        formData.append('tag', newTag.value.trim());

        const response = await fetch(`/api/admin/announcements/${announcementId}/tags/add`, {
            method: 'POST',
            body: formData,
        });

        const data = await response.json();

        if (data.success) {
            // Fetch updated tags
            const tagsResponse = await fetch(`/api/admin/announcements/${announcementId}/tags`, {
                method: 'GET',
            });

            const tagsData = await tagsResponse.json();

            if (tagsData.success) {
                tags.value = tagsData.tags;
                newTag.value = ''; // Clear input
            }
        } else {
            tagError.value = data.message || 'Failed to add tag';
        }
    } catch (err) {
        console.error('Error adding tag:', err);
        tagError.value = 'An error occurred while adding the tag';
    } finally {
        tagAdding.value = false;
    }
};

const deleteTag = async (tagId: number): Promise<void> => {
    tagDeleting.value = true;
    tagError.value = '';

    try {
        const formData = new FormData();

        const response = await fetch(`/api/admin/announcements/${announcementId}/tags/${tagId}/delete`, {
            method: 'POST',
            body: formData,
        });

        const data = await response.json();

        if (data.success) {
            // Remove tag from local state
            tags.value = tags.value.filter((tag) => tag.id !== tagId);
        } else {
            tagError.value = data.message || 'Failed to delete tag';
        }
    } catch (err) {
        console.error('Error deleting tag:', err);
        tagError.value = 'An error occurred while deleting the tag';
    } finally {
        tagDeleting.value = false;
    }
};

// Image management
const handleFileChange = (event: Event): void => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        selectedFile.value = target.files[0];
    } else {
        selectedFile.value = null;
    }
};

const clearSelectedFile = (): void => {
    selectedFile.value = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const uploadImage = async (): Promise<void> => {
    if (!selectedFile.value) return;

    imageUploading.value = true;
    imageError.value = '';

    try {
        const formData = new FormData();
        formData.append('attachments', selectedFile.value);

        const response = await fetch(`/api/admin/announcements/${announcementId}/assets/add`, {
            method: 'POST',
            body: formData,
        });

        const data = await response.json();

        if (data.success) {
            // Fetch updated assets
            const assetsResponse = await fetch(`/api/admin/announcements/${announcementId}/assets`, {
                method: 'GET',
            });

            const assetsData = await assetsResponse.json();

            if (assetsData.success) {
                assets.value = assetsData.assets;
                clearSelectedFile();
            }
        } else {
            imageError.value = data.message || 'Failed to upload image';
        }
    } catch (err) {
        console.error('Error uploading image:', err);
        imageError.value = 'An error occurred while uploading the image';
    } finally {
        imageUploading.value = false;
    }
};

const deleteImage = async (assetId: number): Promise<void> => {
    imageDeleting.value = true;
    imageError.value = '';

    try {
        const formData = new FormData();

        const response = await fetch(`/api/admin/announcements/${announcementId}/assets/${assetId}/delete`, {
            method: 'POST',
            body: formData,
        });

        const data = await response.json();

        if (data.success) {
            // Remove asset from local state
            assets.value = assets.value.filter((asset) => asset.id !== assetId);
        } else {
            imageError.value = data.message || 'Failed to delete image';
        }
    } catch (err) {
        console.error('Error deleting image:', err);
        imageError.value = 'An error occurred while deleting the image';
    } finally {
        imageDeleting.value = false;
    }
};

onMounted(() => {
    fetchAnnouncement();
});
</script>
