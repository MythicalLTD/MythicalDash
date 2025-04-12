<template>
    <LayoutDashboard>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-400">Tickets</h1>
        </div>
        <!-- Tickets Table using TableTanstack -->
        <div v-if="loading" class="flex justify-center items-center py-10">
            <LoaderCircle class="h-8 w-8 animate-spin text-pink-400" />
        </div>
        <TableTanstack v-else :data="tickets" :columns="columns" tableName="Tickets" />

        <!-- Success/Error Toast Notification -->
        <div
            v-if="notification.show"
            class="fixed bottom-4 right-4 py-2 px-4 rounded-lg shadow-lg transition-all duration-300"
            :class="{
                'bg-green-500/20 text-green-400 border border-green-500/30': notification.type === 'success',
                'bg-red-500/20 text-red-400 border border-red-500/30': notification.type === 'error',
            }"
        >
            {{ notification.message }}
        </div>
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, onMounted, h } from 'vue';
import LayoutDashboard from '@/components/admin/LayoutDashboard.vue';
import TableTanstack from '@/components/client/ui/Table/TableTanstack.vue';
import { LoaderCircle, TicketIcon, EyeIcon } from 'lucide-vue-next';
import { useRouter } from 'vue-router';

// Ticket interface based on the API response
interface UserDetails {
    uuid: string;
    username: string;
    name: string;
    email: string;
    avatar: string;
}

interface Ticket {
    id: number;
    user: string;
    department: number;
    priority: string;
    status: string;
    title: string;
    description: string;
    deleted: string;
    locked: string;
    user_details: UserDetails;
}

const router = useRouter();
const tickets = ref<Ticket[]>([]);
const loading = ref(true);
const notification = ref({
    show: false,
    message: '',
    type: 'success' as 'success' | 'error',
});

// Priority and status mappings for display
const priorityMap: Record<string, { name: string; color: string }> = {
    low: { name: 'Low', color: 'bg-blue-500/20 text-blue-400' },
    medium: { name: 'Medium', color: 'bg-yellow-500/20 text-yellow-400' },
    high: { name: 'High', color: 'bg-orange-500/20 text-orange-400' },
    urgent: { name: 'Urgent', color: 'bg-red-500/20 text-red-400' },
};

const statusMap: Record<string, { name: string; color: string }> = {
    open: { name: 'Open', color: 'bg-green-500/20 text-green-400' },
    inprogress: { name: 'In Progress', color: 'bg-blue-500/20 text-blue-400' },
    closed: { name: 'Closed', color: 'bg-gray-500/20 text-gray-400' },
};

// Define columns for TableTanstack
const columns = [
    {
        accessorKey: 'id',
        header: 'ID',
        cell: (info: { getValue: () => number }) => {
            const id = info.getValue();
            return h('div', { class: 'flex items-center' }, [
                h(TicketIcon, { class: 'w-3 h-3 mr-1 text-gray-400' }),
                h('span', {}, `#${id}`),
            ]);
        },
    },
    {
        accessorKey: 'title',
        header: 'Title',
        cell: (info: { getValue: () => string }) => info.getValue(),
    },
    {
        id: 'user',
        header: 'User',
        cell: (info: { row: { original: Ticket } }) => {
            const ticket = info.row.original;
            const userDetails = ticket.user_details;

            return h('div', { class: 'flex items-center' }, [
                h('img', {
                    src:
                        userDetails.avatar ||
                        'https://www.gravatar.com/avatar/00000000000000000000000000000000?d=mp&f=y',
                    alt: userDetails.name,
                    class: 'w-6 h-6 rounded-full mr-2',
                }),
                h('span', {}, userDetails.username),
            ]);
        },
    },
    {
        accessorKey: 'priority',
        header: 'Priority',
        cell: (info: { getValue: () => string }) => {
            const priority = info.getValue();
            const data = priorityMap[priority] || { name: priority, color: 'bg-gray-500/20 text-gray-400' };

            return h(
                'span',
                {
                    class: `px-2 py-1 rounded-full text-xs font-medium ${data.color}`,
                },
                data.name,
            );
        },
    },
    {
        accessorKey: 'status',
        header: 'Status',
        cell: (info: { getValue: () => string }) => {
            const status = info.getValue();
            const data = statusMap[status] || { name: status, color: 'bg-gray-500/20 text-gray-400' };

            return h(
                'span',
                {
                    class: `px-2 py-1 rounded-full text-xs font-medium ${data.color}`,
                },
                data.name,
            );
        },
    },
    {
        id: 'actions',
        header: 'Actions',
        cell: (info: { row: { original: Ticket } }) => {
            const ticket = info.row.original;

            return h('div', { class: 'flex space-x-2' }, [
                h(
                    'button',
                    {
                        class: 'p-1 text-gray-400 hover:text-blue-400 transition-colors',
                        title: 'View Details',
                        onClick: () => viewTicket(ticket),
                    },
                    [h(EyeIcon, { class: 'w-4 h-4' })],
                ),
            ]);
        },
    },
];

// Fetch tickets from API
const fetchTickets = async () => {
    loading.value = true;
    try {
        const response = await fetch('/api/admin/tickets', {
            method: 'GET',
            headers: {
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error('Failed to fetch tickets');
        }

        const data = await response.json();

        if (data.success) {
            tickets.value = data.tickets;
        } else {
            console.error('Failed to load tickets:', data.message);
            showNotification('Failed to load tickets: ' + (data.message || 'Unknown error'), 'error');
        }
    } catch (error) {
        console.error('Error fetching tickets:', error);
        showNotification('Error fetching tickets. Please try again.', 'error');
    } finally {
        loading.value = false;
    }
};

const viewTicket = (ticket: Ticket) => {
    // Navigate to ticket details view
    router.push(`/mc-admin/tickets/${ticket.id}`);
};

const showNotification = (message: string, type: 'success' | 'error') => {
    notification.value = {
        show: true,
        message,
        type,
    };

    // Hide notification after 3 seconds
    setTimeout(() => {
        notification.value.show = false;
    }, 3000);
};

onMounted(() => {
    fetchTickets();
});
</script>
