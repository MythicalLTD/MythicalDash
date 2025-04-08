<template>
    <LayoutDashboard>
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-white">Coin Store</h1>
                <div class="flex items-center bg-gray-800/50 px-4 py-2 rounded-lg">
                    <Coins class="h-5 w-5 text-yellow-500 mr-2" />
                    <span class="text-lg font-medium text-yellow-500">{{ userCoins }}</span>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Store Content -->
                <div class="lg:col-span-2">
                    <CardComponent
                        cardTitle="Available Items"
                        cardDescription="Spend your coins on server resources and upgrades"
                    >
                        <div class="relative overflow-hidden">
                            <!-- Background decorative elements -->
                            <div
                                class="absolute -top-20 -right-20 w-40 h-40 bg-indigo-500/5 rounded-full blur-2xl"
                            ></div>
                            <div
                                class="absolute -bottom-20 -left-20 w-40 h-40 bg-purple-500/5 rounded-full blur-2xl"
                            ></div>

                            <div class="relative z-10 p-6">
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
                                <br />
                                <!-- Loading State -->
                                <div v-if="isLoading" class="py-10 flex flex-col items-center justify-center">
                                    <LoaderIcon class="w-12 h-12 text-indigo-500 animate-spin mb-3" />
                                    <p class="text-gray-400">Loading store items...</p>
                                </div>

                                <!-- Product Grid -->
                                <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div
                                        v-for="item in storeItems"
                                        :key="item.id"
                                        class="bg-gray-800/30 rounded-xl p-6 border border-gray-700/30 hover:border-indigo-500/30 transition-all duration-300"
                                    >
                                        <div class="flex items-start mb-4">
                                            <div
                                                class="w-12 h-12 rounded-xl bg-indigo-500/10 flex items-center justify-center mr-4 flex-shrink-0"
                                            >
                                                <component :is="item.icon" class="h-6 w-6 text-indigo-400" />
                                            </div>
                                            <div>
                                                <h3 class="text-lg font-semibold text-white">{{ item.name }}</h3>
                                                <p class="text-sm text-gray-400">{{ item.description }}</p>
                                            </div>
                                        </div>

                                        <div class="space-y-2 mb-4">
                                            <div
                                                v-for="feature in item.features"
                                                :key="feature"
                                                class="flex items-center text-sm text-gray-300"
                                            >
                                                <CheckIcon class="h-4 w-4 text-emerald-400 mr-2" />
                                                {{ feature }}
                                            </div>
                                        </div>

                                        <div
                                            class="flex items-center justify-between mt-4 pt-4 border-t border-gray-700/30"
                                        >
                                            <div class="flex items-center text-yellow-500">
                                                <Coins class="h-4 w-4 mr-1" />
                                                <span class="font-medium">{{ item.price }}</span>
                                            </div>

                                            <button
                                                @click="purchaseItem(item)"
                                                :disabled="userCoins < parseInt(item.price)"
                                                class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                                                :class="[
                                                    userCoins < parseInt(item.price)
                                                        ? 'bg-gray-700/50 text-gray-400 cursor-not-allowed'
                                                        : 'bg-indigo-600 hover:bg-indigo-700 text-white',
                                                ]"
                                            >
                                                {{
                                                    userCoins < parseInt(item.price) ? 'Insufficient Coins' : 'Purchase'
                                                }}
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Empty State -->
                                <div
                                    v-if="!isLoading && storeItems.length === 0"
                                    class="py-10 flex flex-col items-center justify-center"
                                >
                                    <ShoppingCartIcon class="w-16 h-16 text-gray-600 mb-3" />
                                    <p class="text-gray-400 text-center">No items available in this category</p>
                                    <p class="text-gray-500 text-sm text-center mt-2">Check back later for new items</p>
                                </div>
                            </div>
                        </div>
                    </CardComponent>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Coin Balance Card -->
                    <CardComponent cardTitle="Your Balance" cardDescription="Available coins to spend">
                        <div class="p-5">
                            <div class="bg-gray-800/30 rounded-xl p-5 flex items-center">
                                <div
                                    class="w-12 h-12 rounded-full bg-yellow-500/20 flex items-center justify-center mr-4"
                                >
                                    <Coins class="h-6 w-6 text-yellow-500" />
                                </div>
                                <div>
                                    <div class="text-sm text-gray-400">Current Balance</div>
                                    <div class="text-3xl font-bold text-yellow-500">{{ userCoins }}</div>
                                </div>
                            </div>

                            <div class="mt-4 grid grid-cols-2 gap-3">
                                <RouterLink
                                    to="/earn/redeem"
                                    class="bg-gray-800/30 hover:bg-gray-800/50 transition-colors duration-200 rounded-lg p-3 text-center"
                                >
                                    <GiftIcon class="h-5 w-5 text-indigo-400 mx-auto mb-1" />
                                    <span class="text-sm text-gray-300">Redeem Codes</span>
                                </RouterLink>

                                <RouterLink
                                    to="/earn/links"
                                    class="bg-gray-800/30 hover:bg-gray-800/50 transition-colors duration-200 rounded-lg p-3 text-center"
                                >
                                    <LinkIcon class="h-5 w-5 text-indigo-400 mx-auto mb-1" />
                                    <span class="text-sm text-gray-300">Earn More</span>
                                </RouterLink>
                            </div>
                        </div>
                    </CardComponent>

                    <!-- How It Works Card -->
                    <CardComponent cardTitle="How It Works" cardDescription="Spending coins in the store">
                        <div class="p-4 space-y-4">
                            <div class="bg-gray-800/30 p-4 rounded-lg">
                                <div class="flex items-start mb-2">
                                    <div
                                        class="bg-indigo-900/50 text-indigo-400 w-5 h-5 rounded-full flex items-center justify-center mr-2 flex-shrink-0"
                                    >
                                        <span class="text-xs">1</span>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-white mb-1">Earn Coins</h4>
                                        <p class="text-xs text-gray-400">
                                            Complete tasks in the Earn section to accumulate coins
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
                                        <h4 class="text-sm font-medium text-white mb-1">Browse Store</h4>
                                        <p class="text-xs text-gray-400">
                                            Explore different categories and find items you want
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
                                        <h4 class="text-sm font-medium text-white mb-1">Make Purchase</h4>
                                        <p class="text-xs text-gray-400">Buy items with your accumulated coins</p>
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
                                        <h4 class="text-sm font-medium text-white mb-1">Instant Delivery</h4>
                                        <p class="text-xs text-gray-400">
                                            Resources are immediately added to your account
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
    CheckCircle as CheckCircleIcon,
    AlertTriangle as AlertTriangleIcon,
    X as XIcon,
    ShoppingCart as ShoppingCartIcon,
    Gift as GiftIcon,
    Link as LinkIcon,
    Check as CheckIcon,
    HardDrive as HardDriveIcon,
    Cpu as CpuIcon,
    Database as DatabaseIcon,
    Server as ServerIcon,
    Save as BackupIcon,
    Layers as AllocationIcon,
    Coins,
} from 'lucide-vue-next';
import { RouterLink } from 'vue-router';
import Session from '@/mythicaldash/Session';
import Swal from 'sweetalert2';
import { useSettingsStore } from '@/stores/settings';
import router from '@/router';

