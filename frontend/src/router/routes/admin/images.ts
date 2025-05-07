import type { RouteRecordRaw } from 'vue-router';

const imagesRoutes: RouteRecordRaw[] = [
    {
        path: '/mc-admin/images',
        name: 'admin-images',
        component: () => import('@/views/admin/images/Index.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
    },
    {
        path: '/mc-admin/images/create',
        name: 'admin-images-create',
        component: () => import('@/views/admin/images/Create.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
    },
    {
        path: '/mc-admin/images/:id/delete',
        name: 'admin-images-delete',
        component: () => import('@/views/admin/images/Delete.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
    },
];

export default imagesRoutes;
