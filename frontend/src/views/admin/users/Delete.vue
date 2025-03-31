<template>
    <LayoutDashboard>
        <div class="flex items-center mb-6">
            <button @click="router.back()" class="mr-4 text-gray-400 hover:text-white transition-colors">
                <ArrowLeftIcon class="h-5 w-5" />
            </button>
            <h1 class="text-2xl font-bold text-pink-400">Delete User</h1>
        </div>

        <div v-if="loading" class="flex justify-center items-center py-10">
            <LoaderIcon class="h-8 w-8 animate-spin text-pink-400" />
        </div>
        <div v-else-if="!user" class="bg-red-500/20 text-red-400 p-4 rounded-lg mb-6">User not found</div>
        <div v-else class="bg-gray-800 rounded-lg p-6 shadow-md">
            <div class="text-center mb-6">
                <AlertTriangleIcon class="h-16 w-16 text-red-500 mx-auto mb-4" />
                <h2 class="text-xl font-semibold text-white mb-2">Are you sure you want to delete this user?</h2>
                <p class="text-gray-400">
                    This action cannot be undone. All data associated with this user will be permanently removed.
                </p>
            </div>

            <div class="bg-gray-700 rounded-lg p-4 mb-6">
                <div class="flex items-center mb-4">
                    <img
                        :src="user?.avatar || '/assets/images/default-avatar.png'"
                        alt="User Avatar"
                        class="w-12 h-12 rounded-full mr-4 object-cover"
                    />
                    <div>
                        <h3 class="text-lg font-medium text-white">{{ user?.username }}</h3>
                        <p class="text-gray-300">{{ user?.email }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 text-sm">
                    <div>
                        <span class="text-gray-400">Full Name:</span>
                        <span class="text-white ml-2">{{ user?.first_name }} {{ user?.last_name }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400">Role:</span>
                        <span :class="getRoleClass(user?.role_id || '')" class="ml-2 px-2 py-0.5 rounded-full text-xs">
                            {{ getRoleName(user?.role_id || '') }}
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-400">Credits:</span>
                        <span class="text-white ml-2">{{ user?.credits || 0 }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400">Pterodactyl ID:</span>
                        <span class="text-white ml-2">{{ user?.pterodactyl_user_id || 'None' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400">Registered:</span>
                        <span class="text-white ml-2">{{ formatDate(user?.first_seen) }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400">Last Seen:</span>
                        <span class="text-white ml-2">{{ formatDate(user?.last_seen) }}</span>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-4">
                <button
                    @click="router.back()"
                    class="px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-600 transition-colors"
                >
                    Cancel
                </button>
                <button
                    @click="confirmDelete"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors flex items-center"
                    :disabled="deleting"
                >
                    <LoaderIcon v-if="deleting" class="h-4 w-4 mr-2 animate-spin" />
                    <TrashIcon v-else class="h-4 w-4 mr-2" />
                    Delete User
                </button>
            </div>
        </div>
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import LayoutDashboard from '@/components/admin/LayoutDashboard.vue';
import { ArrowLeftIcon, TrashIcon, LoaderIcon, AlertTriangleIcon } from 'lucide-vue-next';
import Users from '@/mythicaldash/admin/Users';
import Swal from 'sweetalert2';
import { useSound } from '@vueuse/sound';
import failedAlertSfx from '@/assets/sounds/error.mp3';
import successAlertSfx from '@/assets/sounds/success.mp3';

// Define interface for user data
interface User {
    id: number;
    uuid: string;
    username: string;
    first_name: string;
    last_name: string;
    email: string;
    avatar: string;
    pterodactyl_user_id: string | null;
    role_id: string;
    credits: string;
    first_seen: string;
    last_seen: string;
}

const router = useRouter();
const route = useRoute();
const loading = ref(true);
const deleting = ref(false);
const { play: playError } = useSound(failedAlertSfx);
const { play: playSuccess } = useSound(successAlertSfx);

const userId = route.params.id as string;
const user = ref<User | null>(null);

// Role mappings for display purposes
const roles: Record<number, { name: string; color: string }> = {
    1: { name: 'User', color: 'bg-blue-500/20 text-blue-400' },
    2: { name: 'VIP', color: 'bg-green-500/20 text-green-400' },
    3: { name: 'Support Buddy', color: 'bg-yellow-500/20 text-yellow-400' },
    4: { name: 'Support', color: 'bg-purple-500/20 text-purple-400' },
    5: { name: 'Support LVL 3', color: 'bg-pink-500/20 text-pink-400' },
    6: { name: 'Support LVL 4', color: 'bg-pink-500/20 text-pink-400' },
    7: { name: 'Admin', color: 'bg-pink-500/20 text-pink-400' },
    8: { name: 'Administrator', color: 'bg-red-500/20 text-red-400' },
};

const getRoleName = (roleId: string): string => {
    const id = parseInt(roleId);
    return roles[id]?.name || 'Unknown';
};

const getRoleClass = (roleId: string): string => {
    const id = parseInt(roleId);
    return roles[id]?.color || '';
};

const formatDate = (dateString?: string): string => {
    if (!dateString) return 'Never';
    const date = new Date(dateString);
    return date.toLocaleString();
};

// Fetch user data
const fetchUser = async (): Promise<void> => {
    loading.value = true;
    try {
        const response = await Users.getUser(userId);
        if (response.success) {
            user.value = response.user as User;
        } else {
            playError();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'User not found',
                showConfirmButton: true,
            }).then(() => {
                router.push('/mc-admin/users');
            });
        }
    } catch (error) {
        console.error('Error fetching user:', error);
        playError();
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to load user details',
            showConfirmButton: true,
        }).then(() => {
            router.push('/mc-admin/users');
        });
    } finally {
        loading.value = false;
    }
};

// Delete the user
const confirmDelete = async (): Promise<void> => {
    // First, show a confirmation dialog
    const result = await Swal.fire({
        title: 'Are you absolutely sure?',
        text: "You won't be able to revert this! All user data will be permanently deleted.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#4b5563',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
    });

    if (result.isConfirmed) {
        deleting.value = true;
        try {
            const response = await Users.deleteUser(userId);

            if (response.success) {
                playSuccess();
                Swal.fire({
                    icon: 'success',
                    title: 'Deleted!',
                    text: 'The user has been deleted successfully.',
                    showConfirmButton: true,
                }).then(() => {
                    router.push('/mc-admin/users');
                });
            } else {
                playError();
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message || 'Failed to delete user',
                    showConfirmButton: true,
                });
            }
        } catch (error) {
            console.error('Error deleting user:', error);
            playError();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An unexpected error occurred',
                showConfirmButton: true,
            });
        } finally {
            deleting.value = false;
        }
    }
};

onMounted(() => {
    fetchUser();
});
</script>
