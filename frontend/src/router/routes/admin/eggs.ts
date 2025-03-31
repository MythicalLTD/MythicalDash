import type { RouteRecordRaw } from 'vue-router';

const eggRoutes: RouteRecordRaw[] = [
    {
        path: '/mc-admin/eggs',
        name: 'admin-eggs',
        component: () => import('@/views/admin/eggs/Index.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
    },
    {
        path: '/mc-admin/eggs/create',
        name: 'admin-eggs-create',
        component: () => import('@/views/admin/eggs/Create.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
    },
    {
        path: '/mc-admin/eggs/:id/edit',
        name: 'admin-eggs-edit',
        component: () => import('@/views/admin/eggs/Edit.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
    },
    {
        path: '/mc-admin/eggs/:id/delete',
        name: 'admin-eggs-delete',
        component: () => import('@/views/admin/eggs/Delete.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
    },
];

export default eggRoutes;
