<template>
    <!-- T104-T110: Filter Bar Component with search, label, assignee, priority, and due date filters -->
    <div class="filter-bar">
        <div class="filter-row">
            <!-- T105: Search Input with 300ms debounce -->
            <div class="filter-group search-group">
                <label>Search</label>
                <div class="search-input-wrapper">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                    <input
                        v-model="localFilters.search"
                        type="text"
                        placeholder="Search tasks..."
                        @input="debouncedSearch"
                        class="filter-input"
                    />
                    <button
                        v-if="localFilters.search"
                        @click="clearSearch"
                        class="clear-search-btn"
                        title="Clear search"
                    >
                        ✕
                    </button>
                </div>
            </div>

            <!-- T106: Label Filter Multiselect -->
            <div class="filter-group">
                <label>Labels</label>
                <div class="multiselect-wrapper" ref="labelDropdownRef">
                    <button
                        @click="toggleLabelDropdown"
                        class="filter-select"
                        :class="{ active: localFilters.label_ids.length > 0 }"
                    >
                        <span v-if="localFilters.label_ids.length === 0">All Labels</span>
                        <span v-else>{{ localFilters.label_ids.length }} selected</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </button>
                    <div v-if="showLabelDropdown" class="dropdown-menu">
                        <label
                            v-for="label in availableLabels"
                            :key="label.id"
                            class="dropdown-item checkbox-item"
                        >
                            <input
                                type="checkbox"
                                :value="label.id"
                                v-model="localFilters.label_ids"
                                @change="applyFilters"
                            />
                            <span class="label-preview">
                                <span class="label-dot" :style="{ backgroundColor: label.color }"></span>
                                {{ label.name }}
                            </span>
                        </label>
                        <div v-if="availableLabels.length === 0" class="dropdown-empty">
                            No labels available
                        </div>
                    </div>
                </div>
            </div>

            <!-- T107: Assignee Filter Dropdown -->
            <div class="filter-group">
                <label>Assignee</label>
                <select
                    v-model="localFilters.assignee_id"
                    @change="applyFilters"
                    class="filter-select"
                    :class="{ active: localFilters.assignee_id !== null }"
                >
                    <option :value="null">All Assignees</option>
                    <option
                        v-for="member in projectMembers"
                        :key="member.id"
                        :value="member.id"
                    >
                        {{ member.name }}
                    </option>
                </select>
            </div>

            <!-- T108: Priority Filter Checkboxes -->
            <div class="filter-group">
                <label>Priority</label>
                <div class="multiselect-wrapper" ref="priorityDropdownRef">
                    <button
                        @click="togglePriorityDropdown"
                        class="filter-select"
                        :class="{ active: localFilters.priorities.length > 0 }"
                    >
                        <span v-if="localFilters.priorities.length === 0">All Priorities</span>
                        <span v-else>{{ localFilters.priorities.length }} selected</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </button>
                    <div v-if="showPriorityDropdown" class="dropdown-menu">
                        <label
                            v-for="priority in availablePriorities"
                            :key="priority.value"
                            class="dropdown-item checkbox-item"
                        >
                            <input
                                type="checkbox"
                                :value="priority.value"
                                v-model="localFilters.priorities"
                                @change="applyFilters"
                            />
                            <span>{{ priority.label }}</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- T109: Due Date Filter -->
            <div class="filter-group">
                <label>Due Date</label>
                <select
                    v-model="localFilters.due_date_range"
                    @change="applyFilters"
                    class="filter-select"
                    :class="{ active: localFilters.due_date_range !== null }"
                >
                    <option :value="null">All Dates</option>
                    <option value="overdue">Overdue</option>
                    <option value="today">Due Today</option>
                    <option value="this_week">Due This Week</option>
                </select>
            </div>

            <!-- T110: Clear All Filters Button with count badge -->
            <div class="filter-actions">
                <button
                    v-if="activeFilterCount > 0"
                    @click="clearAllFilters"
                    class="btn btn-clear"
                    :title="`Clear ${activeFilterCount} active filter${activeFilterCount > 1 ? 's' : ''}`"
                >
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                    Clear Filters
                    <span class="filter-badge">{{ activeFilterCount }}</span>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { useLabelsStore } from '@/stores/labels';
