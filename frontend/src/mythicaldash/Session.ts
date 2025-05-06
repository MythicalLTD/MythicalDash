import router from '@/router';
import Swal from 'sweetalert2';

interface SessionResponse {
    success: boolean;
    error_code?: string;
    user_info: Record<string, unknown>;
    billing: Record<string, unknown>;
    stats: Record<string, unknown>;
}

/**
 * Session class for managing user session data
 * Enhanced with better error handling, retry logic, and performance optimizations
 */
class Session {
    private static sessionData: Record<string, unknown> = {};
    private static updateInterval: number | null = null;
    private static initPromise: Promise<void> | null = null;
    private static retryCount = 0;
    private static maxRetries = 3;
    private static retryDelay = 2000; // milliseconds
    private static isRefreshing = false;
    /**
     * Checks if the current session is valid by looking for the user_token cookie
     */
    static isSessionValid(): boolean {
        return document.cookie.split(';').some((cookie) => cookie.trim().startsWith('user_token='));
    }

    /**
     * Gets session information from memory or localStorage
     */
    static getInfo(key: string): string {
        // Try memory first for better performance
        if (this.sessionData[key] !== undefined) {
            return this.sessionData[key] as string;
        }

        // Fall back to localStorage
        const item = localStorage.getItem(key);
        if (item) {
            try {
                const value = JSON.parse(item);
                this.sessionData[key] = value; // Cache in memory
                return value;
            } catch {
                return item;
            }
        }
        return '';
    }

    /**
     * Gets session information as an integer
     */
    static getInfoInt(key: string): number {
        return parseInt(this.getInfo(key)) || 0;
    }

    /**
     * Fetches session data from the server with retry logic
     */
    private static async fetchSessionData(retry = true): Promise<SessionResponse> {
        try {
            const response = await fetch('/api/user/session', {
                headers: {
                    'Cache-Control': 'no-cache',
                    Pragma: 'no-cache',
                },
            });

            // Handle HTTP errors including 503 Service Unavailable
            if (!response.ok) {
                const errorCode = response.status === 503 ? 'SERVER_UNAVAILABLE' : 'SERVER_ERROR';
                console.error(`Server responded with status ${response.status}: ${response.statusText}`);

                // For server availability issues, clear the session immediately
                if (response.status === 503 || response.status >= 500) {
                    if (this.isSessionValid()) {
                        await this.handleSessionError({
                            success: false,
                            error_code: errorCode,
                            user_info: {},
                            billing: {},
                            stats: {},
                        });
                    }
                    throw new Error(`Server error: ${response.status} ${response.statusText}`);
                }

                throw new Error(`Server responded with status ${response.status}`);
            }

            const data = await response.json();

            if (!data.success && this.isSessionValid()) {
                await this.handleSessionError(data);
            }

            // Reset retry count on success
            this.retryCount = 0;
            return data;
        } catch (error) {
            console.error('Error fetching session data:', error);

            // Don't retry for server availability issues (already handled above)
            if (
                error instanceof Error &&
                (error.message.includes('503') || error.message.includes('SERVER_UNAVAILABLE'))
            ) {
                throw error;
            }

            // Implement retry logic for other types of errors
            if (retry && this.retryCount < this.maxRetries) {
                this.retryCount++;
                console.log(`Retrying session fetch (${this.retryCount}/${this.maxRetries})...`);

                return new Promise((resolve, reject) => {
                    setTimeout(async () => {
                        try {
                            const result = await this.fetchSessionData(true);
                            resolve(result);
                        } catch (retryError) {
                            console.error('Retry failed:', retryError);
                            // Don't resolve with a fake success response, properly reject
                            reject(retryError);
                        }
                    }, this.retryDelay);
                });
            }

            // Handle network/server errors by clearing the session
            if (this.isSessionValid()) {
                await this.handleSessionError({
                    success: false,
                    error_code: 'SERVER_ERROR',
                    user_info: {},
                    billing: {},
                    stats: {},
                });
            }

            throw error;
        }
    }

    /**
     * Handles session errors and redirects
     */
    private static async handleSessionError(data: SessionResponse): Promise<void> {
        // Remove the user_token cookie (more aggressively)
        document.cookie = 'user_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
        document.cookie =
            'user_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; domain=' + window.location.hostname;
        // Also try with no domain specified
        document.cookie = 'user_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
        // Also try with subdomain wildcard
        document.cookie =
            'user_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; domain=.' + window.location.hostname;

        // Clear any stored session data
        localStorage.clear();
        this.sessionData = {};

        // Cancel any pending update interval
        this.cleanup();

        if (data.error_code === 'TW0_FA_BLOCKED') {
            router.push('/auth/2fa/verify');
        } else if (data.error_code === 'SERVER_UNAVAILABLE') {
            await Swal.fire({
                title: 'Server Unavailable',
                text: 'The server is currently unavailable. Please try again later.',
                icon: 'error',
                confirmButtonText: 'OK',
            });
            router.push('/auth/login');
        } else {
            await Swal.fire({
                title: 'Session Error',
                text: 'Your session has expired or is invalid.',
                footer: 'Please log in again.',
                icon: 'error',
                confirmButtonText: 'OK',
            });
            router.push('/auth/login');
        }
    }

