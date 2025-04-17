<template>
    <LayoutDashboard>
        <div class="p-6">
            <h1 class="text-2xl font-bold text-white mb-6">Join For Rewards</h1>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main J4R Content -->
                <div class="lg:col-span-2">
                    <CardComponent cardTitle="Available Servers" cardDescription="Join servers to earn rewards">
                        <div class="relative overflow-hidden">
                            <!-- Background decorative elements -->
                            <div
                                class="absolute -top-20 -right-20 w-40 h-40 bg-indigo-500/5 rounded-full blur-2xl"
                            ></div>
                            <div
                                class="absolute -bottom-20 -left-20 w-40 h-40 bg-purple-500/5 rounded-full blur-2xl"
                            ></div>

                            <div class="relative z-10">
                                <!-- Server List -->
                                <div class="mb-6">
                                    <div v-if="isLoading" class="py-10 flex flex-col items-center justify-center">
                                        <LoaderIcon class="w-12 h-12 text-indigo-500 animate-spin mb-3" />
                                        <p class="text-gray-400">Loading available servers...</p>
                                    </div>

                                    <div
                                        v-else-if="availableServers.length === 0"
                                        class="py-10 flex flex-col items-center justify-center"
                                    >
                                        <ServerOffIcon class="w-16 h-16 text-gray-600 mb-3" />
                                        <p class="text-gray-400 text-center">No servers available right now</p>
                                        <p class="text-gray-500 text-sm text-center mt-2">
                                            Check back later for new opportunities
                                        </p>
                                    </div>

                                    <div v-else class="space-y-4">
                                        <!-- Server Cards -->
                                        <div
                                            v-for="server in availableServers"
                                            :key="server.id"
                                            class="bg-gray-800/50 rounded-xl overflow-hidden"
                                        >
                                            <div class="flex flex-col md:flex-row">
                                                <!-- Server Icon/Banner -->
                                                <div class="md:w-1/4 relative">
                                                    <img
                                                        :src="
                                                            server.banner || '/assets/images/default-server-banner.png'
                                                        "
                                                        alt="Server Banner"
                                                        class="h-36 md:h-full w-full object-cover"
                                                    />
                                                    <div class="absolute top-3 left-3 bg-gray-900/70 rounded-full p-1">
                                                        <img
                                                            :src="
                                                                server.icon || '/assets/images/default-server-icon.png'
                                                            "
                                                            alt="Server Icon"
                                                            class="w-12 h-12 rounded-full"
                                                        />
                                                    </div>
                                                </div>

                                                <!-- Server Info -->
                                                <div class="md:w-3/4 p-5">
                                                    <div class="flex justify-between items-start mb-3">
                                                        <div>
                                                            <h3 class="text-lg font-bold text-white">
                                                                {{ server.name }}
                                                            </h3>
                                                            <p class="text-sm text-gray-400">
                                                                {{ server.members }} members •
                                                                {{ server.online }} online
                                                            </p>
                                                        </div>
                                                        <div class="flex items-center">
                                                            <div
                                                                class="bg-indigo-900/40 text-indigo-400 px-3 py-1 rounded-full text-xs font-medium inline-flex items-center"
                                                            >
                                                                <Coins class="h-3 w-3 mr-1" />
                                                                {{ server.reward }} coins
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <p class="text-gray-300 text-sm mb-4">{{ server.description }}</p>

                                                    <div class="flex flex-wrap gap-2 mb-4">
                                                        <span
                                                            v-for="tag in server.tags"
                                                            :key="tag"
                                                            class="bg-gray-700/50 text-gray-300 text-xs px-2 py-1 rounded-md"
                                                        >
                                                            {{ tag }}
                                                        </span>
                                                    </div>

                                                    <div class="flex items-center justify-between">
                                                        <div
                                                            v-if="server.completed"
                                                            class="text-green-400 text-sm flex items-center"
                                                        >
                                                            <CheckCircleIcon class="w-4 h-4 mr-1" />
                                                            Joined
                                                        </div>
                                                        <div
                                                            v-else-if="server.expiresIn"
                                                            class="text-yellow-400 text-sm flex items-center"
                                                        >
                                                            <ClockIcon class="w-4 h-4 mr-1" />
                                                            Available for {{ server.expiresIn }}
                                                        </div>
                                                        <div v-else class="text-xs text-gray-400">Always available</div>

                                                        <button
                                                            @click="joinServer(server)"
                                                            :disabled="server.completed"
                                                            :class="[
                                                                'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                                                                server.completed
                                                                    ? 'bg-gray-700 text-gray-400 cursor-not-allowed'
                                                                    : 'bg-indigo-600 hover:bg-indigo-700 text-white',
                                                            ]"
                                                        >
                                                            <span v-if="server.completed"> Already Joined </span>
                                                            <span v-else> Join Server </span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Status Messages -->
                                <transition
                                    enter-active-class="transition-opacity duration-300"
                                    leave-active-class="transition-opacity duration-300"
                                    enter-from-class="opacity-0"
                                    enter-to-class="opacity-100"
                                    leave-from-class="opacity-100"
                                    leave-to-class="opacity-0"
                                >
                                    <div
                                        v-if="statusMessage.text"
                                        :class="[
                                            'p-4 rounded-lg mt-6',
                                            statusMessage.type === 'success'
                                                ? 'bg-emerald-900/30 border border-emerald-700/50 text-emerald-400'
                                                : 'bg-red-900/30 border border-red-700/50 text-red-400',
                                        ]"
                                    >
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0">
                                                <CheckCircleIcon
                                                    v-if="statusMessage.type === 'success'"
                                                    class="h-5 w-5"
                                                />
                                                <AlertTriangleIcon v-else class="h-5 w-5" />
                                            </div>
                                            <div class="ml-3">
                                                <p class="font-medium">{{ statusMessage.text }}</p>
                                            </div>
                                            <button @click="statusMessage.text = ''" class="ml-auto">
                                                <XIcon class="h-4 w-4" />
                                            </button>
                                        </div>
                                    </div>
                                </transition>
                            </div>
                        </div>
                    </CardComponent>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Stats Card -->
                    <CardComponent cardTitle="Your Stats" cardDescription="J4R participation stats">
                        <div class="p-1 space-y-4">
                            <!-- Coin Balance -->
                            <div class="bg-gray-800/30 rounded-xl p-5 flex justify-between items-center">
                                <div class="flex items-center">
                                    <div
                                        class="w-10 h-10 rounded-full bg-yellow-500/20 flex items-center justify-center mr-3"
                                    >
                                        <Coins class="h-5 w-5 text-yellow-500" />
                                    </div>
                                    <div>
                                        <div class="text-sm text-gray-400">Current Balance</div>
                                        <div class="text-2xl font-bold text-yellow-500">{{ totalCoins }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardComponent>

                    <!-- How It Works Card -->
                    <CardComponent cardTitle="How It Works" cardDescription="Earn by joining Discord servers">
                        <div class="p-4 space-y-4">
                            <div class="bg-gray-800/30 p-4 rounded-lg">
                                <div class="flex items-start mb-2">
                                    <div
                                        class="bg-indigo-900/50 text-indigo-400 w-5 h-5 rounded-full flex items-center justify-center mr-2 flex-shrink-0"
                                    >
                                        <span class="text-xs">1</span>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-white mb-1">Browse Available Servers</h4>
                                        <p class="text-xs text-gray-400">
                                            Explore our list of partnered Discord servers
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-800/30 p-4 rounded-lg">
                                <div class="flex items-start mb-2">
                                    <div
                                        class="bg-indigo-900/50 text-indigo-400 w-5 h-5 rounded-full flex items-center justify-center mr-2 flex-shrink-0"
                                    >
                                        <span class="text-xs">2</span>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-white mb-1">Join Server</h4>
                                        <p class="text-xs text-gray-400">
                                            Click "Join Server" to open Discord and join the community
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-800/30 p-4 rounded-lg">
                                <div class="flex items-start mb-2">
                                    <div
                                        class="bg-indigo-900/50 text-indigo-400 w-5 h-5 rounded-full flex items-center justify-center mr-2 flex-shrink-0"
                                    >
                                        <span class="text-xs">3</span>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-white mb-1">Verify Membership</h4>
                                        <p class="text-xs text-gray-400">
                                            Our system will verify your membership automatically
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-800/30 p-4 rounded-lg">
                                <div class="flex items-start mb-2">
                                    <div
                                        class="bg-indigo-900/50 text-indigo-400 w-5 h-5 rounded-full flex items-center justify-center mr-2 flex-shrink-0"
                                    >
                                        <span class="text-xs">4</span>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-white mb-1">Receive Rewards</h4>
                                        <p class="text-xs text-gray-400">
                                            Get coins credited to your account within minutes
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardComponent>
                </div>
            </div>
        </div>
    </LayoutDashboard>
</template>

<script setup lang="ts">
import LayoutDashboard from '@/components/client/LayoutDashboard.vue';
import CardComponent from '@/components/client/ui/Card/CardComponent.vue';
import { ref, onMounted } from 'vue';
import {
    Loader as LoaderIcon,
    ServerOff as ServerOffIcon,
    Clock as ClockIcon,
    CheckCircle as CheckCircleIcon,
    AlertTriangle as AlertTriangleIcon,
    X as XIcon,
    Coins,
} from 'lucide-vue-next';
import Session from '@/mythicaldash/Session';
import { useSettingsStore } from '@/stores/settings';
import router from '@/router';
import Swal from 'sweetalert2';
import { MythicalDOM } from '@/mythicaldash/MythicalDOM';
MythicalDOM.setPageTitle('Join For Rewards');

const Settings = useSettingsStore();

// If J4R is disabled, redirect to dashboard
if (Settings.getSetting('j4r_enabled') === 'false') {
    Swal.fire({
        title: 'Join For Rewards',
        text: 'Join For Rewards is not enabled on this host!',
        icon: 'error',
        confirmButtonText: 'OK',
    });
    router.push('/dashboard');
}

// State
const isLoading = ref(true);
const availableServers = ref<Server[]>([]);
const totalCoins = ref(Session.getInfoInt('credits'));
const statusMessage = ref({
    type: 'success',
    text: '',
});

// User statistics
const userStats = ref({
    serversJoined: 0,
    totalEarned: 0,
    availableNow: 0,
    nextReset: '7 days',
});

// Server interface
interface Server {
    id: string;
    name: string;
    description: string;
    icon: string;
    banner: string;
    members: number;
    online: number;
    reward: number;
    tags: string[];
    completed: boolean;
    expiresIn?: string;
    inviteUrl: string;
}

// Join server function
const joinServer = async (server: Server) => {
    if (server.completed) {
        showStatusMessage('You have already joined this server', 'error');
        return;
    }

    try {
        // Log the join attempt
        await fetch('/api/user/earn/j4r/join', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
            },
            body: JSON.stringify({
                serverId: server.id,
            }),
        });

        // Open Discord invite in new tab
        window.open(server.inviteUrl, '_blank');

        // Show success message
        showStatusMessage('Discord invite opened. Join the server to earn rewards!', 'success');

        // After a delay, check if they've joined
        setTimeout(() => {
            checkServerJoin(server.id);
        }, 10000); // Check after 10 seconds
    } catch (error) {
        console.error('Error joining server:', error);
        showStatusMessage('An error occurred while processing your request', 'error');
    }
};

