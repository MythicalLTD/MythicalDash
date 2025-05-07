<template>
    <LayoutDashboard>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-400">Redirect Links</h1>
            <button
                @click="goToCreation()"
                class="bg-gradient-to-r from-pink-500 to-violet-500 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:opacity-80 flex items-center"
            >
                <PlusIcon class="w-4 h-4 mr-2" />
                Add Redirect Link
            </button>
        </div>
        <!-- Redirect Links Table using TableTanstack -->
        <div v-if="loading" class="flex justify-center items-center py-10">
            <LoaderCircle class="h-8 w-8 animate-spin text-pink-400" />
        </div>
        <TableTanstack v-else :data="redirectLinks" :columns="columns" tableName="Redirect Links" />
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, onMounted, h } from 'vue';
import LayoutDashboard from '@/components/admin/LayoutDashboard.vue';
import TableTanstack from '@/components/client/ui/Table/TableTanstack.vue';
import { PlusIcon, EditIcon, TrashIcon, LoaderCircle, ExternalLinkIcon } from 'lucide-vue-next';
import { useRouter } from 'vue-router';

interface RedirectLink {
    id: number;
    name: string;
    link: string;
    created_at: string;
    updated_at: string;
}

const router = useRouter();
const redirectLinks = ref<RedirectLink[]>([]);
const loading = ref(true);

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
        accessorKey: 'name',
        header: 'Link',
        cell: (info: { getValue: () => string }) => {
            const name = info.getValue();
            const fullUrl = `${window.location.origin}/${name}`;
            return h('div', { class: 'flex items-center gap-2' }, [
                h(
                    'a',
                    {
                        href: fullUrl,
                        target: '_blank',
                        class: 'text-blue-400 hover:text-blue-300 transition-colors',
                        onClick: (e: Event) => e.stopPropagation(),
                    },
                    fullUrl,
                ),
                h(ExternalLinkIcon, { class: 'w-4 h-4 text-gray-400' }),
            ]);
        },
    },
    {
        accessorKey: 'updated_at',
        header: 'Updated At',
        cell: (info: { getValue: () => string }) => new Date(info.getValue()).toLocaleString(),
    },
    {
        accessorKey: 'created_at',
        header: 'Created At',
        cell: (info: { getValue: () => string }) => new Date(info.getValue()).toLocaleString(),
    },
    {
        id: 'actions',
        header: 'Actions',
        cell: (info: { row: { original: RedirectLink } }) => {
            const redirectLink = info.row.original;
            return h('div', { class: 'flex space-x-2' }, [
                h(
                    'button',
                    {
                        class: 'p-1 text-gray-400 hover:text-pink-400 transition-colors',
                        title: 'Edit',
                        onClick: () => editRedirectLink(redirectLink),
                    },
                    [h(EditIcon, { class: 'w-4 h-4' })],
                ),
                h(
                    'button',
                    {
                        class: 'p-1 text-gray-400 hover:text-red-400 transition-colors',
                        title: 'Delete',
                        onClick: () => confirmDelete(redirectLink),
                    },
                    [h(TrashIcon, { class: 'w-4 h-4' })],
                ),
            ]);
        },
    },
];

// Fetch redirect links from API
const fetchRedirectLinks = async () => {
    loading.value = true;
    try {
        const response = await fetch('/api/admin/redirect-links', {
            method: 'GET',
            headers: {
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error('Failed to fetch redirect links');
        }

        const data = await response.json();

        if (data.success) {
            redirectLinks.value = data.redirect_links;
        } else {
            console.error('Failed to load redirect links:', data.message);
        }
    } catch (error) {
        console.error('Error fetching redirect links:', error);
    } finally {
        loading.value = false;
    }
};

const goToCreation = () => {
    router.push('/mc-admin/redirect-links/create');
};

const editRedirectLink = (redirectLink: RedirectLink) => {
    router.push(`/mc-admin/redirect-links/${redirectLink.id}/edit`);
};

const confirmDelete = (redirectLink: RedirectLink) => {
    router.push(`/mc-admin/redirect-links/${redirectLink.id}/delete`);
};

onMounted(() => {
    fetchRedirectLinks();
});
</script>
