import Session from '@/mythicaldash/Session';
import Permissions from '@/mythicaldash/Permissions';
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
        beforeEnter: (to, from, next) => {
            if (Session.hasOrRedirectToErrorPage(Permissions.ADMIN_EGG_LIST)) {
                next();
            } else {
                next('/errors/403');
            }
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
        beforeEnter: (to, from, next) => {
            if (Session.hasOrRedirectToErrorPage(Permissions.ADMIN_EGG_CREATE)) {
                next();
            } else {
                next('/errors/403');
            }
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
        beforeEnter: (to, from, next) => {
            if (Session.hasOrRedirectToErrorPage(Permissions.ADMIN_EGG_EDIT)) {
                next();
            } else {
                next('/errors/403');
            }
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
        beforeEnter: (to, from, next) => {
            if (Session.hasOrRedirectToErrorPage(Permissions.ADMIN_EGG_DELETE)) {
                next();
            } else {
                next('/errors/403');
            }
        },
    },
];

export default eggRoutes;
