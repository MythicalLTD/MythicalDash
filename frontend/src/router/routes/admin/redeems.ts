import type { RouteRecordRaw } from 'vue-router';

const redeemRoutes: RouteRecordRaw[] = [
    {
        path: '/mc-admin/redeem-codes',
        name: 'Redeem Codes',
        component: () => import('@/views/admin/redeem/Index.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
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
    },
    {
        path: '/mc-admin/redeem-codes/:id/edit',
        name: 'Edit Redeem Code',
        component: () => import('@/views/admin/redeem/Edit.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
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
    },
];

export default redeemRoutes;
