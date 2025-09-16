<script setup lang="ts">
import { ref, reactive, onMounted, h, defineComponent, computed } from 'vue';
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
    MessageSquare as DiscordIcon,
    Github as GithubIcon,
    Plus as PlusIcon,
    Pencil as PencilIcon,
    RefreshCcw as RefreshCcwIcon,
    Search as SearchIcon,
    X as XIcon,
    Pause as PauseIcon,
} from 'lucide-vue-next';
import Users from '@/mythicaldash/admin/Users';
import Swal from 'sweetalert2';
import { useSound } from '@vueuse/sound';
import failedAlertSfx from '@/assets/sounds/error.mp3';
import { useSettingsStore } from '@/stores/settings';
const Settings = useSettingsStore();
import successAlertSfx from '@/assets/sounds/success.mp3';
import Roles from '@/mythicaldash/admin/Roles';

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
    // Discord fields
    discord_id: string;
    discord_username: string;
    discord_global_name: string;
    discord_email: string;
    discord_linked: string;
    discord_servers: string;
    j4r_joined_servers: string;
    // GitHub fields
    github_id: string;
    github_username: string;
    github_email: string;
    github_linked: string;
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
    // Discord fields
    discord_id: string;
    discord_username: string;
    discord_global_name: string;
    discord_email: string;
    discord_linked: string;
    // GitHub fields
    github_id: string;
    github_username: string;
    github_email: string;
    github_linked: string;
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

// Server interfaces
interface ServerLimits {
    memory: number;
    cpu: number;
    disk: number;
}

interface ServerFeatureLimits {
    backups: number;
    databases: number;
    allocations: number;
}

interface Server {
    id: string;
    identifier: string;
    name: string;
    description: string;
    suspended: boolean;
    limits: ServerLimits;
    feature_limits: ServerFeatureLimits;
    node: string;
    allocation: number;
    created_at: string;
    updated_at: string;
    location?: {
        name: string;
    };
    service?: {
        name: string;
    };
    category?: {
        name: string;
    };
}

interface QueuedServer {
    id: string;
    name: string;
    description: string;
    status: 'pending' | 'failed' | 'building';
    limits: ServerLimits;
    feature_limits: ServerFeatureLimits;
    location?: {
        name: string;
    };
    service?: {
        name: string;
    };
    category?: {
        name: string;
    };
    created_at: string;
    updated_at: string;
}

// Discord server interface
interface DiscordServer {
    id: string;
    name: string;
    icon?: string;
    owner?: boolean;
}

// J4R server interface (just server IDs)
type J4RServerEntry = string;

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
    color?: string;
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
        showColors: {
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
                        props.options.map((option: RoleOption) => {
                            if (props.showColors && option.color) {
                                return h(
                                    'option',
                                    {
                                        value: option.value,
                                        style: { color: option.color },
                                    },
                                    [h('span', { style: { color: option.color } }, '● '), option.label],
                                );
                            }
                            return h('option', { value: option.value }, option.label);
                        }),
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
    { id: 'discord', name: 'Discord', icon: DiscordIcon },
    { id: 'github', name: 'GitHub', icon: GithubIcon },
    { id: 'servers', name: 'Servers', icon: ServerIcon },
    { id: 'server-queue', name: 'Server Queue', icon: ClockIcon },
];

// Role options for dropdown
const roleOptions = ref<RoleOption[]>([]);
const rolesData = ref<Array<{ id: number; name: string; color: string }>>([]);

// Server data
const servers = ref<Server[]>([]);
const queuedServers = ref<QueuedServer[]>([]);
const serverSearchQuery = ref('');
const queueSearchQuery = ref('');

// Fetch roles for dropdown
const fetchRoles = async () => {
    try {
        const response = await Roles.getRoles();
        if (response.success) {
            rolesData.value = response.roles;
            roleOptions.value = response.roles.map((role: { id: number; name: string; color: string }) => ({
                value: role.id,
                label: role.name,
                color: role.color,
            }));
        }
    } catch (error) {
        console.error('Error fetching roles:', error);
    }
};

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
    // Discord fields
    discord_id: '',
    discord_username: '',
    discord_global_name: '',
    discord_email: '',
    discord_linked: 'false',
    // GitHub fields
    github_id: '',
    github_username: '',
    github_email: '',
    github_linked: 'false',
});

// Track saving state for each field
const saving = reactive<Record<string, boolean>>({});

