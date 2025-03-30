<template>
    <LayoutDashboard>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-400">Ticket Details</h1>
            <button
                @click="router.push('/mc-admin/tickets')"
                class="bg-gray-700 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:bg-gray-600 flex items-center"
            >
                <ArrowLeftIcon class="w-4 h-4 mr-2" />
                Back to Tickets
            </button>
        </div>

        <div v-if="loading" class="flex justify-center items-center py-12">
            <LoaderIcon class="w-8 h-8 animate-spin text-pink-400" />
        </div>

        <div v-else-if="error" class="bg-red-500/20 text-red-400 p-4 rounded-lg mb-6">
            {{ error }}
        </div>

        <div v-else class="bg-gray-800/50 backdrop-blur-md rounded-lg p-6">
            <!-- Ticket Header -->
            <div class="flex flex-col md:flex-row justify-between mb-6 border-b border-gray-700 pb-4">
                <div>
                    <div class="flex items-center mb-1">
                        <TicketIcon class="w-5 h-5 mr-2 text-pink-400" />
                        <h2 class="text-xl font-medium text-white">{{ ticket.title }}</h2>
                    </div>
                    <div class="text-gray-400">Ticket #{{ ticket.id }}</div>
                </div>
                
                <!-- Ticket Meta Information -->
                <div class="flex flex-wrap gap-3 mt-3 md:mt-0">
                    <div class="flex items-center">
                        <span class="text-gray-400 text-sm mr-2">Priority:</span>
                        <span
                            :class="{
                                'px-2 py-0.5 rounded-full text-xs font-medium': true,
                                'bg-blue-500/20 text-blue-400': ticket.priority === 'low',
                                'bg-yellow-500/20 text-yellow-400': ticket.priority === 'medium',
                                'bg-orange-500/20 text-orange-400': ticket.priority === 'high',
                                'bg-red-500/20 text-red-400': ticket.priority === 'urgent',
                            }"
                        >
                            {{ formatPriority(ticket.priority) }}
                        </span>
                    </div>
                    <div class="flex items-center">
                        <span class="text-gray-400 text-sm mr-2">Status:</span>
                        <span
                            :class="{
                                'px-2 py-0.5 rounded-full text-xs font-medium': true,
                                'bg-green-500/20 text-green-400': ticket.status === 'open',
                                'bg-blue-500/20 text-blue-400': ticket.status === 'inprogress',
                                'bg-gray-500/20 text-gray-400': ticket.status === 'closed',
                            }"
                        >
                            {{ formatStatus(ticket.status) }}
                        </span>
                    </div>
                    <div class="flex items-center">
                        <ClockIcon class="w-4 h-4 mr-1 text-gray-400" />
                        <span class="text-gray-400 text-sm">{{ new Date(ticket.date).toLocaleString() }}</span>
                    </div>
                </div>
            </div>

            <!-- Ticket Description -->
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-300 mb-2">Description</h3>
                <div class="bg-gray-900/50 p-4 rounded-lg text-gray-200 whitespace-pre-wrap">
                    {{ ticket.description }}
                </div>
            </div>

            <!-- User Information -->
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-300 mb-2">User Information</h3>
                <div class="bg-gray-900/50 p-4 rounded-lg">
                    <div class="flex items-center mb-4">
                        <img 
                            :src="ticket.user_details.avatar || 'https://www.gravatar.com/avatar/00000000000000000000000000000000?d=mp&f=y'" 
                            :alt="ticket.user_details.name"
                            class="w-12 h-12 rounded-full mr-4"
                        />
                        <div>
                            <div class="font-medium text-white">{{ ticket.user_details.name }}</div>
                            <div class="text-gray-400">@{{ ticket.user_details.username }}</div>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <div class="flex items-center">
                            <span class="text-gray-400 w-24">UUID:</span>
                            <span class="text-gray-200 font-mono text-sm">{{ ticket.user_details.uuid }}</span>
                        </div>
                        <div class="flex items-center">
                            <span class="text-gray-400 w-24">Email:</span>
                            <span class="text-gray-200">{{ ticket.user_details.email || 'Not provided' }}</span>
                        </div>
                        <div class="flex items-center">
                            <span class="text-gray-400 w-24">Department:</span>
                            <span class="text-gray-200">{{ ticket.department }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Admin Actions -->
            <div v-if="ticket.status !== 'closed'" class="flex justify-end border-t border-gray-700 pt-4">
                <button
                    @click="openCloseModal"
                    class="px-4 py-2 bg-red-500 rounded-lg text-white hover:bg-red-600 transition-colors flex items-center"
                >
                    <XCircle class="w-4 h-4 mr-2" />
                    Close Ticket
                </button>
            </div>
        </div>

        <!-- Confirmation Modal -->
        <div v-if="showConfirmModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-gray-800 rounded-lg p-6 max-w-md w-full">
                <h3 class="text-xl font-medium text-gray-100 mb-4">Confirm Action</h3>
                <p class="text-gray-300 mb-4">
                    Are you sure you want to close ticket #{{ ticket.id }}?
                </p>
                <div class="flex justify-end space-x-3">
                    <button
                        @click="closeModal"
                        class="px-4 py-2 border border-gray-600 rounded-lg text-gray-300 hover:bg-gray-700 transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        @click="confirmClose"
                        :disabled="processing"
                        class="px-4 py-2 bg-red-500 rounded-lg text-white hover:bg-red-600 transition-colors flex items-center"
                    >
                        <LoaderIcon v-if="processing" class="animate-spin w-4 h-4 mr-2" />
                        <span v-else>Close Ticket</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Success/Error Toast Notification -->
        <div v-if="notification.show" 
            class="fixed bottom-4 right-4 py-2 px-4 rounded-lg shadow-lg transition-all duration-300"
            :class="{
                'bg-green-500/20 text-green-400 border border-green-500/30': notification.type === 'success',
                'bg-red-500/20 text-red-400 border border-red-500/30': notification.type === 'error'
            }">
            {{ notification.message }}
        </div>
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import LayoutDashboard from '@/components/admin/LayoutDashboard.vue';
import { 
    ArrowLeftIcon, 
    LoaderIcon, 
    TicketIcon, 
    ClockIcon, 
    XCircle 
} from 'lucide-vue-next';

