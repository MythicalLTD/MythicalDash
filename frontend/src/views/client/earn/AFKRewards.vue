<template>
    <LayoutDashboard>
        <div class="p-6">
            <h1 class="text-2xl font-bold text-white mb-6">{{ t('afk.pages.index.title') }}</h1>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main AFK Timer Card -->
                <div class="lg:col-span-2">
                    <CardComponent cardTitle="AFK Timer" cardDescription="Earn coins by staying AFK">
                        <div class="relative overflow-hidden">
                            <!-- Background decorative elements -->
                            <div
                                class="absolute -top-20 -right-20 w-40 h-40 bg-indigo-500/5 rounded-full blur-2xl"
                            ></div>
                            <div
                                class="absolute -bottom-20 -left-20 w-40 h-40 bg-purple-500/5 rounded-full blur-2xl"
                            ></div>

                            <div class="relative z-10">
                                <!-- AFK Status -->
                                <div class="flex items-center justify-between mb-6">
                                    <div class="flex items-center">
                                        <div
                                            class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-600"
                                        >
                                            <Clock class="h-4 w-4 text-white" />
                                        </div>
                                        <span class="ml-3 text-lg font-medium text-white"
                                            >{{ t('afk.pages.index.status') }}
                                            <span :class="isActive ? 'text-emerald-400' : 'text-gray-400'">
                                                {{
                                                    isActive
                                                        ? t('afk.pages.index.active')
                                                        : t('afk.pages.index.inactive')
                                                }}
                                            </span>
                                        </span>
                                    </div>
                                    <button
                                        @click="toggleAFK"
                                        :class="
                                            isActive
                                                ? 'bg-red-600 hover:bg-red-700'
                                                : 'bg-emerald-600 hover:bg-emerald-700'
                                        "
                                        class="px-4 py-2 text-white text-sm font-medium rounded-lg transition-colors"
                                    >
                                        {{ isActive ? t('afk.pages.index.stopafk') : t('afk.pages.index.startafk') }}
                                    </button>
                                </div>

                                <!-- Large Timer Display -->
                                <div class="bg-gray-800/50 rounded-xl p-8 text-center mb-6">
                                    <div class="grid grid-cols-3 gap-4">
                                        <div class="timer-unit">
                                            <div class="text-5xl font-bold text-white">{{ formatTime.hours }}</div>
                                            <div class="text-xs text-gray-400 uppercase tracking-wide mt-2">
                                                {{ t('afk.pages.index.timeDate.hours') }}
                                            </div>
                                        </div>
                                        <div class="timer-unit">
                                            <div class="text-5xl font-bold text-white">{{ formatTime.minutes }}</div>
                                            <div class="text-xs text-gray-400 uppercase tracking-wide mt-2">
                                                {{ t('afk.pages.index.timeDate.minutes') }}
                                            </div>
                                        </div>
                                        <div class="timer-unit">
                                            <div class="text-5xl font-bold text-white">{{ formatTime.seconds }}</div>
                                            <div class="text-xs text-gray-400 uppercase tracking-wide mt-2">
                                                {{ t('afk.pages.index.timeDate.seconds') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Coin Counter -->
                                <div class="bg-gray-800/30 rounded-xl p-6 mb-6">
                                    <div class="flex justify-between items-center">
                                        <div class="flex items-center">
                                            <div
                                                class="w-10 h-10 rounded-full bg-yellow-500/20 flex items-center justify-center mr-3"
                                            >
                                                <Coins class="h-5 w-5 text-yellow-500" />
                                            </div>
                                            <div>
                                                <div class="text-sm text-gray-400">
                                                    {{ t('afk.pages.index.totalCoins') }}
                                                </div>
                                                <div class="text-2xl font-bold text-yellow-500">{{ totalCoins }}</div>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-sm text-gray-400 text-right">
                                                {{ t('afk.pages.index.currentSession') }}
                                            </div>
                                            <div class="text-xl font-medium text-yellow-400">
                                                +{{ sessionCoins }} {{ t('afk.pages.index.coins') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- AFK Stats -->
                                <div class="grid grid-cols-2 gap-4 mb-6">
                                    <div class="bg-gray-800/30 rounded-lg p-4">
                                        <div class="text-sm text-gray-400 mb-1">
                                            {{ t('afk.pages.index.currentSession') }}
                                        </div>
                                        <div class="text-lg font-semibold text-white">
                                            {{ formatTimeString(currentSessionTime) }}
                                        </div>
                                    </div>
                                    <div class="bg-gray-800/30 rounded-lg p-4">
                                        <div class="text-sm text-gray-400 mb-1">
                                            {{ t('afk.pages.index.totalAFKTime') }}
                                        </div>
                                        <div class="text-lg font-semibold text-white">
                                            {{ formatTimeString(totalAFKTime) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardComponent>

                    <!-- Bottom Ad Banner -->
                    <div class="mt-6 p-3 bg-gray-800/30 border border-gray-700/30 rounded-lg">
                        <div class="flex justify-between items-center">
                            <div class="text-xs text-gray-500">Advertisement</div>
                            <button class="text-xs text-gray-500 hover:text-gray-400">×</button>
                        </div>
                        <div class="h-20 flex items-center justify-center text-gray-600 text-sm">
                            [Horizontal Banner Ad Placement]
                        </div>
                    </div>
                </div>

                <!-- Sidebar Ads -->
                <div class="space-y-6">
                    <!-- Ad Square 1 -->
                    <div class="p-3 bg-gray-800/30 border border-gray-700/30 rounded-lg">
                        <div class="flex justify-between items-center">
                            <div class="text-xs text-gray-500">Advertisement</div>
                            <button class="text-xs text-gray-500 hover:text-gray-400">×</button>
                        </div>
                        <div class="h-80 flex items-center justify-center text-gray-600 text-sm">
                            [Square Ad Placement]
                        </div>
                    </div>

                    <!-- Ad Square 2 -->
                    <div class="p-3 bg-gray-800/30 border border-gray-700/30 rounded-lg">
                        <div class="flex justify-between items-center">
                            <div class="text-xs text-gray-500">Advertisement</div>
                            <button class="text-xs text-gray-500 hover:text-gray-400">×</button>
                        </div>
                        <div class="h-80 flex items-center justify-center text-gray-600 text-sm">
                            [Square Ad Placement]
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </LayoutDashboard>
</template>

<script setup lang="ts">
import LayoutDashboard from '@/components/client/LayoutDashboard.vue';
import CardComponent from '@/components/client/ui/Card/CardComponent.vue';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Clock, Coins } from 'lucide-vue-next';
import Session from '@/mythicaldash/Session';
import { useSettingsStore } from '@/stores/settings';
import router from '@/router';
import Swal from 'sweetalert2';
const Settings = useSettingsStore();
import { MythicalDOM } from '@/mythicaldash/MythicalDOM';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

MythicalDOM.setPageTitle(t('afk.pages.index.title'));

// AFK Timer state
const isActive = ref(false);
const seconds = ref(0);
const totalCoins = ref(Session.getInfoInt('credits'));
const sessionCoins = ref(0);
const currentSessionTime = ref(0);
const totalAFKTime = ref(23);
const lastActiveTime = ref(Date.now());
let timerInterval: number | null = null;
const visibilityCheckInterval: number | null = null;

// Get the configurable AFK reward interval from settings (minutes per coin)
const minutesPerCoin = computed(() => {
    const setting = Settings.getSetting('afk_min_per_coin');
    // Default to 1 minute per coin if setting is not found or invalid
    return setting ? parseInt(setting) : 1;
});

if (Settings.getSetting('afk_enabled') === 'false') {
    Swal.fire({
        title: t('afk.notEnabled.title'),
        text: t('afk.notEnabled.text'),
        icon: 'error',
        confirmButtonText: t('afk.notEnabled.button'),
    });
    router.push('/dashboard');
}

// Format time display
const formatTime = computed(() => {
    const hours = Math.floor(seconds.value / 3600);
    const minutes = Math.floor((seconds.value % 3600) / 60);
    const secs = seconds.value % 60;

    return {
        hours: hours.toString().padStart(2, '0'),
        minutes: minutes.toString().padStart(2, '0'),
        seconds: secs.toString().padStart(2, '0'),
    };
});

// Format time into readable string (e.g. "12h 34m")
const formatTimeString = (totalMinutes: number): string => {
    const hours = Math.floor(totalMinutes / 60);
    const minutes = totalMinutes % 60;

    if (hours > 0) {
        return `${hours}h ${minutes}m`;
    }
    return `${minutes}m`;
};

// Check if user is actively on the page
const isUserActive = () => {
    return document.visibilityState === 'visible';
};

// Handle visibility change
const handleVisibilityChange = () => {
    if (!isUserActive() && isActive.value) {
        // User switched to another tab or minimized the window
        stopTimer();
        Swal.fire({
            title: t('afk.pages.afkpaused.title'),
            text: t('afk.pages.afkpaused.text'),
            icon: 'warning',
            confirmButtonText: t('afk.pages.afkpaused.button'),
        });
    }
};

// Toggle AFK mode
const toggleAFK = () => {
    if (isActive.value) {
        stopTimer();
    } else {
        startTimer();
    }
};

// Start the timer
const startTimer = () => {
    if (!isUserActive()) {
        Swal.fire({
            title: t('afk.pages.cannotstartafk.title'),
            text: t('afk.pages.cannotstartafk.text'),
            icon: 'warning',
            confirmButtonText: t('afk.pages.cannotstartafk.button'),
        });
        return;
    }

    isActive.value = true;
    lastActiveTime.value = Date.now();

    // Start the main timer
    timerInterval = window.setInterval(() => {
        if (!isUserActive()) {
            stopTimer();
            return;
        }

        seconds.value++;
        if (seconds.value % 60 === 0) {
            updateAFKStats();
        }
    }, 1000);

    // Add visibility change listener
    document.addEventListener('visibilitychange', handleVisibilityChange);
};

// Update AFK stats - called every minute
const updateAFKStats = async () => {
    // Increase AFK time by 1 minute (since our DB stores minutes)
    currentSessionTime.value += 1;
    totalAFKTime.value += 1;

    // Calculate coins earned based on the configured minutes per coin
    // Only award coins when the current session time is divisible by minutesPerCoin
    const coinsEarned = currentSessionTime.value % minutesPerCoin.value === 0 ? 1 : 0;

    // Update local counters if coins were earned
    if (coinsEarned > 0) {
        sessionCoins.value += coinsEarned;
        totalCoins.value += coinsEarned;
    }

    // Make an API call to update the server
    try {
        // Send the update to the server
        const response = await fetch('/api/user/earn/afk/work', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
            },
            body: JSON.stringify({
                coins_earned: coinsEarned,
                minutes_afk: 1,
            }),
        });

        const data = await response.json();

        // Check for successful API response
        if (response.ok) {
            // Handle successful API call
            if (data.status === 200) {
                // For data safety, save progress every 5 minutes
                if (currentSessionTime.value % 5 === 0) {
                    console.log('Periodic save - Updated AFK stats successfully:', {
                        coins_earned: sessionCoins.value,
                        minutes_afk: currentSessionTime.value,
                        total_coins: totalCoins.value,
                        total_afk_minutes: totalAFKTime.value,
                        message: data.message,
                    });
                }
            } else {
                console.warn('API responded with non-200 status:', data);
            }
        } else {
            // Handle API error
            console.error('Error updating AFK stats:', data.message || 'Unknown error');
        }
    } catch (error) {
        console.error('Failed to update AFK stats:', error);
    }
};

// Stop the timer
const stopTimer = () => {
    isActive.value = false;
    if (timerInterval !== null) {
        clearInterval(timerInterval);
        timerInterval = null;

        // Remove visibility change listener
        document.removeEventListener('visibilitychange', handleVisibilityChange);

        // Save final stats when stopping the timer
        if (currentSessionTime.value > 0) {
            saveStatsToServer();
        }
    }
};

// Save final stats to the server when stopping AFK mode
const saveStatsToServer = async () => {
    try {
        console.log('Saved final AFK session stats:', {
            session_coins: sessionCoins.value,
            session_minutes: currentSessionTime.value,
            total_coins: totalCoins.value,
            total_afk_minutes: totalAFKTime.value,
        });

        // Reset session counters after saving
        seconds.value = 0;
        sessionCoins.value = 0;
        currentSessionTime.value = 0;
    } catch (error) {
        console.error('Failed to save final AFK stats:', error);
    }
};

// Load saved state from localStorage
const loadSavedState = () => {
    const savedTotalCoins = Session.getInfoInt('credits');
    const savedTotalTime = Session.getInfoInt('minutes_afk');

    if (savedTotalCoins) totalCoins.value = savedTotalCoins;
    if (savedTotalTime) totalAFKTime.value = savedTotalTime;
};

// Cleanup on component unmount
onUnmounted(() => {
    // If AFK is active, save stats before stopping
    if (isActive.value && currentSessionTime.value > 0) {
        saveStatsToServer();
    }
    stopTimer();
    if (visibilityCheckInterval !== null) {
        clearInterval(visibilityCheckInterval);
    }
});

onMounted(() => {
    loadSavedState();
    // Check if we should auto-start AFK timer
    const autoStart = localStorage.getItem('afk_autoStart');
    if (autoStart === 'true' && isUserActive()) {
        startTimer();
    }
});
</script>

<style scoped>
.timer-unit {
    background-color: rgba(31, 41, 55, 0.5);
    border-radius: 0.75rem;
    padding: 1.25rem 0.5rem;
    position: relative;
    overflow: hidden;
}

.timer-unit::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: linear-gradient(to right, rgba(99, 102, 241, 0.2), rgba(139, 92, 246, 0.2));
}

.ad-banner {
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
}

.ad-banner:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}
</style>
