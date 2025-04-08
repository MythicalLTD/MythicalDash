import { createRouter, createWebHistory } from 'vue-router';
import type { RouteRecordRaw, NavigationGuardNext, RouteLocationNormalized } from 'vue-router';

// Import route modules
import authRoutes from './routes/auth';
import clientRoutes from './routes/client';
import ticketRoutes from './routes/tickets';
import errorRoutes from './routes/errors';
import adminRoutes from './routes/admin';
import serverRoutes from './routes/server';

// Import middlewares
import { isAuthenticated, guestMiddleware, handleRedirectAfterLogin } from '../middlewares/auth';
import { isAdmin } from '../middlewares/admin';

// Combine all routes
const routes: RouteRecordRaw[] = [
    ...authRoutes,
    ...clientRoutes,
    ...ticketRoutes,
    ...adminRoutes,
    ...errorRoutes,
    ...serverRoutes,
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to: RouteLocationNormalized, from: RouteLocationNormalized, next: NavigationGuardNext) => {
    // Check for requiresAuth meta
    if (to.meta.requiresAuth) {
        if (!isAuthenticated()) {
            // Store the intended destination for redirection after login
            localStorage.setItem('redirectAfterLogin', to.fullPath);
            return next({ name: 'Login' });
        }

        // If route requires admin access
        if (to.meta.requiresAdmin && !isAdmin()) {
            return next({ name: 'Dashboard' });
        }

        return next();
    } else if (to.meta.isAuthPage) {
        // Guest middleware for auth pages
        return guestMiddleware(to, from, next);
    } else if (to.name === 'auth-redirect') {
        // Handle redirect after login
        return handleRedirectAfterLogin(to, from, next);
    } else {
        return next();
    }
});

export default router;
