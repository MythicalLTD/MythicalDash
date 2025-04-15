import type { RouteRecordRaw } from 'vue-router';

const authRoutes: RouteRecordRaw[] = [
    {
        path: '/auth/login',
        name: 'Login',
        component: () => import('@/views/client/auth/Login.vue'),
        meta: {
            requiresAuth: false,
            isAuthPage: true,
        },
    },
    {
        path: '/auth/register',
        name: 'Register',
        component: () => import('@/views/client/auth/Register.vue'),
        meta: {
            requiresAuth: false,
            isAuthPage: true,
        },
    },
    {
        path: '/auth/logout',
        name: 'Logout',
        component: () => import('@/views/client/auth/Logout.vue'),
    },
    {
        path: '/auth/forgot-password',
        name: 'Forgot Password',
        component: () => import('@/views/client/auth/ForgotPassword.vue'),
        meta: {
            requiresAuth: false,
            isAuthPage: true,
        },
    },
    {
        path: '/auth/reset-password',
        name: 'Reset Password',
        component: () => import('@/views/client/auth/ResetPassword.vue'),
        meta: {
            requiresAuth: false,
            isAuthPage: true,
        },
    },
    {
        path: '/auth/2fa/setup',
        name: 'Two Factor Setup',
        component: () => import('@/views/client/auth/TwoFactorSetup.vue'),
        meta: {
            requiresAuth: true,
        },
    },
    {
        path: '/auth/2fa/verify',
        name: 'Two Factor Verify',
        component: () => import('@/views/client/auth/TwoFactorVerify.vue'),
        meta: {
            requiresAuth: false,
            isAuthPage: true,
        },
    },
    {
        path: '/auth/sso',
        name: 'SSO',
        component: () => import('@/views/client/auth/sso.vue'),
        meta: {
            requiresAuth: false,
            isAuthPage: true,
        },
    },
    {
        path: '/auth/2fa/setup/disband',
        redirect: () => {
            window.location.href = '/api/auth/2fa/setup/kill';
            return '/api/auth/2fa/setup/kill';
        },
        meta: {
            requiresAuth: true,
        },
    },
];

export default authRoutes;
