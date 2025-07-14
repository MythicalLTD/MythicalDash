<script setup lang="ts">
import { ref, reactive, watch, onMounted, onUnmounted, computed } from 'vue';
import Layout from '@/components/client/Layout.vue';
import FormCard from '@/components/client/Auth/FormCard.vue';
import FormInput from '@/components/client/Auth/FormInput.vue';
import Swal from 'sweetalert2';
import { useSettingsStore } from '@/stores/settings';
const Settings = useSettingsStore();
import Turnstile from 'vue-turnstile';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import successAlertSfx from '@/assets/sounds/success.mp3';
import failedAlertSfx from '@/assets/sounds/error.mp3';
import { useSound } from '@vueuse/sound';
import Auth from '@/mythicaldash/Auth';
import { MythicalDOM } from '@/mythicaldash/MythicalDOM';

const { play: playError } = useSound(failedAlertSfx);
const { play: playSuccess } = useSound(successAlertSfx);
const router = useRouter();
const { t } = useI18n();

const loading = ref(false);
const form = reactive({
    firstName: '',
    lastName: '',
    username: '',
    email: '',
    password: '',
    turnstileResponse: '',
    referralCode: '',
});

// Add email suggestions
const emailSuggestions = ref<string[]>([]);
const showSuggestions = ref(false);
const commonDomains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'protonmail.com'];

const generateUsername = () => {
    const adjectives = ['happy', 'clever', 'brave', 'swift', 'bright', 'calm', 'eager', 'fair', 'kind', 'lively'];
    const nouns = ['panda', 'tiger', 'eagle', 'dolphin', 'wolf', 'phoenix', 'dragon', 'lion', 'bear', 'fox'];
    const numbers = Math.floor(Math.random() * 1000);
    const randomAdj = adjectives[Math.floor(Math.random() * adjectives.length)];
    const randomNoun = nouns[Math.floor(Math.random() * nouns.length)];
    form.username = `${randomAdj}${randomNoun}${numbers}`;
};

const generatePassword = () => {
    const uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const lowercase = 'abcdefghijklmnopqrstuvwxyz';
    const numbers = '0123456789';
    const symbols = '!@#$%^&*()_+-=[]{}|;:,.<>?';

    const allChars = uppercase + lowercase + numbers + symbols;
    let password = '';

    // Ensure at least one of each character type
    password += uppercase[Math.floor(Math.random() * uppercase.length)];
    password += lowercase[Math.floor(Math.random() * lowercase.length)];
    password += numbers[Math.floor(Math.random() * numbers.length)];
    password += symbols[Math.floor(Math.random() * symbols.length)];

    // Fill the rest randomly
    for (let i = 4; i < 16; i++) {
        password += allChars[Math.floor(Math.random() * allChars.length)];
    }

    // Shuffle the password
    password = password
        .split('')
        .sort(() => Math.random() - 0.5)
        .join('');

    form.password = password;
};

const updateEmailSuggestions = (email: string) => {
    const [localPart] = email.split('@');
    if (localPart) {
        emailSuggestions.value = commonDomains.map((domain) => `${localPart}@${domain}`);
        showSuggestions.value = true;
    } else {
        emailSuggestions.value = [];
        showSuggestions.value = false;
    }
};

const hideSuggestions = () => {
    showSuggestions.value = false;
};

// Watch for email changes
watch(
    () => form.email,
    (newEmail) => {
        updateEmailSuggestions(newEmail);
    },
);

// Add click outside listener
onMounted(() => {
    document.addEventListener('click', (e) => {
        const target = e.target as HTMLElement;
        if (!target.closest('.email-suggestions-container')) {
            hideSuggestions();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            hideSuggestions();
        }
    });
});

onUnmounted(() => {
    document.removeEventListener('click', hideSuggestions);
    document.removeEventListener('keydown', hideSuggestions);
});

if (router.currentRoute.value.query.ref) {
    form.referralCode = router.currentRoute.value.query.ref as string;
}

MythicalDOM.setPageTitle(t('auth.pages.register.page.title'));
const referralsEnabled = Settings.getSetting('referrals_enabled');
const acceptTerms = ref(false);
const validationErrors = ref<{ [key: string]: string }>({});

