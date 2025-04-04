import type { RouteRecordRaw } from 'vue-router';

const settingsRoutes: RouteRecordRaw[] = [
    {
        path: '/mc-admin/settings',
        name: 'admin-settings',
        component: () => import('@/views/admin/settings/Index.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
    },
];

export default settingsRoutes;
