import type { RouteRecordRaw } from 'vue-router';
import Session from '@/mythicaldash/Session';
import Permissions from '@/mythicaldash/Permissions';

const settingsRoutes: RouteRecordRaw[] = [
    {
        path: '/mc-admin/settings',
        name: 'admin-settings-new',
        component: () => import('@/views/admin/settings/SettingsNew.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
        beforeEnter: (to, from, next) => {
            if (Session.hasOrRedirectToErrorPage(Permissions.ADMIN_SETTINGS_VIEW)) {
                next();
            } else {
                next('/errors/403');
            }
        },
    },
];

export default settingsRoutes;