const validateForm = () => {
    validationErrors.value = {};
    // First Name: required, only letters, 1-191 chars
    if (!form.firstName || !/^[a-zA-Z]+$/.test(form.firstName) || form.firstName.length > 191) {
        validationErrors.value.firstName = t('auth.pages.register.page.form.firstName.validation');
    }
    // Last Name: required, only letters, 1-191 chars
    if (!form.lastName || !/^[a-zA-Z]+$/.test(form.lastName) || form.lastName.length > 191) {
        validationErrors.value.lastName = t('auth.pages.register.page.form.lastName.validation');
    }
    // Username: required, 1-191 chars, regex /^[a-z0-9]([\w\.-]+)[a-z0-9]$/i
    if (
        !form.username ||
        form.username.length < 1 ||
        form.username.length > 191 ||
        !/^[a-z0-9]([\w\.-]+)[a-z0-9]$/i.test(form.username)
    ) {
        validationErrors.value.username = t('auth.pages.register.page.form.username.validation');
    }
    // Email: required, valid, 1-191 chars
    if (
        !form.email ||
        form.email.length < 1 ||
        form.email.length > 191 ||
        !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)
    ) {
        validationErrors.value.email = t('auth.pages.register.page.form.email.validation');
    }
    // Password: required, min 8 chars
    if (!form.password || form.password.length < 8) {
        validationErrors.value.password = t('auth.pages.register.page.form.password.validation');
    }
    return Object.keys(validationErrors.value).length === 0;
};

