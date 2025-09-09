<template>
    <LayoutDashboard>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-400">Backups</h1>
            <button
                @click="createBackup"
                class="bg-gradient-to-r from-pink-500 to-violet-500 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:opacity-80 flex items-center"
            >
                <PlusIcon class="w-4 h-4 mr-2" />
                Create Backup
            </button>
        </div>

        <div v-if="loading" class="flex justify-center items-center py-10">
            <LoaderCircle class="h-8 w-8 animate-spin text-pink-400" />
        </div>
        <TableTanstack v-else :data="backups" :columns="columns" tableName="Backups" />
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, onMounted, h } from 'vue';
import LayoutDashboard from '@/components/admin/LayoutDashboard.vue';
import TableTanstack from '@/components/client/ui/Table/TableTanstack.vue';
import { PlusIcon, LoaderCircle, DownloadIcon, TrashIcon, CloudUploadIcon } from 'lucide-vue-next';
import Swal from 'sweetalert2';
import type { SweetAlertOptions } from 'sweetalert2';

interface Backup {
    id: number;
    filename: string;
    path: string;
    size: string;
    created_at: string;
}

const backups = ref<Backup[]>([]);
const loading = ref(true);

// Define columns for TableTanstack
const columns = [
    {
        accessorKey: 'id',
        header: 'ID',
        cell: (info: { getValue: () => number }) => info.getValue(),
    },
    {
        accessorKey: 'filename',
        header: 'Filename',
        cell: (info: { getValue: () => string }) => info.getValue(),
    },
    {
        accessorKey: 'size',
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
        cell: (info: { row: { original: Backup } }) => {
            const backup = info.row.original;
            return h('div', { class: 'flex space-x-2' }, [
                h(
                    'button',
                    {
                        class: 'p-1 text-gray-400 hover:text-pink-400 transition-colors',
                        title: 'Restore',
                        onClick: () => restoreBackup(backup),
                    },
                    [h(DownloadIcon, { class: 'w-4 h-4' })],
                ),
                h(
                    'button',
                    {
                        class: 'p-1 text-gray-400 hover:text-red-400 transition-colors',
                        title: 'Delete',
                        onClick: () => confirmDelete(backup),
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
        const response = await fetch('/api/admin/backups/list', {
            method: 'GET',
            headers: {
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error('Failed to fetch backups');
        }

        const data = await response.json();

        if (data.success) {
            backups.value = data.backups;
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to load backups: ' + data.message,
                confirmButtonColor: '#EC4899',
            });
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error fetching backups: ' + error,
            confirmButtonColor: '#EC4899',
        });
    } finally {
        loading.value = false;
    }
};

const createBackup = async () => {
    try {
        const response = await fetch('/api/admin/backup/create', {
            method: 'GET',
            headers: {
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error('Failed to create backup');
        }

        const data = await response.json();

        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Backup created successfully',
                confirmButtonColor: '#EC4899',
            });
            fetchBackups(); // Refresh the list
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to create backup: ' + data.message,
                confirmButtonColor: '#EC4899',
            });
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error creating backup: ' + error,
            confirmButtonColor: '#EC4899',
        });
    }
};

