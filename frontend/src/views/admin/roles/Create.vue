<template>
    <LayoutDashboard>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-400">Create Role</h1>
            <button
                @click="goBack()"
                class="bg-gray-600 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:opacity-80 flex items-center"
            >
                <ArrowLeftIcon class="w-4 h-4 mr-2" />
                Back
            </button>
        </div>

        <div class="bg-gray-800 rounded-lg p-6">
            <form @submit.prevent="createRole" class="space-y-6">
                <!-- Basic Role Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-300 mb-2">Name</label>
                        <input
                            type="text"
                            id="name"
                            v-model="form.name"
                            class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-pink-500"
                            placeholder="e.g., admin"
                            required
                        />
                    </div>

                    <div>
                        <label for="real_name" class="block text-sm font-medium text-gray-300 mb-2">Display Name</label>
                        <input
                            type="text"
                            id="real_name"
                            v-model="form.real_name"
                            class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-pink-500"
                            placeholder="e.g., Administrator"
                            required
                        />
                    </div>
                </div>

                <div>
                    <label for="color" class="block text-sm font-medium text-gray-300 mb-2">Color</label>
                    <div class="flex items-center gap-4">
                        <input
                            type="color"
                            id="color"
                            v-model="form.color"
                            class="w-16 h-10 bg-gray-700 border border-gray-600 rounded-lg cursor-pointer"
                            required
                        />
                        <input
                            type="text"
                            v-model="form.color"
                            class="flex-1 px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-pink-500"
                            placeholder="#000000"
                            required
                        />
                    </div>
                </div>

                <!-- Enhanced Permissions Section -->
                <div class="border-t border-gray-700 pt-6">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h3 class="text-lg font-medium text-white">Permissions</h3>
                            <p class="text-sm text-gray-400 mt-1">Select permissions to assign to this role</p>
                        </div>
                        <button
                            type="button"
                            @click="showPermissionsModal = true"
                            class="bg-gradient-to-r from-pink-500 to-violet-500 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:opacity-80 flex items-center"
                        >
                            <PlusIcon class="w-4 h-4 mr-2" />
                            Add Permissions
                        </button>
                    </div>

                    <!-- Selected Permissions Display -->
                    <div
                        v-if="selectedPermissions.length === 0"
                        class="text-center py-8 text-gray-400 bg-gray-750 rounded-lg"
                    >
                        <ShieldIcon class="w-12 h-12 mx-auto mb-3 text-gray-600" />
                        <p class="text-sm">No permissions selected yet.</p>
                        <p class="text-xs mt-1">Click "Add Permissions" to select permissions for this role.</p>
                    </div>

                    <div v-else class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-400">
                                {{ selectedPermissions.length }} permission(s) selected
                            </span>
                            <button
                                type="button"
                                @click="clearAllPermissions"
                                class="text-red-400 hover:text-red-300 text-sm transition-colors"
                            >
                                Clear All
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            <div
                                v-for="(permission, index) in selectedPermissions"
                                :key="index"
                                class="flex items-center justify-between p-3 bg-gray-750 rounded-lg border border-gray-700"
                            >
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-medium text-white truncate">
                                        {{ getPermissionDisplayName(permission) }}
                                    </div>
                                    <div class="text-xs text-gray-400 truncate">
                                        {{ permission }}
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    @click="removePermission(index)"
                                    class="ml-2 text-red-400 hover:text-red-300 transition-colors flex-shrink-0"
                                >
                                    <XIcon class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-4 border-t border-gray-700">
                    <button
                        type="submit"
                        class="bg-gradient-to-r from-pink-500 to-violet-500 text-white px-6 py-2 rounded-lg transition-all duration-200 hover:opacity-80 flex items-center"
                        :disabled="loading"
                    >
                        <LoaderCircle v-if="loading" class="w-4 h-4 mr-2 animate-spin" />
                        <PlusIcon v-else class="w-4 h-4 mr-2" />
                        {{ loading ? 'Creating...' : 'Create Role' }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Enhanced Permissions Selection Modal -->
        <div
            v-if="showPermissionsModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        >
            <div class="bg-gray-800 rounded-lg p-6 w-full max-w-4xl mx-4 max-h-[90vh] overflow-hidden flex flex-col">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-white">Select Permissions</h3>
                    <button
                        @click="showPermissionsModal = false"
                        class="text-gray-400 hover:text-white transition-colors"
                    >
                        <XIcon class="w-5 h-5" />
                    </button>
                </div>

                <!-- Search Bar -->
                <div class="mb-4">
                    <div class="relative">
                        <SearchIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" />
                        <input
                            type="text"
                            v-model="searchQuery"
                            class="w-full pl-10 pr-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-pink-500"
                            placeholder="Search permissions..."
                        />
                    </div>
                </div>

                <!-- Permissions List -->
                <div class="flex-1 overflow-y-auto">
                    <div class="space-y-2">
                        <div
                            v-for="category in filteredPermissionCategories"
                            :key="category.name"
                            class="border border-gray-700 rounded-lg overflow-hidden"
                        >
                            <div class="bg-gray-700 px-4 py-2">
                                <h4 class="text-sm font-medium text-gray-300">{{ category.name }}</h4>
                            </div>
                            <div class="p-2 space-y-1">
                                <div
                                    v-for="permission in category.permissions"
                                    :key="permission.value"
                                    class="flex items-center justify-between p-3 bg-gray-750 rounded-lg hover:bg-gray-700 transition-colors"
                                >
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-3">
                                            <input
                                                type="checkbox"
                                                :id="permission.value"
                                                :value="permission.value"
                                                v-model="tempSelectedPermissions"
                                                class="w-4 h-4 text-pink-500 bg-gray-700 border-gray-600 rounded focus:ring-pink-500 focus:ring-2"
                                            />
                                            <div>
                                                <label
                                                    :for="permission.value"
                                                    class="text-sm font-medium text-white cursor-pointer"
                                                >
                                                    {{ permission.constant }}
                                                </label>
                                                <p class="text-xs text-gray-400 mt-1">{{ permission.description }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-xs text-gray-500 font-mono">
                                        {{ permission.value }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="flex justify-between items-center pt-4 border-t border-gray-700">
                    <div class="text-sm text-gray-400">{{ tempSelectedPermissions.length }} permission(s) selected</div>
                    <div class="flex space-x-3">
                        <button
                            type="button"
                            @click="showPermissionsModal = false"
                            class="bg-gray-600 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:opacity-80"
                        >
                            Cancel
                        </button>
                        <button
                            @click="confirmPermissionsSelection"
                            class="bg-gradient-to-r from-pink-500 to-violet-500 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:opacity-80 flex items-center"
                        >
                            <CheckIcon class="w-4 h-4 mr-2" />
                            Confirm Selection ({{ tempSelectedPermissions.length }})
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import LayoutDashboard from '@/components/admin/LayoutDashboard.vue';
import { PlusIcon, ArrowLeftIcon, LoaderCircle, XIcon, SearchIcon, ShieldIcon, CheckIcon } from 'lucide-vue-next';
import Permissions from '@/mythicaldash/Permissions';

interface PermissionNode {
    constant: string;
    value: string;
    category: string;
    description: string;
}

const router = useRouter();
const loading = ref(false);
const showPermissionsModal = ref(false);
const searchQuery = ref('');
const selectedPermissions = ref<string[]>([]);
const tempSelectedPermissions = ref<string[]>([]);

const form = ref({
    name: '',
    real_name: '',
    color: '#000000',
});

// Get all available permissions from Permissions.ts
const allPermissions = Permissions.getAll();

// Group permissions by category
const permissionCategories = computed(() => {
    const categories: { [key: string]: PermissionNode[] } = {};

    allPermissions.forEach((permission) => {
        if (permission.category) {
            if (!categories[permission.category]) {
                categories[permission.category] = [];
            }
            const categoryArray = categories[permission.category];
            if (categoryArray) {
                categoryArray.push(permission);
            }
        }
    });

    return Object.entries(categories).map(([name, permissions]) => ({
        name,
        permissions,
    }));
});

// Filter permissions based on search query
const filteredPermissionCategories = computed(() => {
    if (!searchQuery.value.trim()) {
        return permissionCategories.value;
    }

    const query = searchQuery.value.toLowerCase();
    return permissionCategories.value
        .map((category) => ({
            ...category,
            permissions: category.permissions.filter(
                (permission) =>
                    permission.constant.toLowerCase().includes(query) ||
                    permission.value.toLowerCase().includes(query) ||
                    permission.description.toLowerCase().includes(query) ||
                    permission.category.toLowerCase().includes(query),
            ),
        }))
        .filter((category) => category.permissions.length > 0);
});

// Get display name for permission
const getPermissionDisplayName = (permissionValue: string) => {
    const permission = allPermissions.find((p) => p.value === permissionValue);
    return permission ? permission.constant : permissionValue;
};

const confirmPermissionsSelection = () => {
    selectedPermissions.value = [...tempSelectedPermissions.value];
    showPermissionsModal.value = false;
    searchQuery.value = '';
};

const removePermission = (index: number) => {
    selectedPermissions.value.splice(index, 1);
};

const clearAllPermissions = () => {
    selectedPermissions.value = [];
};

const createRole = async () => {
    loading.value = true;
    try {
        const formData = new FormData();
        formData.append('name', form.value.name);
        formData.append('real_name', form.value.real_name);
        formData.append('color', form.value.color);

        const response = await fetch('/api/admin/roles/create', {
            method: 'POST',
            body: formData,
        });

        if (!response.ok) {
            const data = await response.json();
            throw new Error(data.message || 'Failed to create role');
        }

        const data = await response.json();

        if (data.success) {
            // If there are permissions, create them for the new role
            if (selectedPermissions.value.length > 0) {
                // Get the role ID from the response and create permissions
                const roleId = data.role?.id;
                if (roleId) {
                    await createPermissionsForRole(roleId);
                }
            }
            router.push('/mc-admin/roles');
        } else {
            console.error('Failed to create role:', data.message);
        }
    } catch (error) {
        console.error('Error creating role:', error);
    } finally {
        loading.value = false;
    }
};

const createPermissionsForRole = async (roleId: number) => {
    try {
        // Add each selected permission
        for (const permissionValue of selectedPermissions.value) {
            const formData = new FormData();
            formData.append('permission', permissionValue);
            formData.append('granted', 'true');

            const response = await fetch(`/api/admin/roles/${roleId}/permissions/create`, {
                method: 'POST',
                body: formData,
            });

            if (!response.ok) {
                console.error(`Failed to add permission: ${permissionValue}`);
            }
        }
    } catch (error) {
        console.error('Error creating permissions for role:', error);
    }
};

const goBack = () => {
    router.push('/mc-admin/roles');
};
</script>

<style scoped>
.bg-gray-750 {
    background-color: #374151;
}
</style>
