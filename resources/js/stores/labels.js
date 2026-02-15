import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

/**
 * Labels Pinia Store
 * Manages project labels for task categorization
 */
export const useLabelsStore = defineStore('labels', () => {
    // State
    const labels = ref([]);
    const loading = ref(false);
    const error = ref(null);

    // Computed
    const labelCount = computed(() => labels.value.length);

    const getLabelById = computed(() => (id) => {
        return labels.value.find(l => l.id === id);
    });

    const getLabelsByIds = computed(() => (ids) => {
        return labels.value.filter(l => ids.includes(l.id));
    });

    // Actions: Fetch labels for a project
    const fetchLabels = async (projectId) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await axios.get(`/api/projects/${projectId}/labels`);
            labels.value = response.data.data || response.data;
            return labels.value;
        } catch (err) {
            error.value = err.response?.data?.message || err.message;
            console.error('Failed to fetch labels:', err);
            throw err;
        } finally {
            loading.value = false;
        }
    };

    // Actions: Create label
    const createLabel = async (projectId, labelData) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await axios.post(`/api/projects/${projectId}/labels`, labelData);
            const newLabel = response.data.data || response.data;
            labels.value.push(newLabel);
            return newLabel;
        } catch (err) {
            error.value = err.response?.data?.message || err.message;
            console.error('Failed to create label:', err);
            throw err;
        } finally {
            loading.value = false;
        }
    };

    // Actions: Update label
    const updateLabel = async (projectId, labelId, labelData) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await axios.put(
                `/api/projects/${projectId}/labels/${labelId}`,
                labelData
            );
            const updatedLabel = response.data.data || response.data;
            const index = labels.value.findIndex(l => l.id === labelId);
            if (index !== -1) {
                labels.value[index] = updatedLabel;
            }
            return updatedLabel;
        } catch (err) {
            error.value = err.response?.data?.message || err.message;
            console.error('Failed to update label:', err);
            throw err;
        } finally {
            loading.value = false;
        }
    };

    // Actions: Delete label
    const deleteLabel = async (projectId, labelId) => {
        loading.value = true;
        error.value = null;
        try {
            await axios.delete(`/api/projects/${projectId}/labels/${labelId}`);
            labels.value = labels.value.filter(l => l.id !== labelId);
            return true;
        } catch (err) {
            error.value = err.response?.data?.message || err.message;
            console.error('Failed to delete label:', err);
            throw err;
        } finally {
            loading.value = false;
        }
    };

    // Actions: Clear labels (when switching projects)
    const clearLabels = () => {
        labels.value = [];
        error.value = null;
    };

    // Actions: Reset store
    const resetLabels = () => {
        labels.value = [];
        loading.value = false;
        error.value = null;
    };

    return {
        // State
        labels,
        loading,
        error,
        // Computed
        labelCount,
        getLabelById,
        getLabelsByIds,
        // Actions
        fetchLabels,
        createLabel,
        updateLabel,
        deleteLabel,
        clearLabels,
        resetLabels,
    };
});
