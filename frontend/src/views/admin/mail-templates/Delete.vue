<template>
    <LayoutDashboard>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-400">Delete Mail Template</h1>
            <button
                @click="router.push('/mc-admin/mail-templates')"
                class="bg-gray-700 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:bg-gray-600 flex items-center"
            >
                <ArrowLeftIcon class="w-4 h-4 mr-2" />
                Back to Templates
            </button>
        </div>

        <div v-if="loading" class="flex justify-center items-center py-10">
            <LoaderCircle class="h-8 w-8 animate-spin text-pink-400" />
        </div>

        <div v-else-if="!template" class="bg-red-500/20 text-red-400 p-4 rounded-lg">
            Template not found or has been deleted.
            <button
                @click="router.push('/mc-admin/mail-templates')"
                class="text-white underline ml-2 hover:text-gray-200"
            >
                Go back to templates
            </button>
        </div>

        <div v-else class="bg-gray-800/50 backdrop-blur-md rounded-lg p-6">
            <div class="space-y-6">
                <div class="text-center mb-6">
                    <AlertTriangleIcon class="h-12 w-12 text-red-400 mx-auto mb-4" />
                    <h2 class="text-xl font-semibold text-white">Confirm Deletion</h2>
                    <p class="text-gray-400 mt-2">
                        Are you sure you want to delete the mail template "<span class="text-white font-semibold">{{
                            template.name
                        }}</span
                        >"? <br />This action cannot be undone.
                    </p>
                </div>

                <div class="bg-gray-900/50 rounded-lg p-4 border border-gray-700">
                    <h3 class="text-md font-medium text-white mb-2">Template Details</h3>
                    <div class="text-sm text-gray-300 space-y-1">
                        <p><span class="text-gray-500">ID:</span> {{ template.id }}</p>
                        <p><span class="text-gray-500">Name:</span> {{ template.name }}</p>
                        <p>
                            <span class="text-gray-500">Status:</span>
                            {{ template.active === 'true' ? 'Active' : 'Inactive' }}
                        </p>
                        <p v-if="template.locked === 'true'" class="text-amber-400 flex items-center">
                            <LockIcon class="h-4 w-4 mr-1" /> This template is locked and cannot be deleted
                        </p>
                        <p>
                            <span class="text-gray-500">Created:</span> {{ new Date(template.date).toLocaleString() }}
                        </p>
                    </div>
                </div>

                <div v-if="errorMessage" class="bg-red-500/20 text-red-400 p-4 rounded-lg mt-6">
                    {{ errorMessage }}
                </div>

                <div v-if="successMessage" class="bg-green-500/20 text-green-400 p-4 rounded-lg mt-6">
                    {{ successMessage }}
                </div>

                <div class="flex justify-center space-x-4 pt-4 border-t border-gray-700">
                    <button
                        type="button"
                        @click="router.push('/mc-admin/mail-templates')"
                        class="px-4 py-2 border border-gray-600 rounded-lg text-gray-300 hover:bg-gray-700 transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        v-if="template.locked !== 'true'"
                        type="button"
                        @click="deleteTemplate"
                        :disabled="deleting"
                        class="px-4 py-2 bg-red-500 hover:bg-red-600 rounded-lg text-white transition-colors flex items-center"
                    >
                        <LoaderIcon v-if="deleting" class="animate-spin w-4 h-4 mr-2" />
                        <TrashIcon v-else class="w-4 h-4 mr-2" />
                        Delete Template
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
import { ArrowLeftIcon, TrashIcon, LoaderIcon, LoaderCircle, AlertTriangleIcon, LockIcon } from 'lucide-vue-next';

interface MailTemplate {
    id: number;
    name: string;
    content: string;
    active: string;
    locked: string;
    deleted: string;
    date: string;
}

interface ApiResponse {
    success: boolean;
    message?: string;
}

const router = useRouter();
const route = useRoute();
const templateId = parseInt(route.params.id as string, 10);

const template = ref<MailTemplate | null>(null);
const loading = ref<boolean>(true);
const deleting = ref<boolean>(false);
const errorMessage = ref<string>('');
const successMessage = ref<string>('');

// Fetch template details
const fetchTemplateDetails = async (): Promise<void> => {
    loading.value = true;
    try {
        const response = await fetch(`/api/admin/mail/mail-templates`, {
            method: 'GET',
            headers: {
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error('Failed to fetch template');
        }

        const data = await response.json();

        if (data.success && data.mail_templates) {
            const foundTemplate = data.mail_templates.find((t: MailTemplate) => t.id === templateId);

            if (foundTemplate) {
                template.value = foundTemplate;
            }
        } else {
            console.error('Failed to load template:', data.message);
        }
    } catch (error) {
        console.error('Error fetching template:', error);
        errorMessage.value = 'Failed to load template data';
    } finally {
        loading.value = false;
    }
};

// Delete template function
const deleteTemplate = async (): Promise<void> => {
    if (!template.value || template.value.locked === 'true') {
        return;
    }

    deleting.value = true;
    errorMessage.value = '';
    successMessage.value = '';

    try {
        // Send delete request to API
        const response = await fetch(`/api/admin/mail/mail-templates/${templateId}/delete`, {
            method: 'POST',
        });

        const data = (await response.json()) as ApiResponse;

        if (data.success) {
            successMessage.value = 'Mail template deleted successfully';

            // Wait a moment before redirecting
            setTimeout(() => {
                router.push('/mc-admin/mail-templates');
            }, 1500);
        } else {
            errorMessage.value = data.message || 'Failed to delete mail template';
        }
    } catch (err) {
        console.error('Error deleting mail template:', err);
        errorMessage.value = 'An error occurred while deleting the mail template';
    } finally {
        deleting.value = false;
    }
};

onMounted(() => {
    fetchTemplateDetails();
});
</script>
