import type { RouteRecordRaw } from 'vue-router';

const errorRoutes: RouteRecordRaw[] = [
    {
        path: '/errors/403',
        name: 'Forbidden',
        component: () => import('@/views/client/errors/Forbidden.vue'),
        meta: {
            requiresAuth: false,
        },
    },
    {
        path: '/errors/500',
        name: 'ServerError',
        component: () => import('@/views/client/errors/ServerError.vue'),
        meta: {
            requiresAuth: false,
        },
    },
    {
        path: '/:pathMatch(.*)*',
        name: 'NotFound',
        component: () => import('@/views/client/errors/NotFound.vue'),
        meta: {
            requiresAuth: false,
        },
    },
];

export default errorRoutes;
