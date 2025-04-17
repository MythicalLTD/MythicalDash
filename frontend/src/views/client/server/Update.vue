<template>
    <LayoutDashboard>
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-100 mb-2">{{ t('update.pages.index.title') }}</h1>
            <p class="text-gray-400">{{ t('update.pages.index.subTitle') }}</p>
        </div>

        <div v-if="isLoading" class="flex justify-center my-8">
            <div class="animate-pulse text-gray-300">{{ t('update.pages.index.loading') }}</div>
        </div>

        <form v-else @submit.prevent="updateServer">
            <!-- Server Details -->
            <CardComponent card-title="Server Details" class="mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Left Column - Name and Description -->
                    <div class="space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-300 mb-2">{{
                                t('update.pages.index.form.label')
                            }}</label>
                            <TextInput
                                id="name"
                                v-model="form.name"
                                :placeholder="t('update.pages.index.form.placeholder')"
                                required
                            />
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-300 mb-2">{{
                                t('update.pages.index.form.description')
                            }}</label>
                            <TextArea
                                id="description"
                                v-model="form.description"
                                :placeholder="t('update.pages.index.form.descriptionPlaceholder')"
                                :rows="3"
                            />
                        </div>
                    </div>

                    <!-- Right Column - Server Info (Read Only) -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">{{
                                t('update.pages.index.form.location')
                            }}</label>
                            <div class="p-3 bg-[#0a0a15]/70 rounded border border-[#1a1a2f]/50 text-gray-400">
                                {{ serverDetails.location?.name || t('update.pages.index.form.locationPlaceholder') }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">{{
                                t('update.pages.index.form.category')
                            }}</label>
                            <div class="p-3 bg-[#0a0a15]/70 rounded border border-[#1a1a2f]/50 text-gray-400">
                                {{ serverDetails.category?.name || t('update.pages.index.form.categoryPlaceholder') }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">{{
                                t('update.pages.index.form.egg')
                            }}</label>
                            <div class="p-3 bg-[#0a0a15]/70 rounded border border-[#1a1a2f]/50 text-gray-400">
                                {{ serverDetails.service?.name || t('update.pages.index.form.eggPlaceholder') }}
                            </div>
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
                            {{ t('update.pages.index.form.resources.memory') }} ({{
                                t('update.pages.index.form.resources.mb')
                            }})
                            <span class="text-xs text-gray-500"
                                >({{ t('update.pages.index.form.resources.available') }}:
                                {{ resources.free.memory }})</span
                            >
                        </label>
                        <TextInput id="memory" v-model="memoryModel" type="number" required />
                    </div>

                    <!-- CPU -->
                    <div>
                        <label for="cpu" class="block text-sm font-medium text-gray-300 mb-2">
                            {{ t('update.pages.index.form.resources.cpu') }} ({{
                                t('update.pages.index.form.resources.p')
                            }})
                            <span class="text-xs text-gray-500"
                                >({{ t('update.pages.index.form.resources.available') }}:
                                {{ resources.free.cpu }})</span
                            >
                        </label>
                        <TextInput id="cpu" v-model="cpuModel" type="number" required />
                    </div>

                    <!-- Disk -->
                    <div>
                        <label for="disk" class="block text-sm font-medium text-gray-300 mb-2">
                            {{ t('update.pages.index.form.resources.disk') }} ({{
                                t('update.pages.index.form.resources.mb')
                            }})
                            <span class="text-xs text-gray-500"
                                >({{ t('update.pages.index.form.resources.available') }}:
                                {{ resources.free.disk }})</span
                            >
                        </label>
                        <TextInput id="disk" v-model="diskModel" type="number" required />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">
                    <!-- Databases -->
                    <div>
                        <label for="databases" class="block text-sm font-medium text-gray-300 mb-2">
                            {{ t('update.pages.index.form.resources.databases') }}
                            <span class="text-xs text-gray-500"
                                >({{ t('update.pages.index.form.resources.available') }}:
                                {{ resources.free.databases }})</span
                            >
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
                            {{ t('update.pages.index.form.resources.allocations') }}
                            <span class="text-xs text-gray-500"
                                >({{ t('update.pages.index.form.resources.available') }}:
                                {{ resources.free.allocations }})</span
                            >
                        </label>
                        <TextInput id="allocations" v-model="allocationsModel" type="number" required />
                    </div>
                </div>
            </CardComponent>

            <!-- Submit Buttons -->
            <div class="flex justify-between">
                <Button
                    type="button"
                    :text="t('update.pages.index.form.deleteButton')"
                    variant="danger"
                    @click="showDeleteConfirmation"
                />
                <Button type="submit" :text="t('update.pages.index.form.updateButton')" :loading="isSubmitting" />
            </div>
        </form>
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import LayoutDashboard from '@/components/client/LayoutDashboard.vue';
import CardComponent from '@/components/client/ui/Card/CardComponent.vue';
import { TextInput, TextArea } from '@/components/client/ui/TextForms';
import Button from '@/components/client/ui/Button.vue';
import Swal from 'sweetalert2';
import { MythicalDOM } from '@/mythicaldash/MythicalDOM';
import { useSound } from '@vueuse/sound';
import failedAlertSfx from '@/assets/sounds/error.mp3';
import successAlertSfx from '@/assets/sounds/success.mp3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const { play: playError } = useSound(failedAlertSfx);
const { play: playSuccess } = useSound(successAlertSfx);

