<template>
    <div>
        <h2 class="text-xl font-semibold text-white mb-4">Image Hosting Settings</h2>

        <div class="space-y-6">
            <!-- Enable Image Hosting -->
            <div class="flex items-center justify-between p-4 bg-gray-800/30 border border-gray-700 rounded-lg">
                <div>
                    <label for="image_hosting_enabled" class="block text-sm font-medium text-white">
                        Enable Image Hosting
                    </label>
                    <p class="mt-1 text-xs text-gray-400">Enable or disable the image hosting feature globally</p>
                </div>
                <div class="flex items-center">
                    <input
                        id="image_hosting_enabled"
                        type="checkbox"
                        v-model="formData.image_hosting_enabled"
                        @change="markChanged('image_hosting_enabled')"
                        class="w-4 h-4 text-pink-500 border-gray-600 rounded focus:ring-pink-500 focus:ring-offset-gray-800 bg-gray-700"
                    />
                </div>
            </div>

            <!-- Coins Per Image -->
            <div class="space-y-4">
                <!-- Enable Coins Per Image -->
                <div class="flex items-center justify-between p-4 bg-gray-800/30 border border-gray-700 rounded-lg">
                    <div>
                        <label for="image_hosting_coins_per_image_enabled" class="block text-sm font-medium text-white">
                            Enable Coins Per Image
                        </label>
                        <p class="mt-1 text-xs text-gray-400">Charge users coins for each image upload</p>
                    </div>
                    <div class="flex items-center">
                        <input
                            id="image_hosting_coins_per_image_enabled"
                            type="checkbox"
                            v-model="formData.image_hosting_coins_per_image_enabled"
                            @change="markChanged('image_hosting_coins_per_image_enabled')"
                            class="w-4 h-4 text-pink-500 border-gray-600 rounded focus:ring-pink-500 focus:ring-offset-gray-800 bg-gray-700"
                        />
                    </div>
                </div>

                <!-- Coins Amount -->
                <div class="p-4 bg-gray-800/30 border border-gray-700 rounded-lg">
                    <div class="flex flex-col">
                        <label for="image_hosting_coins_per_image" class="block text-sm font-medium text-white mb-1">
                            Coins Per Image
                        </label>
                        <p class="text-xs text-gray-400 mb-3">Number of coins charged per image upload</p>
                        <input
                            id="image_hosting_coins_per_image"
                            type="number"
                            min="0"
                            v-model="formData.image_hosting_coins_per_image"
                            @change="markChanged('image_hosting_coins_per_image')"
                            class="bg-gray-700/50 border border-gray-600 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500 text-white"
                        />
                    </div>
                </div>
            </div>

            <!-- Max File Size -->
            <div class="p-4 bg-gray-800/30 border border-gray-700 rounded-lg">
                <div class="flex flex-col">
                    <label for="image_hosting_max_file_size" class="block text-sm font-medium text-white mb-1">
                        Maximum File Size (MB)
                    </label>
                    <p class="text-xs text-gray-400 mb-3">Maximum allowed file size for image uploads in kilobytes</p>
                    <input
                        id="image_hosting_max_file_size"
                        type="number"
                        min="1"
                        v-model="formData.image_hosting_max_file_size"
                        @change="markChanged('image_hosting_max_file_size')"
                        class="bg-gray-700/50 border border-gray-600 rounded-lg px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-pink-500 text-white"
                    />
                </div>
            </div>

            <!-- Allow Domains -->
            <div class="flex items-center justify-between p-4 bg-gray-800/30 border border-gray-700 rounded-lg">
                <div>
                    <label for="image_hosting_allow_domains" class="block text-sm font-medium text-white">
                        Allow External Domains
                    </label>
                    <p class="mt-1 text-xs text-gray-400">Allow users to upload images from external domains</p>
                </div>
                <div class="flex items-center">
                    <input
                        id="image_hosting_allow_domains"
                        type="checkbox"
                        v-model="formData.image_hosting_allow_domains"
                        @change="markChanged('image_hosting_allow_domains')"
                        class="w-4 h-4 text-pink-500 border-gray-600 rounded focus:ring-pink-500 focus:ring-offset-gray-800 bg-gray-700"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';

// Props
const props = defineProps<{
    settings: Record<string, string>;
}>();

// Emits
const emit = defineEmits<{
    update: [key: string, value: string];
    'bulk-update': [updates: Record<string, string>];
}>();

// Initialize form data with default values
const formData = ref({
    image_hosting_enabled: 'false',
    image_hosting_coins_per_image_enabled: 'false',
    image_hosting_coins_per_image: '1',
    image_hosting_max_file_size: '1024',
    image_hosting_allow_domains: 'false',
});

// Track changed fields
const changedFields = ref<Set<string>>(new Set());

// Mark a field as changed
const markChanged = (field: string) => {
    changedFields.value.add(field);

    // Emit the change to parent
    const value = formData.value[field as keyof typeof formData.value];
    emit('update', field, value);
};

// Update form data when settings prop changes
watch(
    () => props.settings,
    (newSettings) => {
        formData.value = {
            image_hosting_enabled: newSettings.image_hosting_enabled ?? 'false',
            image_hosting_coins_per_image_enabled: newSettings.image_hosting_coins_per_image_enabled ?? 'false',
            image_hosting_coins_per_image: newSettings.image_hosting_coins_per_image ?? '1',
            image_hosting_max_file_size: newSettings.image_hosting_max_file_size ?? '1024',
            image_hosting_allow_domains: newSettings.image_hosting_allow_domains ?? 'false',
        };

        // Clear changed fields when settings are loaded
        changedFields.value.clear();
    },
    { immediate: true },
);
</script>
