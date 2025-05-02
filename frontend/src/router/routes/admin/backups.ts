import type { RouteRecordRaw } from 'vue-router';

const locationRoutes: RouteRecordRaw[] = [
    {
        path: '/mc-admin/backups',
        name: 'Backups',
        component: () => import('@/views/admin/backups/Index.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
    },
];

export default locationRoutes;
