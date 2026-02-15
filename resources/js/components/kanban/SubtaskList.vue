<template>
    <div class="subtask-list">
        <!-- Subtask Items -->
        <div class="subtasks-container">
            <div
                v-for="subtask in sortedSubtasks"
                :key="subtask.id"
                class="subtask-item"
                :class="{ 'is-completed': subtask.is_completed }"
                draggable="true"
                @dragstart="handleDragStart($event, subtask)"
                @dragend="handleDragEnd"
                @dragover="handleDragOver($event, subtask)"
                @drop="handleDrop($event, subtask)"
            >
                <!-- Drag Handle -->
                <div class="drag-handle" @mousedown.stop>
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                        <circle cx="9" cy="6" r="2"/>
                        <circle cx="15" cy="6" r="2"/>
                        <circle cx="9" cy="12" r="2"/>
                        <circle cx="15" cy="12" r="2"/>
                        <circle cx="9" cy="18" r="2"/>
                        <circle cx="15" cy="18" r="2"/>
                    </svg>
                </div>

                <!-- Checkbox -->
                <label class="checkbox-wrapper">
                    <input
                        type="checkbox"
                        :checked="subtask.is_completed"
                        @change="toggleSubtask(subtask)"
                    />
                    <span class="checkmark">
                        <svg v-if="subtask.is_completed" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </span>
                </label>

                <!-- Title -->
                <span class="subtask-title" :class="{ 'completed': subtask.is_completed }">
                    {{ subtask.title }}
                </span>

                <!-- Delete Button -->
                <button
                    class="delete-btn"
                    @click.stop="deleteSubtask(subtask)"
                    title="Delete subtask"
                >
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Add Subtask Input -->
        <div class="add-subtask">
            <input
                v-model="newSubtaskTitle"
                type="text"
                placeholder="Add a subtask..."
                class="subtask-input"
                @keydown.enter="addSubtask"
            />
            <button
                class="add-btn"
                :disabled="!newSubtaskTitle.trim()"
                @click="addSubtask"
            >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
            </button>
        </div>

        <!-- Progress Bar -->
        <div v-if="subtasks.length > 0" class="progress-section">
            <div class="progress-bar">
                <div class="progress-fill" :style="{ width: progress.percentage + '%' }"></div>
            </div>
            <span class="progress-text">{{ progress.completed }}/{{ progress.total }} complete</span>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useSubtasksStore } from '../../stores/subtasks';

const props = defineProps({
    taskId: {
        type: [Number, String],
        required: true,
    },
});

const emit = defineEmits(['updated']);

const subtasksStore = useSubtasksStore();

// Local state
const newSubtaskTitle = ref('');
const draggedSubtask = ref(null);
const dragOverSubtask = ref(null);

// Computed
const subtasks = computed(() => subtasksStore.getSubtasksByTaskId(props.taskId));
const sortedSubtasks = computed(() => {
    return [...subtasks.value].sort((a, b) => a.position - b.position);
});
const progress = computed(() => subtasksStore.getTaskProgress(props.taskId));

// Lifecycle
onMounted(() => {
    if (props.taskId) {
        subtasksStore.fetchSubtasks(props.taskId);
    }
});

// Watch for task changes
watch(() => props.taskId, (newTaskId) => {
    if (newTaskId) {
        subtasksStore.fetchSubtasks(newTaskId);
    }
});

// Methods
const addSubtask = async () => {
    const title = newSubtaskTitle.value.trim();
    if (!title) return;

    try {
        await subtasksStore.createSubtask(props.taskId, { title });
        newSubtaskTitle.value = '';
        emit('updated');
    } catch (error) {
        console.error('Failed to add subtask:', error);
    }
};

const toggleSubtask = async (subtask) => {
    try {
        await subtasksStore.toggleSubtask(props.taskId, subtask.id);
        emit('updated');
    } catch (error) {
        console.error('Failed to toggle subtask:', error);
    }
};

const deleteSubtask = async (subtask) => {
    try {
        await subtasksStore.deleteSubtask(props.taskId, subtask.id);
        emit('updated');
    } catch (error) {
        console.error('Failed to delete subtask:', error);
    }
};

// Drag and drop handlers
const handleDragStart = (event, subtask) => {
    draggedSubtask.value = subtask;
    event.target.classList.add('dragging');
    event.dataTransfer.effectAllowed = 'move';
    event.dataTransfer.setData('text/plain', subtask.id);
};

const handleDragEnd = (event) => {
    event.target.classList.remove('dragging');
    draggedSubtask.value = null;
    dragOverSubtask.value = null;
};

