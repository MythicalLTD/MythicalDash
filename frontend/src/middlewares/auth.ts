import type { NavigationGuardNext, RouteLocationNormalized } from 'vue-router';

// Check if user is authenticated
export function isAuthenticated(): boolean {
    return document.cookie.split(';').some((cookie) => cookie.trim().startsWith('user_token='));
}

// Guest middleware for auth pages
export function guestMiddleware(to: RouteLocationNormalized, from: RouteLocationNormalized, next: NavigationGuardNext) {
    if (isAuthenticated()) {
        return next({ name: 'Dashboard' });
    }
    return next();
}

// Auth middleware
export function authMiddleware(to: RouteLocationNormalized, from: RouteLocationNormalized, next: NavigationGuardNext) {
    if (!isAuthenticated()) {
        // Store the intended destination for redirection after login
        localStorage.setItem('redirectAfterLogin', to.fullPath);
        return next({ name: 'Login' });
    }
    return next();
}

// Handle redirect after login
export function handleRedirectAfterLogin(
    to: RouteLocationNormalized,
    from: RouteLocationNormalized,
    next: NavigationGuardNext,
) {
    const redirectPath = localStorage.getItem('redirectAfterLogin');

    if (redirectPath) {
        localStorage.removeItem('redirectAfterLogin');
        return next(redirectPath);
    }

    return next({ name: 'Dashboard' });
}
