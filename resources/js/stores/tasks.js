import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

/**
 * Tasks Pinia Store
 * Manages task CRUD operations with optimistic updates and rollback
 */
export const useTasksStore = defineStore('tasks', () => {
    // State
    const tasks = ref([]);
    const currentTask = ref(null);
    const loading = ref(false);
    const error = ref(null);

    // Computed
    const taskCount = computed(() => tasks.value.length);
    const completedCount = computed(() =>
        tasks.value.filter(t => t.progress === 100).length
    );
    const overdueCount = computed(() =>
        tasks.value.filter(t => t.is_overdue).length
    );

    // Getters
    const getTaskById = computed(() => (id) => {
        return tasks.value.find(t => t.id === id);
    });

    const getTasksByColumnId = computed(() => (columnId) => {
        return tasks.value
            .filter(t => t.column_id === columnId)
            .sort((a, b) => a.position - b.position);
    });

    const getTasksByAssignee = computed(() => (assigneeId) => {
        return tasks.value.filter(t => t.assignee_id === assigneeId);
    });

    const getTasksByPriority = computed(() => (priority) => {
        return tasks.value.filter(t => t.priority === priority);
    });

    // Actions: Fetch all tasks with filters
    const fetchTasks = async (filters = {}) => {
        loading.value = true;
        error.value = null;
        try {
            const params = new URLSearchParams();
            if (filters.column_id) params.append('column_id', filters.column_id);
            if (filters.assignee_id) params.append('assignee_id', filters.assignee_id);
            if (filters.priority) params.append('priority', filters.priority);
            if (filters.due_date) params.append('due_date', filters.due_date);

            const url = `/api/tasks${params.toString() ? '?' + params.toString() : ''}`;
            const response = await axios.get(url);
            tasks.value = response.data.data || response.data;
            return tasks.value;
        } catch (err) {
            error.value = err.response?.data?.message || err.message;
            console.error('Failed to fetch tasks:', err);
            throw err;
        } finally {
            loading.value = false;
        }
    };

    // Actions: Fetch single task with full details
    const fetchTask = async (taskId) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await axios.get(`/api/tasks/${taskId}`);
            currentTask.value = response.data.data || response.data;

            // Also update in tasks array if exists
            const index = tasks.value.findIndex(t => t.id === taskId);
            if (index !== -1) {
                tasks.value[index] = currentTask.value;
            }

            return currentTask.value;
        } catch (err) {
            error.value = err.response?.data?.message || err.message;
            console.error('Failed to fetch task:', err);
            throw err;
        } finally {
            loading.value = false;
        }
    };

    // Actions: Create task with optimistic update
    const createTask = async (taskData) => {
        error.value = null;

        // Create optimistic task
        const tempId = `temp-${Date.now()}`;
        const optimisticTask = {
            id: tempId,
            ...taskData,
            position: tasks.value.filter(t => t.column_id === taskData.column_id).length,
            subtask_count: 0,
            completed_subtask_count: 0,
            comment_count: 0,
            label_count: 0,
            progress: 0,
            is_overdue: false,
            labels: [],
            subtasks: [],
            created_at: new Date().toISOString(),
            updated_at: new Date().toISOString(),
        };

        // Add optimistically
        tasks.value.push(optimisticTask);

        try {
            const response = await axios.post('/api/tasks', taskData);
            const newTask = response.data.data || response.data;

            // Replace temp task with real task
            const index = tasks.value.findIndex(t => t.id === tempId);
            if (index !== -1) {
                tasks.value[index] = newTask;
            }

            return newTask;
        } catch (err) {
            // Rollback on error
            tasks.value = tasks.value.filter(t => t.id !== tempId);
            error.value = err.response?.data?.message || err.message;
            console.error('Failed to create task:', err);
            throw err;
        }
    };

    // Actions: Update task with optimistic update
    const updateTask = async (taskId, taskData) => {
        error.value = null;

        const index = tasks.value.findIndex(t => t.id === taskId);
        if (index === -1) {
            throw new Error('Task not found');
        }

        // Store original for rollback
        const originalTask = { ...tasks.value[index] };

        // Apply optimistic update
        tasks.value[index] = { ...tasks.value[index], ...taskData };

        try {
            const response = await axios.put(`/api/tasks/${taskId}`, taskData);
            const updatedTask = response.data.data || response.data;

            // Update with server response
            tasks.value[index] = updatedTask;

            // Update currentTask if it matches
            if (currentTask.value?.id === taskId) {
                currentTask.value = updatedTask;
            }

            return updatedTask;
        } catch (err) {
            // Rollback on error
            tasks.value[index] = originalTask;
            error.value = err.response?.data?.message || err.message;
            console.error('Failed to update task:', err);
            throw err;
        }
    };

    // Actions: Delete task with optimistic update
    const deleteTask = async (taskId) => {
        error.value = null;

        const index = tasks.value.findIndex(t => t.id === taskId);
        if (index === -1) {
            throw new Error('Task not found');
        }

        // Store original for rollback
        const originalTask = tasks.value[index];
        const originalTasks = [...tasks.value];

        // Optimistic removal
        tasks.value = tasks.value.filter(t => t.id !== taskId);

        try {
            await axios.delete(`/api/tasks/${taskId}`);

            // Clear currentTask if it was deleted
            if (currentTask.value?.id === taskId) {
                currentTask.value = null;
            }

            return true;
        } catch (err) {
            // Rollback on error
            tasks.value = originalTasks;
            error.value = err.response?.data?.message || err.message;
            console.error('Failed to delete task:', err);
            throw err;
        }
    };

    // Actions: Move task (drag-and-drop) with optimistic update
    const moveTask = async (taskId, columnId, position) => {
        error.value = null;

        const index = tasks.value.findIndex(t => t.id === taskId);
        if (index === -1) {
            throw new Error('Task not found');
        }

        // Store original for rollback
        const originalTask = { ...tasks.value[index] };

        // Apply optimistic update
        tasks.value[index] = {
            ...tasks.value[index],
            column_id: columnId,
            position: position,
        };

        try {
            const response = await axios.patch(`/api/tasks/${taskId}/move`, {
                column_id: columnId,
                position: position,
            });
            const movedTask = response.data.data || response.data;

            // Update with server response
            tasks.value[index] = movedTask;

            return movedTask;
        } catch (err) {
            // Rollback on error
            tasks.value[index] = originalTask;
            error.value = err.response?.data?.message || err.message;
            console.error('Failed to move task:', err);
            throw err;
        }
    };

    // Actions: Sync labels for a task
    const syncLabels = async (taskId, labelIds) => {
        error.value = null;

        try {
            const response = await axios.post(`/api/tasks/${taskId}/labels`, {
                label_ids: labelIds,
            });
            const updatedTask = response.data.data || response.data;

            // Update in tasks array
            const index = tasks.value.findIndex(t => t.id === taskId);
            if (index !== -1) {
                tasks.value[index] = updatedTask;
            }

            // Update currentTask if it matches
            if (currentTask.value?.id === taskId) {
                currentTask.value = updatedTask;
            }

            return updatedTask;
        } catch (err) {
            error.value = err.response?.data?.message || err.message;
            console.error('Failed to sync labels:', err);
            throw err;
        }
    };

    // Actions: Local update for immediate UI changes
    const updateTaskLocal = (taskId, updates) => {
        const index = tasks.value.findIndex(t => t.id === taskId);
        if (index !== -1) {
            tasks.value[index] = { ...tasks.value[index], ...updates };
        }
    };

    // Actions: Reorder tasks within a column (within-column drag)
    const reorderTasks = async (columnId, taskIds) => {
        error.value = null;

        // Store original positions for rollback
        const originalTasks = tasks.value
            .filter(t => t.column_id === columnId)
            .map(t => ({ id: t.id, position: t.position }));

        // Apply optimistic reorder
        taskIds.forEach((taskId, index) => {
            const taskIndex = tasks.value.findIndex(t => t.id === taskId);
            if (taskIndex !== -1) {
                tasks.value[taskIndex] = { ...tasks.value[taskIndex], position: index };
            }
        });

        try {
            // Send reorder request to server (batch update)
            await Promise.all(
                taskIds.map((taskId, index) =>
                    axios.patch(`/api/tasks/${taskId}/move`, {
                        column_id: columnId,
                        position: index,
                    })
                )
            );
            return true;
        } catch (err) {
            // Rollback on error
            originalTasks.forEach(({ id, position }) => {
                const taskIndex = tasks.value.findIndex(t => t.id === id);
                if (taskIndex !== -1) {
                    tasks.value[taskIndex] = { ...tasks.value[taskIndex], position };
                }
            });
            error.value = err.response?.data?.message || err.message;
            console.error('Failed to reorder tasks:', err);
            throw err;
        }
    };

    // Actions: Reset store
    const resetTasks = () => {
        tasks.value = [];
        currentTask.value = null;
        loading.value = false;
        error.value = null;
    };

    // Actions: Set current task
    const setCurrentTask = (task) => {
        currentTask.value = task;
    };

    // Actions: Clear current task
    const clearCurrentTask = () => {
        currentTask.value = null;
    };

    return {
        // State
        tasks,
        currentTask,
        loading,
        error,
        // Computed
        taskCount,
        completedCount,
        overdueCount,
        getTaskById,
        getTasksByColumnId,
        getTasksByAssignee,
        getTasksByPriority,
        // Actions
        fetchTasks,
        fetchTask,
        createTask,
        updateTask,
        deleteTask,
        moveTask,
        reorderTasks,
        syncLabels,
        updateTaskLocal,
        resetTasks,
        setCurrentTask,
        clearCurrentTask,
    };
});
