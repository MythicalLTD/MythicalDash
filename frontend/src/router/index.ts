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
        console.log("This page requires authentication");
        const authenticated = isAuthenticated();
        console.log("Checking if session is authenticated");
        if (!authenticated) {
            console.log("Session is not authenticated");
            // Store the intended destination for redirection after login
            localStorage.setItem('redirectAfterLogin', to.fullPath);
            console.log("Looks like we can't access this page");
            return next({ name: 'Login' });
        }
        console.log("Session is authenticated");
        console.log("Looks like we can access this page");
        return next();
    } else if (to.meta.isAuthPage) {
        console.log("This page is an auth page");
        // Guest middleware for auth pages
        return guestMiddleware(to, from, next);
    } else if (to.name === 'auth-redirect') {
        console.log("This page is an auth redirect");
        // Handle redirect after login
        return handleRedirectAfterLogin(to, from, next);
    } else {
        console.log("This page is not an auth page");
        return next();
    }
});

export default router;
