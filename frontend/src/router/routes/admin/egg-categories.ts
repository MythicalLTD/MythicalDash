import type { RouteRecordRaw } from 'vue-router';

const eggCategoryRoutes: RouteRecordRaw[] = [
    {
        path: '/mc-admin/egg-categories',
        name: 'Egg Categories',
        component: () => import('@/views/admin/egg-categories/Index.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
    },
    {
        path: '/mc-admin/egg-categories/create',
        name: 'Create Egg Category',
        component: () => import('@/views/admin/egg-categories/Create.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
    },
    {
        path: '/mc-admin/egg-categories/:id/edit',
        name: 'Edit Egg Category',
        component: () => import('@/views/admin/egg-categories/Edit.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
    },
    {
        path: '/mc-admin/egg-categories/:id/delete',
        name: 'Delete Egg Category',
        component: () => import('@/views/admin/egg-categories/Delete.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
    },
];

export default eggCategoryRoutes;
