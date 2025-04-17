<template>
    <CardComponent
        :cardTitle="t('Components.ManageAccount.title')"
        :cardDescription="t('Components.ManageAccount.description')"
    >
        <div class="flex items-center space-x-4 mb-4">
            <img
                :src="`${Session.getInfo('avatar')}?height=60&width=60`"
                alt="Profile"
                class="w-14 h-14 rounded-full border-2 border-indigo-500/50"
            />
            <div>
                <h3 class="text-lg font-semibold text-white">
                    {{ Session.getInfo('first_name') }} {{ Session.getInfo('last_name') }}
                </h3>
                <div
                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-500/20 text-indigo-400 mt-1"
                >
                    {{ Session.getInfo('role_name') }}
                </div>
            </div>
        </div>
        <div class="border-t border-gray-800 mt-4 pt-4">
            <button @click="goToAccount" class="text-sm text-indigo-400 hover:text-indigo-300 flex items-center">
                <SettingsIcon class="w-4 h-4 mr-1" />
                {{ t('Components.ManageAccount.viewMore') }}
                <ChevronRightIcon class="w-4 h-4 ml-auto" />
            </button>
        </div>
    </CardComponent>
</template>

<script setup lang="ts">
import CardComponent from '@/components/client/ui/Card/CardComponent.vue';
import Session from '@/mythicaldash/Session';
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { Settings as SettingsIcon, ChevronRight as ChevronRightIcon } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
const router = useRouter();
const ticketCount = ref(0);
const { t } = useI18n();
// Navigate to account page
const goToAccount = () => {
    router.push('/account');
};

onMounted(async () => {
    // In a real implementation, you would fetch the ticket count from an API
    // This is just a placeholder
    ticketCount.value = Math.floor(Math.random() * 10) + 1;
});
</script>
