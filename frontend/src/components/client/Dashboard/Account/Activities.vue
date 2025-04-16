<script setup lang="ts">
import { ref, onMounted, onErrorCaptured } from 'vue';
import { format } from 'date-fns';
import TableTanstack from '@/components/client/ui/Table/TableTanstack.vue';
import Activities from '@/mythicaldash/Activities';
import { useI18n } from 'vue-i18n';
import { MythicalDOM } from '@/mythicaldash/MythicalDOM';
import LoadingAnimation from '@/components/client/ui/LoadingAnimation.vue';
import { Activity, AlertCircle } from 'lucide-vue-next';

const { t } = useI18n();
MythicalDOM.setPageTitle(t('account.pages.activity.page.title'));

interface Activity {
    id: number;
    user: string;
    action: string;
    ip_address: string;
    deleted: boolean | string;
    locked: boolean | string;
    date: string;
    context: string;
}

const activities = ref<Activity[]>([]);
const loading = ref(true);
const error = ref<string | null>(null);

const fetchActivities = async () => {
    try {
        const response = await Activities.get();
        activities.value = response;
    } catch (err) {
        error.value = err instanceof Error ? err.message : t('account.pages.activity.page.table.error');
    } finally {
        loading.value = false;
    }
};

onMounted(fetchActivities);

onErrorCaptured((err) => {
    error.value = 'An unexpected error occurred';
    console.error('Error captured:', err);
    return false;
});

const columnsActivities = [
    {
        accessorKey: 'action',
        header: t('account.pages.activity.page.table.columns.action'),
    },
    {
        accessorKey: 'ip_address',
        header: t('account.pages.activity.page.table.columns.ip'),
    },
    {
        accessorKey: 'context',
        header: t('account.pages.activity.page.table.columns.context'),
        cell: (info: { getValue: () => string | number | Date }) => {
            return info.getValue();
        },
    },
    {
        accessorKey: 'date',
        header: t('account.pages.activity.page.table.columns.date'),
        cell: (info: { getValue: () => string | number | Date }) =>
            format(new Date(info.getValue()), 'MMM d, yyyy HH:mm'),
    },
];
</script>

<template>
    <div class="space-y-6">
        <!-- Header -->
        <div>
            <h2 class="text-xl font-bold text-gray-100 mb-2">{{ t('account.pages.activity.page.title') }}</h2>
            <p class="text-gray-400">{{ t('account.pages.activity.page.description') }}</p>
        </div>

        <!-- Loading State -->
        <LoadingAnimation
            v-if="loading"
            loadingText="Loading Activities"
            description="Please wait while we fetch your activity history"
        />

        <!-- Error State -->
        <div v-else-if="error" class="bg-red-500/10 border border-red-500/20 rounded-lg p-4 flex items-start gap-3">
            <div class="p-2 rounded-lg bg-red-500/10">
                <AlertCircle class="h-5 w-5 text-red-400" />
            </div>
            <div>
                <h3 class="text-sm font-medium text-red-400">{{ t('account.pages.activity.page.table.error') }}</h3>
                <p class="mt-1 text-sm text-gray-400">{{ error }}</p>
            </div>
        </div>

        <!-- Empty State -->
        <div
            v-else-if="activities.length === 0"
            class="bg-[#1a1a2e]/50 border border-[#2a2a3f]/30 rounded-lg p-8 text-center"
        >
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-indigo-500/10 flex items-center justify-center">
                <Activity class="h-8 w-8 text-indigo-400" />
            </div>
            <h3 class="text-lg font-medium text-gray-200 mb-1">
                {{ t('account.pages.activity.page.table.noResults') }}
            </h3>
            <p class="text-sm text-gray-400">{{ t('account.pages.activity.page.table.info') }}</p>
        </div>

        <!-- Activities Table -->
        <div v-else class="overflow-x-auto">
            <TableTanstack
                :data="activities"
                :columns="columnsActivities"
                :tableName="t('account.pages.activity.page.title')"
            />
        </div>
    </div>
</template>

<style scoped>
/* Hide scrollbar for Chrome, Safari and Opera */
.overflow-x-auto::-webkit-scrollbar {
    display: none;
}

/* Hide scrollbar for IE, Edge and Firefox */
.overflow-x-auto {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

/* Smooth transitions */
.transition-all {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 200ms;
}
</style>