MythicalDOM.setPageTitle(t('update.pages.index.title'));

const router = useRouter();
const route = useRoute();
const serverId = route.params.id as string;
const isSubmitting = ref(false);
const isLoading = ref(true);

interface ResourceLimits {
    memory: number;
    cpu: number;
    disk: number;
    backups: number;
    databases: number;
    allocations: number;
    servers: number;
}

interface ServerDetails {
    id: number;
    attributes: {
        name: string;
        description: string;
        limits: {
            memory: number;
            cpu: number;
            disk: number;
        };
        feature_limits: {
            databases: number;
            backups: number;
            allocations: number;
        };
        relationships: {
            location: {
                attributes: {
                    id: number;
                };
            };
            egg: {
                attributes: {
                    id: number;
                };
            };
            nest: {
                attributes: {
                    id: number;
                };
            };
        };
    };
    location?: {
        name: string;
    };
    category?: {
        name: string;
    };
    service?: {
        name: string;
    };
}

// Server update data
const serverDetails = ref<ServerDetails>({} as ServerDetails);
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

// Form data
const form = reactive({
    name: '',
    description: '',
    memory: 0,
    cpu: 0,
    disk: 0,
    databases: 0,
    backups: 0,
    allocations: 0,
    allocation: 0, // Server allocation ID needed for update
});

function convertToNumber(value: string): number {
    return parseInt(value);
}

function convertToString(value: number): string {
    return value.toString();
}

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

