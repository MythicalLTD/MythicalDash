<template>
    <LayoutDashboard>
        <div v-if="loading" class="flex justify-center items-center h-64">
            <LoaderCircle class="h-10 w-10 animate-spin text-pink-400" />
        </div>
        <template v-else-if="department">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-pink-400">Edit Department: {{ department.name }}</h1>
                <button
                    @click="router.push('/mc-admin/departments')"
                    class="bg-gray-700 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:bg-gray-600 flex items-center"
                >
                    <ArrowLeftIcon class="w-4 h-4 mr-2" />
                    Back to Departments
                </button>
            </div>

            <div class="bg-gray-800/50 backdrop-blur-md rounded-lg p-6">
                <form @submit.prevent="saveDepartment" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-400 mb-1">Name</label>
                            <input
                                id="name"
                                v-model="departmentForm.name"
                                type="text"
                                required
                                class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                                placeholder="e.g. Billing Support"
                            />
                            <p class="text-xs text-gray-400 mt-1">The department name shown to users</p>
                        </div>

                        <div>
                            <label for="enabled" class="block text-sm font-medium text-gray-400 mb-1">Status</label>
                            <select
                                id="enabled"
                                v-model="departmentForm.enabled"
                                class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                            >
                                <option value="true">Enabled</option>
                                <option value="false">Disabled</option>
                            </select>
                            <p class="text-xs text-gray-400 mt-1">Whether this department is active</p>
                        </div>

                        <div class="md:col-span-2">
                            <label for="description" class="block text-sm font-medium text-gray-400 mb-1"
                                >Description</label
                            >
                            <textarea
                                id="description"
                                v-model="departmentForm.description"
                                required
                                rows="3"
                                class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                                placeholder="e.g. Handle billing and subscription related inquiries"
                            ></textarea>
                            <p class="text-xs text-gray-400 mt-1">A description of the department's purpose</p>
                        </div>

                        <div>
                            <label for="time_open" class="block text-sm font-medium text-gray-400 mb-1"
                                >Opening Time</label
                            >
                            <input
                                id="time_open"
                                v-model="departmentForm.open"
                                type="time"
                                required
                                class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                            />
                            <p class="text-xs text-gray-400 mt-1">When this department starts operating (24h format)</p>
                        </div>

                        <div>
                            <label for="time_close" class="block text-sm font-medium text-gray-400 mb-1"
                                >Closing Time</label
                            >
                            <input
                                id="time_close"
                                v-model="departmentForm.close"
                                type="time"
                                required
                                class="bg-gray-800/30 border border-gray-700 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500"
                            />
                            <p class="text-xs text-gray-400 mt-1">When this department stops operating (24h format)</p>
                        </div>
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
                            :disabled="saving"
                            class="px-4 py-2 bg-gradient-to-r from-pink-500 to-violet-500 rounded-lg text-white hover:opacity-90 transition-colors flex items-center"
                        >
                            <LoaderIcon v-if="saving" class="animate-spin w-4 h-4 mr-2" />
                            <SaveIcon v-else class="w-4 h-4 mr-2" />
                            Save Changes
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
            <p class="text-gray-400 mt-2">The department you're trying to edit doesn't exist or has been deleted.</p>
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
import { ArrowLeftIcon, SaveIcon, LoaderIcon, LoaderCircle, AlertCircleIcon } from 'lucide-vue-next';
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
const saving = ref<boolean>(false);
const department = ref<Department | null>(null);
const { play: playError } = useSound(failedAlertSfx);
const { play: playSuccess } = useSound(successAlertSfx);

// Form state
const departmentForm = ref({
    name: '',
    description: '',
    open: '',
    close: '',
    enabled: 'true',
});

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
                departmentForm.value = {
                    name: foundDepartment.name,
                    description: foundDepartment.description,
                    open: foundDepartment.time_open,
                    close: foundDepartment.time_close,
                    enabled: foundDepartment.enabled,
                };
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

const saveDepartment = async () => {
    saving.value = true;

    try {
        const formData = new FormData();
        formData.append('name', departmentForm.value.name);
        formData.append('description', departmentForm.value.description);
        formData.append('open', departmentForm.value.open);
        formData.append('close', departmentForm.value.close);
        formData.append('enabled', departmentForm.value.enabled);

        const response = await fetch(`/api/admin/ticket/departments/${departmentId}/update`, {
            method: 'POST',
            body: formData,
        });

        const data = await response.json();

        if (data.success) {
            playSuccess();
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Department updated successfully',
                showConfirmButton: true,
            });

            // Navigate back to departments list after a short delay
            setTimeout(() => {
                router.push('/mc-admin/departments');
            }, 1500);
        } else {
            const errorMessages = {
                MISSING_REQUIRED_FIELDS: 'Please fill in all required fields',
                INVALID_ENABLED_VALUE: 'Invalid status selected',
                FAILED_TO_UPDATE_DEPARTMENT: 'Server failed to update the department',
                INVALID_SESSION: 'Your session has expired. Please log in again.',
                INVALID_DEPARTMENT_ID: 'Invalid department ID',
                DEPARTMENT_NOT_FOUND: 'Department not found',
            };

            const error_code = data.error_code as keyof typeof errorMessages;
            const errorMessage = errorMessages[error_code] || 'Failed to update department';

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
        console.error('Error updating department:', error);
        playError();
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An unexpected error occurred while updating the department',
            footer: 'Please try again or contact support if the issue persists.',
            showConfirmButton: true,
        });
    } finally {
        saving.value = false;
    }
};

onMounted(() => {
    fetchDepartment();
});
</script>
