<template>
    <LayoutDashboard>
        <div class="p-6 space-y-6">
            <Header />
            <div class="grid gap-6 lg:grid-cols-4">
                <!-- Left Column -->
                <div class="space-y-6">
                    <SupportPin />
                </div>
                <!-- Main Content -->
                <div class="lg:col-span-3 space-y-6">
                    <!-- Recent Tickets -->
                    <TicketList />
                </div>
            </div>
        </div>
    </LayoutDashboard>
</template>

<script setup lang="ts">
import LayoutDashboard from '@/components/client/LayoutDashboard.vue';
import SupportPin from '@/components/client/Dashboard/Main/SupportPin.vue';
import TicketList from '@/components/client/Dashboard/Main/TicketList.vue';
import Header from '@/components/client/Dashboard/Main/Header.vue';
import { MythicalDOM } from '@/mythicaldash/MythicalDOM';

MythicalDOM.setPageTitle(MythicalDOM.getTranslation('dashboard.title'));
</script>
