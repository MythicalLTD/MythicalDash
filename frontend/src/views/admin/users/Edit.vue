<script setup lang="ts">
import { ref, reactive, onMounted, h, defineComponent } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import LayoutDashboard from '@/components/admin/LayoutDashboard.vue';
import {
    ArrowLeftIcon,
    SaveIcon,
    LoaderIcon,
    TrashIcon,
    UserIcon,
    ServerIcon,
    SettingsIcon,
    DatabaseIcon,
    ClockIcon,
    MailIcon as EnvelopeIcon,
    ExternalLink as ExternalLinkIcon,
} from 'lucide-vue-next';
import Users from '@/mythicaldash/admin/Users';
import Swal from 'sweetalert2';
import { useSound } from '@vueuse/sound';
import failedAlertSfx from '@/assets/sounds/error.mp3';
import { useSettingsStore } from '@/stores/settings';
const Settings = useSettingsStore();
import successAlertSfx from '@/assets/sounds/success.mp3';

// Utility function to open URLs safely in a new tab
function openExternalLink(url: string): void {
    // Using the global window object explicitly
    const globalWindow = window as typeof globalThis;
    const newWindow = globalWindow.open(url, '_blank');
    if (newWindow) newWindow.opener = null;
}

// Define interfaces for user data
interface User {
    id: number;
    uuid: string;
    username: string;
    first_name: string;
    last_name: string;
    email: string;
    avatar: string;
    credits: string;
    pterodactyl_user_id: string;
    token: string;
    role: string;
    first_ip: string;
    last_ip: string;
    banned: string;
    support_pin: string;
    verified: string;
    two_fa_enabled: string;
    two_fa_key: string | null;
    two_fa_blocked: string;
    deleted: string;
    last_seen: string;
    first_seen: string;
    background: string;
    disk_limit: string;
    memory_limit: string;
    cpu_limit: string;
    server_limit: string;
    backup_limit: string;
    database_limit: string;
    allocation_limit: string;
    minutes_afk: string;
    last_seen_afk: string;
    [key: string]: string | number | null; // Index signature for dynamic access
}

// Interface for form data
interface FormData {
    username: string;
    email: string;
    password: string;
    first_name: string;
    last_name: string;
    avatar: string;
    credits: number;
    pterodactyl_user_id: number;
    role: number;
    banned: string;
    support_pin: string;
    verified: string;
    two_fa_enabled: string;
    two_fa_blocked: string;
    background: string;
    disk_limit: number;
    memory_limit: number;
    cpu_limit: number;
    server_limit: number;
    backup_limit: number;
    database_limit: number;
    allocation_limit: number;
}
interface ActivityLog {
    id: number;
    user: string;
    action: string;
    ip_address: string;
    deleted: string;
    locked: string;
    date: string;
    context: string;
}

// Interface for emails
interface Email {
    id: number;
    subject: string;
    body: string;
    from: string;
    user: string;
    read: number;
    deleted: string;
    locked: string;
    date: string;
    showBody: boolean;
}

// Interface for tab definition
interface Tab {
    id: string;
    name: string;
    icon: unknown; // This is a component reference
}

// Interface for role option
interface RoleOption {
    value: number | string;
    label: string;
}

// Form Field component
const FormField = defineComponent({
    props: {
        label: {
            type: String,
            required: true,
        },
        modelValue: {
            type: [String, Number],
            required: true,
        },
        type: {
            type: String,
            default: 'text',
        },
        saving: {
            type: Boolean,
            default: false,
        },
        disabled: {
            type: Boolean,
            default: false,
        },
    },
    emits: ['update:modelValue', 'save'],
    setup(props, { emit }) {
        return () =>
            h('div', { class: 'space-y-1' }, [
                h('label', { class: 'block text-sm font-medium text-gray-300' }, props.label),
                h('div', { class: 'relative' }, [
                    h('input', {
                        type: props.type,
                        value: props.modelValue,
                        disabled: props.disabled,
                        onInput: (e: Event) => emit('update:modelValue', (e.target as HTMLInputElement).value),
                        class: 'w-full bg-gray-700 border border-gray-600 rounded-md py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-pink-500',
                    }),
                    !props.disabled &&
                        h(
                            'button',
                            {
                                type: 'button',
                                disabled: props.saving,
                                onClick: () => emit('save'),
                                class: 'absolute right-2 top-2 text-gray-400 hover:text-pink-400',
                            },
                            [
                                props.saving
                                    ? h(LoaderIcon, { class: 'h-4 w-4 animate-spin' })
                                    : h(SaveIcon, { class: 'h-4 w-4' }),
                            ],
                        ),
                ]),
            ]);
    },
});

