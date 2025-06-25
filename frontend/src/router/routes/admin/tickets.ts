import type { RouteRecordRaw } from 'vue-router';
import Session from '@/mythicaldash/Session';
import Permissions from '@/mythicaldash/Permissions';
const locationRoutes: RouteRecordRaw[] = [
    {
        path: '/mc-admin/tickets',
        name: 'Admin Tickets',
        component: () => import('@/views/admin/tickets/Index.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
        beforeEnter: (to, from, next) => {
            if (Session.hasOrRedirectToErrorPage(Permissions.ADMIN_TICKETS_LIST)) {
                next();
            } else {
                next('/errors/403');
            }
        },
    },
    {
        path: '/mc-admin/tickets/:id',
        name: 'Admin Ticket Details',
        component: () => import('@/views/admin/tickets/Details.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
        beforeEnter: (to, from, next) => {
            if (Session.hasOrRedirectToErrorPage(Permissions.ADMIN_TICKETS_VIEW)) {
                next();
            } else {
                next('/errors/403');
            }
        },
    },
];

export default locationRoutes;
