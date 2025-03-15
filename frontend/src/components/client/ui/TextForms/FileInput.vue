<template>
    <div class="w-full">
        <label v-if="label" :for="id" class="block text-sm font-medium text-gray-300 mb-1">{{ label }}</label>

        <!-- File Drop Zone -->
        <div
            class="relative"
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="onDrop"
        >
            <input
                :id="id"
                ref="fileInput"
                type="file"
                class="sr-only"
                :accept="accept"
                :multiple="multiple"
                :disabled="disabled"
                @change="onFileChange"
            />

            <div
                :class="[
                    'flex flex-col items-center justify-center w-full p-6 border-2 border-dashed rounded-lg cursor-pointer transition-all duration-200',
                    isDragging
                        ? 'border-indigo-500 bg-indigo-500/10'
                        : 'border-[#2a2a3f] bg-[#1a1a2e]/50 hover:bg-[#1a1a2e]/70 hover:border-[#2a2a3f]/70',
                    disabled ? 'opacity-50 cursor-not-allowed' : '',
                ]"
                @click="!disabled && fileInput?.click()"
            >
                <div class="flex flex-col items-center justify-center text-center">
                    <UploadCloudIcon class="w-10 h-10 mb-3 text-gray-400" />

                    <p class="mb-2 text-sm text-gray-300">
                        <span class="font-semibold">{{ $t('components.fileInput.click_to_upload') }}</span>
                        {{ $t('components.fileInput.or_drag_and_drop') }}
                    </p>

                    <p class="text-xs text-gray-500">
                        {{ acceptDescription || $t('components.fileInput.supported_formats') }}
                    </p>

                    <p v-if="maxSize" class="text-xs text-gray-500 mt-1">
                        {{ $t('components.fileInput.max_size') }}: {{ formatFileSize(maxSize) }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Selected Files Preview -->
        <div v-if="selectedFiles.length > 0" class="mt-3 space-y-2">
            <div
                v-for="(file, index) in selectedFiles"
                :key="index"
                class="flex items-center justify-between p-3 bg-[#12121f] border border-[#2a2a3f]/30 rounded-lg"
            >
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                        <FileIcon v-if="!isImage(file)" class="w-8 h-8 text-indigo-400" />
                        <img
                            v-else
                            :src="getFilePreview(file)"
                            class="w-8 h-8 object-cover rounded-md border border-[#2a2a3f]/30"
                            alt="Preview"
                        />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-300 truncate">{{ file.name }}</p>
                        <p class="text-xs text-gray-500">{{ formatFileSize(file.size) }}</p>
                    </div>
                </div>
                <button
                    @click="removeFile(index)"
                    type="button"
                    class="p-1 rounded-full hover:bg-[#2a2a3f]/30 text-gray-400 hover:text-gray-200 transition-colors duration-200"
                >
                    <XIcon class="w-5 h-5" />
                </button>
            </div>
        </div>

        <!-- Error Message -->
        <p v-if="errorMessage" class="mt-2 text-sm text-red-400">{{ errorMessage }}</p>

        <!-- Help Text -->
        <p v-if="helpText" class="mt-2 text-sm text-gray-500">{{ helpText }}</p>
    </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import { UploadCloud as UploadCloudIcon, File as FileIcon, X as XIcon } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps({
    modelValue: {
        type: Array as () => File[],
        default: () => [],
    },
    label: {
        type: String,
        default: '',
    },
    accept: {
        type: String,
        default: '',
    },
    acceptDescription: {
        type: String,
        default: '',
    },
    multiple: {
        type: Boolean,
        default: false,
    },
    maxSize: {
        type: Number,
        default: 0, // in bytes, 0 means no limit
    },
    maxFiles: {
        type: Number,
        default: 0, // 0 means no limit
    },
    helpText: {
        type: String,
        default: '',
    },
    id: {
        type: String,
        default: () => `file-input-${Math.random().toString(36).substring(2, 9)}`,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:modelValue', 'error']);

const fileInput = ref<HTMLInputElement | null>(null);
const isDragging = ref(false);
const selectedFiles = ref<File[]>([]);
const errorMessage = ref('');

// Initialize selectedFiles from modelValue
watch(
    () => props.modelValue,
    (newValue) => {
        if (newValue && Array.isArray(newValue)) {
            selectedFiles.value = newValue;
        }
    },
    { immediate: true },
);

// Update modelValue when selectedFiles changes
watch(
    selectedFiles,
    (newValue) => {
        emit('update:modelValue', newValue);
    },
    { deep: true },
);

const onFileChange = (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (input.files) {
        addFiles(Array.from(input.files));
    }
};

const onDrop = (event: DragEvent) => {
    isDragging.value = false;
    if (event.dataTransfer?.files) {
        addFiles(Array.from(event.dataTransfer.files));
    }
};

const addFiles = (files: File[]) => {
    errorMessage.value = '';

    // Check max files limit
    if (props.maxFiles > 0 && selectedFiles.value.length + files.length > props.maxFiles) {
        errorMessage.value = t('components.fileInput.max_files_error', { max: props.maxFiles });
        emit('error', errorMessage.value);
        return;
    }

    // Process each file
    for (const file of files) {
        // Check file size
        if (props.maxSize > 0 && file.size > props.maxSize) {
            errorMessage.value = t('components.fileInput.max_size_error', {
                filename: file.name,
                maxSize: formatFileSize(props.maxSize),
            });
            emit('error', errorMessage.value);
            continue;
        }

        // Check file type if accept is specified
        if (props.accept && !isFileTypeAccepted(file)) {
            errorMessage.value = t('components.fileInput.file_type_error', { filename: file.name });
            emit('error', errorMessage.value);
            continue;
        }

        // Add file to selected files
        if (props.multiple) {
            selectedFiles.value.push(file);
        } else {
            selectedFiles.value = [file];
        }
    }

    // Reset the input to allow selecting the same file again
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const removeFile = (index: number) => {
    selectedFiles.value.splice(index, 1);
    errorMessage.value = '';
};

const isFileTypeAccepted = (file: File): boolean => {
    if (!props.accept) return true;

    const acceptedTypes = props.accept.split(',').map((type) => type.trim());
    const fileType = file.type;
    const fileExtension = `.${file.name.split('.').pop()?.toLowerCase()}`;

    return acceptedTypes.some((type) => {
        if (type.startsWith('.')) {
            // Check file extension
            return fileExtension === type.toLowerCase();
        } else if (type.includes('/*')) {
            // Check MIME type group (e.g., image/*)
            const typeGroup = type.split('/')[0];
            return fileType.startsWith(`${typeGroup}/`);
        } else {
            // Check exact MIME type
            return fileType === type;
        }
    });
};

const formatFileSize = (bytes: number): string => {
    if (bytes === 0) return '0 Bytes';

    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));

    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const isImage = (file: File): boolean => {
    return file.type.startsWith('image/');
};

const getFilePreview = (file: File): string => {
    if (isImage(file)) {
        return URL.createObjectURL(file);
    }
    return '';
};
</script>

<style scoped>
/* Smooth transitions */
.transition-all {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 200ms;
}

/* Focus styles */
:focus {
    outline: none;
}
</style>
