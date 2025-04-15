<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue';
import Layout from '@/components/client/Layout.vue';
import FormCard from '@/components/client/Auth/FormCard.vue';
import FormInput from '@/components/client/Auth/FormInput.vue';
import Swal from 'sweetalert2';
import { useRouter } from 'vue-router';
import Turnstile from 'vue-turnstile';
import { useSettingsStore } from '@/stores/settings';
const Settings = useSettingsStore();
import { useSound } from '@vueuse/sound';
import failedAlertSfx from '@/assets/sounds/error.mp3';
import successAlertSfx from '@/assets/sounds/success.mp3';
import Auth from '@/mythicaldash/Auth';
import { useI18n } from 'vue-i18n';
import { MythicalDOM } from '@/mythicaldash/MythicalDOM';

const { t } = useI18n();
const { play: playError } = useSound(failedAlertSfx);
const { play: playSuccess } = useSound(successAlertSfx);
const router = useRouter();
localStorage.clear();
sessionStorage.clear();
MythicalDOM.setPageTitle(t('auth.pages.login.page.title'));

const loading = ref(false);
const form = reactive({
    email: '',
    password: '',
    turnstileResponse: '',
});
const domainName = localStorage.getItem('domain_name');

const handleSubmit = async () => {
    try {
        loading.value = true;
        const response = await Auth.login(form.email, form.password, form.turnstileResponse);
        if (!response.success) {
            const error_code = response.error_code as keyof typeof errorMessages;

            const errorMessages = {
                TURNSTILE_FAILED: t('auth.pages.login.alerts.error.cloudflare_error'),
                INVALID_CREDENTIALS: t('auth.pages.login.alerts.error.invalid_credentials'),
                ACCOUNT_NOT_VERIFIED: t('auth.pages.login.alerts.error.not_verified'),
                ACCOUNT_BANNED: t('auth.pages.login.alerts.error.banned'),
                ACCOUNT_DELETED: t('auth.pages.login.alerts.error.deleted'),
                PTERODACTYL_USER_NOT_FOUND: t('auth.pages.login.alerts.error.pterodactyl_user_not_found'),
                PTERODACTYL_ERROR: t('auth.pages.login.alerts.error.pterodactyl_error'),
                PTERODACTYL_NOT_ENABLED: t('auth.pages.login.alerts.error.pterodactyl_not_enabled'),
            };

            if (errorMessages[error_code]) {
                playError();
                Swal.fire({
                    icon: 'error',
                    title: t('auth.pages.login.alerts.error.title'),
                    text: errorMessages[error_code],
                    footer: t('auth.pages.login.alerts.error.footer'),
                    showConfirmButton: true,
                });
                loading.value = false;
                throw new Error('Login failed');
            } else {
                playError();
                Swal.fire({
                    icon: 'error',
                    title: t('auth.pages.login.alerts.error.title'),
                    text: t('auth.pages.login.alerts.error.generic'),
                    footer: t('auth.pages.login.alerts.error.footer'),
                    showConfirmButton: true,
                });
                loading.value = false;
                throw new Error('Login failed');
            }
        } else {
            playSuccess();
            Swal.fire({
                icon: 'success',
                title: t('auth.pages.login.alerts.success.title'),
                text: t('auth.pages.login.alerts.success.login_success'),
                footer: t('auth.pages.login.alerts.success.footer'),
                showConfirmButton: true,
            });
            loading.value = false;
            setTimeout(() => {
                router.push('/');
            }, 1500);
        }
    } catch (error) {
        console.error('Login failed:', error);
    } finally {
        loading.value = false;
    }
};

function base64_decode(str: string | null): string {
    if (!str) return '';
    try {
        return atob(str);
    } catch (e) {
        console.error('Failed to decode base64 string:', e);
        return '';
    }
}

onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    const email = base64_decode(urlParams.get('email'));
    const password = base64_decode(urlParams.get('password'));
    const performLogin = urlParams.get('performLogin');

    if (email && password && performLogin === 'true') {
        form.email = email;
        form.password = password;
        handleSubmit();
        window.history.replaceState({}, '', window.location.pathname);
    }
});

const isEnterpriseLogin = localStorage.getItem('domain_name') !== null;

