<script setup lang="ts">
import CardComponent from '@/components/client/ui/Card/CardComponent.vue';
import Tickets from '@/mythicaldash/Tickets';
import Button from '@/components/client/ui/Button.vue';
import { ref, onMounted, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useSettingsStore } from '@/stores/settings';
import { Ticket, FileText, Calendar, Clock } from 'lucide-vue-next';

const Settings = useSettingsStore();

interface Department {
    id: number;
    name: string;
    description: string;
    time_open: string;
    time_close: string;
    enabled: string;
    deleted: string;
    locked: string;
    date: string;
}

interface TicketData {
    id: number;
    user: string;
    department: Department;
    priority: string;
    status: string;
    title: string;
    description: string;
    deleted: string;
    locked: string;
    date: string;
    department_id: number;
}

const isTicketsEnabled = computed(() => {
    return Settings.getSetting('allow_tickets') === 'true';
});

const { t } = useI18n();
const recentTickets = ref<TicketData[]>([]);
const loading = ref(true);
const error = ref<string | null>(null);

import { format } from 'date-fns';
import { useRouter } from 'vue-router';

const router = useRouter();

const goToTicketList = () => {
    router.push('/ticket');
};

const getPriorityIcon = (priority: string) => {
    switch (priority.toLowerCase()) {
        case 'urgent':
            return '🔴';
        case 'high':
            return '🟠';
        case 'medium':
            return '🟡';
        case 'low':
            return '🟢';
        default:
            return '⚪';
    }
};

const getPriorityColor = (priority: string) => {
    switch (priority.toLowerCase()) {
        case 'urgent':
            return 'text-red-300 bg-red-900/10 border-red-800/20';
        case 'high':
            return 'text-orange-300 bg-orange-900/10 border-orange-800/20';
        case 'medium':
            return 'text-yellow-300 bg-yellow-900/10 border-yellow-800/20';
        case 'low':
            return 'text-green-300 bg-green-900/10 border-green-800/20';
        default:
            return 'text-gray-300 bg-gray-900/10 border-gray-800/20';
    }
};

const getStatusColor = (status: string) => {
    switch (status.toLowerCase()) {
        case 'open':
            return 'text-green-300 bg-green-900/10';
        case 'closed':
            return 'text-gray-300 bg-gray-900/10';
        case 'waiting':
            return 'text-yellow-300 bg-yellow-900/10';
        case 'replied':
            return 'text-blue-300 bg-blue-900/10';
        case 'inprogress':
        case 'in progress':
            return 'text-purple-300 bg-purple-900/10';
        default:
            return 'text-gray-300 bg-gray-900/10';
    }
};

const formatStatus = (status: string) => {
    return status
        .split(' ')
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
        .join(' ')
        .replace('Inprogress', 'In Progress');
};

const formatPriority = (priority: string) => {
    return priority.charAt(0).toUpperCase() + priority.slice(1).toLowerCase();
};

const truncateDescription = (description: string, maxLength: number = 90) => {
    if (description.length <= maxLength) return description;
    return description.substring(0, maxLength) + '...';
};

const fetchRecentTickets = async () => {
    loading.value = true;
    error.value = null;

    try {
        const response = await Tickets.getTickets();
        if (response.success && Array.isArray(response.tickets)) {
            recentTickets.value = response.tickets.slice(0, 3).map((ticket: TicketData) => ({
                ...ticket,
                date: format(new Date(ticket.date), 'PPP'),
            }));
        } else {
            throw new Error(response.error || 'Failed to fetch tickets');
        }
    } catch (err) {
        console.error('Error fetching recent tickets:', err);
        error.value = err instanceof Error ? err.message : 'Failed to fetch tickets';
    } finally {
        loading.value = false;
    }
};

onMounted(fetchRecentTickets);
</script>

