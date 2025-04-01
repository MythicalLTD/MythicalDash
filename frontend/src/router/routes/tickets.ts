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
];

export default ticketRoutes;