// Form Select component
const FormSelect = defineComponent({
    props: {
        label: {
            type: String,
            required: true,
        },
        modelValue: {
            type: [String, Number],
            required: true,
        },
        options: {
            type: Array as () => RoleOption[],
            required: true,
        },
        saving: {
            type: Boolean,
            default: false,
        },
        disabled: {
            type: Boolean,
            default: false,
        },
    },
    emits: ['update:modelValue', 'save'],
    setup(props, { emit }) {
        return () =>
            h('div', { class: 'space-y-1' }, [
                h('label', { class: 'block text-sm font-medium text-gray-300' }, props.label),
                h('div', { class: 'relative' }, [
                    h(
                        'select',
                        {
                            value: props.modelValue,
                            disabled: props.disabled,
                            onChange: (e: Event) => emit('update:modelValue', (e.target as HTMLSelectElement).value),
                            class: 'w-full bg-gray-700 border border-gray-600 rounded-md py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-pink-500',
                        },
                        props.options.map((option: RoleOption) => h('option', { value: option.value }, option.label)),
                    ),
                    !props.disabled &&
                        h(
                            'button',
                            {
                                type: 'button',
                                disabled: props.saving,
                                onClick: () => emit('save'),
                                class: 'absolute right-2 top-2 text-gray-400 hover:text-pink-400',
                            },
                            [
                                props.saving
                                    ? h(LoaderIcon, { class: 'h-4 w-4 animate-spin' })
                                    : h(SaveIcon, { class: 'h-4 w-4' }),
                            ],
                        ),
                ]),
            ]);
    },
});

const router = useRouter();
const route = useRoute();
const loading = ref(true);
const { play: playError } = useSound(failedAlertSfx);
const { play: playSuccess } = useSound(successAlertSfx);

const userId = route.params.id as string;
const user = ref<User>({} as User);
const activityLogs = ref<ActivityLog[]>([]);
const emails = ref<Email[]>([]);
const activeTab = ref('basic');

// Tabs for different categories
const tabs: Tab[] = [
    { id: 'basic', name: 'Basic Info', icon: UserIcon },
    { id: 'resources', name: 'Resource Limits', icon: ServerIcon },
    { id: 'account', name: 'Account Settings', icon: SettingsIcon },
    { id: 'system', name: 'System Info', icon: DatabaseIcon },
    { id: 'activity', name: 'Activity Logs', icon: ClockIcon },
    { id: 'emails', name: 'Emails', icon: EnvelopeIcon },
];

// Role options for dropdown
const roleOptions: RoleOption[] = [
    { value: 1, label: 'User' },
    { value: 2, label: 'VIP' },
    { value: 3, label: 'Support Buddy' },
    { value: 4, label: 'Support' },
    { value: 5, label: 'Support LVL 3' },
    { value: 6, label: 'Support LVL 4' },
    { value: 7, label: 'Admin' },
    { value: 8, label: 'Administrator' },
];

// Form data
const formData = ref<FormData>({
    username: '',
    email: '',
    password: '',
    first_name: '',
    last_name: '',
    avatar: '',
    credits: 0,
    pterodactyl_user_id: 0,
    role: 1,
    banned: 'NO',
    support_pin: '',
    verified: 'true',
    two_fa_enabled: 'false',
    two_fa_blocked: 'false',
    background: '',
    disk_limit: 0,
    memory_limit: 0,
    cpu_limit: 0,
    server_limit: 0,
    backup_limit: 0,
    database_limit: 0,
    allocation_limit: 0,
});

// Track saving state for each field
const saving = reactive<Record<string, boolean>>({});

// Role helpers
const getRoleName = (roleId: string): string => {
    const id = parseInt(roleId);
    const role = roleOptions.find((r) => r.value === id);
    return role ? role.label : 'Unknown';
};

