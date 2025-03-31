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
	{
		path: '/mc-admin/departments',
		name: 'Departments',
		component: () => import('@/views/admin/departments/Index.vue'),
	},
	{
		path: '/mc-admin/departments/create',
		name: 'Create Department',
		component: () => import('@/views/admin/departments/Create.vue'),
	},
	{
		path: '/mc-admin/departments/:id/edit',
		name: 'Edit Department',
		component: () => import('@/views/admin/departments/Edit.vue'),
	},
	{
		path: '/mc-admin/departments/:id/delete',
		name: 'Delete Department',
		component: () => import('@/views/admin/departments/Delete.vue'),
	},
    {
        path: '/mc-admin/egg-categories',
        name: 'Egg Categories',
        component: () => import('@/views/admin/egg-categories/Index.vue'),
    },
    {
        path: '/mc-admin/egg-categories/create',
        name: 'Create Egg Category',
        component: () => import('@/views/admin/egg-categories/Create.vue'),
    },
    {
        path: '/mc-admin/egg-categories/:id/edit',
        name: 'Edit Egg Category',
        component: () => import('@/views/admin/egg-categories/Edit.vue'),
    },
    {
        path: '/mc-admin/egg-categories/:id/delete',
        name: 'Delete Egg Category',
        component: () => import('@/views/admin/egg-categories/Delete.vue'),
    },
    {
        path: '/mc-admin/users',
        name: 'Users',
        component: () => import('@/views/admin/users/Index.vue'),
    },
    {
        path: '/mc-admin/users/:id/edit',
        name: 'Edit User',
        component: () => import('@/views/admin/users/Edit.vue'),
    },
    {
        path: '/mc-admin/users/:id/delete',
        name: 'Delete User',
        component: () => import('@/views/admin/users/Delete.vue'),
    },
    {
        path: '/mc-admin/tickets',
        name: 'Admin Tickets',
        component: () => import('@/views/admin/tickets/Index.vue'),
    },
    {
        path: '/mc-admin/tickets/:id',
        name: 'Admin Ticket Details',
        component: () => import('@/views/admin/tickets/Details.vue'),
    },
    {
        path: '/mc-admin/eggs',
        name: 'admin-eggs',
        component: () => import('@/views/admin/eggs/Index.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
    },
    {
        path: '/mc-admin/eggs/create',
        name: 'admin-eggs-create',
        component: () => import('@/views/admin/eggs/Create.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
    },
    {
        path: '/mc-admin/eggs/:id/edit',
        name: 'admin-eggs-edit',
        component: () => import('@/views/admin/eggs/Edit.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
    },
    {
        path: '/mc-admin/eggs/:id/delete',
        name: 'admin-eggs-delete',
        component: () => import('@/views/admin/eggs/Delete.vue'),
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
        },
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
