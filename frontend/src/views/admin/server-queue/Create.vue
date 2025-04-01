<template>
    <LayoutDashboard>
        <div class="flex items-center mb-6">
            <button @click="router.back()" class="mr-4 text-gray-400 hover:text-white transition-colors">
                <ArrowLeftIcon class="h-5 w-5" />
            </button>
            <h1 class="text-2xl font-bold text-pink-400">Add Server to Queue</h1>
        </div>

        <div class="bg-gray-800/50 backdrop-blur-md rounded-lg p-6 shadow-md">
            <form @submit.prevent="saveServer" class="space-y-6">
                <!-- Server Details -->
                <div>
                    <h2 class="text-lg font-medium text-white mb-4">Server Details</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-300 mb-1">Server Name</label>
                            <input
                                id="name"
                                v-model="serverForm.name"
                                type="text"
                                required
                                class="w-full bg-gray-700 border border-gray-600 rounded-md py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-pink-500"
                                placeholder="Enter server name"
                            />
                        </div>

                        <div>
                            <label for="user" class="block text-sm font-medium text-gray-300 mb-1">User</label>
                            <select
                                id="user"
                                v-model="serverForm.user"
                                required
                                class="w-full bg-gray-700 border border-gray-600 rounded-md py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-pink-500"
                            >
                                <option value="" disabled>Select a user</option>
                                <option v-for="user in users" :key="user.uuid" :value="user.uuid">
                                    {{ user.username }} ({{ user.email }})
                                </option>
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label for="description" class="block text-sm font-medium text-gray-300 mb-1"
                                >Description</label
                            >
                            <textarea
                                id="description"
                                v-model="serverForm.description"
                                rows="3"
                                class="w-full bg-gray-700 border border-gray-600 rounded-md py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-pink-500"
                                placeholder="Enter server description"
                            ></textarea>
                        </div>
                    </div>
                </div>

                <!-- Server Resources -->
                <div>
                    <h2 class="text-lg font-medium text-white mb-4">Server Resources</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="ram" class="block text-sm font-medium text-gray-300 mb-1">RAM (MB)</label>
                            <input
                                id="ram"
                                v-model.number="serverForm.ram"
                                type="number"
                                min="256"
                                required
                                class="w-full bg-gray-700 border border-gray-600 rounded-md py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-pink-500"
                            />
                        </div>

                        <div>
                            <label for="cpu" class="block text-sm font-medium text-gray-300 mb-1">CPU (%)</label>
                            <input
                                id="cpu"
                                v-model.number="serverForm.cpu"
                                type="number"
                                min="5"
                                max="400"
                                required
                                class="w-full bg-gray-700 border border-gray-600 rounded-md py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-pink-500"
                            />
                        </div>

                        <div>
                            <label for="disk" class="block text-sm font-medium text-gray-300 mb-1">Disk (MB)</label>
                            <input
                                id="disk"
                                v-model.number="serverForm.disk"
                                type="number"
                                min="1024"
                                required
                                class="w-full bg-gray-700 border border-gray-600 rounded-md py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-pink-500"
                            />
                        </div>

                        <div>
                            <label for="ports" class="block text-sm font-medium text-gray-300 mb-1">Ports</label>
                            <input
                                id="ports"
                                v-model.number="serverForm.ports"
                                type="number"
                                min="1"
                                required
                                class="w-full bg-gray-700 border border-gray-600 rounded-md py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-pink-500"
                            />
                        </div>

                        <div>
                            <label for="databases" class="block text-sm font-medium text-gray-300 mb-1"
                                >Databases</label
                            >
                            <input
                                id="databases"
                                v-model.number="serverForm.databases"
                                type="number"
                                min="0"
                                required
                                class="w-full bg-gray-700 border border-gray-600 rounded-md py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-pink-500"
                            />
                        </div>

                        <div>
                            <label for="backups" class="block text-sm font-medium text-gray-300 mb-1">Backups</label>
                            <input
                                id="backups"
                                v-model.number="serverForm.backups"
                                type="number"
                                min="0"
                                required
                                class="w-full bg-gray-700 border border-gray-600 rounded-md py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-pink-500"
                            />
                        </div>
                    </div>
                </div>

                <!-- Server Location and Type -->
                <div>
                    <h2 class="text-lg font-medium text-white mb-4">Server Location and Type</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="location" class="block text-sm font-medium text-gray-300 mb-1">Location</label>
                            <select
                                id="location"
                                v-model="serverForm.location"
                                required
                                class="w-full bg-gray-700 border border-gray-600 rounded-md py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-pink-500"
                            >
                                <option value="" disabled>Select a location</option>
                                <option v-for="location in locations" :key="location.id" :value="location.id">
                                    {{ location.name }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label for="nest" class="block text-sm font-medium text-gray-300 mb-1">Nest</label>
                            <select
                                id="nest"
                                v-model="serverForm.nest"
                                required
                                class="w-full bg-gray-700 border border-gray-600 rounded-md py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-pink-500"
                                @change="loadEggs"
                            >
                                <option value="" disabled>Select a nest</option>
                                <option v-for="category in categories" :key="category.id" :value="category.id">
                                    {{ category.name }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label for="egg" class="block text-sm font-medium text-gray-300 mb-1">Egg</label>
                            <select
                                id="egg"
                                v-model="serverForm.egg"
                                required
                                class="w-full bg-gray-700 border border-gray-600 rounded-md py-2 px-3 text-white focus:outline-none focus:ring-2 focus:ring-pink-500"
                                :disabled="!serverForm.nest || eggs.length === 0"
                            >
                                <option value="" disabled>
                                    {{ eggs.length === 0 ? 'Select a nest first' : 'Select an egg' }}
                                </option>
                                <option v-for="egg in eggs" :key="egg.id" :value="egg.id">
                                    {{ egg.name }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-700">
                    <button
                        type="button"
                        @click="router.push('/mc-admin/server-queue')"
                        class="px-4 py-2 border border-gray-600 rounded-lg text-gray-300 hover:bg-gray-700 transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        :disabled="loading"
                        class="px-4 py-2 bg-gradient-to-r from-pink-500 to-violet-500 rounded-lg text-white hover:opacity-90 transition-colors flex items-center"
                    >
                        <LoaderIcon v-if="loading" class="animate-spin w-4 h-4 mr-2" />
                        <SaveIcon v-else class="w-4 h-4 mr-2" />
                        Add to Queue
                    </button>
                </div>
            </form>
        </div>
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import LayoutDashboard from '@/components/admin/LayoutDashboard.vue';
import { ArrowLeftIcon, SaveIcon, LoaderIcon } from 'lucide-vue-next';
import ServerQueue from '@/mythicaldash/admin/ServerQueue';
import EggCategories from '@/mythicaldash/admin/EggCategories';
import Eggs from '@/mythicaldash/admin/Eggs';
import Users from '@/mythicaldash/admin/Users';
import Swal from 'sweetalert2';
import { useSound } from '@vueuse/sound';
import failedAlertSfx from '@/assets/sounds/error.mp3';
import successAlertSfx from '@/assets/sounds/success.mp3';

const router = useRouter();
const loading = ref(false);
const { play: playError } = useSound(failedAlertSfx);
const { play: playSuccess } = useSound(successAlertSfx);

// Form state
const serverForm = ref({
    name: '',
    description: '',
    ram: 1024,
    disk: 5120,
    cpu: 100,
    ports: 1,
    databases: 1,
    backups: 1,
    location: '',
    user: '',
    nest: '',
    egg: '',
});

// Data for selects
interface Location {
    id: number;
    name: string;
    description: string;
    pterodactyl_location_id: number;
    node_ip: string;
    status: string;
}

interface Category {
    id: number;
    name: string;
    description: string;
    pterodactyl_nest_id: string;
    enabled: string;
}

interface Egg {
    id: number;
    name: string;
    description: string;
    category: string;
    pterodactyl_egg_id: string;
    enabled: string;
}

interface User {
    id: number;
    username: string;
    email: string;
    uuid: string;
}

const locations = ref<Location[]>([]);
const categories = ref<Category[]>([]);
const eggs = ref<Egg[]>([]);
const users = ref<User[]>([]);

// Load locations
const loadLocations = async () => {
    try {
        const response = await fetch('/api/admin/locations', {
            method: 'GET',
        });
        const data = await response.json();

        if (data.success) {
            locations.value = data.locations;
        } else {
            console.error('Failed to load locations:', data.message);
        }
    } catch (error) {
        console.error('Error loading locations:', error);
    }
};

// Load categories (nests)
const loadCategories = async () => {
    try {
        const response = await EggCategories.getCategories();
        if (response.success) {
            categories.value = response.categories;
        } else {
            console.error('Failed to load categories:', response.message);
        }
    } catch (error) {
        console.error('Error loading categories:', error);
    }
};

// Load eggs for selected nest
const loadEggs = async () => {
    if (!serverForm.value.nest) return;

    try {
        const response = await Eggs.getEggsByCategory(Number(serverForm.value.nest));
        if (response.success) {
            eggs.value = response.eggs;
            // Reset egg selection when nest changes
            serverForm.value.egg = '';
        } else {
            console.error('Failed to load eggs:', response.message);
        }
    } catch (error) {
        console.error('Error loading eggs:', error);
    }
};

// Load users
const loadUsers = async () => {
    try {
        const response = await Users.getUsers();
        if (response.success) {
            users.value = response.users;
        } else {
            console.error('Failed to load users:', response.message);
        }
    } catch (error) {
        console.error('Error loading users:', error);
    }
};

// Save server to queue
const saveServer = async () => {
    loading.value = true;

    try {
        const response = await ServerQueue.createServerQueueItem(
            serverForm.value.name,
            serverForm.value.description,
            serverForm.value.ram,
            serverForm.value.disk,
            serverForm.value.cpu,
            serverForm.value.ports,
            serverForm.value.databases,
            serverForm.value.backups,
            Number(serverForm.value.location),
            serverForm.value.user,
            Number(serverForm.value.nest),
            Number(serverForm.value.egg),
        );

        if (response.success) {
            playSuccess();
            Swal.fire({
                title: 'Success',
                text: 'Server added to queue successfully',
                icon: 'success',
                showConfirmButton: true,
            }).then(() => {
                router.push('/mc-admin/server-queue');
            });
        } else {
            playError();
            Swal.fire({
                title: 'Error',
                text: response.message || 'Failed to add server to queue',
                icon: 'error',
                showConfirmButton: true,
            });
        }
    } catch (error) {
        console.error('Error adding server to queue:', error);
        playError();
        Swal.fire({
            title: 'Error',
            text: 'An unexpected error occurred',
            icon: 'error',
            showConfirmButton: true,
        });
    } finally {
        loading.value = false;
    }
};

onMounted(async () => {
    await Promise.all([loadLocations(), loadCategories(), loadUsers()]);
});
</script>
