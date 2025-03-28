import { createRouter, createWebHistory } from 'vue-router';

const routes = [
    {
        path: '/auth/login',
        name: 'Login',
        component: () => import('@/views/client/auth/Login.vue'),
    },
    {
        path: '/auth/register',
        name: 'Register',
        component: () => import('@/views/client/auth/Register.vue'),
    },
    {
        path: '/auth/forgot-password',
        name: 'Forgot Password',
        component: () => import('@/views/client/auth/ForgotPassword.vue'),
    },
    {
        path: '/auth/reset-password',
        name: 'Reset Password',
        component: () => import('@/views/client/auth/ResetPassword.vue'),
    },
    {
        path: '/auth/2fa/setup',
        name: 'Two Factor Setup',
        component: () => import('@/views/client/auth/TwoFactorSetup.vue'),
    },
    {
        path: '/errors/403',
        name: 'Forbidden',
        component: () => import('@/views/client/errors/Forbidden.vue'),
    },
    {
        path: '/errors/500',
        name: 'ServerError',
        component: () => import('@/views/client/errors/ServerError.vue'),
    },
    {
        path: '/dashboard',
        name: 'Dashboard',
        component: () => import('@/views/client/Home.vue'),
    },
    {
        path: '/account',
        name: 'Account',
        component: () => import('@/views/client/Account.vue'),
    },
    {
        path: '/announcements',
        name: 'Announcements',
        component: () => import('@/views/client/Announcements.vue'),
    },
    {
        path: '/earn/afk',
        name: 'AFK Rewards',
        component: () => import('@/views/client/earn/AFKRewards.vue'),
    },
    {
        path: '/earn/redeem',
        name: 'Redeem',
        component: () => import('@/views/client/earn/CodeRedemption.vue'),
    },
    {
        path: '/earn/j4r',
        name: 'Join For Rewards',
        component: () => import('@/views/client/earn/JoinForRewards.vue'),
    },
    {
        path: '/earn/referrals',
        name: 'Referrals',
        component: () => import('@/views/client/earn/Referrals.vue'),
    },
    {
        path: '/earn/links',
        name: 'Link For Rewards',
        component: () => import('@/views/client/earn/LinkForRewards.vue'),
    },
    {
        path: '/store',
        name: 'Store',
        component: () => import('@/views/client/store/Store.vue'),
    },
    {
        path: '/ticket',
        name: 'Ticket',
        component: () => import('@/views/client/ticket/List.vue'),
    },
    {
        path: '/ticket/create',
        name: 'Create Ticket',
        component: () => import('@/views/client/ticket/Create.vue'),
    },
    {
        path: '/ticket/:id',
        name: 'Ticket Detail',
        component: () => import('@/views/client/ticket/[id].vue'),
    },
    {
        path: '/auth/sso',
        name: 'SSO',
        component: () => import('@/views/client/auth/sso.vue'),
    },
    {
        path: '/auth/2fa/setup/disband',
        redirect: () => {
            window.location.href = '/api/auth/2fa/setup/kill';
            return '/api/auth/2fa/setup/kill';
        },
    },
    {
        path: '/auth/logout',
        redirect: () => {
            window.location.href = '/api/user/auth/logout';
            return '/api/user/auth/logout';
        },
    },
    {
        path: '/auth/2fa/verify',
        name: 'Two Factor Verify',
        component: () => import('@/views/client/auth/TwoFactorVerify.vue'),
    },
    {
        path: '/',
        redirect: '/dashboard',
    },
    {
        path: '/mc-admin',
        name: 'Admin Home',
        component: () => import('@/views/admin/Home.vue'),
    },
    {
        path: '/mc-admin/locations',
        name: 'Locations',
        component: () => import('@/views/admin/locations/Index.vue'),
    },
    {
        path: '/mc-admin/locations/create',
        name: 'Create Location',
        component: () => import('@/views/admin/locations/Create.vue'),
    },
    {
        path: '/mc-admin/locations/:id/edit',
        name: 'Edit Location',
        component: () => import('@/views/admin/locations/Edit.vue'),
    },
    {
        path: '/mc-admin/locations/:id/delete',
        name: 'Delete Location',
        component: () => import('@/views/admin/locations/Delete.vue'),
    },
];

routes.push({
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: () => import('@/views/client/errors/NotFound.vue'),
});

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
