import type { RouteRecordRaw } from 'vue-router';
import Session from '@/mythicaldash/Session';
import Permissions from '@/mythicaldash/Permissions';

const redeemRoutes: RouteRecordRaw[] = [
    {
        path: '/mc-admin/redeem-codes',
        name: 'Redeem Codes',
        component: () => import('@/views/admin/redeem/Index.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
        beforeEnter: (to, from, next) => {
            if (Session.hasOrRedirectToErrorPage(Permissions.ADMIN_REDEEM_CODES_LIST)) {
                next();
            } else {
                next('/errors/403');
            }
        },
    },
    {
        path: '/mc-admin/redeem-codes/create',
        name: 'Create Redeem Code',
        component: () => import('@/views/admin/redeem/Create.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
        beforeEnter: (to, from, next) => {
            if (Session.hasOrRedirectToErrorPage(Permissions.ADMIN_REDEEM_CODES_CREATE)) {
                next();
            } else {
                next('/errors/403');
            }
        },
    },
    {
        path: '/mc-admin/redeem-codes/:id/edit',
        name: 'Edit Redeem Code',
        component: () => import('@/views/admin/redeem/Edit.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
        beforeEnter: (to, from, next) => {
            if (Session.hasOrRedirectToErrorPage(Permissions.ADMIN_REDEEM_CODES_EDIT)) {
                next();
            } else {
                next('/errors/403');
            }
        },
    },
    {
        path: '/mc-admin/redeem-codes/:id/delete',
        name: 'Delete Redeem Code',
        component: () => import('@/views/admin/redeem/Delete.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
        beforeEnter: (to, from, next) => {
            if (Session.hasOrRedirectToErrorPage(Permissions.ADMIN_REDEEM_CODES_DELETE)) {
                next();
            } else {
                next('/errors/403');
            }
        },
    },
];

export default redeemRoutes;
