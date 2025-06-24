<script setup lang="ts">
import type { TransitionType } from '@/types/transitions';
import { ref } from 'vue';
import {
    useSkinSettings,
    type DropdownAnimationStyle,
    type DropdownSettings,
    type SidebarSettings,
    type TopNavSettings,
    type NotificationSettings,
} from '@/composables/useSkinSettings';

const props = defineProps<{
    selectedTransition: TransitionType;
}>();

const emit = defineEmits<{
    (e: 'select', type: TransitionType): void;
    (e: 'close'): void;
}>();

const activeTab = ref('transitions');

// Use skin settings composable
const {
    skinSettings,
    dropdownSettings,
    sidebarSettings,
    topNavSettings,
    notificationSettings,
    performanceSettings,
    updateSkinSetting,
    updateDropdownSetting,
    updateSidebarSetting,
    updateTopNavSetting,
    updateNotificationSetting,
    updatePerformanceSetting,
} = useSkinSettings();

// Define specific types for each UI section
interface DropdownSection {
    id: 'dropdown';
    name: string;
    settings: DropdownSettings;
    updateSetting: typeof updateDropdownSetting;
    hasAnimations: true;
    hasBorderRadius: true;
    hasBackground: true;
    hasUserBackground: true;
}

interface NotificationSection {
    id: 'notifications';
    name: string;
    settings: NotificationSettings;
    updateSetting: typeof updateNotificationSetting;
    hasAnimations: true;
    hasBackground: true;
}

interface SidebarSection {
    id: 'sidebar';
    name: string;
    settings: SidebarSettings;
    updateSetting: typeof updateSidebarSetting;
    hasAnimations: true;
    hasCompactMode: true;
    hasSectionDividers: true;
}

interface TopNavSection {
    id: 'topnav';
    name: string;
    settings: TopNavSettings;
    updateSetting: typeof updateTopNavSetting;
    hasSearchBar: true;
}

type UISection = DropdownSection | NotificationSection | SidebarSection | TopNavSection;

const transitions: Array<{ id: TransitionType; name: string; description: string }> = [
    {
        id: 'fade',
        name: 'Fade',
        description: 'Simple fade in and out',
    },
    {
        id: 'slide-right',
        name: 'Slide Right',
        description: 'Pages slide from left to right',
    },
    {
        id: 'slide-left',
        name: 'Slide Left',
        description: 'Pages slide from right to left',
    },
    {
        id: 'slide-up',
        name: 'Slide Up',
        description: 'Pages slide from bottom to top',
    },
    {
        id: 'slide-down',
        name: 'Slide Down',
        description: 'Pages slide from top to bottom',
    },
    {
        id: 'scale',
        name: 'Scale',
        description: 'Pages zoom in and out',
    },
    {
        id: 'flip',
        name: 'Flip',
        description: '3D flip animation',
    },
    {
        id: 'rotate',
        name: 'Rotate',
        description: 'Subtle rotation with scaling',
    },
];

const selectTransition = (type: TransitionType) => {
    emit('select', type);
};

const tabs = [
    {
        id: 'transitions',
        name: 'Transitions',
        icon: 'TransitionIcon',
    },
    {
        id: 'appearance',
        name: 'UI Components',
        icon: 'PaintBrushIcon',
    },
    {
        id: 'performance',
        name: 'Performance',
        icon: 'SpeedIcon',
    },
];

const dropdownAnimations: Array<{ id: DropdownAnimationStyle; name: string; description: string }> = [
    { id: 'fade', name: 'Fade', description: 'Smooth fade animation' },
    { id: 'slide', name: 'Slide', description: 'Slide from right' },
    { id: 'scale', name: 'Scale', description: 'Scale up/down effect' },
    { id: 'bounce', name: 'Bounce', description: 'Bouncy entrance' },
];

