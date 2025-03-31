<template>
    <LayoutDashboard>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-400">Egg Categories</h1>
            <button
                @click="goToCreation()"
                class="bg-gradient-to-r from-pink-500 to-violet-500 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:opacity-80 flex items-center"
            >
                <PlusIcon class="w-4 h-4 mr-2" />
                Create Category
            </button>
        </div>

        <div v-if="loading" class="flex justify-center items-center py-10">
            <LoaderCircle class="h-8 w-8 animate-spin text-pink-400" />
        </div>
        <TableTanstack v-else :data="categories" :columns="columns" tableName="Egg Categories" />
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, onMounted, h } from 'vue';
import LayoutDashboard from '@/components/admin/LayoutDashboard.vue';
import TableTanstack from '@/components/client/ui/Table/TableTanstack.vue';
import { PlusIcon, EditIcon, TrashIcon, LoaderCircle } from 'lucide-vue-next';
import { useRouter } from 'vue-router';
import EggCategories from '@/mythicaldash/admin/EggCategories';

// Category interface matching the API response
interface Category {
    id: number;
    name: string;
    description: string;
    pterodactyl_nest_id: string;
    enabled: string;
    created_at: string;
    updated_at: string;
}

const router = useRouter();
const categories = ref<Category[]>([]);
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
        accessorKey: 'description',
        header: 'Description',
        cell: (info: { getValue: () => string }) => info.getValue(),
    },
    {
        accessorKey: 'pterodactyl_nest_id',
        header: 'Nest ID',
        cell: (info: { getValue: () => string }) => info.getValue() || 'N/A',
    },
    {
        accessorKey: 'enabled',
        header: 'Status',
        cell: (info: { getValue: () => string }) => {
            const status = info.getValue() === 'true';
            return h(
                'span',
                {
                    class: {
                        'px-2 py-1 rounded-full text-xs font-medium': true,
                        'bg-green-500/20 text-green-400': status,
                        'bg-red-500/20 text-red-400': !status,
                    },
                },
                status ? 'Enabled' : 'Disabled',
            );
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
        cell: (info: { row: { original: Category } }) => {
            const category = info.row.original;
            return h('div', { class: 'flex space-x-2' }, [
                h(
                    'button',
                    {
                        class: 'p-1 text-gray-400 hover:text-pink-400 transition-colors',
                        title: 'Edit',
                        onClick: () => editCategory(category),
                    },
                    [h(EditIcon, { class: 'w-4 h-4' })],
                ),
                h(
                    'button',
                    {
                        class: 'p-1 text-gray-400 hover:text-red-400 transition-colors',
                        title: 'Delete',
                        onClick: () => confirmDelete(category),
                    },
                    [h(TrashIcon, { class: 'w-4 h-4' })],
                ),
            ]);
        },
    },
];

// Fetch categories from API
const fetchCategories = async () => {
    loading.value = true;
    try {
        const response = await EggCategories.getCategories();
        if (response.success) {
            categories.value = response.categories;
        } else {
            console.error('Failed to fetch categories:', response);
        }
    } catch (error) {
        console.error('Error fetching categories:', error);
    } finally {
        loading.value = false;
    }
};

const goToCreation = () => {
    router.push('/mc-admin/egg-categories/create');
};

const editCategory = (category: Category) => {
    router.push(`/mc-admin/egg-categories/${category.id}/edit`);
};

const confirmDelete = (category: Category) => {
    router.push(`/mc-admin/egg-categories/${category.id}/delete`);
};

onMounted(() => {
    fetchCategories();
});
</script>
