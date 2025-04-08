<template>
    <LayoutDashboard>
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-100 mb-2">Create a New Server</h1>
            <p class="text-gray-400">Configure and deploy your new server</p>
        </div>

        <!-- Resource Overview -->
        <CardComponent card-title="Resource Availability" class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div
                    v-for="(resource, key) in resourceItems"
                    :key="key"
                    class="p-3 bg-[#0a0a15]/50 rounded-lg border border-[#1a1a2f]/30"
                >
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-gray-400 text-sm">{{ resource.label }}</span>
                        <span
                            class="text-xs px-2 py-0.5 rounded-full"
                            :class="
                                resources.free[key] > 0
                                    ? 'bg-green-900/30 text-green-400'
                                    : 'bg-red-900/30 text-red-400'
                            "
                        >
                            {{ resources.free[key] > 0 ? 'Available' : 'Depleted' }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-200 font-medium">{{
                            formatResource(resources.free[key], resource.unit)
                        }}</span>
                        <span class="text-gray-500 text-xs"
                            >of {{ formatResource(resources.total[key], resource.unit) }}</span
                        >
                    </div>
                    <div class="w-full bg-[#030305] rounded-full h-1.5 mt-2">
                        <div
                            class="h-1.5 rounded-full"
                            :class="resources.free[key] > 0 ? 'bg-indigo-500' : 'bg-red-500'"
                            :style="`width: ${calculatePercentage(resources.used[key], resources.total[key])}%`"
                        ></div>
                    </div>
                </div>
            </div>
        </CardComponent>

        <form @submit.prevent="createServer">
            <!-- Server Details -->
            <CardComponent card-title="Server Details" class="mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Left Column - Name and Description -->
                    <div class="space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-300 mb-2">Server Name</label>
                            <TextInput id="name" v-model="form.name" placeholder="My Awesome Server" required />
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-300 mb-2"
                                >Description (Optional)</label
                            >
                            <TextArea
                                id="description"
                                v-model="form.description"
                                placeholder="Describe your server..."
                                :rows="3"
                            />
                        </div>
                    </div>

                    <!-- Right Column - Location and Type -->
                    <div class="space-y-4">
                        <div>
                            <label for="location" class="block text-sm font-medium text-gray-300 mb-2"
                                >Server Location</label
                            >
                            <SelectInput
                                id="location"
                                v-model="form.location_id"
                                :options="locationOptions"
                                placeholder="Select a location"
                            />
                        </div>

                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-300 mb-2"
                                >Server Type</label
                            >
                            <SelectInput
                                id="category"
                                v-model="form.category_id"
                                :options="categoryOptions"
                                placeholder="Select a server type"
                                @update:modelValue="updateEggs"
                            />
                        </div>

                        <div>
                            <label for="egg" class="block text-sm font-medium text-gray-300 mb-2">Server Version</label>
                            <SelectInput
                                id="egg"
                                v-model="form.egg_id"
                                :options="eggOptions"
                                placeholder="Select a version"
                            />
                        </div>
                    </div>
                </div>
            </CardComponent>

            <!-- Resource Allocation -->
            <CardComponent card-title="Resource Allocation" class="mb-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Memory -->
                    <div>
                        <label for="memory" class="block text-sm font-medium text-gray-300 mb-2">
                            Memory (MB)
                            <span class="text-xs text-gray-500">(Available: {{ resources.free.memory }})</span>
                        </label>
                        <TextInput id="memory" v-model="memoryModel" type="number" required />
                    </div>

                    <!-- CPU -->
                    <div>
                        <label for="cpu" class="block text-sm font-medium text-gray-300 mb-2">
                            CPU (%)
                            <span class="text-xs text-gray-500">(Available: {{ resources.free.cpu }})</span>
                        </label>
                        <TextInput id="cpu" v-model="cpuModel" type="number" required />
                    </div>

                    <!-- Disk -->
                    <div>
                        <label for="disk" class="block text-sm font-medium text-gray-300 mb-2">
                            Disk (MB)
                            <span class="text-xs text-gray-500">(Available: {{ resources.free.disk }})</span>
                        </label>
                        <TextInput id="disk" v-model="diskModel" type="number" required />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">
                    <!-- Databases -->
                    <div>
                        <label for="databases" class="block text-sm font-medium text-gray-300 mb-2">
                            Databases
                            <span class="text-xs text-gray-500">(Available: {{ resources.free.databases }})</span>
                        </label>
                        <TextInput id="databases" v-model="databasesModel" type="number" required />
                    </div>

                    <!-- Backups -->
                    <div>
                        <label for="backups" class="block text-sm font-medium text-gray-300 mb-2">
                            Backups
                            <span class="text-xs text-gray-500">(Available: {{ resources.free.backups }})</span>
                        </label>
                        <TextInput id="backups" v-model="backupsModel" type="number" required />
                    </div>

                    <!-- Allocations -->
                    <div>
                        <label for="allocations" class="block text-sm font-medium text-gray-300 mb-2">
                            Allocations
                            <span class="text-xs text-gray-500">(Available: {{ resources.free.allocations }})</span>
                        </label>
                        <TextInput id="allocations" v-model="allocationsModel" type="number" required />
                    </div>
                </div>
            </CardComponent>

            <!-- Submit Button -->
            <div class="flex justify-end">
                <Button type="submit" text="Create Server" :disabled="!canCreateServer" :loading="isSubmitting" />
            </div>
        </form>
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import LayoutDashboard from '@/components/client/LayoutDashboard.vue';
import CardComponent from '@/components/client/ui/Card/CardComponent.vue';
import { TextInput, TextArea, SelectInput } from '@/components/client/ui/TextForms';
import Button from '@/components/client/ui/Button.vue';
import Swal from 'sweetalert2';
import { MythicalDOM } from '@/mythicaldash/MythicalDOM';
import { useSound } from '@vueuse/sound';
import failedAlertSfx from '@/assets/sounds/error.mp3';
import successAlertSfx from '@/assets/sounds/success.mp3';

