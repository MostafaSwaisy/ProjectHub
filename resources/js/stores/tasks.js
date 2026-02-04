import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

/**
 * T044: Tasks Pinia Store
 * Manages task CRUD operations and task state
 */
export const useTasksStore = defineStore('tasks', () => {
    // State
    const tasks = ref([]);
    const loading = ref(false);
    const error = ref(null);

    // Computed
    const taskCount = computed(() => tasks.value.length);
    const completedCount = computed(() =>
        tasks.value.filter(t => t.status === 'done').length
    );
    const inProgressCount = computed(() =>
        tasks.value.filter(t => t.status === 'in_progress').length
    );
    const overdueCount = computed(() =>
        tasks.value.filter(t => {
            if (!t.due_date || t.status === 'done') return false;
            return new Date(t.due_date) < new Date() && t.status !== 'done';
        }).length
    );

    // Getters
    const getTaskById = computed(() => (id) => {
        return tasks.value.find(t => t.id === id);
    });

    const getTasksByStatus = computed(() => (status) => {
        return tasks.value.filter(t => t.status === status);
    });

    // Actions: Fetch
    const fetchTasks = async (projectId) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await axios.get(`/api/projects/${projectId}/tasks`);
            tasks.value = response.data.data || response.data;
            return tasks.value;
        } catch (err) {
            error.value = err.message;
            console.error('Failed to fetch tasks:', err);
            throw err;
        } finally {
            loading.value = false;
        }
    };

    // Actions: Create
    const createTask = async (projectId, taskData) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await axios.post(
                `/api/projects/${projectId}/tasks`,
                taskData
            );
            const newTask = response.data.data || response.data;
            tasks.value.push(newTask);
            return newTask;
        } catch (err) {
            error.value = err.message;
            console.error('Failed to create task:', err);
            throw err;
        } finally {
            loading.value = false;
        }
    };

    // Actions: Update
    const updateTask = async (projectId, taskId, taskData) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await axios.put(
                `/api/projects/${projectId}/tasks/${taskId}`,
                taskData
            );
            const updatedTask = response.data.data || response.data;
            const index = tasks.value.findIndex(t => t.id === taskId);
            if (index !== -1) {
                tasks.value[index] = updatedTask;
            }
            return updatedTask;
        } catch (err) {
            error.value = err.message;
            console.error('Failed to update task:', err);
            throw err;
        } finally {
            loading.value = false;
        }
    };

    // Actions: Delete
    const deleteTask = async (projectId, taskId) => {
        loading.value = true;
        error.value = null;
        try {
            await axios.delete(`/api/projects/${projectId}/tasks/${taskId}`);
            tasks.value = tasks.value.filter(t => t.id !== taskId);
            return true;
        } catch (err) {
            error.value = err.message;
            console.error('Failed to delete task:', err);
            throw err;
        } finally {
            loading.value = false;
        }
    };

    // Actions: Change Status (Move between columns)
    const changeTaskStatus = async (projectId, taskId, newStatus) => {
        const task = getTaskById.value(taskId);
        if (!task) return;
        return updateTask(projectId, taskId, { status: newStatus });
    };

    // Actions: Local Update (Optimistic update for drag-drop)
    const updateTaskLocal = (taskId, updates) => {
        const index = tasks.value.findIndex(t => t.id === taskId);
        if (index !== -1) {
            tasks.value[index] = { ...tasks.value[index], ...updates };
        }
    };

    // Actions: Duplicate
    const duplicateTask = async (projectId, taskId) => {
        const originalTask = getTaskById.value(taskId);
        if (!originalTask) throw new Error('Task not found');

        const { id, created_at, updated_at, ...taskData } = originalTask;
        taskData.title = `${taskData.title} (Copy)`;

        return createTask(projectId, taskData);
    };

    // Actions: Archive
    const archiveTask = async (projectId, taskId) => {
        return updateTask(projectId, taskId, { status: 'archived' });
    };

    // Actions: Reset
    const resetTasks = () => {
        tasks.value = [];
        loading.value = false;
        error.value = null;
    };

    return {
        // State
        tasks,
        loading,
        error,
        // Computed
        taskCount,
        completedCount,
        inProgressCount,
        overdueCount,
        getTaskById,
        getTasksByStatus,
        // Actions
        fetchTasks,
        createTask,
        updateTask,
        deleteTask,
        changeTaskStatus,
        updateTaskLocal,
        duplicateTask,
        archiveTask,
        resetTasks,
    };
});
