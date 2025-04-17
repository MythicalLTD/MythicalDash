<template>
    <LayoutDashboard>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-400">Mail Templates</h1>
            <button
                @click="goToCreation()"
                class="bg-gradient-to-r from-pink-500 to-violet-500 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:opacity-80 flex items-center"
            >
                <PlusIcon class="w-4 h-4 mr-2" />
                Add Template
            </button>
        </div>
        <!-- Mail Templates Table using TableTanstack -->
        <div v-if="loading" class="flex justify-center items-center py-10">
            <LoaderCircle class="h-8 w-8 animate-spin text-pink-400" />
        </div>
        <TableTanstack v-else :data="mailTemplates" :columns="columns" tableName="Mail Templates" />
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, onMounted, h } from 'vue';
import LayoutDashboard from '@/components/admin/LayoutDashboard.vue';
import TableTanstack from '@/components/client/ui/Table/TableTanstack.vue';
import { PlusIcon, EditIcon, TrashIcon, LoaderCircle, CheckCircleIcon, XCircleIcon, LockIcon } from 'lucide-vue-next';
import { useRouter } from 'vue-router';

// Mail Template interface based on the API response
interface MailTemplate {
    id: number;
    name: string;
    content: string;
    deleted: string;
    locked: string;
    active: string;
    date: string;
}

const router = useRouter();
const mailTemplates = ref<MailTemplate[]>([]);
const loading = ref<boolean>(true);

// Define columns for TableTanstack
const columns = [
    {
        accessorKey: 'id',
        header: 'ID',
        cell: (info: { getValue: () => number }) => info.getValue(),
    },
    {
        accessorKey: 'name',
        header: 'Name',
        cell: (info: { getValue: () => string }) => info.getValue(),
    },
    {
        id: 'status',
        header: 'Status',
        cell: (info: { row: { original: MailTemplate } }) => {
            const template = info.row.original;
            const isActive = template.active === 'true';
            const isLocked = template.locked === 'true';

            return h('div', { class: 'flex items-center space-x-2' }, [
                // Active status
                h(
                    'span',
                    {
                        class: `flex items-center ${isActive ? 'text-green-400' : 'text-gray-400'}`,
                        title: isActive ? 'Active' : 'Inactive',
                    },
                    [
                        isActive
                            ? h(CheckCircleIcon, { class: 'w-4 h-4 mr-1' })
                            : h(XCircleIcon, { class: 'w-4 h-4 mr-1' }),
                        isActive ? 'Active' : 'Inactive',
                    ],
                ),
                // Lock status if template is locked
                isLocked
                    ? h(
                          'span',
                          {
                              class: 'flex items-center text-amber-400 ml-2',
                              title: 'Locked',
                          },
                          [h(LockIcon, { class: 'w-4 h-4' })],
                      )
                    : null,
            ]);
        },
    },
    {
        accessorKey: 'date',
        header: 'Created',
        cell: (info: { getValue: () => string }) => new Date(info.getValue()).toLocaleString(),
    },
    {
        id: 'actions',
        header: 'Actions',
        cell: (info: { row: { original: MailTemplate } }) => {
            const template = info.row.original;
            const isLocked = template.locked === 'true';

            return h('div', { class: 'flex space-x-2' }, [
                h(
                    'button',
                    {
                        class: 'p-1 text-gray-400 hover:text-pink-400 transition-colors',
                        title: 'Edit',
                        onClick: () => editTemplate(template),
                        disabled: isLocked,
                    },
                    [h(EditIcon, { class: `w-4 h-4 ${isLocked ? 'opacity-50' : ''}` })],
                ),
                h(
                    'button',
                    {
                        class: 'p-1 text-gray-400 hover:text-red-400 transition-colors',
                        title: 'Delete',
                        onClick: () => confirmDelete(template),
                        disabled: isLocked,
                    },
                    [h(TrashIcon, { class: `w-4 h-4 ${isLocked ? 'opacity-50' : ''}` })],
                ),
            ]);
        },
    },
];

// Fetch mail templates from API
const fetchMailTemplates = async (): Promise<void> => {
    loading.value = true;
    try {
        const response = await fetch('/api/admin/mail/mail-templates', {
            method: 'GET',
            headers: {
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error('Failed to fetch mail templates');
        }

        const data = await response.json();

        if (data.success) {
            mailTemplates.value = data.mail_templates;
        } else {
            console.error('Failed to load mail templates:', data.message);
        }
    } catch (error) {
        console.error('Error fetching mail templates:', error);
    } finally {
        loading.value = false;
    }
};

const goToCreation = (): void => {
    router.push('/mc-admin/mail-templates/create');
};

const editTemplate = (template: MailTemplate): void => {
    if (template.locked === 'true') {
        return; // Don't allow editing locked templates
    }
    router.push(`/mc-admin/mail-templates/${template.id}/edit`);
};

const confirmDelete = (template: MailTemplate): void => {
    if (template.locked === 'true') {
        return; // Don't allow deleting locked templates
    }
    router.push(`/mc-admin/mail-templates/${template.id}/delete`);
};

onMounted(() => {
    fetchMailTemplates();
});
</script>
