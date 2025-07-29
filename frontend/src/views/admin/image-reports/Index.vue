<template>
    <LayoutDashboard>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-400">Image Reports</h1>
            <div class="flex gap-2">
                <button
                    @click="refreshData"
                    class="bg-gradient-to-r from-blue-500 to-cyan-500 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:opacity-80 flex items-center"
                    :disabled="loading"
                >
                    <RefreshCwIcon class="w-4 h-4 mr-2" />
                    Refresh
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
            <div class="bg-gray-800/50 rounded-lg p-4 border border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-400">Total Reports</p>
                        <p class="text-2xl font-bold text-white">{{ stats.total }}</p>
                    </div>
                    <FileTextIcon class="w-8 h-8 text-blue-400" />
                </div>
            </div>
            <div class="bg-gray-800/50 rounded-lg p-4 border border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-400">Pending</p>
                        <p class="text-2xl font-bold text-yellow-400">{{ stats.pending }}</p>
                    </div>
                    <ClockIcon class="w-8 h-8 text-yellow-400" />
                </div>
            </div>
            <div class="bg-gray-800/50 rounded-lg p-4 border border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-400">Reviewed</p>
                        <p class="text-2xl font-bold text-blue-400">{{ stats.reviewed }}</p>
                    </div>
                    <EyeIcon class="w-8 h-8 text-blue-400" />
                </div>
            </div>
            <div class="bg-gray-800/50 rounded-lg p-4 border border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-400">Resolved</p>
                        <p class="text-2xl font-bold text-green-400">{{ stats.resolved }}</p>
                    </div>
                    <CheckCircleIcon class="w-8 h-8 text-green-400" />
                </div>
            </div>
            <div class="bg-gray-800/50 rounded-lg p-4 border border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-400">Dismissed</p>
                        <p class="text-2xl font-bold text-red-400">{{ stats.dismissed }}</p>
                    </div>
                    <XCircleIcon class="w-8 h-8 text-red-400" />
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-gray-800/50 rounded-lg p-4 mb-6 border border-gray-700">
            <div class="flex flex-wrap gap-4 items-center">
                <div class="flex-1 min-w-48">
                    <label class="block text-sm font-medium text-gray-400 mb-2">Status Filter</label>
                    <select
                        v-model="filters.status"
                        @change="applyFilters"
                        class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-pink-500"
                    >
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="reviewed">Reviewed</option>
                        <option value="resolved">Resolved</option>
                        <option value="dismissed">Dismissed</option>
                    </select>
                </div>
                <div class="flex-1 min-w-48">
                    <label class="block text-sm font-medium text-gray-400 mb-2">Reason Filter</label>
                    <select
                        v-model="filters.reason"
                        @change="applyFilters"
                        class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-pink-500"
                    >
                        <option value="">All Reasons</option>
                        <option value="inappropriate">Inappropriate</option>
                        <option value="copyright">Copyright</option>
                        <option value="spam">Spam</option>
                        <option value="harassment">Harassment</option>
                        <option value="violence">Violence</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button
                        @click="clearFilters"
                        class="bg-gray-600 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:bg-gray-500"
                    >
                        Clear Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Reports Table -->
        <div v-if="loading" class="flex justify-center items-center py-10">
            <LoaderCircle class="h-8 w-8 animate-spin text-pink-400" />
        </div>
        <TableTanstack v-else :data="reports" :columns="columns" tableName="Image Reports" />
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, onMounted, h, computed } from 'vue';
import LayoutDashboard from '@/components/admin/LayoutDashboard.vue';
import TableTanstack from '@/components/client/ui/Table/TableTanstack.vue';
import {
    EditIcon,
    TrashIcon,
    LoaderCircle,
    ClockIcon,
    RefreshCwIcon,
    FileTextIcon,
    EyeIcon,
    CheckCircleIcon,
    XCircleIcon,
    AlertTriangleIcon,
    CopyrightIcon,
    MessageSquareIcon,
    UserXIcon,
    SkullIcon,
    HelpCircleIcon,
} from 'lucide-vue-next';
import { useRouter } from 'vue-router';
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

// Stats interface
interface Stats {
    total: number;
    pending: number;
    reviewed: number;
    resolved: number;
    dismissed: number;
}

const router = useRouter();
const reports = ref<ImageReport[]>([]);
const stats = ref<Stats>({
    total: 0,
    pending: 0,
    reviewed: 0,
    resolved: 0,
    dismissed: 0,
});
const loading = ref(true);
const filters = ref({
    status: '',
    reason: '',
});

// Get reason icon and color
const getReasonInfo = (reason: string) => {
    const reasonMap = {
        inappropriate: { icon: AlertTriangleIcon, color: 'text-red-400', bgColor: 'bg-red-400/20' },
        copyright: { icon: CopyrightIcon, color: 'text-orange-400', bgColor: 'bg-orange-400/20' },
        spam: { icon: MessageSquareIcon, color: 'text-yellow-400', bgColor: 'bg-yellow-400/20' },
        harassment: { icon: UserXIcon, color: 'text-purple-400', bgColor: 'bg-purple-400/20' },
        violence: { icon: SkullIcon, color: 'text-red-600', bgColor: 'bg-red-600/20' },
        other: { icon: HelpCircleIcon, color: 'text-gray-400', bgColor: 'bg-gray-400/20' },
    };

    return reasonMap[reason as keyof typeof reasonMap] || reasonMap.other;
};

