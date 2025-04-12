<script setup lang="ts">
import Session from '@/mythicaldash/Session';
import { computed } from 'vue';
import { CheckCircle, AlertCircle, Clock, MapPin } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
// Format date to be more readable
const formatDate = (dateString: string | null | undefined): string => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(date);
};

const firstSeen = computed(() => formatDate(Session.getInfo('first_seen')));
const lastSeen = computed(() => formatDate(Session.getInfo('last_seen')));

const isVerified = computed(() => {
    const verified = Session.getInfo('verified');
    if (typeof verified === 'boolean') {
        return verified;
    } else if (typeof verified === 'string') {
        return verified.toLowerCase() === 'true' || verified === '1';
    } else if (typeof verified === 'number') {
        return verified === 1;
    }
    return false;
});
</script>

<style scoped>
/* Hide scrollbar for Chrome, Safari and Opera */
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}

/* Hide scrollbar for IE, Edge and Firefox */
.scrollbar-hide {
    -ms-overflow-style: none; /* IE and Edge */
    scrollbar-width: none; /* Firefox */
}

.user-profile-card {
    animation: fadeIn 0.4s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Smooth transitions */
.transition-all {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 200ms;
}
</style>

<template>
    <div class="user-profile-card bg-[#0a0a15]/50 border border-[#1a1a2f]/30 rounded-xl shadow-lg overflow-hidden">
        <div class="p-6">
            <!-- User Profile Header -->
            <div class="flex flex-col md:flex-row items-start gap-6 mb-6">
                <!-- Avatar Section -->
                <div class="relative">
                    <div
                        class="h-20 w-20 rounded-lg bg-gradient-to-tr from-indigo-500/20 to-blue-500/20 flex items-center justify-center ring-2 ring-indigo-500/20 overflow-hidden"
                    >
                        <img :src="Session.getInfo('avatar')" alt="User Avatar" class="h-full w-full object-cover" />
                    </div>
                    <div
                        v-if="isVerified"
                        class="absolute -bottom-1 -right-1 h-5 w-5 rounded-full bg-green-500 ring-2 ring-[#050508] flex items-center justify-center"
                    >
                        <CheckCircle class="h-3 w-3 text-white" />
                    </div>
                </div>

                <!-- User Info -->
                <div class="flex-1 min-w-0">
                    <h2 class="text-xl font-bold text-gray-100 mb-1">
                        {{ Session.getInfo('first_name') }} {{ Session.getInfo('last_name') }}
                    </h2>
                    <p class="text-gray-400 mb-3">{{ Session.getInfo('email') }}</p>

                    <!-- Role Badge -->
                    <div
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-500/20 text-indigo-400 mb-4"
                    >
                        {{ Session.getInfo('role_name') }}
                    </div>

                    <!-- UUID -->
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xs text-gray-500">{{ t('account.components.header.uuid') }}</span>
                        <code
                            class="text-xs font-mono bg-[#050508]/70 px-2 py-0.5 rounded text-indigo-400 overflow-x-auto scrollbar-hide"
                        >
                            {{ Session.getInfo('uuid') }}
                        </code>
                    </div>
                </div>
            </div>

            <!-- User Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- IP Information -->
                <div class="bg-[#050508]/70 rounded-lg p-4 border border-[#1a1a2f]/30">
                    <div class="flex items-start gap-3">
                        <div class="p-2 rounded-lg bg-indigo-500/10">
                            <MapPin class="h-5 w-5 text-indigo-400" />
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-300 mb-1">
                                {{ t('account.components.header.ip') }}
                            </h3>
                            <div class="flex flex-col gap-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-gray-500">{{
                                        t('account.components.header.first')
                                    }}</span>
                                    <code class="text-xs font-mono text-gray-300">{{
                                        Session.getInfo('first_ip')
                                    }}</code>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-gray-500">{{
                                        t('account.components.header.current')
                                    }}</span>
                                    <code class="text-xs font-mono text-gray-300">{{
                                        Session.getInfo('last_ip')
                                    }}</code>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- First Seen -->
                <div class="bg-[#050508]/70 rounded-lg p-4 border border-[#1a1a2f]/30">
                    <div class="flex items-start gap-3">
                        <div class="p-2 rounded-lg bg-indigo-500/10">
                            <Clock class="h-5 w-5 text-indigo-400" />
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-300 mb-1">
                                {{ t('account.components.header.firstSeen') }}
                            </h3>
                            <p class="text-xs text-gray-400">{{ firstSeen }}</p>
                        </div>
                    </div>
                </div>

                <!-- Last Seen -->
                <div class="bg-[#050508]/70 rounded-lg p-4 border border-[#1a1a2f]/30">
                    <div class="flex items-start gap-3">
                        <div class="p-2 rounded-lg bg-indigo-500/10">
                            <Clock class="h-5 w-5 text-indigo-400" />
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-300 mb-1">
                                {{ t('account.components.header.lastSeen') }}
                            </h3>
                            <p class="text-xs text-gray-400">{{ lastSeen }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Verification Status -->
            <div class="mt-4 flex items-center gap-3 bg-[#050508]/70 rounded-lg p-4 border border-[#1a1a2f]/30">
                <div class="p-2 rounded-lg" :class="isVerified ? 'bg-green-500/10' : 'bg-red-500/10'">
                    <component
                        :is="isVerified ? CheckCircle : AlertCircle"
                        class="h-5 w-5"
                        :class="isVerified ? 'text-green-400' : 'text-red-400'"
                    />
                </div>
                <div>
                    <h3 class="text-sm font-medium text-gray-300">{{ t('account.components.header.verification') }}</h3>
                    <p class="text-xs" :class="isVerified ? 'text-green-400' : 'text-red-400'">
                        {{
                            isVerified
                                ? t('account.components.header.verified')
                                : t('account.components.header.notVerified')
                        }}
                    </p>
                </div>
            </div>

            <!-- Content Slot -->
            <slot></slot>
        </div>
    </div>
</template>
