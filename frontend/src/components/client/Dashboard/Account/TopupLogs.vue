<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue';
import CardComponent from '@/components/client/ui/Card/CardComponent.vue';
import Button from '@/components/client/ui/Button.vue';
import {
    CreditCard as CreditCardIcon,
    DollarSign as DollarIcon,
    AlertCircle as AlertIcon,
    Search as SearchIcon,
    Download as DownloadIcon,
    ChevronLeft as ChevronLeftIcon,
    ChevronRight as ChevronRightIcon,
    Filter as FilterIcon,
} from 'lucide-vue-next';
import Session from '@/mythicaldash/Session';
import { TextInput } from '@/components/client/ui/TextForms';

interface TopupTransaction {
    id: string;
    amount: number;
    credits: number;
    method: 'credit_card' | 'paypal' | 'crypto' | 'bank_transfer' | 'admin' | 'gift';
    status: 'completed' | 'pending' | 'failed' | 'refunded';
    date: string;
    reference?: string;
    notes?: string;
}

const transactions = ref<TopupTransaction[]>([]);
const isLoading = ref(true);
const error = ref<string | null>(null);
const searchQuery = ref('');
const currentPage = ref(1);
const itemsPerPage = 10;
const totalItems = ref(0);
const filterStatus = ref<string | null>(null);

// Get method icon
const getMethodIcon = (method: string) => {
    switch (method) {
        case 'credit_card':
            return CreditCardIcon;
        case 'paypal':
        case 'crypto':
        case 'bank_transfer':
        case 'admin':
        case 'gift':
        default:
            return DollarIcon;
    }
};

// Get method name
const getMethodName = (method: string): string => {
    switch (method) {
        case 'credit_card':
            return 'Credit Card';
        case 'paypal':
            return 'PayPal';
        case 'crypto':
            return 'Cryptocurrency';
        case 'bank_transfer':
            return 'Bank Transfer';
        case 'admin':
            return 'Admin Credit';
        case 'gift':
            return 'Gift';
        default:
            return method.charAt(0).toUpperCase() + method.slice(1).replace('_', ' ');
    }
};

// Get status color
const getStatusColor = (status: string): string => {
    switch (status) {
        case 'completed':
            return 'bg-green-500/10 text-green-400';
        case 'pending':
            return 'bg-yellow-500/10 text-yellow-400';
        case 'failed':
            return 'bg-red-500/10 text-red-400';
        case 'refunded':
            return 'bg-blue-500/10 text-blue-400';
        default:
            return 'bg-gray-500/10 text-gray-400';
    }
};

// Format date
const formatDate = (dateString: string): string => {
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(date);
};

// Format currency
const formatCurrency = (amount: number): string => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2,
    }).format(amount);
};

// Filter transactions
const filteredTransactions = computed(() => {
    let result = transactions.value;

    // Apply search filter
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        result = result.filter(
            (t) =>
                t.id.toLowerCase().includes(query) ||
                t.reference?.toLowerCase().includes(query) ||
                getMethodName(t.method).toLowerCase().includes(query) ||
                t.status.toLowerCase().includes(query),
        );
    }

    // Apply status filter
    if (filterStatus.value) {
        result = result.filter((t) => t.status === filterStatus.value);
    }

    return result;
});

// Update total items when filtered transactions change
watch(
    filteredTransactions,
    (newValue) => {
        totalItems.value = newValue.length;
    },
    { immediate: true },
);

// Paginated transactions
const paginatedTransactions = computed(() => {
    const startIndex = (currentPage.value - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    return filteredTransactions.value.slice(startIndex, endIndex);
});

// Total pages
const totalPages = computed(() => Math.ceil(totalItems.value / itemsPerPage));

// Navigate to page
const goToPage = (page: number) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
};

// Reset filters
const resetFilters = () => {
    searchQuery.value = '';
    filterStatus.value = null;
    currentPage.value = 1;
};

