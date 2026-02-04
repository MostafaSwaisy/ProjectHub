<template>
    <!-- T052: Kanban Board Container Component -->
    <div class="kanban-board-wrapper">
        <!-- Board Header -->
        <BoardHeader
            @add-task="openCreateModal"
            @filters-changed="onFiltersChanged"
        />

        <!-- Board Content -->
        <div class="board-content">
            <!-- Stats Section -->
            <div class="stats-section">
                <BoardStats />
            </div>

            <!-- Columns Section -->
            <div class="columns-section">
                <div v-if="tasksStore.loading" class="loading-state">
                    <div class="spinner"></div>
                    <p>Loading tasks...</p>
                </div>

                <div v-else class="columns-container">
                    <KanbanColumn
                        v-for="column in kanbanStore.columns"
                        :key="column.id"
                        :column="column"
                        :tasks="getTasksByStatus(column.id)"
                        @edit-task="editTask"
                        @duplicate-task="duplicateTask"
                        @archive-task="archiveTask"
                        @delete-task="deleteTask"
                        @open-details="openTaskDetails"
                        @tasks-dropped="onTaskDropped"
                        @move-to-task="onMoveToTask"
                    />
                </div>

                <!-- Empty State -->
                <div v-if="!tasksStore.loading && tasksStore.tasks.length === 0" class="empty-board">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M12 2v20M2 12h20" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    <h3>No tasks yet</h3>
                    <p>Create your first task to get started</p>
                    <button class="btn-add" @click="openCreateModal">Add Task</button>
                </div>
            </div>
        </div>

        <!-- Task Modal -->
        <TaskModal
            v-if="kanbanStore.isTaskModalOpen"
            :task="editingTask"
            :project-id="projectId"
            @save="onTaskSaved"
            @close="kanbanStore.closeTaskModal"
        />

        <!-- Task Details Modal -->
        <TaskDetailModal
            v-if="kanbanStore.showTaskDetails"
            :task="selectedTask"
            @close="kanbanStore.closeTaskDetails"
            @edit="editTask(selectedTask.id)"
            @delete="deleteTask(selectedTask.id)"
        />

        <!-- Confirmation Dialog for Delete -->
        <div v-if="showDeleteConfirm" class="confirmation-dialog" @click.self="showDeleteConfirm = false">
            <div class="dialog-content">
                <h3>Delete Task?</h3>
                <p>This action cannot be undone. Are you sure you want to delete this task?</p>
                <div class="dialog-actions">
                    <button class="btn-cancel" @click="showDeleteConfirm = false">Cancel</button>
                    <button class="btn-delete" @click="confirmDelete">Delete</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useTasksStore } from '../../stores/tasks';
import { useKanbanStore } from '../../stores/kanban';
import { useTaskFiltering } from '../../composables/useTaskFiltering';
import BoardHeader from './BoardHeader.vue';
import BoardStats from './BoardStats.vue';
import KanbanColumn from './KanbanColumn.vue';
import TaskModal from './TaskModal.vue';
import TaskDetailModal from './TaskDetailModal.vue';

const props = defineProps({
    projectId: {
        type: [String, Number],
        required: true,
    },
});

const tasksStore = useTasksStore();
const kanbanStore = useKanbanStore();
const { getTasksByStatus } = useTaskFiltering(computed(() => tasksStore.tasks));

// Local state
const showDeleteConfirm = ref(false);
const taskToDelete = ref(null);

// Computed: Get editing task
const editingTask = computed(() => {
    if (!kanbanStore.editingTaskId) return null;
    return tasksStore.getTaskById.value(kanbanStore.editingTaskId);
});

// Computed: Get selected task for details modal
const selectedTask = computed(() => {
    if (!kanbanStore.editingTaskId) return null;
    return tasksStore.getTaskById.value(kanbanStore.editingTaskId);
});

// Lifecycle
onMounted(async () => {
    // Fetch tasks for the project
    try {
        await tasksStore.fetchTasks(props.projectId);
    } catch (error) {
        console.error('Failed to fetch tasks:', error);
        // Load mock data if API fails (for development/testing)
        loadMockData();
    }
});