const handleDragOver = (event, subtask) => {
    event.preventDefault();
    if (draggedSubtask.value && draggedSubtask.value.id !== subtask.id) {
        dragOverSubtask.value = subtask;
    }
};

const handleDrop = async (event, targetSubtask) => {
    event.preventDefault();

    if (!draggedSubtask.value || draggedSubtask.value.id === targetSubtask.id) {
        return;
    }

    // Calculate new order
    const currentOrder = sortedSubtasks.value.map(s => s.id);
    const draggedIndex = currentOrder.indexOf(draggedSubtask.value.id);
    const targetIndex = currentOrder.indexOf(targetSubtask.id);

    // Remove dragged item and insert at new position
    currentOrder.splice(draggedIndex, 1);
    currentOrder.splice(targetIndex, 0, draggedSubtask.value.id);

    try {
        await subtasksStore.reorderSubtasks(props.taskId, currentOrder);
        emit('updated');
    } catch (error) {
        console.error('Failed to reorder subtasks:', error);
    }

    draggedSubtask.value = null;
    dragOverSubtask.value = null;
};
</script>

<style scoped>
.subtask-list {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-md);
}

.subtasks-container {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-xs);
}

/* Subtask Item */
.subtask-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    padding: var(--spacing-sm);
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: var(--radius-sm);
    transition: all var(--transition-normal);
}

.subtask-item:hover {
    background: rgba(255, 255, 255, 0.06);
    border-color: rgba(255, 255, 255, 0.12);
}

.subtask-item.is-completed {
    opacity: 0.7;
}

.subtask-item.dragging {
    opacity: 0.5;
    transform: scale(0.98);
}

/* Drag Handle */
.drag-handle {
    color: var(--text-secondary);
    opacity: 0;
    cursor: grab;
    padding: 2px;
    transition: opacity var(--transition-normal);
}

.subtask-item:hover .drag-handle {
    opacity: 0.5;
}

.drag-handle:hover {
    opacity: 1 !important;
    color: var(--orange-primary);
}

/* Checkbox */
.checkbox-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    cursor: pointer;
}

.checkbox-wrapper input {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}

.checkmark {
    width: 18px;
    height: 18px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all var(--transition-normal);
}

.checkbox-wrapper:hover .checkmark {
    border-color: var(--orange-primary);
}

.checkbox-wrapper input:checked + .checkmark {
    background: var(--orange-primary);
    border-color: var(--orange-primary);
}

.checkmark svg {
    color: white;
}

/* Subtask Title */
.subtask-title {
    flex: 1;
    font-size: var(--text-sm);
    color: var(--text-primary);
    transition: all var(--transition-normal);
}

.subtask-title.completed {
    text-decoration: line-through;
    color: var(--text-secondary);
}

/* Delete Button */
.delete-btn {
    opacity: 0;
    background: none;
    border: none;
    color: var(--text-secondary);
    cursor: pointer;
    padding: 4px;
    border-radius: var(--radius-sm);
    transition: all var(--transition-normal);
}

.subtask-item:hover .delete-btn {
    opacity: 0.6;
}

.delete-btn:hover {
    opacity: 1 !important;
    color: var(--red-primary);
    background: rgba(239, 68, 68, 0.1);
}

/* Add Subtask Input */
.add-subtask {
    display: flex;
    gap: var(--spacing-sm);
}

.subtask-input {
    flex: 1;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--radius-sm);
    padding: var(--spacing-sm) var(--spacing-md);
    color: var(--text-primary);
    font-size: var(--text-sm);
    transition: all var(--transition-normal);
}

.subtask-input:focus {
    outline: none;
    border-color: rgba(255, 107, 53, 0.5);
    background: rgba(255, 255, 255, 0.08);
}

.subtask-input::placeholder {
    color: var(--text-secondary);
}

.add-btn {
    background: var(--orange-primary);
    border: none;
    border-radius: var(--radius-sm);
    padding: var(--spacing-sm);
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all var(--transition-normal);
}

.add-btn:hover:not(:disabled) {
    background: var(--orange-light);
}

.add-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Progress Section */
.progress-section {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    padding-top: var(--spacing-sm);
    border-top: 1px solid rgba(255, 255, 255, 0.08);
}

.progress-bar {
    flex: 1;
    height: 4px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 2px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: var(--orange-primary);
    transition: width var(--transition-normal);
}

.progress-text {
    font-size: var(--text-xs);
    color: var(--text-secondary);
    white-space: nowrap;
}
</style>
