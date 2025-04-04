<template>
    <LayoutDashboard>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-400">Edit Mail Template</h1>
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
            <form @submit.prevent="updateTemplate" class="space-y-6">
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
                        <div class="flex items-center justify-between mb-3">
                            <label for="content" class="block text-sm font-medium text-gray-400"
                                >Template Content</label
                            >
                            <div class="flex items-center gap-4">
                                <div class="flex items-center">
                                    <input
                                        type="checkbox"
                                        id="splitView"
                                        v-model="splitView"
                                        class="rounded border-gray-700 text-pink-500 focus:ring-pink-500 bg-gray-800/30 mr-2"
                                    />
                                    <label for="splitView" class="text-xs text-gray-400">Side-by-side Preview</label>
                                </div>
                                <button
                                    type="button"
                                    @click="openPreview"
                                    class="text-pink-400 hover:text-pink-300 text-xs flex items-center"
                                >
                                    <MaximizeIcon class="w-3.5 h-3.5 mr-1" />
                                    Full Preview
                                </button>
                                <div class="text-xs text-gray-500">HTML and variables supported</div>
                            </div>
                        </div>

                        <div :class="{ 'grid grid-cols-1 md:grid-cols-2 gap-4': splitView }">
                            <!-- Code Editor -->
                            <div class="flex flex-col">
                                <MonacoHtmlEditor
                                    v-model="templateForm.content"
                                    :height="splitView ? '500px' : '400px'"
                                    :options="{
                                        fontSize: 14,
                                        wordWrap: 'on',
                                        formatOnPaste: true,
                                        formatOnType: true,
                                    }"
                                />
                            </div>

                            <!-- Live Preview -->
                            <div v-if="splitView" class="flex flex-col h-[500px]">
                                <div
                                    class="bg-gray-700/50 px-3 py-2 text-xs text-gray-300 rounded-t-md flex justify-between items-center"
                                >
                                    <span>Live Preview</span>
                                    <button @click="refreshLivePreview" class="text-gray-400 hover:text-white">
                                        <RefreshCcwIcon class="w-3.5 h-3.5" />
                                    </button>
                                </div>
                                <div class="flex-1 bg-white overflow-auto rounded-b-md shadow">
                                    <iframe ref="livePreviewFrame" class="w-full h-full border-0"></iframe>
                                </div>
                            </div>
                        </div>

                        <div class="mt-2 text-xs text-gray-500">
                            <p>You can use HTML and the following variables in your template:</p>
                            <ul class="list-disc pl-5 mt-1 space-y-1">
                                <li><code>{user_name}</code> - The name of the user</li>
                                <li><code>{user_email}</code> - The email of the user</li>
                                <li><code>{server_name}</code> - The name of the server</li>
                                <li><code>{company_name}</code> - Your company name</li>
                            </ul>
                        </div>
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
                        Update Template
                    </button>
                </div>
            </form>
        </div>

        <!-- Template Preview Modal -->
        <TemplatePreviewModal
            :is-open="previewModalOpen"
            :html-content="templateForm.content"
            @close="previewModalOpen = false"
        />
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import LayoutDashboard from '@/components/admin/LayoutDashboard.vue';
import MonacoHtmlEditor from '@/components/admin/editors/MonacoHtmlEditor.vue';
import TemplatePreviewModal from '@/components/admin/editors/TemplatePreviewModal.vue';
import { ArrowLeftIcon, SaveIcon, LoaderIcon, LoaderCircle, MaximizeIcon, RefreshCcwIcon } from 'lucide-vue-next';

interface MailTemplate {
    id: number;
    name: string;
    content: string;
    active: string;
    locked: string;
    deleted: string;
    date: string;
}

interface TemplateForm {
    name: string;
    content: string;
    isActive: boolean;
}

interface ApiResponse {
    success: boolean;
    message?: string;
    mail_template?: MailTemplate;
}

const router = useRouter();
const route = useRoute();
const templateId = parseInt(route.params.id as string, 10);

const template = ref<MailTemplate | null>(null);
const loading = ref<boolean>(true);
const saving = ref<boolean>(false);
const errorMessage = ref<string>('');
const successMessage = ref<string>('');
const previewModalOpen = ref<boolean>(false);
const splitView = ref<boolean>(true);
const livePreviewFrame = ref<HTMLIFrameElement | null>(null);

// Form state with default values
const templateForm = ref<TemplateForm>({
    name: '',
    content: '',
    isActive: false,
});

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

// Open full preview modal
const openPreview = () => {
    previewModalOpen.value = true;
};

// Update the live preview iframe
const updateLivePreview = () => {
    if (!livePreviewFrame.value || !splitView.value) return;

    const processed = processContent(templateForm.value.content);

    // Get the iframe document
    const doc = livePreviewFrame.value.contentDocument || livePreviewFrame.value.contentWindow?.document;
    if (!doc) return;

    // Write content to iframe
    doc.open();
    doc.write(processed);
    doc.close();
};

// Manually refresh the live preview
const refreshLivePreview = () => {
    updateLivePreview();
};

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

                // Initialize form with template data
                templateForm.value = {
                    name: foundTemplate.name,
                    content: foundTemplate.content,
                    isActive: foundTemplate.active === 'true',
                };

                // Update the preview after a short delay
                setTimeout(updateLivePreview, 100);
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

// Update template function
const updateTemplate = async (): Promise<void> => {
    saving.value = true;
    errorMessage.value = '';
    successMessage.value = '';

    try {
        // Create FormData object
        const formData = new FormData();
        formData.append('name', templateForm.value.name);
        formData.append('content', templateForm.value.content);
        formData.append('active', templateForm.value.isActive ? 'true' : 'false');

        // Send update request to API
        const response = await fetch(`/api/admin/mail/mail-templates/${templateId}/update`, {
            method: 'POST',
            body: formData,
        });

        const data = (await response.json()) as ApiResponse;

        if (data.success) {
            successMessage.value = 'Mail template updated successfully';

            // Update local template data
            if (template.value) {
                template.value.name = templateForm.value.name;
                template.value.content = templateForm.value.content;
                template.value.active = templateForm.value.isActive ? 'true' : 'false';
            }

            // Wait a moment before redirecting
            setTimeout(() => {
                router.push('/mc-admin/mail-templates');
            }, 1500);
        } else {
            errorMessage.value = data.message || 'Failed to update mail template';
        }
    } catch (err) {
        console.error('Error updating mail template:', err);
        errorMessage.value = 'An error occurred while updating the mail template';
    } finally {
        saving.value = false;
    }
};

// Watch for content changes to update the live preview
watch(
    () => templateForm.value.content,
    () => {
        if (splitView.value) {
            updateLivePreview();
        }
    },
);

// Watch for split view changes
watch(
    () => splitView.value,
    (newVal) => {
        if (newVal) {
            // If turning on split view, update preview after a short delay
            setTimeout(updateLivePreview, 100);
        }
    },
);

onMounted(() => {
    fetchTemplateDetails();
});
</script>

<style scoped>
code {
    background-color: rgba(45, 55, 72, 0.5);
    padding: 0.1rem 0.3rem;
    border-radius: 0.25rem;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;
    color: #f687b3; /* Pink-ish color to match the theme */
}
</style>
