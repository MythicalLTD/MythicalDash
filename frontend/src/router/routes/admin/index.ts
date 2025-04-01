import type { RouteRecordRaw } from 'vue-router';
import locationRoutes from './locations.ts';
import departmentRoutes from './departments.ts';
import eggRoutes from './eggs.ts';
import eggCategoryRoutes from './egg-categories.ts';
import userRoutes from './users.ts';
import announcementRoutes from './announcements.ts';
import ticketRoutes from './tickets.ts';
// Main admin dashboard route
const mainAdminRoutes: RouteRecordRaw[] = [
    {
        path: '/mc-admin',
        name: 'Admin Home',
        component: () => import('@/views/admin/Home.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
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
    ...announcementRoutes,
    ...ticketRoutes,
];

export default adminRoutes;