const getRoleClass = (roleId: string): string => {
    const id = parseInt(roleId);
    switch (id) {
        case 1:
            return 'bg-blue-500/20 text-blue-400';
        case 2:
            return 'bg-green-500/20 text-green-400';
        case 3:
            return 'bg-yellow-500/20 text-yellow-400';
        case 4:
            return 'bg-purple-500/20 text-purple-400';
        case 5:
        case 6:
        case 7:
            return 'bg-pink-500/20 text-pink-400';
        case 8:
            return 'bg-red-500/20 text-red-400';
        default:
            return 'bg-gray-500/20 text-gray-400';
    }
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
            activityLogs.value = response.activity || [];
            emails.value = response.mails || [];

            // Populate form data
            formData.value = {
                username: user.value.username || '',
                email: user.value.email || '',
                password: '',
                first_name: user.value.first_name || '',
                last_name: user.value.last_name || '',
                avatar: user.value.avatar || '',
                credits: parseInt(user.value.credits) || 0,
                pterodactyl_user_id: parseInt(user.value.pterodactyl_user_id) || 0,
                role: parseInt(user.value.role) || 1,
                banned: user.value.banned || 'NO',
                support_pin: user.value.support_pin || '',
                verified: user.value.verified || 'true',
                two_fa_enabled: user.value.two_fa_enabled || 'false',
                two_fa_blocked: user.value.two_fa_blocked || 'false',
                background: user.value.background || '',
                disk_limit: parseInt(user.value.disk_limit) || 0,
                memory_limit: parseInt(user.value.memory_limit) || 0,
                cpu_limit: parseInt(user.value.cpu_limit) || 0,
                server_limit: parseInt(user.value.server_limit) || 0,
                backup_limit: parseInt(user.value.backup_limit) || 0,
                database_limit: parseInt(user.value.database_limit) || 0,
                allocation_limit: parseInt(user.value.allocation_limit) || 0,
            };
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

// Save a specific field
const saveField = async (column: string, value: string | number, encrypted: boolean = false): Promise<void> => {
    saving[column] = true;

    try {
        // Convert number to string for API
        const stringValue = value.toString();

        const response = await Users.updateUser(userId, column, stringValue, encrypted);

        if (response.success) {
            playSuccess();
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Field updated successfully',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
            });

            // Update the user object with the new value
            user.value[column] = value;

            // Clear password field after successful update
            if (column === 'password') {
                formData.value.password = '';
            }
        } else {
            playError();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: response.message || 'Failed to update field',
                showConfirmButton: true,
            });
        }
    } catch (error) {
        console.error(`Error updating ${column}:`, error);
        playError();
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An unexpected error occurred',
            showConfirmButton: true,
        });
    } finally {
        saving[column] = false;
    }
};