const Settings = useSettingsStore();

// Check if Store is enabled
if (Settings.getSetting('store_enabled') === 'false') {
    Swal.fire({
        title: 'Store',
        text: 'Store is not enabled on this host!',
        icon: 'error',
        confirmButtonText: 'OK',
    });
    router.push('/dashboard');
}

// State
const isLoading = ref(true);
const userCoins = ref(Session.getInfoInt('credits') || 0);
const statusMessage = ref({
    type: 'success',
    text: '',
});

// Store API response interfaces
interface StoreItemData {
    id: string;
    price: string;
}

interface StoreItem extends StoreItemData {
    name: string;
    description: string;
    icon: typeof ShoppingCartIcon;
    features: string[];
}

const storeItems = ref<StoreItem[]>([]);

// Item metadata mapping
const itemMetadata: Record<string, { name: string; description: string; features: string[]; icon: unknown }> = {
    ram: {
        name: 'Additional RAM',
        description: "Increase your server's memory capacity",
        features: ['Instant allocation', 'Flexible scaling', 'No restart required'],
        icon: CpuIcon,
    },
    disk: {
        name: 'Storage Space',
        description: "Expand your server's storage capacity",
        features: ['SSD storage', 'High performance', 'Instant provisioning'],
        icon: HardDriveIcon,
    },
    cpu: {
        name: 'CPU Power',
        description: "Boost your server's processing power",
        features: ['High-performance cores', 'Dedicated resources', 'Real-time scaling'],
        icon: CpuIcon,
    },
    server_slot: {
        name: 'Server Slot',
        description: 'Add an additional server to your account',
        features: ['Full server access', 'Custom configuration', 'Instant deployment'],
        icon: ServerIcon,
    },
    server_backup: {
        name: 'Backup Slot',
        description: 'Additional backup storage for your servers',
        features: ['Automated backups', 'Instant restoration', 'Secure storage'],
        icon: BackupIcon,
    },
    server_allocation: {
        name: 'Port Allocation',
        description: 'Additional network port for your server',
        features: ['Dedicated port', 'Custom port range', 'Instant assignment'],
        icon: AllocationIcon,
    },
    server_database: {
        name: 'Database',
        description: 'Additional database for your server',
        features: ['MySQL/MariaDB', 'Automated backups', 'Secure access'],
        icon: DatabaseIcon,
    },
};

