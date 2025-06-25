import type { RouteRecordRaw } from 'vue-router';
import Session from '@/mythicaldash/Session';
import Permissions from '@/mythicaldash/Permissions';
const mythicalcloudRoutes: RouteRecordRaw[] = [
    {
        path: '/mc-admin/mythicalcloud',
        name: 'MythicalCloud',
        component: () => import('@/views/admin/mythicalcloud/Index.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
        beforeEnter: (to, from, next) => {
            if (Session.hasOrRedirectToErrorPage(Permissions.ADMIN_MYTHICALCLOUD_VIEW)) {
                next();
            } else {
                next('/errors/403');
            }
        },
    },
];

export default mythicalcloudRoutes;
