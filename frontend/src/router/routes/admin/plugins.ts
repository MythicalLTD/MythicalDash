import type { RouteRecordRaw } from 'vue-router';
import Session from '@/mythicaldash/Session';
import Permissions from '@/mythicaldash/Permissions';
const pluginRoutes: RouteRecordRaw[] = [
    {
        path: '/mc-admin/plugins',
        name: 'admin-plugins',
        component: () => import('@/views/admin/plugin/Index.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
        beforeEnter: (to, from, next) => {
            if (Session.hasOrRedirectToErrorPage(Permissions.ADMIN_PLUGINS_LIST)) {
                next();
            } else {
                next('/errors/403');
            }
        },
    },
    {
        path: '/plugins/:identifier/config',
        name: 'plugin-config',
        component: () => import('@/views/admin/plugin/Config.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
        beforeEnter: (to, from, next) => {
            if (Session.hasOrRedirectToErrorPage(Permissions.ADMIN_PLUGINS_EDIT)) {
                next();
            } else {
                next('/errors/403');
            }
        },
    },
];

export default pluginRoutes;