onMounted(() => {
    fetchUser();
});
</script>
<template>
    <LayoutDashboard>
        <div class="flex items-center mb-6">
            <button @click="router.back()" class="mr-4 text-gray-400 hover:text-white transition-colors">
                <ArrowLeftIcon class="h-5 w-5" />
            </button>
            <h1 class="text-2xl font-bold text-pink-400">Edit User</h1>
        </div>

        <div v-if="loading" class="flex justify-center items-center py-10">
            <LoaderIcon class="h-8 w-8 animate-spin text-pink-400" />
        </div>
        <div v-else class="space-y-6">
            <!-- User Profile Header -->
            <div class="bg-gray-800 rounded-lg shadow-md overflow-hidden">
                <div class="bg-gradient-to-r from-pink-500/10 to-blue-500/10 p-6 border-b border-gray-700">
                    <div class="flex flex-col md:flex-row items-start md:items-center gap-6">
                        <div class="relative">
                            <img
                                :src="user.avatar || '/assets/images/default-avatar.png'"
                                alt="User Avatar"
                                class="w-20 h-20 md:w-24 md:h-24 rounded-full object-cover border-4 border-gray-700 shadow-lg"
                            />
                            <span
                                :class="[
                                    'absolute -bottom-1 -right-1 w-6 h-6 rounded-full flex items-center justify-center shadow-md',
                                    user.banned === 'YES'
                                        ? 'bg-red-500 text-white'
                                        : user.verified === 'true'
                                          ? 'bg-green-500 text-white'
                                          : 'bg-yellow-500 text-gray-800',
                                ]"
                            >
                                <span v-if="user.banned === 'YES'" class="text-xs">!</span>
                                <span v-else-if="user.verified === 'true'" class="text-xs">✓</span>
                                <span v-else class="text-xs">?</span>
                            </span>
                        </div>

                        <div class="flex-1">
                            <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-4">
                                <h2 class="text-2xl font-bold text-white">{{ user.username }}</h2>
                                <span
                                    :class="getRoleClass(user.role)"
                                    class="px-3 py-1 rounded-full text-xs inline-flex items-center w-fit"
                                >
                                    {{ getRoleName(user.role) }}
                                </span>
                            </div>

                            <div class="mt-2 text-gray-300">{{ user.email }}</div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-4">
                                <div class="flex items-center gap-2 text-gray-400 text-sm">
                                    <ClockIcon class="h-4 w-4 text-gray-500" />
                                    <span>Last active: {{ formatDate(user.last_seen) }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-gray-400 text-sm">
                                    <UserIcon class="h-4 w-4 text-gray-500" />
                                    <span>{{ user.first_name }} {{ user.last_name }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-gray-400 text-sm">
                                    <DatabaseIcon class="h-4 w-4 text-gray-500" />
                                    <span>Credits: {{ user.credits }}</span>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-2 mt-4">
                                <div v-if="user.pterodactyl_user_id" class="flex">
                                    <button
                                        @click="
                                            openExternalLink(
                                                Settings.getSetting('pterodactyl_base_url') +
                                                    `/admin/users/view/${user.pterodactyl_user_id}`,
                                            )
                                        "
                                        class="flex items-center gap-1 px-3 py-1 bg-blue-500/20 text-blue-400 rounded-lg hover:bg-blue-500/30 transition-colors text-sm"
                                    >
                                        <ExternalLinkIcon class="h-3.5 w-3.5" />
                                        Pterodactyl Account
                                    </button>
                                </div>
                                <div class="flex">
                                    <button
                                        @click="router.push('/mc-admin/users')"
                                        class="flex items-center gap-1 px-3 py-1 bg-gray-700 text-gray-300 rounded-lg hover:bg-gray-600 transition-colors text-sm"
                                    >
                                        <ArrowLeftIcon class="h-3.5 w-3.5" />
                                        Back to Users
                                    </button>
                                </div>
                                <div class="flex">
                                    <button
                                        @click="router.push(`/mc-admin/users/${userId}/delete`)"
                                        class="flex items-center gap-1 px-3 py-1 bg-red-500/20 text-red-400 rounded-lg hover:bg-red-500/30 transition-colors text-sm"
                                    >
                                        <TrashIcon class="h-3.5 w-3.5" />
                                        Delete User
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs for different categories -->
            <div class="bg-gray-800 rounded-lg shadow-md overflow-hidden">
                <div class="flex border-b border-gray-700">
                    <button
                        v-for="tab in tabs"
                        :key="tab.id"
                        @click="activeTab = tab.id"
                        :class="[
                            'px-4 py-3 text-sm font-medium transition-colors',
                            activeTab === tab.id
                                ? 'text-pink-400 border-b-2 border-pink-400'
                                : 'text-gray-400 hover:text-white',
                        ]"
                    >
                        <component :is="tab.icon" class="h-4 w-4 inline-block mr-2" />
                        {{ tab.name }}
                    </button>
                </div>

                <div class="p-6">
                    <!-- Basic Information -->
                    <div v-if="activeTab === 'basic'" class="space-y-6">
                        <h3 class="text-lg font-medium text-white">Basic Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <FormField
                                label="Username"
                                v-model="formData.username"
                                :saving="saving.username"
                                @save="saveField('username', formData.username)"
                            />
                            <FormField
                                label="Email"
                                v-model="formData.email"
                                type="email"
                                :saving="saving.email"
                                @save="saveField('email', formData.email)"
                            />
                            <FormField
                                label="First Name"
                                v-model="formData.first_name"
                                :saving="saving.first_name"
                                @save="saveField('first_name', formData.first_name)"
                            />
                            <FormField
                                label="Last Name"
                                v-model="formData.last_name"
                                :saving="saving.last_name"
                                @save="saveField('last_name', formData.last_name)"
                            />
                            <FormField
                                label="Password"
                                v-model="formData.password"
                                type="password"
                                :saving="saving.password"
                                @save="saveField('password', formData.password, true)"
                            />
                            <FormField
                                label="Avatar URL"
                                v-model="formData.avatar"
                                :saving="saving.avatar"
                                @save="saveField('avatar', formData.avatar)"
                            />
                            <FormSelect
                                label="Role"
                                v-model="formData.role"
                                :options="roleOptions"
                                :saving="saving.role"
                                @save="saveField('role', formData.role)"
                            />
                            <FormField
                                label="Credits"
                                v-model="formData.credits"
                                type="number"
                                :saving="saving.credits"
                                @save="saveField('credits', formData.credits)"
                            />
                        </div>
                    </div>

                    <!-- Resource Limits -->
                    <div v-if="activeTab === 'resources'" class="space-y-6">
                        <h3 class="text-lg font-medium text-white">Resource Limits</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <FormField
                                label="Memory Limit (MB)"
                                v-model="formData.memory_limit"
                                type="number"
                                :saving="saving.memory_limit"
                                @save="saveField('memory_limit', formData.memory_limit)"
                            />
                            <FormField
                                label="CPU Limit (%)"
                                v-model="formData.cpu_limit"
                                type="number"
                                :saving="saving.cpu_limit"
                                @save="saveField('cpu_limit', formData.cpu_limit)"
                            />
                            <FormField
                                label="Disk Limit (MB)"
                                v-model="formData.disk_limit"
                                type="number"
                                :saving="saving.disk_limit"
                                @save="saveField('disk_limit', formData.disk_limit)"
                            />
                            <FormField
                                label="Server Limit"
                                v-model="formData.server_limit"
                                type="number"
                                :saving="saving.server_limit"
                                @save="saveField('server_limit', formData.server_limit)"
                            />
                            <FormField
                                label="Backup Limit"
                                v-model="formData.backup_limit"
                                type="number"
                                :saving="saving.backup_limit"
                                @save="saveField('backup_limit', formData.backup_limit)"
                            />
                            <FormField
                                label="Database Limit"
                                v-model="formData.database_limit"
                                type="number"
                                :saving="saving.database_limit"
                                @save="saveField('database_limit', formData.database_limit)"
                            />
                            <FormField
                                label="Allocation Limit"
                                v-model="formData.allocation_limit"
                                type="number"
                                :saving="saving.allocation_limit"
                                @save="saveField('allocation_limit', formData.allocation_limit)"
                            />
                        </div>
                    </div>

                    <!-- Account Settings -->
                    <div v-if="activeTab === 'account'" class="space-y-6">
                        <h3 class="text-lg font-medium text-white">Account Settings</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <FormField
                                label="Pterodactyl User ID"
                                v-model="formData.pterodactyl_user_id"
                                type="number"
                                readonly
                                disabled
                            />
                            <FormField
                                label="Background URL"
                                v-model="formData.background"
                                :saving="saving.background"
                                @save="saveField('background', formData.background)"
                            />
                            <FormSelect
                                label="Banned"
                                v-model="formData.banned"
                                :options="[
                                    { value: 'NO', label: 'No' },
                                    { value: 'YES', label: 'Yes' },
                                ]"
                                :saving="saving.banned"
                                @save="saveField('banned', formData.banned)"
                            />
                            <FormSelect
                                label="Verified"
                                v-model="formData.verified"
                                :options="[
                                    { value: 'true', label: 'Yes' },
                                    { value: 'false', label: 'No' },
                                ]"
                                :saving="saving.verified"
                                @save="saveField('verified', formData.verified)"
                            />
                            <FormSelect
                                label="2FA Enabled"
                                v-model="formData.two_fa_enabled"
                                :options="[
                                    { value: 'true', label: 'Yes' },
                                    { value: 'false', label: 'No' },
                                ]"
                                :saving="saving.two_fa_enabled"
                                @save="saveField('2fa_enabled', formData.two_fa_enabled)"
                            />
                            <FormSelect
                                label="2FA Blocked"
                                v-model="formData.two_fa_blocked"
                                :options="[
                                    { value: 'true', label: 'Yes' },
                                    { value: 'false', label: 'No' },
                                ]"
                                :saving="saving.two_fa_blocked"
                                @save="saveField('2fa_blocked', formData.two_fa_blocked)"
                            />
                            <FormField
                                label="Support PIN"
                                v-model="formData.support_pin"
                                :saving="saving.support_pin"
                                @save="saveField('support_pin', formData.support_pin)"
                            />
                        </div>
                    </div>
                    <div v-if="activeTab === 'activity'" class="space-y-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-white">Activity Logs</h3>
                            <span class="text-sm text-gray-400">{{ activityLogs.length }} entries</span>
                        </div>
                        <div class="space-y-4">
                            <div
                                v-for="log in activityLogs"
                                :key="log.id"
                                class="bg-gray-700 rounded-lg p-4 hover:bg-gray-650 transition-colors"
                            >
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-medium text-pink-400">{{ log.action }}</span>
                                    <span class="text-sm text-gray-400">{{ formatDate(log.date) }}</span>
                                </div>
                                <div class="grid grid-cols-2 gap-2 text-sm">
                                    <div>
                                        <span class="text-gray-400">IP Address:</span>
                                        <span class="text-white ml-2">{{ log.ip_address }}</span>
                                    </div>
                                    <div class="col-span-2">
                                        <span class="text-gray-400">Context:</span>
                                        <span class="text-white ml-2">{{ log.context }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="activeTab === 'emails'" class="space-y-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-white">Last 50 Emails</h3>
                            <span class="text-sm text-gray-400">{{ emails.length }} messages</span>
                        </div>
                        <div class="space-y-4">
                            <div
                                v-for="email in emails"
                                :key="email.id"
                                class="bg-gray-700 rounded-lg p-4 hover:bg-gray-650 transition-colors"
                            >
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-medium text-pink-400">{{ email.subject }}</span>
                                    <span class="text-sm text-gray-400">{{ formatDate(email.date) }}</span>
                                </div>
                                <div class="text-sm">
                                    <div class="flex items-center">
                                        <span class="text-gray-400">From:</span>
                                        <span class="text-white ml-2">{{ email.from }}</span>
                                    </div>
                                </div>
                                <div class="text-sm">
                                    <span class="text-gray-400">To:</span>
                                    <span class="text-white ml-2">{{ user.email }}</span>
                                </div>
                                <div class="mt-2">
                                    <button
                                        @click="email.showBody = !email.showBody"
                                        class="text-sm text-pink-400 hover:text-pink-300 transition-colors"
                                    >
                                        {{ email.showBody ? 'Hide Content' : 'Show Content' }}
                                    </button>
                                    <div
                                        v-if="email.showBody"
                                        class="mt-3 border border-gray-600 rounded-lg overflow-hidden"
                                    >
                                        <iframe
                                            :srcdoc="email.body"
                                            class="w-full h-96 bg-white"
                                            sandbox="allow-same-origin"
                                        ></iframe>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- System Information (Read-only) -->
                    <div v-if="activeTab === 'system'" class="space-y-6">
                        <h3 class="text-lg font-medium text-white">System Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div class="bg-gray-700 rounded-lg p-3">
                                <span class="text-gray-400">First IP:</span>
                                <span class="text-white ml-2">{{ user.first_ip }}</span>
                            </div>
                            <div class="bg-gray-700 rounded-lg p-3">
                                <span class="text-gray-400">Last IP:</span>
                                <span class="text-white ml-2">{{ user.last_ip }}</span>
                            </div>
                            <div class="bg-gray-700 rounded-lg p-3">
                                <span class="text-gray-400">First Seen:</span>
                                <span class="text-white ml-2">{{ formatDate(user.first_seen) }}</span>
                            </div>
                            <div class="bg-gray-700 rounded-lg p-3">
                                <span class="text-gray-400">Last Seen:</span>
                                <span class="text-white ml-2">{{ formatDate(user.last_seen) }}</span>
                            </div>
                            <div class="bg-gray-700 rounded-lg p-3">
                                <span class="text-gray-400">Minutes AFK:</span>
                                <span class="text-white ml-2">{{ user.minutes_afk }}</span>
                            </div>
                            <div class="bg-gray-700 rounded-lg p-3">
                                <span class="text-gray-400">Last Seen AFK:</span>
                                <span class="text-white ml-2">{{ user.last_seen_afk || 'Never' }}</span>
                            </div>
                            <div class="bg-gray-700 rounded-lg p-3 md:col-span-2">
                                <span class="text-gray-400">UUID:</span>
                                <span class="text-white ml-2 font-mono text-xs">{{ user.uuid }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </LayoutDashboard>
</template>
