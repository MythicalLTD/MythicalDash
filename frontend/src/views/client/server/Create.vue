<template>
    <LayoutDashboard>
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-100 mb-2">{{ t('create.pages.index.title') }}</h1>
            <p class="text-gray-400">{{ t('create.pages.index.subTitle') }}</p>
        </div>

        <!-- Resource Overview -->
        <CardComponent :card-title="t('create.pages.index.resources.title')" class="mb-6">
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
                            {{
                                resources.free[key] > 0
                                    ? t('create.pages.index.resources.available')
                                    : t('create.pages.index.resources.depleted')
                            }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-200 font-medium">{{
                            formatResource(resources.free[key], resource.unit)
                        }}</span>
                        <span class="text-gray-500 text-xs"
                            >{{ t('create.pages.index.resources.of') }}
                            {{ formatResource(resources.total[key], resource.unit) }}</span
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
                            <label for="name" class="block text-sm font-medium text-gray-300 mb-2">{{
                                t('create.pages.index.form.label')
                            }}</label>
                            <TextInput
                                id="name"
                                v-model="form.name"
                                :placeholder="t('create.pages.index.form.placeholder')"
                                required
                            />
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-300 mb-2">{{
                                t('create.pages.index.form.description')
                            }}</label>
                            <TextArea
                                id="description"
                                v-model="form.description"
                                :placeholder="t('create.pages.index.form.descriptionPlaceholder')"
                                :rows="3"
                            />
                        </div>
                    </div>

                    <!-- Right Column - Location and Type -->
                    <div class="space-y-4">
                        <div>
                            <label for="location" class="block text-sm font-medium text-gray-300 mb-2">{{
                                t('create.pages.index.form.location')
                            }}</label>
                            <SelectInput
                                id="location"
                                v-model="form.location_id"
                                :options="locationOptions"
                                :placeholder="t('create.pages.index.form.locationPlaceholder')"
                            />
                        </div>

                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-300 mb-2">{{
                                t('create.pages.index.form.category')
                            }}</label>
                            <SelectInput
                                id="category"
                                v-model="form.category_id"
                                :options="categoryOptions"
                                :placeholder="t('create.pages.index.form.categoryPlaceholder')"
                                @update:modelValue="updateEggs"
                            />
                        </div>

                        <div>
                            <label for="egg" class="block text-sm font-medium text-gray-300 mb-2">{{
                                t('create.pages.index.form.egg')
                            }}</label>
                            <SelectInput
                                id="egg"
                                v-model="form.egg_id"
                                :options="eggOptions"
                                :placeholder="t('create.pages.index.form.eggPlaceholder')"
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
                            {{ t('create.pages.index.resources.memory') }} ({{ t('create.pages.index.resources.mb') }})
                            <span class="text-xs text-gray-500"
                                >({{ t('create.pages.index.form.available') }}: {{ resources.free.memory }})</span
                            >
                        </label>
                        <TextInput id="memory" v-model="memoryModel" type="number" required />
                    </div>

                    <!-- CPU -->
                    <div>
                        <label for="cpu" class="block text-sm font-medium text-gray-300 mb-2">
                            {{ t('create.pages.index.resources.cpu') }} ({{ t('create.pages.index.resources.p') }})
                            <span class="text-xs text-gray-500"
                                >({{ t('create.pages.index.form.available') }}: {{ resources.free.cpu }})</span
                            >
                        </label>
                        <TextInput id="cpu" v-model="cpuModel" type="number" required />
                    </div>

                    <!-- Disk -->
                    <div>
                        <label for="disk" class="block text-sm font-medium text-gray-300 mb-2">
                            {{ t('create.pages.index.resources.disk') }} ({{ t('create.pages.index.resources.mb') }})
                            <span class="text-xs text-gray-500"
                                >({{ t('create.pages.index.form.available') }}: {{ resources.free.disk }})</span
                            >
                        </label>
                        <TextInput id="disk" v-model="diskModel" type="number" required />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">
                    <!-- Databases -->
                    <div>
                        <label for="databases" class="block text-sm font-medium text-gray-300 mb-2">
                            {{ t('create.pages.index.resources.databases') }}
                            <span class="text-xs text-gray-500"
                                >({{ t('create.pages.index.form.available') }}: {{ resources.free.databases }})</span
                            >
                        </label>
                        <TextInput id="databases" v-model="databasesModel" type="number" required />
                    </div>

                    <!-- Backups -->
                    <div>
                        <label for="backups" class="block text-sm font-medium text-gray-300 mb-2">
                            {{ t('create.pages.index.resources.backups') }}
                            <span class="text-xs text-gray-500"
                                >({{ t('create.pages.index.form.available') }}: {{ resources.free.backups }})</span
                            >
                        </label>
                        <TextInput id="backups" v-model="backupsModel" type="number" required />
                    </div>

                    <!-- Allocations -->
                    <div>
                        <label for="allocations" class="block text-sm font-medium text-gray-300 mb-2">
                            {{ t('create.pages.index.resources.allocations') }}
                            <span class="text-xs text-gray-500"
                                >({{ t('create.pages.index.form.available') }}: {{ resources.free.allocations }})</span
                            >
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
import { useI18n } from 'vue-i18n';
import { useSettingsStore } from '@/stores/settings';

const { t } = useI18n();
const Settings = useSettingsStore();

const { play: playError } = useSound(failedAlertSfx);
const { play: playSuccess } = useSound(successAlertSfx);