const { play: playError } = useSound(failedAlertSfx);
const { play: playSuccess } = useSound(successAlertSfx);

MythicalDOM.setPageTitle('Create Server');

const router = useRouter();
const isSubmitting = ref(false);

// Define interfaces for the API data
interface Location {
    id: number;
    name: string;
    description: string;
    pterodactyl_location_id: number;
    node_ip: string;
    status: string;
    deleted: string;
    locked: string;
    updated_at: string;
    created_at: string;
}

interface Egg {
    id: number;
    name: string;
    description: string;
    category: number;
    pterodactyl_egg_id: number;
    enabled: string;
    deleted: string;
    locked: string;
    updated_at: string;
    created_at: string;
}

interface Category {
    id: number;
    name: string;
    description: string;
    pterodactyl_nest_id: number;
    enabled: string;
    deleted: string;
    locked: string;
    updated_at: string;
    created_at: string;
    eggs: Egg[];
}

interface ResourceLimits {
    memory: number;
    cpu: number;
    disk: number;
    backups: number;
    databases: number;
    allocations: number;
    servers: number;
}

function convertToNumber(value: string): number {
    return parseInt(value);
}

function convertToString(value: number): string {
    return value.toString();
}

interface Resource {
    label: string;
    unit: string;
}

interface SelectOption {
    value: string;
    label: string;
}

// Server creation data
const locations = ref<Location[]>([]);
const categories = ref<Category[]>([]);
const availableEggs = ref<Egg[]>([]);
const resources = reactive<{
    used: ResourceLimits;
    total: ResourceLimits;
    free: ResourceLimits;
}>({
    used: {
        memory: 0,
        cpu: 0,
        disk: 0,
        backups: 0,
        databases: 0,
        allocations: 0,
        servers: 0,
    },
    total: {
        memory: 0,
        cpu: 0,
        disk: 0,
        backups: 0,
        databases: 0,
        allocations: 0,
        servers: 0,
    },
    free: {
        memory: 0,
        cpu: 0,
        disk: 0,
        backups: 0,
        databases: 0,
        allocations: 0,
        servers: 0,
    },
});

