<script setup lang="ts">
import { ref } from 'vue';
import {
    useVueTable,
    FlexRender,
    getCoreRowModel,
    getPaginationRowModel,
    getSortedRowModel,
    getFilteredRowModel,
} from '@tanstack/vue-table';

import { ArrowBigRight, ArrowBigLeft, ArrowBigRightDash, ArrowBigLeftDash } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

const props = defineProps({
    data: {
        type: Array,
        required: true,
    },
    columns: {
        type: Array,
        required: true,
    },
    tableName: {
        type: String,
        required: true,
    },
});

const data = ref(props.data);
import type { ColumnDef, SortingState } from '@tanstack/vue-table';
import CardComponent from '../Card/CardComponent.vue';

const sorting = ref<SortingState>([]);
const filter = ref('');

const { t } = useI18n();

const table = useVueTable({
    data: data,
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    columns: props.columns as ColumnDef<unknown, any>[],
    getCoreRowModel: getCoreRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    state: {
        get sorting() {
            return sorting.value;
        },
        get globalFilter() {
            return filter.value;
        },
    },
    onSortingChange: (updaterOrValue) => {
        sorting.value = typeof updaterOrValue === 'function' ? updaterOrValue(sorting.value) : updaterOrValue;
    },
    initialState: {
        pagination: {
            pageSize: 10,
        },
    },
});
</script>

<template>
    <CardComponent>
        <div class="w-full">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 gap-3">
                <h2 class="text-lg font-medium text-gray-200">{{ tableName }}</h2>
                <div class="relative">
                    <input
                        v-model="filter"
                        type="text"
                        :placeholder="t('components.table.search')"
                        class="px-4 py-2 pl-10 bg-[#1a1a2e]/50 border border-[#2a2a3f]/30 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500/50 w-full md:w-64 transition-all duration-200"
                    />
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg
                            class="h-5 w-5 text-gray-400"
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                            aria-hidden="true"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto rounded-xl border border-[#2a2a3f]/30 shadow-lg">
                <table class="min-w-full divide-y divide-[#2a2a3f]/30">
                    <thead class="bg-[#0a0a0f]/70">
                        <tr>
                            <th
                                v-for="header in table.getHeaderGroups()[0].headers"
                                :key="header.id"
                                scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider"
                                :class="{ 'cursor-pointer select-none': header.column.getCanSort() }"
                                @click="header.column.getToggleSortingHandler()"
                            >
                                <div class="flex items-center gap-1">
                                    <FlexRender :render="header.column.columnDef.header" :props="header.getContext()" />
                                    <span v-if="header.column.getCanSort()">
                                        <span v-if="header.column.getIsSorted() === 'asc'">▲</span>
                                        <span v-else-if="header.column.getIsSorted() === 'desc'">▼</span>
                                        <span v-else>◆</span>
                                    </span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-[#12121f]/50 divide-y divide-[#2a2a3f]/30">
                        <tr
                            v-for="row in table.getRowModel().rows"
                            :key="row.id"
                            class="hover:bg-[#1a1a2e]/30 transition-colors duration-150"
                        >
                            <td
                                v-for="cell in row.getVisibleCells()"
                                :key="cell.id"
                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-300"
                            >
                                <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                            </td>
                        </tr>
                        <tr v-if="table.getRowModel().rows.length === 0">
                            <td :colspan="table.getAllColumns().length" class="px-6 py-8 text-center text-gray-400">
                                <div class="flex flex-col items-center justify-center">
                                    <svg
                                        class="w-12 h-12 text-gray-500 mb-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                        xmlns="http://www.w3.org/2000/svg"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                        ></path>
                                    </svg>
                                    <p class="text-lg font-medium">{{ t('components.table.no_data') }}</p>
                                    <p class="text-sm text-gray-500 mt-1">
                                        {{ t('components.table.no_data_description') }}
                                    </p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="flex items-center justify-between mt-4">
                <div class="flex items-center gap-2">
                    <select
                        v-model="table.getState().pagination.pageSize"
                        class="px-3 py-2 bg-[#1a1a2e]/50 border border-[#2a2a3f]/30 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500/50 transition-all duration-200"
                    >
                        <option v-for="pageSize in [10, 20, 30, 40, 50]" :key="pageSize" :value="pageSize">
                            {{ pageSize }} {{ t('components.table.per_page') }}
                        </option>
                    </select>
                    <span class="text-sm text-gray-400">
                        {{ t('components.table.showing') }}
                        {{ table.getState().pagination.pageIndex * table.getState().pagination.pageSize + 1 }} -
                        {{
                            Math.min(
                                (table.getState().pagination.pageIndex + 1) * table.getState().pagination.pageSize,
                                table.getFilteredRowModel().rows.length,
                            )
                        }}
                        {{ t('components.table.of') }} {{ table.getFilteredRowModel().rows.length }}
                    </span>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        class="p-2 rounded-lg bg-[#1a1a2e]/50 border border-[#2a2a3f]/30 text-gray-400 hover:bg-[#1a1a2e]/70 hover:text-gray-200 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
                        :disabled="!table.getCanPreviousPage()"
                        @click="table.setPageIndex(0)"
                    >
                        <ArrowBigLeftDash class="h-5 w-5" />
                    </button>
                    <button
                        class="p-2 rounded-lg bg-[#1a1a2e]/50 border border-[#2a2a3f]/30 text-gray-400 hover:bg-[#1a1a2e]/70 hover:text-gray-200 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
                        :disabled="!table.getCanPreviousPage()"
                        @click="table.previousPage()"
                    >
                        <ArrowBigLeft class="h-5 w-5" />
                    </button>
                    <button
                        class="p-2 rounded-lg bg-[#1a1a2e]/50 border border-[#2a2a3f]/30 text-gray-400 hover:bg-[#1a1a2e]/70 hover:text-gray-200 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
                        :disabled="!table.getCanNextPage()"
                        @click="table.nextPage()"
                    >
                        <ArrowBigRight class="h-5 w-5" />
                    </button>
                    <button
                        class="p-2 rounded-lg bg-[#1a1a2e]/50 border border-[#2a2a3f]/30 text-gray-400 hover:bg-[#1a1a2e]/70 hover:text-gray-200 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
                        :disabled="!table.getCanNextPage()"
                        @click="table.setPageIndex(table.getPageCount() - 1)"
                    >
                        <ArrowBigRightDash class="h-5 w-5" />
                    </button>
                </div>
            </div>
        </div>
    </CardComponent>
</template>

<style scoped>
/* Smooth transitions */
.transition-all {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 200ms;
}

/* Shadow effects */
.shadow-lg {
    box-shadow:
        0 10px 15px -3px rgba(0, 0, 0, 0.1),
        0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

/* Focus styles */
:focus {
    outline: none;
}

/* Table styles */
table {
    border-collapse: separate;
    border-spacing: 0;
}

th:first-child {
    border-top-left-radius: 0.5rem;
}

th:last-child {
    border-top-right-radius: 0.5rem;
}

tr:last-child td:first-child {
    border-bottom-left-radius: 0.5rem;
}

tr:last-child td:last-child {
    border-bottom-right-radius: 0.5rem;
}
</style>