// Load store data
const loadStoreData = async () => {
    isLoading.value = true;

    try {
        const response = await fetch('/api/user/store/items', {
            headers: {
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error('Failed to fetch store items');
        }

        const data = await response.json();

        if (data.success && data.data.items) {
            storeItems.value = data.data.items.map((item: StoreItemData) => ({
                ...item,
                ...itemMetadata[item.id],
            }));
        } else {
            showStatusMessage(data.message || 'Failed to load store items', 'error');
        }
    } catch (error) {
        console.error('Error loading store data:', error);
        showStatusMessage('An error occurred while loading store data', 'error');
    } finally {
        isLoading.value = false;
    }
};

// Purchase an item
const purchaseItem = async (item: StoreItem) => {
    if (userCoins.value < parseInt(item.price)) {
        showStatusMessage('You do not have enough coins for this purchase', 'error');
        return;
    }

    try {
        // Confirmation dialog
        const result = await Swal.fire({
            title: 'Confirm Purchase',
            html: `Are you sure you want to purchase <strong>${item.name}</strong> for <strong>${item.price} coins</strong>?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Purchase',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#4f46e5',
            cancelButtonColor: '#1f2937',
        });

        if (!result.isConfirmed) {
            return;
        }

        const formData = new FormData();
        formData.append('itemId', item.id);

        // Call API to process purchase
        const response = await fetch('/api/user/store/purchase', {
            method: 'POST',
            body: formData,
        });

        const data = await response.json();

        if (data.success) {
            // Update user coins
            userCoins.value = data.remaining_coins;

            // Show success message
            showStatusMessage(`Successfully purchased ${item.name}!`, 'success');

            // Show more detailed success message
            Swal.fire({
                title: 'Purchase Successful!',
                html: `<p>You have successfully purchased <strong>${item.name}</strong>.</p>
                       <p class="mt-2">The resource has been added to your account.</p>
                       <p class="mt-2 text-sm text-gray-400">Your new balance: <strong>${userCoins.value} coins</strong></p>`,
                icon: 'success',
                confirmButtonText: 'OK',
                confirmButtonColor: '#4f46e5',
            });
        } else {
            // Handle specific error codes
            let errorMessage = 'Failed to process purchase';

            switch (data.error_code) {
                case 'MISSING_ITEM_ID':
                    errorMessage = 'Item ID is required';
                    break;
                case 'EMPTY_ITEM_ID':
                    errorMessage = 'Item ID cannot be empty';
                    break;
                case 'INVALID_ITEM_ID':
                    errorMessage = `Invalid item ID: ${item.id}`;
                    break;
                case 'INSUFFICIENT_COINS':
                    errorMessage = `Insufficient coins. Required: ${data.required}, Available: ${data.available}`;
                    break;
                case 'STORE_NOT_ENABLED':
                    errorMessage = 'Store is not enabled on this host';
                    break;
                case 'PURCHASE_FAILED':
                    errorMessage = data.message || 'Failed to process purchase';
                    break;
                default:
                    errorMessage = data.message || 'An unexpected error occurred';
            }

            showStatusMessage(errorMessage, 'error');

            // Show error dialog for more details
            Swal.fire({
                title: 'Purchase Failed',
                html: `<p class="text-red-400">${errorMessage}</p>
                       <p class="mt-2 text-sm text-gray-400">Please try again or contact support if the issue persists.</p>`,
                icon: 'error',
                confirmButtonText: 'OK',
                confirmButtonColor: '#4f46e5',
            });
        }
    } catch (error) {
        console.error('Error purchasing item:', error);
        showStatusMessage('An error occurred while processing your purchase', 'error');

        // Show error dialog for network/technical errors
        Swal.fire({
            title: 'Network Error',
            html: `<p class="text-red-400">Unable to connect to the server</p>
                   <p class="mt-2 text-sm text-gray-400">Please check your internet connection and try again.</p>`,
            icon: 'error',
            confirmButtonText: 'OK',
            confirmButtonColor: '#4f46e5',
        });
    }
};

// Show status message
const showStatusMessage = (text: string, type: 'success' | 'error') => {
    statusMessage.value = { text, type };

    // Clear message after 5 seconds
    setTimeout(() => {
        statusMessage.value.text = '';
        statusMessage.value.type = '';
    }, 5000);
};

onMounted(() => {
    loadStoreData();
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
