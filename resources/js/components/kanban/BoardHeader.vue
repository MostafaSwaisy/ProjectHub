<template>
    <!-- T051: Board Header with search and add task button -->
    <div class="board-header">
        <!-- Search and Filters -->
        <div class="search-filters">
            <div class="search-box">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.35-4.35"></path>
                </svg>
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search tasks..."
                    @input="updateSearch"
                    class="search-input"
                />
                <button
                    v-if="searchQuery"
                    @click="clearSearch"
                    class="clear-btn"
                >
                    ✕
                </button>
            </div>

            <!-- Active Filters Display -->
            <div v-if="hasActiveFilters" class="active-filters">
                <span class="filter-label">Filters:</span>
                <span class="filter-summary">{{ filterSummary }}</span>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="actions">
            <button
                v-if="hasActiveFilters"
                @click="clearFilters"
                class="btn btn-secondary"
            >
                Clear Filters
            </button>
            <button
                @click="openAddTaskModal"
                class="btn btn-primary"
            >
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
                </svg>
                Add Task
            </button>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useKanbanStore } from '../../stores/kanban';

const emit = defineEmits(['add-task', 'filters-changed']);

const kanbanStore = useKanbanStore();

// Computed: Get search query from store
const searchQuery = computed({
    get() {
        return kanbanStore.searchQuery;
    },
    set(value) {
        kanbanStore.setSearchQuery(value);
    },
});

// Computed: Check if filters are active
const hasActiveFilters = computed(() => kanbanStore.hasActiveFilters);

// Computed: Get filter summary
const filterSummary = computed(() => kanbanStore.filterSummary);

// Methods
const updateSearch = () => {
    emit('filters-changed');
};

const clearSearch = () => {
    kanbanStore.setSearchQuery('');
    emit('filters-changed');
};

const clearFilters = () => {
    kanbanStore.clearAllFilters();
    emit('filters-changed');
};

const openAddTaskModal = () => {
    kanbanStore.openCreateTaskModal();
    emit('add-task');
};
</script>

<style scoped>
/* T051: Board Header Styling */

.board-header {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-lg);
    padding: var(--spacing-lg);
    background: rgba(255, 255, 255, 0.02);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

/* Search and Filters Section */
.search-filters {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-md);
}

.search-box {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--radius-md);
    padding: var(--spacing-sm) var(--spacing-md);
    transition: all var(--transition-normal);
}

.search-box:focus-within {
    background: rgba(255, 255, 255, 0.08);
    border-color: rgba(255, 107, 53, 0.5);
    box-shadow: 0 0 0 2px rgba(255, 107, 53, 0.1);
}

.search-box svg {
    color: var(--text-secondary);
    flex-shrink: 0;
}

.search-input {
    flex: 1;
    background: none;
    border: none;
    color: var(--text-primary);
    font-size: var(--text-sm);
    font-family: inherit;
    outline: none;
}

.search-input::placeholder {
    color: var(--text-secondary);
}

.clear-btn {
    background: none;
    border: none;
    color: var(--text-secondary);
    cursor: pointer;
    font-size: var(--text-base);
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all var(--transition-normal);
}

.clear-btn:hover {
    color: var(--text-primary);
}

/* Active Filters Display */
.active-filters {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    font-size: var(--text-sm);
}

.filter-label {
    color: var(--text-secondary);
    font-weight: var(--font-medium);
}

.filter-summary {
    color: var(--orange-primary);
    font-weight: var(--font-medium);
}

/* Actions Section */
.actions {
    display: flex;
    gap: var(--spacing-md);
    justify-content: flex-end;
    align-items: center;
}

.btn {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    padding: 0.625rem 1rem;
    border-radius: var(--radius-md);
    font-weight: var(--font-medium);
    font-size: var(--text-sm);
    border: none;
    cursor: pointer;
    transition: all var(--transition-normal);
    white-space: nowrap;
}

.btn-primary {
    background: var(--orange-primary);
    color: white;
}

.btn-primary:hover {
    background: var(--orange-light);
    box-shadow: var(--shadow-orange);
    transform: translateY(-2px);
}

.btn-primary:active {
    transform: translateY(0);
}

.btn-secondary {
    background: rgba(255, 255, 255, 0.1);
    color: var(--text-primary);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.btn-secondary:hover {
    background: rgba(255, 255, 255, 0.15);
    border-color: rgba(255, 255, 255, 0.3);
}

/* Mobile Responsive */
@media (max-width: 639px) {
    .board-header {
        gap: var(--spacing-md);
        padding: var(--spacing-md);
    }

    .actions {
        width: 100%;
    }

    .btn {
        flex: 1;
        justify-content: center;
    }

    .btn svg {
        display: none;
    }

    .search-input {
        font-size: 16px; /* Prevents zoom on iOS */
    }
}
</style>
