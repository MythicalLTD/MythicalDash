import type { RouteRecordRaw } from 'vue-router';

const redirectLinks: RouteRecordRaw[] = [
    {
        path: '/mc-admin/redirect-links',
        name: 'admin-redirect-links',
        component: () => import('@/views/admin/redirect-links/Index.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
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
    },
    {
        path: '/mc-admin/redirect-links/:id/delete',
        name: 'admin-redirect-links-delete',
        component: () => import('@/views/admin/redirect-links/Delete.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
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
    },
];

export default redirectLinks;
