<!-- src/components/Auth/Layout.vue -->

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { LicenseServer } from '@/mythicaldash/LicenseServer';

const showFooter = ref(true);

onMounted(async () => {
    try {
        const isValid = await LicenseServer.isLicenseValid();
        showFooter.value = !isValid;
    } catch (error) {
        console.error('Error checking license:', error);
        showFooter.value = true;
    }
});
</script>

<template>
    <div class="min-h-screen bg-[#0a0a0f] relative overflow-hidden">
        <!-- Background elements -->
        <div class="absolute inset-0 bg-gradient-to-b from-[#0a0a0f] via-[#12121f] to-[#0a0a0f]">
            <!-- Enhanced star field -->
            <div class="stars-small"></div>
            <div class="stars-medium"></div>
            <div class="stars-large"></div>

            <!-- Grid overlay with better visibility -->
            <div class="grid-overlay"></div>

            <!-- Enhanced glow effects -->
            <div class="glow-effects"></div>

            <!-- Additional accent elements -->
            <div class="accent-line-1"></div>
            <div class="accent-circle"></div>
        </div>
        <!-- Content -->
        <div class="relative z-10 min-h-screen flex flex-col items-center justify-center p-4">
            <slot></slot>

            <!-- Footer - Only shown if license is not valid -->
            <div v-if="showFooter" class="absolute bottom-4 text-center text-sm text-gray-500">
                <a href="https://mythical.systems" class="hover:text-indigo-400 transition-colors duration-200"
                    >MythicalSystems</a
                >
                <p>LTD 2020 - {{ new Date().getFullYear() }}</p>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Star field with multiple layers for depth */
.stars-small {
    position: absolute;
    inset: 0;
    background-image:
        radial-gradient(1px 1px at 20% 30%, rgba(255, 255, 255, 0.2) 0%, transparent 100%),
        radial-gradient(1px 1px at 40% 70%, rgba(255, 255, 255, 0.2) 0%, transparent 100%),
        radial-gradient(1px 1px at 60% 40%, rgba(255, 255, 255, 0.2) 0%, transparent 100%),
        radial-gradient(1px 1px at 80% 10%, rgba(255, 255, 255, 0.2) 0%, transparent 100%);
    background-size:
        300px 300px,
        250px 250px,
        350px 350px,
        400px 400px;
    animation: twinkle-small 12s infinite alternate;
}

.stars-medium {
    position: absolute;
    inset: 0;
    background-image:
        radial-gradient(1.5px 1.5px at 15% 15%, rgba(255, 255, 255, 0.3) 0%, transparent 100%),
        radial-gradient(1.5px 1.5px at 35% 85%, rgba(255, 255, 255, 0.3) 0%, transparent 100%),
        radial-gradient(1.5px 1.5px at 65% 25%, rgba(255, 255, 255, 0.3) 0%, transparent 100%),
        radial-gradient(1.5px 1.5px at 85% 65%, rgba(255, 255, 255, 0.3) 0%, transparent 100%);
    background-size:
        350px 350px,
        300px 300px,
        400px 400px,
        450px 450px;
    animation: twinkle-medium 15s infinite alternate;
}

.stars-large {
    position: absolute;
    inset: 0;
    background-image:
        radial-gradient(2px 2px at 10% 50%, rgba(255, 255, 255, 0.4) 0%, transparent 100%),
        radial-gradient(2px 2px at 30% 20%, rgba(255, 255, 255, 0.4) 0%, transparent 100%),
        radial-gradient(2px 2px at 70% 60%, rgba(255, 255, 255, 0.4) 0%, transparent 100%),
        radial-gradient(2px 2px at 90% 30%, rgba(255, 255, 255, 0.4) 0%, transparent 100%);
    background-size:
        400px 400px,
        350px 350px,
        450px 450px,
        500px 500px;
    animation: twinkle-large 18s infinite alternate;
}

/* Enhanced grid overlay */
.grid-overlay {
    position: absolute;
    inset: 0;
    background-image:
        linear-gradient(to right, rgba(42, 42, 63, 0.07) 1px, transparent 1px),
        linear-gradient(to bottom, rgba(42, 42, 63, 0.07) 1px, transparent 1px);
    background-size: 50px 50px;
    mask-image: radial-gradient(circle at center, rgba(0, 0, 0, 0.7) 0%, transparent 80%);
}

/* Enhanced glow effects */
.glow-effects {
    position: absolute;
    inset: 0;
    background:
        radial-gradient(circle at 20% 20%, rgba(99, 102, 241, 0.08) 0%, transparent 60%),
        radial-gradient(circle at 80% 80%, rgba(99, 102, 241, 0.08) 0%, transparent 60%),
        radial-gradient(circle at 50% 50%, rgba(99, 102, 241, 0.03) 0%, transparent 70%);
    pointer-events: none;
}

/* Accent elements for visual interest */
.accent-line-1 {
    position: absolute;
    top: 15%;
    left: -5%;
    width: 30%;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(99, 102, 241, 0.2), transparent);
    transform: rotate(-30deg);
}

.accent-circle {
    position: absolute;
    top: 70%;
    left: 15%;
    width: 300px;
    height: 300px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(99, 102, 241, 0.03) 0%, transparent 70%);
    opacity: 0.5;
}

/* Star animations */
@keyframes twinkle-small {
    0%,
    100% {
        opacity: 0.3;
    }
    50% {
        opacity: 0.5;
    }
}

@keyframes twinkle-medium {
    0%,
    100% {
        opacity: 0.4;
    }
    50% {
        opacity: 0.6;
    }
}

@keyframes twinkle-large {
    0%,
    100% {
        opacity: 0.5;
    }
    50% {
        opacity: 0.7;
    }
}

/* Mobile optimizations */
@media (max-width: 768px) {
    .grid-overlay {
        background-size: 30px 30px;
    }

    .accent-line-1 {
        display: none;
    }

    .accent-circle {
        width: 150px;
        height: 150px;
    }
}
</style>
