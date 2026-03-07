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
    const orphanedItem = ref(null); // State for handling orphaned items
    const availableColumns = ref([]);

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

    // Actions: Clear orphaned item state
    const clearOrphanedItem = () => {
        orphanedItem.value = null;
        availableColumns.value = [];
    };

    // Actions: Restore a trash item
    const restoreItem = async (projectId, type, id, columnId = null) => {
        loading.value = true;
        error.value = null;

        try {
            const payload = {
                type,
                id,
            };

            if (columnId !== null) {
                payload.column_id = columnId;
            }

            const response = await axios.post(`/api/projects/${projectId}/restore`, payload);

            // Remove the restored item from the trash list
            items.value = items.value.filter(item => !(item.type === type && item.id === id));

            // Clear orphaned item state
            clearOrphanedItem();

            return {
                success: true,
                data: response.data.data,
            };
        } catch (err) {
            // Handle 409 Conflict - orphaned parent
            if (err.response?.status === 409) {
                const data = err.response.data;

                if (data.type === 'orphaned_task') {
                    orphanedItem.value = {
                        type,
                        id,
                        message: data.message,
                    };
                    availableColumns.value = data.available_columns || [];

                    return {
                        success: false,
                        orphaned: true,
                        type: data.type,
                        message: data.message,
                        availableColumns: data.available_columns,
                    };
                }
            }

            error.value = err.response?.data?.message || 'Failed to restore item';
            console.error('Error restoring item:', err);

            return {
                success: false,
                orphaned: false,
                error: error.value,
            };
        } finally {
            loading.value = false;
        }
    };

    // Actions: Force delete a trash item
    const forceDeleteItem = async (projectId, type, id) => {
        loading.value = true;
        error.value = null;

        try {
            const payload = {
                type,
                id,
            };

            await axios.delete(`/api/projects/${projectId}/force`, { data: payload });

            // Remove the force-deleted item from the trash list
            items.value = items.value.filter(item => !(item.type === type && item.id === id));

            return {
                success: true,
            };
        } catch (err) {
            // Handle 403 Forbidden - permission denied
            if (err.response?.status === 403) {
                error.value = 'You do not have permission to permanently delete this item.';
                console.error('Permission denied for force delete:', err);

                return {
                    success: false,
                    permissionDenied: true,
                    error: error.value,
                };
            }

            error.value = err.response?.data?.message || 'Failed to permanently delete item';
            console.error('Error force deleting item:', err);

            return {
                success: false,
                permissionDenied: false,
                error: error.value,
            };
        } finally {
            loading.value = false;
        }
    };

    return {
        // State
        items,
        loading,
        error,
        pagination,
        activeFilter,
        orphanedItem,
        availableColumns,

        // Getters
        filteredItems,
        hasItems,
        isEmpty,

        // Actions
        fetchTrashItems,
        setFilter,
        resetFilter,
        clearItems,
        clearOrphanedItem,
        restoreItem,
        forceDeleteItem,
    };
});