// Check if user has joined the server
const checkServerJoin = async (serverId: string) => {
    try {
        const response = await fetch(`/api/user/earn/j4r/verify?serverId=${serverId}`, {
            method: 'GET',
            headers: {
                Accept: 'application/json',
            },
        });

        const data = await response.json();

        if (response.ok && data.status === 200 && data.joined) {
            // User has joined, update UI and add coins
            const serverIndex = availableServers.value.findIndex((s) => s.id === serverId);
            if (serverIndex !== -1) {
                // Mark server as completed
                availableServers.value[serverIndex].completed = true;

                // Update coin balance
                const rewardAmount = availableServers.value[serverIndex].reward;
                totalCoins.value += rewardAmount;

                // Update user stats
                userStats.value.serversJoined++;
                userStats.value.totalEarned += rewardAmount;
                userStats.value.availableNow--;

                // Show success message
                showStatusMessage(`You've successfully joined the server and earned ${rewardAmount} coins!`, 'success');
            }
        } else if (data.status === 202) {
            // Still pending, check again after a delay
            setTimeout(() => {
                checkServerJoin(serverId);
            }, 10000); // Check again after 10 seconds
        }
    } catch (error) {
        console.error('Error verifying server join:', error);
    }
};

// Show status message
const showStatusMessage = (text: string, type: 'success' | 'error') => {
    statusMessage.value = { text, type };

    // Clear message after 5 seconds
    setTimeout(() => {
        statusMessage.value.text = '';
    }, 5000);
};

