<template>
    <LayoutDashboard>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-400">Mail Templates</h1>
            <div class="flex items-center gap-2">
                <button
                    @click="openMassSendDialog()"
                    class="bg-gradient-to-r from-amber-500 to-pink-500 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:opacity-80 flex items-center"
                >
                    <SendIcon class="w-4 h-4 mr-2" />
                    Send Mass Email
                </button>
                <button
                    @click="goToCreation()"
                    class="bg-gradient-to-r from-pink-500 to-violet-500 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:opacity-80 flex items-center"
                >
                    <PlusIcon class="w-4 h-4 mr-2" />
                    Add Template
                </button>
            </div>
        </div>
        <!-- Mail Templates Table using TableTanstack -->
        <div v-if="loading" class="flex justify-center items-center py-10">
            <LoaderCircle class="h-8 w-8 animate-spin text-pink-400" />
        </div>
        <TableTanstack v-else :data="mailTemplates" :columns="columns" tableName="Mail Templates" />
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, onMounted, h, computed } from 'vue';
import LayoutDashboard from '@/components/admin/LayoutDashboard.vue';
import TableTanstack from '@/components/client/ui/Table/TableTanstack.vue';
import {
    PlusIcon,
    EditIcon,
    TrashIcon,
    LoaderCircle,
    CheckCircleIcon,
    XCircleIcon,
    LockIcon,
    SendIcon,
} from 'lucide-vue-next';
import { useRouter } from 'vue-router';
import Swal from 'sweetalert2';

// Mail Template interface based on the API response
interface MailTemplate {
    id: number;
    name: string;
    body: string;
    deleted: string;
    locked: string;
    active: string;
    date: string;
    subject?: string;
}

const router = useRouter();
const mailTemplates = ref<MailTemplate[]>([]);
const loading = ref<boolean>(true);

const sending = ref<boolean>(false);

const activeTemplates = computed<MailTemplate[]>(() =>
    mailTemplates.value.filter((t) => t.active === 'true' && t.locked !== 'true'),
);

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

const openMassSendDialog = async (): Promise<void> => {
    if (activeTemplates.value.length === 0) {
        await Swal.fire({
            icon: 'info',
            title: 'No active templates',
            text: 'Create or activate a template first.',
            confirmButtonText: 'OK',
            background: '#12121f',
            color: '#e5e7eb',
            confirmButtonColor: '#db2777',
        });
        return;
    }

    const options: Record<string, string> = {};
    for (const t of activeTemplates.value) {
        options[String(t.id)] = t.name;
    }

    const result = await Swal.fire<{ value?: string | null }>({
        title: '<span class="text-gray-100">Send mass email</span>',
        html: `<div class="text-left text-sm text-gray-300 space-y-2">
                <p>Choose a template to email <span class="text-gray-100 font-medium">all users</span>. This will enqueue emails and send them via your SMTP settings.</p>
                <ul class="list-disc ml-5">
                    <li>Only <span class="text-green-400">active</span> and <span class="text-gray-100">unlocked</span> templates are available.</li>
                    <li>Delivery may take time depending on queue size.</li>
                </ul>
            </div>`,
        input: 'select',
        inputOptions: options,
        inputPlaceholder: 'Select a template',
        showCancelButton: true,
        confirmButtonText: 'Continue',
        cancelButtonText: 'Cancel',
        background: '#12121f',
        color: '#e5e7eb',
        confirmButtonColor: '#db2777',
        cancelButtonColor: '#374151',
        inputValidator: (value: string) => {
            if (!value) return 'Please select a template';
            return undefined as unknown as string;
        },
    });

    if (!result.isConfirmed || !result.value) return;

    const id = Number(result.value);
    const tpl = activeTemplates.value.find((t) => t.id === id);

    // Confirmation preview
    await Swal.fire({
        icon: 'warning',
        title: 'Confirm mass send',
        html: `<div class="text-left text-sm text-gray-300 space-y-3">
                <p>You are about to queue an email to <span class="text-gray-100 font-medium">all users</span> using:</p>
                <div class="bg-[#0b0b14] border border-[#2a2a3f] rounded-lg p-3">
                    <div class="text-gray-200 font-semibold">Template</div>
                    <div class="mb-2">${tpl ? tpl.name : 'Unknown'}</div>
                    <div class="text-gray-200 font-semibold">Subject</div>
                    <div class="mb-2">${tpl?.subject ?? 'No subject'}</div>
                    <div class="text-gray-200 font-semibold">Body preview</div>
                    <div class="whitespace-pre-wrap">${(tpl?.body ?? '').slice(0, 300).replace(/</g, '&lt;').replace(/>/g, '&gt;')}${tpl && tpl.body && tpl.body.length > 300 ? '…' : ''}</div>
                </div>
                <p class="text-amber-400">This action will enqueue emails; it cannot be undone.</p>
            </div>`,
        showCancelButton: true,
        confirmButtonText: 'Yes, queue emails',
        cancelButtonText: 'Review again',
        background: '#12121f',
        color: '#e5e7eb',
        confirmButtonColor: '#f59e0b',
        cancelButtonColor: '#374151',
    }).then(async (confirmRes) => {
        if (confirmRes.isConfirmed) {
            await sendMassEmailById(id);
        }
    });
};

const sendMassEmailById = async (id: number): Promise<void> => {
    if (!id || sending.value) return;
    sending.value = true;
    try {
        const response = await fetch(`/api/admin/mail/mail-templates/${id}/mass-send`, {
            method: 'POST',
            headers: { Accept: 'application/json' },
        });
        if (!response.ok) {
            const txt = await response.text();
            throw new Error(txt || 'Failed to start mass email');
        }
        const data = await response.json().catch(() => ({}));
        await Swal.fire({
            icon: 'success',
            title: 'Queued',
            text: data.message || 'Mass email queued successfully.',
            confirmButtonText: 'OK',
            customClass: { popup: 'bg-[#12121f] text-gray-200', confirmButton: 'bg-pink-600' },
        });
    } catch (e) {
        const msg = e instanceof Error ? e.message : 'Unknown error';
        await Swal.fire({
            icon: 'error',
            title: 'Failed',
            text: msg,
            confirmButtonText: 'OK',
            customClass: { popup: 'bg-[#12121f] text-gray-200', confirmButton: 'bg-pink-600' },
        });
    } finally {
        sending.value = false;
    }
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