const uiSections = ref<UISection[]>([
    {
        id: 'dropdown',
        name: 'Profile Dropdown',
        settings: dropdownSettings,
        updateSetting: updateDropdownSetting,
        hasAnimations: true,
        hasBorderRadius: true,
        hasBackground: true,
        hasUserBackground: true,
    } as DropdownSection,
    {
        id: 'notifications',
        name: 'Notifications',
        settings: notificationSettings,
        updateSetting: updateNotificationSetting,
        hasAnimations: true,
        hasBackground: true,
    } as NotificationSection,
    {
        id: 'sidebar',
        name: 'Sidebar',
        settings: sidebarSettings,
        updateSetting: updateSidebarSetting,
        hasAnimations: true,
        hasCompactMode: true,
        hasSectionDividers: true,
    } as SidebarSection,
    {
        id: 'topnav',
        name: 'Top Navigation',
        settings: topNavSettings,
        updateSetting: updateTopNavSetting,
        hasSearchBar: true,
    } as TopNavSection,
]);

// Type guard functions
const isDropdownSection = (section: UISection): section is DropdownSection => section.id === 'dropdown';
const isNotificationSection = (section: UISection): section is NotificationSection => section.id === 'notifications';
const isSidebarSection = (section: UISection): section is SidebarSection => section.id === 'sidebar';
const isTopNavSection = (section: UISection): section is TopNavSection => section.id === 'topnav';

// Helper functions for updating settings
const updateSectionSetting = (section: UISection, key: string, value: unknown) => {
    if (isDropdownSection(section)) {
        const typedKey = key as keyof DropdownSettings;
        const typedValue = value as DropdownSettings[keyof DropdownSettings];
        section.updateSetting(typedKey, typedValue);
    } else if (isNotificationSection(section)) {
        const typedKey = key as keyof NotificationSettings;
        const typedValue = value as NotificationSettings[keyof NotificationSettings];
        section.updateSetting(typedKey, typedValue);
    } else if (isSidebarSection(section)) {
        const typedKey = key as keyof SidebarSettings;
        const typedValue = value as SidebarSettings[keyof SidebarSettings];
        section.updateSetting(typedKey, typedValue);
    } else if (isTopNavSection(section)) {
        const typedKey = key as keyof TopNavSettings;
        const typedValue = value as TopNavSettings[keyof TopNavSettings];
        section.updateSetting(typedKey, typedValue);
    }
};

const getSectionSetting = (section: UISection, key: string): number | boolean | string => {
    type SettingsUnion = DropdownSettings | NotificationSettings | SidebarSettings | TopNavSettings;
    const settings = section.settings as SettingsUnion;
    const value = settings[key as keyof SettingsUnion];
    return value as number | boolean | string;
};

const hasSectionProperty = (section: UISection, property: string): boolean => {
    return property in section;
};
</script>

