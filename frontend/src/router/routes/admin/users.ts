import type { RouteRecordRaw } from 'vue-router';

const userRoutes: RouteRecordRaw[] = [
    {
        path: '/mc-admin/users',
        name: 'Users',
        component: () => import('@/views/admin/users/Index.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
    },
    {
        path: '/mc-admin/users/:id/edit',
        name: 'Edit User',
        component: () => import('@/views/admin/users/Edit.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
    },
    {
        path: '/mc-admin/users/:id/delete',
        name: 'Delete User',
        component: () => import('@/views/admin/users/Delete.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
    },
];

export default userRoutes;
