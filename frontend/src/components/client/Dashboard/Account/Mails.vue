<script setup lang="ts">
import TableTanstack from '@/components/client/ui/Table/TableTanstack.vue';
import Mails from '@/mythicaldash/Mails';
import { format } from 'date-fns';
import { h, onErrorCaptured, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { MythicalDOM } from '@/mythicaldash/MythicalDOM';
import LoadingAnimation from '@/components/client/ui/LoadingAnimation.vue';
import { Mail, Download } from 'lucide-vue-next';

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
                    class: 'inline-flex items-center justify-center gap-2 px-3 py-1.5 text-sm font-medium rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition-all duration-200 shadow-sm shadow-indigo-900/20 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-indigo-500/50 focus:ring-offset-[#12121f]',
                    target: '_blank',
                    rel: 'noopener noreferrer',
                    onClick: () => (window.location.href = `/api/user/session/emails/${row.original.id}/raw`),
                },
                [h(Download, { class: 'h-4 w-4' }), t('account.pages.emails.page.table.results.viewButton')],
            ),
    },
];
</script>

<template>
    <div class="space-y-6">
        <!-- Header -->
        <div>
            <h2 class="text-xl font-bold text-gray-100 mb-2">{{ t('account.pages.emails.page.title') }}</h2>
            <p class="text-gray-400">{{ t('account.pages.emails.page.description') }}</p>
        </div>

        <!-- Loading State -->
        <LoadingAnimation
            v-if="loading"
            :loadingText="t('account.pages.emails.page.loadingText')"
            :description="t('account.pages.emails.page.loadingDescription')"
        />

        <!-- Error State -->
        <div v-else-if="error" class="bg-red-500/10 border border-red-500/20 rounded-lg p-4 flex items-start gap-3">
            <div class="p-2 rounded-lg bg-red-500/10">
                <svg
                    class="h-5 w-5 text-red-400"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                >
                    <path
                        fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                        clip-rule="evenodd"
                    />
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-medium text-red-400">{{ t('account.pages.emails.page.error') }}</h3>
                <p class="mt-1 text-sm text-gray-400">{{ error }}</p>
            </div>
        </div>

        <!-- Empty State -->
        <div
            v-else-if="emails.length === 0"
            class="bg-[#1a1a2e]/50 border border-[#2a2a3f]/30 rounded-lg p-8 text-center"
        >
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-indigo-500/10 flex items-center justify-center">
                <Mail class="h-8 w-8 text-indigo-400" />
            </div>
            <h3 class="text-lg font-medium text-gray-200 mb-1">{{ t('account.pages.emails.page.noMails.title') }}</h3>
            <p class="text-sm text-gray-400">{{ t('account.pages.emails.page.noMails.description') }}</p>
        </div>

        <!-- Email Table -->
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
    scrollbar-width: none;
}

/* Smooth transitions */
.transition-all {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 200ms;
}
</style>
