<template>
    <LayoutDashboard>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-400">Create Department</h1>
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
                        <label for="time_open" class="block text-sm font-medium text-gray-400 mb-1">Opening Time</label>
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
                        :disabled="loading"
                        class="px-4 py-2 bg-gradient-to-r from-pink-500 to-violet-500 rounded-lg text-white hover:opacity-90 transition-colors flex items-center"
                    >
                        <LoaderIcon v-if="loading" class="animate-spin w-4 h-4 mr-2" />
                        <SaveIcon v-else class="w-4 h-4 mr-2" />
                        Create Department
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
import Swal from 'sweetalert2';
import { useSound } from '@vueuse/sound';
import failedAlertSfx from '@/assets/sounds/error.mp3';
import successAlertSfx from '@/assets/sounds/success.mp3';

const router = useRouter();
const loading = ref(false);
const { play: playError } = useSound(failedAlertSfx);
const { play: playSuccess } = useSound(successAlertSfx);

// Form state
const departmentForm = ref({
    name: '',
    description: '',
    open: '09:00',
    close: '17:00',
    enabled: 'true',
});

const saveDepartment = async () => {
    loading.value = true;

    try {
        const formData = new FormData();
        formData.append('name', departmentForm.value.name);
        formData.append('description', departmentForm.value.description);
        formData.append('open', departmentForm.value.open);
        formData.append('close', departmentForm.value.close);
        formData.append('enabled', departmentForm.value.enabled);

        const response = await fetch('/api/admin/ticket/departments/create', {
            method: 'POST',
            body: formData,
        });

        const data = await response.json();

        if (data.success) {
            playSuccess();
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Department created successfully',
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
                FAILED_TO_CREATE_DEPARTMENT: 'Server failed to create the department',
                INVALID_SESSION: 'Your session has expired. Please log in again.',
            };

            const error_code = data.error_code as keyof typeof errorMessages;
            const errorMessage = errorMessages[error_code] || 'Failed to create department';

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
        console.error('Error creating department:', error);
        playError();
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An unexpected error occurred while creating the department',
            footer: 'Please try again or contact support if the issue persists.',
            showConfirmButton: true,
        });
    } finally {
        loading.value = false;
    }
};
</script>
