<template>
    <LayoutDashboard>
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-100 mb-2">{{ t('account.pages.index.title') }}</h1>
            <p class="text-gray-400">{{ t('account.pages.index.description') }}</p>
        </div>

        <!-- User Profile Card -->
        <LayoutAccount class="mb-8" />

        <!-- Tabs Card -->
        <div class="bg-[#0a0a15]/50 border border-[#1a1a2f]/30 rounded-xl shadow-lg overflow-hidden">
            <!-- Tabs Navigation -->
            <div class="border-b border-[#1a1a2f]/30 bg-[#050508]/70">
                <div class="flex overflow-x-auto scrollbar-hide">
                    <button
                        v-for="tab in tabs"
                        :key="tab.name"
                        class="flex items-center gap-2 px-6 py-4 text-sm font-medium whitespace-nowrap transition-all duration-200"
                        :class="[
                            activeTab === tab.name
                                ? 'text-indigo-400 border-b-2 border-indigo-500 bg-[#0a0a15]/50'
                                : 'text-gray-400 hover:text-gray-200 hover:bg-[#0a0a15]/30',
                        ]"
                        @click="activeTab = tab.name"
                    >
                        <component :is="tab.icon" class="w-4 h-4" />
                        {{ tab.name }}
                    </button>
                </div>
            </div>

            <!-- Tab Content -->
            <div class="p-6">
                <div class="tab-content">
                    <SettingsTab v-if="activeTab === t('account.pages.index.tabs.settings')" />
                    <SecurityTab v-if="activeTab === t('account.pages.index.tabs.security')" />
                    <MailsTab v-if="activeTab === t('account.pages.index.tabs.emails')" />
                    <ActivitiesTab v-if="activeTab === t('account.pages.index.tabs.activity')" />
                    <ApiKey v-if="activeTab === t('account.pages.index.tabs.apikey')" />
                    <LinkedAccounts v-if="activeTab === t('account.pages.index.tabs.linked_accounts')" />
                    <ImageHosting v-if="activeTab === t('account.pages.index.tabs.image_hosting')" />
                </div>
            </div>
        </div>
    </LayoutDashboard>
</template>

<script setup lang="ts">
import LayoutDashboard from '@/components/client/LayoutDashboard.vue';
import SettingsTab from '@/components/client/Dashboard/Account/Settings.vue';
import SecurityTab from '@/components/client/Dashboard/Account/Security.vue';
import MailsTab from '@/components/client/Dashboard/Account/Mails.vue';
import ActivitiesTab from '@/components/client/Dashboard/Account/Activities.vue';
import ApiKey from '@/components/client/Dashboard/Account/ApiKey.vue';
import LinkedAccounts from '@/components/client/Dashboard/Account/LinkedAccounts.vue';
import ImageHosting from '@/components/client/Dashboard/Account/ImageHosting.vue';
import LayoutAccount from '@/components/client/Dashboard/Account/Layout.vue';
import { MythicalDOM } from '@/mythicaldash/MythicalDOM';
import { ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import {
    Settings as SettingsIcon,
    Lock as SecurityIcon,
    Mail as MailIcon,
    Bell as ActivityIcon,
    Key as ApiKeyIcon,
    Image as ImageHostingIcon,
    Link as LinkedAccountsIcon,
} from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const route = useRoute();
const router = useRouter();

MythicalDOM.setPageTitle(t('account.pages.index.title'));

// Get available tab names for validation
const availableTabs = [
    t('account.pages.index.tabs.settings'),
    t('account.pages.index.tabs.security'),
    t('account.pages.index.tabs.emails'),
    t('account.pages.index.tabs.activity'),
    t('account.pages.index.tabs.apikey'),
    t('account.pages.index.tabs.linked_accounts'),
    t('account.pages.index.tabs.image_hosting'),
];

// Initialize active tab from URL or default to settings
const getInitialTab = () => {
    const tabFromUrl = route.query.tab as string;
    if (tabFromUrl && availableTabs.includes(tabFromUrl)) {
        return tabFromUrl;
    }
    return t('account.pages.index.tabs.settings');
};

const activeTab = ref(getInitialTab());

const tabs = [
    { name: t('account.pages.index.tabs.settings'), icon: SettingsIcon },
    { name: t('account.pages.index.tabs.security'), icon: SecurityIcon },
    { name: t('account.pages.index.tabs.emails'), icon: MailIcon },
    { name: t('account.pages.index.tabs.activity'), icon: ActivityIcon },
    { name: t('account.pages.index.tabs.apikey'), icon: ApiKeyIcon },
    { name: t('account.pages.index.tabs.linked_accounts'), icon: LinkedAccountsIcon },
    { name: t('account.pages.index.tabs.image_hosting'), icon: ImageHostingIcon },
];

// Update URL when tab changes
const updateUrl = (tabName: string) => {
    router.replace({
        query: { ...route.query, tab: tabName },
    });
};

// Watch for tab changes and update URL
watch(activeTab, (newTab) => {
    updateUrl(newTab);
});

// Watch for route changes and update active tab
watch(
    () => route.query.tab,
    (newTab) => {
        if (newTab && availableTabs.includes(newTab as string)) {
            activeTab.value = newTab as string;
        }
    },
);
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

/* Tab content animation */
.tab-content {
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(5px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