if (isEnterpriseLogin) {
    document.title = `${t('auth.pages.login.page.subTitle')} - ${localStorage.getItem('domain_name')}`;
}
</script>
<template>
    <Layout>
        <FormCard
            :title="`${$t('auth.pages.login.page.subTitle')} ${domainName ? ` - ${domainName}` : ''}`"
            @submit="handleSubmit"
        >
            <FormInput
                id="email"
                :label="$t('auth.pages.login.page.form.email.label')"
                v-model="form.email"
                :placeholder="$t('auth.pages.login.page.form.email.placeholder')"
                required
            />
            <div class="flex items-center justify-between mb-2">
                <label class="block text-sm text-gray-400">{{ $t('auth.pages.login.page.form.password.label') }}</label>
                <router-link to="/auth/forgot-password" class="text-sm text-purple-400 hover:text-purple-300">
                    {{ $t('auth.pages.login.page.form.forgot_password') }}
                </router-link>
            </div>

            <FormInput
                id="password"
                type="password"
                v-model="form.password"
                :placeholder="t('auth.pages.login.page.form.password.placeholder')"
                required
            />
            <div
                v-if="Settings.getSetting('turnstile_enabled') == 'true'"
                style="display: flex; justify-content: center; margin-top: 20px"
            >
                <Turnstile :site-key="Settings.getSetting('turnstile_key_pub')" v-model="form.turnstileResponse" />
            </div>
            <button
                type="submit"
                class="w-full mt-6 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors"
                :disabled="loading"
            >
                {{
                    loading
                        ? $t('auth.pages.login.page.form.login_button.loading')
                        : $t('auth.pages.login.page.form.login_button.label')
                }}
            </button>

            <div class="flex items-center my-4">
                <div class="flex-1 border-t border-gray-600"></div>
                <span class="px-4 text-sm text-gray-400">or</span>
                <div class="flex-1 border-t border-gray-600"></div>
            </div>

            <a
                v-if="Settings.getSetting('discord_enabled') === 'true'"
                href="/api/user/auth/callback/discord/login"
                class="flex items-center justify-center w-full px-4 py-2 bg-[#5865F2] hover:bg-[#4752C4] text-white rounded-lg transition-colors"
            >
                <svg
                    class="w-5 h-5 mr-2"
                    viewBox="0 -28.5 256 256"
                    version="1.1"
                    xmlns="http://www.w3.org/2000/svg"
                    preserveAspectRatio="xMidYMid"
                >
                    <path
                        d="M216.856339,16.5966031 C200.285002,8.84328665 182.566144,3.2084988 164.041564,0 C161.766523,4.11318106 159.108624,9.64549908 157.276099,14.0464379 C137.583995,11.0849896 118.072967,11.0849896 98.7430163,14.0464379 C96.9108417,9.64549908 94.1925838,4.11318106 91.8971895,0 C73.3526068,3.2084988 55.6133949,8.86399117 39.0420583,16.6376612 C5.61752293,67.146514 -3.4433191,116.400813 1.08711069,164.955721 C23.2560196,181.510915 44.7403634,191.567697 65.8621325,198.148576 C71.0772151,190.971126 75.7283628,183.341335 79.7352139,175.300261 C72.104019,172.400575 64.7949724,168.822202 57.8887866,164.667963 C59.7209612,163.310589 61.5131304,161.891452 63.2445898,160.431257 C105.36741,180.133187 151.134928,180.133187 192.754523,160.431257 C194.506336,161.891452 196.298154,163.310589 198.110326,164.667963 C191.183787,168.842556 183.854737,172.420929 176.223542,175.320965 C180.230393,183.341335 184.861538,190.991831 190.096624,198.16893 C211.238746,191.588051 232.743023,181.531619 254.911949,164.955721 C260.227747,108.668201 245.831087,59.8662432 216.856339,16.5966031 Z M85.4738752,135.09489 C72.8290281,135.09489 62.4592217,123.290155 62.4592217,108.914901 C62.4592217,94.5396472 72.607595,82.7145587 85.4738752,82.7145587 C98.3405064,82.7145587 108.709962,94.5189427 108.488529,108.914901 C108.508531,123.290155 98.3405064,135.09489 85.4738752,135.09489 Z M170.525237,135.09489 C157.88039,135.09489 147.510584,123.290155 147.510584,108.914901 C147.510584,94.5396472 157.658606,82.7145587 170.525237,82.7145587 C183.391518,82.7145587 193.761324,94.5189427 193.539891,108.914901 C193.539891,123.290155 183.391518,135.09489 170.525237,135.09489 Z"
                        fill="currentColor"
                        fill-rule="nonzero"
                    ></path>
                </svg>
                Continue with Discord
            </a>

            <a
                v-if="Settings.getSetting('github_enabled') === 'true'"
                href="/api/user/auth/callback/github/login"
                class="flex items-center justify-center w-full px-4 py-2 bg-[#24292e] hover:bg-[#1b1f23] text-white rounded-lg transition-colors mt-2"
            >
                <svg class="w-5 h-5 mr-2" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                    <path
                        fill-rule="evenodd"
                        d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0016 8c0-4.42-3.58-8-8-8z"
                    ></path>
                </svg>
                Continue with GitHub
            </a>

            <p class="mt-4 text-center text-sm text-gray-400">
                {{ $t('auth.pages.login.page.form.register.label') }}
                <router-link to="/auth/register" class="text-purple-400 hover:text-purple-300">
                    {{ $t('auth.pages.login.page.form.register.link') }}
                </router-link>
            </p>
        </FormCard>
    </Layout>
</template>