// Load server data and resource info
onMounted(async () => {
    isLoading.value = true;
    try {
        // Load server data
        const serverResponse = await fetch(`/api/user/server/${serverId}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            },
        });

        if (!serverResponse.ok) {
            throw new Error('Failed to load server data');
        }

        const serverData = await serverResponse.json();

        if (serverData.success) {
            serverDetails.value = serverData.server;

            // Initialize form with server data
            form.name = serverDetails.value.attributes.name;
            form.description = serverDetails.value.attributes.description;
            form.memory = serverDetails.value.attributes.limits.memory;
            form.cpu = serverDetails.value.attributes.limits.cpu;
            form.disk = serverDetails.value.attributes.limits.disk;
            form.databases = serverDetails.value.attributes.feature_limits.databases;
            form.backups = serverDetails.value.attributes.feature_limits.backups;
            form.allocations = serverDetails.value.attributes.feature_limits.allocations;
            form.allocation = serverData.server.allocation || 0;

            // Load resource data
            const resourceResponse = await fetch('/api/user/server/create', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                },
            });

            if (!resourceResponse.ok) {
                throw new Error('Failed to load resource data');
            }

            const resourceData = await resourceResponse.json();

            if (resourceData.success) {
                // Set resource limits - add current server resources to free resources
                if (resourceData.used_resources) resources.used = resourceData.used_resources;
                if (resourceData.total_resources) resources.total = resourceData.total_resources;
                if (resourceData.free_resources) {
                    resources.free = resourceData.free_resources;
                    // Add current server resources to free resources for proper calculation
                    resources.free.memory += form.memory;
                    resources.free.cpu += form.cpu;
                    resources.free.disk += form.disk;
                    resources.free.databases += form.databases;
                    resources.free.backups += form.backups;
                    resources.free.allocations += form.allocations;
                }
            }
        } else {
            throw new Error(serverData.message || 'Failed to load server data');
        }
    } catch (error) {
        console.error('Error loading server data:', error);
        playError();
        Swal.fire({
            icon: 'error',
            title: t('update.pages.alerts.error.title'),
            text: t('update.pages.alerts.error.generic'),
            footer: t('update.pages.alerts.error.footer'),
            confirmButtonText: t('update.pages.alerts.error.confirmButtonText'),
            showConfirmButton: true,
        }).then(() => {
            router.push('/dashboard');
        });
    } finally {
        isLoading.value = false;
    }
});

// Update server
const updateServer = async () => {
    isSubmitting.value = true;

    try {
        const formData = new URLSearchParams();
        formData.append('name', form.name);
        formData.append('description', form.description);
        formData.append('memory', form.memory.toString());
        formData.append('cpu', form.cpu.toString());
        formData.append('disk', form.disk.toString());
        formData.append('databases', form.databases.toString());
        formData.append('backups', form.backups.toString());
        formData.append('allocations', form.allocations.toString());

        const response = await fetch(`/api/user/server/${serverId}/update`, {
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
                title: t('update.pages.alerts.success.title'),
                text: t('update.pages.alerts.success.generic'),
                footer: t('update.pages.alerts.success.footer'),
                confirmButtonText: t('update.pages.alerts.success.confirmButtonText'),
                showConfirmButton: true,
            }).then(() => {
                router.push('/dashboard');
            });
        } else {
            playError();
            Swal.fire({
                icon: 'error',
                title: t('update.pages.alerts.error.title'),
                text: data.message || t('update.pages.alerts.error.generic'),
                footer: t('update.pages.alerts.error.footer'),
                confirmButtonText: t('update.pages.alerts.error.confirmButtonText'),
                showConfirmButton: true,
            });
        }
    } catch (error) {
        console.error('Error updating server:', error);
        playError();
        Swal.fire({
            icon: 'error',
            title: t('update.pages.alerts.error.title'),
            text: t('update.pages.alerts.error.generic'),
            footer: t('update.pages.alerts.error.footer'),
            confirmButtonText: t('update.pages.alerts.error.confirmButtonText'),
            showConfirmButton: true,
        });
    } finally {
        isSubmitting.value = false;
    }
};

// Show delete confirmation
const showDeleteConfirmation = () => {
    Swal.fire({
        title: t('update.pages.alerts.error.title'),
        text: t('update.pages.alerts.error.delete'),
        footer: t('update.pages.alerts.error.footer'),
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: t('update.pages.alerts.error.confirmButtonText'),
        confirmButtonColor: '#ef4444',
        cancelButtonText: t('update.pages.alerts.error.cancelButtonText'),
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed) {
            deleteServer();
        }
    });
};

// Delete server
const deleteServer = async () => {
    isSubmitting.value = true;

    try {
        const response = await fetch(`/api/user/server/${serverId}/delete`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
        });

        const data = await response.json();

        if (data.success) {
            playSuccess();
            Swal.fire({
                icon: 'success',
                title: t('update.pages.alerts.success.title'),
                text: t('update.pages.alerts.success.generic'),
                footer: t('update.pages.alerts.success.footer'),
                confirmButtonText: t('update.pages.alerts.success.confirmButtonText'),
                showConfirmButton: true,
            }).then(() => {
                router.push('/dashboard');
            });
        } else {
            playError();
            Swal.fire({
                icon: 'error',
                title: t('update.pages.alerts.error.title'),
                text: data.message || t('update.pages.alerts.error.generic'),
                footer: t('update.pages.alerts.error.footer'),
                confirmButtonText: t('update.pages.alerts.error.confirmButtonText'),
                showConfirmButton: true,
            });
        }
    } catch (error) {
        console.error('Error deleting server:', error);
        playError();
        Swal.fire({
            icon: 'error',
            title: t('update.pages.alerts.error.title'),
            text: t('update.pages.alerts.error.generic'),
            footer: t('update.pages.alerts.error.footer'),
            confirmButtonText: t('update.pages.alerts.error.confirmButtonText'),
            showConfirmButton: true,
        });
    } finally {
        isSubmitting.value = false;
    }
};
</script>