    /**
     * Updates session information in memory and localStorage
     */
    private static updateSessionStorage(data: SessionResponse): void {
        if (!data || !data.success) return;

        const { user_info, billing, stats } = data;

        // Update memory cache
        this.sessionData = {
            ...this.sessionData,
            ...(stats || {}),
        };

        try {
            // Update localStorage with null checks
            if (user_info && typeof user_info === 'object') {
                Object.entries(user_info).forEach(([key, value]) => {
                    if (value !== null && value !== undefined) {
                        localStorage.setItem(key, JSON.stringify(value));
                    }
                });
            }

            if (billing && typeof billing === 'object') {
                Object.entries(billing).forEach(([key, value]) => {
                    if (value !== null && value !== undefined) {
                        localStorage.setItem(key, JSON.stringify(value));
                    }
                });
            }

            if (stats && typeof stats === 'object') {
                Object.entries(stats).forEach(([key, value]) => {
                    if (value !== null && value !== undefined) {
                        localStorage.setItem(key, JSON.stringify(value));
                    }
                });
            }
        } catch (error) {
            console.error('Error updating session storage:', error);
        }
    }

    /**
     * Ensures session is initialized only once
     */
    private static async ensureInitialized(): Promise<void> {
        if (!this.initPromise) {
            this.initPromise = this.initialize();
        }
        return this.initPromise;
    }

    /**
     * Initializes the session
     */
    private static async initialize(): Promise<void> {
        try {
            if (!this.isSessionValid()) {
                throw new Error('No valid session found');
            }

            const data = await this.fetchSessionData();
            if (data.success) {
                this.updateSessionStorage(data);
            }
        } catch (error) {
            console.error('Error initializing session:', error);
            this.initPromise = null;
            throw error;
        }
    }

    /**
     * Refreshes the session data
     */
    static async refreshSession(): Promise<boolean> {
        if (this.isRefreshing) return false;

        try {
            this.isRefreshing = true;

            if (!this.isSessionValid()) {
                return false;
            }

            const data = await this.fetchSessionData(false);
            if (data.success) {
                this.updateSessionStorage(data);
                return true;
            }
            return false;
        } catch (error) {
            console.error('Error refreshing session:', error);
            return false;
        } finally {
            this.isRefreshing = false;
        }
    }

    /**
     * Starts the session and sets up periodic updates
     */
    static async startSession(): Promise<void> {
        // Cleanup any existing interval
        if (this.updateInterval !== null) {
            clearInterval(this.updateInterval);
            this.updateInterval = null;
        }

        if (!this.isSessionValid()) {
            console.warn('Cannot start session: No valid session token');
            return;
        }

        try {
            await this.ensureInitialized();

            // Set up periodic updates with advanced error handling
            this.updateInterval = window.setInterval(async () => {
                if (!this.isSessionValid()) {
                    this.cleanup();
                    return;
                }

                try {
                    await this.refreshSession();
                } catch (error) {
                    console.error('Error updating session:', error);

                    // If we got a server error, stop trying to refresh
                    if (
                        error instanceof Error &&
                        (error.message.includes('503') ||
                            error.message.includes('SERVER_UNAVAILABLE') ||
                            error.message.includes('SERVER_ERROR'))
                    ) {
                        console.error('Server unavailable, stopping session refresh');
                        this.cleanup();
                    }
                }
            }, 60000); // Update every minute
        } catch (error) {
            console.error('Failed to start session:', error);
            this.cleanup();

            // Only redirect if there's a valid token but session initialization failed
            if (this.isSessionValid()) {
                await Swal.fire({
                    title: 'Error',
                    text: 'Failed to start session. Please try again.',
                    icon: 'error',
                    confirmButtonText: 'OK',
                });
                router.push('/auth/login');
            }
        }
    }

    /**
     * Cleans up session resources
     */
    static cleanup(): void {
        if (this.updateInterval !== null) {
            clearInterval(this.updateInterval);
            this.updateInterval = null;
        }
        this.retryCount = 0;
        this.sessionData = {};
        this.initPromise = null;
        this.isRefreshing = false;
    }
}

export default Session;
