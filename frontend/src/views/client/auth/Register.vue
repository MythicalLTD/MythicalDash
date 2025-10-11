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
const isDiscordLink = ref(false);
const isGithubLink = ref(false);
const isMailLink = ref(false);
const { t } = useI18n();

// Computed property to get all required account links
const requiredAccountLinks = computed(() => {
    const links = [];
    if (isDiscordLink.value) links.push('Discord');
    if (isGithubLink.value) links.push('GitHub');
    if (isMailLink.value) links.push('Email');
    return links;
});

// Computed property to check if any account linking is required
const hasAccountLinkingRequirements = computed(() => {
    return isDiscordLink.value || isGithubLink.value || isMailLink.value;
});

// Computed property to get the count of required links
const requiredLinksCount = computed(() => {
    return requiredAccountLinks.value.length;
});

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
const commonDomains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'proton.me', 'pm.me'];

const generateUsername = () => {
    const adjectives = ['happy', 'clever', 'brave', 'swift', 'bright', 'calm', 'eager', 'fair', 'kind', 'lively'];
    const nouns = ['panda', 'tiger', 'eagle', 'dolphin', 'wolf', 'phoenix', 'dragon', 'lion', 'bear', 'fox', 'cat'];
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

    if (Settings.getSetting('force_discord_link') == 'true') {
        isDiscordLink.value = true;
    }
    if (Settings.getSetting('force_github_link') == 'true') {
        isGithubLink.value = true;
    }
    if (Settings.getSetting('force_mail_link') == 'true') {
        isMailLink.value = true;
    }
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

        // Show account linking warning if required
        if (hasAccountLinkingRequirements.value) {
            setTimeout(() => {
                const linkTypes = requiredAccountLinks.value;
                let message = '';

                if (linkTypes.length === 1) {
                    message = t('auth.pages.register.alerts.warning.account_linking.message_single', {
                        type: linkTypes[0],
                    });
                } else if (linkTypes.length === 2) {
                    message = t('auth.pages.register.alerts.warning.account_linking.message_two', {
                        first: linkTypes[0],
                        second: linkTypes[1],
                    });
                } else {
                    message = t('auth.pages.register.alerts.warning.account_linking.message_multiple', {
                        types: linkTypes.join(', '),
                    });
                }

                Swal.fire({
                    icon: 'warning',
                    title: t('auth.pages.register.alerts.warning.account_linking.title'),
                    text: message,
                    footer: t('auth.pages.register.alerts.warning.account_linking.footer'),
                    showConfirmButton: true,
                    confirmButtonText: t('auth.pages.register.alerts.warning.account_linking.continue'),
                });
            }, 2000);
        }

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

            <!-- Account Linking Validation Errors -->
            <div v-if="validationErrors.discord" class="text-red-500 text-xs mt-1">
                {{ validationErrors.discord }}
            </div>
            <div v-if="validationErrors.github" class="text-red-500 text-xs mt-1">
                {{ validationErrors.github }}
            </div>
            <div v-if="referralsEnabled == 'true'">
                <FormInput
                    id="referralCode"
                    :label="t('auth.pages.register.page.form.referralCode.label')"
                    v-model="form.referralCode"
                    :placeholder="t('auth.pages.register.page.form.referralCode.placeholder')"
                />
            </div>
            <!-- Account Linking Requirements -->
            <div
                v-if="hasAccountLinkingRequirements"
                class="mt-6 p-4 bg-blue-900/20 border border-blue-500/30 rounded-lg"
            >
                <h3 class="text-lg font-medium text-blue-400 mb-3">
                    {{ t('auth.pages.register.page.form.account_linking.title') }}
                    <span class="text-sm text-gray-400 ml-2">
                        ({{ requiredLinksCount }} {{ requiredLinksCount === 1 ? 'requirement' : 'requirements' }})
                    </span>
                </h3>

                <!-- Discord Requirement -->
                <div v-if="isDiscordLink" class="mb-4 p-3 bg-gray-800/50 rounded-lg border border-gray-700">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <h4 class="font-medium text-white flex items-center">
                                <svg class="w-5 h-5 mr-2 text-indigo-400" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515a.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0a12.64 12.64 0 0 0-.617-1.25a.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057a19.9 19.9 0 0 0 5.993 3.03a.078.078 0 0 0 .084-.028a14.09 14.09 0 0 0 1.226-1.994a.076.076 0 0 0-.041-.106a13.107 13.107 0 0 1-1.872-.892a.077.077 0 0 1-.008-.128a10.2 10.2 0 0 0 .372-.292a.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127a12.299 12.299 0 0 1-1.873.892a.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028a19.839 19.839 0 0 0 6.002-3.03a.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419c0-1.333.956-2.419 2.157-2.419c1.21 0 2.176 1.096 2.157 2.42c0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419c0-1.333.955-2.419 2.157-2.419c1.21 0 2.176 1.096 2.157 2.42c0 1.333-.946 2.418-2.157 2.418z"
                                    />
                                </svg>
                                Discord Account
                            </h4>
                            <p class="text-sm text-gray-400 mt-1">
                                {{ t('auth.pages.register.page.form.account_linking.discord_description') }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- GitHub Requirement -->
                <div v-if="isGithubLink" class="mb-4 p-3 bg-gray-800/50 rounded-lg border border-gray-700">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <h4 class="font-medium text-white flex items-center">
                                <svg class="w-5 h-5 mr-2 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"
                                    />
                                </svg>
                                GitHub Account
                            </h4>
                            <p class="text-sm text-gray-400 mt-1">
                                {{ t('auth.pages.register.page.form.account_linking.github_description') }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Mail Requirement -->
                <div v-if="isMailLink" class="mb-4 p-3 bg-gray-800/50 rounded-lg border border-gray-700">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <h4 class="font-medium text-white flex items-center">
                                <svg class="w-5 h-5 mr-2 text-blue-400" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"
                                    />
                                </svg>
                                Email Verification
                            </h4>
                            <p class="text-sm text-gray-400 mt-1">
                                {{ t('auth.pages.register.page.form.account_linking.mail_description') }}
                            </p>
                        </div>
                    </div>
                </div>
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