<template>
    <!-- Backdrop overlay -->
    <div class="fixed inset-0 bg-black/20 backdrop-blur-sm z-[999]" @click="$emit('close')"></div>

    <!-- Settings Panel -->
    <div
        class="fixed bottom-20 right-5 w-[400px] max-h-[700px] bg-white dark:bg-gray-900 rounded-lg shadow-xl z-[1000] overflow-hidden flex flex-col border border-gray-200 dark:border-gray-800"
        @click.stop
    >
        <div class="flex justify-between items-center px-5 py-4 border-b border-gray-200 dark:border-gray-800">
            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">UI Customization</h3>
            <button
                @click="$emit('close')"
                class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 p-1 rounded-md transition-colors duration-200"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="20"
                    height="20"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <!-- Settings Tabs -->
        <div class="flex gap-2 p-3 border-b border-gray-200 dark:border-gray-800">
            <button
                v-for="tab in tabs"
                :key="tab.id"
                class="px-4 py-2 rounded-md text-sm font-medium transition-all duration-200"
                :class="{
                    'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-400': activeTab === tab.id,
                    'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800': activeTab !== tab.id,
                }"
                @click="activeTab = tab.id"
            >
                {{ tab.name }}
            </button>
        </div>

        <!-- Settings Content -->
        <div class="overflow-y-auto p-3">
            <!-- Transitions Tab -->
            <div v-if="activeTab === 'transitions'" class="space-y-2">
                <div
                    v-for="transition in transitions"
                    :key="transition.id"
                    class="flex items-center p-3 rounded-lg cursor-pointer transition-all duration-200"
                    :class="{
                        'bg-indigo-50 border border-indigo-100 dark:bg-indigo-900/30 dark:border-indigo-800':
                            props.selectedTransition === transition.id,
                        'hover:bg-gray-50 dark:hover:bg-gray-800/50': props.selectedTransition !== transition.id,
                    }"
                    @click="selectTransition(transition.id)"
                >
                    <div
                        class="w-12 h-12 bg-gray-100 dark:bg-gray-800 rounded-md mr-4 flex items-center justify-center overflow-hidden relative"
                    >
                        <div
                            class="w-8 h-8 bg-gray-400 dark:bg-gray-600 rounded transition-transform duration-200"
                            :class="[`preview-${transition.id}`]"
                        ></div>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-1">{{ transition.name }}</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ transition.description }}</p>
                    </div>
                </div>
            </div>

            <!-- UI Components Tab -->
            <div v-if="activeTab === 'appearance'" class="space-y-6">
                <div v-for="section in uiSections" :key="section.id">
                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-4">{{ section.name }}</h4>

                    <!-- Basic Settings -->
                    <div class="space-y-3 mb-4">
                        <!-- Glass Effect Toggle -->
                        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <label class="text-sm font-medium text-gray-900 dark:text-gray-100"
                                        >Glass Effect</label
                                    >
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Enable glassmorphism styling</p>
                                </div>
                                <button
                                    @click="
                                        updateSectionSetting(
                                            section,
                                            'glassEffect',
                                            !getSectionSetting(section, 'glassEffect'),
                                        )
                                    "
                                    :class="[
                                        'relative inline-flex h-6 w-11 items-center rounded-full transition-colors',
                                        getSectionSetting(section, 'glassEffect')
                                            ? 'bg-indigo-600'
                                            : 'bg-gray-300 dark:bg-gray-600',
                                    ]"
                                >
                                    <span
                                        :class="[
                                            'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                                            getSectionSetting(section, 'glassEffect')
                                                ? 'translate-x-6'
                                                : 'translate-x-1',
                                        ]"
                                    />
                                </button>
                            </div>
                        </div>

                        <!-- Border Glow Toggle -->
                        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <label class="text-sm font-medium text-gray-900 dark:text-gray-100"
                                        >Border Glow</label
                                    >
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Add subtle glow effects</p>
                                </div>
                                <button
                                    @click="
                                        updateSectionSetting(
                                            section,
                                            'borderGlow',
                                            !getSectionSetting(section, 'borderGlow'),
                                        )
                                    "
                                    :class="[
                                        'relative inline-flex h-6 w-11 items-center rounded-full transition-colors',
                                        getSectionSetting(section, 'borderGlow')
                                            ? 'bg-indigo-600'
                                            : 'bg-gray-300 dark:bg-gray-600',
                                    ]"
                                >
                                    <span
                                        :class="[
                                            'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                                            getSectionSetting(section, 'borderGlow')
                                                ? 'translate-x-6'
                                                : 'translate-x-1',
                                        ]"
                                    />
                                </button>
                            </div>
                        </div>

                        <!-- Background Opacity -->
                        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4">
                            <label class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                                Background Opacity:
                                {{ Math.round((section.settings.backgroundOpacity || 0) * 100) }}%
                            </label>
                            <input
                                type="range"
                                min="0.1"
                                max="1.0"
                                step="0.05"
                                :value="getSectionSetting(section, 'backgroundOpacity')"
                                @input="
                                    updateSectionSetting(
                                        section,
                                        'backgroundOpacity',
                                        parseFloat(($event.target as HTMLInputElement).value),
                                    )
                                "
                                class="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer"
                            />
                        </div>
                    </div>

                    <!-- Component-specific settings -->
                    <div class="space-y-3">
                        <!-- Animation Style (for dropdowns) -->
                        <div v-if="hasSectionProperty(section, 'hasAnimations')" class="mb-4">
                            <label class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-2"
                                >Animation Style</label
                            >
                            <div class="grid grid-cols-2 gap-2">
                                <button
                                    v-for="animation in dropdownAnimations"
                                    :key="animation.id"
                                    @click="updateSectionSetting(section, 'animationStyle', animation.id)"
                                    :class="[
                                        'p-3 rounded-lg border text-left transition-all duration-200',
                                        getSectionSetting(section, 'animationStyle') === animation.id
                                            ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/30 dark:border-indigo-400'
                                            : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600',
                                    ]"
                                >
                                    <div class="text-xs font-medium text-gray-900 dark:text-gray-100">
                                        {{ animation.name }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ animation.description }}
                                    </div>
                                </button>
                            </div>
                        </div>

                        <!-- Specific component toggles -->
                        <template v-if="hasSectionProperty(section, 'hasSearchBar')">
                            <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <label class="text-sm font-medium text-gray-900 dark:text-gray-100"
                                            >Show Search Bar</label
                                        >
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            Display search functionality
                                        </p>
                                    </div>
                                    <button
                                        @click="
                                            updateSectionSetting(
                                                section,
                                                'showSearchBar',
                                                !getSectionSetting(section, 'showSearchBar'),
                                            )
                                        "
                                        :class="[
                                            'relative inline-flex h-6 w-11 items-center rounded-full transition-colors',
                                            getSectionSetting(section, 'showSearchBar')
                                                ? 'bg-indigo-600'
                                                : 'bg-gray-300 dark:bg-gray-600',
                                        ]"
                                    >
                                        <span
                                            :class="[
                                                'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                                                getSectionSetting(section, 'showSearchBar')
                                                    ? 'translate-x-6'
                                                    : 'translate-x-1',
                                            ]"
                                        />
                                    </button>
                                </div>
                            </div>
                        </template>

                        <template v-if="hasSectionProperty(section, 'hasCompactMode')">
                            <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <label class="text-sm font-medium text-gray-900 dark:text-gray-100"
                                            >Compact Mode</label
                                        >
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            Use smaller spacing and icons
                                        </p>
                                    </div>
                                    <button
                                        @click="
                                            updateSectionSetting(
                                                section,
                                                'compactMode',
                                                !getSectionSetting(section, 'compactMode'),
                                            )
                                        "
                                        :class="[
                                            'relative inline-flex h-6 w-11 items-center rounded-full transition-colors',
                                            getSectionSetting(section, 'compactMode')
                                                ? 'bg-indigo-600'
                                                : 'bg-gray-300 dark:bg-gray-600',
                                        ]"
                                    >
                                        <span
                                            :class="[
                                                'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                                                getSectionSetting(section, 'compactMode')
                                                    ? 'translate-x-6'
                                                    : 'translate-x-1',
                                            ]"
                                        />
                                    </button>
                                </div>
                            </div>
                        </template>

                        <template v-if="hasSectionProperty(section, 'hasBackground')">
                            <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <label class="text-sm font-medium text-gray-900 dark:text-gray-100"
                                            >Show Background</label
                                        >
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            Display decorative patterns
                                        </p>
                                    </div>
                                    <button
                                        @click="
                                            updateSectionSetting(
                                                section,
                                                'showBackground',
                                                !getSectionSetting(section, 'showBackground'),
                                            )
                                        "
                                        :class="[
                                            'relative inline-flex h-6 w-11 items-center rounded-full transition-colors',
                                            getSectionSetting(section, 'showBackground')
                                                ? 'bg-indigo-600'
                                                : 'bg-gray-300 dark:bg-gray-600',
                                        ]"
                                    >
                                        <span
                                            :class="[
                                                'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                                                getSectionSetting(section, 'showBackground')
                                                    ? 'translate-x-6'
                                                    : 'translate-x-1',
                                            ]"
                                        />
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Skin Viewer Settings -->
                <div>
                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-4">Skin Viewer</h4>
                    <div class="space-y-3">
                        <!-- Walking Animation Toggle -->
                        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <label class="text-sm font-medium text-gray-900 dark:text-gray-100"
                                        >Walking Animation</label
                                    >
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Enable walking animation</p>
                                </div>
                                <button
                                    @click="updateSkinSetting('walkingAnimation', !skinSettings.walkingAnimation)"
                                    :class="[
                                        'relative inline-flex h-6 w-11 items-center rounded-full transition-colors',
                                        skinSettings.walkingAnimation
                                            ? 'bg-indigo-600'
                                            : 'bg-gray-300 dark:bg-gray-600',
                                    ]"
                                >
                                    <span
                                        :class="[
                                            'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                                            skinSettings.walkingAnimation ? 'translate-x-6' : 'translate-x-1',
                                        ]"
                                    />
                                </button>
                            </div>
                        </div>

                        <!-- Auto Rotate Toggle -->
                        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <label class="text-sm font-medium text-gray-900 dark:text-gray-100"
                                        >Auto Rotate</label
                                    >
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Automatically rotate the skin viewer
                                    </p>
                                </div>
                                <button
                                    @click="updateSkinSetting('autoRotate', !skinSettings.autoRotate)"
                                    :class="[
                                        'relative inline-flex h-6 w-11 items-center rounded-full transition-colors',
                                        skinSettings.autoRotate ? 'bg-indigo-600' : 'bg-gray-300 dark:bg-gray-600',
                                    ]"
                                >
                                    <span
                                        :class="[
                                            'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                                            skinSettings.autoRotate ? 'translate-x-6' : 'translate-x-1',
                                        ]"
                                    />
                                </button>
                            </div>
                        </div>

                        <!-- Zoom Slider -->
                        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4">
                            <label class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                                Zoom Level: {{ skinSettings.zoom.toFixed(1) }}
                            </label>
                            <input
                                type="range"
                                min="0.5"
                                max="1.5"
                                step="0.1"
                                :value="skinSettings.zoom"
                                @input="
                                    updateSkinSetting('zoom', parseFloat(($event.target as HTMLInputElement).value))
                                "
                                class="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Tab -->
            <div v-if="activeTab === 'performance'" class="space-y-6">
                <!-- Animation Performance -->
                <div>
                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-4">Animation & Motion</h4>
                    <div class="space-y-3">
                        <!-- Reduce Motion -->
                        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <label class="text-sm font-medium text-gray-900 dark:text-gray-100"
                                        >Reduce Motion</label
                                    >
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Respect system accessibility preferences
                                    </p>
                                </div>
                                <button
                                    @click="updatePerformanceSetting('reduceMotion', !performanceSettings.reduceMotion)"
                                    :class="[
                                        'relative inline-flex h-6 w-11 items-center rounded-full transition-colors',
                                        performanceSettings.reduceMotion
                                            ? 'bg-indigo-600'
                                            : 'bg-gray-300 dark:bg-gray-600',
                                    ]"
                                >
                                    <span
                                        :class="[
                                            'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                                            performanceSettings.reduceMotion ? 'translate-x-6' : 'translate-x-1',
                                        ]"
                                    />
                                </button>
                            </div>
                        </div>

                        <!-- Disable Animations -->
                        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <label class="text-sm font-medium text-gray-900 dark:text-gray-100"
                                        >Disable Animations</label
                                    >
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Turn off all UI animations for better performance
                                    </p>
                                </div>
                                <button
                                    @click="
                                        updatePerformanceSetting(
                                            'disableAnimations',
                                            !performanceSettings.disableAnimations,
                                        )
                                    "
                                    :class="[
                                        'relative inline-flex h-6 w-11 items-center rounded-full transition-colors',
                                        performanceSettings.disableAnimations
                                            ? 'bg-indigo-600'
                                            : 'bg-gray-300 dark:bg-gray-600',
                                    ]"
                                >
                                    <span
                                        :class="[
                                            'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                                            performanceSettings.disableAnimations ? 'translate-x-6' : 'translate-x-1',
                                        ]"
                                    />
                                </button>
                            </div>
                        </div>

                        <!-- Transition Speed -->
                        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4">
                            <label class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                                Transition Speed: {{ performanceSettings.transitionSpeed.toFixed(1) }}x
                            </label>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                                Adjust animation speed (lower = faster performance)
                            </p>
                            <input
                                type="range"
                                min="0.5"
                                max="5.0"
                                step="0.1"
                                :value="performanceSettings.transitionSpeed"
                                @input="
                                    updatePerformanceSetting(
                                        'transitionSpeed',
                                        parseFloat(($event.target as HTMLInputElement).value),
                                    )
                                "
                                class="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer"
                            />
                            <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mt-1">
                                <span>Fast</span>
                                <span>Normal</span>
                                <span>Slow</span>
                            </div>
                        </div>

                        <!-- Frame Rate Limit -->
                        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4">
                            <label class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">
                                Frame Rate Limit: {{ performanceSettings.frameRateLimit }} FPS
                            </label>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                                Lower frame rates save battery and CPU
                            </p>
                            <input
                                type="range"
                                min="30"
                                max="144"
                                step="15"
                                :value="performanceSettings.frameRateLimit"
                                @input="
                                    updatePerformanceSetting(
                                        'frameRateLimit',
                                        parseInt(($event.target as HTMLInputElement).value),
                                    )
                                "
                                class="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer"
                            />
                            <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mt-1">
                                <span>30</span>
                                <span>60</span>
                                <span>144</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Visual Effects -->
                <div>
                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-4">Visual Effects</h4>
                    <div class="space-y-3">
                        <!-- Background Effects -->
                        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <label class="text-sm font-medium text-gray-900 dark:text-gray-100"
                                        >Background Effects</label
                                    >
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Disable decorative backgrounds and patterns
                                    </p>
                                </div>
                                <button
                                    @click="
                                        updatePerformanceSetting(
                                            'backgroundEffects',
                                            !performanceSettings.backgroundEffects,
                                        )
                                    "
                                    :class="[
                                        'relative inline-flex h-6 w-11 items-center rounded-full transition-colors',
                                        performanceSettings.backgroundEffects
                                            ? 'bg-indigo-600'
                                            : 'bg-gray-300 dark:bg-gray-600',
                                    ]"
                                >
                                    <span
                                        :class="[
                                            'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                                            performanceSettings.backgroundEffects ? 'translate-x-6' : 'translate-x-1',
                                        ]"
                                    />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Resource Optimization -->
                <div>
                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-4">Resource Optimization</h4>
                    <div class="space-y-3">
                        <!-- Image Optimization -->
                        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <label class="text-sm font-medium text-gray-900 dark:text-gray-100"
                                        >Optimize Images</label
                                    >
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Use compressed image formats and sizes
                                    </p>
                                </div>
                                <button
                                    @click="
                                        updatePerformanceSetting('optimizeImages', !performanceSettings.optimizeImages)
                                    "
                                    :class="[
                                        'relative inline-flex h-6 w-11 items-center rounded-full transition-colors',
                                        performanceSettings.optimizeImages
                                            ? 'bg-indigo-600'
                                            : 'bg-gray-300 dark:bg-gray-600',
                                    ]"
                                >
                                    <span
                                        :class="[
                                            'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                                            performanceSettings.optimizeImages ? 'translate-x-6' : 'translate-x-1',
                                        ]"
                                    />
                                </button>
                            </div>
                        </div>

                        <!-- Lazy Loading -->
                        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <label class="text-sm font-medium text-gray-900 dark:text-gray-100"
                                        >Lazy Loading</label
                                    >
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Load content only when needed
                                    </p>
                                </div>
                                <button
                                    @click="updatePerformanceSetting('lazyLoading', !performanceSettings.lazyLoading)"
                                    :class="[
                                        'relative inline-flex h-6 w-11 items-center rounded-full transition-colors',
                                        performanceSettings.lazyLoading
                                            ? 'bg-indigo-600'
                                            : 'bg-gray-300 dark:bg-gray-600',
                                    ]"
                                >
                                    <span
                                        :class="[
                                            'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                                            performanceSettings.lazyLoading ? 'translate-x-6' : 'translate-x-1',
                                        ]"
                                    />
                                </button>
                            </div>
                        </div>

                        <!-- Preload Critical -->
                        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <label class="text-sm font-medium text-gray-900 dark:text-gray-100"
                                        >Preload Critical Resources</label
                                    >
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Load important assets in advance
                                    </p>
                                </div>
                                <button
                                    @click="
                                        updatePerformanceSetting(
                                            'preloadCritical',
                                            !performanceSettings.preloadCritical,
                                        )
                                    "
                                    :class="[
                                        'relative inline-flex h-6 w-11 items-center rounded-full transition-colors',
                                        performanceSettings.preloadCritical
                                            ? 'bg-indigo-600'
                                            : 'bg-gray-300 dark:bg-gray-600',
                                    ]"
                                >
                                    <span
                                        :class="[
                                            'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                                            performanceSettings.preloadCritical ? 'translate-x-6' : 'translate-x-1',
                                        ]"
                                    />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Memory & Network -->
                <div>
                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-4">Memory & Network</h4>
                    <div class="space-y-3">
                        <!-- Memory Optimization -->
                        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <label class="text-sm font-medium text-gray-900 dark:text-gray-100"
                                        >Memory Optimization</label
                                    >
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Aggressive memory cleanup (may cause slight delays)
                                    </p>
                                </div>
                                <button
                                    @click="
                                        updatePerformanceSetting(
                                            'memoryOptimization',
                                            !performanceSettings.memoryOptimization,
                                        )
                                    "
                                    :class="[
                                        'relative inline-flex h-6 w-11 items-center rounded-full transition-colors',
                                        performanceSettings.memoryOptimization
                                            ? 'bg-indigo-600'
                                            : 'bg-gray-300 dark:bg-gray-600',
                                    ]"
                                >
                                    <span
                                        :class="[
                                            'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                                            performanceSettings.memoryOptimization ? 'translate-x-6' : 'translate-x-1',
                                        ]"
                                    />
                                </button>
                            </div>
                        </div>

                        <!-- Network Optimization -->
                        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <label class="text-sm font-medium text-gray-900 dark:text-gray-100"
                                        >Network Optimization</label
                                    >
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Optimize requests for slower connections
                                    </p>
                                </div>
                                <button
                                    @click="
                                        updatePerformanceSetting(
                                            'networkOptimization',
                                            !performanceSettings.networkOptimization,
                                        )
                                    "
                                    :class="[
                                        'relative inline-flex h-6 w-11 items-center rounded-full transition-colors',
                                        performanceSettings.networkOptimization
                                            ? 'bg-indigo-600'
                                            : 'bg-gray-300 dark:bg-gray-600',
                                    ]"
                                >
                                    <span
                                        :class="[
                                            'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                                            performanceSettings.networkOptimization ? 'translate-x-6' : 'translate-x-1',
                                        ]"
                                    />
                                </button>
                            </div>
                        </div>

                        <!-- Enable Caching -->
                        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <label class="text-sm font-medium text-gray-900 dark:text-gray-100"
                                        >Enable Caching</label
                                    >
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Cache resources for faster loading
                                    </p>
                                </div>
                                <button
                                    @click="
                                        updatePerformanceSetting('enableCaching', !performanceSettings.enableCaching)
                                    "
                                    :class="[
                                        'relative inline-flex h-6 w-11 items-center rounded-full transition-colors',
                                        performanceSettings.enableCaching
                                            ? 'bg-indigo-600'
                                            : 'bg-gray-300 dark:bg-gray-600',
                                    ]"
                                >
                                    <span
                                        :class="[
                                            'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                                            performanceSettings.enableCaching ? 'translate-x-6' : 'translate-x-1',
                                        ]"
                                    />
                                </button>
                            </div>
                        </div>

                        <!-- Compress Assets -->
                        <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <label class="text-sm font-medium text-gray-900 dark:text-gray-100"
                                        >Compress Assets</label
                                    >
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Use compressed versions of CSS/JS files
                                    </p>
                                </div>
                                <button
                                    @click="
                                        updatePerformanceSetting('compressAssets', !performanceSettings.compressAssets)
                                    "
                                    :class="[
                                        'relative inline-flex h-6 w-11 items-center rounded-full transition-colors',
                                        performanceSettings.compressAssets
                                            ? 'bg-indigo-600'
                                            : 'bg-gray-300 dark:bg-gray-600',
                                    ]"
                                >
                                    <span
                                        :class="[
                                            'inline-block h-4 w-4 transform rounded-full bg-white transition-transform',
                                            performanceSettings.compressAssets ? 'translate-x-6' : 'translate-x-1',
                                        ]"
                                    />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Performance Info -->
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-800">
                    <div class="flex items-start gap-3">
                        <div
                            class="w-5 h-5 rounded-full bg-blue-500 flex items-center justify-center flex-shrink-0 mt-0.5"
                        >
                            <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clip-rule="evenodd"
                                ></path>
                            </svg>
                        </div>
                        <div>
                            <h5 class="text-sm font-medium text-blue-900 dark:text-blue-100 mb-1">Performance Tips</h5>
                            <p class="text-xs text-blue-700 dark:text-blue-300">
                                Enable "Reduce Motion" for accessibility compliance. Disable animations and background
                                effects for low-end devices. Memory optimization is recommended for devices with limited
                                RAM.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Animation keyframes for transition previews */
