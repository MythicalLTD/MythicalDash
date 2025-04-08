import type { RouteRecordRaw } from 'vue-router';

const serverRoutes: RouteRecordRaw[] = [
    {
        path: '/server/create',
        name: 'Create Server',
        component: () => import('@/views/client/server/Create.vue'),
        meta: {
            requiresAuth: true,
        },
    },
];

export default serverRoutes;
