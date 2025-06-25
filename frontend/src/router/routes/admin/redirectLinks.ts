import type { RouteRecordRaw } from 'vue-router';
import Session from '@/mythicaldash/Session';
import Permissions from '@/mythicaldash/Permissions';

const redirectLinks: RouteRecordRaw[] = [
    {
        path: '/mc-admin/redirect-links',
        name: 'admin-redirect-links',
        component: () => import('@/views/admin/redirect-links/Index.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
        beforeEnter: (to, from, next) => {
            if (Session.hasOrRedirectToErrorPage(Permissions.ADMIN_REDIRECT_LINKS_LIST)) {
                next();
            } else {
                next('/errors/403');
            }
        },
    },
    {
        path: '/mc-admin/redirect-links/create',
        name: 'admin-redirect-links-create',
        component: () => import('@/views/admin/redirect-links/Create.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
        beforeEnter: (to, from, next) => {
            if (Session.hasOrRedirectToErrorPage(Permissions.ADMIN_REDIRECT_LINKS_CREATE)) {
                next();
            } else {
                next('/errors/403');
            }
        },
    },
    {
        path: '/mc-admin/redirect-links/:id/delete',
        name: 'admin-redirect-links-delete',
        component: () => import('@/views/admin/redirect-links/Delete.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
        beforeEnter: (to, from, next) => {
            if (Session.hasOrRedirectToErrorPage(Permissions.ADMIN_REDIRECT_LINKS_DELETE)) {
                next();
            } else {
                next('/errors/403');
            }
        },
    },
    {
        path: '/mc-admin/redirect-links/:id/edit',
        name: 'admin-redirect-links-edit',
        component: () => import('@/views/admin/redirect-links/Edit.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
        beforeEnter: (to, from, next) => {
            if (Session.hasOrRedirectToErrorPage(Permissions.ADMIN_REDIRECT_LINKS_EDIT)) {
                next();
            } else {
                next('/errors/403');
            }
        },
    },
];

export default redirectLinks;
