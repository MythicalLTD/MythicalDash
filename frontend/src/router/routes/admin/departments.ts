import type { RouteRecordRaw } from 'vue-router';

const departmentRoutes: RouteRecordRaw[] = [
    {
        path: '/mc-admin/departments',
        name: 'Departments',
        component: () => import('@/views/admin/departments/Index.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
    },
    {
        path: '/mc-admin/departments/create',
        name: 'Create Department',
        component: () => import('@/views/admin/departments/Create.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
    },
    {
        path: '/mc-admin/departments/:id/edit',
        name: 'Edit Department',
        component: () => import('@/views/admin/departments/Edit.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
    },
    {
        path: '/mc-admin/departments/:id/delete',
        name: 'Delete Department',
        component: () => import('@/views/admin/departments/Delete.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
    },
];

export default departmentRoutes;