const restoreBackup = async (backup: Backup) => {
    // First confirmation
    const firstConfirm = await Swal.fire({
        title: 'Are you sure?',
        text: 'This will overwrite current data. This action cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EC4899',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Yes, restore it!',
        cancelButtonText: 'Cancel',
    });

    if (!firstConfirm.isConfirmed) {
        return;
    }

    // Math question confirmation
    const num1 = Math.floor(Math.random() * 10) + 1;
    const num2 = Math.floor(Math.random() * 10) + 1;
    const correctAnswer = num1 + num2;

    const { value: answer } = await Swal.fire({
        title: 'Security Check',
        html: `Please solve this math problem to confirm: ${num1} + ${num2} = ?`,
        input: 'number',
        inputAttributes: {
            min: 0,
            step: 1,
        },
        showCancelButton: true,
        confirmButtonColor: '#EC4899',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Confirm',
        cancelButtonText: 'Cancel',
        inputValidator: (value: string) => {
            if (!value) {
                return 'Please enter an answer';
            }
            if (parseInt(value) !== correctAnswer) {
                return 'Incorrect answer';
            }
            return null;
        },
    } as unknown as SweetAlertOptions);

    if (!answer || parseInt(answer) !== correctAnswer) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Incorrect answer. Operation cancelled.',
            confirmButtonColor: '#EC4899',
        });
        return;
    }

    // Final confirmation
    const finalConfirm = await Swal.fire({
        title: 'Final Confirmation',
        text: 'You are about to restore this backup. This will overwrite all current data. Are you absolutely sure?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EC4899',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Yes, I am absolutely sure!',
        cancelButtonText: 'Cancel',
        showDenyButton: true,
        denyButtonText: 'No, cancel',
        denyButtonColor: '#6B7280',
    });

    if (!finalConfirm.isConfirmed) {
        return;
    }

    // Show loading screen
    Swal.fire({
        title: 'Restoring Backup',
        html: `
            <div class="flex flex-col items-center justify-center space-y-4">
                <div class="relative w-24 h-24">
                    <div class="absolute inset-0 border-4 border-pink-500/20 rounded-full"></div>
                    <div class="absolute inset-0 border-4 border-pink-500 rounded-full animate-spin border-t-transparent"></div>
                </div>
                <div class="text-center space-y-2">
                    <p class="text-lg font-medium text-gray-200">Restoring your backup...</p>
                    <p class="text-sm text-gray-400">This process may take a few minutes</p>
                    <p class="text-xs text-gray-500 mt-4">If this page takes more than 5 minutes,<br>you might want to refresh this page</p>
                </div>
            </div>
        `,
        showConfirmButton: false,
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => {
            Swal.showLoading();
        },
    });

    try {
        const response = await fetch(`/api/admin/backup/${backup.id}/restore`, {
            method: 'GET',
            headers: {
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error('Failed to restore backup');
        }

        const data = await response.json();

        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                html: `
                    <div class="flex flex-col items-center justify-center space-y-4">
                        <div class="w-16 h-16 bg-green-500/20 rounded-full flex items-center justify-center">
                            <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <p class="text-lg font-medium text-gray-200">Backup Restored Successfully!</p>
                        <p class="text-sm text-gray-400">Your system has been restored to the selected backup point</p>
                    </div>
                `,
                confirmButtonColor: '#EC4899',
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                html: `
                    <div class="flex flex-col items-center justify-center space-y-4">
                        <div class="w-16 h-16 bg-red-500/20 rounded-full flex items-center justify-center">
                            <svg class="w-8 h-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </div>
                        <p class="text-lg font-medium text-gray-200">Restore Failed</p>
                        <p class="text-sm text-gray-400">${data.message}</p>
                    </div>
                `,
                confirmButtonColor: '#EC4899',
            });
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            html: `
                <div class="flex flex-col items-center justify-center space-y-4">
                    <div class="w-16 h-16 bg-red-500/20 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                    <p class="text-lg font-medium text-gray-200">Restore Failed</p>
                    <p class="text-sm text-gray-400">${error}</p>
                </div>
            `,
            confirmButtonColor: '#EC4899',
        });
    }
};

const confirmDelete = async (backup: Backup) => {
    const result = await Swal.fire({
        title: 'Are you sure?',
        text: 'This backup will be permanently deleted!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EC4899',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
    });

    if (!result.isConfirmed) {
        return;
    }

    try {
        const response = await fetch(`/api/admin/backup/${backup.id}/delete`, {
            method: 'GET',
            headers: {
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error('Failed to delete backup');
        }

        const data = await response.json();

        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Backup deleted successfully',
                confirmButtonColor: '#EC4899',
            });
            fetchBackups(); // Refresh the list
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to delete backup: ' + data.message,
                confirmButtonColor: '#EC4899',
            });
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error deleting backup: ' + error,
            confirmButtonColor: '#EC4899',
        });
    }
};

onMounted(() => {
    fetchBackups();
});
</script>