import { useRoute, useRouter } from 'vue-router';

const props = defineProps({
    projectId: {
        type: [Number, String],
        required: true,
    },
    projectMembers: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['filters-changed']);

const route = useRoute();
const router = useRouter();
const labelsStore = useLabelsStore();

// State
const localFilters = ref({
    search: '',
    label_ids: [],
    assignee_id: null,
    priorities: [],
    due_date_range: null,
});

const showLabelDropdown = ref(false);
const showPriorityDropdown = ref(false);
const labelDropdownRef = ref(null);
const priorityDropdownRef = ref(null);
let searchDebounceTimer = null;

// Available options
const availablePriorities = [
    { value: 'low', label: 'Low' },
    { value: 'medium', label: 'Medium' },
    { value: 'high', label: 'High' },
    { value: 'critical', label: 'Critical' },
];

// Computed
const availableLabels = computed(() => labelsStore.labels);

const activeFilterCount = computed(() => {
    let count = 0;
    if (localFilters.value.search) count++;
    if (localFilters.value.label_ids.length > 0) count++;
    if (localFilters.value.assignee_id !== null) count++;
    if (localFilters.value.priorities.length > 0) count++;
    if (localFilters.value.due_date_range !== null) count++;
    return count;
});

// Methods
const debouncedSearch = () => {
    clearTimeout(searchDebounceTimer);
    searchDebounceTimer = setTimeout(() => {
        applyFilters();
    }, 300);
};

const clearSearch = () => {
    localFilters.value.search = '';
    applyFilters();
};

const toggleLabelDropdown = () => {
    showLabelDropdown.value = !showLabelDropdown.value;
    if (showLabelDropdown.value) {
        showPriorityDropdown.value = false;
    }
};

const togglePriorityDropdown = () => {
    showPriorityDropdown.value = !showPriorityDropdown.value;
    if (showPriorityDropdown.value) {
        showLabelDropdown.value = false;
    }
};

const applyFilters = () => {
    // T111-T113: Update URL query params
    const query = {};

    if (localFilters.value.search) {
        query.search = localFilters.value.search;
    }
    if (localFilters.value.label_ids.length > 0) {
        query.label_ids = localFilters.value.label_ids.join(',');
    }
    if (localFilters.value.assignee_id !== null) {
        query.assignee_id = localFilters.value.assignee_id;
    }
    if (localFilters.value.priorities.length > 0) {
        query.priority = localFilters.value.priorities.join(',');
    }
    if (localFilters.value.due_date_range !== null) {
        query.due_date_range = localFilters.value.due_date_range;
    }

    // Update URL without reloading page
    router.push({ query });

    // Emit filters to parent
    emit('filters-changed', localFilters.value);
};

const clearAllFilters = () => {
    localFilters.value = {
        search: '',
        label_ids: [],
        assignee_id: null,
        priorities: [],
        due_date_range: null,
    };
    applyFilters();
};

// T112: Restore filters from URL on mount
const restoreFiltersFromURL = () => {
    const query = route.query;

    if (query.search) {
        localFilters.value.search = query.search;
    }
    if (query.label_ids) {
        localFilters.value.label_ids = query.label_ids.split(',').map(Number);
    }
    if (query.assignee_id) {
        localFilters.value.assignee_id = parseInt(query.assignee_id);
    }
    if (query.priority) {
        localFilters.value.priorities = query.priority.split(',');
    }
    if (query.due_date_range) {
        localFilters.value.due_date_range = query.due_date_range;
    }

    // Emit initial filters if any are set
    if (activeFilterCount.value > 0) {
        emit('filters-changed', localFilters.value);
    }
};

// Close dropdowns when clicking outside
const handleClickOutside = (event) => {
    if (labelDropdownRef.value && !labelDropdownRef.value.contains(event.target)) {
        showLabelDropdown.value = false;
    }
    if (priorityDropdownRef.value && !priorityDropdownRef.value.contains(event.target)) {
        showPriorityDropdown.value = false;
    }
};

// Lifecycle
onMounted(async () => {
    // Load labels for the project
    if (props.projectId) {
        try {
            await labelsStore.fetchLabels(props.projectId);
        } catch (err) {
            console.error('Failed to load labels for filters:', err);
        }
    }

    // Restore filters from URL
    restoreFiltersFromURL();

    // Add click outside listener
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    clearTimeout(searchDebounceTimer);
    document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
.filter-bar {
    padding: 16px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.filter-row {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    align-items: flex-end;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
    min-width: 150px;
}

.filter-group.search-group {
    flex: 1;
    min-width: 200px;
}

.filter-group label {
    font-size: 12px;
    font-weight: 500;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Search Input */
.search-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    gap: 8px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 6px;
    padding: 0 12px;
    transition: all 0.2s;
}

.search-input-wrapper:focus-within {
    background: rgba(255, 255, 255, 0.08);
    border-color: var(--primary-color);
}

.search-input-wrapper svg {
    color: var(--text-secondary);
    flex-shrink: 0;
}

.filter-input {
    flex: 1;
    background: none;
    border: none;
    color: var(--text-primary);
    font-size: 14px;
    padding: 10px 0;
    outline: none;
}

.filter-input::placeholder {
    color: var(--text-secondary);
}

.clear-search-btn {
    background: none;
    border: none;
    color: var(--text-secondary);
    cursor: pointer;
    padding: 4px;
    font-size: 16px;
    transition: all 0.2s;
}

.clear-search-btn:hover {
    color: var(--text-primary);
}

/* Filter Select */
.filter-select {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    width: 100%;
    padding: 10px 12px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 6px;
    color: var(--text-primary);
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s;
}

.filter-select:hover {
    background: rgba(255, 255, 255, 0.08);
    border-color: rgba(255, 255, 255, 0.2);
}

.filter-select.active {
    background: rgba(59, 130, 246, 0.1);
    border-color: rgba(59, 130, 246, 0.3);
    color: #3B82F6;
}

.filter-select option {
    background: #1a1a24;
    color: var(--text-primary);
}

/* Multiselect Dropdown */
.multiselect-wrapper {
    position: relative;
}

.dropdown-menu {
    position: absolute;
    top: calc(100% + 4px);
    left: 0;
    right: 0;
    max-height: 250px;
    overflow-y: auto;
    background: rgba(30, 30, 40, 0.98);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 6px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    z-index: 100;
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 12px;
    cursor: pointer;
    transition: all 0.2s;
}

.dropdown-item:hover {
    background: rgba(255, 255, 255, 0.05);
}

.checkbox-item input[type="checkbox"] {
    cursor: pointer;
}

.label-preview {
    display: flex;
    align-items: center;
    gap: 8px;
}

.label-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    flex-shrink: 0;
}

.dropdown-empty {
    padding: 16px 12px;
    text-align: center;
    color: var(--text-secondary);
    font-size: 13px;
}

/* Filter Actions */
.filter-actions {
    display: flex;
    align-items: flex-end;
}

.btn-clear {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.3);
    border-radius: 6px;
    color: #EF4444;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
}

.btn-clear:hover {
    background: rgba(239, 68, 68, 0.15);
    border-color: rgba(239, 68, 68, 0.4);
}

.filter-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 20px;
    height: 20px;
    padding: 0 6px;
    background: #EF4444;
    color: white;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 600;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .filter-row {
        flex-direction: column;
    }

    .filter-group,
    .filter-group.search-group {
        width: 100%;
        min-width: unset;
    }

    .filter-actions {
        width: 100%;
    }

    .btn-clear {
        width: 100%;
        justify-content: center;
    }
}
</style>
