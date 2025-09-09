<template>
    <LayoutDashboard>
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-pink-400">Role Permissions</h1>
                <p class="text-gray-400 mt-1">{{ roleName }}</p>
            </div>
            <div class="flex space-x-3">
                <button
                    @click="showAddPermissionModal = true"
                    class="bg-gradient-to-r from-pink-500 to-violet-500 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:opacity-80 flex items-center"
                >
                    <PlusIcon class="w-4 h-4 mr-2" />
                    Add Permission
                </button>
                <button
                    @click="goBack()"
                    class="bg-gray-600 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:opacity-80 flex items-center"
                >
                    <ArrowLeftIcon class="w-4 h-4 mr-2" />
                    Back
                </button>
            </div>
        </div>

        <div v-if="loading" class="flex justify-center items-center py-10">
            <LoaderCircle class="h-8 w-8 animate-spin text-pink-400" />
        </div>

        <div v-else>
            <!-- Permissions Table -->
            <div v-if="permissions.length > 0" class="bg-gray-800 rounded-lg overflow-hidden">
                <TableTanstack :data="permissions" :columns="columns" tableName="Permissions" />
            </div>

            <!-- Empty State -->
            <div v-else class="bg-gray-800 rounded-lg p-8 text-center">
                <ShieldIcon class="w-16 h-16 text-gray-600 mx-auto mb-4" />
                <h3 class="text-lg font-medium text-gray-300 mb-2">No Permissions Found</h3>
                <p class="text-gray-400 mb-4">This role doesn't have any permissions assigned yet.</p>
                <button
                    @click="showAddPermissionModal = true"
                    class="bg-gradient-to-r from-pink-500 to-violet-500 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:opacity-80"
                >
                    Add First Permission
                </button>
            </div>
        </div>

        <!-- Enhanced Add Permission Modal -->
        <div
            v-if="showAddPermissionModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        >
            <div class="bg-gray-800 rounded-lg p-6 w-full max-w-4xl mx-4 max-h-[90vh] overflow-hidden flex flex-col">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-white">Add Permission to {{ roleName }}</h3>
                    <button
                        @click="showAddPermissionModal = false"
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
                                                v-model="selectedPermissions"
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
                    <div class="text-sm text-gray-400">{{ selectedPermissions.length }} permission(s) selected</div>
                    <div class="flex space-x-3">
                        <button
                            type="button"
                            @click="showAddPermissionModal = false"
                            class="bg-gray-600 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:opacity-80"
                        >
                            Cancel
                        </button>
                        <button
                            @click="addSelectedPermissions"
                            class="bg-gradient-to-r from-pink-500 to-violet-500 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:opacity-80 flex items-center"
                            :disabled="adding || selectedPermissions.length === 0"
                        >
                            <LoaderCircle v-if="adding" class="w-4 h-4 mr-2 animate-spin" />
                            <PlusIcon v-else class="w-4 h-4 mr-2" />
                            {{
                                adding
                                    ? 'Adding...'
                                    : `Add ${selectedPermissions.length} Permission${selectedPermissions.length !== 1 ? 's' : ''}`
                            }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, onMounted, h, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import LayoutDashboard from '@/components/admin/LayoutDashboard.vue';
import TableTanstack from '@/components/client/ui/Table/TableTanstack.vue';
import {
    PlusIcon,
    ArrowLeftIcon,
    LoaderCircle,
    ShieldIcon,
    EditIcon,
    TrashIcon,
    XIcon,
    SearchIcon,
} from 'lucide-vue-next';
import Permissions from '@/mythicaldash/Permissions';

interface Permission {
    id: number;
    role_id: number;
    permission: string;
    granted: string;
    created_at: string;
    updated_at: string;
}

interface PermissionNode {
    constant: string;
    value: string;
    category: string;
    description: string;
}

const router = useRouter();
const route = useRoute();
const loading = ref(true);
const adding = ref(false);
const showAddPermissionModal = ref(false);
const permissions = ref<Permission[]>([]);
const roleName = ref('');
const searchQuery = ref('');
const selectedPermissions = ref<string[]>([]);

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

// Define columns for TableTanstack
const columns = [
    {
        accessorKey: 'id',
        header: 'ID',
        cell: (info: { getValue: () => number }) => info.getValue(),
    },
    {
        accessorKey: 'permission',
        header: 'Permission',
        cell: (info: { getValue: () => string }) => info.getValue(),
    },
    {
        accessorKey: 'granted',
        header: 'Granted',
        cell: (info: { getValue: () => string }) => {
            const granted = info.getValue();
            return h(
                'span',
                {
                    class:
                        granted === 'true'
                            ? 'px-2 py-1 rounded-full text-xs font-medium bg-green-500/20 text-green-400'
                            : 'px-2 py-1 rounded-full text-xs font-medium bg-red-500/20 text-red-400',
                },
                granted === 'true' ? 'Yes' : 'No',
            );
        },
    },
    {
        id: 'actions',
        header: 'Actions',
        cell: (info: { row: { original: Permission } }) => {
            const permission = info.row.original;
            return h('div', { class: 'flex space-x-2' }, [
                h(
                    'button',
                    {
                        class: 'p-1 text-gray-400 hover:text-pink-400 transition-colors',
                        title: 'Edit',
                        onClick: () => editPermission(permission),
                    },
                    [h(EditIcon, { class: 'w-4 h-4' })],
                ),
                h(
                    'button',
                    {
                        class: 'p-1 text-gray-400 hover:text-red-400 transition-colors',
                        title: 'Delete',
                        onClick: () => deletePermission(permission),
                    },
                    [h(TrashIcon, { class: 'w-4 h-4' })],
                ),
            ]);
        },
    },
];

// Fetch role name and permissions
const fetchRoleData = async () => {
    loading.value = true;
    try {
        // Fetch role details
        const roleResponse = await fetch(`/api/admin/roles/${route.params.id}`, {
            method: 'GET',
            headers: {
                Accept: 'application/json',
            },
        });

        if (roleResponse.ok) {
            const roleData = await roleResponse.json();
            if (roleData.success) {
                roleName.value = roleData.role.real_name || roleData.role.name;
            }
        }

        // Fetch permissions for this role
        const permissionsResponse = await fetch(`/api/admin/roles/${route.params.id}/permissions`, {
            method: 'GET',
            headers: {
                Accept: 'application/json',
            },
        });

        if (permissionsResponse.ok) {
            const permissionsData = await permissionsResponse.json();
            if (permissionsData.success) {
                permissions.value = permissionsData.permissions;
            }
        }
    } catch (error) {
        console.error('Error fetching role data:', error);
    } finally {
        loading.value = false;
    }
};

const addSelectedPermissions = async () => {
    if (selectedPermissions.value.length === 0) return;

    adding.value = true;
    try {
        // Add each selected permission
        for (const permissionValue of selectedPermissions.value) {
            const formData = new FormData();
            formData.append('permission', permissionValue);
            formData.append('granted', 'true');

            const response = await fetch(`/api/admin/roles/${route.params.id}/permissions/create`, {
                method: 'POST',
                body: formData,
            });

            if (!response.ok) {
                console.error(`Failed to add permission: ${permissionValue}`);
            }
        }

        showAddPermissionModal.value = false;
        selectedPermissions.value = [];
        searchQuery.value = '';
        fetchRoleData(); // Refresh the list
    } catch (error) {
        console.error('Error adding permissions:', error);
    } finally {
        adding.value = false;
    }
};

const editPermission = (permission: Permission) => {
    router.push(`/mc-admin/permissions/${permission.id}/edit`);
};

const deletePermission = async (permission: Permission) => {
    if (!confirm('Are you sure you want to delete this permission?')) {
        return;
    }

    try {
        const formData = new FormData();
        formData.append('id', permission.id.toString());

        const response = await fetch('/api/admin/permissions/delete', {
            method: 'POST',
            body: formData,
        });

        if (response.ok) {
            const data = await response.json();
            if (data.success) {
                fetchRoleData(); // Refresh the list
            }
        }
    } catch (error) {
        console.error('Error deleting permission:', error);
    }
};

const goBack = () => {
    router.push('/mc-admin/roles');
};

onMounted(() => {
    fetchRoleData();
});
</script>

<style scoped>
.bg-gray-750 {
    background-color: #374151;
}
</style>
