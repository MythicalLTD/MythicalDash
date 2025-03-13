<script setup lang="ts">
import { ShieldAlertIcon } from 'lucide-vue-next';
import ErrorPage from '@/components/client/Errors/ErrorPage.vue';
import Translation from '@/mythicaldash/Translation';
import { MythicalDOM } from '@/mythicaldash/MythicalDOM';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
MythicalDOM.setPageTitle(t('errors.forbidden.title'));
</script>

<template>
    <ErrorPage
        :icon="ShieldAlertIcon"
        :title="Translation.getTranslation('errors.forbidden.title')"
        :message="Translation.getTranslation('errors.forbidden.message')"
    />
</template>
