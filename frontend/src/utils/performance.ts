import { type PerformanceSettings, useSkinSettings } from '@/composables/useSkinSettings';

interface OptimizedWindow extends Window {
    __performanceCleanupInterval?: number;
    __domCache?: Record<string, Element>;
    gc?: () => void;
}

class PerformanceManager {
    private static instance: PerformanceManager;
    private observer: IntersectionObserver | null = null;
    private animationFrame: number | null = null;
    private lastFrameTime = 0;
    private cleanupInterval: number | null = null;

    private constructor() {
        // Don't initialize immediately - wait for proper timing
    }

    public static getInstance(): PerformanceManager {
        if (!PerformanceManager.instance) {
            PerformanceManager.instance = new PerformanceManager();
        }
        return PerformanceManager.instance;
    }

    public initialize(): void {
        if (typeof window === 'undefined') return;

        try {
            const { performanceSettings } = useSkinSettings();
            this.initializePerformanceOptimizations(performanceSettings);
        } catch (error) {
            console.warn('Failed to initialize performance optimizations:', error);
        }
    }

    private initializePerformanceOptimizations(settings: PerformanceSettings): void {
        // Apply CSS custom properties for performance settings
        this.updateCSSVariables(settings);

        // Set up frame rate limiting
        if (settings.frameRateLimit < 60) {
            this.setupFrameRateLimiting(settings.frameRateLimit);
        }

        // Initialize lazy loading if enabled
        if (settings.lazyLoading) {
            this.setupLazyLoading();
        }

        // Setup memory optimization
        if (settings.memoryOptimization) {
            this.setupMemoryOptimization();
        }

        // Setup reduce motion
        if (settings.reduceMotion) {
            this.setupReduceMotion();
        }

        // Apply transition speed
        this.applyTransitionSpeed(settings.transitionSpeed);
    }

    private updateCSSVariables(settings: PerformanceSettings): void {
        const root = document.documentElement;

        // Animation duration based on transition speed
        const duration = `${1 / settings.transitionSpeed}s`;
        root.style.setProperty('--transition-duration', duration);

        // Disable animations if needed
        if (settings.disableAnimations) {
            root.style.setProperty('--animation-duration', '0s');
            root.style.setProperty('--transition-duration', '0s');
            document.body.classList.add('disable-animations');
        } else {
            document.body.classList.remove('disable-animations');
        }

        // Background effects
        if (!settings.backgroundEffects) {
            root.style.setProperty('--background-opacity', '0');
            root.style.setProperty('--backdrop-blur', 'none');
            document.body.classList.add('disable-backgrounds');
        } else {
            document.body.classList.remove('disable-backgrounds');
        }
    }

    private applyTransitionSpeed(speed: number): void {
        const root = document.documentElement;
        root.style.setProperty('--global-transition-speed', speed.toString());

        // Apply to existing transition classes
        const style = document.createElement('style');
        style.id = 'performance-transitions';

        // Remove existing style if it exists
        const existingStyle = document.getElementById('performance-transitions');
        if (existingStyle) {
            existingStyle.remove();
        }

        style.textContent = `
            :root {
                --performance-transition-duration: ${1 / speed}s;
            }
            
            .transition-all,
            .transition {
                transition-duration: var(--performance-transition-duration) !important;
            }
            
            [class*="transition-"] {
                transition-duration: var(--performance-transition-duration) !important;
            }
        `;

        document.head.appendChild(style);
    }

    private setupFrameRateLimiting(targetFPS: number): void {
        const frameInterval = 1000 / targetFPS;
        // const optimizedWindow = window as OptimizedWindow;

        const originalRAF = window.requestAnimationFrame;
        const limitedRequestAnimationFrame = (callback: FrameRequestCallback): number => {
            const now = performance.now();
            const elapsed = now - this.lastFrameTime;

            if (elapsed >= frameInterval) {
                this.lastFrameTime = now;
                return originalRAF(callback);
            } else {
                return window.setTimeout(() => {
                    return originalRAF(callback);
                }, frameInterval - elapsed) as unknown as number;
            }
        };

        // Override global requestAnimationFrame
        window.requestAnimationFrame = limitedRequestAnimationFrame;
    }

