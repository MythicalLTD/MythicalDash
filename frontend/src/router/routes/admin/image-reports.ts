import type { RouteRecordRaw } from 'vue-router';
import Session from '@/mythicaldash/Session';
import Permissions from '@/mythicaldash/Permissions';

const imageReportsRoutes: RouteRecordRaw[] = [
    {
        path: '/mc-admin/image-reports',
        name: 'admin-image-reports',
        component: () => import('@/views/admin/image-reports/Index.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
        beforeEnter: (to, from, next) => {
            if (Session.hasOrRedirectToErrorPage(Permissions.ADMIN_IMAGE_REPORTS_VIEW)) {
                next();
            } else {
                next('/errors/403');
            }
        },
    },
    {
        path: '/mc-admin/image-reports/:id/edit',
        name: 'admin-image-reports-edit',
        component: () => import('@/views/admin/image-reports/Edit.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
        beforeEnter: (to, from, next) => {
            if (Session.hasOrRedirectToErrorPage(Permissions.ADMIN_IMAGE_REPORTS_EDIT)) {
                next();
            } else {
                next('/errors/403');
            }
        },
    },
];

export default imageReportsRoutes;
