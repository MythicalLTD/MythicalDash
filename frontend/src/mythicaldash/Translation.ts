import { useI18n } from 'vue-i18n';

class Translation {
    static getTranslation(key: string): string {
        const { t } = useI18n();
        return t(key);
    }
}

export default Translation;
