import type { RouteRecordRaw } from 'vue-router';

const serversRoutes: RouteRecordRaw[] = [
    {
        path: '/mc-admin/servers',
        name: 'admin-servers',
        component: () => import('@/views/admin/server/Index.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
    },
];

export default serversRoutes;
