<template>
    <LayoutDashboard>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-400">Create Mail Template</h1>
            <button
                @click="router.push('/mc-admin/mail-templates')"
                class="bg-gray-700 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:bg-gray-600 flex items-center"
            >
                <ArrowLeftIcon class="w-4 h-4 mr-2" />
                Back to Templates
            </button>
        </div>

        <div class="bg-gray-800/50 backdrop-blur-md rounded-lg p-6">
            <form @submit.prevent="createTemplate" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label for="name" class="block text-sm font-medium text-gray-400 mb-1">Template Name</label>
                        <input
                            id="name"
                            v-model="templateForm.name"
                            type="text"
                            required
                            class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                            placeholder="Enter template name"
                        />
                    </div>

                    <div class="md:col-span-2">
                        <div class="flex items-center justify-between mb-1">
                            <label for="content" class="block text-sm font-medium text-gray-400"
                                >Template Content</label
                            >
                            <div class="text-xs text-gray-500">HTML and variables supported</div>
                        </div>
                        <textarea
                            id="content"
                            v-model="templateForm.content"
                            rows="10"
                            required
                            class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500 font-mono text-sm"
                            placeholder="Enter your email template content here..."
                        ></textarea>
                    </div>

                    <div class="md:col-span-2 flex items-center space-x-3">
                        <div class="flex items-center space-x-2">
                            <input
                                type="checkbox"
                                id="active"
                                v-model="templateForm.isActive"
                                class="rounded border-gray-700 text-pink-500 focus:ring-pink-500 bg-gray-800/30"
                            />
                            <label for="active" class="text-sm text-gray-400">Active</label>
                        </div>
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
                        @click="router.push('/mc-admin/mail-templates')"
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
                        Create Template
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

interface TemplateForm {
    name: string;
    content: string;
    isActive: boolean;
}

interface ApiResponse {
    success: boolean;
    message?: string;
    mail_template?: {
        id: number;
        name: string;
        content: string;
        active: string;
    };
}

const router = useRouter();
const saving = ref<boolean>(false);
const errorMessage = ref<string>('');
const successMessage = ref<string>('');

// Form state with default values
const templateForm = ref<TemplateForm>({
    name: '',
    content: '',
    isActive: false,
});

// Create mail template function
const createTemplate = async (): Promise<void> => {
    saving.value = true;
    errorMessage.value = '';
    successMessage.value = '';

    try {
        // Create FormData object
        const formData = new FormData();
        formData.append('name', templateForm.value.name);
        formData.append('content', templateForm.value.content);
        formData.append('active', templateForm.value.isActive ? 'true' : 'false');

        // Send create request to API
        const response = await fetch('/api/admin/mail/mail-templates/create', {
            method: 'POST',
            body: formData,
        });

        const data = (await response.json()) as ApiResponse;

        if (data.success && data.mail_template) {
            successMessage.value = 'Mail template created successfully';

            // Wait a moment before redirecting
            setTimeout(() => {
                router.push('/mc-admin/mail-templates');
            }, 1500);
        } else {
            errorMessage.value = data.message || 'Failed to create mail template';
        }
    } catch (err) {
        console.error('Error creating mail template:', err);
        errorMessage.value = 'An error occurred while creating the mail template';
    } finally {
        saving.value = false;
    }
};
</script>