// Export transactions as CSV
const exportTransactions = () => {
    // Create CSV content
    const headers = ['ID', 'Date', 'Amount', 'Credits', 'Method', 'Status', 'Reference', 'Notes'];
    const csvContent = [
        headers.join(','),
        ...transactions.value.map((t) =>
            [
                t.id,
                t.date,
                t.amount,
                t.credits,
                getMethodName(t.method),
                t.status,
                t.reference || '',
                t.notes || '',
            ].join(','),
        ),
    ].join('\n');

    // Create download link
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.setAttribute('href', url);
    link.setAttribute('download', `topup-logs-${new Date().toISOString().split('T')[0]}.csv`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};

// Fetch transactions
const fetchTransactions = async () => {
    isLoading.value = true;
    error.value = null;

    try {
        // In a real implementation, this would be an API call
        // For now, we'll simulate fetching the transactions
        setTimeout(() => {
            // Generate mock data
            const mockTransactions: TopupTransaction[] = [];
            const methods = ['credit_card', 'paypal', 'crypto', 'bank_transfer', 'admin', 'gift'];
            const statuses = ['completed', 'pending', 'failed', 'refunded'];

            // Generate transactions for the last 3 months
            const now = new Date();
            for (let i = 0; i < 25; i++) {
                const date = new Date(now);
                date.setDate(date.getDate() - Math.floor(Math.random() * 90)); // Random date in last 90 days

                const amount = Math.round(Math.random() * 100) + 5; // Random amount between $5 and $105
                const credits = amount * 100; // 1 USD = 100 credits
                const method = methods[Math.floor(Math.random() * methods.length)] as TopupTransaction['method'];
                const status = statuses[Math.floor(Math.random() * statuses.length)] as TopupTransaction['status'];

                mockTransactions.push({
                    id: `TXN-${Math.floor(Math.random() * 1000000)
                        .toString()
                        .padStart(6, '0')}`,
                    amount,
                    credits,
                    method,
                    status,
                    date: date.toISOString(),
                    reference: method === 'credit_card' ? `CARD-${Math.floor(Math.random() * 10000)}` : undefined,
                    notes: Math.random() > 0.8 ? 'Promotional credit bonus applied' : undefined,
                });
            }

            // Sort by date (newest first)
            mockTransactions.sort((a, b) => new Date(b.date).getTime() - new Date(a.date).getTime());

            transactions.value = mockTransactions;
            totalItems.value = mockTransactions.length;
            isLoading.value = false;
        }, 1000);
    } catch (err) {
        console.error('Error fetching topup logs:', err);
        error.value = 'Failed to load topup logs. Please refresh the page.';
        isLoading.value = false;
    }
};

onMounted(() => {
    fetchTransactions();
});
</script>

<template>
    <div class="space-y-6">
        <!-- Topup Logs Header -->
        <div>
            <h2 class="text-xl font-bold text-gray-100">Credit Topup History</h2>
            <p class="text-gray-400 mt-1">View your account credit topup history and transaction details.</p>
        </div>

        <!-- Error Alert -->
        <div v-if="error" class="bg-red-500/10 border border-red-500/20 rounded-lg p-4 flex items-start gap-3">
            <AlertIcon class="h-5 w-5 text-red-400 mt-0.5 flex-shrink-0" />
            <div>
                <h3 class="text-sm font-medium text-red-400">Error</h3>
                <p class="text-xs text-gray-400 mt-1">{{ error }}</p>
            </div>
        </div>

        <!-- Filters and Actions -->
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="relative flex-1">
                <SearchIcon class="absolute left-3 top-2.5 h-4 w-4 text-gray-400" />
                <TextInput v-model="searchQuery" placeholder="Search transactions..." inputClass="pl-9" />
            </div>

            <div class="flex gap-2">
                <Button @click="resetFilters" variant="secondary" small>
                    <template #icon>
                        <FilterIcon class="h-4 w-4" />
                    </template>
                    Reset
                </Button>

                <Button @click="exportTransactions" variant="secondary" small>
                    <template #icon>
                        <DownloadIcon class="h-4 w-4" />
                    </template>
                    Export
                </Button>
            </div>
        </div>

        <!-- Status Filter Pills -->
        <div class="flex flex-wrap gap-2">
            <button
                @click="filterStatus = null"
                class="px-3 py-1 text-xs rounded-full transition-colors duration-200"
                :class="
                    filterStatus === null
                        ? 'bg-indigo-500/20 text-indigo-400'
                        : 'bg-[#1a1a2e]/30 text-gray-400 hover:bg-[#1a1a2e]/50 hover:text-gray-300'
                "
            >
                All
            </button>
            <button
                @click="filterStatus = 'completed'"
                class="px-3 py-1 text-xs rounded-full transition-colors duration-200"
                :class="
                    filterStatus === 'completed'
                        ? 'bg-green-500/20 text-green-400'
                        : 'bg-[#1a1a2e]/30 text-gray-400 hover:bg-[#1a1a2e]/50 hover:text-gray-300'
                "
            >
                Completed
            </button>
            <button
                @click="filterStatus = 'pending'"
                class="px-3 py-1 text-xs rounded-full transition-colors duration-200"
                :class="
                    filterStatus === 'pending'
                        ? 'bg-yellow-500/20 text-yellow-400'
                        : 'bg-[#1a1a2e]/30 text-gray-400 hover:bg-[#1a1a2e]/50 hover:text-gray-300'
                "
            >
                Pending
            </button>
            <button
                @click="filterStatus = 'failed'"
                class="px-3 py-1 text-xs rounded-full transition-colors duration-200"
                :class="
                    filterStatus === 'failed'
                        ? 'bg-red-500/20 text-red-400'
                        : 'bg-[#1a1a2e]/30 text-gray-400 hover:bg-[#1a1a2e]/50 hover:text-gray-300'
                "
            >
                Failed
            </button>
            <button
                @click="filterStatus = 'refunded'"
                class="px-3 py-1 text-xs rounded-full transition-colors duration-200"
                :class="
                    filterStatus === 'refunded'
                        ? 'bg-blue-500/20 text-blue-400'
                        : 'bg-[#1a1a2e]/30 text-gray-400 hover:bg-[#1a1a2e]/50 hover:text-gray-300'
                "
            >
                Refunded
            </button>
        </div>

        <!-- Loading State -->
        <div v-if="isLoading" class="space-y-4">
            <div v-for="i in 5" :key="i" class="bg-[#1a1a2e]/30 rounded-lg p-4 animate-pulse">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-lg bg-[#1a1a2e]/50"></div>
                    <div class="flex-1">
                        <div class="h-5 w-32 bg-[#1a1a2e]/50 rounded mb-2"></div>
                        <div class="h-4 w-24 bg-[#1a1a2e]/50 rounded"></div>
                    </div>
                    <div class="w-20 h-8 bg-[#1a1a2e]/50 rounded-lg"></div>
                </div>
            </div>
        </div>

        <!-- Transactions Table -->
        <div v-else-if="paginatedTransactions.length > 0" class="overflow-hidden rounded-lg border border-[#1a1a2f]/30">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-[#1a1a2f]/30">
                    <thead class="bg-[#050508]/70">
                        <tr>
                            <th
                                scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider"
                            >
                                Transaction
                            </th>
                            <th
                                scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider"
                            >
                                Date
                            </th>
                            <th
                                scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider"
                            >
                                Amount
                            </th>
                            <th
                                scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider"
                            >
                                Credits
                            </th>
                            <th
                                scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider"
                            >
                                Method
                            </th>
                            <th
                                scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider"
                            >
                                Status
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-[#0a0a15]/50 divide-y divide-[#1a1a2f]/30">
                        <tr
                            v-for="transaction in paginatedTransactions"
                            :key="transaction.id"
                            class="hover:bg-[#1a1a2e]/30 transition-colors duration-150"
                        >
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-200">{{ transaction.id }}</div>
                                <div v-if="transaction.reference" class="text-xs text-gray-500">
                                    Ref: {{ transaction.reference }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-300">{{ formatDate(transaction.date) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-300">{{ formatCurrency(transaction.amount) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-indigo-400 font-medium">
                                    {{ transaction.credits.toLocaleString() }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div
                                        class="flex-shrink-0 h-8 w-8 rounded-md bg-[#1a1a2e]/50 flex items-center justify-center mr-2"
                                    >
                                        <component
                                            :is="getMethodIcon(transaction.method)"
                                            class="h-4 w-4 text-gray-400"
                                        />
                                    </div>
                                    <div class="text-sm text-gray-300">{{ getMethodName(transaction.method) }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-medium rounded-full py-1"
                                    :class="getStatusColor(transaction.status)"
                                >
                                    {{ transaction.status.charAt(0).toUpperCase() + transaction.status.slice(1) }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                class="bg-[#050508]/70 px-4 py-3 flex items-center justify-between border-t border-[#1a1a2f]/30 sm:px-6"
            >
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-400">
                            Showing <span class="font-medium">{{ (currentPage - 1) * itemsPerPage + 1 }}</span> to
                            <span class="font-medium">{{ Math.min(currentPage * itemsPerPage, totalItems) }}</span> of
                            <span class="font-medium">{{ totalItems }}</span> results
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                            <button
                                @click="goToPage(currentPage - 1)"
                                :disabled="currentPage === 1"
                                class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-[#1a1a2f]/30 bg-[#0a0a15]/50 text-sm font-medium text-gray-400 hover:bg-[#1a1a2e]/30 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <span class="sr-only">Previous</span>
                                <ChevronLeftIcon class="h-5 w-5" />
                            </button>

                            <button
                                v-for="page in totalPages"
                                :key="page"
                                @click="goToPage(page)"
                                :class="[
                                    'relative inline-flex items-center px-4 py-2 border border-[#1a1a2f]/30 text-sm font-medium',
                                    currentPage === page
                                        ? 'bg-indigo-500/10 text-indigo-400 border-indigo-500/30'
                                        : 'bg-[#0a0a15]/50 text-gray-400 hover:bg-[#1a1a2e]/30',
                                ]"
                            >
                                {{ page }}
                            </button>

                            <button
                                @click="goToPage(currentPage + 1)"
                                :disabled="currentPage === totalPages"
                                class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-[#1a1a2f]/30 bg-[#0a0a15]/50 text-sm font-medium text-gray-400 hover:bg-[#1a1a2e]/30 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <span class="sr-only">Next</span>
                                <ChevronRightIcon class="h-5 w-5" />
                            </button>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else-if="!isLoading" class="text-center py-12 bg-[#0a0a15]/50 rounded-lg border border-[#1a1a2f]/30">
            <DollarIcon class="mx-auto h-12 w-12 text-gray-500" />
            <h3 class="mt-2 text-sm font-medium text-gray-300">No transactions found</h3>
            <p class="mt-1 text-sm text-gray-500">
                {{
                    searchQuery || filterStatus
                        ? 'Try adjusting your filters'
                        : 'You have not made any credit topups yet'
                }}
            </p>
            <div class="mt-6">
                <Button @click="resetFilters" variant="secondary">
                    <template #icon>
                        <FilterIcon class="h-4 w-4" />
                    </template>
                    Reset Filters
                </Button>
            </div>
        </div>

        <!-- Account Balance Card -->
        <CardComponent cardTitle="Current Account Balance" class="mt-8">
            <div class="flex items-center gap-6">
                <div class="p-4 rounded-lg bg-indigo-500/10 flex items-center justify-center">
                    <DollarIcon class="h-8 w-8 text-indigo-400" />
                </div>
                <div>
                    <p class="text-sm text-gray-400">Available Credits</p>
                    <p class="text-2xl font-bold text-indigo-400">{{ Session.getInfo('credits') || '0' }}</p>
                </div>
            </div>

            <div class="mt-4 pt-4 border-t border-[#1a1a2f]/30">
                <p class="text-sm text-gray-400">Need more credits?</p>
                <div class="mt-2">
                    <Button variant="primary">
                        <template #icon>
                            <DollarIcon class="h-4 w-4" />
                        </template>
                        Purchase Credits
                    </Button>
                </div>
            </div>
        </CardComponent>
    </div>
</template>

<style scoped>
/* Animation for loading skeleton */
@keyframes pulse {
    0%,
    100% {
        opacity: 0.5;
    }
    50% {
        opacity: 0.8;
    }
}

.animate-pulse {
    animation: pulse 1.5s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Staggered animation delay for loading items */
.animate-pulse:nth-child(1) {
    animation-delay: 0s;
}
.animate-pulse:nth-child(2) {
    animation-delay: 0.1s;
}
.animate-pulse:nth-child(3) {
    animation-delay: 0.2s;
}
.animate-pulse:nth-child(4) {
    animation-delay: 0.3s;
}
.animate-pulse:nth-child(5) {
    animation-delay: 0.4s;
}

/* Smooth transitions */
.transition-colors {
    transition-property: background-color, border-color, color, fill, stroke;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 200ms;
}
</style>
