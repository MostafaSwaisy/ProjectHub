import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

/**
 * T045: Kanban Board Store
 * Manages kanban board state including columns, filters, and UI state
 */
export const useKanbanStore = defineStore('kanban', () => {
    // Board columns - Will be loaded from API
    const columns = ref([]);
    const boardId = ref(null);
    const loading = ref(false);
    const error = ref(null);

    // Filter state
    const searchQuery = ref('');
    const selectedLabels = ref([]);
    const selectedAssignees = ref([]);
    const selectedPriorities = ref([]);

    // UI state
    const draggedTaskId = ref(null);
    const draggedFromColumn = ref(null);
    const draggedOverColumn = ref(null);
    const isTaskModalOpen = ref(false);
    const editingTaskId = ref(null);
    const showTaskDetails = ref(false);

    // Sorting preferences
    const sortBy = ref('created_at'); // 'created_at', 'due_date', 'priority'
    const sortOrder = ref('asc'); // 'asc', 'desc'

    // Computed: Get column by ID
    const getColumnById = computed(() => (columnId) => {
        return columns.value.find(c => c.id === columnId);
    });

    // Computed: Check if any filters are active
    const hasActiveFilters = computed(() => {
        return searchQuery.value.length > 0 ||
               selectedLabels.value.length > 0 ||
               selectedAssignees.value.length > 0 ||
               selectedPriorities.value.length > 0;
    });

    // Computed: Get filter summary
    const filterSummary = computed(() => {
        const parts = [];
        if (searchQuery.value) parts.push(`Search: "${searchQuery.value}"`);
        if (selectedLabels.value.length > 0) parts.push(`Labels: ${selectedLabels.value.length}`);
        if (selectedAssignees.value.length > 0) parts.push(`Assignees: ${selectedAssignees.value.length}`);
        if (selectedPriorities.value.length > 0) parts.push(`Priorities: ${selectedPriorities.value.length}`);
        return parts.join(' • ');
    });

    // Actions: Update search query
    const setSearchQuery = (query) => {
        searchQuery.value = query;
    };

    // Actions: Toggle label filter
    const toggleLabelFilter = (labelId) => {
        const index = selectedLabels.value.indexOf(labelId);
        if (index > -1) {
            selectedLabels.value.splice(index, 1);
        } else {
            selectedLabels.value.push(labelId);
        }
    };

    // Actions: Toggle assignee filter
    const toggleAssigneeFilter = (assigneeId) => {
        const index = selectedAssignees.value.indexOf(assigneeId);
        if (index > -1) {
            selectedAssignees.value.splice(index, 1);
        } else {
            selectedAssignees.value.push(assigneeId);
        }
    };

    // Actions: Toggle priority filter
    const togglePriorityFilter = (priority) => {
        const index = selectedPriorities.value.indexOf(priority);
        if (index > -1) {
            selectedPriorities.value.splice(index, 1);
        } else {
            selectedPriorities.value.push(priority);
        }
    };

    // Actions: Clear all filters
    const clearAllFilters = () => {
        searchQuery.value = '';
        selectedLabels.value = [];
        selectedAssignees.value = [];
        selectedPriorities.value = [];
    };

    // Actions: Drag and drop
    const startDrag = (taskId, fromColumn) => {
        draggedTaskId.value = taskId;
        draggedFromColumn.value = fromColumn;
    };

    const setDragOverColumn = (columnId) => {
        draggedOverColumn.value = columnId;
    };

    const clearDrag = () => {
        draggedTaskId.value = null;
        draggedFromColumn.value = null;
        draggedOverColumn.value = null;
    };

    // Actions: Task modal
    const openCreateTaskModal = () => {
        editingTaskId.value = null;
        isTaskModalOpen.value = true;
    };

    const openEditTaskModal = (taskId) => {
        editingTaskId.value = taskId;
        isTaskModalOpen.value = true;
    };

    const closeTaskModal = () => {
        isTaskModalOpen.value = false;
        editingTaskId.value = null;
    };

    // Actions: Task details
    const openTaskDetails = (taskId) => {
        editingTaskId.value = taskId;
        showTaskDetails.value = true;
    };

    const closeTaskDetails = () => {
        showTaskDetails.value = false;
        editingTaskId.value = null;
    };

    // Actions: Sorting
    const setSortBy = (field) => {
        if (sortBy.value === field) {
            // Toggle order if clicking same field
            sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
        } else {
            sortBy.value = field;
            sortOrder.value = 'asc';
        }
    };

    // Actions: Fetch board with columns
    const fetchBoard = async (projectId) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await axios.get(`/api/projects/${projectId}/boards`);
            const boards = response.data.data || response.data;

            // Get the first board (assuming one board per project for now)
            const board = Array.isArray(boards) ? boards[0] : boards;

            if (board && board.columns) {
                boardId.value = board.id;
                // Remove duplicates by id and sort by position
                const uniqueColumns = Array.from(
                    new Map(board.columns.map(col => [col.id, col])).values()
                );
                columns.value = uniqueColumns.sort((a, b) => a.position - b.position);
            }

            return board;
        } catch (err) {
            error.value = err.response?.data?.message || err.message;
            console.error('Failed to fetch board:', err);
            throw err;
        } finally {
            loading.value = false;
        }
    };

    // Actions: Reset board state
    const resetBoardState = () => {
        searchQuery.value = '';
        selectedLabels.value = [];
        selectedAssignees.value = [];
        selectedPriorities.value = [];
        draggedTaskId.value = null;
        draggedFromColumn.value = null;
        draggedOverColumn.value = null;
        isTaskModalOpen.value = false;
        editingTaskId.value = null;
        showTaskDetails.value = false;
        sortBy.value = 'created_at';
        sortOrder.value = 'asc';
    };

    return {
        // State
        columns,
        boardId,
        loading,
        error,
        searchQuery,
        selectedLabels,
        selectedAssignees,
        selectedPriorities,
        draggedTaskId,
        draggedFromColumn,
        draggedOverColumn,
        isTaskModalOpen,
        editingTaskId,
        showTaskDetails,
        sortBy,
        sortOrder,
        // Computed
        getColumnById,
        hasActiveFilters,
        filterSummary,
        // Actions
        fetchBoard,
        setSearchQuery,
        toggleLabelFilter,
        toggleAssigneeFilter,
        togglePriorityFilter,
        clearAllFilters,
        startDrag,
        setDragOverColumn,
        clearDrag,
        openCreateTaskModal,
        openEditTaskModal,
        closeTaskModal,
        openTaskDetails,
        closeTaskDetails,
        setSortBy,
        resetBoardState,
    };
});
