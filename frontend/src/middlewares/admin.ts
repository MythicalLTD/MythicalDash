import type { RouteLocationNormalized, NavigationGuardNext } from 'vue-router';

// Check if user is admin
export function isAdmin(): boolean {
    const userRole = localStorage.getItem('role');
    const adminRoles = ['2', '3', '4', '5', '6', '7', '8'];
    return adminRoles.includes(userRole || '');
}

// Admin middleware
export function adminMiddleware(to: RouteLocationNormalized, from: RouteLocationNormalized, next: NavigationGuardNext) {
    if (!isAdmin()) {
        return next({ name: 'Dashboard' });
    }
    return next();
}
