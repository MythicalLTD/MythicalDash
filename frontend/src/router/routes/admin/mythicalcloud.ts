import type { RouteRecordRaw } from 'vue-router';

const locationRoutes: RouteRecordRaw[] = [
    {
        path: '/mc-admin/mythicalcloud',
        name: 'Admin MythicalCloud',
        component: () => import('@/views/admin/mythicalcloud/Index.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
    },
];

export default locationRoutes;
