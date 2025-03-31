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
    },
    {
        path: '/mc-admin/announcements/create',
        name: 'admin-announcements-create',
        component: () => import('@/views/admin/announcements/Create.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
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
    },
    {
        path: '/mc-admin/announcements/:id/delete',
        name: 'admin-announcements-delete',
        component: () => import('@/views/admin/announcements/Delete.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
    },
];

export default announcementRoutes;
