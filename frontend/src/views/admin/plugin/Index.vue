<template>
    <LayoutDashboard>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-400">Plugins</h1>
        </div>

        <div v-if="loading" class="flex justify-center items-center py-10">
            <LoaderCircle class="h-8 w-8 animate-spin text-pink-400" />
        </div>
        <div v-else>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div
                    v-for="(plugin, identifier) in plugins"
                    :key="identifier"
                    class="bg-gray-800/50 rounded-xl overflow-hidden shadow-md border border-gray-700/50 transition-all duration-300 hover:bg-gray-800/70"
                >
                    <div class="p-5">
                        <div class="flex items-center space-x-4 mb-4">
                            <img
                                :src="plugin.plugin.icon || 'https://github.com/mythicalltd.png'"
                                :alt="plugin.plugin.name"
                                class="w-12 h-12 rounded-md bg-gray-600 object-cover"
                                @error="
                                    (e: Event) => {
                                        const target = e.target as HTMLImageElement;
                                        target.src = 'https://github.com/mythicalltd.png';
                                    }
                                "
                            />
                            <div>
                                <h3 class="text-lg font-semibold text-white">{{ plugin.plugin.name }}</h3>
                                <span class="text-xs text-gray-400">{{ plugin.plugin.identifier }}</span>
                            </div>
                        </div>

                        <p class="text-gray-300 mb-4 text-sm">{{ plugin.plugin.description }}</p>

                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-400">Version:</span>
                                <span class="text-white">{{ plugin.plugin.version }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-400">Target:</span>
                                <span class="text-white">{{ plugin.plugin.target }}</span>
                            </div>
                            <div
                                class="flex justify-between text-sm"
                                v-if="plugin.plugin.author && plugin.plugin.author.length"
                            >
                                <span class="text-gray-400">Author:</span>
                                <span class="text-white">{{ plugin.plugin.author.join(', ') }}</span>
                            </div>
                        </div>

                        <div class="mt-4" v-if="plugin.plugin.flags && plugin.plugin.flags.length">
                            <div class="flex flex-wrap gap-1">
                                <span
                                    v-for="flag in plugin.plugin.flags"
                                    :key="flag"
                                    class="px-2 py-1 text-xs bg-pink-500/20 text-pink-400 rounded-full"
                                >
                                    {{ flag }}
                                </span>
                            </div>
                        </div>

                        <div class="mt-4" v-if="plugin.plugin.dependencies && plugin.plugin.dependencies.length">
                            <p class="text-sm text-gray-400 mb-2">Dependencies:</p>
                            <ul class="text-xs text-gray-300 space-y-1">
                                <li v-for="dep in plugin.plugin.dependencies" :key="dep" class="flex items-center">
                                    <ChevronRightIcon class="w-3 h-3 mr-1 text-pink-400" />
                                    {{ dep }}
                                </li>
                            </ul>
                        </div>

                        <div class="mt-6 pt-4 border-t border-gray-700/30">
                            <button
                                @click="navigateToConfig(plugin.plugin.identifier)"
                                class="w-full py-2 bg-gradient-to-r from-pink-500 to-violet-500 text-white rounded-lg transition-all duration-200 hover:opacity-90 flex items-center justify-center"
                            >
                                <SettingsIcon class="w-4 h-4 mr-2" />
                                Plugin Config
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div
                v-if="Object.keys(plugins).length === 0"
                class="flex flex-col items-center justify-center py-10 text-center"
            >
                <PackageXIcon class="h-16 w-16 text-gray-600 mb-4" />
                <h3 class="text-xl font-semibold text-gray-400">No Plugins Found</h3>
                <p class="text-gray-600 mt-2">There are no plugins installed or available.</p>
            </div>
        </div>
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { LoaderCircle, ChevronRightIcon, PackageXIcon, Settings as SettingsIcon } from 'lucide-vue-next';
import LayoutDashboard from '@/components/admin/LayoutDashboard.vue';
import Plugins from '@/mythicaldash/admin/Plugins';
import { useRouter } from 'vue-router';

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
        dependencies: string[];
    };
}

const plugins = ref<Record<string, PluginInfo>>({});
const loading = ref(true);
const router = useRouter();

const fetchPlugins = async () => {
    try {
        const data = await Plugins.getList();

        if (data.success) {
            plugins.value = data.plugins;
        } else {
            console.error('Failed to load plugins:', data.message);
        }
    } catch (error) {
        console.error('Error fetching plugins:', error);
    } finally {
        loading.value = false;
    }
};

const navigateToConfig = (identifier: string) => {
    router.push(`/plugins/${identifier}/config`);
};

onMounted(() => {
    fetchPlugins();
});
</script>