// Role helpers
const getRoleName = (roleId: string): string => {
    const id = parseInt(roleId);
    const role = roleOptions.value.find((r) => r.value === id);
    return role ? role.label : 'Unknown';
};

const getRoleClass = (roleId: string): string => {
    const id = parseInt(roleId);
    const role = rolesData.value.find((r) => r.id === id);
    if (role) {
        return 'px-3 py-1 rounded-full text-xs inline-flex items-center w-fit';
    }
    return 'px-3 py-1 rounded-full text-xs inline-flex items-center w-fit bg-gray-500/20 text-gray-400';
};

const getRoleStyle = (roleId: string): Record<string, string> => {
    const id = parseInt(roleId);
    const role = rolesData.value.find((r) => r.id === id);
    if (role) {
        // Convert hex color to rgba for background with opacity
        const hex = role.color.replace('#', '');
        const r = parseInt(hex.substr(0, 2), 16);
        const g = parseInt(hex.substr(2, 2), 16);
        const b = parseInt(hex.substr(4, 2), 16);

        return {
            backgroundColor: `rgba(${r}, ${g}, ${b}, 0.2)`,
            color: role.color,
        };
    }
    return {
        backgroundColor: 'rgba(107, 114, 128, 0.2)',
        color: '#9CA3AF',
    };
};

const formatDate = (dateString?: string): string => {
    if (!dateString) return 'Never';
    const date = new Date(dateString);
    return date.toLocaleString();
};

// Utility function to safely parse JSON or return the value if it's already an object
const parseJsonSafely = (value: string | null | undefined | unknown): DiscordServer[] | J4RServerEntry[] | null => {
    if (!value) return null;

    // If it's already an array, return it
    if (Array.isArray(value)) {
        return value;
    }

    // If it's a string, try to parse it
    if (typeof value === 'string') {
        try {
            const parsed = JSON.parse(value);
            return Array.isArray(parsed) ? parsed : null;
        } catch (e) {
            console.error('Error parsing JSON:', e);
            return null;
        }
    }

    return null;
};

// Parse Discord servers specifically
const parseDiscordServers = (value: string | null | undefined | unknown): DiscordServer[] => {
    const parsed = parseJsonSafely(value);
    return (parsed as DiscordServer[]) || [];
};

// Parse J4R server entries specifically
const parseJ4RServers = (value: string | null | undefined | unknown): J4RServerEntry[] => {
    const parsed = parseJsonSafely(value);
    return (parsed as J4RServerEntry[]) || [];
};

// Server utility functions
const formatBytes = (bytes: number) => {
    if (bytes === 0) return '0 MB';
    const k = 1024;
    const sizes = ['MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const getServerStatus = (server: Server | QueuedServer) => {
    if ('suspended' in server) {
        return {
            color: server.suspended ? 'bg-red-500' : 'bg-green-500',
            text: server.suspended ? 'Suspended' : 'Active',
        };
    } else {
        switch (server.status) {
            case 'pending':
                return {
                    color: 'bg-yellow-500',
                    text: 'Pending',
                };
            case 'building':
                return {
                    color: 'bg-blue-500',
                    text: 'Building',
                };
            case 'failed':
                return {
                    color: 'bg-red-500',
                    text: 'Failed',
                };
            default:
                return {
                    color: 'bg-gray-500',
                    text: 'Unknown',
                };
        }
    }
};

const getServerIdentifier = (server: Server | QueuedServer): string => {
    return 'identifier' in server ? server.identifier : `Queue #${server.id}`;
};

const getServerId = (server: Server | QueuedServer): string => {
    return 'identifier' in server ? server.id : server.id;
};

const jumpToPanel = (identifier: string) => {
    const pterodactylUrl = Settings.getSetting('pterodactyl_base_url');
    if (!pterodactylUrl) {
        console.error('Pterodactyl URL not found in settings');
        return;
    }
    window.open(`${pterodactylUrl}/server/${identifier}`, '_blank');
};

// Server management functions
const deleteServer = async (server: Server) => {
    const result = await Swal.fire({
        title: 'Delete Server',
        text: `Are you sure you want to delete "${server.name}"? This action cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
        background: '#1f2937',
        color: '#fff',
    });

    if (result.isConfirmed) {
        try {
            // Call the delete server API
            const response = await fetch(`/api/admin/servers/delete/${server.id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
            });

            const data = await response.json();

            if (data.success) {
                playSuccess();
                Swal.fire({
                    title: 'Deleted!',
                    text: 'Server has been deleted successfully.',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false,
                    background: '#1f2937',
                    color: '#fff',
                });

                // Refresh server data
                await fetchUser();
            } else {
                playError();
                Swal.fire({
                    title: 'Error!',
                    text: data.message || 'Failed to delete server.',
                    icon: 'error',
                    background: '#1f2937',
                    color: '#fff',
                });
            }
        } catch (error) {
            console.error('Error deleting server:', error);
            playError();
            Swal.fire({
                title: 'Error!',
                text: 'An unexpected error occurred.',
                icon: 'error',
                background: '#1f2937',
                color: '#fff',
            });
        }
    }
};

