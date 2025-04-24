<template>
    <nav class="fixed top-0 left-0 right-0 h-16 bg-[#0a0a0f]/95 backdrop-blur-md border-b border-[#2a2a3f]/30 z-30">
        <div class="h-full px-4 flex items-center justify-between">
            <!-- Left: Logo & Menu Button -->
            <div class="flex items-center gap-3">
                <button
                    class="lg:hidden p-2 hover:bg-[#1a1a2e]/50 rounded-lg transition-colors duration-200"
                    @click="$emit('toggle-sidebar')"
                >
                    <MenuIcon v-if="!isSidebarOpen" class="w-5 h-5" />
                    <XIcon v-else class="w-5 h-5" />
                </button>

                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 flex items-center justify-center bg-[#1a1a2e]/30 rounded-lg">
                        <img :src="appLogo" alt="MythicalDash" class="h-6 w-6" />
                    </div>
                    <span
                        class="text-xl font-bold bg-gradient-to-r from-indigo-400 to-indigo-600 bg-clip-text text-transparent"
                    >
                        {{ appName }}
                    </span>
                </div>
            </div>

            <!-- Search Bar (Desktop) -->
            <div class="hidden lg:block absolute left-1/2 transform -translate-x-1/2">
                <div class="relative group">
                    <SearchIcon
                        class="absolute left-3 top-2.5 h-5 w-5 text-gray-400 group-hover:text-indigo-400 transition-colors duration-200"
                    />
                    <input
                        type="text"
                        :placeholder="t('components.search.placeholder')"
                        class="px-10 py-2 w-72 bg-[#1a1a2e]/30 border border-[#2a2a3f]/30 rounded-lg text-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50"
                        @click="$emit('toggle-search')"
                        readonly
                    />
                </div>
            </div>

            <!-- Search Icon (Mobile) -->
            <button
                class="lg:hidden p-2 hover:bg-[#1a1a2e]/50 rounded-lg transition-colors duration-200"
                @click="$emit('toggle-search')"
            >
                <SearchIcon class="w-5 h-5" />
            </button>

            <!-- Right: Actions -->
            <div class="flex items-center gap-2">
                <SocialMediaLinks class="hidden lg:flex" />

                <!-- Language Selector -->
                <div class="relative">
                    <select
                        v-model="locale"
                        @change="changeLocale"
                        class="appearance-none bg-[#1a1a2e]/30 border border-[#2a2a3f]/30 rounded-lg pl-8 pr-3 py-1.5 text-sm text-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 transition-all duration-200 cursor-pointer hover:bg-[#1a1a2e]/50"
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
                    <div class="absolute left-2 top-1/2 -translate-y-1/2 pointer-events-none">
                        <GlobeIcon class="w-4 h-4 text-gray-400" />
                    </div>
                    <div class="absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none">
                        <ChevronDownIcon class="w-4 h-4 text-gray-400" />
                    </div>
                </div>

                <button
                    @click="$emit('toggle-notifications')"
                    class="p-2 hover:bg-[#1a1a2e]/50 rounded-lg relative transition-colors duration-200"
                >
                    <BellIcon class="w-5 h-5" />
                    <span
                        class="absolute top-1.5 right-1.5 w-2 h-2 bg-indigo-500 rounded-full ring-4 ring-[#0a0a0f]/95"
                    ></span>
                </button>

                <button
                    @click="$emit('toggle-profile')"
                    class="lg:hidden p-2 hover:bg-[#1a1a2e]/50 rounded-lg transition-colors duration-200"
                >
                    <UserIcon class="w-5 h-5" />
                </button>

                <button
                    @click="$emit('toggle-profile')"
                    class="hidden lg:flex items-center gap-3 px-3 py-2 hover:bg-[#1a1a2e]/50 rounded-lg transition-colors duration-200"
                >
                    <div class="relative">
                        <img
                            :src="Session.getInfo('avatar')"
                            alt="Profile"
                            class="w-8 h-8 rounded-lg ring-2 ring-[#2a2a3f]/30"
                        />
                        <div
                            class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-green-500 rounded-full ring-2 ring-[#0a0a0f]/95"
                        ></div>
                    </div>
                    <div class="flex flex-col items-start">
                        <span class="text-sm font-medium text-gray-200">{{ Session.getInfo('username') }}</span>
                        <span class="text-xs text-gray-400">{{ role }}</span>
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
const Settings = useSettingsStore();
import { useI18n } from 'vue-i18n';
import Session from '@/mythicaldash/Session';
import SocialMediaLinks from './SocialMediaLinks.vue';

const role =
    (Session.getInfo('role_real_name') ?? '').charAt(0).toUpperCase() +
    (Session.getInfo('role_real_name') ?? '').slice(1);
const { t, locale } = useI18n();

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
</script>

<style scoped>
.bg-gradient-to-r {
    -webkit-background-clip: text;
    background-clip: text;
}

/* Smooth hover transitions */
.transition-colors {
    transition-property: background-color, border-color, color, fill, stroke;
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
</style>
