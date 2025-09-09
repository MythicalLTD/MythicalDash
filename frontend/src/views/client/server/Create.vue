<template>
    <LayoutDashboard>
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-100 mb-2">{{ t('create.pages.index.title') }}</h1>
                    <p class="text-gray-400">{{ t('create.pages.index.subTitle') }}</p>
                </div>
                <div
                    v-if="hasVipPermission"
                    class="flex items-center space-x-2 bg-gradient-to-r from-yellow-600/20 to-yellow-400/20 border border-yellow-500/30 rounded-lg px-3 py-2"
                >
                    <Crown class="w-5 h-5 text-yellow-400" />
                    <span class="text-yellow-400 font-medium">{{ t('create.pages.index.vip_access') }}</span>
                </div>
            </div>
        </div>

        <!-- Progress Steps -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div v-for="(step, index) in steps" :key="index" class="flex items-center">
                    <div
                        class="flex items-center justify-center w-10 h-10 rounded-full transition-all duration-200"
                        :class="[
                            currentStep > index
                                ? 'bg-indigo-500 text-white'
                                : currentStep === index
                                  ? 'bg-indigo-500 text-white ring-4 ring-indigo-500/20'
                                  : 'bg-[#1a1a2f] text-gray-400',
                        ]"
                    >
                        <span class="text-sm font-medium">{{ index + 1 }}</span>
                    </div>
                    <div
                        v-if="index < steps.length - 1"
                        class="w-24 h-0.5 mx-2"
                        :class="currentStep > index ? 'bg-indigo-500' : 'bg-[#1a1a2f]'"
                    ></div>
                </div>
            </div>
            <div class="flex justify-between mt-2">
                <span
                    v-for="(step, index) in steps"
                    :key="index"
                    class="text-sm font-medium"
                    :class="currentStep === index ? 'text-indigo-400' : 'text-gray-500'"
                >
                    {{ step }}
                </span>
            </div>
        </div>

        <!-- Step Content -->
        <form @submit.prevent="createServer">
            <!-- Step 1: Server Details -->
            <div v-if="currentStep === 0" class="animate-fade-in">
                <CardComponent card-title="Server Details" class="mb-6">
                    <div class="max-w-2xl mx-auto space-y-6">
                        <!-- Server Name -->
                        <div class="transform transition-all duration-300 hover:scale-[1.02]">
                            <label for="name" class="block text-lg font-medium text-gray-200 mb-2">
                                {{ t('create.pages.index.form.label') }}
                            </label>
                            <TextInput
                                id="name"
                                v-model="form.name"
                                :placeholder="t('create.pages.index.form.placeholder')"
                                class="w-full text-lg py-3"
                                required
                            />
                        </div>

                        <!-- Server Description -->
                        <div class="transform transition-all duration-300 hover:scale-[1.02]">
                            <label for="description" class="block text-lg font-medium text-gray-200 mb-2">
                                {{ t('create.pages.index.form.description') }}
                            </label>
                            <TextArea
                                id="description"
                                v-model="form.description"
                                :placeholder="t('create.pages.index.form.descriptionPlaceholder')"
                                :rows="4"
                                class="w-full text-lg"
                            />
                        </div>

                        <!-- Terms and Conditions -->
                        <div class="mt-8 p-4 bg-[#0a0a15]/50 rounded-lg border border-[#1a1a2f]/30">
                            <div class="flex items-start space-x-3">
                                <div class="flex items-center h-5">
                                    <input
                                        id="terms"
                                        v-model="form.acceptedTerms"
                                        type="checkbox"
                                        class="w-4 h-4 text-indigo-500 border-gray-600 rounded focus:ring-indigo-500 focus:ring-offset-gray-800"
                                        required
                                    />
                                </div>
                                <div class="text-sm text-gray-400">
                                    <label for="terms" class="font-medium text-gray-300">
                                        {{ t('create.pages.index.i_accept_the_terms_and_conditions') }}
                                    </label>
                                    <p class="mt-1">
                                        {{ t('create.pages.index.by_creating_a_server') }}
                                        <router-link
                                            to="/terms-of-service"
                                            target="_blank"
                                            class="text-indigo-400 hover:text-indigo-300"
                                            >{{ t('create.pages.index.terms_and_conditions') }}</router-link
                                        >
                                        {{ t('create.pages.index.and') }}
                                        <router-link
                                            to="/privacy-policy"
                                            target="_blank"
                                            class="text-indigo-400 hover:text-indigo-300"
                                            >{{ t('create.pages.index.privacy_policy') }}</router-link
                                        >. {{ t('create.pages.index.please_review_them_before_proceeding') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardComponent>
            </div>

            <!-- Step 2: Category Selection -->
            <div v-if="currentStep === 1" class="animate-fade-in">
                <CardComponent card-title="Select Category" class="mb-6">
                    <div v-if="categories.length === 0" class="text-center py-12 animate-fade-in">
                        <div class="w-16 h-16 mx-auto mb-4 text-gray-500">
                            <FolderOpen class="w-12 h-12 mx-auto" />
                        </div>
                        <h3 class="text-lg font-medium text-gray-300 mb-2">
                            {{ t('create.pages.index.no_category_found') }}
                        </h3>
                        <p class="text-gray-500">{{ t('create.pages.index.no_category_found_description') }}</p>
                    </div>
                    <div v-else class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div
                            v-for="category in categories"
                            :key="category.id"
                            class="relative group cursor-pointer transform transition-all duration-300 hover:scale-[1.02]"
                            @click="
                                form.category_id = category.id.toString();
                                updateEggs();
                            "
                        >
                            <div
                                class="relative overflow-hidden rounded-lg border transition-all duration-300"
                                :class="
                                    form.category_id === category.id.toString()
                                        ? 'border-indigo-500 ring-2 ring-indigo-500'
                                        : 'border-[#1a1a2f]/30 hover:border-indigo-500/50'
                                "
                            >
                                <img
                                    :src="category.image?.image || '/images/default-category.jpg'"
                                    :alt="category.name"
                                    class="w-full h-40 object-cover transform group-hover:scale-110 transition-transform duration-500"
                                />
                                <div
                                    class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                                ></div>
                                <div
                                    class="absolute bottom-0 left-0 right-0 p-4 transform translate-y-2 group-hover:translate-y-0 transition-transform duration-300"
                                >
                                    <div class="bg-black/50 backdrop-blur-sm rounded-lg p-3">
                                        <h3 class="text-white font-medium">{{ category.name }}</h3>
                                        <p class="text-gray-400 text-sm line-clamp-2">{{ category.description }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardComponent>
            </div>

            <!-- Step 3: Location Selection -->
            <div v-if="currentStep === 2" class="animate-fade-in">
                <CardComponent card-title="Select Location" class="mb-6">
                    <div v-if="locations.length === 0" class="text-center py-12 animate-fade-in">
                        <div class="w-16 h-16 mx-auto mb-4 text-gray-500">
                            <Server class="w-12 h-12 mx-auto" />
                        </div>
                        <h3 class="text-lg font-medium text-gray-300 mb-2">
                            {{ t('create.pages.index.no_location_found') }}
                        </h3>
                        <p class="text-gray-500">{{ t('create.pages.index.no_location_found_description') }}</p>
                    </div>
                    <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div
                            v-for="location in locations"
                            :key="location.id"
                            class="relative group cursor-pointer transform transition-all duration-300 hover:scale-[1.02]"
                            @click="form.location_id = location.id.toString()"
                        >
                            <div
                                class="relative overflow-hidden rounded-lg border transition-all duration-300"
                                :class="
                                    form.location_id === location.id.toString()
                                        ? 'border-indigo-500 ring-2 ring-indigo-500'
                                        : 'border-[#1a1a2f]/30 hover:border-indigo-500/50'
                                "
                            >
                                <img
                                    :src="location.image?.image || '/images/default-location.jpg'"
                                    :alt="location.name"
                                    class="w-full h-48 object-cover transform group-hover:scale-110 transition-transform duration-500"
                                />
                                <div
                                    class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                                ></div>
                                <div
                                    class="absolute bottom-0 left-0 right-0 p-4 transform translate-y-2 group-hover:translate-y-0 transition-transform duration-300"
                                >
                                    <div class="bg-black/50 backdrop-blur-sm rounded-lg p-3">
                                        <div class="flex items-center justify-between mb-2">
                                            <h3 class="text-white font-medium">{{ location.name }}</h3>
                                            <div
                                                v-if="location.vip_only === 'true'"
                                                class="flex items-center space-x-1"
                                            >
                                                <Crown class="w-4 h-4 text-yellow-400" />
                                                <span class="text-xs text-yellow-400 font-medium">{{
                                                    t('create.pages.index.vip_access')
                                                }}</span>
                                            </div>
                                        </div>
                                        <p class="text-gray-400 text-sm line-clamp-2">{{ location.description }}</p>
                                        <div class="flex items-center justify-between mt-2">
                                            <span class="text-xs text-gray-400">
                                                {{ location.used_slots }}/{{ location.slots }}
                                                {{ t('create.pages.index.slots') }}
                                            </span>
                                            <div class="flex items-center space-x-2">
                                                <span
                                                    class="text-xs px-2 py-0.5 rounded-full transform transition-all duration-300"
                                                    :class="
                                                        location.status === 'online'
                                                            ? 'bg-green-900/30 text-green-400'
                                                            : 'bg-red-900/30 text-red-400'
                                                    "
                                                >
                                                    {{ location.status }}
                                                </span>
                                                <span
                                                    v-if="locationPings[location.id] !== undefined"
                                                    class="text-xs px-2 py-0.5 rounded-full flex items-center space-x-1"
                                                    :class="
                                                        (locationPings[location.id] ?? -1) < 0
                                                            ? 'bg-red-900/30 text-red-400'
                                                            : (locationPings[location.id] ?? -1) < 100
                                                              ? 'bg-green-900/30 text-green-400'
                                                              : (locationPings[location.id] ?? -1) < 200
                                                                ? 'bg-yellow-900/30 text-yellow-400'
                                                                : 'bg-red-900/30 text-red-400'
                                                    "
                                                >
                                                    <Wifi class="w-3 h-3" />
                                                    <span>{{
                                                        (locationPings[location.id] ?? -1) < 0
                                                            ? 'N/A'
                                                            : `${locationPings[location.id]}ms`
                                                    }}</span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardComponent>
            </div>

            <!-- Step 4: Egg Selection -->
            <div v-if="currentStep === 3" class="animate-fade-in">
                <CardComponent card-title="Select Server Type" class="mb-6">
                    <div v-if="availableEggs.length === 0" class="text-center py-12 animate-fade-in">
                        <div class="w-16 h-16 mx-auto mb-4 text-gray-500">
                            <Box class="w-12 h-12 mx-auto" />
                        </div>
                        <h3 class="text-lg font-medium text-gray-300 mb-2">
                            {{ t('create.pages.index.no_egg_found') }}
                        </h3>
                        <p class="text-gray-500">{{ t('create.pages.index.no_egg_found_description') }}</p>
                    </div>
                    <div v-else class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div
                            v-for="egg in availableEggs"
                            :key="egg.id"
                            class="relative group cursor-pointer transform transition-all duration-300 hover:scale-[1.02]"
                            @click="form.egg_id = egg.id.toString()"
                        >
                            <div
                                class="relative overflow-hidden rounded-lg border transition-all duration-300"
                                :class="
                                    form.egg_id === egg.id.toString()
                                        ? 'border-indigo-500 ring-2 ring-indigo-500'
                                        : 'border-[#1a1a2f]/30 hover:border-indigo-500/50'
                                "
                            >
                                <img
                                    :src="egg.image?.image || '/images/default-egg.jpg'"
                                    :alt="egg.name"
                                    class="w-full h-40 object-cover transform group-hover:scale-110 transition-transform duration-500"
                                />
                                <div
                                    class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                                ></div>
                                <div
                                    class="absolute bottom-0 left-0 right-0 p-4 transform translate-y-2 group-hover:translate-y-0 transition-transform duration-300"
                                >
                                    <div class="bg-black/50 backdrop-blur-sm rounded-lg p-3">
                                        <div class="flex items-center justify-between mb-2">
                                            <h3 class="text-white font-medium">{{ egg.name }}</h3>
                                            <div v-if="egg.vip_only === 'true'" class="flex items-center space-x-1">
                                                <Crown class="w-4 h-4 text-yellow-400" />
                                                <span class="text-xs text-yellow-400 font-medium">{{
                                                    t('create.pages.index.vip_only')
                                                }}</span>
                                            </div>
                                        </div>
                                        <p class="text-gray-400 text-sm line-clamp-2">{{ egg.description }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardComponent>
            </div>

            <!-- Step 5: Resource Allocation -->
            <div v-if="currentStep === 4" class="animate-fade-in">
                <CardComponent card-title="Resource Allocation" class="mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Memory -->
                        <div
                            class="bg-[#0a0a15]/50 rounded-lg border border-[#1a1a2f]/30 p-4 transform transition-all duration-300 hover:scale-[1.02]"
                        >
                            <div class="flex items-center space-x-2 mb-4">
                                <div class="w-8 h-8 rounded-lg bg-indigo-500/10 flex items-center justify-center">
                                    <component :is="getResourceIcon('memory')" class="w-5 h-5 text-indigo-400" />
                                </div>
                                <div>
                                    <label for="memory" class="block text-sm font-medium text-gray-300">
                                        {{ t('create.pages.index.resources.memory') }}
                                    </label>
                                    <span class="text-xs text-gray-500"
                                        >({{ t('create.pages.index.form.available') }}: {{ resources.free.memory }}
                                        {{ t('create.pages.index.resources.mb') }})</span
                                    >
                                </div>
                            </div>
                            <TextInput id="memory" v-model="memoryModel" type="number" required />
                        </div>

                        <!-- CPU -->
                        <div
                            class="bg-[#0a0a15]/50 rounded-lg border border-[#1a1a2f]/30 p-4 transform transition-all duration-300 hover:scale-[1.02]"
                        >
                            <div class="flex items-center space-x-2 mb-4">
                                <div class="w-8 h-8 rounded-lg bg-indigo-500/10 flex items-center justify-center">
                                    <component :is="getResourceIcon('cpu')" class="w-5 h-5 text-indigo-400" />
                                </div>
                                <div>
                                    <label for="cpu" class="block text-sm font-medium text-gray-300">
                                        {{ t('create.pages.index.resources.cpu') }}
                                    </label>
                                    <span class="text-xs text-gray-500"
                                        >({{ t('create.pages.index.form.available') }}: {{ resources.free.cpu }}
                                        {{ t('create.pages.index.resources.p') }})</span
                                    >
                                </div>
                            </div>
                            <TextInput id="cpu" v-model="cpuModel" type="number" required />
                        </div>

                        <!-- Disk -->
                        <div
                            class="bg-[#0a0a15]/50 rounded-lg border border-[#1a1a2f]/30 p-4 transform transition-all duration-300 hover:scale-[1.02]"
                        >
                            <div class="flex items-center space-x-2 mb-4">
                                <div class="w-8 h-8 rounded-lg bg-indigo-500/10 flex items-center justify-center">
                                    <component :is="getResourceIcon('disk')" class="w-5 h-5 text-indigo-400" />
                                </div>
                                <div>
                                    <label for="disk" class="block text-sm font-medium text-gray-300">
                                        {{ t('create.pages.index.resources.disk') }}
                                    </label>
                                    <span class="text-xs text-gray-500"
                                        >({{ t('create.pages.index.form.available') }}: {{ resources.free.disk }}
                                        {{ t('create.pages.index.resources.mb') }})</span
                                    >
                                </div>
                            </div>
                            <TextInput id="disk" v-model="diskModel" type="number" required />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">
                        <!-- Databases -->
                        <div
                            class="bg-[#0a0a15]/50 rounded-lg border border-[#1a1a2f]/30 p-4 transform transition-all duration-300 hover:scale-[1.02]"
                        >
                            <div class="flex items-center space-x-2 mb-4">
                                <div class="w-8 h-8 rounded-lg bg-indigo-500/10 flex items-center justify-center">
                                    <component :is="getResourceIcon('databases')" class="w-5 h-5 text-indigo-400" />
                                </div>
                                <div>
                                    <label for="databases" class="block text-sm font-medium text-gray-300">
                                        {{ t('create.pages.index.resources.databases') }}
                                    </label>
                                    <span class="text-xs text-gray-500"
                                        >({{ t('create.pages.index.form.available') }}:
                                        {{ resources.free.databases }})</span
                                    >
                                </div>
                            </div>
                            <TextInput id="databases" v-model="databasesModel" type="number" required />
                        </div>

                        <!-- Backups -->
                        <div
                            class="bg-[#0a0a15]/50 rounded-lg border border-[#1a1a2f]/30 p-4 transform transition-all duration-300 hover:scale-[1.02]"
                        >
                            <div class="flex items-center space-x-2 mb-4">
                                <div class="w-8 h-8 rounded-lg bg-indigo-500/10 flex items-center justify-center">
                                    <component :is="getResourceIcon('backups')" class="w-5 h-5 text-indigo-400" />
                                </div>
                                <div>
                                    <label for="backups" class="block text-sm font-medium text-gray-300">
                                        {{ t('create.pages.index.resources.backups') }}
                                    </label>
                                    <span class="text-xs text-gray-500"
                                        >({{ t('create.pages.index.form.available') }}:
                                        {{ resources.free.backups }})</span
                                    >
                                </div>
                            </div>
                            <TextInput id="backups" v-model="backupsModel" type="number" required />
                        </div>

                        <!-- Allocations -->
                        <div
                            class="bg-[#0a0a15]/50 rounded-lg border border-[#1a1a2f]/30 p-4 transform transition-all duration-300 hover:scale-[1.02]"
                        >
                            <div class="flex items-center space-x-2 mb-4">
                                <div class="w-8 h-8 rounded-lg bg-indigo-500/10 flex items-center justify-center">
                                    <component :is="getResourceIcon('allocations')" class="w-5 h-5 text-indigo-400" />
                                </div>
                                <div>
                                    <label for="allocations" class="block text-sm font-medium text-gray-300">
                                        {{ t('create.pages.index.resources.allocations') }}
                                    </label>
                                    <span class="text-xs text-gray-500"
                                        >({{ t('create.pages.index.form.available') }}:
                                        {{ resources.free.allocations }})</span
                                    >
                                </div>
                            </div>
                            <TextInput id="allocations" v-model="allocationsModel" type="number" required />
                        </div>
                    </div>
                </CardComponent>
            </div>

            <!-- Navigation Buttons -->
            <div class="flex justify-between mt-6">
                <Button
                    v-if="currentStep > 0"
                    type="button"
                    :text="t('create.pages.index.previous')"
                    @click="currentStep--"
                    class="bg-[#1a1a2f] hover:bg-[#1a1a2f]/80 transform transition-all duration-300 hover:scale-105"
                />
                <div class="flex space-x-4">
                    <Button
                        v-if="currentStep < steps.length - 1"
                        type="button"
                        :text="t('create.pages.index.next')"
                        @click="nextStep"
                        :disabled="!canProceed"
                        class="transform transition-all duration-300 hover:scale-105"
                    />
                    <Button
                        v-else
                        type="submit"
                        :text="t('create.pages.index.create_server')"
                        :disabled="!canCreateServer"
                        :loading="isSubmitting"
                        class="transform transition-all duration-300 hover:scale-105"
                    />
                </div>
            </div>
        </form>
    </LayoutDashboard>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import LayoutDashboard from '@/components/client/LayoutDashboard.vue';
import CardComponent from '@/components/client/ui/Card/CardComponent.vue';
import { TextInput, TextArea } from '@/components/client/ui/TextForms';
import Button from '@/components/client/ui/Button.vue';
import Swal from 'sweetalert2';
import { MythicalDOM } from '@/mythicaldash/MythicalDOM';
import { useSound } from '@vueuse/sound';
import failedAlertSfx from '@/assets/sounds/error.mp3';
import successAlertSfx from '@/assets/sounds/success.mp3';
import { useI18n } from 'vue-i18n';
import { useSettingsStore } from '@/stores/settings';
import { Cpu, HardDrive, Database, Archive, Server, FolderOpen, Box, Wifi, Crown } from 'lucide-vue-next';

const { t } = useI18n();
const Settings = useSettingsStore();

const { play: playError } = useSound(failedAlertSfx);
const { play: playSuccess } = useSound(successAlertSfx);

MythicalDOM.setPageTitle(t('create.pages.index.title'));

const router = useRouter();
const isSubmitting = ref(false);

// Define interfaces for the API data

interface Image {
    id: number;
    name: string;
    image: string;
}

interface Location {
    id: number;
    name: string;
    description: string;
    slots: number;
    used_slots: number;
    pterodactyl_location_id: number;
    node_ip: string;
    status: string;
    deleted: string;
    vip_only: string;
    image: Image;
    locked: string;
    updated_at: string;
    created_at: string;
}

interface Egg {
    id: number;
    name: string;
    description: string;
    category: number;
    pterodactyl_egg_id: number;
    enabled: string;
    deleted: string;
    locked: string;
    image: Image;
    vip_only: string;
    updated_at: string;
    created_at: string;
}

interface Category {
    id: number;
    name: string;
    description: string;
    pterodactyl_nest_id: number;
    enabled: string;
    deleted: string;
    locked: string;
    image: Image;
    updated_at: string;
    created_at: string;
    eggs: Egg[];
}

interface ResourceLimits {
    memory: number;
    cpu: number;
    disk: number;
    backups: number;
    databases: number;
    allocations: number;
    servers: number;
}

function convertToNumber(value: string): number {
    return parseInt(value);
}

function convertToString(value: number): string {
    return value.toString();
}

// Server creation data
const locations = ref<Location[]>([]);
const categories = ref<Category[]>([]);
const availableEggs = ref<Egg[]>([]);
const hasVipPermission = ref(false);
const resources = reactive<{
    used: ResourceLimits;
    total: ResourceLimits;
    free: ResourceLimits;
}>({
    used: {
        memory: 0,
        cpu: 0,
        disk: 0,
        backups: 0,
        databases: 0,
        allocations: 0,
        servers: 0,
    },
    total: {
        memory: 0,
        cpu: 0,
        disk: 0,
        backups: 0,
        databases: 0,
        allocations: 0,
        servers: 0,
    },
    free: {
        memory: 0,
        cpu: 0,
        disk: 0,
        backups: 0,
        databases: 0,
        allocations: 0,
        servers: 0,
    },
});

// Form data
const form = reactive({
    name: '',
    description: '',
    location_id: '',
    category_id: '',
    egg_id: '',
    memory: 1024,
    cpu: 100,
    disk: 1024,
    databases: 1,
    backups: 1,
    allocations: 1,
    acceptedTerms: false,
});

// Create computed properties for number inputs to handle string-number conversion
const memoryModel = computed({
    get: () => convertToString(form.memory),
    set: (value: string) => {
        form.memory = convertToNumber(value);
    },
});

const cpuModel = computed({
    get: () => convertToString(form.cpu),
    set: (value: string) => {
        form.cpu = convertToNumber(value);
    },
});

const diskModel = computed({
    get: () => convertToString(form.disk),
    set: (value: string) => {
        form.disk = convertToNumber(value);
    },
});

const databasesModel = computed({
    get: () => convertToString(form.databases),
    set: (value: string) => {
        form.databases = convertToNumber(value);
    },
});

const backupsModel = computed({
    get: () => convertToString(form.backups),
    set: (value: string) => {
        form.backups = convertToNumber(value);
    },
});

const allocationsModel = computed({
    get: () => convertToString(form.allocations),
    set: (value: string) => {
        form.allocations = convertToNumber(value);
    },
});

// Add ping calculation function
const calculatePing = async (ip: string): Promise<number> => {
    try {
        const startTime = performance.now();
        const response = await fetch(`/api/system/ping?host=${ip}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            },
        });
        const endTime = performance.now();

        if (!response.ok) {
            return -1;
        }

        const data = await response.json();
        return data.ping || Math.round(endTime - startTime);
    } catch (error) {
        console.error('Error calculating ping:', error);
        return -1;
    }
};

// Add ping state
const locationPings = reactive<Record<number, number>>({});

// Modify the onMounted function to calculate pings
onMounted(async () => {
    try {
        const response = await fetch('/api/user/server/create', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            },
        });

        if (!response.ok) {
            playError();
            Swal.fire({
                icon: 'error',
                title: t('create.pages.alerts.error.title'),
                text: t('create.pages.alerts.error.generic'),
                showConfirmButton: true,
            });
            throw new Error('Failed to load server creation data');
        }

        const data = await response.json();

        if (data.success) {
            locations.value = data.locations || [];
            categories.value = data.categories || [];
            hasVipPermission.value = data.has_vip_permission || false;
            // Set resource limits
            if (data.used_resources) resources.used = data.used_resources;
            if (data.total_resources) resources.total = data.total_resources;
            if (data.free_resources) resources.free = data.free_resources;

            // Calculate pings for all locations
            for (const location of locations.value) {
                locationPings[location.id] = await calculatePing(location.node_ip);
            }

            // Set default values based on available resources
            form.memory = Math.min(1024, resources.free.memory);
            form.cpu = Math.min(100, resources.free.cpu);
            form.disk = Math.min(1024, resources.free.disk);
            form.databases = Math.min(1, resources.free.databases);
            form.backups = Math.min(1, resources.free.backups);
            form.allocations = Math.min(1, resources.free.allocations);
        } else {
            playError();
            Swal.fire({
                icon: 'error',
                title: t('create.pages.alerts.error.title'),
                text: data.message || t('create.pages.alerts.error.generic'),
                showConfirmButton: true,
            });
        }
    } catch (error) {
        console.error('Error loading server creation data:', error);
    }
});

// Update available eggs when category changes
const updateEggs = () => {
    const category = categories.value.find((c) => c.id === parseInt(form.category_id));
    availableEggs.value = category ? category.eggs || [] : [];
    form.egg_id = ''; // Reset egg selection
};

// Check if server can be created
const canCreateServer = computed(() => {
    return (
        form.name &&
        form.location_id &&
        form.category_id &&
        form.egg_id &&
        form.memory > 0 &&
        form.memory <= resources.free.memory &&
        form.cpu > 0 &&
        form.cpu <= resources.free.cpu &&
        form.disk > 0 &&
        form.disk <= resources.free.disk &&
        form.databases >= 0 &&
        form.databases <= resources.free.databases &&
        form.backups >= 0 &&
        form.backups <= resources.free.backups &&
        form.allocations > 0 &&
        form.allocations <= resources.free.allocations &&
        resources.free.servers > 0 &&
        Settings.getSetting('allow_servers') === 'true' &&
        (() => {
            const location = locations.value.find((location) => location.id === parseInt(form.location_id));
            return location ? location.used_slots < location.slots : false;
        })()
    );
});

// Create server
const createServer = async () => {
    if (!canCreateServer.value) {
        playError();
        Swal.fire({
            icon: 'error',
            title: t('create.pages.alerts.error.title'),
            text: t('create.pages.alerts.error.generic'),
            showConfirmButton: true,
        });
        return;
    }

    isSubmitting.value = true;

    try {
        const formData = new URLSearchParams();
        formData.append('name', form.name);
        formData.append('description', form.description);
        formData.append('location_id', form.location_id);
        formData.append('category_id', form.category_id);
        formData.append('egg_id', form.egg_id);
        formData.append('memory', form.memory.toString());
        formData.append('cpu', form.cpu.toString());
        formData.append('disk', form.disk.toString());
        formData.append('databases', form.databases.toString());
        formData.append('backups', form.backups.toString());
        formData.append('allocations', form.allocations.toString());

        const response = await fetch('/api/user/server/create', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: formData,
        });

        const data = await response.json();

        if (data.success) {
            playSuccess();
            Swal.fire({
                icon: 'success',
                title: t('create.pages.alerts.success.title'),
                text: t('create.pages.alerts.success.generic'),
                footer: t('create.pages.alerts.success.footer'),
                showConfirmButton: true,
            }).then(() => {
                router.push('/dashboard'); // Redirect to servers list
            });
            router.push('/dashboard');
        } else {
            const errorCode = data.error_code as keyof typeof errorMessages;
            const errorMessages = {
                MISSING_REQUIRED_FIELDS: t('create.pages.alerts.error.deploy.MISSING_REQUIRED_FIELDS'),
                NAME_TOO_LONG: t('create.pages.alerts.error.deploy.NAME_TOO_LONG'),
                DESCRIPTION_TOO_LONG: t('create.pages.alerts.error.deploy.DESCRIPTION_TOO_LONG'),
                LOCATION_DOES_NOT_EXIST: t('create.pages.alerts.error.deploy.LOCATION_DOES_NOT_EXIST'),
                CATEGORY_DOES_NOT_EXIST: t('create.pages.alerts.error.deploy.CATEGORY_DOES_NOT_EXIST'),
                EGG_DOES_NOT_EXIST: t('create.pages.alerts.error.deploy.EGG_DOES_NOT_EXIST'),
                MEMORY_TOO_LOW: t('create.pages.alerts.error.deploy.MEMORY_TOO_LOW'),
                CPU_TOO_LOW: t('create.pages.alerts.error.deploy.CPU_TOO_LOW'),
                DISK_TOO_LOW: t('create.pages.alerts.error.deploy.DISK_TOO_LOW'),
                ALLOCATIONS_TOO_LOW: t('create.pages.alerts.error.deploy.ALLOCATIONS_TOO_LOW'),
                PENDING_SERVER_CREATION_REQUEST: 'You already have a pending server creation request',
                NOT_ENOUGH_MEMORY: 'You do not have enough memory resources',
                NOT_ENOUGH_DISK_SPACE: t('create.pages.alerts.error.deploy.NOT_ENOUGH_DISK_SPACE'),
                NOT_ENOUGH_CPU: t('create.pages.alerts.error.deploy.NOT_ENOUGH_CPU'),
                NOT_ENOUGH_DATABASES: t('create.pages.alerts.error.deploy.NOT_ENOUGH_DATABASES'),
                NOT_ENOUGH_BACKUPS: t('create.pages.alerts.error.deploy.NOT_ENOUGH_BACKUPS'),
                NOT_ENOUGH_ALLOCATIONS: t('create.pages.alerts.error.deploy.NOT_ENOUGH_ALLOCATIONS'),
                NOT_ENOUGH_SERVERS: t('create.pages.alerts.error.deploy.NOT_ENOUGH_SERVERS'),
                FAILED_TO_CREATE_SERVER_QUEUE_ITEM: t(
                    'create.pages.alerts.error.deploy.FAILED_TO_CREATE_SERVER_QUEUE_ITEM',
                ),
                LOCATION_VIP_ONLY: t('create.pages.alerts.error.deploy.LOCATION_VIP_ONLY'),
                EGG_VIP_ONLY: t('create.pages.alerts.error.deploy.EGG_VIP_ONLY'),
            };

            playError();
            Swal.fire({
                icon: 'error',
                title: t('create.pages.alerts.error.title'),
                text: errorMessages[errorCode] || data.message || t('create.pages.alerts.error.generic'),
                footer: t('create.pages.alerts.error.footer'),
                showConfirmButton: true,
            });
        }
    } catch (error) {
        console.error('Error creating server:', error);
        playError();
        Swal.fire({
            icon: 'error',
            title: t('create.pages.alerts.error.title'),
            text: t('create.pages.alerts.error.generic'),
            footer: t('create.pages.alerts.error.footer'),
            showConfirmButton: true,
        });
    } finally {
        isSubmitting.value = false;
    }
};

const currentStep = ref(0);
const steps = [
    t('create.pages.index.server_details'),
    t('create.pages.index.select_category'),
    t('create.pages.index.select_location'),
    t('create.pages.index.select_server_type'),
    t('create.pages.index.resource_allocation'),
];

// Get resource icon based on type
const getResourceIcon = (type: keyof ResourceLimits) => {
    const icons = {
        memory: Cpu,
        cpu: Cpu,
        disk: HardDrive,
        databases: Database,
        backups: Archive,
        allocations: Server,
        servers: Server,
    };
    return icons[type] || Server;
};

// Check if can proceed to next step
const canProceed = computed(() => {
    switch (currentStep.value) {
        case 0:
            return form.name.trim() !== '' && form.acceptedTerms;
        case 1:
            return form.category_id !== '';
        case 2:
            return form.location_id !== '';
        case 3:
            return form.egg_id !== '';
        default:
            return true;
    }
});

// Navigate to next step
const nextStep = () => {
    if (currentStep.value < steps.length - 1) {
        currentStep.value++;
    }
};
</script>