// Get status color
const getStatusColor = (status: string) => {
    const statusColors = {
        pending: 'text-yellow-400 bg-yellow-400/20',
        reviewed: 'text-blue-400 bg-blue-400/20',
        resolved: 'text-green-400 bg-green-400/20',
        dismissed: 'text-red-400 bg-red-400/20',
    };

    return statusColors[status as keyof typeof statusColors] || 'text-gray-400 bg-gray-400/20';
};

// Fetch reports
const fetchReports = async () => {
    try {
        loading.value = true;
        const params = new URLSearchParams();
        if (filters.value.status) params.append('status', filters.value.status);
        if (filters.value.reason) params.append('reason', filters.value.reason);

        const response = await fetch(`/api/admin/image-reports?${params.toString()}`);
        const data = await response.json();

        if (data.success) {
            reports.value = data.reports;
        } else {
            throw new Error(data.message || 'Failed to fetch reports');
        }
    } catch (error) {
        console.error('Error fetching reports:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to fetch image reports',
        });
    } finally {
        loading.value = false;
    }
};

// Fetch stats
const fetchStats = async () => {
    try {
        const response = await fetch('/api/admin/image-reports/stats/overview');
        const data = await response.json();

        if (data.success) {
            stats.value = data.stats;
        }
    } catch (error) {
        console.error('Error fetching stats:', error);
    }
};

// Apply filters
const applyFilters = () => {
    fetchReports();
};

// Clear filters
const clearFilters = () => {
    filters.value = { status: '', reason: '' };
    fetchReports();
};

// Refresh data
const refreshData = () => {
    fetchReports();
    fetchStats();
};

// Edit report
const editReport = (reportId: number) => {
    router.push(`/mc-admin/image-reports/${reportId}/edit`);
};

// Delete report
const deleteReport = async (reportId: number) => {
    const result = await Swal.fire({
        title: 'Delete Report?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Delete',
        cancelButtonText: 'Cancel',
    });

    if (result.isConfirmed) {
        try {
            const response = await fetch(`/api/admin/image-reports/${reportId}`, {
                method: 'DELETE',
            });
            const data = await response.json();

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'Report deleted successfully',
                });
                fetchReports();
                fetchStats();
            } else {
                throw new Error(data.message || 'Failed to delete report');
            }
        } catch (error) {
            console.error('Error deleting report:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to delete report',
            });
        }
    }
};

// Define columns for TableTanstack
const columns = [
    {
        accessorKey: 'id',
        header: 'ID',
        cell: (info: { getValue: () => number }) => info.getValue(),
    },
    {
        accessorKey: 'reason',
        header: 'Reason',
        cell: (info: { getValue: () => string }) => {
            const reason = info.getValue();
            const reasonInfo = getReasonInfo(reason);
            return h(
                'div',
                {
                    class: `flex items-center gap-2 px-3 py-1 rounded-full ${reasonInfo.bgColor}`,
                },
                [
                    h(reasonInfo.icon, { class: `w-4 h-4 ${reasonInfo.color}` }),
                    h('span', { class: 'text-sm font-medium' }, reason.charAt(0).toUpperCase() + reason.slice(1)),
                ],
            );
        },
    },
    {
        accessorKey: 'status',
        header: 'Status',
        cell: (info: { getValue: () => string }) => {
            const status = info.getValue();
            const statusClass = getStatusColor(status);
            return h(
                'span',
                {
                    class: `px-3 py-1 rounded-full text-sm font-medium ${statusClass}`,
                },
                status.charAt(0).toUpperCase() + status.slice(1),
            );
        },
    },
    {
        accessorKey: 'reported_at',
        header: 'Reported At',
        cell: (info: { getValue: () => string }) => {
            const date = new Date(info.getValue());
            return h(
                'span',
                {
                    class: 'text-sm text-gray-400',
                },
                date.toLocaleDateString() + ' ' + date.toLocaleTimeString(),
            );
        },
    },
    {
        accessorKey: 'reporter_ip',
        header: 'Reporter IP',
        cell: (info: { getValue: () => string }) => {
            const ip = info.getValue();
            return h(
                'span',
                {
                    class: 'font-mono text-sm text-gray-400',
                },
                ip,
            );
        },
    },
    {
        accessorKey: 'actions',
        header: 'Actions',
        cell: (info: { row: { original: ImageReport } }) => {
            const report = info.row.original;
            return h(
                'div',
                {
                    class: 'flex gap-2',
                },
                [
                    h(
                        'button',
                        {
                            onClick: () => editReport(report.id),
                            class: 'p-2 text-blue-400 hover:text-blue-300 transition-colors',
                        },
                        h(EditIcon, { class: 'w-4 h-4' }),
                    ),
                    h(
                        'button',
                        {
                            onClick: () => deleteReport(report.id),
                            class: 'p-2 text-red-400 hover:text-red-300 transition-colors',
                        },
                        h(TrashIcon, { class: 'w-4 h-4' }),
                    ),
                ],
            );
        },
    },
];

onMounted(() => {
    fetchReports();
    fetchStats();
});
</script>