const resourceItems: Record<keyof ResourceLimits, Resource> = {
    memory: { label: 'Memory', unit: 'MB' },
    disk: { label: 'Disk Space', unit: 'MB' },
    cpu: { label: 'CPU', unit: '%' },
    databases: { label: 'Databases', unit: '' },
    backups: { label: 'Backups', unit: '' },
    allocations: { label: 'Allocations', unit: '' },
    servers: { label: 'Servers', unit: '' },
};

// Form data
const form = reactive({
    name: '',
    description: '',
    location_id: '',
    category_id: '',
    egg_id: '',
    memory: 1024,
    cpu: 100,
    disk: 1024,
    databases: 1,
    backups: 1,
    allocations: 1,
});

// Create computed properties for number inputs to handle string-number conversion
const memoryModel = computed({
    get: () => convertToString(form.memory),
    set: (value: string) => {
        form.memory = convertToNumber(value);
    },
});

const cpuModel = computed({
    get: () => convertToString(form.cpu),
    set: (value: string) => {
        form.cpu = convertToNumber(value);
    },
});

const diskModel = computed({
    get: () => convertToString(form.disk),
    set: (value: string) => {
        form.disk = convertToNumber(value);
    },
});

const databasesModel = computed({
    get: () => convertToString(form.databases),
    set: (value: string) => {
        form.databases = convertToNumber(value);
    },
});

const backupsModel = computed({
    get: () => convertToString(form.backups),
    set: (value: string) => {
        form.backups = convertToNumber(value);
    },
});

const allocationsModel = computed({
    get: () => convertToString(form.allocations),
    set: (value: string) => {
        form.allocations = convertToNumber(value);
    },
});

// Options for select inputs
const locationOptions = computed<SelectOption[]>(() => {
    return locations.value.map((location) => ({
        value: location.id.toString(),
        label: `${location.name} - ${location.status}`,
    }));
});

const categoryOptions = computed<SelectOption[]>(() => {
    return categories.value.map((category) => ({
        value: category.id.toString(),
        label: category.name,
    }));
});

const eggOptions = computed<SelectOption[]>(() => {
    return availableEggs.value.map((egg) => ({
        value: egg.id.toString(),
        label: egg.name,
    }));
});

// Load server creation data
onMounted(async () => {
    try {
        const response = await fetch('/api/user/server/create', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            },
        });

        if (!response.ok) {
            playError();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to load server creation data',
                showConfirmButton: true,
            });
            throw new Error('Failed to load server creation data');
        }

        const data = await response.json();

        if (data.success) {
            locations.value = data.locations || [];
            categories.value = data.categories || [];
            // Set resource limits
            if (data.used_resources) resources.used = data.used_resources;
            if (data.total_resources) resources.total = data.total_resources;
            if (data.free_resources) resources.free = data.free_resources;

            // Set default values based on available resources
            form.memory = Math.min(1024, resources.free.memory);
            form.cpu = Math.min(100, resources.free.cpu);
            form.disk = Math.min(1024, resources.free.disk);
            form.databases = Math.min(1, resources.free.databases);
            form.backups = Math.min(1, resources.free.backups);
            form.allocations = Math.min(1, resources.free.allocations);
        } else {
            playError();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'Failed to load server creation data',
                showConfirmButton: true,
            });
        }
    } catch (error) {
        console.error('Error loading server creation data:', error);
    }
});

// Update available eggs when category changes
const updateEggs = () => {
    const category = categories.value.find((c) => c.id === parseInt(form.category_id));
    availableEggs.value = category ? category.eggs || [] : [];
    form.egg_id = ''; // Reset egg selection
};

// Format resource display
const formatResource = (value: number, unit: string): string => {
    return unit ? `${value} ${unit}` : `${value}`;
};