@keyframes preview-fade {
    0%,
    100% {
        opacity: 1;
    }
    50% {
        opacity: 0.3;
    }
}

@keyframes preview-slide-right {
    0%,
    100% {
        transform: translateX(0);
    }
    50% {
        transform: translateX(10px);
    }
}

@keyframes preview-slide-left {
    0%,
    100% {
        transform: translateX(0);
    }
    50% {
        transform: translateX(-10px);
    }
}

@keyframes preview-slide-up {
    0%,
    100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-10px);
    }
}

@keyframes preview-slide-down {
    0%,
    100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(10px);
    }
}

@keyframes preview-scale {
    0%,
    100% {
        transform: scale(1);
    }
    50% {
        transform: scale(0.8);
    }
}

@keyframes preview-flip {
    0%,
    100% {
        transform: rotateY(0);
    }
    50% {
        transform: rotateY(180deg);
    }
}

@keyframes preview-rotate {
    0%,
    100% {
        transform: rotate(0);
    }
    50% {
        transform: rotate(15deg);
    }
}

.preview-fade {
    animation: preview-fade 2s infinite;
}
.preview-slide-right {
    animation: preview-slide-right 2s infinite;
}
.preview-slide-left {
    animation: preview-slide-left 2s infinite;
}
.preview-slide-up {
    animation: preview-slide-up 2s infinite;
}
.preview-slide-down {
    animation: preview-slide-down 2s infinite;
}
.preview-scale {
    animation: preview-scale 2s infinite;
}
.preview-flip {
    animation: preview-flip 2s infinite;
}
.preview-rotate {
    animation: preview-rotate 2s infinite;
}
</style>
