<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import { RefreshCwIcon } from 'lucide-vue-next';

const props = defineProps<{
    isReloading: boolean;
}>();

const mounted = ref(false);
const progress = ref(0);

onMounted(() => {
    mounted.value = true;
});

// Progress animation
const startProgress = () => {
    progress.value = 0;
    const interval = setInterval(() => {
        if (progress.value < 90) {
            progress.value += Math.random() * 15;
        }
        if (!props.isReloading) {
            progress.value = 100;
            clearInterval(interval);
        }
    }, 500);
};

watch(
    () => props.isReloading,
    (newValue) => {
        if (newValue) {
            startProgress();
        }
    },
);
</script>

<template>
    <div>
        <!-- Progress bar at the top -->
        <div class="fixed top-0 left-0 w-full h-1 z-[60]" v-show="isReloading">
            <div
                class="h-full bg-gradient-to-r from-purple-600 via-purple-400 to-purple-600 animate-pulse"
                :style="{ width: `${progress}%` }"
            />
        </div>

        <!-- Fullscreen overlay -->
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="isReloading" class="fixed inset-0 z-[55] bg-[#0a0a1f]/80 backdrop-blur-sm">
                <!-- Animated background elements -->
                <div class="absolute inset-0 overflow-hidden">
                    <div class="stars"></div>
                    <div class="shooting-stars"></div>
                </div>

                <!-- Center content -->
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="text-center">
                        <!-- Spinning icon with glow -->
                        <div class="relative inline-block mb-6">
                            <div class="absolute inset-0 bg-purple-500/30 blur-xl animate-pulse rounded-full"></div>
                            <RefreshCwIcon
                                class="w-12 h-12 text-purple-400 animate-spin relative z-10"
                                :class="{ 'opacity-0': !mounted }"
                            />
                        </div>

                        <!-- Loading text -->
                        <div class="space-y-2">
                            <h3 class="text-xl font-medium text-white animate-pulse">Refreshing your experience</h3>
                            <div class="flex items-center justify-center gap-1">
                                <div
                                    v-for="i in 3"
                                    :key="i"
                                    class="w-2 h-2 rounded-full bg-purple-500 animate-bounce"
                                    :style="{ animationDelay: `${i * 0.2}s` }"
                                ></div>
                            </div>
                            <p class="text-purple-300/80 text-sm">
                                {{ Math.min(Math.round(progress), 100) }}% Complete
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.animate-spin {
    animation: spin 2s linear infinite;
}

.stars {
    position: absolute;
    width: 100%;
    height: 100%;
    background-image:
        radial-gradient(2px 2px at 20px 30px, #eee, rgba(0, 0, 0, 0)),
        radial-gradient(2px 2px at 40px 70px, #fff, rgba(0, 0, 0, 0)),
        radial-gradient(2px 2px at 50px 160px, #ddd, rgba(0, 0, 0, 0)),
        radial-gradient(2px 2px at 90px 40px, #fff, rgba(0, 0, 0, 0)),
        radial-gradient(2px 2px at 130px 80px, #fff, rgba(0, 0, 0, 0));
    background-repeat: repeat;
    background-size: 200px 200px;
    animation: twinkle 4s ease-in-out infinite;
    opacity: 0.5;
}

.shooting-stars {
    position: absolute;
    width: 100%;
    height: 100%;
    transform: rotate(-45deg);
}

.shooting-stars::before,
.shooting-stars::after {
    content: '';
    position: absolute;
    width: 100px;
    height: 2px;
    background: linear-gradient(90deg, #fff, transparent);
    animation: shooting 3s linear infinite;
}

.shooting-stars::after {
    animation-delay: 1.5s;
}

@keyframes twinkle {
    0%,
    100% {
        opacity: 0.5;
    }

    50% {
        opacity: 0.8;
    }
}

@keyframes shooting {
    0% {
        transform: translateX(-100%) translateY(-100%);
        opacity: 1;
    }

    50% {
        opacity: 0;
    }

    100% {
        transform: translateX(200%) translateY(200%);
        opacity: 0;
    }
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }

    to {
        transform: rotate(360deg);
    }
}

/* Bouncing dots animation */
.animate-bounce {
    animation: bounce 1s infinite;
}

@keyframes bounce {
    0%,
    100% {
        transform: translateY(0);
    }

    50% {
        transform: translateY(-8px);
    }
}

/* Progress bar animation */
.bg-gradient-to-r {
    background-size: 200% 100%;
    animation: gradient 2s linear infinite;
}

@keyframes gradient {
    0% {
        background-position: 100% 0;
    }

    100% {
        background-position: -100% 0;
    }
}
</style>
