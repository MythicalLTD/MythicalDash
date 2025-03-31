<template>
    <LayoutDashboard>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-pink-400">Delete User</h1>
            <button
                @click="router.push('/mc-admin/users')"
                class="bg-gray-700 text-white px-4 py-2 rounded-lg transition-all duration-200 hover:bg-gray-600 flex items-center"
            >
                <ArrowLeftIcon class="w-4 h-4 mr-2" />
                Back to Users
            </button>
        </div>

        <div class="bg-gray-800/50 backdrop-blur-md rounded-lg p-6">
            <div class="text-center">
                <AlertTriangleIcon class="w-16 h-16 mx-auto text-red-400 mb-4" />
                <h2 class="text-xl font-medium text-gray-100 mb-2">Confirm Deletion</h2>
                <p class="text-gray-300 mb-2">This page is under development. User ID: {{ userId }}</p>
                <p class="text-gray-400 mb-6">This action cannot be undone.</p>
            </div>
        </div>
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { useRouter, useRoute } from 'vue-router';
import LayoutDashboard from '@/components/admin/LayoutDashboard.vue';
import { ArrowLeftIcon, AlertTriangleIcon } from 'lucide-vue-next';

const router = useRouter();
const route = useRoute();
const userId = Number(route.params.id);
</script>
