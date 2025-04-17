import type { RouteRecordRaw } from 'vue-router';

const locationRoutes: RouteRecordRaw[] = [
    {
        path: '/mc-admin/locations',
        name: 'Locations',
        component: () => import('@/views/admin/locations/Index.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
    },
    {
        path: '/mc-admin/locations/create',
        name: 'Create Location',
        component: () => import('@/views/admin/locations/Create.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
    },
    {
        path: '/mc-admin/locations/:id/edit',
        name: 'Edit Location',
        component: () => import('@/views/admin/locations/Edit.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
    },
    {
        path: '/mc-admin/locations/:id/delete',
        name: 'Delete Location',
        component: () => import('@/views/admin/locations/Delete.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
    },
];

export default locationRoutes;
