<script setup lang="ts">
import { ref, onMounted, computed, onBeforeUnmount, watch, nextTick } from 'vue';
import { ChevronDown, Check, Search } from 'lucide-vue-next';

const props = defineProps({
    modelValue: String,
    options: {
        type: Array as () => Array<{ value: string; label: string }>,
        default: () => [],
    },
    inputClass: {
        type: String,
        default: '',
    },
    placeholder: {
        type: String,
        default: 'Select an option',
    },
    searchable: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:modelValue']);

const inputValue = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

const searchQuery = ref('');
const isDropdownOpen = ref(false);
const searchInputRef = ref<HTMLInputElement | null>(null);
const optionsRef = ref<HTMLUListElement | null>(null);
const selectedIndex = ref(-1);

const filteredOptions = computed(() => {
    if (!props.searchable || !searchQuery.value) {
        return props.options;
    }
    return props.options.filter((option) => option.label.toLowerCase().includes(searchQuery.value.toLowerCase()));
});

const selectedOption = computed(() => {
    return props.options.find((option) => option.value === inputValue.value);
});

onMounted(() => {
    if (!props.modelValue && props.options.length > 0) {
        emit('update:modelValue', props.options[0].value);
    }

    document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
});

const handleClickOutside = (event: MouseEvent) => {
    const target = event.target as HTMLElement;
    if (!target.closest('.dropdown-container')) {
        isDropdownOpen.value = false;
        searchQuery.value = '';
    }
};

const toggleDropdown = () => {
    isDropdownOpen.value = !isDropdownOpen.value;
    if (isDropdownOpen.value && props.searchable) {
        nextTick(() => {
            searchInputRef.value?.focus();
        });
    }
};

const selectOption = (option: { value: string; label: string }) => {
    emit('update:modelValue', option.value);
    isDropdownOpen.value = false;
    searchQuery.value = '';
};

const handleKeydown = (event: KeyboardEvent) => {
    if (!isDropdownOpen.value) {
        if (event.key === 'Enter' || event.key === ' ' || event.key === 'ArrowDown') {
            event.preventDefault();
            isDropdownOpen.value = true;
        }
        return;
    }

    switch (event.key) {
        case 'Escape':
            isDropdownOpen.value = false;
            searchQuery.value = '';
            break;
        case 'ArrowDown':
            event.preventDefault();
            selectedIndex.value = Math.min(selectedIndex.value + 1, filteredOptions.value.length - 1);
            scrollToSelectedOption();
            break;
        case 'ArrowUp':
            event.preventDefault();
            selectedIndex.value = Math.max(selectedIndex.value - 1, 0);
            scrollToSelectedOption();
            break;
        case 'Enter':
            if (selectedIndex.value >= 0 && selectedIndex.value < filteredOptions.value.length) {
                selectOption(filteredOptions.value[selectedIndex.value]);
            }
            break;
    }
};

const scrollToSelectedOption = () => {
    nextTick(() => {
        const selectedElement = optionsRef.value?.querySelector(`[data-index="${selectedIndex.value}"]`);
        selectedElement?.scrollIntoView({ block: 'nearest' });
    });
};

watch(isDropdownOpen, (newValue) => {
    if (newValue) {
        selectedIndex.value = props.options.findIndex((option) => option.value === inputValue.value);
        nextTick(() => {
            scrollToSelectedOption();
        });
    }
});
</script>

<template>
    <div class="relative dropdown-container">
        <!-- Selected option display -->
        <div
            @click="toggleDropdown"
            class="w-full bg-[#1a1a2e]/50 border border-[#2a2a3f]/30 rounded-lg px-4 py-2 text-sm text-gray-200 cursor-pointer flex items-center justify-between transition-all duration-200"
            :class="[
                inputClass,
                isDropdownOpen ? 'ring-2 ring-indigo-500/30 border-indigo-500/50' : 'hover:border-[#2a2a3f]/50',
            ]"
            tabindex="0"
            @keydown="handleKeydown"
        >
            <span v-if="selectedOption">{{ selectedOption.label }}</span>
            <span v-else class="text-gray-500">{{ placeholder }}</span>
            <ChevronDown
                class="h-4 w-4 text-gray-400 transition-transform duration-200"
                :class="{ 'transform rotate-180': isDropdownOpen }"
            />
        </div>

        <!-- Dropdown -->
        <div
            v-if="isDropdownOpen"
            class="absolute z-50 w-full mt-1 bg-[#12121f] border border-[#2a2a3f]/30 rounded-lg shadow-lg overflow-hidden"
        >
            <!-- Search input -->
            <div v-if="searchable" class="p-2 border-b border-[#2a2a3f]/30">
                <div class="relative">
                    <input
                        ref="searchInputRef"
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search..."
                        class="w-full bg-[#1a1a2e]/50 border border-[#2a2a3f]/30 rounded-lg pl-9 pr-4 py-2 text-sm text-gray-200 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500/50 transition-all duration-200"
                    />
                    <Search class="absolute left-3 top-2.5 h-4 w-4 text-gray-400" />
                </div>
            </div>

            <!-- Options list -->
            <ul
                ref="optionsRef"
                class="max-h-60 overflow-y-auto scrollbar-thin scrollbar-track-[#0a0a0f] scrollbar-thumb-[#2a2a3f]/50"
            >
                <li
                    v-for="(option, index) in filteredOptions"
                    :key="option.value"
                    :data-index="index"
                    @click="selectOption(option)"
                    class="px-4 py-2 cursor-pointer flex items-center justify-between transition-colors duration-150"
                    :class="[
                        selectedIndex === index ? 'bg-indigo-500/20' : 'hover:bg-[#1a1a2e]/50',
                        option.value === inputValue ? 'text-indigo-400' : 'text-gray-300',
                    ]"
                >
                    <span>{{ option.label }}</span>
                    <Check v-if="option.value === inputValue" class="h-4 w-4" />
                </li>
                <li v-if="filteredOptions.length === 0" class="px-4 py-3 text-gray-500 text-center">
                    No options found
                </li>
            </ul>
        </div>
    </div>
</template>

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

/* Scrollbar styling */
.scrollbar-thin::-webkit-scrollbar {
    width: 4px;
}

.scrollbar-thin::-webkit-scrollbar-track {
    background: transparent;
}

.scrollbar-thin::-webkit-scrollbar-thumb {
    background: rgba(42, 42, 63, 0.5);
    border-radius: 2px;
}

.scrollbar-thin::-webkit-scrollbar-thumb:hover {
    background: rgba(42, 42, 63, 0.7);
}
</style>