// Example mock data for development
const loadMockData = () => {
    availableServers.value = [
        {
            id: '1',
            name: 'Gaming Community',
            description:
                'A friendly community for gamers of all types. Join us for game nights, tournaments, and more!',
            icon: 'https://via.placeholder.com/128',
            banner: 'https://via.placeholder.com/800x200/1a1a2e/ffffff?text=Gaming+Community',
            members: 5280,
            online: 423,
            reward: 50,
            tags: ['Gaming', 'Tournaments', 'Community'],
            completed: false,
            inviteUrl: 'https://discord.gg/example1',
        },
        {
            id: '2',
            name: 'Developers Hub',
            description:
                'Connect with developers, share knowledge, and collaborate on projects. Perfect for coders of all skill levels.',
            icon: 'https://via.placeholder.com/128',
            banner: 'https://via.placeholder.com/800x200/1a1a2e/ffffff?text=Developers+Hub',
            members: 3450,
            online: 215,
            reward: 75,
            tags: ['Programming', 'Development', 'Collaboration'],
            completed: true,
            inviteUrl: 'https://discord.gg/example2',
        },
        {
            id: '3',
            name: 'Anime & Manga Club',
            description: 'Discuss your favorite anime and manga series, share fan art, and join watch parties!',
            icon: 'https://via.placeholder.com/128',
            banner: 'https://via.placeholder.com/800x200/1a1a2e/ffffff?text=Anime+Club',
            members: 7890,
            online: 612,
            reward: 40,
            tags: ['Anime', 'Manga', 'Fan Art'],
            completed: false,
            expiresIn: '2 days',
            inviteUrl: 'https://discord.gg/example3',
        },
    ];

    userStats.value = {
        serversJoined: 12,
        totalEarned: 650,
        availableNow: 8,
        nextReset: '7 days',
    };

    isLoading.value = false;
};

onMounted(() => {
    // In a production environment, uncomment the line below to load real data:
    // loadServers();

    // For development/demo purposes, load mock data:
    setTimeout(() => {
        loadMockData();
    }, 1000);
});
</script>

<style scoped>
.animate-spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}
</style>
