<template>
    <LayoutDashboard>
        <div v-if="loading" class="flex justify-center items-center h-64">
            <LoaderCircle class="h-10 w-10 animate-spin text-pink-400" />
        </div>
        <template v-else-if="department">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-pink-400">Delete Department: {{ department.name }}</h1>
                <button
                    @click="router.push('/mc-admin/departments')"
                    class="bg-gray-700 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:bg-gray-600 flex items-center"
                >
                    <ArrowLeftIcon class="w-4 h-4 mr-2" />
                    Back to Departments
                </button>
            </div>

            <div class="bg-gray-800/50 backdrop-blur-md rounded-lg p-6">
                <div class="mb-6 flex items-start">
                    <div class="mr-4 mt-1 text-red-400">
                        <AlertTriangleIcon class="w-8 h-8" />
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-white">Confirm Department Deletion</h2>
                        <p class="text-gray-400 mt-2">
                            You are about to delete the department
                            <span class="font-bold text-white">"{{ department.name }}"</span>. This action cannot be
                            undone.
                        </p>
                        <p class="text-gray-400 mt-2">
                            If tickets are associated with this department, they may become inaccessible or display
                            incorrectly.
                        </p>
                    </div>
                </div>

                <div class="bg-gray-900/50 rounded-lg p-4 mb-6">
                    <h3 class="text-white font-medium mb-2">Department Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-gray-400">ID</p>
                            <p class="text-white">{{ department.id }}</p>
                        </div>
                        <div>
                            <p class="text-gray-400">Name</p>
                            <p class="text-white">{{ department.name }}</p>
                        </div>
                        <div>
                            <p class="text-gray-400">Status</p>
                            <p class="text-white">{{ department.enabled === 'true' ? 'Enabled' : 'Disabled' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-400">Hours</p>
                            <p class="text-white">{{ department.time_open }} - {{ department.time_close }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-gray-400">Description</p>
                            <p class="text-white">{{ department.description }}</p>
                        </div>
                    </div>
                </div>

                <form @submit.prevent="confirmDelete" class="space-y-4">
                    <div class="flex items-center">
                        <input
                            id="confirm"
                            v-model="confirmationChecked"
                            type="checkbox"
                            class="w-4 h-4 text-pink-500 bg-gray-800 border-gray-600 rounded focus:ring-pink-500"
                        />
                        <label for="confirm" class="ml-2 text-sm text-gray-300">
                            I understand that this action cannot be undone
                        </label>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-700">
                        <button
                            type="button"
                            @click="router.push('/mc-admin/departments')"
                            class="px-4 py-2 border border-gray-600 rounded-lg text-gray-300 hover:bg-gray-700 transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="deleting || !confirmationChecked"
                            class="px-4 py-2 bg-red-500 rounded-lg text-white hover:bg-red-600 transition-colors flex items-center disabled:opacity-50 disabled:pointer-events-none"
                        >
                            <TrashIcon v-if="!deleting" class="w-4 h-4 mr-2" />
                            <LoaderIcon v-else class="animate-spin w-4 h-4 mr-2" />
                            Delete Department
                        </button>
                    </div>
                </form>
            </div>
        </template>
        <div v-else class="bg-gray-800/50 backdrop-blur-md rounded-lg p-6 text-center">
            <div class="text-red-400 mb-4">
                <AlertCircleIcon class="w-16 h-16 mx-auto" />
            </div>
            <h2 class="text-xl font-semibold text-white">Department Not Found</h2>
            <p class="text-gray-400 mt-2">
                The department you're trying to delete doesn't exist or has already been deleted.
            </p>
            <button
                @click="router.push('/mc-admin/departments')"
                class="mt-4 px-4 py-2 bg-gray-700 text-white rounded-lg transition-all duration-200 hover:bg-gray-600"
            >
                Back to Departments
            </button>
        </div>
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import LayoutDashboard from '@/components/admin/LayoutDashboard.vue';
import {
    ArrowLeftIcon,
    TrashIcon,
    LoaderIcon,
    LoaderCircle,
    AlertCircleIcon,
    AlertTriangleIcon,
} from 'lucide-vue-next';
import Swal from 'sweetalert2';
import { useSound } from '@vueuse/sound';
import failedAlertSfx from '@/assets/sounds/error.mp3';
import successAlertSfx from '@/assets/sounds/success.mp3';

// Define interfaces for the data structures
interface Department {
    id: number;
    name: string;
    description: string;
    time_open: string;
    time_close: string;
    enabled: string;
    // Add any other properties that might be in the department object
}

interface ApiResponse {
    success: boolean;
    message?: string;
    departments?: Department[];
    error_code?: string;
}

const router = useRouter();
const route = useRoute();
const departmentId = parseInt(route.params.id as string);

const loading = ref<boolean>(true);
const deleting = ref<boolean>(false);
const department = ref<Department | null>(null);
const confirmationChecked = ref<boolean>(false);
const { play: playError } = useSound(failedAlertSfx);
const { play: playSuccess } = useSound(successAlertSfx);

// Fetch department details
const fetchDepartment = async (): Promise<void> => {
    loading.value = true;
    try {
        const response = await fetch(`/api/admin/ticket/departments`, {
            method: 'GET',
            headers: {
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error('Failed to fetch departments');
        }

        const data = (await response.json()) as ApiResponse;

        if (data.success && data.departments) {
            const foundDepartment = data.departments.find((dept: Department) => dept.id === departmentId);
            if (foundDepartment) {
                department.value = foundDepartment;
            }
        } else {
            console.error('Failed to load department:', data.message);
        }
    } catch (error) {
        console.error('Error fetching department:', error);
    } finally {
        loading.value = false;
    }
};

const confirmDelete = async () => {
    if (!confirmationChecked.value) {
        playError();
        Swal.fire({
            icon: 'error',
            title: 'Confirmation Required',
            text: 'Please confirm that you understand this action cannot be undone',
            showConfirmButton: true,
        });
        return;
    }

    deleting.value = true;

    try {
        const formData = new FormData();

        const response = await fetch(`/api/admin/ticket/departments/${departmentId}/delete`, {
            method: 'POST',
            body: formData,
        });

        const data = await response.json();

        if (data.success) {
            playSuccess();
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Department deleted successfully',
                showConfirmButton: true,
            });

            // Navigate back to departments list after a short delay
            setTimeout(() => {
                router.push('/mc-admin/departments');
            }, 1500);
        } else {
            const errorMessages = {
                INVALID_DEPARTMENT_ID: 'Invalid department ID',
                DEPARTMENT_NOT_FOUND: 'Department not found',
                FAILED_TO_DELETE_DEPARTMENT: 'Server failed to delete the department',
                INVALID_SESSION: 'Your session has expired. Please log in again.',
            };

            const error_code = data.error_code as keyof typeof errorMessages;
            const errorMessage = errorMessages[error_code] || 'Failed to delete department';

            playError();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: errorMessage,
                footer: 'Please try again or contact support if the issue persists.',
                showConfirmButton: true,
            });

            // If session expired, redirect to login
            if (error_code === 'INVALID_SESSION') {
                setTimeout(() => {
                    window.location.href = '/login?redirect=' + encodeURIComponent(window.location.pathname);
                }, 1500);
            }
        }
    } catch (error) {
        console.error('Error deleting department:', error);
        playError();
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An unexpected error occurred while deleting the department',
            footer: 'Please try again or contact support if the issue persists.',
            showConfirmButton: true,
        });
    } finally {
        deleting.value = false;
    }
};

onMounted(() => {
    fetchDepartment();
});
</script>
