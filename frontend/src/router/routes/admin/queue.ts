import type { RouteRecordRaw } from 'vue-router';
import Session from '@/mythicaldash/Session';
import Permissions from '@/mythicaldash/Permissions';

const queueRoutes: RouteRecordRaw[] = [
    {
        path: '/mc-admin/server-queue',
        name: 'admin-server-queue',
        component: () => import('@/views/admin/server-queue/Index.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
        beforeEnter: (to, from, next) => {
            if (Session.hasOrRedirectToErrorPage(Permissions.ADMIN_SERVER_QUEUE_LIST)) {
                next();
            } else {
                next('/errors/403');
            }
        },
    },
    {
        path: '/mc-admin/server-queue/create',
        name: 'admin-server-queue-create',
        component: () => import('@/views/admin/server-queue/Create.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
        beforeEnter: (to, from, next) => {
            if (Session.hasOrRedirectToErrorPage(Permissions.ADMIN_SERVER_QUEUE_CREATE)) {
                next();
            } else {
                next('/errors/403');
            }
        },
    },
    {
        path: '/mc-admin/server-queue/:id',
        name: 'admin-server-queue-view',
        component: () => import('@/views/admin/server-queue/View.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
        beforeEnter: (to, from, next) => {
            if (Session.hasOrRedirectToErrorPage(Permissions.ADMIN_SERVER_QUEUE_VIEW)) {
                next();
            } else {
                next('/errors/403');
            }
        },
    },
    {
        path: '/mc-admin/server-queue/:id/delete',
        name: 'admin-server-queue-delete',
        component: () => import('@/views/admin/server-queue/Delete.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
        beforeEnter: (to, from, next) => {
            if (Session.hasOrRedirectToErrorPage(Permissions.ADMIN_SERVER_QUEUE_DELETE)) {
                next();
            } else {
                next('/errors/403');
            }
        },
    },
    {
        path: '/mc-admin/server-queue/logs',
        name: 'admin-server-queue-logs',
        component: () => import('@/views/admin/server-queue/Logs.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
        beforeEnter: (to, from, next) => {
            if (Session.hasOrRedirectToErrorPage(Permissions.ADMIN_SERVER_QUEUE_LOGS_VIEW)) {
                next();
            } else {
                next('/errors/403');
            }
        },
    },
];

export default queueRoutes;
