import { reactive, watch } from 'vue';

export interface SkinSettings {
    walkingAnimation: boolean;
    autoRotate: boolean;
    zoom: number;
    animationSpeed: number;
    cameraLight: number;
    globalLight: number;
}

export type DropdownAnimationStyle = 'fade' | 'slide' | 'scale' | 'bounce';
export type BorderRadiusSize = 'sm' | 'md' | 'lg' | 'xl';

export interface DropdownSettings {
    backdropBlur: boolean;
    glassEffect: boolean;
    animationStyle: DropdownAnimationStyle;
    borderGlow: boolean;
    showBackground: boolean;
    showUserBackground: boolean;
    backgroundOpacity: number;
    borderRadius: BorderRadiusSize;
}

export interface SidebarSettings {
    glassEffect: boolean;
    backgroundOpacity: number;
    borderGlow: boolean;
    compactMode: boolean;
    showSectionDividers: boolean;
    animationStyle: DropdownAnimationStyle;
}

export interface TopNavSettings {
    glassEffect: boolean;
    backgroundOpacity: number;
    borderGlow: boolean;
    showSearchBar: boolean;
}

export interface NotificationSettings {
    glassEffect: boolean;
    backgroundOpacity: number;
    borderGlow: boolean;
    animationStyle: DropdownAnimationStyle;
    showBackground: boolean;
}

export interface PerformanceSettings {
    reduceMotion: boolean;
    disableAnimations: boolean;
    optimizeImages: boolean;
    lazyLoading: boolean;
    preloadCritical: boolean;
    backgroundEffects: boolean;
    transitionSpeed: number;
    frameRateLimit: number;
    memoryOptimization: boolean;
    networkOptimization: boolean;
    enableCaching: boolean;
    compressAssets: boolean;
}

export interface UISettings {
    skin: SkinSettings;
    dropdown: DropdownSettings;
    sidebar: SidebarSettings;
    topNav: TopNavSettings;
    notifications: NotificationSettings;
    performance: PerformanceSettings;
}

const defaultSkinSettings: SkinSettings = {
    walkingAnimation: true,
    autoRotate: false,
    zoom: 0.8,
    animationSpeed: 1.0,
    cameraLight: 3.0,
    globalLight: 2.9,
};

const defaultDropdownSettings: DropdownSettings = {
    backdropBlur: true,
    glassEffect: true,
    animationStyle: 'fade',
    borderGlow: true,
    showBackground: true,
    showUserBackground: true,
    backgroundOpacity: 0.95,
    borderRadius: 'xl',
};

const defaultSidebarSettings: SidebarSettings = {
    glassEffect: true,
    backgroundOpacity: 0.95,
    borderGlow: true,
    compactMode: false,
    showSectionDividers: true,
    animationStyle: 'fade',
};

const defaultTopNavSettings: TopNavSettings = {
    glassEffect: true,
    backgroundOpacity: 0.95,
    borderGlow: true,
    showSearchBar: true,
};

const defaultNotificationSettings: NotificationSettings = {
    glassEffect: true,
    backgroundOpacity: 0.95,
    borderGlow: true,
    animationStyle: 'fade',
    showBackground: true,
};

const defaultPerformanceSettings: PerformanceSettings = {
    reduceMotion: false,
    disableAnimations: false,
    optimizeImages: true,
    lazyLoading: true,
    preloadCritical: true,
    backgroundEffects: true,
    transitionSpeed: 4.5,
    frameRateLimit: 75,
    memoryOptimization: false,
    networkOptimization: true,
    enableCaching: true,
    compressAssets: true,
};

const defaultSettings: UISettings = {
    skin: defaultSkinSettings,
    dropdown: defaultDropdownSettings,
    sidebar: defaultSidebarSettings,
    topNav: defaultTopNavSettings,
    notifications: defaultNotificationSettings,
    performance: defaultPerformanceSettings,
};

// Create reactive settings that persist across components
const settings = reactive<UISettings>({ ...defaultSettings });

// Load settings from localStorage
const loadSettings = () => {
    const saved = localStorage.getItem('ui-settings');
    if (saved) {
        try {
            const parsedSettings = JSON.parse(saved);
            Object.assign(settings, {
                skin: { ...defaultSkinSettings, ...parsedSettings.skin },
                dropdown: { ...defaultDropdownSettings, ...parsedSettings.dropdown },
                sidebar: { ...defaultSidebarSettings, ...parsedSettings.sidebar },
                topNav: { ...defaultTopNavSettings, ...parsedSettings.topNav },
                notifications: { ...defaultNotificationSettings, ...parsedSettings.notifications },
                performance: { ...defaultPerformanceSettings, ...parsedSettings.performance },
            });
        } catch (e) {
            console.warn('Failed to parse saved UI settings', e);
        }
    }
};

// Save settings to localStorage
const saveSettings = () => {
    localStorage.setItem('ui-settings', JSON.stringify(settings));
};

// Initialize settings on first load
let initialized = false;
if (!initialized) {
    loadSettings();
    initialized = true;
}

// Watch for changes and auto-save
watch(settings, saveSettings, { deep: true });

export const useSkinSettings = () => {
    const updateSkinSetting = <K extends keyof SkinSettings>(key: K, value: SkinSettings[K]) => {
        settings.skin[key] = value;
    };

    const updateDropdownSetting = <K extends keyof DropdownSettings>(key: K, value: DropdownSettings[K]) => {
        settings.dropdown[key] = value;
    };

    const updateSidebarSetting = <K extends keyof SidebarSettings>(key: K, value: SidebarSettings[K]) => {
        settings.sidebar[key] = value;
    };

    const updateTopNavSetting = <K extends keyof TopNavSettings>(key: K, value: TopNavSettings[K]) => {
        settings.topNav[key] = value;
    };

    const updateNotificationSetting = <K extends keyof NotificationSettings>(
        key: K,
        value: NotificationSettings[K],
    ) => {
        settings.notifications[key] = value;
    };

    const updatePerformanceSetting = <K extends keyof PerformanceSettings>(key: K, value: PerformanceSettings[K]) => {
        settings.performance[key] = value;
    };

    const resetToDefaults = () => {
        Object.assign(settings, defaultSettings);
    };

    return {
        skinSettings: settings.skin,
        dropdownSettings: settings.dropdown,
        sidebarSettings: settings.sidebar,
        topNavSettings: settings.topNav,
        notificationSettings: settings.notifications,
        performanceSettings: settings.performance,
        updateSkinSetting,
        updateDropdownSetting,
        updateSidebarSetting,
        updateTopNavSetting,
        updateNotificationSetting,
        updatePerformanceSetting,
        resetToDefaults,
        loadSettings,
        saveSettings,
    };
};