// Mock data for development/testing when API is not available
const loadMockData = () => {
    const mockTasks = [
        { id: 1, title: 'Design system component library', description: 'Create reusable components with consistent styling', status: 'in_progress', priority: 'high', due_date: '2026-02-05', labels: [{ id: 1, name: 'Design', color: '#FF6B35' }], assignees: [{ id: 1, name: 'John Doe' }], subtasks: [{ id: 1, title: 'Button component', completed: true }], created_at: '2026-01-28', updated_at: '2026-02-01' },
        { id: 2, title: 'Implement kanban board UI', description: 'Build drag-drop kanban with all features', status: 'in_progress', priority: 'high', due_date: '2026-02-10', labels: [{ id: 2, name: 'Feature', color: '#4F46E5' }], assignees: [{ id: 2, name: 'Jane Smith' }], subtasks: [], created_at: '2026-01-29', updated_at: '2026-02-01' },
        { id: 3, title: 'Mobile responsive design', description: 'Optimize for mobile and tablet devices', status: 'todo', priority: 'medium', due_date: '2026-02-15', labels: [{ id: 3, name: 'Mobile', color: '#22C55E' }], assignees: [], subtasks: [], created_at: '2026-01-30', updated_at: '2026-02-01' },
        { id: 4, title: 'API endpoint creation', description: 'Create REST API for task management', status: 'todo', priority: 'high', due_date: '2026-02-08', labels: [{ id: 4, name: 'Backend', color: '#EC4899' }], assignees: [{ id: 1, name: 'John Doe' }], subtasks: [], created_at: '2026-01-31', updated_at: '2026-02-01' },
        { id: 5, title: 'Write unit tests', description: 'Add tests for all components', status: 'todo', priority: 'low', due_date: '2026-02-20', labels: [{ id: 5, name: 'Testing', color: '#8B5CF6' }], assignees: [{ id: 2, name: 'Jane Smith' }], subtasks: [], created_at: '2026-02-01', updated_at: '2026-02-01' },
        { id: 6, title: 'Documentation', description: 'Update README', status: 'in_review', priority: 'medium', due_date: null, labels: [{ id: 6, name: 'Documentation', color: '#06B6D4' }], assignees: [], subtasks: [], created_at: '2026-01-28', updated_at: '2026-02-01' },
        { id: 7, title: 'Performance optimization', description: 'Optimize bundle size', status: 'done', priority: 'medium', due_date: '2026-01-31', labels: [], assignees: [{ id: 1, name: 'John Doe' }], subtasks: [], created_at: '2026-01-15', updated_at: '2026-02-01' },
    ];
    tasksStore.tasks = mockTasks;
    tasksStore.loading = false;
};

// T085: Cleanup ongoing animations when navigating away
onBeforeUnmount(() => {
    // Clear any ongoing drag operations
    kanbanStore.clearDrag();

    // Close any open modals
    kanbanStore.closeTaskModal();
    kanbanStore.closeTaskDetails();

    // Reset board state to initial
    kanbanStore.resetBoardState();

    // Cancel any pending API requests by clearing loading state
    if (tasksStore.loading) {
        tasksStore.loading = false;
    }
});

// Methods
const openCreateModal = () => {
    kanbanStore.openCreateTaskModal();
};

const editTask = (taskId) => {
    kanbanStore.openEditTaskModal(taskId);
};

const deleteTask = (taskId) => {
    taskToDelete.value = taskId;
    showDeleteConfirm.value = true;
};

const confirmDelete = async () => {
    if (!taskToDelete.value) return;

    try {
        await tasksStore.deleteTask(props.projectId, taskToDelete.value);
        showDeleteConfirm.value = false;
        taskToDelete.value = null;
    } catch (error) {
        console.error('Failed to delete task:', error);
    }
};

const duplicateTask = async (taskId) => {
    try {
        await tasksStore.duplicateTask(props.projectId, taskId);
    } catch (error) {
        console.error('Failed to duplicate task:', error);
    }
};

const archiveTask = async (taskId) => {
    try {
        await tasksStore.changeTaskStatus(props.projectId, taskId, 'archived');
    } catch (error) {
        console.error('Failed to archive task:', error);
    }
};

const onTaskSaved = async () => {
    kanbanStore.closeTaskModal();
    // Tasks are automatically updated via the store
};

const openTaskDetails = (taskId) => {
    kanbanStore.openTaskDetails(taskId);
};

const onTaskDropped = async (event) => {
    const { toColumn, taskId } = event;

    try {
        // Update task status
        await tasksStore.changeTaskStatus(props.projectId, taskId, toColumn);
    } catch (error) {
        console.error('Failed to move task:', error);
        // Error handling is done in the store
    }
};