const handleSubmit = async () => {
    if (!acceptTerms.value) {
        playError();
        Swal.fire({
            icon: 'error',
            title: t('auth.pages.register.alerts.error.title'),
            text: t('auth.pages.register.page.form.accept_terms.required'),
            showConfirmButton: true,
        });
        loading.value = false;
        return;
    }
    if (!validateForm()) {
        playError();
        Swal.fire({
            icon: 'error',
            title: t('auth.pages.register.alerts.error.title'),
            text: t('auth.pages.register.page.form.validation_failed'),
            showConfirmButton: true,
        });
        return;
    }
    loading.value = true;
    try {
        const response = await Auth.register(
            form.firstName,
            form.lastName,
            form.email,
            form.username,
            form.password,
            form.turnstileResponse,
            form.referralCode,
        );

        if (!response.success) {
            const error_code = response.error_code as keyof typeof errorMessages;

            const errorMessages = {
                TURNSTILE_FAILED: t('auth.pages.register.alerts.error.cloudflare_error'),
                USERNAME_ALREADY_IN_USE: t('auth.pages.register.alerts.error.username_exists'),
                EMAIL_ALREADY_IN_USE: t('auth.pages.register.alerts.error.email_exists'),
                DATABASE_ERROR: t('auth.pages.register.alerts.error.generic'),
                PTERODACTYL_NOT_ENABLED: t('auth.pages.register.alerts.error.pterodactyl_not_enabled'),
                PTERODACTYL_ERROR: t('auth.pages.register.alerts.error.pterodactyl_error'),
                PROXY_DETECTED: t('auth.pages.register.alerts.error.proxy_detected'),
            };

            if (errorMessages[error_code]) {
                playError();
                Swal.fire({
                    icon: 'error',
                    title: t('auth.pages.register.alerts.error.title'),
                    text: errorMessages[error_code],
                    footer: t('auth.pages.register.alerts.error.footer'),
                    showConfirmButton: true,
                });
                throw new Error('Registration failed');
            } else {
                playError();
                Swal.fire({
                    icon: 'error',
                    title: t('auth.pages.register.alerts.error.title'),
                    text: response.message,
                    showConfirmButton: true,
                    footer: t('auth.pages.register.alerts.error.footer'),
                });
                throw new Error('Registration failed');
            }
        }
        playSuccess();
        Swal.fire({
            icon: 'success',
            title: t('auth.pages.register.alerts.success.title'),
            text: t('auth.pages.register.alerts.success.register_success'),
            footer: t('auth.pages.register.alerts.success.footer'),
            showConfirmButton: true,
        });
        setTimeout(() => {
            router.push('/auth/login');
        }, 1500);
    } catch (error) {
        console.error('Register failed:', error);
    } finally {
        loading.value = false;
    }
};
</script>
<template>
    <Layout>
        <FormCard :title="t('auth.pages.register.page.subTitle')" @submit="handleSubmit">
            <div class="flex space-x-4">
                <div class="w-1/2">
                    <FormInput
                        id="firstName"
                        :label="t('auth.pages.register.page.form.firstName.label')"
                        v-model="form.firstName"
                        :placeholder="t('auth.pages.register.page.form.firstName.placeholder')"
                        required
                    />
                    <div v-if="validationErrors.firstName" class="text-red-500 text-xs mt-1">
                        {{ validationErrors.firstName }}
                    </div>
                </div>
                <div class="w-1/2">
                    <FormInput
                        id="lastName"
                        :label="t('auth.pages.register.page.form.lastName.label')"
                        v-model="form.lastName"
                        :placeholder="t('auth.pages.register.page.form.lastName.placeholder')"
                        required
                    />
                    <div v-if="validationErrors.lastName" class="text-red-500 text-xs mt-1">
                        {{ validationErrors.lastName }}
                    </div>
                </div>
            </div>
            <div class="relative">
                <FormInput
                    id="username"
                    :label="t('auth.pages.register.page.form.username.label')"
                    v-model="form.username"
                    :placeholder="t('auth.pages.register.page.form.username.placeholder')"
                    required
                />
                <button
                    type="button"
                    @click="generateUsername"
                    class="absolute right-2 top-8 px-2 py-1 text-sm bg-purple-600 hover:bg-purple-700 text-white rounded transition-colors"
                >
                    {{ t('auth.pages.register.page.form.generate_username.label') }}
                </button>
                <div v-if="validationErrors.username" class="text-red-500 text-xs mt-1">
                    {{ validationErrors.username }}
                </div>
            </div>
            <div class="relative">
                <FormInput
                    id="email"
                    :label="t('auth.pages.register.page.form.email.label')"
                    v-model="form.email"
                    :placeholder="t('auth.pages.register.page.form.email.placeholder')"
                    type="email"
                    required
                />
                <div
                    v-if="showSuggestions && emailSuggestions.length > 0"
                    class="email-suggestions-container absolute left-full ml-2 top-0 w-48 bg-gray-800 rounded-md shadow-lg z-10"
                >
                    <ul class="py-1">
                        <li
                            v-for="suggestion in emailSuggestions"
                            :key="suggestion"
                            @click="
                                form.email = suggestion;
                                hideSuggestions();
                            "
                            class="px-4 py-2 text-sm text-gray-300 hover:bg-gray-700 cursor-pointer"
                        >
                            {{ suggestion }}
                        </li>
                    </ul>
                </div>
                <div v-if="validationErrors.email" class="text-red-500 text-xs mt-1">
                    {{ validationErrors.email }}
                </div>
            </div>
            <div class="flex items-center justify-between mb-2">
                <label class="block text-sm text-gray-400">{{
                    t('auth.pages.register.page.form.password.label')
                }}</label>
                <button type="button" @click="generatePassword" class="text-sm text-purple-400 hover:text-purple-300">
                    {{ t('auth.pages.register.page.form.generate_password.label') }}
                </button>
            </div>
            <FormInput
                id="password"
                type="password"
                v-model="form.password"
                :minlength="8"
                :placeholder="t('auth.pages.register.page.form.password.placeholder')"
                required
            />
            <div v-if="validationErrors.password" class="text-red-500 text-xs mt-1">
                {{ validationErrors.password }}
            </div>
            <div v-if="referralsEnabled == 'true'">
                <FormInput
                    id="referralCode"
                    :label="t('auth.pages.register.page.form.referralCode.label')"
                    v-model="form.referralCode"
                    :placeholder="t('auth.pages.register.page.form.referralCode.placeholder')"
                />
            </div>
            <div class="flex items-center mt-4">
                <input id="acceptTerms" type="checkbox" v-model="acceptTerms" required class="mr-2" />
                <label for="acceptTerms" class="text-sm text-gray-400">
                    {{ t('auth.pages.register.page.form.accept_terms.label') }}
                    <a
                        href="/terms-of-service"
                        target="_blank"
                        class="text-purple-400 hover:text-purple-300 underline mx-1"
                    >
                        {{ t('auth.pages.register.page.form.accept_terms.terms') }}
                    </a>
                    {{ t('auth.pages.register.page.form.accept_terms.and') }}
                    <a
                        href="/privacy-policy"
                        target="_blank"
                        class="text-purple-400 hover:text-purple-300 underline mx-1"
                    >
                        {{ t('auth.pages.register.page.form.accept_terms.privacy') }}
                    </a>
                </label>
            </div>
            <button
                type="submit"
                class="w-full mt-6 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors"
                :disabled="loading"
            >
                {{
                    loading
                        ? t('auth.pages.register.page.form.register_button.loading')
                        : t('auth.pages.register.page.form.register_button.label')
                }}
            </button>

            <div
                v-if="Settings.getSetting('turnstile_enabled') == 'true'"
                style="display: flex; justify-content: center; margin-top: 20px"
            >
                <Turnstile :site-key="Settings.getSetting('turnstile_key_pub')" v-model="form.turnstileResponse" />
            </div>

            <p class="mt-4 text-center text-sm text-gray-400">
                {{ t('auth.pages.register.page.form.login.label') }}
                <router-link to="/auth/login" class="text-purple-400 hover:text-purple-300">
                    {{ t('auth.pages.register.page.form.login.link') }}
                </router-link>
            </p>
        </FormCard>
    </Layout>
</template>
