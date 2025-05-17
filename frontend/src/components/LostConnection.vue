<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center overflow-hidden space-bg">
        <!-- Animated Space Background: Stars -->
        <div class="absolute inset-0 pointer-events-none select-none">
            <div v-for="n in 60" :key="n" :class="`star star${n}`"></div>
            <div class="blob blob1"></div>
            <div class="blob blob2"></div>
            <div class="blob blob3"></div>
            <div class="blob blob4"></div>
        </div>
        <div
            class="max-w-md w-full mx-4 p-8 bg-[#1a1a2e] rounded-xl border border-[#2a2a3f]/30 shadow-2xl relative z-10"
        >
            <!-- Connection Status Icon -->
            <div class="flex justify-center mb-6">
                <div class="relative">
                    <div class="w-16 h-16 rounded-full bg-[#2a2a3f]/30 flex items-center justify-center">
                        <svg
                            class="w-8 h-8 text-indigo-400"
                            viewBox="0 0 24 24"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                            <path
                                d="M12 6V12L16 14"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </div>
                    <!-- Pulsing Ring Animation -->
                    <div class="absolute inset-0 rounded-full animate-ping bg-indigo-400/20"></div>
                </div>
            </div>

            <!-- Message -->
            <div class="text-center">
                <h2 class="text-xl font-semibold text-white mb-2">
                    {{ t('lost_connection.title') }}
                </h2>
                <p class="text-gray-400 mb-6">
                    {{ t('lost_connection.message') }}
                </p>
            </div>

            <!-- Loading Animation -->
            <div class="flex justify-center mb-6">
                <div class="flex space-x-2">
                    <div class="w-3 h-3 rounded-full bg-indigo-400 animate-bounce" style="animation-delay: 0s"></div>
                    <div class="w-3 h-3 rounded-full bg-indigo-400 animate-bounce" style="animation-delay: 0.2s"></div>
                    <div class="w-3 h-3 rounded-full bg-indigo-400 animate-bounce" style="animation-delay: 0.4s"></div>
                </div>
            </div>

            <!-- Instructions -->
            <div class="bg-[#2a2a3f]/30 rounded-lg p-4">
                <div class="flex items-start space-x-3">
                    <svg
                        class="w-5 h-5 text-indigo-400 mt-0.5"
                        viewBox="0 0 24 24"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path
                            d="M13 16H12V12H11M12 8H12.01M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                    <p class="text-sm text-gray-400">
                        {{ t('lost_connection.instructions') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
</script>

<style scoped>
.space-bg {
    background: radial-gradient(ellipse at 60% 40%, #232b4d 0%, #1a1440 60%, #0a0a1a 100%);
}

/* Twinkling Stars */
.star {
    position: absolute;
    border-radius: 50%;
    background: white;
    opacity: 0.8;
    width: 2px;
    height: 2px;
    animation: twinkle 2.5s infinite alternate;
}
/* Randomize star positions and delays */
.star1 {
    top: 10%;
    left: 20%;
    animation-delay: 0.1s;
}
.star2 {
    top: 30%;
    left: 80%;
    animation-delay: 0.3s;
}
.star3 {
    top: 60%;
    left: 50%;
    animation-delay: 0.5s;
}
.star4 {
    top: 80%;
    left: 10%;
    animation-delay: 0.7s;
}
.star5 {
    top: 15%;
    left: 60%;
    animation-delay: 0.9s;
}
.star6 {
    top: 40%;
    left: 30%;
    animation-delay: 1.1s;
}
.star7 {
    top: 70%;
    left: 70%;
    animation-delay: 1.3s;
}
.star8 {
    top: 25%;
    left: 40%;
    animation-delay: 1.5s;
}
.star9 {
    top: 55%;
    left: 15%;
    animation-delay: 1.7s;
}
.star10 {
    top: 85%;
    left: 85%;
    animation-delay: 1.9s;
}
.star11 {
    top: 12%;
    left: 75%;
    animation-delay: 2.1s;
}
.star12 {
    top: 22%;
    left: 55%;
    animation-delay: 2.3s;
}
.star13 {
    top: 35%;
    left: 10%;
    animation-delay: 2.5s;
}
.star14 {
    top: 45%;
    left: 90%;
    animation-delay: 2.7s;
}
.star15 {
    top: 65%;
    left: 35%;
    animation-delay: 2.9s;
}
.star16 {
    top: 75%;
    left: 60%;
    animation-delay: 3.1s;
}
.star17 {
    top: 20%;
    left: 85%;
    animation-delay: 3.3s;
}
.star18 {
    top: 50%;
    left: 60%;
    animation-delay: 3.5s;
}
.star19 {
    top: 80%;
    left: 40%;
    animation-delay: 3.7s;
}
.star20 {
    top: 90%;
    left: 20%;
    animation-delay: 3.9s;
}
/* ...repeat for up to .star60 with different positions/delays for more density */

@keyframes twinkle {
    0%,
    100% {
        opacity: 0.8;
    }
    50% {
        opacity: 0.2;
    }
}

@keyframes ping {
    75%,
    100% {
        transform: scale(2);
        opacity: 0;
    }
}

.animate-ping {
    animation: ping 2s cubic-bezier(0, 0, 0.2, 1) infinite;
}

@keyframes bounce {
    0%,
    100% {
        transform: translateY(-25%);
        animation-timing-function: cubic-bezier(0.8, 0, 1, 1);
    }
    50% {
        transform: translateY(0);
        animation-timing-function: cubic-bezier(0, 0, 0.2, 1);
    }
}

.animate-bounce {
    animation: bounce 1s infinite;
}

/* Animated Blobs (Nebulae) */
.blob {
    position: absolute;
    border-radius: 50%;
    filter: blur(80px);
    opacity: 0.35;
    mix-blend-mode: lighten;
    pointer-events: none;
}
.blob1 {
    width: 500px;
    height: 500px;
    background: linear-gradient(135deg, #6366f1 0%, #a5b4fc 100%);
    top: 10%;
    left: 5%;
    animation: blobMove1 18s ease-in-out infinite alternate;
}
.blob2 {
    width: 350px;
    height: 350px;
    background: linear-gradient(135deg, #a5b4fc 0%, #818cf8 100%);
    bottom: 10%;
    right: 10%;
    animation: blobMove2 22s ease-in-out infinite alternate;
}
.blob3 {
    width: 300px;
    height: 300px;
    background: linear-gradient(135deg, #818cf8 0%, #312e81 100%);
    top: 60%;
    left: 60%;
    animation: blobMove3 20s ease-in-out infinite alternate;
}
.blob4 {
    width: 400px;
    height: 400px;
    background: linear-gradient(135deg, #312e81 0%, #6366f1 100%);
    bottom: 15%;
    left: 40%;
    animation: blobMove4 25s ease-in-out infinite alternate;
}
@keyframes blobMove1 {
    0% {
        transform: translate(0, 0) scale(1);
    }
    50% {
        transform: translate(60px, -40px) scale(1.1);
    }
    100% {
        transform: translate(-40px, 60px) scale(0.95);
    }
}
@keyframes blobMove2 {
    0% {
        transform: translate(0, 0) scale(1);
    }
    40% {
        transform: translate(-50px, 30px) scale(1.05);
    }
    100% {
        transform: translate(30px, -60px) scale(1.15);
    }
}
@keyframes blobMove3 {
    0% {
        transform: translate(0, 0) scale(1);
    }
    60% {
        transform: translate(-30px, 40px) scale(1.08);
    }
    100% {
        transform: translate(40px, -30px) scale(0.92);
    }
}
@keyframes blobMove4 {
    0% {
        transform: translate(0, 0) scale(1);
    }
    50% {
        transform: translate(50px, 50px) scale(1.12);
    }
    100% {
        transform: translate(-60px, -40px) scale(0.98);
    }
}
</style>
