import type { RouteRecordRaw } from 'vue-router';

const clientRoutes: RouteRecordRaw[] = [
    {
        path: '/dashboard',
        name: 'Dashboard',
        component: () => import('@/views/client/Home.vue'),
        meta: {
            requiresAuth: true,
        },
    },
    {
        path: '/account',
        name: 'Account',
        component: () => import('@/views/client/Account.vue'),
        meta: {
            requiresAuth: true,
        },
    },
    {
        path: '/announcements',
        name: 'Announcements',
        component: () => import('@/views/client/Announcements.vue'),
        meta: {
            requiresAuth: true,
        },
    },
    {
        path: '/earn/afk',
        name: 'AFK Rewards',
        component: () => import('@/views/client/earn/AFKRewards.vue'),
        meta: {
            requiresAuth: true,
        },
    },
    {
        path: '/earn/redeem',
        name: 'Redeem',
        component: () => import('@/views/client/earn/CodeRedemption.vue'),
        meta: {
            requiresAuth: true,
        },
    },
    {
        path: '/earn/j4r',
        name: 'Join For Rewards',
        component: () => import('@/views/client/earn/JoinForRewards.vue'),
        meta: {
            requiresAuth: true,
        },
    },
    {
        path: '/earn/referrals',
        name: 'Referrals',
        component: () => import('@/views/client/earn/Referrals.vue'),
        meta: {
            requiresAuth: true,
        },
    },
    {
        path: '/earn/links',
        name: 'Link For Rewards',
        component: () => import('@/views/client/earn/LinkForRewards.vue'),
        meta: {
            requiresAuth: true,
        },
    },
    {
        path: '/store',
        name: 'Store',
        component: () => import('@/views/client/store/Store.vue'),
        meta: {
            requiresAuth: true,
        },
    },
    {
        path: '/store/add-credits',
        name: 'Add Credits',
        component: () => import('@/views/client/store/AddCredits.vue'),
        meta: {
            requiresAuth: true,
        },
    },
    {
        path: '/leaderboard',
        name: 'Leaderboard',
        component: () => import('@/views/client/Leaderboard.vue'),
        meta: {
            requiresAuth: true,
        },
    },
    {
        path: '/profile/:uuid',
        name: 'Profile',
        component: () => import('@/views/client/profile/Profile.vue'),
        meta: {
            requiresAuth: true,
        },
    },
    {
        path: '/profile/:uuid/gift-coins',
        name: 'Gift Coins',
        component: () => import('@/views/client/profile/GiftCoins.vue'),
        meta: {
            requiresAuth: true,
        },
    },
    {
        path: '/',
        redirect: '/dashboard',
    },
];

export default clientRoutes;
