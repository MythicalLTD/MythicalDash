<template>
    <LayoutDashboard>
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center gap-4">
                <button @click="router.back()" class="text-gray-400 hover:text-white transition-colors">
                    <ArrowLeftIcon class="w-6 h-6" />
                </button>
                <h1 class="text-2xl font-bold text-pink-400">Edit Image Report</h1>
            </div>
            <div class="flex gap-2">
                <button
                    @click="saveReport"
                    :disabled="loading || saving || report?.status === 'resolved'"
                    class="bg-gradient-to-r from-green-500 to-emerald-500 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:opacity-80 flex items-center disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <SaveIcon class="w-4 h-4 mr-2" />
                    {{ saving ? 'Saving...' : report?.status === 'resolved' ? 'Status Locked' : 'Save Changes' }}
                </button>
            </div>
        </div>

        <div v-if="loading" class="flex justify-center items-center py-10">
            <LoaderCircle class="h-8 w-8 animate-spin text-pink-400" />
        </div>

        <div v-else-if="report" class="space-y-6">
            <!-- Report Details -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Left Column -->
                <div class="space-y-6">
                    <!-- Basic Information -->
                    <div class="bg-gray-800/50 rounded-lg p-6 border border-gray-700">
                        <h3 class="text-lg font-semibold text-white mb-4">Report Information</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-2">Report ID</label>
                                <div class="bg-gray-700 px-3 py-2 rounded-lg">
                                    <span class="text-white font-mono">#{{ report.id }}</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-2">Image ID</label>
                                <div class="bg-gray-700 px-3 py-2 rounded-lg">
                                    <span class="text-white font-mono">{{ report.image_id }}</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-2">Image URL</label>
                                <div class="bg-gray-700 px-3 py-2 rounded-lg">
                                    <a
                                        :href="report.image_url"
                                        target="_blank"
                                        class="text-blue-400 hover:text-blue-300 break-all"
                                    >
                                        {{ report.image_url }}
                                    </a>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-2">Reason</label>
                                <div class="flex items-center gap-2">
                                    <component
                                        :is="getReasonInfo(report.reason).icon"
                                        :class="`w-5 h-5 ${getReasonInfo(report.reason).color}`"
                                    />
                                    <span class="text-white capitalize">{{ report.reason }}</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-2">Reported At</label>
                                <div class="text-white">
                                    {{ formatDate(report.reported_at) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reporter Information -->
                    <div class="bg-gray-800/50 rounded-lg p-6 border border-gray-700">
                        <h3 class="text-lg font-semibold text-white mb-4">Reporter Information</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-2">IP Address</label>
                                <div class="bg-gray-700 px-3 py-2 rounded-lg">
                                    <span class="text-white font-mono">{{ report.reporter_ip }}</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-2">User Agent</label>
                                <div class="bg-gray-700 px-3 py-2 rounded-lg">
                                    <span class="text-white text-sm break-all">{{ report.user_agent }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Image Owner Information -->
                    <div class="bg-gray-800/50 rounded-lg p-6 border border-gray-700">
                        <h3 class="text-lg font-semibold text-white mb-4">Image Owner Information</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-2">User UUID</label>
                                <div class="bg-gray-700 px-3 py-2 rounded-lg">
                                    <span class="text-white font-mono">{{ getImageOwnerUuid(report.image_id) }}</span>
                                </div>
                            </div>
                            <div>
                                <a
                                    :href="`/mc-admin/users/${getImageOwnerUuid(report.image_id)}/edit`"
                                    target="_blank"
                                    class="inline-flex items-center gap-2 bg-gradient-to-r from-blue-500 to-cyan-500 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:opacity-80"
                                >
                                    <UserIcon class="w-4 h-4" />
                                    Manage User
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <!-- Status Management -->
                    <div class="bg-gray-800/50 rounded-lg p-6 border border-gray-700">
                        <h3 class="text-lg font-semibold text-white mb-4">Status Management</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-2">Current Status</label>
                                <select
                                    v-model="formData.status"
                                    :disabled="report.status === 'resolved'"
                                    class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-pink-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <option value="pending">Pending</option>
                                    <option value="reviewed">Reviewed</option>
                                    <option value="resolved" class="text-red-400">Resolved (Deletes Image)</option>
                                    <option value="dismissed">Dismissed</option>
                                </select>
                                <p v-if="formData.status === 'resolved'" class="text-xs text-red-400 mt-1">
                                    ⚠️ This will permanently delete the reported image
                                </p>
                                <p v-if="report.status === 'resolved'" class="text-xs text-yellow-400 mt-1">
                                    🔒 Status locked - Image has been deleted and cannot be changed
                                </p>
                            </div>
                            <div v-if="report.resolved_at">
                                <label class="block text-sm font-medium text-gray-400 mb-2">Resolved At</label>
                                <div class="text-white">
                                    {{ formatDate(report.resolved_at) }}
                                </div>
                            </div>
                            <div v-if="report.resolved_by">
                                <label class="block text-sm font-medium text-gray-400 mb-2">Resolved By</label>
                                <div class="text-white">
                                    {{ report.resolved_by }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Admin Notes -->
                    <div class="bg-gray-800/50 rounded-lg p-6 border border-gray-700">
                        <h3 class="text-lg font-semibold text-white mb-4">Admin Notes</h3>
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-2">Notes</label>
                            <textarea
                                v-model="formData.admin_notes"
                                rows="6"
                                class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-pink-500 resize-none"
                                placeholder="Add admin notes here..."
                                maxlength="1000"
                            ></textarea>
                            <div class="text-xs text-gray-500 mt-1">
                                {{ formData.admin_notes.length }}/1000 characters
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report Details -->
            <div class="bg-gray-800/50 rounded-lg p-6 border border-gray-700">
                <h3 class="text-lg font-semibold text-white mb-4">Report Details</h3>
                <div v-if="report.details" class="bg-gray-700 p-4 rounded-lg">
                    <p class="text-white whitespace-pre-wrap">{{ report.details }}</p>
                </div>
                <div v-else class="text-gray-400 italic">No additional details provided</div>
            </div>

            <!-- Image Preview -->
            <div class="bg-gray-800/50 rounded-lg p-6 border border-gray-700">
                <h3 class="text-lg font-semibold text-white mb-4">Image Preview</h3>
                <div class="flex justify-center">
                    <div class="relative">
                        <img
                            :src="getDirectImageUrl(report.image_url)"
                            :alt="report.image_id"
                            class="max-w-full max-h-96 rounded-lg border border-gray-600"
                            @error="handleImageError"
                        />
                        <div v-if="imageError" class="bg-gray-700 p-4 rounded-lg text-center">
                            <AlertTriangleIcon class="w-8 h-8 text-yellow-400 mx-auto mb-2" />
                            <p class="text-gray-400">Image could not be loaded</p>
                            <p class="text-xs text-gray-500 mt-2">
                                Direct URL: {{ getDirectImageUrl(report.image_url) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="text-center py-10">
            <AlertTriangleIcon class="w-12 h-12 text-red-400 mx-auto mb-4" />
            <h3 class="text-lg font-semibold text-white mb-2">Report Not Found</h3>
            <p class="text-gray-400">The requested image report could not be found.</p>
        </div>
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import LayoutDashboard from '@/components/admin/LayoutDashboard.vue';
import {
    ArrowLeftIcon,
    SaveIcon,
    LoaderCircle,
    AlertTriangleIcon,
    AlertTriangleIcon as AlertTriangleIcon2,
    CopyrightIcon,
    MessageSquareIcon,
    UserXIcon,
    SkullIcon,
    HelpCircleIcon,
    UserIcon,
} from 'lucide-vue-next';
import Swal from 'sweetalert2';

// Image Report interface
interface ImageReport {
    id: number;
    image_id: string;
    image_url: string;
    reason: string;
    details: string;
    reporter_ip: string;
    user_agent: string;
    reported_at: string;
    status: string;
    admin_notes: string;
    resolved_at: string;
    resolved_by: string;
}

const route = useRoute();
const router = useRouter();
const report = ref<ImageReport | null>(null);
const loading = ref(true);
const saving = ref(false);
const imageError = ref(false);

const formData = ref({
    status: '',
    admin_notes: '',
});

// Get reason icon and color
const getReasonInfo = (reason: string) => {
    const reasonMap = {
        inappropriate: { icon: AlertTriangleIcon2, color: 'text-red-400' },
        copyright: { icon: CopyrightIcon, color: 'text-orange-400' },
        spam: { icon: MessageSquareIcon, color: 'text-yellow-400' },
        harassment: { icon: UserXIcon, color: 'text-purple-400' },
        violence: { icon: SkullIcon, color: 'text-red-600' },
        other: { icon: HelpCircleIcon, color: 'text-gray-400' },
    };

    return reasonMap[reason as keyof typeof reasonMap] || reasonMap.other;
};

// Format date
const formatDate = (dateString: string) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
};

// Handle image error
const handleImageError = () => {
    imageError.value = true;
};

// Get direct image URL for preview
const getDirectImageUrl = (imageUrl: string) => {
    // Extract image ID from the URL (format: /i/{uuid-timestamp.ext})
    const match = imageUrl.match(/\/i\/([0-9a-f\-]{36}-\d+\.[a-zA-Z0-9]+)/);
    if (match) {
        const imageId = match[1];
        // Extract UUID from image ID
        const uuidMatch = imageId?.match(/^([0-9a-f\-]{36})-/);
        if (uuidMatch) {
            const uuid = uuidMatch[1];
            // Construct direct file URL
            return `${window.location.origin}/attachments/imgs/users/${uuid}/raw/${imageId}`;
        }
    }
    // Fallback to original URL if parsing fails
    return imageUrl;
};

// Get image owner UUID from image ID
const getImageOwnerUuid = (imageId: string) => {
    const match = imageId.match(/^([0-9a-f\-]{36})-/);
    if (match) {
        return match[1];
    }
    return 'N/A';
};

// Fetch report
const fetchReport = async () => {
    try {
        loading.value = true;
        const reportId = route.params.id;

        const response = await fetch(`/api/admin/image-reports/${reportId}`);
        const data = await response.json();

        if (data.success) {
            report.value = data.report;
            formData.value.status = data.report.status;
            formData.value.admin_notes = data.report.admin_notes || '';
        } else {
            throw new Error(data.message || 'Failed to fetch report');
        }
    } catch (error) {
        console.error('Error fetching report:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to fetch image report',
        });
    } finally {
        loading.value = false;
    }
};

// Save report
const saveReport = async () => {
    // Prevent saving if report is already resolved
    if (report.value && report.value.status === 'resolved') {
        Swal.fire({
            icon: 'warning',
            title: 'Status Locked',
            text: 'This report has already been resolved and the image has been deleted. The status cannot be changed.',
        });
        return;
    }

    // If the status is being changed to resolved, show confirmation
    if (formData.value.status === 'resolved' && report.value && report.value.status !== 'resolved') {
        const result = await Swal.fire({
            title: 'Delete Image?',
            text: 'Marking this report as resolved will permanently delete the reported image. This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, Delete Image',
            cancelButtonText: 'Cancel',
        });

        if (!result.isConfirmed) {
            return;
        }
    }

    try {
        saving.value = true;
        const reportId = route.params.id;

        const response = await fetch(`/api/admin/image-reports/${reportId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                status: formData.value.status,
                admin_notes: formData.value.admin_notes,
            }),
        });

        const data = await response.json();

        if (data.success) {
            // Show different message if image was deleted
            if (data.image_deleted) {
                Swal.fire({
                    icon: 'success',
                    title: 'Report Resolved',
                    text: 'Report marked as resolved and image has been permanently deleted.',
                });
            } else {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'Report updated successfully',
                });
            }

            // Refresh the report data
            await fetchReport();
        } else {
            throw new Error(data.message || 'Failed to update report');
        }
    } catch (error) {
        console.error('Error updating report:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to update report',
        });
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    fetchReport();
});
</script>
