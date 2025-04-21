import type { RouteRecordRaw } from 'vue-router';

const queueRoutes: RouteRecordRaw[] = [
    {
        path: '/mc-admin/server-queue',
        name: 'admin-server-queue',
        component: () => import('@/views/admin/server-queue/Index.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
    },
    {
        path: '/mc-admin/server-queue/create',
        name: 'admin-server-queue-create',
        component: () => import('@/views/admin/server-queue/Create.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
    },
    {
        path: '/mc-admin/server-queue/:id',
        name: 'admin-server-queue-view',
        component: () => import('@/views/admin/server-queue/View.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
    },
    {
        path: '/mc-admin/server-queue/:id/delete',
        name: 'admin-server-queue-delete',
        component: () => import('@/views/admin/server-queue/Delete.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
    },
    {
        path: '/mc-admin/server-queue/logs',
        name: 'admin-server-queue-logs',
        component: () => import('@/views/admin/server-queue/Logs.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
    },
];

export default queueRoutes;
