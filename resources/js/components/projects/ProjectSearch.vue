<template>
    <div class="search-container">
        <div class="search-input-wrapper">
            <!-- Search Icon -->
            <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>

            <!-- Search Input -->
            <input
                ref="searchInput"
                v-model="searchQuery"
                type="text"
                class="search-input"
                placeholder="Search projects..."
                @input="handleSearchInput"
                @keydown.esc="clearSearch"
            />

            <!-- Clear Button -->
            <button
                v-if="searchQuery"
                class="clear-btn"
                @click="clearSearch"
                title="Clear search"
            >
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Search Results Count -->
        <div v-if="searchQuery && resultsCount !== null" class="results-count">
            {{ resultsCount }} {{ resultsCount === 1 ? 'project' : 'projects' }} found
        </div>
    </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    resultsCount: {
        type: Number,
        default: null,
    },
});

const emit = defineEmits(['update:modelValue', 'search']);

const route = useRoute();
const router = useRouter();
const searchInput = ref(null);
const searchQuery = ref(props.modelValue);

let debounceTimer = null;

// Load search from URL query params on mount
onMounted(() => {
    if (route.query.search) {
        searchQuery.value = route.query.search;
        emit('update:modelValue', searchQuery.value);
        emit('search', searchQuery.value);
    }
});

// Watch for prop changes
watch(
    () => props.modelValue,
    (newValue) => {
        if (newValue !== searchQuery.value) {
            searchQuery.value = newValue;
        }
    }
);

// Handle search input with debounce
const handleSearchInput = () => {
    // Clear existing timer
    if (debounceTimer) {
        clearTimeout(debounceTimer);
    }

    // Update parent immediately for reactive UI
    emit('update:modelValue', searchQuery.value);

    // Debounce the actual search (300ms)
    debounceTimer = setTimeout(() => {
        emit('search', searchQuery.value);
        updateURLParam();
    }, 300);
};

// Clear search
const clearSearch = () => {
    searchQuery.value = '';
    emit('update:modelValue', '');
    emit('search', '');

    // Remove search param from URL
    const query = { ...route.query };
    delete query.search;
    router.push({ query });

    // Blur the input
    if (searchInput.value) {
        searchInput.value.blur();
    }
};

// Update URL param
const updateURLParam = () => {
    const query = { ...route.query };

    if (searchQuery.value) {
        query.search = searchQuery.value;
    } else {
        delete query.search;
    }

    router.push({ query });
};
</script>

<style scoped>
.search-container {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
}

.search-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.search-icon {
    position: absolute;
    left: 1rem;
    width: 1.25rem;
    height: 1.25rem;
    color: #94a3b8;
    pointer-events: none;
}

.search-input {
    width: 100%;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(148, 163, 184, 0.2);
    color: #f8fafc;
    padding: 0.75rem 3rem 0.75rem 3rem;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    transition: all 0.2s;
}

.search-input::placeholder {
    color: #64748b;
}

.search-input:hover {
    background-color: rgba(255, 255, 255, 0.08);
    border-color: rgba(148, 163, 184, 0.4);
}

.search-input:focus {
    outline: none;
    border-color: #667eea;
    background-color: rgba(255, 255, 255, 0.08);
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.clear-btn {
    position: absolute;
    right: 0.75rem;
    background: transparent;
    border: none;
    color: #94a3b8;
    padding: 0.25rem;
    border-radius: 0.25rem;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.clear-btn:hover {
    color: #f8fafc;
    background: rgba(255, 255, 255, 0.1);
}

.clear-btn .icon {
    width: 1rem;
    height: 1rem;
}

.results-count {
    font-size: 0.75rem;
    color: #94a3b8;
    padding-left: 0.25rem;
}

@media (max-width: 768px) {
    .search-input {
        padding: 0.625rem 2.5rem 0.625rem 2.5rem;
    }
}
</style>