// Ticket interface
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
    date: string;
    user_details: UserDetails;
}

const router = useRouter();
const route = useRoute();
const ticketId = Number(route.params.id);

const loading = ref(true);
const error = ref('');
const ticket = ref<Ticket>({
    id: 0,
    user: '',
    department: 0,
    priority: '',
    status: '',
    title: '',
    description: '',
    deleted: 'false',
    locked: 'false',
    date: '',
    user_details: {
        uuid: '',
        username: '',
        name: '',
        email: '',
        avatar: ''
    }
});

const showConfirmModal = ref(false);
const processing = ref(false);
const notification = ref({
    show: false,
    message: '',
    type: 'success' as 'success' | 'error'
});

// Format functions
const formatPriority = (priority: string): string => {
    const map: Record<string, string> = {
        low: 'Low',
        medium: 'Medium',
        high: 'High',
        urgent: 'Urgent',
    };
    return map[priority] || priority;
};

const formatStatus = (status: string): string => {
    const map: Record<string, string> = {
        open: 'Open',
        inprogress: 'In Progress',
        closed: 'Closed',
    };
    return map[status] || status;
};

// Fetch ticket details
const fetchTicketDetails = async () => {
    loading.value = true;
    try {
        const response = await fetch(`/api/admin/tickets`, {
            method: 'GET',
            headers: {
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error('Failed to fetch ticket details');
        }

        const data = await response.json();
        
        if (data.success) {
            // Find the ticket with the matching ID
            const foundTicket = data.tickets.find((t: Ticket) => t.id === ticketId);
            
            if (!foundTicket) {
                error.value = 'Ticket not found';
                return;
            }
            
            ticket.value = foundTicket;
        } else {
            error.value = data.message || 'Failed to load ticket data';
        }
    } catch (err) {
        console.error('Error fetching ticket details:', err);
        error.value = 'An error occurred while fetching ticket details';
    } finally {
        loading.value = false;
    }
};

// Modal control
const openCloseModal = () => {
    showConfirmModal.value = true;
};

const closeModal = () => {
    showConfirmModal.value = false;
    processing.value = false;
};

// Close ticket action
const confirmClose = async () => {
    processing.value = true;
    
    try {
        await closeTicket();
    } catch (error) {
        console.error('Error during close action:', error);
        showNotification('Failed to close ticket. Please try again.', 'error');
    } finally {
        processing.value = false;
        closeModal();
    }
};

const closeTicket = async () => {
    try {
        // Create FormData for the close request
        const formData = new FormData();
        
        // Send close request to API
        const response = await fetch(`/api/admin/tickets/${ticketId}/close`, {
            method: 'POST',
            body: formData,
        });

        const data = await response.json();

        if (data.success) {
            showNotification('Ticket closed successfully', 'success');
            // Refresh ticket details
            await fetchTicketDetails();
        } else {
            showNotification(data.message || 'Failed to close ticket', 'error');
        }
    } catch (err) {
        console.error('Error closing ticket:', err);
        throw err;
    }
};

// Notification handler
const showNotification = (message: string, type: 'success' | 'error') => {
    notification.value = {
        show: true,
        message,
        type
    };
    
    // Hide notification after 3 seconds
    setTimeout(() => {
        notification.value.show = false;
    }, 3000);
};

onMounted(() => {
    fetchTicketDetails();
});
</script> 