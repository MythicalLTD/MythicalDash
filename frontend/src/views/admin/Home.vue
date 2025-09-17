<template>
    <LayoutDashboard>
        <div class="space-y-6">
            <!-- Welcome Header -->
            <div
                class="bg-gradient-to-r from-gray-900/70 to-gray-800/50 backdrop-blur-md rounded-xl p-6 border border-gray-800/30"
            >
                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 class="text-3xl font-bold mb-2">Welcome to {{ Settings.getSetting('debug_name') }}!</h1>
                        <p class="text-gray-400">Version: {{ Settings.getSetting('debug_version') }}</p>
                    </div>
                    <div class="mt-4 md:mt-0 flex flex-wrap gap-3">
                        <button
                            @click="refreshData"
                            class="flex items-center gap-2 px-4 py-2 bg-gray-800/50 hover:bg-gray-700/50 border border-gray-700/30 rounded-lg transition-all duration-200"
                            :class="{ 'opacity-50 cursor-wait': isRefreshing }"
                        >
                            <RefreshCcw v-if="!isRefreshing" class="w-4 h-4 text-gray-400" />
                            <Loader v-else class="w-4 h-4 text-gray-400 animate-spin" />
                            <span class="text-sm text-gray-300">Refresh</span>
                        </button>
                        <RouterLink
                            to="/mc-admin/settings"
                            class="flex items-center gap-2 px-4 py-2 bg-indigo-600/20 hover:bg-indigo-600/30 border border-indigo-700/30 rounded-lg transition-all duration-200"
                        >
                            <SettingsIcon2 class="w-4 h-4 text-indigo-400" />
                            <span class="text-sm text-gray-300">Settings</span>
                        </RouterLink>
                    </div>
                </div>

                <!-- Update warning banner -->
                <div v-if="!isUpToDate && latestRelease" class="mt-6">
                    <div class="rounded-xl p-5 border border-gray-800/40 bg-gray-800/30">
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="text-white font-semibold">
                                    New release available: {{ latestRelease.tag_name }}
                                </h3>
                                <p class="text-sm text-gray-400">
                                    Your current version is {{ Settings.getSetting('debug_version') }}
                                </p>
                            </div>
                            <a
                                :href="latestRelease.html_url"
                                target="_blank"
                                class="text-xs text-indigo-300 underline decoration-dotted hover:text-indigo-200"
                                >View on GitHub</a
                            >
                        </div>
                        <div class="mt-4">
                            <div
                                v-if="latestReleaseBodyHtml"
                                class="prose prose-invert max-w-none"
                                v-html="latestReleaseBodyHtml"
                            ></div>
                            <pre v-else class="whitespace-pre-wrap text-gray-200 text-sm">{{
                                latestRelease?.body || ''
                            }}</pre>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Dashboard Grid -->
            <div class="grid grid-cols-12 gap-6">
                <!-- Left Column -->
                <div class="col-span-12 lg:col-span-8 space-y-6">
                    <!-- Stats Overview -->
                    <div
                        class="bg-gray-800/50 rounded-xl border border-gray-800/30"
                        v-if="Session.hasPermission(Permissions.ADMIN_DASHBOARD_COMPONENTS_SYSTEM_OVERVIEW)"
                    >
                        <div class="p-4 border-b border-gray-800/30">
                            <h2 class="text-lg font-medium text-white">System Overview</h2>
                        </div>
                        <div class="p-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                <div
                                    v-for="(stat, index) in statsCards"
                                    :key="index"
                                    class="bg-gray-800/80 backdrop-blur-md rounded-xl p-5 border border-gray-800/50 hover:border-indigo-500/30 transition-all duration-300"
                                >
                                    <div class="flex items-center gap-3 mb-3">
                                        <div
                                            class="w-10 h-10 rounded-lg flex items-center justify-center"
                                            :class="stat.iconBg"
                                        >
                                            <component :is="stat.icon" class="w-5 h-5" :class="stat.iconColor" />
                                        </div>
                                        <div>
                                            <h3 class="text-sm font-medium text-gray-400">{{ stat.title }}</h3>
                                            <p class="text-xl font-bold" :class="stat.valueColor">{{ stat.value }}</p>
                                        </div>
                                    </div>
                                    <div
                                        class="mt-3 pt-3 border-t border-gray-700/20 flex items-center justify-between"
                                    >
                                        <span class="text-xs text-gray-500">{{ stat.description }}</span>
                                        <RouterLink
                                            :to="stat.link"
                                            class="text-xs text-indigo-400 hover:text-indigo-300 transition-colors flex items-center gap-1"
                                        >
                                            View All
                                            <ArrowRight class="w-3 h-3" />
                                        </RouterLink>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- At-a-Glance Analytics -->
                    <AtAGlanceAnalytics
                        v-if="Session.hasPermission(Permissions.ADMIN_DASHBOARD_COMPONENTS_ANALYTICS_VIEW)"
                    />

                    <!-- GitHub Stats -->
                    <div
                        class="bg-gray-800/50 rounded-xl border border-gray-800/30"
                        v-if="Session.hasPermission(Permissions.ADMIN_DASHBOARD_COMPONENTS_GITHUB_VIEW)"
                    >
                        <div class="p-4 border-b border-gray-800/30">
                            <h2 class="text-lg font-medium text-white">GitHub Repository</h2>
                        </div>
                        <div class="p-4">
                            <div class="flex items-center gap-4 mb-6">
                                <img
                                    :src="dashboardData.github_data?.owner?.avatar_url"
                                    :alt="dashboardData.github_data?.owner?.login"
                                    class="w-16 h-16 rounded-xl"
                                />
                                <div>
                                    <h3 class="text-xl font-semibold text-white mb-1">
                                        {{ dashboardData.github_data?.name }}
                                    </h3>
                                    <p class="text-gray-400">{{ dashboardData.github_data?.description }}</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                                <div class="bg-gray-800/80 rounded-xl p-4">
                                    <div class="text-2xl font-bold text-white mb-1">
                                        {{ dashboardData.github_data?.stargazers_count }}
                                    </div>
                                    <div class="text-sm text-gray-400">Stars</div>
                                </div>
                                <div class="bg-gray-800/80 rounded-xl p-4">
                                    <div class="text-2xl font-bold text-white mb-1">
                                        {{ dashboardData.github_data?.forks_count }}
                                    </div>
                                    <div class="text-sm text-gray-400">Forks</div>
                                </div>
                                <div class="bg-gray-800/80 rounded-xl p-4">
                                    <div class="text-2xl font-bold text-white mb-1">
                                        {{ dashboardData.github_data?.open_issues_count }}
                                    </div>
                                    <div class="text-sm text-gray-400">Open Issues</div>
                                </div>
                            </div>
                            <a
                                :href="dashboardData.github_data?.html_url"
                                target="_blank"
                                class="flex items-center justify-center gap-2 w-full mt-6 py-3 bg-gray-700/50 hover:bg-gray-700/70 rounded-xl transition-colors duration-200"
                            >
                                <Github class="w-5 h-5" />
                                <span>View on GitHub</span>
                            </a>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div
                        class="bg-gray-800/50 rounded-xl border border-gray-800/30"
                        v-if="Session.hasPermission(Permissions.ADMIN_DASHBOARD_COMPONENTS_ACTIVITY_VIEW)"
                    >
                        <div class="p-4 border-b border-gray-800/30">
                            <h2 class="text-lg font-medium text-white">Recent Activity</h2>
                        </div>
                        <div class="p-4">
                            <div class="space-y-3">
                                <div
                                    v-for="activity in displayedActivities"
                                    :key="activity.id"
                                    class="bg-gray-800/80 rounded-xl p-4 hover:bg-gray-700/50 transition-colors duration-200"
                                >
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <p class="text-sm font-medium text-white">{{ activity.action }}</p>
                                            <p class="text-xs text-gray-400">{{ activity.context }}</p>
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ new Date(activity.date).toLocaleString() }}
                                        </div>
                                    </div>
                                    <div class="mt-2 flex items-center gap-2">
                                        <span
                                            class="text-xs px-2 py-1 rounded-full"
                                            :class="{
                                                'bg-blue-500/20 text-blue-400': activity.action.includes('settings'),
                                                'bg-green-500/20 text-green-400': activity.action.includes('login'),
                                                'bg-amber-500/20 text-amber-400': activity.action.includes('update'),
                                            }"
                                        >
                                            {{ activity.action.split(':')[0] }}
                                        </span>
                                        <span class="text-xs text-gray-500">{{ activity.ip_address }}</span>
                                    </div>
                                </div>
                            </div>
                            <div v-if="hasMoreActivities" class="mt-4 flex justify-center">
                                <button
                                    @click="loadMoreActivities"
                                    class="px-4 py-2 bg-gray-700/50 hover:bg-gray-700/70 rounded-lg text-sm text-gray-300 transition-colors duration-200"
                                >
                                    Load More
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-span-12 lg:col-span-4 space-y-6">
                    <!-- System Updates -->
                    <div
                        class="bg-gray-800/50 rounded-xl border border-gray-800/30"
                        v-if="Session.hasPermission(Permissions.ADMIN_DASHBOARD_COMPONENTS_SYSTEM_UPDATES)"
                    >
                        <div class="p-4 border-b border-gray-800/30">
                            <h2 class="text-lg font-medium text-white">System Updates</h2>
                        </div>
                        <div class="p-4">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <p class="text-gray-300 font-medium">Current Version</p>
                                    <p class="text-green-400 font-semibold">
                                        {{ Settings.getSetting('debug_version') }}
                                    </p>
                                </div>
                                <div
                                    class="rounded-full px-3 py-1 text-xs"
                                    :class="{
                                        'bg-green-500/10 text-green-400': isUpToDate,
                                        'bg-amber-500/10 text-amber-400': !isUpToDate,
                                    }"
                                >
                                    {{ isUpToDate ? 'Up to date' : 'Update available' }}
                                </div>
                            </div>
                            <p v-if="isUpToDate" class="text-gray-400 text-sm mb-4">
                                Your system is running the latest version. The last update check was performed
                                {{ lastCheckTime }}.
                            </p>
                            <div v-else class="text-amber-400 text-sm mb-4">
                                <p class="mb-2">
                                    A new version ({{ latestVersion }}) is available. Please update your system.
                                </p>
                                <a
                                    v-if="latestRelease"
                                    :href="latestRelease.html_url"
                                    target="_blank"
                                    class="inline-flex items-center gap-2 text-amber-200 underline decoration-dotted hover:text-amber-100"
                                >
                                    <Github class="w-4 h-4" />
                                    View release
                                </a>
                            </div>
                            <button
                                @click="checkForUpdates"
                                :disabled="isChecking"
                                class="w-full py-2.5 bg-gray-700/50 hover:bg-gray-700/70 border border-gray-700/30 rounded-lg transition-all duration-200 text-sm text-gray-300 flex items-center justify-center gap-2"
                            >
                                <Loader v-if="isChecking" class="w-4 h-4 animate-spin" />
                                <RefreshCcw v-else class="w-4 h-4" />
                                {{ isChecking ? 'Checking...' : 'Check for Updates' }}
                            </button>
                        </div>
                    </div>

                    <!-- Cron Jobs Status -->
                    <div class="bg-gray-800/50 rounded-xl border border-gray-800/30">
                        <div class="p-4 border-b border-gray-800/30">
                            <h2 class="text-lg font-medium text-white">Automations Status</h2>
                        </div>
                        <div class="p-4">
                            <div v-if="cronData?.summary" class="text-sm text-gray-400 mb-4">
                                {{ cronData.summary }}
                            </div>
                            <div v-else class="space-y-3">
                                <div
                                    v-for="cron in cronData?.recent?.slice(0, 12)"
                                    :key="cron.id"
                                    class="flex items-center justify-between p-3 bg-gray-700/30 rounded-lg border border-gray-600/30"
                                >
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-2 h-2 rounded-full"
                                            :class="{
                                                'bg-green-500': !cron.late && cron.last_run_success,
                                                'bg-yellow-500': cron.late && cron.last_run_success,
                                                'bg-red-500': !cron.last_run_success,
                                            }"
                                        ></div>
                                        <div>
                                            <h3 class="text-sm font-medium text-gray-200">
                                                {{
                                                    cron.task_name
                                                        .replace(/-/g, ' ')
                                                        .replace(/\b\w/g, (l) => l.toUpperCase())
                                                }}
                                            </h3>
                                            <p class="text-xs text-gray-400">
                                                {{ formatTimeAgo(cron.last_run_at) }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span
                                            class="text-xs px-2 py-1 rounded-full"
                                            :class="{
                                                'bg-green-500/20 text-green-400': !cron.late && cron.last_run_success,
                                                'bg-yellow-500/20 text-yellow-400': cron.late && cron.last_run_success,
                                                'bg-red-500/20 text-red-400': !cron.last_run_success,
                                            }"
                                        >
                                            {{ cron.late ? 'Late' : cron.last_run_success ? 'OK' : 'Failed' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4">
                                <RouterLink
                                    to="/mc-admin/health"
                                    class="flex items-center justify-center gap-2 w-full py-2 bg-gray-700/50 hover:bg-gray-700/70 rounded-lg text-sm text-gray-300 transition-colors duration-200"
                                >
                                    <span>View All Cron Jobs</span>
                                    <ArrowRight class="w-4 h-4" />
                                </RouterLink>
                            </div>
                        </div>
                    </div>

                    <!-- Support & Resources -->
                    <div
                        class="bg-gray-800/50 rounded-xl border border-gray-800/30"
                        v-if="Session.hasPermission(Permissions.ADMIN_DASHBOARD_COMPONENTS_SUPPORT_VIEW)"
                    >
                        <div class="p-4 border-b border-gray-800/30">
                            <h2 class="text-lg font-medium text-white">Support & Resources</h2>
                        </div>
                        <div class="p-4">
                            <div class="space-y-2">
                                <a
                                    v-for="(resource, index) in supportResources"
                                    :key="index"
                                    :href="resource.link"
                                    target="_blank"
                                    class="flex items-center justify-between p-3 hover:bg-gray-700/20 transition-colors duration-200 rounded-lg"
                                >
                                    <div class="flex items-center">
                                        <component
                                            :is="resource.icon"
                                            class="w-5 h-5 mr-3"
                                            :class="resource.iconColor"
                                        />
                                        <span class="text-sm text-gray-300">{{ resource.title }}</span>
                                    </div>
                                    <ExternalLink class="w-4 h-4 text-gray-500" />
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- System Logs -->
                    <div
                        class="bg-gray-800/50 rounded-xl border border-gray-800/30"
                        v-if="Session.hasPermission(Permissions.ADMIN_DASHBOARD_COMPONENTS_SYSTEM_LOGS)"
                    >
                        <div class="p-4 border-b border-gray-800/30">
                            <h2 class="text-lg font-medium text-white">System Logs</h2>
                        </div>
                        <div class="p-4">
                            <div class="space-y-2 max-h-[300px] overflow-y-auto">
                                <div
                                    v-for="(log, index) in dashboardData.logs"
                                    :key="index"
                                    class="text-sm p-2 rounded-lg"
                                    :class="{
                                        'bg-red-500/10 text-red-400': log.includes('[ERROR]'),
                                        'bg-yellow-500/10 text-yellow-400': log.includes('[WARNING]'),
                                        'bg-green-500/10 text-green-400': log.includes('[INFO]'),
                                    }"
                                >
                                    {{ log }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Modal -->
        <div v-if="showActivityModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-900/80 backdrop-blur-sm"></div>
                </div>

                <div
                    class="inline-block align-bottom bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full"
                >
                    <div class="bg-gray-800 px-4 pt-5 pb-4 sm:p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-white">Activity Log</h3>
                            <button @click="showActivityModal = false" class="text-gray-400 hover:text-gray-300">
                                <X class="w-5 h-5" />
                            </button>
                        </div>
                        <!-- Activity List -->
                        <div class="space-y-3 max-h-[60vh] overflow-y-auto">
                            <div
                                v-for="activity in displayedActivities"
                                :key="activity.id"
                                class="bg-gray-700/30 rounded-lg p-4 hover:bg-gray-700/50 transition-colors duration-200"
                            >
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-white">{{ activity.action }}</p>
                                        <p class="text-xs text-gray-400">{{ activity.context }}</p>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ new Date(activity.date).toLocaleString() }}
                                    </div>
                                </div>
                                <div class="mt-2 flex items-center gap-2">
                                    <span
                                        class="text-xs px-2 py-1 rounded-full"
                                        :class="{
                                            'bg-blue-500/20 text-blue-400': activity.action.includes('settings'),
                                            'bg-green-500/20 text-green-400': activity.action.includes('login'),
                                            'bg-amber-500/20 text-amber-400': activity.action.includes('update'),
                                        }"
                                    >
                                        {{ activity.action.split(':')[0] }}
                                    </span>
                                    <span class="text-xs text-gray-500">{{ activity.ip_address }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import {
    Heart,
    Users,
    Ticket,
    Server,
    RefreshCcw,
    Loader,
    ArrowRight,
    ExternalLink,
    BookOpen,
    MessageCircle,
    Github,
    Sparkles,
    Settings as SettingsIcon2,
    Clock,
    X,
    CheckCircle,
    AlertTriangle,
    XCircle,
} from 'lucide-vue-next';
import LayoutDashboard from '@/components/admin/LayoutDashboard.vue';
import { useSettingsStore } from '@/stores/settings';
import Dashboard from '@/mythicaldash/admin/Dashboard';
import { RouterLink } from 'vue-router';
import AtAGlanceAnalytics from '@/components/admin/Analytics/AtAGlanceAnalytics.vue';
import Session from '@/mythicaldash/Session';
import Permissions from '@/mythicaldash/Permissions';
import { useHealthStore } from '@/stores/health';
import type { CronData } from '@/types/health';

const Settings = useSettingsStore();
const healthStore = useHealthStore();
const isRefreshing = ref(false);
const cronData = ref<CronData | null>(null);

// Add interfaces for GitHub data and activity
interface GitHubOwner {
    login: string;
    avatar_url: string;
}

interface GitHubData {
    name: string;
    description: string;
    owner: GitHubOwner;
    stargazers_count: number;
    forks_count: number;
    open_issues_count: number;
    html_url: string;
}

interface Activity {
    id: number;
    user: string;
    action: string;
    ip_address: string;
    date: string;
    context: string;
}

// Releases types
interface GitHubReleaseAsset {
    browser_download_url: string;
}
interface GitHubRelease {
    id: number;
    tag_name: string;
    name: string;
    body: string;
    body_html?: string;
    html_url: string;
    draft: boolean;
    prerelease: boolean;
    published_at: string | null;
    assets?: GitHubReleaseAsset[];
}

const dashboardData = ref({
    counts: {
        user_count: 0,
        locations_count: 0,
        tickets_count: 0,
        eggs_count: 0,
        departments_count: 0,
        announcements_count: 0,
        server_queue_count: 0,
        mail_templates_count: 0,
        settings_count: 0,
        addons_count: 0,
        roles_count: 0,
        plugins_count: 0,
        servers_count: 0,
        redeem_codes_count: 0,
    },
    github_data: null as GitHubData | null,
    github_release: [] as GitHubRelease[],
    activity: [] as Activity[],
    logs: [] as string[],
});

// Activity display
const activitiesPerLoad = ref(10);
const displayedActivities = ref<Activity[]>([]);

// Computed property to check if there are more activities to load
const hasMoreActivities = computed(() => {
    return displayedActivities.value.length < dashboardData.value.activity.length;
});

// Method to load more activities
const loadMoreActivities = () => {
    const currentLength = displayedActivities.value.length;
    const newActivities = dashboardData.value.activity.slice(currentLength, currentLength + activitiesPerLoad.value);
    displayedActivities.value = [...displayedActivities.value, ...newActivities];
};

// Version check
const isUpToDate = ref(true);
const isChecking = ref(false);
const latestVersion = ref('');
const lastCheckTime = ref('never');

// Latest release refs
const latestRelease = ref<GitHubRelease | null>(null);
const latestReleaseBodyHtml = ref('');

// No markdown parsing: we prefer GitHub-rendered HTML or plain text fallback
const formatTimeAgo = (dateString: string | null): string => {
    if (!dateString) return 'Never';
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now.getTime() - date.getTime();
    const diffSeconds = Math.floor(diffMs / 1000);
    const diffMinutes = Math.floor(diffSeconds / 60);
    const diffHours = Math.floor(diffMinutes / 60);
    const diffDays = Math.floor(diffHours / 24);

    if (diffSeconds < 60) return `${diffSeconds}s ago`;
    if (diffMinutes < 60) return `${diffMinutes}m ago`;
    if (diffHours < 24) return `${diffHours}h ago`;
    return `${diffDays}d ago`;
};

// Format interval to human readable
const formatInterval = (seconds: number): string => {
    if (seconds < 60) return `${seconds}s`;
    if (seconds < 3600) return `${Math.floor(seconds / 60)}m`;
    if (seconds < 86400) return `${Math.floor(seconds / 3600)}h`;
    return `${Math.floor(seconds / 86400)}d`;
};

const computeUpdateStateFromReleases = () => {
    const currentVersion = Settings.getSetting('debug_version');
    const releases = dashboardData.value.github_release || [];
    const stableReleases = releases.filter((r) => !r.draft && !r.prerelease);
    const top = stableReleases[0] || releases[0] || null;
    latestRelease.value = top || null;
    latestVersion.value = top?.tag_name || '';
    isUpToDate.value = currentVersion === latestVersion.value || latestVersion.value === '';
    latestReleaseBodyHtml.value = top?.body_html || '';
    lastCheckTime.value = 'just now';
};

const checkForUpdates = async () => {
    // Use backend-provided releases; no direct GitHub call here
    isChecking.value = true;
    try {
        computeUpdateStateFromReleases();
    } catch (error) {
        console.error('Failed to compute update state:', error);
    } finally {
        isChecking.value = false;
    }
};

// Check for updates on mount
onMounted(async () => {
    try {
        const data = await Dashboard.get();
        dashboardData.value = {
            counts: data.count || dashboardData.value.counts,
            github_data: data.core?.github_data || null,
            github_release: data.core?.github_release || [],
            activity: data.etc?.activity || [],
            logs: data.core?.logs || [],
        };
        // Initialize displayed activities
        displayedActivities.value = dashboardData.value.activity.slice(0, activitiesPerLoad.value);

        // Load health data including cron jobs
        await healthStore.fetchHealthData();
        cronData.value = healthStore.cronData;

        // Check for updates
        await checkForUpdates();
    } catch (error) {
        console.error('Failed to load dashboard data:', error);
    }
});

// Refresh data
const refreshData = async () => {
    isRefreshing.value = true;
    try {
        const data = await Dashboard.get();
        dashboardData.value = {
            counts: data.count || dashboardData.value.counts,
            github_data: data.core?.github_data || null,
            github_release: data.core?.github_release || [],
            activity: data.etc?.activity || [],
            logs: data.core?.logs || [],
        };
        // Reset displayed activities
        displayedActivities.value = dashboardData.value.activity.slice(0, activitiesPerLoad.value);

        // Refresh health data including cron jobs
        await healthStore.fetchHealthData();
        cronData.value = healthStore.cronData;

        computeUpdateStateFromReleases();
    } catch (error) {
        console.error('Failed to refresh dashboard data:', error);
    } finally {
        isRefreshing.value = false;
    }
};

// Stats cards
const statsCards = computed(() => [
    {
        title: 'Total Users',
        value: dashboardData.value.counts.user_count,
        icon: Users,
        iconBg: 'bg-blue-500/20',
        iconColor: 'text-blue-400',
        valueColor: 'text-blue-400',
        description: 'Active users on the platform',
        link: '/mc-admin/users',
    },
    {
        title: 'Open Tickets',
        value: dashboardData.value.counts.tickets_count,
        icon: Ticket,
        iconBg: 'bg-amber-500/20',
        iconColor: 'text-amber-400',
        valueColor: 'text-amber-400',
        description: 'Tickets requiring attention',
        link: '/mc-admin/tickets',
    },
    {
        title: 'Servers',
        value: dashboardData.value.counts.servers_count,
        icon: Server,
        iconBg: 'bg-emerald-500/20',
        iconColor: 'text-emerald-400',
        valueColor: 'text-emerald-400',
        description: 'Active servers',
        link: '/mc-admin/servers',
    },
    {
        title: 'Queue',
        value: dashboardData.value.counts.server_queue_count,
        icon: Clock,
        iconBg: 'bg-purple-500/20',
        iconColor: 'text-purple-400',
        valueColor: 'text-purple-400',
        description: 'Servers in queue',
        link: '/mc-admin/server-queue',
    },
]);

// Support resources
const supportResources = [
    {
        title: 'Documentation',
        icon: BookOpen,
        iconColor: 'text-indigo-400',
        link: 'https://www.mythical.systems/docs',
    },
    {
        title: 'Discord Community',
        icon: MessageCircle,
        iconColor: 'text-violet-400',
        link: 'https://discord.mythical.systems',
    },
    {
        title: 'GitHub Repository',
        icon: Github,
        iconColor: 'text-gray-400',
        link: 'https://github.com/mythicalltd',
    },
    {
        title: 'Support Team',
        icon: Heart,
        iconColor: 'text-pink-400',
        link: 'https://discord.mythical.systems',
    },
];

// Add new refs for modal
const showActivityModal = ref(false);
</script>

<style scoped>
@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

.animate-spin {
    animation: spin 1s linear infinite;
}

.bg-gradient-to-r {
    background-image: linear-gradient(to right, var(--tw-gradient-stops));
}
</style>