// T093: Handle "Move to..." menu selection from mobile
const onMoveToTask = async (event) => {
    const { taskId, toColumn } = event;

    try {
        // Update task status (same as drag-drop, but initiated by mobile menu)
        await tasksStore.changeTaskStatus(props.projectId, taskId, toColumn);
    } catch (error) {
        console.error('Failed to move task:', error);
    }
};

const onFiltersChanged = () => {
    // Filters are automatically applied by the useTaskFiltering composable
};
</script>

<style scoped>
/* T052: Kanban Board Container */

.kanban-board-wrapper {
    display: flex;
    flex-direction: column;
    height: 100vh;
    background-color: var(--black-primary);
    color: var(--text-primary);
    overflow: hidden;
}

/* Board Content */
.board-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    padding: var(--spacing-lg);
    gap: var(--spacing-lg);
}

/* Stats Section */
.stats-section {
    flex-shrink: 0;
}

/* Columns Section */
.columns-section {
    flex: 1;
    overflow-x: auto;
    overflow-y: hidden;
    display: flex;
    gap: var(--spacing-lg);
    padding-bottom: 8px;
}

.columns-section::-webkit-scrollbar {
    height: 6px;
}

.columns-section::-webkit-scrollbar-track {
    background: transparent;
}

.columns-section::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 3px;
}

.columns-section::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.2);
}

.columns-container {
    display: flex;
    gap: var(--spacing-lg);
    flex: 1;
}

/* Loading State */
.loading-state {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: var(--spacing-md);
    color: var(--text-secondary);
}

.spinner {
    width: 40px;
    height: 40px;
    border: 3px solid rgba(255, 255, 255, 0.1);
    border-top-color: var(--orange-primary);
    border-radius: 50%;
    animation: spin var(--transition-normal) linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

/* Empty Board State */
.empty-board {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: var(--spacing-md);
    color: var(--text-secondary);
    min-width: 100%;
}

.empty-board svg {
    opacity: 0.3;
}

.empty-board h3 {
    font-size: var(--text-2xl);
    font-weight: var(--font-semibold);
    color: var(--text-primary);
    margin: 0;
}

.empty-board p {
    margin: 0;
}

.btn-add {
    margin-top: var(--spacing-md);
    padding: 0.625rem 1.5rem;
    background: var(--orange-primary);
    color: white;
    border: none;
    border-radius: var(--radius-md);
    font-weight: var(--font-medium);
    cursor: pointer;
    transition: all var(--transition-normal);
}

.btn-add:hover {
    background: var(--orange-light);
    box-shadow: var(--shadow-orange);
    transform: translateY(-2px);
}

/* Confirmation Dialog */
.confirmation-dialog {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    animation: backdropFadeIn var(--transition-normal) ease-out;
}

.dialog-content {
    background: var(--black-primary);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--radius-lg);
    padding: var(--spacing-2xl);
    max-width: 400px;
    box-shadow: var(--shadow-lg);
    animation: modalSlideIn var(--transition-normal) ease-out;
}

.dialog-content h3 {
    font-size: var(--text-xl);
    margin: 0 0 var(--spacing-md) 0;
    color: var(--text-primary);
}

.dialog-content p {
    font-size: var(--text-sm);
    color: var(--text-secondary);
    margin: 0 0 var(--spacing-lg) 0;
    line-height: var(--line-height-relaxed);
}

.dialog-actions {
    display: flex;
    gap: var(--spacing-md);
    justify-content: flex-end;
}

.btn-cancel,
.btn-delete {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: var(--radius-md);
    font-weight: var(--font-medium);
    cursor: pointer;
    transition: all var(--transition-normal);
    font-size: var(--text-sm);
}

.btn-cancel {
    background: rgba(255, 255, 255, 0.1);
    color: var(--text-primary);
}

.btn-cancel:hover {
    background: rgba(255, 255, 255, 0.15);
}

.btn-delete {
    background: var(--red-primary);
    color: white;
}

.btn-delete:hover {
    background: var(--red-light);
}

/* Mobile Responsive */
@media (max-width: 639px) {
    .kanban-board-wrapper {
        height: 100vh;
    }

    .board-content {
        padding: var(--spacing-md);
        gap: var(--spacing-md);
    }

    .columns-section {
        gap: var(--spacing-md);
    }

    .dialog-content {
        padding: var(--spacing-lg);
        max-width: 90vw;
    }
}
</style>
