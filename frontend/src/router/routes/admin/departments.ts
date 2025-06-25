import Permissions from '@/mythicaldash/Permissions';
import Session from '@/mythicaldash/Session';
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
        beforeEnter: (to, from, next) => {
            if (Session.hasOrRedirectToErrorPage(Permissions.ADMIN_DEPARTMENTS_LIST)) {
                next();
            } else {
                next('/errors/403');
            }
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
        beforeEnter: (to, from, next) => {
            if (Session.hasOrRedirectToErrorPage(Permissions.ADMIN_DEPARTMENTS_CREATE)) {
                next();
            } else {
                next('/errors/403');
            }
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
        beforeEnter: (to, from, next) => {
            if (Session.hasOrRedirectToErrorPage(Permissions.ADMIN_DEPARTMENTS_EDIT)) {
                next();
            } else {
                next('/errors/403');
            }
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
        beforeEnter: (to, from, next) => {
            if (Session.hasOrRedirectToErrorPage(Permissions.ADMIN_DEPARTMENTS_DELETE)) {
                next();
            } else {
                next('/errors/403');
            }
        },
    },
];

export default departmentRoutes;