MythicalDOM.setPageTitle(t('create.pages.index.title'));

const router = useRouter();
const isSubmitting = ref(false);

// Define interfaces for the API data
interface Location {
    id: number;
    name: string;
    description: string;
    slots: number;
    used_slots: number;
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
    memory: { label: t('create.pages.index.resources.memory'), unit: t('create.pages.index.resources.mb') },
    disk: { label: t('create.pages.index.resources.disk'), unit: t('create.pages.index.resources.mb') },
    cpu: { label: t('create.pages.index.resources.cpu'), unit: t('create.pages.index.resources.p') },
    databases: { label: t('create.pages.index.resources.databases'), unit: '' },
    backups: { label: t('create.pages.index.resources.backups'), unit: '' },
    allocations: { label: t('create.pages.index.resources.allocations'), unit: '' },
    servers: { label: t('create.pages.index.resources.servers'), unit: '' },
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
        label:
            `${location.name} - ${location.status} (${location.used_slots}/${location.slots} ` +
            t('create.pages.index.slots') +
            `) [${location.slots - location.used_slots} ${t('create.pages.index.form.available')}]`,
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
                title: t('create.pages.alerts.error.title'),
                text: t('create.pages.alerts.error.generic'),
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
                title: t('create.pages.alerts.error.title'),
                text: data.message || t('create.pages.alerts.error.generic'),
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
        resources.free.servers > 0 &&
        Settings.getSetting('allow_servers') === 'true' &&
        (() => {
            const location = locations.value.find((location) => location.id === parseInt(form.location_id));
            return location ? location.used_slots < location.slots : false;
        })()
    );
});

// Create server
const createServer = async () => {
    if (!canCreateServer.value) {
        playError();
        Swal.fire({
            icon: 'error',
            title: t('create.pages.alerts.error.title'),
            text: t('create.pages.alerts.error.generic'),
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
                title: t('create.pages.alerts.success.title'),
                text: t('create.pages.alerts.success.generic'),
                footer: t('create.pages.alerts.success.footer'),
                showConfirmButton: true,
            }).then(() => {
                router.push('/dashboard'); // Redirect to servers list
            });
        } else {
            const errorCode = data.error_code as keyof typeof errorMessages;
            const errorMessages = {
                MISSING_REQUIRED_FIELDS: t('create.pages.alerts.error.deploy.MISSING_REQUIRED_FIELDS'),
                NAME_TOO_LONG: t('create.pages.alerts.error.deploy.NAME_TOO_LONG'),
                DESCRIPTION_TOO_LONG: t('create.pages.alerts.error.deploy.DESCRIPTION_TOO_LONG'),
                LOCATION_DOES_NOT_EXIST: t('create.pages.alerts.error.deploy.LOCATION_DOES_NOT_EXIST'),
                CATEGORY_DOES_NOT_EXIST: t('create.pages.alerts.error.deploy.CATEGORY_DOES_NOT_EXIST'),
                EGG_DOES_NOT_EXIST: t('create.pages.alerts.error.deploy.EGG_DOES_NOT_EXIST'),
                MEMORY_TOO_LOW: t('create.pages.alerts.error.deploy.MEMORY_TOO_LOW'),
                CPU_TOO_LOW: t('create.pages.alerts.error.deploy.CPU_TOO_LOW'),
                DISK_TOO_LOW: t('create.pages.alerts.error.deploy.DISK_TOO_LOW'),
                ALLOCATIONS_TOO_LOW: t('create.pages.alerts.error.deploy.ALLOCATIONS_TOO_LOW'),
                PENDING_SERVER_CREATION_REQUEST: 'You already have a pending server creation request',
                NOT_ENOUGH_MEMORY: 'You do not have enough memory resources',
                NOT_ENOUGH_DISK_SPACE: t('create.pages.alerts.error.deploy.NOT_ENOUGH_DISK_SPACE'),
                NOT_ENOUGH_CPU: t('create.pages.alerts.error.deploy.NOT_ENOUGH_CPU'),
                NOT_ENOUGH_DATABASES: t('create.pages.alerts.error.deploy.NOT_ENOUGH_DATABASES'),
                NOT_ENOUGH_BACKUPS: t('create.pages.alerts.error.deploy.NOT_ENOUGH_BACKUPS'),
                NOT_ENOUGH_ALLOCATIONS: t('create.pages.alerts.error.deploy.NOT_ENOUGH_ALLOCATIONS'),
                NOT_ENOUGH_SERVERS: t('create.pages.alerts.error.deploy.NOT_ENOUGH_SERVERS'),
                FAILED_TO_CREATE_SERVER_QUEUE_ITEM: t(
                    'create.pages.alerts.error.deploy.FAILED_TO_CREATE_SERVER_QUEUE_ITEM',
                ),
            };

            playError();
            Swal.fire({
                icon: 'error',
                title: t('create.pages.alerts.error.title'),
                text: errorMessages[errorCode] || data.message || t('create.pages.alerts.error.generic'),
                footer: t('create.pages.alerts.error.footer'),
                showConfirmButton: true,
            });
        }
    } catch (error) {
        console.error('Error creating server:', error);
        playError();
        Swal.fire({
            icon: 'error',
            title: t('create.pages.alerts.error.title'),
            text: t('create.pages.alerts.error.generic'),
            footer: t('create.pages.alerts.error.footer'),
            showConfirmButton: true,
        });
    } finally {
        isSubmitting.value = false;
    }
};
</script>
