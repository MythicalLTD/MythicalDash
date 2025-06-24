<template>
    <nav :class="navClasses" :style="backgroundStyle">
        <div class="h-full px-4 flex items-center justify-between">
            <!-- Left: Logo & Menu Button -->
            <div class="flex items-center gap-3 flex-shrink-0">
                <button
                    class="lg:hidden p-2 hover:bg-[#1a1a2e]/50 rounded-lg transition-all duration-200 hover:scale-105"
                    @click="$emit('toggle-sidebar')"
                >
                    <MenuIcon v-if="!isSidebarOpen" class="w-5 h-5" />
                    <XIcon v-else class="w-5 h-5" />
                </button>

                <div class="hidden md:flex items-center gap-2 group">
                    <div
                        class="w-8 h-8 flex items-center justify-center bg-[#1a1a2e]/30 rounded-lg transition-all duration-200 group-hover:bg-indigo-500/10 group-hover:scale-105"
                        :class="topNavSettings.borderGlow ? 'shadow-md shadow-indigo-500/20' : ''"
                    >
                        <img :src="appLogo" alt="McCloudAdmin" class="h-6 w-6" />
                    </div>
                    <span
                        class="text-xl font-bold bg-gradient-to-r from-indigo-400 to-indigo-600 bg-clip-text text-transparent transition-all duration-200 group-hover:from-indigo-300 group-hover:to-indigo-500 drop-shadow-sm"
                    >
                        {{ appName }}
                    </span>
                </div>
            </div>

            <!-- Center: Search Bar (Desktop) -->
            <div v-if="topNavSettings.showSearchBar" class="hidden lg:flex flex-1 justify-center">
                <div class="relative group w-72">
                    <SearchIcon
                        class="absolute left-3 top-2.5 h-5 w-5 text-gray-400 group-hover:text-indigo-400 transition-all duration-200"
                    />
                    <input
                        type="text"
                        :placeholder="t('components.search.placeholder')"
                        class="px-10 py-2 w-full bg-[#1a1a2e]/30 border border-[#2a2a3f]/30 rounded-lg text-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 hover:bg-[#1a1a2e]/40 backdrop-blur-sm"
                        :class="topNavSettings.borderGlow ? 'focus:shadow-lg focus:shadow-indigo-500/20' : ''"
                        @click="$emit('toggle-search')"
                        readonly
                    />
                </div>
            </div>

            <!-- Search Icon (Mobile) -->
            <button
                v-if="topNavSettings.showSearchBar"
                class="lg:hidden p-2 hover:bg-[#1a1a2e]/50 rounded-lg transition-all duration-200 hover:scale-105"
                @click="$emit('toggle-search')"
            >
                <SearchIcon class="w-5 h-5" />
            </button>

            <!-- Right: Actions -->
            <div class="flex items-center gap-2 flex-shrink-0">
                <SocialMediaLinks class="hidden lg:flex" />

                <!-- Language Selector -->
                <div class="relative group">
                    <select
                        v-model="locale"
                        @change="changeLocale"
                        class="appearance-none bg-[#1a1a2e]/30 border border-[#2a2a3f]/30 rounded-lg pl-8 pr-3 py-1.5 text-sm text-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 transition-all duration-200 cursor-pointer hover:bg-[#1a1a2e]/50 group-hover:border-indigo-500/30 backdrop-blur-sm"
                        :class="topNavSettings.borderGlow ? 'focus:shadow-lg focus:shadow-indigo-500/20' : ''"
                    >
                        <option
                            v-for="lang in availableLocales"
                            :key="lang"
                            :value="lang"
                            class="bg-[#12121f] text-gray-200"
                        >
                            {{ lang }}
                        </option>
                    </select>
                    <div
                        class="absolute left-2 top-1/2 -translate-y-1/2 pointer-events-none transition-all duration-200 group-hover:text-indigo-400"
                    >
                        <GlobeIcon class="w-4 h-4 text-gray-400" />
                    </div>
                    <div
                        class="absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none transition-all duration-200 group-hover:text-indigo-400"
                    >
                        <ChevronDownIcon class="w-4 h-4 text-gray-400" />
                    </div>
                </div>

                <button
                    @click="$emit('toggle-notifications')"
                    class="p-2 hover:bg-[#1a1a2e]/50 rounded-lg relative transition-all duration-200 hover:scale-105"
                    :class="topNavSettings.borderGlow ? 'hover:shadow-md hover:shadow-indigo-500/20' : ''"
                >
                    <BellIcon class="w-5 h-5" />
                    <span
                        class="absolute top-1.5 right-1.5 w-2 h-2 bg-indigo-500 rounded-full ring-4 ring-[#0a0a0f]/95 animate-pulse"
                        :class="topNavSettings.borderGlow ? 'shadow-sm shadow-indigo-500/50' : ''"
                    ></span>
                </button>

                <button
                    @click="$emit('toggle-profile')"
                    class="lg:hidden p-2 hover:bg-[#1a1a2e]/50 rounded-lg transition-all duration-200 hover:scale-105"
                    :class="topNavSettings.borderGlow ? 'hover:shadow-md hover:shadow-indigo-500/20' : ''"
                >
                    <UserIcon class="w-5 h-5" />
                </button>

                <button
                    @click="$emit('toggle-profile')"
                    class="hidden lg:flex items-center gap-3 px-3 py-2 hover:bg-[#1a1a2e]/50 rounded-lg transition-all duration-200 group"
                    :class="topNavSettings.borderGlow ? 'hover:shadow-md hover:shadow-indigo-500/20' : ''"
                >
                    <div class="relative">
                        <img
                            :src="Session.getInfo('avatar')"
                            alt="Profile"
                            class="w-8 h-8 rounded-lg ring-2 ring-[#2a2a3f]/30 transition-all duration-200 group-hover:ring-indigo-500/30 group-hover:scale-105"
                        />
                        <div
                            class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-green-500 rounded-full ring-2 ring-[#0a0a0f]/95 animate-pulse"
                            :class="topNavSettings.borderGlow ? 'shadow-sm shadow-green-500/50' : ''"
                        ></div>
                    </div>
                    <div class="flex flex-col items-start">
                        <span
                            class="text-sm font-medium text-gray-200 group-hover:text-gray-100 transition-colors duration-200 drop-shadow-sm"
                            >{{ Session.getInfo('username') }}</span
                        >
                        <span
                            class="text-xs text-gray-400 group-hover:text-gray-300 transition-colors duration-200 drop-shadow-sm"
                            >{{ role }}</span
                        >
                    </div>
                </button>
            </div>
        </div>
    </nav>
</template>

<script setup lang="ts">
import {
    Search as SearchIcon,
    Bell as BellIcon,
    User as UserIcon,
    Menu as MenuIcon,
    X as XIcon,
    ChevronDown as ChevronDownIcon,
    Globe as GlobeIcon,
} from 'lucide-vue-next';
import { useSettingsStore } from '@/stores/settings';
import { useI18n } from 'vue-i18n';
import { computed } from 'vue';
import Session from '@/mythicaldash/Session';
import SocialMediaLinks from './SocialMediaLinks.vue';
import { useSkinSettings } from '@/composables/useSkinSettings';

const role =
    (Session.getInfo('role_real_name') ?? '').charAt(0).toUpperCase() +
    (Session.getInfo('role_real_name') ?? '').slice(1);
const { t, locale } = useI18n();
const { topNavSettings } = useSkinSettings();

const Settings = useSettingsStore();
const availableLocales = ['EN', 'RO', 'FR', 'DE', 'ES', 'MD'];

const changeLocale = (event: Event) => {
    const target = event.target as HTMLSelectElement;
    localStorage.setItem('locale', target.value);
    window.location.href = '/';
};

defineProps<{
    isSidebarOpen: boolean;
}>();

defineEmits(['toggle-sidebar', 'toggle-search', 'toggle-notifications', 'toggle-profile']);

const appLogo = Settings.getSetting('app_logo');
const appName = Settings.getSetting('app_name');

// Computed styles based on settings
const navClasses = computed(() => {
    const baseClasses = 'fixed top-0 left-0 right-0 h-16 z-30 transition-all duration-200';
    const backgroundClasses = topNavSettings.glassEffect
        ? `bg-[#0a0a0f]/${Math.round(topNavSettings.backgroundOpacity * 100)} backdrop-blur-md`
        : 'bg-[#0a0a0f]';
    const borderClasses = topNavSettings.borderGlow
        ? 'border-b border-[#2a2a3f]/30 shadow-lg shadow-indigo-500/5'
        : 'border-b border-[#2a2a3f]/20';

    return [baseClasses, backgroundClasses, borderClasses].join(' ');
});

const backgroundStyle = computed(() => {
    if (!topNavSettings.glassEffect) return {};

    return {
        backgroundImage: `linear-gradient(135deg, 
            rgba(99, 102, 241, 0.02) 0%, 
            rgba(168, 85, 247, 0.01) 50%, 
            rgba(59, 130, 246, 0.02) 100%)`,
    };
});
</script>

<style scoped>
.bg-gradient-to-r {
    -webkit-background-clip: text;
    background-clip: text;
}

/* Smooth transitions */
.transition-all {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 200ms;
}

/* Input focus ring styling */
input:focus {
    box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.1);
}

/* Custom select styling */
select {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    min-width: 80px;
}

select::-ms-expand {
    display: none;
}

/* Hover effect for select */
select:hover {
    background-color: rgba(26, 26, 46, 0.5);
}

/* Animation for notification dot */
@keyframes pulse {
    0%,
    100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Scale effect for buttons */
.hover\:scale-105:hover {
    transform: scale(1.05);
}
</style>
