import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

/**
 * Subtasks Pinia Store
 * Manages subtask CRUD operations with optimistic updates
 */
export const useSubtasksStore = defineStore('subtasks', () => {
    // State
    const subtasks = ref([]);
    const loading = ref(false);
    const error = ref(null);

    // Computed: Get subtasks by task id
    const getSubtasksByTaskId = computed(() => (taskId) => {
        return subtasks.value
            .filter(s => s.task_id === taskId)
            .sort((a, b) => a.position - b.position);
    });

    // Computed: Get completion stats for a task
    const getTaskProgress = computed(() => (taskId) => {
        const taskSubtasks = subtasks.value.filter(s => s.task_id === taskId);
        const total = taskSubtasks.length;
        const completed = taskSubtasks.filter(s => s.is_completed).length;
        const percentage = total > 0 ? Math.round((completed / total) * 100) : 0;
        return { total, completed, percentage };
    });

    // Actions: Fetch subtasks for a task
    const fetchSubtasks = async (taskId) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await axios.get(`/api/tasks/${taskId}/subtasks`);
            const fetchedSubtasks = response.data.data || response.data;

            // Replace subtasks for this task
            subtasks.value = [
                ...subtasks.value.filter(s => s.task_id !== taskId),
                ...fetchedSubtasks,
            ];

            return fetchedSubtasks;
        } catch (err) {
            error.value = err.response?.data?.message || err.message;
            console.error('Failed to fetch subtasks:', err);
            throw err;
        } finally {
            loading.value = false;
        }
    };

    // Actions: Create subtask with optimistic update
    const createSubtask = async (taskId, subtaskData) => {
        error.value = null;

        // Create optimistic subtask
        const tempId = `temp-${Date.now()}`;
        const taskSubtasks = subtasks.value.filter(s => s.task_id === taskId);
        const optimisticSubtask = {
            id: tempId,
            task_id: taskId,
            title: subtaskData.title,
            is_completed: false,
            position: taskSubtasks.length,
            created_at: new Date().toISOString(),
            updated_at: new Date().toISOString(),
        };

        // Add optimistically
        subtasks.value.push(optimisticSubtask);

        try {
            const response = await axios.post(`/api/tasks/${taskId}/subtasks`, subtaskData);
            const newSubtask = response.data.data || response.data;

            // Replace temp subtask with real subtask
            const index = subtasks.value.findIndex(s => s.id === tempId);
            if (index !== -1) {
                subtasks.value[index] = newSubtask;
            }

            return newSubtask;
        } catch (err) {
            // Rollback on error
            subtasks.value = subtasks.value.filter(s => s.id !== tempId);
            error.value = err.response?.data?.message || err.message;
            console.error('Failed to create subtask:', err);
            throw err;
        }
    };

    // Actions: Update subtask with optimistic update
    const updateSubtask = async (taskId, subtaskId, subtaskData) => {
        error.value = null;

        const index = subtasks.value.findIndex(s => s.id === subtaskId);
        if (index === -1) {
            throw new Error('Subtask not found');
        }

        // Store original for rollback
        const originalSubtask = { ...subtasks.value[index] };

        // Apply optimistic update
        subtasks.value[index] = { ...subtasks.value[index], ...subtaskData };

        try {
            const response = await axios.put(`/api/tasks/${taskId}/subtasks/${subtaskId}`, subtaskData);
            const updatedSubtask = response.data.data || response.data;

            // Update with server response
            subtasks.value[index] = updatedSubtask;

            return updatedSubtask;
        } catch (err) {
            // Rollback on error
            subtasks.value[index] = originalSubtask;
            error.value = err.response?.data?.message || err.message;
            console.error('Failed to update subtask:', err);
            throw err;
        }
    };

    // Actions: Toggle subtask completion (optimistic)
    const toggleSubtask = async (taskId, subtaskId) => {
        const subtask = subtasks.value.find(s => s.id === subtaskId);
        if (!subtask) {
            throw new Error('Subtask not found');
        }

        return updateSubtask(taskId, subtaskId, {
            is_completed: !subtask.is_completed,
        });
    };

    // Actions: Delete subtask with optimistic update
    const deleteSubtask = async (taskId, subtaskId) => {
        error.value = null;

        const index = subtasks.value.findIndex(s => s.id === subtaskId);
        if (index === -1) {
            throw new Error('Subtask not found');
        }

        // Store original for rollback
        const originalSubtask = subtasks.value[index];
        const originalSubtasks = [...subtasks.value];

        // Optimistic removal
        subtasks.value = subtasks.value.filter(s => s.id !== subtaskId);

        try {
            await axios.delete(`/api/tasks/${taskId}/subtasks/${subtaskId}`);
            return true;
        } catch (err) {
            // Rollback on error
            subtasks.value = originalSubtasks;
            error.value = err.response?.data?.message || err.message;
            console.error('Failed to delete subtask:', err);
            throw err;
        }
    };

    // Actions: Reorder subtasks (optimistic)
    const reorderSubtasks = async (taskId, subtaskIds) => {
        error.value = null;

        // Store original positions for rollback
        const originalSubtasks = subtasks.value
            .filter(s => s.task_id === taskId)
            .map(s => ({ id: s.id, position: s.position }));

        // Apply optimistic reorder
        subtaskIds.forEach((id, index) => {
            const subtaskIndex = subtasks.value.findIndex(s => s.id === id);
            if (subtaskIndex !== -1) {
                subtasks.value[subtaskIndex] = {
                    ...subtasks.value[subtaskIndex],
                    position: index,
                };
            }
        });

        try {
            const response = await axios.post(`/api/tasks/${taskId}/subtasks/reorder`, {
                subtask_ids: subtaskIds,
            });
            const reorderedSubtasks = response.data.data || response.data;

            // Update with server response
            subtasks.value = [
                ...subtasks.value.filter(s => s.task_id !== taskId),
                ...reorderedSubtasks,
            ];

            return reorderedSubtasks;
        } catch (err) {
            // Rollback on error
            originalSubtasks.forEach(({ id, position }) => {
                const subtaskIndex = subtasks.value.findIndex(s => s.id === id);
                if (subtaskIndex !== -1) {
                    subtasks.value[subtaskIndex] = {
                        ...subtasks.value[subtaskIndex],
                        position,
                    };
                }
            });
            error.value = err.response?.data?.message || err.message;
            console.error('Failed to reorder subtasks:', err);
            throw err;
        }
    };

    // Actions: Clear subtasks for a task
    const clearSubtasks = (taskId) => {
        subtasks.value = subtasks.value.filter(s => s.task_id !== taskId);
    };

    // Actions: Reset store
    const resetSubtasks = () => {
        subtasks.value = [];
        loading.value = false;
        error.value = null;
    };

    return {
        // State
        subtasks,
        loading,
        error,
        // Computed
        getSubtasksByTaskId,
        getTaskProgress,
        // Actions
        fetchSubtasks,
        createSubtask,
        updateSubtask,
        toggleSubtask,
        deleteSubtask,
        reorderSubtasks,
        clearSubtasks,
        resetSubtasks,
    };
});
