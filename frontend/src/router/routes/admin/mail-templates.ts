import type { RouteRecordRaw } from 'vue-router';

const mailTemplatesRoutes: RouteRecordRaw[] = [
    {
        path: '/mc-admin/mail-templates',
        name: 'admin-mail-templates',
        component: () => import('@/views/admin/mail-templates/Index.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
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
    },
    {
        path: '/mc-admin/mail-templates/:id/edit',
        name: 'admin-mail-templates-edit',
        component: () => import('@/views/admin/mail-templates/Edit.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
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
    },
];

export default mailTemplatesRoutes;
