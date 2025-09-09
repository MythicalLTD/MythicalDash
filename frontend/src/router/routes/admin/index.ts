import type { RouteRecordRaw } from 'vue-router';
import locationRoutes from './locations.ts';
import departmentRoutes from './departments.ts';
import eggRoutes from './eggs.ts';
import eggCategoryRoutes from './egg-categories.ts';
import userRoutes from './users.ts';
import announcementRoutes from './announcements.ts';
import ticketRoutes from './tickets.ts';
import queueRoutes from './queue.ts';
import mailTemplatesRoutes from './mail-templates.ts';
import settingsRoutes from './settings.ts';
import serversRoutes from './servers.ts';
import redeemRoutes from './redeems.ts';
import pluginRoutes from './plugins.ts';
import backupsRoutes from './backups.ts';
import imagesRoutes from './images.ts';
import redirectLinks from './redirectLinks.ts';
import rolesRoutes from './roles.ts';
import j4rRoutes from './j4r.ts';
import imageReportsRoutes from './image-reports.ts';
import Session from '@/mythicaldash/Session';
import Permissions from '@/mythicaldash/Permissions';

// Main admin dashboard routes
const mainAdminRoutes: RouteRecordRaw[] = [
    {
        path: '/mc-admin',
        name: 'Admin Home',
        component: () => import('@/views/admin/Home.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
        beforeEnter: (to, from, next) => {
            if (Session.hasOrRedirectToErrorPage(Permissions.ADMIN_DASHBOARD_VIEW)) {
                next();
            } else {
                next('/errors/403');
            }
        },
    },
    {
        path: '/mc-admin/health',
        name: 'Health',
        component: () => import('@/views/admin/health/Health.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
        beforeEnter: (to, from, next) => {
            if (Session.hasOrRedirectToErrorPage(Permissions.ADMIN_HEALTH_VIEW)) {
                next();
            } else {
                next('/errors/403');
            }
        },
    },
];

// Combine all admin routes
const adminRoutes: RouteRecordRaw[] = [
    ...mainAdminRoutes,
    ...locationRoutes,
    ...departmentRoutes,
    ...eggRoutes,
    ...eggCategoryRoutes,
    ...userRoutes,
    ...settingsRoutes,
    ...announcementRoutes,
    ...ticketRoutes,
    ...queueRoutes,
    ...mailTemplatesRoutes,
    ...redeemRoutes,
    ...serversRoutes,
    ...pluginRoutes,
    ...backupsRoutes,
    ...imagesRoutes,
    ...redirectLinks,
    ...rolesRoutes,
    ...j4rRoutes,
    ...imageReportsRoutes,
];

export default adminRoutes;
