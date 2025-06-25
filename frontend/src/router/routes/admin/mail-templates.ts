import type { RouteRecordRaw } from 'vue-router';
import Session from '@/mythicaldash/Session';
import Permissions from '@/mythicaldash/Permissions';

const mailTemplatesRoutes: RouteRecordRaw[] = [
    {
        path: '/mc-admin/mail-templates',
        name: 'admin-mail-templates',
        component: () => import('@/views/admin/mail-templates/Index.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
        beforeEnter: (to, from, next) => {
            if (Session.hasOrRedirectToErrorPage(Permissions.ADMIN_MAIL_TEMPLATES_LIST)) {
                next();
            } else {
                next('/errors/403');
            }
        },
    },
    {
        path: '/mc-admin/mail-templates/create',
        name: 'admin-mail-templates-create',
        component: () => import('@/views/admin/mail-templates/Create.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
        beforeEnter: (to, from, next) => {
            if (Session.hasOrRedirectToErrorPage(Permissions.ADMIN_MAIL_TEMPLATES_CREATE)) {
                next();
            } else {
                next('/errors/403');
            }
        },
    },
    {
        path: '/mc-admin/mail-templates/:id/edit',
        name: 'admin-mail-templates-edit',
        component: () => import('@/views/admin/mail-templates/Edit.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
        beforeEnter: (to, from, next) => {
            if (Session.hasOrRedirectToErrorPage(Permissions.ADMIN_MAIL_TEMPLATES_EDIT)) {
                next();
            } else {
                next('/errors/403');
            }
        },
    },
    {
        path: '/mc-admin/mail-templates/:id/delete',
        name: 'admin-mail-templates-delete',
        component: () => import('@/views/admin/mail-templates/Delete.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
        beforeEnter: (to, from, next) => {
            if (Session.hasOrRedirectToErrorPage(Permissions.ADMIN_MAIL_TEMPLATES_DELETE)) {
                next();
            } else {
                next('/errors/403');
            }
        },
    },
];

export default mailTemplatesRoutes;
