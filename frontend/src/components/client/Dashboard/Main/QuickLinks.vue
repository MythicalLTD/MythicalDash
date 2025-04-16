<template>
    <CardComponent
        :cardTitle="t('Components.QuickLinks.title')"
        :cardDescription="t('Components.QuickLinks.description')"
    >
        <div class="quicklinks-container relative overflow-hidden">
            <!-- Background decoration -->
            <div class="absolute -top-20 -left-20 w-40 h-40 bg-indigo-500/5 rounded-full blur-2xl"></div>
            <div class="absolute -bottom-20 -right-20 w-40 h-40 bg-blue-500/5 rounded-full blur-2xl"></div>

            <div class="space-y-3 relative z-10">
                <div
                    v-for="(link, index) in quickLinks"
                    :key="link.name"
                    @click="navigateTo(link.href)"
                    class="quick-link flex items-center py-2.5 px-3.5 rounded-lg cursor-pointer hover:bg-gray-800/50 transition-all duration-200 backdrop-blur-sm border border-gray-700/20 hover:border-indigo-500/30"
                    :style="{ animationDelay: `${index * 0.1}s` }"
                >
                    <div
                        class="icon-container w-10 h-10 rounded-lg bg-gradient-to-br from-indigo-600/20 to-blue-600/20 flex items-center justify-center mr-3 relative overflow-hidden"
                    >
                        <component :is="link.icon" class="w-5 h-5 text-indigo-400 relative z-10" />
                        <div class="icon-glow"></div>
                    </div>
                    <div class="flex-1">
                        <h3
                            class="text-sm font-medium text-white group-hover:text-indigo-400 transition-colors duration-200"
                        >
                            {{ link.name }}
                        </h3>
                        <p class="text-xs text-gray-400">{{ link.description }}</p>
                    </div>
                    <div class="arrow-container">
                        <ChevronRightIcon class="w-4 h-4 text-gray-400 arrow-icon" />
                    </div>
                </div>
            </div>
        </div>
    </CardComponent>
</template>

<script setup lang="ts">
import CardComponent from '@/components/client/ui/Card/CardComponent.vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';
import {
    Ticket as TicketIcon,
    AArrowDown as AnnouncementIcon,
    Settings as SettingsIcon,
    ChevronRight as ChevronRightIcon,
} from 'lucide-vue-next';
import { useSettingsStore } from '@/stores/settings';
import { computed } from 'vue';

const Settings = useSettingsStore();

const router = useRouter();
const { t } = useI18n();

const isTicketsEnabled = computed(() => {
    return Settings.getSetting('allow_tickets') === 'true';
});

const quickLinks = [
    {
        name: t('Components.QuickLinks.announcements.title'),
        description: t('Components.QuickLinks.announcements.description'),
        href: '/announcements',
        icon: AnnouncementIcon,
    },
    ...(isTicketsEnabled.value
        ? [
              {
                  name: t('Components.QuickLinks.tickets.title'),
                  description: t('Components.QuickLinks.tickets.description'),
                  href: '/ticket',
                  icon: TicketIcon,
              },
          ]
        : []),
    {
        name: t('Components.QuickLinks.account.title'),
        description: t('Components.QuickLinks.account.description'),
        href: '/account',
        icon: SettingsIcon,
    },
];

const navigateTo = (path: string) => {
    router.push(path);
};
</script>

<style scoped>
.quicklinks-container {
    position: relative;
}

.quick-link {
    animation: fadeInUp 0.5s ease forwards;
    opacity: 0;
    transform: translateY(10px);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
}

.quick-link:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.icon-container {
    position: relative;
    transition: all 0.3s ease;
}

.quick-link:hover .icon-container {
    transform: scale(1.05);
    box-shadow: 0 0 15px rgba(79, 70, 229, 0.3);
}

.icon-glow {
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at center, rgba(99, 102, 241, 0.3) 0%, transparent 70%);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.quick-link:hover .icon-glow {
    opacity: 1;
    animation: pulse 2s infinite;
}

.arrow-container {
    transition: transform 0.2s ease;
}

.quick-link:hover .arrow-container {
    transform: translateX(3px);
}

.arrow-icon {
    transition: all 0.2s ease;
}

.quick-link:hover .arrow-icon {
    color: #818cf8;
}

@keyframes fadeInUp {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes pulse {
    0%,
    100% {
        opacity: 0.5;
    }
    50% {
        opacity: 0.8;
    }
}
</style>
