<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-gradient backdrop-blur-sm">
        <div class="text-center p-8 rounded-xl bg-[#0a0a0f]/70 border border-[#2a2a3f]/30 shadow-2xl">
            <div class="w-16 h-16 mb-6 mx-auto relative">
                <!-- Outer spinner -->
                <svg
                    class="animate-spin-slow absolute inset-0"
                    viewBox="0 0 24 24"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <circle class="opacity-20" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"></circle>
                    <path
                        class="opacity-75"
                        fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                    ></path>
                </svg>

                <!-- Inner spinner -->
                <svg
                    class="animate-spin absolute inset-0 text-indigo-500"
                    viewBox="0 0 24 24"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <circle class="opacity-25" cx="12" cy="12" r="8" stroke="currentColor" stroke-width="2"></circle>
                    <path
                        class="opacity-75"
                        fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                    ></path>
                </svg>
            </div>
            <div class="text-xl font-bold bg-gradient-to-r from-indigo-400 to-indigo-600 bg-clip-text text-transparent">
                {{ $t('components.loading.title') }}
            </div>
            <p class="text-gray-400 mt-2 text-sm">Please wait while we load your dashboard</p>
        </div>
    </div>
</template>

<script setup lang="ts"></script>

<style scoped>
.bg-gradient {
    background: radial-gradient(circle at center, rgba(18, 18, 31, 0.9) 0%, rgba(10, 10, 15, 0.95) 100%);
}

.bg-gradient-to-r {
    -webkit-background-clip: text;
    background-clip: text;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

@keyframes spin-reverse {
    to {
        transform: rotate(-360deg);
    }
}

.animate-spin {
    animation: spin 1.5s linear infinite;
}

.animate-spin-slow {
    animation: spin-reverse 3s linear infinite;
}
</style>
