<template>
    <!-- T051: Board Header with search and add task button -->
    <div class="board-header">
        <!-- T114: Filter Bar Integration -->
        <FilterBar
            v-if="projectId"
            :project-id="projectId"
            :project-members="projectMembers"
            @filters-changed="onFiltersChanged"
        />

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
                @click="showLabelManager = true"
                class="btn btn-secondary"
                title="Manage project labels"
            >
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                    <line x1="7" y1="7" x2="7.01" y2="7"></line>
                </svg>
                Labels
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

        <!-- Label Manager Modal -->
        <div v-if="showLabelManager" class="modal-backdrop" @click.self="showLabelManager = false">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Label Management</h2>
                    <button class="close-btn" @click="showLabelManager = false">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>
                <LabelManager />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useKanbanStore } from '../../stores/kanban';
import LabelManager from './LabelManager.vue';
import FilterBar from './FilterBar.vue';

const props = defineProps({
    projectId: {
        type: [String, Number],
        default: null,
    },
    projectMembers: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['add-task', 'filters-changed']);

const kanbanStore = useKanbanStore();
const showLabelManager = ref(false);

// Methods
const onFiltersChanged = (filters) => {
    emit('filters-changed', filters);
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

/* Filter Bar will handle its own styles */

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

/* Modal Styles */
.modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.75);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 20px;
}

.modal-content {
    background: rgba(30, 30, 40, 0.95);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    max-width: 600px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
}

.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 24px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.modal-header h2 {
    font-size: 20px;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0;
}

.close-btn {
    background: none;
    border: none;
    color: var(--text-secondary);
    cursor: pointer;
    padding: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.close-btn:hover {
    color: var(--text-primary);
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

    .modal-content {
        max-height: 95vh;
    }
}
</style>
