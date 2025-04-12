<template>
    <!-- Support PIN -->
    <CardComponent cardTitle="Support PIN" cardDescription="Share this PIN with the support team!">
        <div class="pin-container relative overflow-hidden">
            <!-- Decorative elements -->
            <div class="flex items-center justify-between relative z-10">
                <div class="pin-display group relative">
                    <span
                        class="text-emerald-400 text-3xl font-mono font-bold relative z-10 transition-all duration-300 group-hover:text-emerald-300"
                    >
                        {{ Session.getInfo('support_pin') }}
                    </span>
                    <div
                        class="pin-glow absolute inset-0 bg-emerald-500/10 blur-md opacity-0 group-hover:opacity-100 transition-opacity duration-500"
                    ></div>
                </div>
                <div class="flex space-x-2">
                    <button
                        @click="copyPin"
                        class="action-btn text-blue-500 hover:text-emerald-400 transition-all duration-300 p-2 rounded-full hover:bg-emerald-500/10"
                        title="Copy PIN"
                    >
                        <CopyIcon class="w-5 h-5" />
                    </button>
                    <button
                        @click="resetPin"
                        class="action-btn text-blue-500 hover:text-emerald-400 transition-all duration-300 p-2 rounded-full hover:bg-emerald-500/10"
                        title="Reset PIN"
                    >
                        <RefreshCcwIcon class="refresh-icon w-5 h-5 transition-transform duration-500" />
                    </button>
                </div>
            </div>

            <div class="mt-4 pt-4 border-t border-gray-800/30">
                <div class="text-xs text-gray-400 flex items-center">
                    <ShieldCheckIcon class="w-4 h-4 text-emerald-500 mr-2" />
                    <span>{{ t('Components.SupportPin.description') }}</span>
                </div>
            </div>
        </div>
    </CardComponent>
</template>

<script setup lang="ts">
import CardComponent from '@/components/client/ui/Card/CardComponent.vue';
import Auth from '@/mythicaldash/Auth';
import Session from '@/mythicaldash/Session';
import Swal from 'sweetalert2';
import { useI18n } from 'vue-i18n';
import { RefreshCcw as RefreshCcwIcon, Copy as CopyIcon, ShieldCheck as ShieldCheckIcon } from 'lucide-vue-next';

const { t } = useI18n();

const copyPin = async () => {
    const pin = Session.getInfo('support_pin');
    try {
        if (!navigator.clipboard) {
            const textArea = document.createElement('textarea');
            textArea.value = pin;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
        } else {
            await navigator.clipboard.writeText(pin);
        }

        Swal.fire({
            icon: 'success',
            title: t('Components.Global.Navigation.Copy.Title'),
            text: t('Components.Global.Navigation.Copy.Success'),
            footer: t('Components.Global.Navigation.Copy.Footer'),
        });
    } catch (err) {
        console.error('Failed to copy command to clipboard', err);
    }
};

const resetPin = async () => {
    try {
        const ping = await Auth.resetPin();
        const pinElement = document.querySelector('span.text-emerald-400');
        if (pinElement) {
            pinElement.textContent = ping.toString();
            Swal.fire({
                title: t('Components.SupportPin.alerts.success.title'),
                text: t('Components.SupportPin.alerts.success.pin_success'),
                icon: 'success',
                footer: t('Components.SupportPin.alerts.success.footer'),
            });
        }
    } catch (error) {
        console.error('Failed to reset support pin:', error);
        Swal.fire({
            title: t('Components.SupportPin.alerts.error.title'),
            text: t('Components.SupportPin.alerts.error.generic'),
            icon: 'error',
            footer: t('Components.SupportPin.alerts.error.footer'),
        });
    }
};
</script>

<style scoped>
.pin-container {
    position: relative;
    padding: 1rem 0;
}

.pin-display {
    display: inline-block;
    position: relative;
    padding: 0.5rem 0.5rem;
    border-radius: 0.5rem;
}

.action-btn:hover .refresh-icon {
    transform: rotate(180deg);
}

.action-btn {
    position: relative;
    overflow: hidden;
}

.action-btn::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: radial-gradient(circle, rgba(16, 185, 129, 0.2) 0%, transparent 80%);
    opacity: 0;
    transition: opacity 0.3s ease;
    border-radius: 9999px;
}

.action-btn:hover::after {
    opacity: 1;
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
