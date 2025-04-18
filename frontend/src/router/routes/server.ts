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
    {
        path: '/server/:id/delete',
        name: 'Delete Server',
        component: () => import('@/views/client/server/Delete.vue'),
        meta: {
            requiresAuth: true,
        },
    },
    {
        path: '/server/:id/update',
        name: 'Update Server',
        component: () => import('@/views/client/server/Update.vue'),
        meta: {
            requiresAuth: true,
        },
    },
    {
        path: '/server/:id/renew',
        name: 'Renew Server',
        component: () => import('@/views/client/server/Renew.vue'),
        meta: {
            requiresAuth: true,
        },
    },
];

export default serverRoutes;
