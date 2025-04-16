<template>
    <LayoutDashboard>
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-white">{{ t('store.pages.index.title') }}</h1>
                <div class="flex items-center bg-gray-800/50 px-4 py-2 rounded-lg">
                    <Coins class="h-5 w-5 text-yellow-500 mr-2" />
                    <span class="text-lg font-medium text-yellow-500">{{ userCoins }}</span>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Store Content -->
                <div class="lg:col-span-2">
                    <CardComponent
                        :cardTitle="t('store.pages.index.card.title')"
                        :cardDescription="t('store.pages.index.card.description')"
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
                                    <p class="text-gray-400">{{ t('store.pages.index.loading') }}</p>
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
                                                    userCoins < parseInt(item.price)
                                                        ? t('store.pages.index.insufficientButton')
                                                        : t('store.pages.index.buyButton')
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
                                    <p class="text-gray-400 text-center">{{ t('store.pages.index.empty') }}</p>
                                    <p class="text-gray-500 text-sm text-center mt-2">
                                        {{ t('store.pages.index.emptyDescription') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </CardComponent>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Coin Balance Card -->
                    <CardComponent
                        :cardTitle="t('store.pages.index.cardYourBalance')"
                        :cardDescription="t('store.pages.index.cardAvailableCoins')"
                    >
                        <div class="p-5">
                            <div class="bg-gray-800/30 rounded-xl p-5 flex items-center">
                                <div
                                    class="w-12 h-12 rounded-full bg-yellow-500/20 flex items-center justify-center mr-4"
                                >
                                    <Coins class="h-6 w-6 text-yellow-500" />
                                </div>
                                <div>
                                    <div class="text-sm text-gray-400">
                                        {{ t('store.pages.index.cardCurrentBalance') }}
                                    </div>
                                    <div class="text-3xl font-bold text-yellow-500">{{ userCoins }}</div>
                                </div>
                            </div>
                            <RouterLink
                                to="/store/add-credits"
                                v-if="Settings.getSetting('credits_recharge_enabled') === 'true'"
                                class="mt-4 bg-indigo-600/20 hover:bg-indigo-600/30 transition-all duration-200 rounded-lg p-4 flex items-center justify-center gap-2 group"
                            >
                                <PlusIcon
                                    class="h-5 w-5 text-indigo-400 group-hover:text-indigo-300 transition-colors duration-200"
                                />
                                <span class="text-sm text-indigo-300 group-hover:text-indigo-200 font-medium">{{
                                    t('store.pages.index.addCredits')
                                }}</span>
                            </RouterLink>
                            <div class="mt-4 grid grid-cols-2 gap-3">
                                <RouterLink
                                    to="/earn/redeem"
                                    class="bg-gray-800/30 hover:bg-gray-800/50 transition-colors duration-200 rounded-lg p-3 text-center"
                                >
                                    <GiftIcon class="h-5 w-5 text-indigo-400 mx-auto mb-1" />
                                    <span class="text-sm text-gray-300">{{ t('store.pages.index.redeemCodes') }}</span>
                                </RouterLink>

                                <RouterLink
                                    to="/earn/links"
                                    class="bg-gray-800/30 hover:bg-gray-800/50 transition-colors duration-200 rounded-lg p-3 text-center"
                                >
                                    <LinkIcon class="h-5 w-5 text-indigo-400 mx-auto mb-1" />
                                    <span class="text-sm text-gray-300">{{ t('store.pages.index.earnMore') }}</span>
                                </RouterLink>
                            </div>
                        </div>
                    </CardComponent>

                    <!-- How It Works Card -->
                    <CardComponent
                        :cardTitle="t('store.pages.index.howItWorks.title')"
                        :cardDescription="t('store.pages.index.howItWorks.description')"
                    >
                        <div class="p-4 space-y-4">
                            <div class="bg-gray-800/30 p-4 rounded-lg">
                                <div class="flex items-start mb-2">
                                    <div
                                        class="bg-indigo-900/50 text-indigo-400 w-5 h-5 rounded-full flex items-center justify-center mr-2 flex-shrink-0"
                                    >
                                        <span class="text-xs">{{
                                            t('store.pages.index.howItWorks.steps.one.number')
                                        }}</span>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-white mb-1">
                                            {{ t('store.pages.index.howItWorks.steps.one.title') }}
                                        </h4>
                                        <p class="text-xs text-gray-400">
                                            {{ t('store.pages.index.howItWorks.steps.one.description') }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-800/30 p-4 rounded-lg">
                                <div class="flex items-start mb-2">
                                    <div
                                        class="bg-indigo-900/50 text-indigo-400 w-5 h-5 rounded-full flex items-center justify-center mr-2 flex-shrink-0"
                                    >
                                        <span class="text-xs">{{
                                            t('store.pages.index.howItWorks.steps.two.number')
                                        }}</span>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-white mb-1">
                                            {{ t('store.pages.index.howItWorks.steps.two.title') }}
                                        </h4>
                                        <p class="text-xs text-gray-400">
                                            {{ t('store.pages.index.howItWorks.steps.two.description') }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-800/30 p-4 rounded-lg">
                                <div class="flex items-start mb-2">
                                    <div
                                        class="bg-indigo-900/50 text-indigo-400 w-5 h-5 rounded-full flex items-center justify-center mr-2 flex-shrink-0"
                                    >
                                        <span class="text-xs">{{
                                            t('store.pages.index.howItWorks.steps.three.number')
                                        }}</span>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-white mb-1">
                                            {{ t('store.pages.index.howItWorks.steps.three.title') }}
                                        </h4>
                                        <p class="text-xs text-gray-400">
                                            {{ t('store.pages.index.howItWorks.steps.three.description') }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-800/30 p-4 rounded-lg">
                                <div class="flex items-start mb-2">
                                    <div
                                        class="bg-indigo-900/50 text-indigo-400 w-5 h-5 rounded-full flex items-center justify-center mr-2 flex-shrink-0"
                                    >
                                        <span class="text-xs">{{
                                            t('store.pages.index.howItWorks.steps.four.number')
                                        }}</span>
                                    </div>
                                    <div>
                                        <h4 class="text-sm font-medium text-white mb-1">
                                            {{ t('store.pages.index.howItWorks.steps.four.title') }}
                                        </h4>
                                        <p class="text-xs text-gray-400">
                                            {{ t('store.pages.index.howItWorks.steps.four.description') }}
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
import { MythicalDOM } from '@/mythicaldash/MythicalDOM';
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
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const Settings = useSettingsStore();
MythicalDOM.setPageTitle(t('store.pages.index.title'));

// Check if Store is enabled
if (Settings.getSetting('store_enabled') === 'false') {
    Swal.fire({
        title: t('store.pages.index.title'),
        text: t('store.pages.index.subTitle'),
        footer: t('store.notEnabled.footer'),
        confirmButtonText: t('store.notEnabled.button'),
        icon: 'error',
        showConfirmButton: true,
    }).then(() => {
        router.push('/dashboard');
    });
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
        name: t('store.pages.index.info.ram.title'),
        description: t('store.pages.index.info.ram.description'),
        features: [
            t('store.pages.index.info.ram.features.one'),
            t('store.pages.index.info.ram.features.two'),
            t('store.pages.index.info.ram.features.three'),
        ],
        icon: CpuIcon,
    },
    disk: {
        name: t('store.pages.index.info.disk.title'),
        description: t('store.pages.index.info.disk.description'),
        features: [
            t('store.pages.index.info.disk.features.one'),
            t('store.pages.index.info.disk.features.two'),
            t('store.pages.index.info.disk.features.three'),
        ],
        icon: HardDriveIcon,
    },
    cpu: {
        name: t('store.pages.index.info.cpu.title'),
        description: t('store.pages.index.info.cpu.description'),
        features: [
            t('store.pages.index.info.cpu.features.one'),
            t('store.pages.index.info.cpu.features.two'),
            t('store.pages.index.info.cpu.features.three'),
        ],
        icon: CpuIcon,
    },
    server_slot: {
        name: t('store.pages.index.info.server_slot.title'),
        description: t('store.pages.index.info.server_slot.description'),
        features: [
            t('store.pages.index.info.server_slot.features.one'),
            t('store.pages.index.info.server_slot.features.two'),
            t('store.pages.index.info.server_slot.features.three'),
        ],
        icon: ServerIcon,
    },
    server_backup: {
        name: t('store.pages.index.info.server_backup.title'),
        description: t('store.pages.index.info.server_backup.description'),
        features: [
            t('store.pages.index.info.server_backup.features.one'),
            t('store.pages.index.info.server_backup.features.two'),
            t('store.pages.index.info.server_backup.features.three'),
        ],
        icon: BackupIcon,
    },
    server_allocation: {
        name: t('store.pages.index.info.server_allocation.title'),
        description: t('store.pages.index.info.server_allocation.description'),
        features: [
            t('store.pages.index.info.server_allocation.features.one'),
            t('store.pages.index.info.server_allocation.features.two'),
            t('store.pages.index.info.server_allocation.features.three'),
        ],
        icon: AllocationIcon,
    },
    server_database: {
        name: t('store.pages.index.info.server_database.title'),
        description: t('store.pages.index.info.server_database.description'),
        features: [
            t('store.pages.index.info.server_database.features.one'),
            t('store.pages.index.info.server_database.features.two'),
            t('store.pages.index.info.server_database.features.three'),
        ],
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
            showStatusMessage(data.message || t('store.pages.alerts.error.generic'), 'error');
        }
    } catch (error) {
        console.error('Error loading store data:', error);
        showStatusMessage(t('store.pages.alerts.error.generic'), 'error');
    } finally {
        isLoading.value = false;
    }
};

// Purchase an item
const purchaseItem = async (item: StoreItem) => {
    if (userCoins.value < parseInt(item.price)) {
        showStatusMessage(t('store.pages.alerts.error.notEnoughCoins'), 'error');
        return;
    }

    try {
        // Confirmation dialog
        const result = await Swal.fire({
            title: t('store.pages.alerts.buy.title'),
            html: t('store.pages.alerts.buy.description', {
                name: item.name,
                price: item.price,
            }),
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: t('store.pages.alerts.buy.confirmButtonText'),
            cancelButtonText: t('store.pages.alerts.buy.cancelButtonText'),
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
            showStatusMessage(
                t('store.pages.index.info.buy.success.description', {
                    name: item.name,
                }),
                'success',
            );

            // Show more detailed success message
            Swal.fire({
                title: t('store.pages.index.info.buy.success.title'),
                html: t('store.pages.index.info.buy.success.description', {
                    name: item.name,
                }),
                icon: 'success',
                confirmButtonText: t('store.pages.index.info.buy.success.confirmButtonText'),
                confirmButtonColor: '#4f46e5',
            });
        } else {
            // Handle specific error codes
            let errorMessage = t('store.pages.alerts.error.generic');

            switch (data.error_code) {
                case 'MISSING_ITEM_ID':
                    errorMessage = t('store.pages.alerts.error.buy.MISSING_ITEM_ID');
                    break;
                case 'EMPTY_ITEM_ID':
                    errorMessage = t('store.pages.alerts.error.buy.EMPTY_ITEM_ID');
                    break;
                case 'INVALID_ITEM_ID':
                    errorMessage = t('store.pages.alerts.error.buy.INVALID_ITEM_ID');
                    break;
                case 'INSUFFICIENT_COINS':
                    errorMessage = t('store.pages.alerts.error.buy.INSUFFICIENT_COINS', {
                        required: data.required,
                        available: data.available,
                    });
                    break;
                case 'STORE_NOT_ENABLED':
                    errorMessage = t('store.pages.alerts.error.buy.STORE_NOT_ENABLED');
                    break;
                case 'PURCHASE_FAILED':
                    errorMessage = data.message || t('store.pages.alerts.error.buy.PURCHASE_FAILED');
                    break;
                case 'MAX_RAM_LIMIT':
                    errorMessage = t('store.pages.alerts.error.buy.MAX_RAM_LIMIT', {
                        required: data.required,
                        available: data.available,
                    });
                    break;
                case 'MAX_DISK_LIMIT':
                    errorMessage = t('store.pages.alerts.error.buy.MAX_DISK_LIMIT', {
                        required: data.required,
                        available: data.available,
                    });
                    break;
                case 'MAX_CPU_LIMIT':
                    errorMessage = t('store.pages.alerts.error.buy.MAX_CPU_LIMIT', {
                        required: data.required,
                        available: data.available,
                    });
                    break;
                case 'MAX_PORTS_LIMIT':
                    errorMessage = t('store.pages.alerts.error.buy.MAX_PORTS_LIMIT', {
                        required: data.required,
                        available: data.available,
                    });
                    break;
                case 'MAX_DATABASES_LIMIT':
                    errorMessage = t('store.pages.alerts.error.buy.MAX_DATABASES_LIMIT', {
                        required: data.required,
                        available: data.available,
                    });
                    break;
                case 'MAX_SERVER_SLOTS_LIMIT':
                    errorMessage = t('store.pages.alerts.error.buy.MAX_SERVER_SLOTS_LIMIT', {
                        required: data.required,
                        available: data.available,
                    });
                    break;
                case 'MAX_BACKUPS_LIMIT':
                    errorMessage = t('store.pages.alerts.error.buy.MAX_BACKUPS_LIMIT', {
                        required: data.required,
                        available: data.available,
                    });
                    break;
                default:
                    errorMessage = data.message || t('store.pages.alerts.error.generic');
            }

            showStatusMessage(errorMessage, 'error');
        }
    } catch (error) {
        console.error('Error purchasing item:', error);
        showStatusMessage(t('store.pages.alerts.error.generic'), 'error');
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
