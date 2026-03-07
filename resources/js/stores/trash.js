import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

/**
 * Trash Pinia Store
 * Manages soft-deleted items in project trash
 */
export const useTrashStore = defineStore('trash', () => {
    // State
    const items = ref([]);
    const loading = ref(false);
    const error = ref(null);
    const pagination = ref({
        current_page: 1,
        per_page: 20,
        total: 0,
        last_page: 1,
    });
    const activeFilter = ref(null); // null for all, or: tasks, boards, columns, subtasks, comments

    // Computed
    const filteredItems = computed(() => {
        if (!activeFilter.value) {
            return items.value;
        }
        return items.value.filter(item => item.type === activeFilter.value);
    });

    const hasItems = computed(() => items.value.length > 0);

    const isEmpty = computed(() => items.value.length === 0);

    // Actions: Fetch trash items
    const fetchTrashItems = async (projectId, type = null, page = 1, perPage = 20) => {
        loading.value = true;
        error.value = null;

        try {
            const params = new URLSearchParams();
            if (type) params.append('type', type);
            params.append('page', page);
            params.append('per_page', perPage);

            const url = `/api/projects/${projectId}/trash${params.toString() ? '?' + params.toString() : ''}`;
            const response = await axios.get(url);

            items.value = response.data.data || [];
            pagination.value = response.data.meta || {
                current_page: 1,
                per_page: perPage,
                total: items.value.length,
                last_page: 1,
            };

            return items.value;
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to fetch trash items';
            console.error('Error fetching trash items:', err);
            throw err;
        } finally {
            loading.value = false;
        }
    };

    // Actions: Set active filter
    const setFilter = (type) => {
        activeFilter.value = type;
    };

    // Actions: Reset filter
    const resetFilter = () => {
        activeFilter.value = null;
    };

    // Actions: Clear trash items from state (after successful operation)
    const clearItems = () => {
        items.value = [];
        pagination.value = {
            current_page: 1,
            per_page: 20,
            total: 0,
            last_page: 1,
        };
    };

    return {
        // State
        items,
        loading,
        error,
        pagination,
        activeFilter,

        // Getters
        filteredItems,
        hasItems,
        isEmpty,

        // Actions
        fetchTrashItems,
        setFilter,
        resetFilter,
        clearItems,
    };
});
