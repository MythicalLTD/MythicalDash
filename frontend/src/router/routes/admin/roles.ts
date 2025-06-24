import type { RouteRecordRaw } from 'vue-router';

const rolesRoutes: RouteRecordRaw[] = [
    {
        path: '/mc-admin/roles',
        name: 'Roles',
        component: () => import('@/views/admin/roles/Index.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
    },
    {
        path: '/mc-admin/roles/create',
        name: 'Create Role',
        component: () => import('@/views/admin/roles/Create.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
    },
    {
        path: '/mc-admin/roles/:id/edit',
        name: 'Edit Role',
        component: () => import('@/views/admin/roles/Edit.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
    },
    {
        path: '/mc-admin/roles/:id/delete',
        name: 'Delete Role',
        component: () => import('@/views/admin/roles/Delete.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
    },
    {
        path: '/mc-admin/roles/:id/permissions',
        name: 'Role Permissions',
        component: () => import('@/views/admin/roles/Permissions.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
    },
];

export default rolesRoutes;
