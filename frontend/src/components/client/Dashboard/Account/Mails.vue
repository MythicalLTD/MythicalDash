<script setup lang="ts">
import TableTanstack from '@/components/client/ui/Table/TableTanstack.vue';
import Mails from '@/mythicaldash/Mails';
import { format } from 'date-fns';
import { h, onErrorCaptured, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { MythicalDOM } from '@/mythicaldash/MythicalDOM';

const { t } = useI18n();
MythicalDOM.setPageTitle(t('account.pages.emails.page.title'));

interface Email {
    id: string;
    subject: string;
    from: string;
    date: string;
}
const emails = ref<Email[]>([]);
const loading = ref(true);
const error = ref<string | null>(null);

const fetchMails = async () => {
    try {
        const response = await Mails.get();
        emails.value = response;
        console.log(response);
    } catch (err) {
        error.value = err instanceof Error ? err.message : 'An unknown error occurred';
    } finally {
        loading.value = false;
    }
};

onMounted(fetchMails);

onErrorCaptured((err) => {
    error.value = t('account.pages.emails.alerts.error.generic');
    console.error('Error captured:', err);
    return false;
});

const columnsEmails = [
    {
        accessorKey: t('account.pages.emails.page.table.columns.id'),
        header: 'ID',
    },
    {
        accessorKey: 'subject',
        header: t('account.pages.emails.page.table.columns.subject'),
    },
    {
        accessorKey: 'from',
        header: t('account.pages.emails.page.table.columns.from'),
    },
    {
        accessorKey: 'date',
        header: t('account.pages.emails.page.table.columns.date'),
        cell: (info: { getValue: () => string | number | Date }) => format(new Date(info.getValue()), 'MMM d, yyyy'),
    },
    {
        accessorKey: 'actions',
        header: t('account.pages.emails.page.table.columns.actions'),
        enableSorting: false,
        cell: ({ row }: { row: { original: Email } }) =>
            h(
                'button',
                {
                    class: 'px-4 py-2 bg-blue-500 text-white rounded-sm hover:bg-blue-600',
                    target: '_blank',
                    rel: 'noopener noreferrer',
                    onClick: () => (window.location.href = `/api/user/session/emails/${row.original.id}/raw`),
                },
                t('account.pages.emails.page.table.results.viewButton'),
            ),
    },
];
</script>
<template>
    <div>
        <div v-if="loading" class="text-center py-4">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-gray-900"></div>
            <p class="mt-2">Loading emails...</p>
        </div>

        <div v-else-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-sm" role="alert">
            <p>{{ error }}</p>
        </div>

        <div v-else class="overflow-x-auto">
            <TableTanstack :data="emails" :columns="columnsEmails" :tableName="t('account.pages.emails.page.title')" />
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
    /* IE and Edge */
    scrollbar-width: none;
    /* Firefox */
}

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
