import type { RouteRecordRaw } from 'vue-router';

const ticketRoutes: RouteRecordRaw[] = [
    {
        path: '/ticket',
        name: 'Ticket',
        component: () => import('@/views/client/ticket/List.vue'),
        meta: {
            requiresAuth: true,
        },
    },
    {
        path: '/ticket/create',
        name: 'Create Ticket',
        component: () => import('@/views/client/ticket/Create.vue'),
        meta: {
            requiresAuth: true,
        },
    },
    {
        path: '/ticket/:id',
        name: 'Ticket Detail',
        component: () => import('@/views/client/ticket/[id].vue'),
        meta: {
            requiresAuth: true,
        },
    },
    // Admin ticket routes
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

export default ticketRoutes;
