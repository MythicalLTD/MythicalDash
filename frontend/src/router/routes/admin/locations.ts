import type { RouteRecordRaw } from 'vue-router';
import Session from '@/mythicaldash/Session';
import Permissions from '@/mythicaldash/Permissions';

const locationRoutes: RouteRecordRaw[] = [
    {
        path: '/mc-admin/locations',
        name: 'Locations',
        component: () => import('@/views/admin/locations/Index.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
        beforeEnter: (to, from, next) => {
            if (Session.hasOrRedirectToErrorPage(Permissions.ADMIN_LOCATIONS_LIST)) {
                next();
            } else {
                next('/errors/403');
            }
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
        beforeEnter: (to, from, next) => {
            if (Session.hasOrRedirectToErrorPage(Permissions.ADMIN_LOCATIONS_CREATE)) {
                next();
            } else {
                next('/errors/403');
            }
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
        beforeEnter: (to, from, next) => {
            if (Session.hasOrRedirectToErrorPage(Permissions.ADMIN_LOCATIONS_EDIT)) {
                next();
            } else {
                next('/errors/403');
            }
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
        beforeEnter: (to, from, next) => {
            if (Session.hasOrRedirectToErrorPage(Permissions.ADMIN_LOCATIONS_DELETE)) {
                next();
            } else {
                next('/errors/403');
            }
        },
    },
];

export default locationRoutes;
