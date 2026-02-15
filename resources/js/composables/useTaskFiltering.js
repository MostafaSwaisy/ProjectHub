import { computed } from 'vue';
import { useKanbanStore } from '../stores/kanban';

/**
 * T047: Task Filtering Composable
 * Handles search and filtering logic for tasks
 */
export const useTaskFiltering = (tasksRef) => {
    const kanbanStore = useKanbanStore();

    /**
     * Check if task matches search query
     */
    const matchesSearchQuery = (task) => {
        if (!kanbanStore.searchQuery) return true;

        const query = kanbanStore.searchQuery.toLowerCase();
        return (
            task.title?.toLowerCase().includes(query) ||
            task.description?.toLowerCase().includes(query) ||
            task.id?.toString().includes(query)
        );
    };

    /**
     * Check if task matches selected labels
     */
    const matchesLabelFilter = (task) => {
        if (kanbanStore.selectedLabels.length === 0) return true;

        const taskLabelIds = (task.labels || []).map(l => l.id);
        return kanbanStore.selectedLabels.some(labelId =>
            taskLabelIds.includes(labelId)
        );
    };

    /**
     * Check if task matches selected assignees
     */
    const matchesAssigneeFilter = (task) => {
        if (kanbanStore.selectedAssignees.length === 0) return true;

        const taskAssigneeIds = (task.assignees || []).map(a => a.id);
        return kanbanStore.selectedAssignees.some(assigneeId =>
            taskAssigneeIds.includes(assigneeId)
        );
    };

    /**
     * Check if task matches selected priorities
     */
    const matchesPriorityFilter = (task) => {
        if (kanbanStore.selectedPriorities.length === 0) return true;

        return kanbanStore.selectedPriorities.includes(task.priority);
    };

    /**
     * Apply all filters to task
     */
    const matchesAllFilters = (task) => {
        return (
            matchesSearchQuery(task) &&
            matchesLabelFilter(task) &&
            matchesAssigneeFilter(task) &&
            matchesPriorityFilter(task)
        );
    };

    /**
     * Filter tasks by column ID
     */
    const getTasksByStatus = computed(() => {
        return (columnId) => {
            if (!tasksRef || !tasksRef.value) return [];

            return tasksRef.value
                .filter(task => task.column_id === columnId)
                .filter(matchesAllFilters)
                .sort((a, b) => sortTasks(a, b));
        };
    });

    /**
     * Sort tasks based on kanban store preferences
     */
    const sortTasks = (a, b) => {
        const field = kanbanStore.sortBy;
        const order = kanbanStore.sortOrder;
        let aVal, bVal;

        switch (field) {
            case 'due_date':
                aVal = new Date(a.due_date || '9999-12-31');
                bVal = new Date(b.due_date || '9999-12-31');
                break;
            case 'priority':
                // Priority order: high=1, medium=2, low=3
                const priorityOrder = { high: 1, medium: 2, low: 3 };
                aVal = priorityOrder[a.priority] || 999;
                bVal = priorityOrder[b.priority] || 999;
                break;
            case 'created_at':
            default:
                aVal = new Date(a.created_at || 0);
                bVal = new Date(b.created_at || 0);
        }

        const comparison = aVal < bVal ? -1 : aVal > bVal ? 1 : 0;
        return order === 'asc' ? comparison : -comparison;
    };

    /**
     * Get all unique labels from tasks
     */
    const getAllLabels = computed(() => {
        if (!tasksRef || !tasksRef.value) return [];

        const labelsMap = new Map();
        tasksRef.value.forEach(task => {
            (task.labels || []).forEach(label => {
                if (!labelsMap.has(label.id)) {
                    labelsMap.set(label.id, label);
                }
            });
        });

        return Array.from(labelsMap.values());
    });

    /**
     * Get all unique assignees from tasks
     */
    const getAllAssignees = computed(() => {
        if (!tasksRef || !tasksRef.value) return [];

        const assigneesMap = new Map();
        tasksRef.value.forEach(task => {
            (task.assignees || []).forEach(assignee => {
                if (!assigneesMap.has(assignee.id)) {
                    assigneesMap.set(assignee.id, assignee);
                }
            });
        });

        return Array.from(assigneesMap.values());
    });

    /**
     * Get all unique priorities from tasks
     */
    const getAllPriorities = computed(() => {
        if (!tasksRef || !tasksRef.value) return [];

        const priorities = new Set();
        tasksRef.value.forEach(task => {
            if (task.priority) {
                priorities.add(task.priority);
            }
        });

        // Return in standard priority order
        const priorityOrder = ['high', 'medium', 'low'];
        return priorityOrder.filter(p => priorities.has(p));
    });

    /**
     * Get count of tasks matching filters (by status)
     */
    const getFilteredTaskCount = computed(() => {
        return (status) => {
            return getTasksByStatus.value(status).length;
        };
    });

    /**
     * Get total tasks across all statuses
     */
    const getTotalFilteredTasks = computed(() => {
        if (!tasksRef || !tasksRef.value) return 0;
        return tasksRef.value.filter(matchesAllFilters).length;
    });

    /**
     * Clear all filters
     */
    const clearFilters = () => {
        kanbanStore.clearAllFilters();
    };

    /**
     * Reset filters to initial state
     */
    const resetSearch = () => {
        kanbanStore.setSearchQuery('');
    };

    return {
        // Computed filters
        getTasksByStatus,
        getAllLabels,
        getAllAssignees,
        getAllPriorities,
        getFilteredTaskCount,
        getTotalFilteredTasks,
        // Actions
        clearFilters,
        resetSearch,
        // Helper methods (exposed for testing)
        matchesAllFilters,
        matchesSearchQuery,
        matchesLabelFilter,
        matchesAssigneeFilter,
        matchesPriorityFilter,
    };
};
