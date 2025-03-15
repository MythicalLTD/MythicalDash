<template>
    <div class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Backdrop -->
            <div
                class="fixed inset-0 bg-[#0a0a0f]/80 backdrop-blur-sm transition-opacity"
                aria-hidden="true"
                @click="$emit('close')"
            ></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal Content -->
            <div
                class="inline-block align-bottom bg-[#12121f] rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-[#2a2a3f]/30"
            >
                <!-- Header -->
                <div class="bg-[#0a0a0f]/50 px-6 py-4 border-b border-[#2a2a3f]/30">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg leading-6 font-medium text-gray-100" id="modal-title">
                            <slot name="header"></slot>
                        </h3>
                        <button
                            @click="$emit('close')"
                            class="text-gray-400 hover:text-gray-200 transition-colors duration-200"
                            aria-label="Close modal"
                        >
                            <svg
                                class="h-5 w-5"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Body -->
                <div class="bg-[#12121f] px-6 py-4">
                    <div class="w-full">
                        <slot name="body"></slot>
                    </div>
                </div>

                <!-- Footer -->
                <div class="bg-[#0a0a0f]/50 px-6 py-4 border-t border-[#2a2a3f]/30 sm:flex sm:flex-row-reverse">
                    <slot name="footer"></slot>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
defineEmits(['close']);
</script>

<style scoped>
/* Transitions */
.transform {
    transition-property: transform, opacity;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}

/* Focus handling for accessibility */
:focus {
    outline: 2px solid rgba(99, 102, 241, 0.5);
    outline-offset: 2px;
}
</style>