const toggleSuspend = async (server: Server) => {
    const action = server.suspended ? 'unsuspend' : 'suspend';
    const result = await Swal.fire({
        title: `${server.suspended ? 'Unsuspend' : 'Suspend'} Server`,
        text: `Are you sure you want to ${action} "${server.name}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: server.suspended ? '#10b981' : '#f59e0b',
        cancelButtonColor: '#6b7280',
        confirmButtonText: `Yes, ${action} it!`,
        cancelButtonText: 'Cancel',
        background: '#1f2937',
        color: '#fff',
    });

    if (result.isConfirmed) {
        try {
            // Call the suspend/unsuspend server API
            const response = await fetch(`/api/admin/servers/toggle-suspend/${server.id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
            });

            const data = await response.json();

            if (data.success) {
                playSuccess();
                Swal.fire({
                    title: 'Success!',
                    text: `Server has been ${action}ed successfully.`,
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false,
                    background: '#1f2937',
                    color: '#fff',
                });

                // Refresh server data
                await fetchUser();
            } else {
                playError();
                Swal.fire({
                    title: 'Error!',
                    text: data.message || `Failed to ${action} server.`,
                    icon: 'error',
                    background: '#1f2937',
                    color: '#fff',
                });
            }
        } catch (error) {
            console.error(`Error ${action}ing server:`, error);
            playError();
            Swal.fire({
                title: 'Error!',
                text: 'An unexpected error occurred.',
                icon: 'error',
                background: '#1f2937',
                color: '#fff',
            });
        }
    }
};

