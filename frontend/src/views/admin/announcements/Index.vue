<template>
    <LayoutDashboard>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-400">Announcements</h1>
            <button
                @click="goToCreation()"
                class="bg-gradient-to-r from-pink-500 to-violet-500 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:opacity-80 flex items-center"
            >
                <PlusIcon class="w-4 h-4 mr-2" />
                Add Announcement
            </button>
        </div>
        <!-- Announcements Table using TableTanstack -->
        <div v-if="loading" class="flex justify-center items-center py-10">
            <LoaderCircle class="h-8 w-8 animate-spin text-pink-400" />
        </div>
        <TableTanstack v-else :data="announcements" :columns="columns" tableName="Announcements" />
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, onMounted, h } from 'vue';
import LayoutDashboard from '@/components/admin/LayoutDashboard.vue';
import TableTanstack from '@/components/client/ui/Table/TableTanstack.vue';
import { PlusIcon, EditIcon, TrashIcon, LoaderCircle, TagIcon } from 'lucide-vue-next';
import { useRouter } from 'vue-router';

// Interface for the Tag data
interface Tag {
    id: number;
    tag: string;
}

// Interface for the Asset data
interface Asset {
    id: number;
    images: string;
}

// Announcement interface based on the API response
interface Announcement {
    id: number;
    title: string;
    shortDescription: string;
    description: string;
    date: string;
    deleted: string;
    tags: Tag[];
    assets: Asset[];
}

const router = useRouter();
const announcements = ref<Announcement[]>([]);
const loading = ref<boolean>(true);

// Define columns for TableTanstack
const columns = [
    {
        accessorKey: 'id',
        header: 'ID',
        cell: (info: { getValue: () => number }) => info.getValue(),
    },
    {
        accessorKey: 'title',
        header: 'Title',
        cell: (info: { getValue: () => string }) => info.getValue(),
    },
    {
        accessorKey: 'shortDescription',
        header: 'Short Description',
        cell: (info: { getValue: () => string }) => info.getValue(),
    },
    {
        id: 'tags',
        header: 'Tags',
        cell: (info: { row: { original: Announcement } }) => {
            const announcementTags = info.row.original.tags || [];

            return h(
                'div',
                { class: 'flex flex-wrap gap-1' },
                announcementTags.length > 0
                    ? announcementTags.map((tag) =>
                          h(
                              'span',
                              {
                                  class: 'bg-gray-700 text-gray-300 px-2 py-1 rounded-full text-xs flex items-center',
                              },
                              [h(TagIcon, { class: 'w-3 h-3 mr-1' }), tag.tag],
                          ),
                      )
                    : h('span', { class: 'text-gray-500 text-sm' }, 'No tags'),
            );
        },
    },
    {
        id: 'images',
        header: 'Images',
        cell: (info: { row: { original: Announcement } }) => {
            const assets = info.row.original.assets || [];

            if (assets.length === 0) {
                return h('span', { class: 'text-gray-500 text-sm' }, 'No images');
            }

            // Show only first image with count indicator if there are more
            return h('div', { class: 'relative inline-block' }, [
                h('img', {
                    src: assets[0].images,
                    alt: 'Announcement image',
                    class: 'w-10 h-10 object-cover rounded-md',
                }),
                assets.length > 1
                    ? h(
                          'span',
                          {
                              class: 'absolute -top-2 -right-2 bg-pink-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center',
                          },
                          assets.length,
                      )
                    : null,
            ]);
        },
    },
    {
        accessorKey: 'date',
        header: 'Date',
        cell: (info: { getValue: () => string }) => new Date(info.getValue()).toLocaleString(),
    },
    {
        id: 'actions',
        header: 'Actions',
        cell: (info: { row: { original: Announcement } }) => {
            const announcement = info.row.original;
            return h('div', { class: 'flex space-x-2' }, [
                h(
                    'button',
                    {
                        class: 'p-1 text-gray-400 hover:text-pink-400 transition-colors',
                        title: 'Edit',
                        onClick: () => editAnnouncement(announcement),
                    },
                    [h(EditIcon, { class: 'w-4 h-4' })],
                ),
                h(
                    'button',
                    {
                        class: 'p-1 text-gray-400 hover:text-red-400 transition-colors',
                        title: 'Delete',
                        onClick: () => confirmDelete(announcement),
                    },
                    [h(TrashIcon, { class: 'w-4 h-4' })],
                ),
            ]);
        },
    },
];

// Fetch announcements from API
const fetchAnnouncements = async (): Promise<void> => {
    loading.value = true;
    try {
        const response = await fetch('/api/admin/announcements', {
            method: 'GET',
            headers: {
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error('Failed to fetch announcements');
        }

        const data = await response.json();

        if (data.success) {
            announcements.value = data.announcements;
        } else {
            console.error('Failed to load announcements:', data.message);
        }
    } catch (error) {
        console.error('Error fetching announcements:', error);
    } finally {
        loading.value = false;
    }
};

const goToCreation = (): void => {
    router.push('/mc-admin/announcements/create');
};

const editAnnouncement = (announcement: Announcement): void => {
    router.push(`/mc-admin/announcements/${announcement.id}/edit`);
};

const confirmDelete = (announcement: Announcement): void => {
    router.push(`/mc-admin/announcements/${announcement.id}/delete`);
};

onMounted(() => {
    fetchAnnouncements();
});
</script>
