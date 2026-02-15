<template>
    <!-- T049: Kanban Column with colored status indicator -->
    <div class="kanban-column" @dragover="handleDragOver" @dragleave="handleDragLeave" @drop="handleDrop">
        <!-- Column Header -->
        <div class="column-header">
            <div class="column-title-group">
                <div class="column-indicator" :style="{ backgroundColor: column.color }"></div>
                <h3 class="column-title">{{ column.title }}</h3>
                <span class="task-count" :class="wipCountClass">
                    {{ tasks.length }}<span v-if="column.wip_limit > 0">/{{ column.wip_limit }}</span>
                </span>
            </div>
            <!-- WIP Limit Warning -->
            <div v-if="isAtWipLimit" class="wip-warning">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2L2 22h20L12 2zm0 15a1.5 1.5 0 110 3 1.5 1.5 0 010-3zm-1-2h2v-6h-2v6z"/>
                </svg>
                <span>WIP limit reached</span>
            </div>
        </div>

        <!-- Column Body (Scrollable) -->
        <div class="column-body">
            <!-- Drag Overlay -->
            <div v-if="isDragTarget" class="drag-overlay">
                <p>Drop here</p>
            </div>

            <!-- Tasks List -->
            <div v-if="tasks.length > 0" class="tasks-list">
                <TaskCard
                    v-for="task in tasks"
                    :key="task.id"
                    :task="task"
                    :columns="allColumns"
                    @drag-start="handleTaskDragStart($event, task)"
                    @drag-end="handleTaskDragEnd"
                    @edit="$emit('edit-task', task.id)"
                    @duplicate="$emit('duplicate-task', task.id)"
                    @archive="$emit('archive-task', task.id)"
                    @delete="$emit('delete-task', task.id)"
                    @details="$emit('open-details', task.id)"
                    @move-to="handleMoveTask(task.id, $event)"
                />
            </div>

            <!-- Empty State -->
            <div v-else class="empty-state">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M12 2v20M2 12h20" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <p>No tasks</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import TaskCard from './TaskCard.vue';
import { useKanbanStore } from '../../stores/kanban';

