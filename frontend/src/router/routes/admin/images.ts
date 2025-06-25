import Session from '@/mythicaldash/Session';
import Permissions from '@/mythicaldash/Permissions';
import type { RouteRecordRaw } from 'vue-router';

const imagesRoutes: RouteRecordRaw[] = [
    {
        path: '/mc-admin/images',
        name: 'admin-images',
        component: () => import('@/views/admin/images/Index.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
        beforeEnter: (to, from, next) => {
            if (Session.hasOrRedirectToErrorPage(Permissions.ADMIN_IMAGES_LIST)) {
                next();
            } else {
                next('/errors/403');
            }
        },
    },
    {
        path: '/mc-admin/images/create',
        name: 'admin-images-create',
        component: () => import('@/views/admin/images/Create.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
        beforeEnter: (to, from, next) => {
            if (Session.hasOrRedirectToErrorPage(Permissions.ADMIN_IMAGES_CREATE)) {
                next();
            } else {
                next('/errors/403');
            }
        },
    },
    {
        path: '/mc-admin/images/:id/delete',
        name: 'admin-images-delete',
        component: () => import('@/views/admin/images/Delete.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
        beforeEnter: (to, from, next) => {
            if (Session.hasOrRedirectToErrorPage(Permissions.ADMIN_IMAGES_DELETE)) {
                next();
            } else {
                next('/errors/403');
            }
        },
    },
];

export default imagesRoutes;
