<template>
    <LayoutDashboard>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-400">Mythical Cloud Backups</h1>
            <div class="flex gap-2">
                <button
                    @click="refreshBackups"
                    class="bg-gradient-to-r from-blue-500 to-indigo-500 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:opacity-80 flex items-center"
                >
                    <RefreshCcwIcon class="w-4 h-4 mr-2" />
                    Refresh
                </button>
                <button
                    @click="confirmWipeBackups"
                    class="bg-gradient-to-r from-red-500 to-orange-500 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:opacity-80 flex items-center"
                >
                    <TrashIcon class="w-4 h-4 mr-2" />
                    Wipe All
                </button>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-md mb-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-700 dark:text-gray-200">Storage Information</h2>
            <div v-if="storageInfo" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Used Space</p>
                    <p class="text-xl font-bold text-pink-500">{{ storageInfo.used_space_formatted }}</p>
                    <p class="text-xs text-gray-500">{{ storageInfo.backup_count }} backups</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Remaining Space</p>
                    <p class="text-xl font-bold text-green-500">{{ storageInfo.remaining_space_formatted }}</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total Storage</p>
                    <p class="text-xl font-bold text-blue-500">{{ storageInfo.storage_limit_formatted }}</p>
                </div>
            </div>
            <div v-else class="py-4 text-center text-gray-500">Storage information not available</div>
        </div>

        <div v-if="loading" class="flex justify-center items-center py-10">
            <LoaderCircle class="h-8 w-8 animate-spin text-pink-400" />
        </div>
        <TableTanstack v-else :data="backups" :columns="columns" tableName="Cloud Backups" />
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, onMounted, h } from 'vue';
import LayoutDashboard from '@/components/admin/LayoutDashboard.vue';
import TableTanstack from '@/components/client/ui/Table/TableTanstack.vue';
import { RefreshCcwIcon, LoaderCircle, DownloadIcon, TrashIcon, InfoIcon } from 'lucide-vue-next';
import Swal from 'sweetalert2';

interface CloudBackup {
    id: number | string;
    filename: string;
    size: number;
    size_formatted: string;
    created_at: string;
    download_url?: string;
    info_url?: string;
}

interface StorageInfo {
    used_space: number;
    used_space_formatted: string;
    backup_count: number;
    remaining_space: number;
    remaining_space_formatted: string;
    storage_limit: number;
    storage_limit_formatted: string;
}

const backups = ref<CloudBackup[]>([]);
const loading = ref(true);
const storageInfo = ref<StorageInfo | null>(null);

// Define columns for TableTanstack
const columns = [
    {
        accessorKey: 'id',
        header: 'ID',
        cell: (info: { getValue: () => number | string }) => info.getValue(),
    },
    {
        accessorKey: 'filename',
        header: 'Filename',
        cell: (info: { getValue: () => string }) => info.getValue(),
    },
    {
        accessorKey: 'size_formatted',
        header: 'Size',
        cell: (info: { getValue: () => string }) => info.getValue(),
    },
    {
        accessorKey: 'created_at',
        header: 'Created At',
        cell: (info: { getValue: () => string }) =>
            info.getValue() ? new Date(info.getValue()).toLocaleString() : 'N/A',
    },
    {
        id: 'actions',
        header: 'Actions',
        cell: (info: { row: { original: CloudBackup } }) => {
            const backup = info.row.original;
            return h('div', { class: 'flex space-x-2' }, [
                h(
                    'button',
                    {
                        class: 'p-1 text-gray-400 hover:text-blue-400 transition-colors',
                        title: 'Info',
                        onClick: () => viewBackupInfo(backup),
                    },
                    [h(InfoIcon, { class: 'w-4 h-4' })],
                ),
                h(
                    'button',
                    {
                        class: 'p-1 text-gray-400 hover:text-green-400 transition-colors',
                        title: 'Download',
                        onClick: () => downloadBackup(backup),
                    },
                    [h(DownloadIcon, { class: 'w-4 h-4' })],
                ),
                h(
                    'button',
                    {
                        class: 'p-1 text-gray-400 hover:text-red-400 transition-colors',
                        title: 'Delete',
                        onClick: () => confirmDeleteBackup(backup),
                    },
                    [h(TrashIcon, { class: 'w-4 h-4' })],
                ),
            ]);
        },
    },
];

// Fetch backups from API
const fetchBackups = async () => {
    loading.value = true;
    try {
        const response = await fetch('/api/admin/cloud/backups', {
            method: 'GET',
            headers: {
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error('Failed to fetch cloud backups');
        }

        const data = await response.json();

        if (data.success) {
            backups.value = data.backups || [];
            storageInfo.value = data.storage_info || null;
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to load cloud backups: ' + data.message,
                confirmButtonColor: '#EC4899',
            });
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error fetching cloud backups: ' + error,
            confirmButtonColor: '#EC4899',
        });
    } finally {
        loading.value = false;
    }
};

