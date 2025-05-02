import type { RouteRecordRaw } from 'vue-router';

const mythicalcloudRoutes: RouteRecordRaw[] = [
    {
        path: '/mc-admin/mythicalcloud',
        name: 'MythicalCloud',
        component: () => import('@/views/admin/mythicalcloud/Index.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
    },
];

export default mythicalcloudRoutes;
