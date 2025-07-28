import Permissions from '@/mythicaldash/Permissions';
import Session from '@/mythicaldash/Session';
import type { RouteRecordRaw } from 'vue-router';

const announcementRoutes: RouteRecordRaw[] = [
    {
        path: '/mc-admin/j4r-servers',
        name: 'admin-j4r-servers',
        component: () => import('@/views/admin/j4r-servers/Index.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
        beforeEnter: (to, from, next) => {
            if (Session.hasOrRedirectToErrorPage(Permissions.ADMIN_J4R_SERVERS_LIST)) {
                next();
            } else {
                next('/errors/403');
            }
        },
    },
    {
        path: '/mc-admin/j4r-servers/create',
        name: 'admin-j4r-servers-create',
        component: () => import('@/views/admin/j4r-servers/Create.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
        beforeEnter: (to, from, next) => {
            if (Session.hasOrRedirectToErrorPage(Permissions.ADMIN_J4R_SERVERS_CREATE)) {
                next();
            } else {
                next('/errors/403');
            }
        },
    },
    {
        path: '/mc-admin/j4r-servers/:id/edit',
        name: 'admin-j4r-servers-edit',
        component: () => import('@/views/admin/j4r-servers/Edit.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
        beforeEnter: (to, from, next) => {
            if (Session.hasOrRedirectToErrorPage(Permissions.ADMIN_J4R_SERVERS_EDIT)) {
                next();
            } else {
                next('/errors/403');
            }
        },
    },
    {
        path: '/mc-admin/j4r-servers/:id/delete',
        name: 'admin-j4r-servers-delete',
        component: () => import('@/views/admin/j4r-servers/Delete.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
        beforeEnter: (to, from, next) => {
            if (Session.hasOrRedirectToErrorPage(Permissions.ADMIN_J4R_SERVERS_DELETE)) {
                next();
            } else {
                next('/errors/403');
            }
        },
    },
];

export default announcementRoutes;