<template>
    <!-- Recent Tickets -->
    <CardComponent
        v-if="isTicketsEnabled"
        :cardTitle="t('Components.Tickets.title')"
        :cardDescription="t('Components.Tickets.description')"
    >
        <!-- Loading State -->
        <div v-if="loading" class="flex justify-center py-8">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-gray-500"></div>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="text-center py-8">
            <div class="text-red-400 text-sm mb-2">{{ t('Components.Tickets.error.failed_to_load') }}</div>
            <button @click="fetchRecentTickets" class="text-xs text-gray-400 hover:text-gray-300">
                {{ t('Components.Tickets.error.retry') }}
            </button>
        </div>

        <!-- Tickets List -->
        <div v-else-if="recentTickets.length > 0" class="space-y-3">
            <div
                v-for="ticket in recentTickets"
                :key="ticket.id"
                class="bg-gray-800/20 rounded-lg p-4 border border-gray-700/30 hover:border-gray-600/40 transition-all duration-200 hover:bg-gray-800/30"
            >
                <!-- Header with ID, Priority, and Status -->
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-mono text-gray-400 bg-gray-700/30 px-2 py-1 rounded">
                            #{{ ticket.id }}
                        </span>
                        <span
                            :class="['text-xs font-medium px-2 py-1 rounded border', getPriorityColor(ticket.priority)]"
                        >
                            {{ getPriorityIcon(ticket.priority) }} {{ formatPriority(ticket.priority) }}
                        </span>
                    </div>
                    <span :class="['px-2 py-1 rounded text-xs font-medium', getStatusColor(ticket.status)]">
                        {{ formatStatus(ticket.status) }}
                    </span>
                </div>

                <!-- Title -->
                <h3 class="font-medium text-white text-sm mb-2 line-clamp-1">
                    {{ ticket.title }}
                </h3>

                <!-- Description Preview -->
                <p class="text-sm text-gray-400 mb-3 leading-relaxed">
                    {{ truncateDescription(ticket.description) }}
                </p>

                <!-- Footer with Department and Date -->
                <div class="flex items-center justify-between text-xs text-gray-500">
                    <div class="flex items-center gap-1">
                        <FileText class="w-3 h-3" />
                        <span>{{ ticket.department.name }}</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <Calendar class="w-3 h-3" />
                        <span>{{ ticket.date }}</span>
                    </div>
                </div>

                <!-- Department Hours (if available) -->
                <div
                    v-if="ticket.department.time_open && ticket.department.time_close"
                    class="text-xs text-gray-500 mt-2 flex items-center gap-1"
                >
                    <Clock class="w-3 h-3" />
                    <span
                        >{{ t('Components.Tickets.departmentHours') }}: {{ ticket.department.time_open }} -
                        {{ ticket.department.time_close }}</span
                    >
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-8">
            <Ticket class="w-12 h-12 text-gray-500 mx-auto mb-3" />
            <h3 class="text-sm font-medium text-gray-400 mb-1">{{ t('Components.Tickets.empty.title') }}</h3>
            <p class="text-xs text-gray-500">{{ t('Components.Tickets.empty.description') }}</p>
        </div>

        <!-- Action Button -->
        <div v-if="recentTickets.length > 0" class="border-t border-gray-700/30 mt-4 pt-4">
            <Button
                @click="goToTicketList"
                class="w-full px-4 py-2 bg-gray-700/50 hover:bg-gray-600/50 text-gray-300 border border-gray-600/30 hover:border-gray-500/40 transition-colors"
            >
                {{ t('Components.Tickets.viewMore') }}
            </Button>
        </div>
        <div v-else-if="!loading && !error" class="pt-2">
            <Button
                @click="() => router.push('/ticket/create')"
                class="w-full px-4 py-2 bg-gray-700/50 hover:bg-gray-600/50 text-gray-300 border border-gray-600/30 hover:border-gray-500/40 transition-colors"
            >
                {{ t('Components.Tickets.createFirst') }}
            </Button>
        </div>
    </CardComponent>
</template>

<style scoped>
.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

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