const deleteQueuedServer = async (server: QueuedServer) => {
    const result = await Swal.fire({
        title: 'Delete Queued Server',
        text: `Are you sure you want to delete "${server.name}" from the queue?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
        background: '#1f2937',
        color: '#fff',
    });

    if (result.isConfirmed) {
        try {
            // Call the delete queued server API
            const response = await fetch(`/api/admin/server-queue/${server.id}/delete`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
            });

            const data = await response.json();

            if (data.success) {
                playSuccess();
                Swal.fire({
                    title: 'Deleted!',
                    text: 'Server has been removed from the queue.',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false,
                    background: '#1f2937',
                    color: '#fff',
                });

                // Refresh server data
                await fetchUser();
            } else {
                playError();
                Swal.fire({
                    title: 'Error!',
                    text: data.message || 'Failed to delete server from queue.',
                    icon: 'error',
                    background: '#1f2937',
                    color: '#fff',
                });
            }
        } catch (error) {
            console.error('Error deleting queued server:', error);
            playError();
            Swal.fire({
                title: 'Error!',
                text: 'An unexpected error occurred.',
                icon: 'error',
                background: '#1f2937',
                color: '#fff',
            });
        }
    }
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
            servers.value = response.servers || [];
            queuedServers.value = response.servers_queue || [];

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
                // Discord fields
                discord_id: user.value.discord_id || '',
                discord_username: user.value.discord_username || '',
                discord_global_name: user.value.discord_global_name || '',
                discord_email: user.value.discord_email || '',
                discord_linked: user.value.discord_linked || 'false',
                // GitHub fields
                github_id: user.value.github_id || '',
                github_username: user.value.github_username || '',
                github_email: user.value.github_email || '',
                github_linked: user.value.github_linked || 'false',
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
const saveField = async (column: string, value: string | number): Promise<void> => {
    saving[column] = true;

    try {
        // Convert number to string for API
        const stringValue = value.toString();

        const response = await Users.updateUser(userId, column, stringValue);

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

// Ban helpers
const isBanned = computed<boolean>(() => {
    // Prefer the form state when present, fall back to loaded user state
    return (formData.value?.banned || user.value?.banned || 'NO') === 'YES';
});

const setBanned = async (status: 'YES' | 'NO'): Promise<void> => {
    saving.banned = true;
    try {
        const resp = await fetch(`/api/admin/user/${userId}/ban`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ status }),
        });
        const data = await resp.json();
        if (resp.ok && data?.status === status) {
            formData.value.banned = status;
            if (user.value) {
                (user.value as unknown as Record<string, string>)['banned'] = status;
            }
            playSuccess();
            Swal.fire({
                icon: 'success',
                title: status === 'YES' ? 'User Banned' : 'User Unbanned',
                toast: true,
                position: 'top-end',
                timer: 2000,
                showConfirmButton: false,
            });
        } else {
            throw new Error(data?.message || 'Failed to update ban status');
        }
    } catch (e) {
        console.error('Ban toggle failed:', e);
        playError();
        Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to update ban status' });
    } finally {
        saving.banned = false;
    }
};

const toggleBan = async (): Promise<void> => {
    const next = isBanned.value ? 'NO' : 'YES';
    await setBanned(next);
};

onMounted(() => {
    fetchUser();
    fetchRoles();
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
                                :src="user.avatar || 'https://github.com/mythicalltd.png'"
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
                                <span :class="getRoleClass(user.role)" :style="getRoleStyle(user.role)">
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
                                <div class="flex">
                                    <button
                                        @click="toggleBan()"
                                        :class="[
                                            'flex items-center gap-1 px-3 py-1 rounded-lg transition-colors text-sm',
                                            isBanned
                                                ? 'bg-green-500/20 text-green-400 hover:bg-green-500/30'
                                                : 'bg-red-500/20 text-red-400 hover:bg-red-500/30',
                                        ]"
                                    >
                                        <PauseIcon class="h-3.5 w-3.5" />
                                        {{ isBanned ? 'Unban User' : 'Ban User' }}
                                    </button>
                                </div>
                                <div class="flex items-center">
                                    <span
                                        :class="[
                                            'ml-1 px-2 py-0.5 rounded-full text-xs border',
                                            isBanned
                                                ? 'border-red-500/40 text-red-300 bg-red-500/10'
                                                : 'border-green-500/40 text-green-300 bg-green-500/10',
                                        ]"
                                    >
                                        {{ isBanned ? 'BANNED' : 'NOT BANNED' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content with Sidebar -->
            <div class="flex gap-6">
                <!-- Sidebar Navigation -->
                <div class="w-64 flex-shrink-0">
                    <div class="bg-gray-800 rounded-lg shadow-md overflow-hidden sticky top-4">
                        <div class="p-4 border-b border-gray-700">
                            <h3 class="text-lg font-medium text-white">Categories</h3>
                        </div>
                        <nav class="p-2">
                            <button
                                v-for="tab in tabs"
                                :key="tab.id"
                                @click="activeTab = tab.id"
                                :class="[
                                    'w-full flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg transition-colors text-left',
                                    activeTab === tab.id
                                        ? 'bg-pink-500/20 text-pink-400 border border-pink-500/30'
                                        : 'text-gray-400 hover:text-white hover:bg-gray-700/50',
                                ]"
                            >
                                <component :is="tab.icon" class="h-4 w-4 flex-shrink-0" />
                                {{ tab.name }}
                            </button>
                        </nav>
                    </div>
                </div>

                <!-- Main Content Area -->
                <div class="flex-1 min-w-0">
                    <div class="bg-gray-800 rounded-lg shadow-md overflow-hidden">
                        <div class="p-6">
                            <!-- Basic Information -->
                            <div v-if="activeTab === 'basic'" class="space-y-6">
                                <h3 class="text-lg font-medium text-white">Basic Information</h3>
                                <form @submit.prevent>
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
                                            @save="saveField('password', formData.password)"
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
                                            :showColors="true"
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
                                </form>
                            </div>

                            <!-- Resource Limits -->
                            <div v-if="activeTab === 'resources'" class="space-y-6">
                                <h3 class="text-lg font-medium text-white">Resource Limits</h3>
                                <form @submit.prevent>
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
                                </form>
                            </div>

                            <!-- Account Settings -->
                            <div v-if="activeTab === 'account'" class="space-y-6">
                                <h3 class="text-lg font-medium text-white">Account Settings</h3>
                                <form @submit.prevent>
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
                                        <!-- Removed banned dropdown. Ban/Unban is handled by header button. -->
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
                                </form>
                            </div>

                            <!-- Discord Information -->
                            <div v-if="activeTab === 'discord'" class="space-y-6">
                                <h3 class="text-lg font-medium text-white">Discord Information</h3>
                                <form @submit.prevent>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <FormField label="Discord ID" v-model="formData.discord_id" readonly disabled />
                                        <FormField
                                            label="Discord Username"
                                            v-model="formData.discord_username"
                                            readonly
                                            disabled
                                        />
                                        <FormField
                                            label="Discord Global Name"
                                            v-model="formData.discord_global_name"
                                            readonly
                                            disabled
                                        />
                                        <FormField
                                            label="Discord Email"
                                            v-model="formData.discord_email"
                                            type="email"
                                            readonly
                                            disabled
                                        />
                                        <FormSelect
                                            label="Discord Linked"
                                            v-model="formData.discord_linked"
                                            :options="[
                                                { value: 'true', label: 'Yes' },
                                                { value: 'false', label: 'No' },
                                            ]"
                                            :saving="saving.discord_linked"
                                            @save="saveField('discord_linked', formData.discord_linked)"
                                        />
                                    </div>
                                </form>

                                <!-- Read-only Server Listings -->
                                <div class="space-y-6 mt-8">
                                    <!-- Discord Servers -->
                                    <div class="bg-gray-700 rounded-lg p-4">
                                        <h4 class="text-md font-medium text-white mb-3">Discord Servers (Read-only)</h4>
                                        <div v-if="user.discord_servers">
                                            <div
                                                v-for="(server, index) in parseDiscordServers(user.discord_servers) ||
                                                []"
                                                :key="index"
                                                class="flex items-center justify-between p-2 bg-gray-600 rounded mb-2"
                                            >
                                                <div class="flex items-center space-x-3">
                                                    <img
                                                        v-if="server.icon"
                                                        :src="`https://cdn.discordapp.com/icons/${server.id}/${server.icon}.png`"
                                                        :alt="server.name"
                                                        class="w-8 h-8 rounded"
                                                    />
                                                    <div
                                                        v-else
                                                        class="w-8 h-8 bg-gray-500 rounded flex items-center justify-center"
                                                    >
                                                        <span class="text-xs text-gray-300">#</span>
                                                    </div>
                                                    <div>
                                                        <div class="text-white font-medium">
                                                            {{ server.name || 'Unknown Server' }}
                                                        </div>
                                                        <div class="text-gray-400 text-sm">
                                                            {{ server.id || 'No ID' }}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="text-gray-400 text-sm">
                                                    {{ server.owner ? 'Owner' : 'Member' }}
                                                </div>
                                            </div>
                                        </div>
                                        <div v-else class="text-gray-400 text-sm">No Discord servers found</div>
                                    </div>

                                    <!-- J4R Joined Servers -->
                                    <div class="bg-gray-700 rounded-lg p-4">
                                        <h4 class="text-md font-medium text-white mb-3">
                                            J4R Joined Servers (Read-only)
                                        </h4>
                                        <div v-if="user.j4r_joined_servers">
                                            <div
                                                v-for="(serverId, index) in parseJ4RServers(user.j4r_joined_servers) ||
                                                []"
                                                :key="index"
                                                class="flex items-center justify-between p-2 bg-gray-600 rounded mb-2"
                                            >
                                                <div class="flex items-center space-x-3">
                                                    <div
                                                        class="w-8 h-8 bg-green-500 rounded flex items-center justify-center"
                                                    >
                                                        <span class="text-xs text-white">✓</span>
                                                    </div>
                                                    <div>
                                                        <div class="text-white font-medium">
                                                            Server ID: {{ serverId }}
                                                        </div>
                                                        <div class="text-gray-400 text-sm">Joined for rewards</div>
                                                    </div>
                                                </div>
                                                <div class="text-green-400 text-sm">Joined</div>
                                            </div>
                                        </div>
                                        <div v-else class="text-gray-400 text-sm">No J4R servers joined</div>
                                    </div>
                                </div>
                            </div>

                            <!-- GitHub Information -->
                            <div v-if="activeTab === 'github'" class="space-y-6">
                                <h3 class="text-lg font-medium text-white">GitHub Information</h3>
                                <form @submit.prevent>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <FormField label="GitHub ID" v-model="formData.github_id" readonly disabled />
                                        <FormField
                                            label="GitHub Username"
                                            v-model="formData.github_username"
                                            readonly
                                            disabled
                                        />
                                        <FormField
                                            label="GitHub Email"
                                            v-model="formData.github_email"
                                            type="email"
                                            readonly
                                            disabled
                                        />
                                        <FormSelect
                                            label="GitHub Linked"
                                            v-model="formData.github_linked"
                                            :options="[
                                                { value: 'true', label: 'Yes' },
                                                { value: 'false', label: 'No' },
                                            ]"
                                            :saving="saving.github_linked"
                                            @save="saveField('github_linked', formData.github_linked)"
                                        />
                                    </div>
                                </form>

                                <!-- GitHub Account Status -->
                                <div class="space-y-6 mt-8">
                                    <div class="bg-gray-700 rounded-lg p-4">
                                        <h4 class="text-md font-medium text-white mb-3">GitHub Account Status</h4>
                                        <div v-if="user.github_linked === 'true'" class="space-y-3">
                                            <div
                                                class="flex items-center space-x-3 p-3 bg-green-500/20 rounded-lg border border-green-500/30"
                                            >
                                                <div
                                                    class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center"
                                                >
                                                    <GithubIcon class="w-4 h-4 text-white" />
                                                </div>
                                                <div>
                                                    <div class="text-white font-medium">GitHub Account Linked</div>
                                                    <div class="text-gray-400 text-sm">
                                                        Username: {{ user.github_username || 'Unknown' }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div v-if="user.github_id" class="text-gray-400 text-sm">
                                                GitHub ID: {{ user.github_id }}
                                            </div>
                                            <div v-if="user.github_email" class="text-gray-400 text-sm">
                                                GitHub Email: {{ user.github_email }}
                                            </div>
                                        </div>
                                        <div
                                            v-else
                                            class="flex items-center space-x-3 p-3 bg-gray-600/20 rounded-lg border border-gray-600/30"
                                        >
                                            <div
                                                class="w-8 h-8 bg-gray-500 rounded-full flex items-center justify-center"
                                            >
                                                <GithubIcon class="w-4 h-4 text-gray-300" />
                                            </div>
                                            <div>
                                                <div class="text-gray-300 font-medium">GitHub Account Not Linked</div>
                                                <div class="text-gray-400 text-sm">
                                                    User has not connected their GitHub account
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Servers -->
                            <div v-if="activeTab === 'servers'" class="space-y-6">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-medium text-white">User Servers</h3>
                                    <div class="flex items-center gap-2">
                                        <div class="relative">
                                            <div
                                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                                            >
                                                <SearchIcon class="h-5 w-5 text-gray-400" />
                                            </div>
                                            <input
                                                v-model="serverSearchQuery"
                                                type="text"
                                                class="block w-full pl-10 pr-10 py-2 border border-gray-700 rounded-lg bg-gray-800/50 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-colors"
                                                placeholder="Search servers..."
                                            />
                                            <button
                                                v-if="serverSearchQuery"
                                                @click="serverSearchQuery = ''"
                                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-300 transition-colors"
                                            >
                                                <XIcon class="h-5 w-5" />
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="servers.length === 0" class="text-center py-8">
                                    <ServerIcon class="w-12 h-12 text-gray-600 mx-auto mb-3" />
                                    <h3 class="text-gray-300 font-medium mb-1">No Servers Found</h3>
                                    <p class="text-gray-500 text-sm">This user doesn't have any active servers.</p>
                                </div>

                                <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                                    <div
                                        v-for="server in servers.filter(
                                            (s) =>
                                                !serverSearchQuery ||
                                                s.name.toLowerCase().includes(serverSearchQuery.toLowerCase()) ||
                                                getServerIdentifier(s)
                                                    .toLowerCase()
                                                    .includes(serverSearchQuery.toLowerCase()) ||
                                                s.location?.name
                                                    ?.toLowerCase()
                                                    .includes(serverSearchQuery.toLowerCase()) ||
                                                s.service?.name
                                                    ?.toLowerCase()
                                                    .includes(serverSearchQuery.toLowerCase()),
                                        )"
                                        :key="getServerId(server)"
                                        class="group relative bg-gray-900/40 border border-gray-800 rounded-xl p-5 hover:bg-gray-800/40 transition-all duration-200 hover:border-gray-700"
                                    >
                                        <!-- Server Status Indicator -->
                                        <div class="absolute top-4 right-4">
                                            <div class="flex items-center gap-2">
                                                <div class="flex items-center gap-1.5">
                                                    <div
                                                        class="w-2 h-2 rounded-full"
                                                        :class="getServerStatus(server).color"
                                                    ></div>
                                                    <span class="text-xs text-gray-400">{{
                                                        getServerStatus(server).text
                                                    }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Server Header -->
                                        <div class="mb-4">
                                            <h3 class="text-lg font-medium text-white mb-1">{{ server.name }}</h3>
                                            <p class="text-sm text-gray-400">{{ getServerIdentifier(server) }}</p>
                                        </div>

                                        <!-- Server Details -->
                                        <div class="space-y-3">
                                            <div class="grid grid-cols-2 gap-3">
                                                <div class="bg-gray-800/50 rounded-lg p-3">
                                                    <div class="text-xs text-gray-400 mb-1">Location</div>
                                                    <div class="text-sm text-white">
                                                        {{ server.location?.name || 'Unknown' }}
                                                    </div>
                                                </div>
                                                <div class="bg-gray-800/50 rounded-lg p-3">
                                                    <div class="text-xs text-gray-400 mb-1">Service</div>
                                                    <div class="text-sm text-white">
                                                        {{ server.service?.name || 'Unknown' }}
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-3 gap-3">
                                                <div class="bg-gray-800/50 rounded-lg p-3">
                                                    <div class="text-xs text-gray-400 mb-1">Memory</div>
                                                    <div class="text-sm text-white">
                                                        {{ formatBytes(server.limits.memory) }}
                                                    </div>
                                                </div>
                                                <div class="bg-gray-800/50 rounded-lg p-3">
                                                    <div class="text-xs text-gray-400 mb-1">CPU</div>
                                                    <div class="text-sm text-white">{{ server.limits.cpu }}%</div>
                                                </div>
                                                <div class="bg-gray-800/50 rounded-lg p-3">
                                                    <div class="text-xs text-gray-400 mb-1">Disk</div>
                                                    <div class="text-sm text-white">
                                                        {{ formatBytes(server.limits.disk) }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Action Buttons -->
                                        <div class="mt-4 pt-4 border-t border-gray-800 flex flex-wrap gap-2">
                                            <button
                                                class="flex-1 min-w-0 px-4 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-medium rounded-lg transition-all duration-200 shadow-lg hover:shadow-xl flex items-center justify-center gap-2 text-sm"
                                                @click="jumpToPanel(getServerIdentifier(server))"
                                            >
                                                <ExternalLinkIcon class="w-4 h-4" />
                                                View Panel
                                            </button>
                                            <button
                                                class="flex-1 min-w-0 px-4 py-2.5 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white font-medium rounded-lg transition-all duration-200 shadow-lg hover:shadow-xl flex items-center justify-center gap-2 text-sm"
                                                @click="deleteServer(server)"
                                            >
                                                <TrashIcon class="w-4 h-4" />
                                                Delete
                                            </button>
                                            <button
                                                :class="[
                                                    'flex-1 min-w-0 px-4 py-2.5 font-medium rounded-lg transition-all duration-200 shadow-lg hover:shadow-xl flex items-center justify-center gap-2 text-sm',
                                                    server.suspended
                                                        ? 'bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white'
                                                        : 'bg-gradient-to-r from-orange-600 to-orange-700 hover:from-orange-700 hover:to-orange-800 text-white',
                                                ]"
                                                @click="toggleSuspend(server)"
                                            >
                                                <PauseIcon class="w-4 h-4" />
                                                {{ server.suspended ? 'Unsuspend' : 'Suspend' }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Server Queue -->
                            <div v-if="activeTab === 'server-queue'" class="space-y-6">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-medium text-white">Server Queue</h3>
                                    <div class="flex items-center gap-2">
                                        <div class="relative">
                                            <div
                                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                                            >
                                                <SearchIcon class="h-5 w-5 text-gray-400" />
                                            </div>
                                            <input
                                                v-model="queueSearchQuery"
                                                type="text"
                                                class="block w-full pl-10 pr-10 py-2 border border-gray-700 rounded-lg bg-gray-800/50 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-colors"
                                                placeholder="Search queue..."
                                            />
                                            <button
                                                v-if="queueSearchQuery"
                                                @click="queueSearchQuery = ''"
                                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-300 transition-colors"
                                            >
                                                <XIcon class="h-5 w-5" />
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="queuedServers.length === 0" class="text-center py-8">
                                    <ClockIcon class="w-12 h-12 text-gray-600 mx-auto mb-3" />
                                    <h3 class="text-gray-300 font-medium mb-1">No Servers in Queue</h3>
                                    <p class="text-gray-500 text-sm">
                                        This user doesn't have any servers in the creation queue.
                                    </p>
                                </div>

                                <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                                    <div
                                        v-for="server in queuedServers.filter(
                                            (s) =>
                                                !queueSearchQuery ||
                                                s.name.toLowerCase().includes(queueSearchQuery.toLowerCase()) ||
                                                getServerIdentifier(s)
                                                    .toLowerCase()
                                                    .includes(queueSearchQuery.toLowerCase()) ||
                                                s.location?.name
                                                    ?.toLowerCase()
                                                    .includes(queueSearchQuery.toLowerCase()) ||
                                                s.service?.name?.toLowerCase().includes(queueSearchQuery.toLowerCase()),
                                        )"
                                        :key="getServerId(server)"
                                        class="group relative bg-gray-900/40 border border-gray-800 rounded-xl p-5 hover:bg-gray-800/40 transition-all duration-200 hover:border-gray-700"
                                    >
                                        <!-- Server Status Indicator -->
                                        <div class="absolute top-4 right-4">
                                            <div class="flex items-center gap-2">
                                                <div class="flex items-center gap-1.5">
                                                    <div
                                                        class="w-2 h-2 rounded-full"
                                                        :class="getServerStatus(server).color"
                                                    ></div>
                                                    <span class="text-xs text-gray-400">{{
                                                        getServerStatus(server).text
                                                    }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Server Header -->
                                        <div class="mb-4">
                                            <h3 class="text-lg font-medium text-white mb-1">{{ server.name }}</h3>
                                            <p class="text-sm text-gray-400">{{ getServerIdentifier(server) }}</p>
                                        </div>

                                        <!-- Server Details -->
                                        <div class="space-y-3">
                                            <div class="grid grid-cols-2 gap-3">
                                                <div class="bg-gray-800/50 rounded-lg p-3">
                                                    <div class="text-xs text-gray-400 mb-1">Location</div>
                                                    <div class="text-sm text-white">
                                                        {{ server.location?.name || 'Unknown' }}
                                                    </div>
                                                </div>
                                                <div class="bg-gray-800/50 rounded-lg p-3">
                                                    <div class="text-xs text-gray-400 mb-1">Service</div>
                                                    <div class="text-sm text-white">
                                                        {{ server.service?.name || 'Unknown' }}
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-3 gap-3">
                                                <div class="bg-gray-800/50 rounded-lg p-3">
                                                    <div class="text-xs text-gray-400 mb-1">Memory</div>
                                                    <div class="text-sm text-white">
                                                        {{ formatBytes(server.limits.memory) }}
                                                    </div>
                                                </div>
                                                <div class="bg-gray-800/50 rounded-lg p-3">
                                                    <div class="text-xs text-gray-400 mb-1">CPU</div>
                                                    <div class="text-sm text-white">{{ server.limits.cpu }}%</div>
                                                </div>
                                                <div class="bg-gray-800/50 rounded-lg p-3">
                                                    <div class="text-xs text-gray-400 mb-1">Disk</div>
                                                    <div class="text-sm text-white">
                                                        {{ formatBytes(server.limits.disk) }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Queue Status -->
                                        <div class="mt-4 pt-4 border-t border-gray-800">
                                            <div class="text-center">
                                                <div class="text-sm text-gray-400">Queue Status</div>
                                                <div class="text-xs text-gray-500 mt-1">
                                                    {{
                                                        server.status === 'pending'
                                                            ? 'Waiting to be processed'
                                                            : server.status === 'building'
                                                              ? 'Currently being built'
                                                              : server.status === 'failed'
                                                                ? 'Build failed'
                                                                : 'Unknown status'
                                                    }}
                                                </div>
                                            </div>
                                        </div>
                                        <div
                                            class="mt-4 pt-4 border-t border-gray-800 flex items-center justify-center gap-2"
                                        >
                                            <button
                                                class="px-4 py-2.5 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white font-medium rounded-lg transition-all duration-200 shadow-lg hover:shadow-xl flex items-center gap-2 text-sm"
                                                @click="deleteQueuedServer(server)"
                                            >
                                                <TrashIcon class="w-4 h-4" />
                                                Remove from Queue
                                            </button>
                                        </div>
                                    </div>
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
            </div>
        </div>
    </LayoutDashboard>
</template>