    private setupLazyLoading(): void {
        // Create intersection observer for lazy loading
        this.observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        const target = entry.target as HTMLElement;
                        this.loadElement(target);
                        this.observer?.unobserve(target);
                    }
                });
            },
            {
                rootMargin: '50px',
                threshold: 0.1,
            },
        );

        // Observe all lazy-loadable elements
        this.observeLazyElements();

        // Set up mutation observer to watch for new lazy elements
        const mutationObserver = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        const element = node as Element;
                        if (element.hasAttribute('data-lazy')) {
                            this.observer?.observe(element);
                        }
                        // Check children too
                        const lazyChildren = element.querySelectorAll('[data-lazy]');
                        lazyChildren.forEach((child) => {
                            this.observer?.observe(child);
                        });
                    }
                });
            });
        });

        mutationObserver.observe(document.body, {
            childList: true,
            subtree: true,
        });
    }

    private observeLazyElements(): void {
        const lazyElements = document.querySelectorAll('[data-lazy]');
        lazyElements.forEach((element) => {
            this.observer?.observe(element);
        });
    }

    private loadElement(element: HTMLElement): void {
        const dataSrc = element.getAttribute('data-src');
        const dataBackground = element.getAttribute('data-background');

        if (dataSrc && element instanceof HTMLImageElement) {
            try {
                const validatedSrc = new URL(dataSrc, window.location.origin).toString();
                element.src = validatedSrc;
                element.removeAttribute('data-src');
            } catch {
                console.error('Invalid data-src URL:', dataSrc);
            }
        }

        if (dataBackground) {
            // Properly escape the background URL to prevent CSS injection
            const escapedBackground = this.escapeCSSUrl(dataBackground);
            element.style.backgroundImage = `url(${escapedBackground})`;
            element.removeAttribute('data-background');
        }

        element.removeAttribute('data-lazy');
        element.classList.add('loaded');
    }

    /**
     * Escape a URL for safe use in CSS url() function
     * Prevents CSS injection attacks by properly escaping special characters
     *
     * @param url The URL to escape
     * @returns The escaped URL safe for CSS
     */
    private escapeCSSUrl(url: string): string {
        // Remove any existing quotes and escape special characters
        let escaped = url.replace(/['"]/g, ''); // Remove quotes

        // Escape backslashes and other special characters that could be used for injection
        escaped = escaped.replace(/\\/g, '\\\\'); // Escape backslashes
        escaped = escaped.replace(/\)/g, '\\)'); // Escape closing parentheses

        // Validate that it's a safe URL (basic check)
        try {
            // Try to create a URL object to validate
            new URL(escaped, window.location.origin);
        } catch {
            // If it's not a valid URL, return empty string to prevent injection
            console.warn('Invalid URL detected in data-background attribute:', url);
            return '';
        }

        return escaped;
    }

    private setupMemoryOptimization(): void {
        const optimizedWindow = window as OptimizedWindow;

        // Aggressive garbage collection hints
        this.cleanupInterval = window.setInterval(() => {
            // Clear unused event listeners
            this.cleanupEventListeners();

            // Clear cached DOM queries
            this.clearCachedQueries();

            // Force garbage collection if available
            if (optimizedWindow.gc) {
                optimizedWindow.gc();
            }
        }, 30000); // Every 30 seconds

        // Store interval ID for cleanup
        optimizedWindow.__performanceCleanupInterval = this.cleanupInterval;
    }

    private cleanupEventListeners(): void {
        // Remove unused event listeners from DOM elements
        const elements = document.querySelectorAll('[data-cleanup-listeners]');
        elements.forEach((element) => {
            const clonedElement = element.cloneNode(true);
            element.parentNode?.replaceChild(clonedElement, element);
        });
    }

    private clearCachedQueries(): void {
        const optimizedWindow = window as OptimizedWindow;
        // Clear any cached DOM queries stored in global objects
        if (optimizedWindow.__domCache) {
            optimizedWindow.__domCache = {};
        }
    }

    private setupReduceMotion(): void {
        // Add reduced motion class to body
        document.body.classList.add('reduce-motion');

        // Override CSS animations
        const style = document.createElement('style');
        style.id = 'reduce-motion-styles';
        style.textContent = `
            .reduce-motion *,
            .reduce-motion *::before,
            .reduce-motion *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
                scroll-behavior: auto !important;
            }
            
            .disable-animations * {
                animation: none !important;
                transition: none !important;
            }
            
            .disable-backgrounds .bg-gradient-to-r,
            .disable-backgrounds .bg-gradient-to-l,
            .disable-backgrounds .bg-gradient-to-t,
            .disable-backgrounds .bg-gradient-to-b,
            .disable-backgrounds .bg-gradient-to-br,
            .disable-backgrounds .bg-gradient-to-bl,
            .disable-backgrounds .bg-gradient-to-tr,
            .disable-backgrounds .bg-gradient-to-tl {
                background: transparent !important;
            }
        `;
        document.head.appendChild(style);
    }

    public updateSettings(): void {
        try {
            const { performanceSettings } = useSkinSettings();
            this.initializePerformanceOptimizations(performanceSettings);
        } catch (error) {
            console.warn('Failed to update performance settings:', error);
        }
    }

    public preloadCriticalResources(resources: string[]): void {
        try {
            const { performanceSettings } = useSkinSettings();

            if (!performanceSettings.preloadCritical) return;

            resources.forEach((resource) => {
                const link = document.createElement('link');
                link.rel = 'preload';

                if (resource.endsWith('.css')) {
                    link.as = 'style';
                } else if (resource.endsWith('.js')) {
                    link.as = 'script';
                } else if (resource.match(/\.(jpg|jpeg|png|webp|gif)$/)) {
                    link.as = 'image';
                }

                link.href = resource;
                document.head.appendChild(link);
            });
        } catch (error) {
            console.warn('Failed to preload resources:', error);
        }
    }

    public optimizeImage(src: string, options: { width?: number; height?: number; quality?: number } = {}): string {
        try {
            const { performanceSettings } = useSkinSettings();

            if (!performanceSettings.optimizeImages) return src;

            // Add optimization parameters to image URLs
            const url = new URL(src, window.location.origin);
            const params = new URLSearchParams();

            if (options.width) params.set('w', options.width.toString());
            if (options.height) params.set('h', options.height.toString());
            if (options.quality) params.set('q', options.quality.toString());

            // Add format optimization
            if (this.supportsWebP()) {
                params.set('f', 'webp');
            }

            url.search = params.toString();
            return url.toString();
        } catch (error) {
            console.warn('Failed to optimize image:', error);
            return src;
        }
    }

    private supportsWebP(): boolean {
        try {
            const canvas = document.createElement('canvas');
            canvas.width = 1;
            canvas.height = 1;
            return canvas.toDataURL('image/webp').indexOf('webp') > -1;
        } catch {
            return false;
        }
    }

    public enableCaching(): void {
        try {
            const { performanceSettings } = useSkinSettings();

            if (!performanceSettings.enableCaching) return;

            // Enable service worker for caching
            if ('serviceWorker' in navigator) {
            }
        } catch (error) {
            console.warn('Failed to enable caching:', error);
        }
    }

    public destroy(): void {
        // Cleanup
        if (this.observer) {
            this.observer.disconnect();
        }

        if (this.animationFrame) {
            cancelAnimationFrame(this.animationFrame);
        }

        if (this.cleanupInterval) {
            clearInterval(this.cleanupInterval);
        }

        // Clear cleanup interval
        const optimizedWindow = window as OptimizedWindow;
        if (optimizedWindow.__performanceCleanupInterval) {
            clearInterval(optimizedWindow.__performanceCleanupInterval);
            delete optimizedWindow.__performanceCleanupInterval;
        }

        // Remove styles
        const reduceMotionStyle = document.getElementById('reduce-motion-styles');
        const performanceStyle = document.getElementById('performance-transitions');
        if (reduceMotionStyle) reduceMotionStyle.remove();
        if (performanceStyle) performanceStyle.remove();
    }
}

// Export singleton instance
export const performanceManager = PerformanceManager.getInstance();

// Utility functions
export const optimizeImage = (src: string, options?: { width?: number; height?: number; quality?: number }) => {
    return performanceManager.optimizeImage(src, options);
};

export const preloadResources = (resources: string[]) => {
    performanceManager.preloadCriticalResources(resources);
};

export const initializePerformanceOptimizations = () => {
    performanceManager.initialize();
    performanceManager.enableCaching();

    // Preload critical resources
    const criticalResources = ['/assets/main.css', '/api/system/custom.css'];

    preloadResources(criticalResources);
};

export const updatePerformanceSettings = () => {
    performanceManager.updateSettings();
};