const refreshBackups = () => {
    fetchBackups();
};

const viewBackupInfo = async (backup: CloudBackup) => {
    try {
        Swal.fire({
            title: 'Loading Backup Info',
            html: 'Please wait...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            },
        });

        const response = await fetch(`/api/admin/cloud/backup/${backup.id}`, {
            method: 'GET',
            headers: {
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error('Failed to fetch backup info');
        }

        const data = await response.json();

        if (data.success) {
            const backupInfo = data.backup;
            Swal.fire({
                title: 'Backup Information',
                html: `
                    <div class="text-left">
                        <p><strong>ID:</strong> ${backupInfo.id}</p>
                        <p><strong>Filename:</strong> ${backupInfo.filename}</p>
                        <p><strong>Size:</strong> ${backupInfo.size_formatted}</p>
                        <p><strong>Created At:</strong> ${new Date(backupInfo.created_at).toLocaleString()}</p>
                        <p><strong>Original Name:</strong> ${backupInfo.metadata?.original_name || 'N/A'}</p>
                        <hr>
                        <h3 class="text-lg font-bold mt-2">OS Information</h3>
                        <p><strong>OS Type:</strong> ${backupInfo.os_info?.os_type || 'Unknown'}</p>
                        <p><strong>Kernel:</strong> ${backupInfo.os_info?.kernel_version || 'Unknown'}</p>
                        <p><strong>CPU Architecture:</strong> ${backupInfo.os_info?.cpu_architecture || 'Unknown'}</p>
                    </div>
                `,
                icon: 'info',
                confirmButtonColor: '#EC4899',
            });
        } else {
            throw new Error(data.message || 'Failed to fetch backup info');
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: typeof error === 'string' ? error : (error as Error).message,
            confirmButtonColor: '#EC4899',
        });
    }
};

const downloadBackup = async (backup: CloudBackup) => {
    try {
        Swal.fire({
            title: 'Downloading Backup',
            html: 'Please wait while we prepare your backup...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            },
        });

        const response = await fetch(`/api/admin/cloud/backup/${backup.id}/download`, {
            method: 'GET',
            headers: {
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error('Failed to download backup');
        }

        const data = await response.json();

        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Download Complete',
                text: 'Backup has been downloaded successfully to your server storage.',
                confirmButtonColor: '#EC4899',
            });
        } else {
            throw new Error(data.message || 'Failed to download backup');
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: typeof error === 'string' ? error : (error as Error).message,
            confirmButtonColor: '#EC4899',
        });
    }
};

const confirmDeleteBackup = async (backup: CloudBackup) => {
    const result = await Swal.fire({
        title: 'Are you sure?',
        text: `Do you want to delete the backup "${backup.filename}"? This action cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Yes, delete it!',
    });

    if (result.isConfirmed) {
        try {
            Swal.fire({
                title: 'Deleting Backup',
                html: 'Please wait...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                },
            });

            const response = await fetch(`/api/admin/cloud/backup/${backup.id}/delete`, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                },
            });

            if (!response.ok) {
                throw new Error('Failed to delete backup');
            }

            const data = await response.json();

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Deleted!',
                    text: 'Your backup has been deleted.',
                    confirmButtonColor: '#EC4899',
                });
                fetchBackups(); // Refresh the list
            } else {
                throw new Error(data.message || 'Failed to delete backup');
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: typeof error === 'string' ? error : (error as Error).message,
                confirmButtonColor: '#EC4899',
            });
        }
    }
};

const confirmWipeBackups = async () => {
    const result = await Swal.fire({
        title: 'Delete All Backups?',
        text: 'Are you sure you want to delete ALL cloud backups? This action cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Yes, delete all!',
    });

    if (result.isConfirmed) {
        try {
            Swal.fire({
                title: 'Deleting All Backups',
                html: 'Please wait...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                },
            });

            const response = await fetch('/api/admin/cloud/backups/wipe', {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                },
            });

            if (!response.ok) {
                throw new Error('Failed to wipe all backups');
            }

            const data = await response.json();

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'All Backups Deleted!',
                    text: data.message || 'All backups have been deleted successfully.',
                    confirmButtonColor: '#EC4899',
                });
                fetchBackups(); // Refresh the list
            } else {
                throw new Error(data.message || 'Failed to wipe all backups');
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: typeof error === 'string' ? error : (error as Error).message,
                confirmButtonColor: '#EC4899',
            });
        }
    }
};

onMounted(() => {
    fetchBackups();
});
</script>
