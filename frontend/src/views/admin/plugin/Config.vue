<template>
    <LayoutDashboard>
        <div class="flex items-center mb-6">
            <button @click="goBack" class="mr-3 p-2 bg-gray-800 rounded-lg hover:bg-gray-700/50 transition-colors">
                <ArrowLeftIcon class="w-5 h-5 text-gray-400" />
            </button>
            <h1 class="text-2xl font-bold text-pink-400">Plugin Configuration</h1>
        </div>

        <div v-if="loading" class="flex justify-center items-center py-10">
            <LoaderCircle class="h-8 w-8 animate-spin text-pink-400" />
        </div>
        <div v-else-if="error" class="bg-red-500/20 text-red-400 p-4 rounded-lg">
            {{ error }}
        </div>
        <div v-else>
            <div class="bg-gray-800/50 rounded-xl overflow-hidden shadow-md border border-gray-700/50 p-6 mb-6">
                <div class="flex items-center mb-4">
                    <img
                        :src="plugin?.plugin?.icon || 'https://github.com/mythicalltd.png'"
                        :alt="plugin?.plugin?.name"
                        class="w-12 h-12 rounded-md bg-gray-600 object-cover mr-4"
                        @error="
                            (e: Event) => {
                                const target = e.target as HTMLImageElement;
                                target.src = 'https://github.com/mythicalltd.png';
                            }
                        "
                    />
                    <div>
                        <h2 class="text-xl font-semibold text-white">{{ plugin?.plugin?.name || 'Plugin' }}</h2>
                        <p class="text-sm text-gray-400">{{ plugin?.plugin?.identifier }}</p>
                    </div>
                </div>
                <p class="text-gray-300 mb-4">{{ plugin?.plugin?.description }}</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div class="bg-gray-700/30 rounded-lg p-3">
                        <span class="text-gray-400">Version:</span>
                        <span class="text-white ml-2">{{ plugin?.plugin?.version }}</span>
                    </div>
                    <div class="bg-gray-700/30 rounded-lg p-3">
                        <span class="text-gray-400">Target:</span>
                        <span class="text-white ml-2">{{ plugin?.plugin?.target }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-gray-800/50 rounded-xl overflow-hidden shadow-md border border-gray-700/50 mb-6">
                <div class="border-b border-gray-700/50 p-4">
                    <h3 class="text-lg font-medium text-white">Configuration Options</h3>
                    <p class="text-sm text-gray-400">Manage settings for this plugin</p>
                    <div v-if="hasMissingRequiredConfigs" class="mt-2 p-2 bg-red-500/20 rounded-lg">
                        <p class="text-red-400 text-sm">
                            ⚠️ Required configurations are missing. Please configure them before proceeding.
                        </p>
                    </div>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 gap-6">
                        <div class="flex flex-col">
                            <div class="flex justify-between items-center mb-4">
                                <button
                                    @click="showAddConfigModal = true"
                                    class="flex items-center text-white bg-emerald-600 hover:bg-emerald-700 px-4 py-2 rounded-lg transition-all duration-200 hover:scale-105"
                                >
                                    <PlusIcon class="w-4 h-4 mr-2" />
                                    Add Config
                                </button>
                            </div>

                            <div v-if="Object.keys(settings).length === 0" class="bg-gray-700/30 rounded-lg p-4 mb-4">
                                <p class="text-center text-gray-400 text-sm">
                                    No configuration entries found. Click "Add Config" to create one.
                                </p>
                            </div>

                            <div v-else class="bg-gray-700/30 rounded-lg divide-y divide-gray-700/50">
                                <div
                                    v-for="(value, key) in settings"
                                    :key="key"
                                    class="p-4 flex items-center justify-between hover:bg-gray-600/30 transition-colors duration-200"
                                >
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h4 class="text-white font-medium">{{ key }}</h4>
                                            <span
                                                v-if="isRequiredConfig(key)"
                                                class="px-2 py-0.5 text-xs bg-pink-500/20 text-pink-400 rounded-full"
                                            >
                                                Required
                                            </span>
                                        </div>
                                        <p class="text-gray-400 text-sm">{{ value }}</p>
                                    </div>
                                    <div class="flex space-x-2">
                                        <button
                                            @click="editSetting(key, value)"
                                            class="p-2 text-blue-400 hover:text-blue-300 bg-blue-400/10 rounded-lg transition-colors duration-200 hover:bg-blue-400/20"
                                            title="Edit setting"
                                        >
                                            <EditIcon class="w-4 h-4" />
                                        </button>
                                        <button
                                            v-if="!isRequiredConfig(key)"
                                            @click="removeSetting(key)"
                                            class="p-2 text-red-400 hover:text-red-300 bg-red-400/10 rounded-lg transition-colors duration-200 hover:bg-red-400/20"
                                            title="Remove setting"
                                        >
                                            <TrashIcon class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-gray-800/50 rounded-xl overflow-hidden shadow-md border border-gray-700/50">
                <div class="border-b border-gray-700/50 p-4">
                    <h3 class="text-lg font-medium text-white">Plugin Information</h3>
                    <p class="text-sm text-gray-400">Technical details about this plugin</p>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div class="bg-gray-700/30 rounded-lg p-4">
                            <h4 class="text-white font-medium mb-2">Flags</h4>
                            <div class="flex flex-wrap gap-2">
                                <span
                                    v-for="flag in plugin?.plugin?.flags"
                                    :key="flag"
                                    class="px-2 py-1 text-xs bg-pink-500/20 text-pink-400 rounded-full"
                                >
                                    {{ flag }}
                                </span>
                                <span v-if="!plugin?.plugin?.flags?.length" class="text-gray-400 text-sm">
                                    No flags
                                </span>
                            </div>
                        </div>

                        <div class="bg-gray-700/30 rounded-lg p-4">
                            <h4 class="text-white font-medium mb-2">Authors</h4>
                            <div class="flex flex-wrap gap-2">
                                <span
                                    v-for="author in plugin?.plugin?.author"
                                    :key="author"
                                    class="px-2 py-1 text-xs bg-violet-500/20 text-violet-400 rounded-full"
                                >
                                    {{ author }}
                                </span>
                                <span v-if="!plugin?.plugin?.author?.length" class="text-gray-400 text-sm">
                                    No authors specified
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-700/30 rounded-lg p-4">
                        <h4 class="text-white font-medium mb-2">Dependencies</h4>
                        <ul class="text-sm space-y-1">
                            <li v-for="dep in plugin?.plugin?.dependencies" :key="dep" class="flex items-center">
                                <ChevronRightIcon class="w-3 h-3 mr-1 text-pink-400" />
                                <span class="text-gray-300">{{ dep }}</span>
                            </li>
                            <li v-if="!plugin?.plugin?.dependencies?.length" class="text-gray-400">
                                No dependencies required
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add/Edit Config Modal -->
        <div v-if="showAddConfigModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-gray-800 rounded-lg p-6 w-full max-w-md">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-white">{{ editingKey ? 'Edit' : 'Add' }} Configuration</h3>
                    <button @click="closeModal" class="text-gray-400 hover:text-gray-300">
                        <XIcon class="w-5 h-5" />
                    </button>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-1">Key</label>
                        <input
                            v-model="newConfig.key"
                            type="text"
                            class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-pink-500 transition-colors duration-200"
                            :disabled="!!editingKey"
                            placeholder="Enter setting key"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-400 mb-1">Value</label>
                        <input
                            v-model="newConfig.value"
                            type="text"
                            class="w-full bg-gray-700 border border-gray-600 rounded-lg px-3 py-2 text-white focus:outline-none focus:ring-2 focus:ring-pink-500 transition-colors duration-200"
                            placeholder="Enter setting value"
                        />
                    </div>
                </div>

                <div class="flex justify-end space-x-3 mt-6">
                    <button
                        @click="closeModal"
                        class="px-4 py-2 text-gray-400 hover:text-gray-300 rounded-lg transition-colors duration-200"
                    >
                        Cancel
                    </button>
                    <button
                        @click="saveConfig"
                        class="px-4 py-2 bg-pink-500 text-white rounded-lg hover:bg-pink-600 transition-colors duration-200"
                    >
                        {{ editingKey ? 'Update' : 'Add' }}
                    </button>
                </div>
            </div>
        </div>
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import {
    LoaderCircle,
    ChevronRight as ChevronRightIcon,
    ArrowLeft as ArrowLeftIcon,
    Plus as PlusIcon,
    Edit as EditIcon,
    Trash as TrashIcon,
    X as XIcon,
} from 'lucide-vue-next';
import Swal from 'sweetalert2';
import LayoutDashboard from '@/components/admin/LayoutDashboard.vue';
import Plugins from '@/mythicaldash/admin/Plugins';

interface PluginInfo {
    plugin: {
        name: string;
        identifier: string;
        description: string;
        flags: string[];
        version: string;
        target: string;
        author: string[];
        icon: string;
        requiredConfigs: string[];
        dependencies: string[];
    };
}

interface Config {
    key: string;
    value: string;
}

const route = useRoute();
const router = useRouter();
const plugin = ref<PluginInfo | null>(null);
const settings = ref<Record<string, string>>({});
const loading = ref(true);
const error = ref<string | null>(null);
const showAddConfigModal = ref(false);
const editingKey = ref<string | null>(null);
const newConfig = ref<Config>({ key: '', value: '' });
const hasMissingRequiredConfigs = ref(false);

const isRequiredConfig = (key: string) => {
    return plugin.value?.plugin?.requiredConfigs?.includes(key) || false;
};

const checkRequiredConfigs = () => {
    if (!plugin.value?.plugin?.requiredConfigs) return false;
    return plugin.value.plugin.requiredConfigs.some((config) => !(config in settings.value));
};

const fetchPluginDetails = async () => {
    const identifier = route.params.identifier as string;

    try {
        const data = await Plugins.getConfig(identifier);

        if (data.success) {
            plugin.value = data.plugin;
            settings.value = data.settings;
            hasMissingRequiredConfigs.value = checkRequiredConfigs();

            if (hasMissingRequiredConfigs.value && plugin.value?.plugin?.requiredConfigs) {
                await Swal.fire({
                    title: 'Configuration Required',
                    html: `
                        <div class="text-left">
                            <p class="mb-4">Hello! Let's start by configuring this plugin.</p>
                            <p class="text-gray-400">The following required configurations are missing:</p>
                            <ul class="list-disc list-inside mt-2 text-gray-300">
                                ${plugin.value.plugin.requiredConfigs
                                    .filter((config) => !(config in settings.value))
                                    .map((config) => `<li>${config}</li>`)
                                    .join('')}
                            </ul>
                        </div>
                    `,
                    icon: 'info',
                    confirmButtonText: 'Start Configuring',
                    background: '#1f2937',
                    color: '#fff',
                });
                showAddConfigModal.value = true;
            }
        } else {
            error.value = `Plugin with identifier '${identifier}' not found`;
        }
    } catch (err) {
        error.value = 'Failed to load plugin details';
        console.error(err);
    } finally {
        loading.value = false;
    }
};

const goBack = async () => {
    if (hasMissingRequiredConfigs.value) {
        const result = await Swal.fire({
            title: 'Required Configurations Missing',
            text: 'You must configure all required settings before leaving this page.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Stay and Configure',
            cancelButtonText: 'Leave Anyway',
            background: '#1f2937',
            color: '#fff',
        });

        if (result.isConfirmed) {
            showAddConfigModal.value = true;
            return;
        }
    }
    router.push('/mc-admin/plugins');
};

const editSetting = (key: string, value: string) => {
    editingKey.value = key;
    newConfig.value = { key, value };
    showAddConfigModal.value = true;
};

const removeSetting = async (key: string) => {
    if (isRequiredConfig(key)) {
        await Swal.fire({
            title: 'Cannot Remove Required Setting',
            text: 'This setting is required and cannot be removed.',
            icon: 'error',
            background: '#1f2937',
            color: '#fff',
        });
        return;
    }

    const result = await Swal.fire({
        title: 'Are you sure?',
        text: `This will permanently remove the setting "${key}"`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, remove it!',
        cancelButtonText: 'Cancel',
        background: '#1f2937',
        color: '#fff',
    });

    if (result.isConfirmed) {
        try {
            const identifier = route.params.identifier as string;
            await Plugins.removeSetting(identifier, key);
            delete settings.value[key];
            hasMissingRequiredConfigs.value = checkRequiredConfigs();

            await Swal.fire({
                title: 'Success!',
                text: 'Setting has been removed.',
                icon: 'success',
                background: '#1f2937',
                color: '#fff',
            });
        } catch (err) {
            await Swal.fire({
                title: 'Error!',
                text: 'Failed to remove setting.',
                icon: 'error',
                background: '#1f2937',
                color: '#fff',
            });
            console.error('Failed to remove setting:', err);
        }
    }
};

const saveConfig = async () => {
    if (!newConfig.value.key || !newConfig.value.value) {
        await Swal.fire({
            title: 'Error!',
            text: 'Please fill in both key and value fields.',
            icon: 'error',
            background: '#1f2937',
            color: '#fff',
        });
        return;
    }

    try {
        const identifier = route.params.identifier as string;
        await Plugins.setSetting(identifier, newConfig.value.key, newConfig.value.value);

        if (editingKey.value) {
            delete settings.value[editingKey.value];
        }
        settings.value[newConfig.value.key] = newConfig.value.value;
        hasMissingRequiredConfigs.value = checkRequiredConfigs();

        await Swal.fire({
            title: 'Success!',
            text: `Setting has been ${editingKey.value ? 'updated' : 'added'} successfully.`,
            icon: 'success',
            background: '#1f2937',
            color: '#fff',
        });

        closeModal();
    } catch (err) {
        await Swal.fire({
            title: 'Error!',
            text: 'Failed to save setting.',
            icon: 'error',
            background: '#1f2937',
            color: '#fff',
        });
        console.error('Failed to save setting:', err);
    }
};

const closeModal = () => {
    showAddConfigModal.value = false;
    editingKey.value = null;
    newConfig.value = { key: '', value: '' };
};

onMounted(() => {
    fetchPluginDetails();
});
</script>
