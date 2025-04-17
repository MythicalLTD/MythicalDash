import type { RouteRecordRaw } from 'vue-router';

const locationRoutes: RouteRecordRaw[] = [
    {
        path: '/mc-admin/tickets',
        name: 'Admin Tickets',
        component: () => import('@/views/admin/tickets/Index.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
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
    },
];

export default locationRoutes;
