import type { RouteRecordRaw } from 'vue-router';

const pluginRoutes: RouteRecordRaw[] = [
    {
        path: '/mc-admin/plugins',
        name: 'admin-plugins',
        component: () => import('@/views/admin/plugin/Index.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
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
    },
];

export default pluginRoutes;
