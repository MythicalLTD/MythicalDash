<template>
    <LayoutDashboard>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-400">Redeem Codes</h1>
            <button
                @click="goToCreation()"
                class="bg-gradient-to-r from-pink-500 to-violet-500 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:opacity-80 flex items-center"
            >
                <PlusIcon class="w-4 h-4 mr-2" />
                Add Redeem Code
            </button>
        </div>
        <!-- Redeem Codes Table -->
        <div v-if="loading" class="flex justify-center items-center py-10">
            <LoaderCircle class="h-8 w-8 animate-spin text-pink-400" />
        </div>
        <TableTanstack v-else :data="redeemCodes" :columns="columns" tableName="Redeem Codes" />
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, onMounted, h } from 'vue';
import LayoutDashboard from '@/components/admin/LayoutDashboard.vue';
import TableTanstack from '@/components/client/ui/Table/TableTanstack.vue';
import { PlusIcon, EditIcon, TrashIcon, LoaderCircle } from 'lucide-vue-next';
import { useRouter } from 'vue-router';
import Redeem from '@/mythicaldash/admin/Redeem';

// Interface for redeem code
interface RedeemCode {
    id: number;
    code: string;
    coins: number;
    uses: number;
    enabled: string;
    deleted: string;
    created_at: string;
    updated_at: string;
}

const router = useRouter();
const redeemCodes = ref<RedeemCode[]>([]);
const loading = ref(true);

// Define columns for TableTanstack
const columns = [
    {
        accessorKey: 'id',
        header: 'ID',
        cell: (info: { getValue: () => number }) => info.getValue(),
    },
    {
        accessorKey: 'code',
        header: 'Code',
        cell: (info: { getValue: () => string }) => info.getValue(),
    },
    {
        accessorKey: 'coins',
        header: 'Coins',
        cell: (info: { getValue: () => number }) => info.getValue(),
    },
    {
        accessorKey: 'uses',
        header: 'Uses Left',
        cell: (info: { getValue: () => number }) => info.getValue(),
    },
    {
        accessorKey: 'enabled',
        header: 'Status',
        cell: (info: { getValue: () => string }) => {
            const enabled = info.getValue() === 'true';
            return h(
                'span',
                {
                    class: {
                        'px-2 py-1 rounded-full text-xs font-medium': true,
                        'bg-green-500/20 text-green-400': enabled,
                        'bg-red-500/20 text-red-400': !enabled,
                    },
                },
                enabled ? 'Enabled' : 'Disabled',
            );
        },
    },
    {
        accessorKey: 'created_at',
        header: 'Created At',
        cell: (info: { getValue: () => string }) => new Date(info.getValue()).toLocaleString(),
    },
    {
        id: 'actions',
        header: 'Actions',
        cell: (info: { row: { original: RedeemCode } }) => {
            const code = info.row.original;
            return h('div', { class: 'flex space-x-2' }, [
                h(
                    'button',
                    {
                        class: 'p-1 text-gray-400 hover:text-pink-400 transition-colors',
                        title: 'Edit',
                        onClick: () => editRedeemCode(code),
                    },
                    [h(EditIcon, { class: 'w-4 h-4' })],
                ),
                h(
                    'button',
                    {
                        class: 'p-1 text-gray-400 hover:text-red-400 transition-colors',
                        title: 'Delete',
                        onClick: () => confirmDelete(code),
                    },
                    [h(TrashIcon, { class: 'w-4 h-4' })],
                ),
            ]);
        },
    },
];

// Fetch redeem codes from API
const fetchRedeemCodes = async () => {
    loading.value = true;
    try {
        const response = await Redeem.getRedeemCodes();

        if (response.success) {
            redeemCodes.value = response.codes;
        } else {
            console.error('Failed to load redeem codes:', response.message);
        }
    } catch (error) {
        console.error('Error fetching redeem codes:', error);
    } finally {
        loading.value = false;
    }
};

const goToCreation = () => {
    router.push('/mc-admin/redeem-codes/create');
};

const editRedeemCode = (code: RedeemCode) => {
    router.push(`/mc-admin/redeem-codes/${code.id}/edit`);
};

const confirmDelete = (code: RedeemCode) => {
    router.push(`/mc-admin/redeem-codes/${code.id}/delete`);
};

onMounted(() => {
    fetchRedeemCodes();
});
</script>
