import Permissions from '@/mythicaldash/Permissions';
import Session from '@/mythicaldash/Session';
import type { RouteRecordRaw } from 'vue-router';

const announcementRoutes: RouteRecordRaw[] = [
    {
        path: '/mc-admin/announcements',
        name: 'admin-announcements',
        component: () => import('@/views/admin/announcements/Index.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
        beforeEnter: (to, from, next) => {
            if (Session.hasOrRedirectToErrorPage(Permissions.ADMIN_ANNOUNCEMENTS_LIST)) {
                next();
            } else {
                next('/errors/403');
            }
        },
    },
    {
        path: '/mc-admin/announcements/create',
        name: 'admin-announcements-create',
        component: () => import('@/views/admin/announcements/Create.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
        beforeEnter: (to, from, next) => {
            if (Session.hasOrRedirectToErrorPage(Permissions.ADMIN_ANNOUNCEMENTS_CREATE)) {
                next();
            } else {
                next('/errors/403');
            }
        },
    },
    {
        path: '/mc-admin/announcements/:id/edit',
        name: 'admin-announcements-edit',
        component: () => import('@/views/admin/announcements/Edit.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
        beforeEnter: (to, from, next) => {
            if (Session.hasOrRedirectToErrorPage(Permissions.ADMIN_ANNOUNCEMENTS_EDIT)) {
                next();
            } else {
                next('/errors/403');
            }
        },
    },
    {
        path: '/mc-admin/announcements/:id/delete',
        name: 'admin-announcements-delete',
        component: () => import('@/views/admin/announcements/Delete.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
        beforeEnter: (to, from, next) => {
            if (Session.hasOrRedirectToErrorPage(Permissions.ADMIN_ANNOUNCEMENTS_DELETE)) {
                next();
            } else {
                next('/errors/403');
            }
        },
    },
];

export default announcementRoutes;
