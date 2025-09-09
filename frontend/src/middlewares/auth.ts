import type { NavigationGuardNext, RouteLocationNormalized } from 'vue-router';

// Helper function to get cookie value by name
function getCookie(name: string): string | null {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) {
        const cookieValue = parts.pop()?.split(';').shift();
        return cookieValue || null;
    }
    return null;
}

// Check if user is authenticated
export function isAuthenticated(): boolean {
    console.log("Checking if session is authenticated");
    console.log("All cookies:", document.cookie);
    
    // Check for various possible cookie names
    const possibleCookieNames = ['user_token', 'session_token', 'auth_token', 'token', 'mythicaldash_token'];
    
    for (const cookieName of possibleCookieNames) {
        const token = getCookie(cookieName);
        if (token) {
            console.log(`Found ${cookieName}:`, token);
            return true;
        }
    }
    
    console.log("No authentication cookie found");
    return false;
}

// Guest middleware for auth pages
export function guestMiddleware(to: RouteLocationNormalized, from: RouteLocationNormalized, next: NavigationGuardNext) {
    if (isAuthenticated()) {
		console.log("Session is authenticated");
        return next({ name: 'Dashboard' });
    }
    console.log("Session is not authenticated");
    return next();
}

// Auth middleware
export function authMiddleware(to: RouteLocationNormalized, from: RouteLocationNormalized, next: NavigationGuardNext) {
    if (!isAuthenticated()) {
		console.log("Session is not authenticated");
        // Store the intended destination for redirection after login
        localStorage.setItem('redirectAfterLogin', to.fullPath);
		console.log("Redirecting to: Login");
        return next({ name: 'Login' });
    }
    console.log("Session is authenticated");
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
		console.log("Redirecting to: " + redirectPath);
        localStorage.removeItem('redirectAfterLogin');
        return next(redirectPath);
    }

    console.log("Redirecting to: Dashboard");
    return next({ name: 'Dashboard' });
}
