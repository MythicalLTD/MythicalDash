<template>
    <LayoutDashboard>
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-100 mb-2">Account Management</h1>
            <p class="text-gray-400">View and manage your account settings and preferences</p>
        </div>

        <!-- User Profile Card -->
        <LayoutAccount class="mb-8" />

        <!-- Tabs Card -->
        <div class="bg-[#0a0a15]/50 border border-[#1a1a2f]/30 rounded-xl shadow-lg overflow-hidden">
            <!-- Tabs Navigation -->
            <div class="border-b border-[#1a1a2f]/30 bg-[#050508]/70">
                <div class="flex overflow-x-auto scrollbar-hide">
                    <button
                        v-for="tab in tabs"
                        :key="tab.name"
                        class="flex items-center gap-2 px-6 py-4 text-sm font-medium whitespace-nowrap transition-all duration-200"
                        :class="[
                            activeTab === tab.name
                                ? 'text-indigo-400 border-b-2 border-indigo-500 bg-[#0a0a15]/50'
                                : 'text-gray-400 hover:text-gray-200 hover:bg-[#0a0a15]/30',
                        ]"
                        @click="activeTab = tab.name"
                    >
                        <component :is="tab.icon" class="w-4 h-4" />
                        {{ tab.name }}
                    </button>
                </div>
            </div>

            <!-- Tab Content -->
            <div class="p-6">
                <div class="tab-content">
                    <SettingsTab v-if="activeTab === 'Settings'" />
                    <SecurityTab v-if="activeTab === 'Security'" />
                    <MailsTab v-if="activeTab === 'Mails'" />
                    <ActivitiesTab v-if="activeTab === 'Activities'" />
                    <ApiKey v-if="activeTab === 'API Key'" />
                    <LinkedAccounts v-if="activeTab === 'Linked Accounts'" />
                    <TopupLogs v-if="activeTab === 'Topup Logs'" />
                </div>
            </div>
        </div>
    </LayoutDashboard>
</template>

<script setup lang="ts">
import LayoutDashboard from '@/components/client/LayoutDashboard.vue';
import SettingsTab from '@/components/client/Dashboard/Account/Settings.vue';
import SecurityTab from '@/components/client/Dashboard/Account/Security.vue';
import MailsTab from '@/components/client/Dashboard/Account/Mails.vue';
import ActivitiesTab from '@/components/client/Dashboard/Account/Activities.vue';
import ApiKey from '@/components/client/Dashboard/Account/ApiKey.vue';
import LinkedAccounts from '@/components/client/Dashboard/Account/LinkedAccounts.vue';
import TopupLogs from '@/components/client/Dashboard/Account/TopupLogs.vue';
import LayoutAccount from '@/components/client/Dashboard/Account/Layout.vue';

import { ref } from 'vue';
import {
    Settings as SettingsIcon,
    Lock as SecurityIcon,
    Mail as MailIcon,
    Bell as ActivityIcon,
    Key as ApiKeyIcon,
    Link as LinkedAccountsIcon,
    DollarSign as TopupLogsIcon,
} from 'lucide-vue-next';

const activeTab = ref('Settings');

const tabs = [
    { name: 'Settings', icon: SettingsIcon },
    { name: 'Security', icon: SecurityIcon },
    { name: 'Linked Accounts', icon: LinkedAccountsIcon },
    { name: 'Topup Logs', icon: TopupLogsIcon },
    { name: 'Mails', icon: MailIcon },
    { name: 'Activities', icon: ActivityIcon },
    { name: 'API Key', icon: ApiKeyIcon },
];
</script>

<style scoped>
/* Hide scrollbar for Chrome, Safari and Opera */
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}

/* Hide scrollbar for IE, Edge and Firefox */
.scrollbar-hide {
    -ms-overflow-style: none; /* IE and Edge */
    scrollbar-width: none; /* Firefox */
}

/* Tab content animation */
.tab-content {
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(5px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
