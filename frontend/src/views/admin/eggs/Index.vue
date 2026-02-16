<template>
    <LayoutDashboard>
        <!-- Sponsor Banner -->
        <DismissibleBanner
            cookie-key="sponsor_banner_hidden"
            title="Support MythicalDash"
            subtitle="Help us continue providing free updates and patches"
            :icon="Heart"
            class="mb-4"
        >
            <p class="text-sm text-gray-300 mb-4 leading-relaxed">
                MythicalDash has been providing <strong class="text-white">free updates and patches since 2021</strong>.
                We're committed to keeping the platform free and open-source. Your support helps us maintain, improve,
                and add new features to MythicalDash.
            </p>

            <template #actions>
                <a
                    href="https://donate.stripe.com/00gcO2epX5yj2ysfYY"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="group flex-1 flex items-center justify-center gap-2 py-2.5 px-4 bg-black hover:bg-gray-900 border border-gray-800 hover:border-gray-700 rounded-lg text-sm font-medium text-white transition-all duration-200"
                >
                    <span>Donate via Stripe</span>
                    <ExternalLink class="w-4 h-4" />
                </a>
                <a
                    href="https://www.paypal.com/paypalme/nayskutzu"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="group flex-1 flex items-center justify-center gap-2 py-2.5 px-4 bg-gray-800 hover:bg-gray-700 border border-gray-700 hover:border-gray-600 rounded-lg text-sm font-medium text-gray-200 hover:text-white transition-all duration-200"
                >
                    <span>Donate via PayPal</span>
                    <ExternalLink class="w-4 h-4" />
                </a>
            </template>
        </DismissibleBanner>

        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-400">Eggs</h1>
            <button
                @click="goToCreation()"
                class="bg-linear-to-r from-pink-500 to-violet-500 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:opacity-80 flex items-center"
            >
                <PlusIcon class="w-4 h-4 mr-2" />
                Create Egg
            </button>
        </div>

        <div v-if="loading" class="flex justify-center items-center py-10">
            <LoaderCircle class="h-8 w-8 animate-spin text-pink-400" />
        </div>
        <TableTanstack v-else :data="eggs" :columns="columns" tableName="Eggs" />
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, onMounted, h } from 'vue';
import LayoutDashboard from '@/components/admin/LayoutDashboard.vue';
import DismissibleBanner from '@/components/admin/DismissibleBanner.vue';
import TableTanstack from '@/components/client/ui/Table/TableTanstack.vue';
import { PlusIcon, EditIcon, TrashIcon, LoaderCircle, Heart, ExternalLink } from 'lucide-vue-next';
import { useRouter } from 'vue-router';
import Eggs from '@/mythicaldash/admin/Eggs';
import EggCategories from '@/mythicaldash/admin/EggCategories';

// Egg interface matching the API response
interface Egg {
    id: number;
    name: string;
    description: string;
    category: string;
    pterodactyl_egg_id: string;
    enabled: string;
    created_at: string;
    updated_at: string;
}

interface CellInfo {
    getValue: () => string;
    row: {
        original: Egg;
    };
}

const router = useRouter();
const loading = ref(true);
const eggs = ref<Egg[]>([]);
const categories = ref<Record<string, string>>({});

// Fetch categories to display category names
const fetchCategories = async () => {
    try {
        const response = await EggCategories.getCategories();
        if (response.success) {
            const categoryMap: Record<string, string> = {};
            response.categories.forEach((category: { id: string; name: string }) => {
                categoryMap[category.id] = category.name;
            });
            categories.value = categoryMap;
        }
    } catch (error) {
        console.error('Error fetching categories:', error);
    }
};

// Define table columns
const columns = [
    {
        header: 'ID',
        accessorKey: 'id',
        cell: (info: CellInfo) => info.getValue(),
    },
    {
        header: 'Name',
        accessorKey: 'name',
        cell: (info: CellInfo) => info.getValue(),
    },
    {
        header: 'Category',
        accessorKey: 'category',
        cell: (info: CellInfo) => {
            const categoryId = info.getValue();
            return categories.value[categoryId] || 'Unknown';
        },
    },
    {
        header: 'Pterodactyl Egg ID',
        accessorKey: 'pterodactyl_egg_id',
        cell: (info: CellInfo) => info.getValue(),
    },
    {
        header: 'Status',
        accessorKey: 'enabled',
        cell: (info: CellInfo) => {
            const enabled = info.getValue();
            return h(
                'span',
                {
                    class: enabled === 'true' ? 'text-green-400' : 'text-red-400',
                },
                enabled === 'true' ? 'Enabled' : 'Disabled',
            );
        },
    },
    {
        header: 'Created',
        accessorKey: 'created_at',
        cell: (info: CellInfo) => {
            const date = new Date(info.getValue());
            return h('span', { class: 'text-gray-400' }, date.toLocaleDateString());
        },
    },
    {
        header: 'Actions',
        id: 'actions',
        cell: (info: CellInfo) => {
            return h('div', { class: 'flex space-x-2' }, [
                h(
                    'button',
                    {
                        class: 'p-1 text-blue-400 hover:text-blue-300 transition-colors',
                        onClick: () => editEgg(info.row.original),
                    },
                    [h(EditIcon, { class: 'h-4 w-4' })],
                ),
                h(
                    'button',
                    {
                        class: 'p-1 text-red-400 hover:text-red-300 transition-colors',
                        onClick: () => confirmDelete(info.row.original),
                    },
                    [h(TrashIcon, { class: 'h-4 w-4' })],
                ),
            ]);
        },
    },
];

// Fetch eggs from API
const fetchEggs = async () => {
    loading.value = true;
    try {
        const response = await Eggs.getEggs();
        if (response.success) {
            eggs.value = response.eggs;
        } else {
            console.error('Failed to fetch eggs:', response);
        }
    } catch (error) {
        console.error('Error fetching eggs:', error);
    } finally {
        loading.value = false;
    }
};

const goToCreation = () => {
    router.push('/mc-admin/eggs/create');
};

const editEgg = (egg: Egg) => {
    router.push(`/mc-admin/eggs/${egg.id}/edit`);
};

const confirmDelete = (egg: Egg) => {
    router.push(`/mc-admin/eggs/${egg.id}/delete`);
};

onMounted(async () => {
    await fetchCategories();
    await fetchEggs();
});
</script>
