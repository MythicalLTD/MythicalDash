<script setup lang="ts">
import { LicenseServer } from '@/mythicaldash/LicenseServer';
import { LogOutIcon } from 'lucide-vue-next';
import { onMounted, ref } from 'vue';

interface Props {
    isOpen: boolean;
    profileMenu: Array<{
        name: string;
        icon: unknown;
        href: string;
    }>;
    userInfo: {
        firstName: string;
        lastName: string;
        roleName: string;
        email: string;
        avatar: string;
    };
    stats: {
        tickets: string;
        coins: string;
        servers: string;
    };
}
withDefaults(defineProps<Props>(), {
    stats: () => ({
        tickets: '0',
        coins: '0',
        servers: '0',
    }),
});

const showAd = ref(true);

onMounted(async () => {
    try {
        const isValid = await LicenseServer.isLicenseValid();
        showAd.value = !isValid;
    } catch (error) {
        console.error('Error checking license:', error);
        showAd.value = true;
    }
});

const handleLogout = () => {
    location.href = '/auth/logout';
};
</script>
<template>
    <Transition name="dropdown">
        <div
            v-if="isOpen"
            class="absolute top-16 right-4 w-80 bg-[#0a0a0f]/95 backdrop-blur-md border border-[#2a2a3f]/30 rounded-xl shadow-2xl z-50 overflow-hidden"
            @click.stop
        >
            <!-- User Profile Section -->
            <div class="p-5 border-b border-[#2a2a3f]/30">
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <div
                            class="h-14 w-14 rounded-lg bg-gradient-to-tr from-indigo-500/20 to-blue-500/20 flex items-center justify-center ring-2 ring-indigo-500/20 overflow-hidden"
                        >
                            <img :src="userInfo.avatar" alt="User Avatar" class="h-full w-full object-cover" />
                        </div>
                        <div
                            class="absolute -bottom-1 -right-1 h-4 w-4 rounded-full bg-green-500 ring-2 ring-[#0a0a0f]"
                        ></div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="font-medium text-gray-200 truncate">
                            {{ userInfo.firstName }} {{ userInfo.lastName }}
                        </h3>
                        <p class="text-sm text-gray-400 truncate">{{ userInfo.email }}</p>
                        <div
                            class="mt-1 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-500/20 text-indigo-400"
                        >
                            {{ userInfo.roleName }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-3 gap-1 p-3 border-b border-[#2a2a3f]/30 bg-[#1a1a2e]/30">
                <div
                    class="text-center p-2 rounded-lg hover:bg-[#2a2a3f]/30 transition-colors duration-200 cursor-pointer"
                >
                    <p class="text-lg font-semibold text-indigo-400">{{ stats.tickets }}</p>
                    <p class="text-xs text-gray-400">{{ $t('components.profile.tickets') }}</p>
                </div>
                <div
                    class="text-center p-2 rounded-lg hover:bg-[#2a2a3f]/30 transition-colors duration-200 cursor-pointer"
                >
                    <p class="text-lg font-semibold text-indigo-400">{{ stats.coins }}</p>
                    <p class="text-xs text-gray-400">{{ $t('components.profile.coins') }}</p>
                </div>
                <div
                    class="text-center p-2 rounded-lg hover:bg-[#2a2a3f]/30 transition-colors duration-200 cursor-pointer"
                >
                    <p class="text-lg font-semibold text-indigo-400">{{ stats.servers }}</p>
                    <p class="text-xs text-gray-400">{{ $t('components.profile.servers') }}</p>
                </div>
            </div>

            <!-- Menu Items -->
            <div class="p-2">
                <RouterLink
                    v-for="item in profileMenu"
                    :key="item.name"
                    :to="item.href"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-[#1a1a2e]/70 transition-colors duration-200 group"
                >
                    <div
                        class="w-8 h-8 rounded-lg bg-[#1a1a2e]/50 flex items-center justify-center group-hover:bg-indigo-500/10"
                    >
                        <component
                            :is="item.icon"
                            class="h-4 w-4 text-gray-400 group-hover:text-indigo-400 transition-colors duration-200"
                        />
                    </div>
                    <span class="text-sm text-gray-300 group-hover:text-gray-100">{{ item.name }}</span>
                </RouterLink>

                <!-- Logout Button -->
                <button
                    @click="handleLogout"
                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-red-500/10 text-gray-300 hover:text-red-400 transition-colors duration-200 group mt-2"
                >
                    <div
                        class="w-8 h-8 rounded-lg bg-[#1a1a2e]/50 flex items-center justify-center group-hover:bg-red-500/10"
                    >
                        <LogOutIcon
                            class="h-4 w-4 text-gray-400 group-hover:text-red-400 transition-colors duration-200"
                        />
                    </div>
                    <span class="text-sm">{{ $t('components.profile.logout') }}</span>
                </button>
            </div>

            <!-- Footer -->
            <div v-if="showAd" class="p-3 bg-[#1a1a2e]/30 text-center text-xs text-gray-500">
                <p>
                    Made with ❤️ by
                    <a href="https://mythical.systems" target="_blank" class="text-indigo-400">MythicalSystems</a>
                </p>
            </div>
        </div>
    </Transition>
</template>
<style scoped>
.dropdown-enter-active,
.dropdown-leave-active {
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.dropdown-enter-from,
.dropdown-leave-to {
    opacity: 0;
    transform: translateY(-12px) scale(0.96);
}

.dropdown-enter-to,
.dropdown-leave-from {
    opacity: 1;
    transform: translateY(0) scale(1);
}

/* Smooth transitions */
.transition-colors {
    transition-property: background-color, border-color, color, fill, stroke;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 200ms;
}
</style>