// Calculate percentage for resource usage bars
const calculatePercentage = (used: number, total: number): number => {
    if (total <= 0) return 0;
    return Math.min(100, Math.round((used / total) * 100));
};

// Check if server can be created
const canCreateServer = computed(() => {
    return (
        form.name &&
        form.location_id &&
        form.category_id &&
        form.egg_id &&
        form.memory > 0 &&
        form.memory <= resources.free.memory &&
        form.cpu > 0 &&
        form.cpu <= resources.free.cpu &&
        form.disk > 0 &&
        form.disk <= resources.free.disk &&
        form.databases >= 0 &&
        form.databases <= resources.free.databases &&
        form.backups >= 0 &&
        form.backups <= resources.free.backups &&
        form.allocations > 0 &&
        form.allocations <= resources.free.allocations &&
        resources.free.servers > 0
    );
});

// Create server
const createServer = async () => {
    if (!canCreateServer.value) {
        playError();
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Cannot create server with the current configuration',
            showConfirmButton: true,
        });
        return;
    }

    isSubmitting.value = true;

    try {
        const formData = new URLSearchParams();
        formData.append('name', form.name);
        formData.append('description', form.description);
        formData.append('location_id', form.location_id);
        formData.append('category_id', form.category_id);
        formData.append('egg_id', form.egg_id);
        formData.append('memory', form.memory.toString());
        formData.append('cpu', form.cpu.toString());
        formData.append('disk', form.disk.toString());
        formData.append('databases', form.databases.toString());
        formData.append('backups', form.backups.toString());
        formData.append('allocations', form.allocations.toString());

        const response = await fetch('/api/user/server/create', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: formData,
        });

        const data = await response.json();

        if (data.success) {
            playSuccess();
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Server creation request submitted successfully please wait for it to be built.',
                showConfirmButton: true,
            }).then(() => {
                router.push('/dashboard'); // Redirect to servers list
            });
        } else {
            const errorCode = data.error_code as keyof typeof errorMessages;
            const errorMessages = {
                MISSING_REQUIRED_FIELDS: 'Missing required fields',
                NAME_TOO_LONG: 'Server name must be less than 32 characters',
                DESCRIPTION_TOO_LONG: 'Description must be less than 255 characters',
                LOCATION_DOES_NOT_EXIST: 'Selected location does not exist',
                CATEGORY_DOES_NOT_EXIST: 'Selected category does not exist',
                EGG_DOES_NOT_EXIST: 'Selected version does not exist',
                MEMORY_TOO_LOW: 'Memory must be at least 256MB',
                CPU_TOO_LOW: 'CPU must be at least 5%',
                DISK_TOO_LOW: 'Disk must be at least 256MB',
                ALLOCATIONS_TOO_LOW: 'Allocations must be at least 1',
                PENDING_SERVER_CREATION_REQUEST: 'You already have a pending server creation request',
                NOT_ENOUGH_MEMORY: 'You do not have enough memory resources',
                NOT_ENOUGH_DISK_SPACE: 'You do not have enough disk space resources',
                NOT_ENOUGH_CPU: 'You do not have enough CPU resources',
                NOT_ENOUGH_DATABASES: 'You do not have enough database resources',
                NOT_ENOUGH_BACKUPS: 'You do not have enough backup resources',
                NOT_ENOUGH_ALLOCATIONS: 'You do not have enough allocation resources',
                NOT_ENOUGH_SERVERS: 'You have reached your server limit',
                FAILED_TO_CREATE_SERVER_QUEUE_ITEM: 'Failed to create server queue item',
            };

            playError();
            Swal.fire({
                icon: 'error',
                title: 'Server Creation Failed',
                text: errorMessages[errorCode] || data.message || 'An unknown error occurred',
                footer: 'Please check your input and try again',
                showConfirmButton: true,
            });
        }
    } catch (error) {
        console.error('Error creating server:', error);
        playError();
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to create server: ' + error,
            showConfirmButton: true,
        });
    } finally {
        isSubmitting.value = false;
    }
};
</script>