const props = defineProps({
    column: {
        type: Object,
        required: true,
    },
    tasks: {
        type: Array,
        default: () => [],
    },
    allColumns: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits([
    'edit-task',
    'duplicate-task',
    'archive-task',
    'delete-task',
    'open-details',
    'tasks-dropped',
    'move-to-task',
]);

const kanbanStore = useKanbanStore();

// Computed: Check if this column is the drag target
const isDragTarget = computed(() => {
    return kanbanStore.draggedOverColumn === props.column.id;
});

// Computed: Check if at WIP limit
const isAtWipLimit = computed(() => {
    if (!props.column.wip_limit || props.column.wip_limit === 0) return false;
    return props.tasks.length >= props.column.wip_limit;
});

// Computed: Check if near WIP limit (80% or more)
const isNearWipLimit = computed(() => {
    if (!props.column.wip_limit || props.column.wip_limit === 0) return false;
    return props.tasks.length >= props.column.wip_limit * 0.8;
});

// Computed: WIP count styling class
const wipCountClass = computed(() => {
    if (isAtWipLimit.value) return 'wip-at-limit';
    if (isNearWipLimit.value) return 'wip-near-limit';
    return '';
});

// Methods
const handleDragOver = (event) => {
    event.preventDefault();
    event.dataTransfer.dropEffect = 'move';
    kanbanStore.setDragOverColumn(props.column.id);
};

const handleDragLeave = (event) => {
    // Only clear if leaving the column itself
    if (event.target.classList?.contains('kanban-column')) {
        kanbanStore.setDragOverColumn(null);
    }
};

const handleDrop = (event) => {
    event.preventDefault();
    emit('tasks-dropped', {
        fromColumn: kanbanStore.draggedFromColumn,
        toColumn: props.column.id,
        taskId: kanbanStore.draggedTaskId,
    });
    kanbanStore.setDragOverColumn(null);
};

const handleTaskDragStart = (event, task) => {
    // Set up drag state in kanban store
    kanbanStore.startDrag(task.id, props.column.id);
    // Set drag data for native browser drag-drop
    if (event.dataTransfer) {
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', task.id.toString());
    }
};

const handleTaskDragEnd = () => {
    // Clear drag state when drag ends
    kanbanStore.clearDrag();
};

// T093: Handle "Move to..." menu selection from mobile
const handleMoveTask = (taskId, toColumn) => {
    emit('move-to-task', {
        taskId,
        toColumn,
    });
};
</script>

<style scoped>
/* T049: Kanban Column Styling */

.kanban-column {
    display: flex;
    flex-direction: column;
    min-width: 300px;
    max-width: 350px;
    height: 100%;
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: var(--radius-lg);
    overflow: hidden;
    transition: all var(--transition-normal);
}

.kanban-column:hover {
    background: rgba(255, 255, 255, 0.04);
    border-color: rgba(255, 255, 255, 0.12);
}

/* Column Header */
.column-header {
    padding: var(--spacing-md);
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    background: rgba(255, 255, 255, 0.05);
}

.column-title-group {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
}

.column-indicator {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    flex-shrink: 0;
}

.column-title {
    font-size: var(--text-base);
    font-weight: var(--font-semibold);
    color: var(--text-primary);
    margin: 0;
    flex: 1;
}

.task-count {
    font-size: var(--text-sm);
    color: var(--text-secondary);
    background: rgba(255, 255, 255, 0.1);
    padding: 2px 8px;
    border-radius: 12px;
    font-weight: var(--font-medium);
    transition: all var(--transition-normal);
}

.task-count.wip-near-limit {
    background: rgba(255, 107, 53, 0.2);
    color: var(--orange-primary);
}

.task-count.wip-at-limit {
    background: rgba(239, 68, 68, 0.2);
    color: var(--red-primary);
}

/* WIP Limit Warning */
.wip-warning {
    display: flex;
    align-items: center;
    gap: var(--spacing-xs);
    margin-top: var(--spacing-xs);
    padding: 4px 8px;
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.3);
    border-radius: var(--radius-sm);
    font-size: var(--text-xs);
    color: var(--red-primary);
    animation: fadeIn var(--transition-normal) ease-out;
}

.wip-warning svg {
    flex-shrink: 0;
}

/* Column Body */
.column-body {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    padding: var(--spacing-md);
    position: relative;
}

.column-body::-webkit-scrollbar {
    width: 6px;
}

.column-body::-webkit-scrollbar-track {
    background: transparent;
}

.column-body::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 3px;
}

.column-body::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.2);
}

/* Drag Overlay */
.drag-overlay {
    position: absolute;
    inset: 0;
    background: rgba(255, 107, 53, 0.1);
    border: 2px dashed var(--orange-primary);
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 5;
    animation: fadeIn var(--transition-normal) ease-out;
}

.drag-overlay p {
    font-size: var(--text-sm);
    color: var(--orange-primary);
    font-weight: var(--font-semibold);
    margin: 0;
}

/* Tasks List */
.tasks-list {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-md);
}

/* Empty State */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 200px;
    gap: var(--spacing-md);
    color: var(--text-secondary);
}

.empty-state svg {
    opacity: 0.3;
}

.empty-state p {
    font-size: var(--text-sm);
    margin: 0;
}

/* Mobile Responsive */
@media (max-width: 1023px) {
    .kanban-column {
        min-width: 280px;
        max-width: 320px;
    }
}

@media (max-width: 639px) {
    .kanban-column {
        min-width: 250px;
        max-width: 280px;
    }

    .column-title {
        font-size: var(--text-sm);
    }

    .column-header {
        padding: var(--spacing-sm);
    }

    .column-body {
        padding: var(--spacing-sm);
    }
}
</style>
