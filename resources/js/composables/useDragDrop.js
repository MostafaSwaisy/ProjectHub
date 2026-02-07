import { ref } from 'vue';
import { useKanbanStore } from '../stores/kanban';
import { useTasksStore } from '../stores/tasks';
import { useToast } from './useToast';

/**
 * Drag and Drop Composable
 * Handles HTML5 native drag-drop API for kanban board with WIP limit checking
 */
export const useDragDrop = () => {
    const kanbanStore = useKanbanStore();
    const tasksStore = useTasksStore();
    const toast = useToast();
    const dragPreview = ref(null);
    const isAnimatingCancel = ref(false);
    const wipLimitError = ref(null);

    /**
     * Check if dropping to column would exceed WIP limit
     */
    const checkWipLimit = (targetColumn) => {
        if (!targetColumn || !targetColumn.wip_limit || targetColumn.wip_limit === 0) {
            return { allowed: true };
        }

        const tasksInColumn = tasksStore.getTasksByColumnId(targetColumn.id);
        const currentCount = tasksInColumn.length;

        if (currentCount >= targetColumn.wip_limit) {
            return {
                allowed: false,
                message: `Column "${targetColumn.title}" has reached its WIP limit of ${targetColumn.wip_limit}`,
                wipLimit: targetColumn.wip_limit,
                currentCount: currentCount,
            };
        }

        return { allowed: true };
    };

    /**
     * Handle dragstart event on task card
     */
    const handleDragStart = (event, taskId, fromColumnId) => {
        if (!event.dataTransfer) return;

        wipLimitError.value = null;
        kanbanStore.startDrag(taskId, fromColumnId);
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', taskId.toString());

        // Add visual feedback class
        event.target.classList.add('dragging');
    };

    /**
     * Handle dragover event on column
     */
    const handleDragOver = (event, columnId, column = null) => {
        if (!event.dataTransfer) return;

        event.preventDefault();

        // Check WIP limit if column data is provided
        if (column && kanbanStore.draggedFromColumn !== columnId) {
            const wipCheck = checkWipLimit(column);
            if (!wipCheck.allowed) {
                event.dataTransfer.dropEffect = 'none';
                wipLimitError.value = wipCheck;
                return;
            }
        }

        event.dataTransfer.dropEffect = 'move';
        wipLimitError.value = null;
        kanbanStore.setDragOverColumn(columnId);
    };

    /**
     * Handle dragleave event on column
     */
    const handleDragLeave = (event) => {
        // Only clear if leaving the actual column element
        if (event.target.classList?.contains('kanban-column')) {
            kanbanStore.setDragOverColumn(null);
            wipLimitError.value = null;
        }
    };

    /**
     * Handle drop event on column
     */
    const handleDrop = async (event, toColumnId, column = null) => {
        event.preventDefault();
        if (!event.dataTransfer) return;

        const taskId = parseInt(kanbanStore.draggedTaskId);
        const fromColumnId = kanbanStore.draggedFromColumn;

        // Don't allow drop if invalid
        if (!taskId || fromColumnId === undefined) {
            kanbanStore.clearDrag();
            return;
        }

        // Check WIP limit before dropping
        if (column && fromColumnId !== toColumnId) {
            const wipCheck = checkWipLimit(column);
            if (!wipCheck.allowed) {
                wipLimitError.value = wipCheck;
                toast.warning(wipCheck.message, 'WIP Limit Exceeded');
                kanbanStore.clearDrag();
                kanbanStore.setDragOverColumn(null);
                return;
            }
        }

        try {
            // Calculate new position (add to end of column)
            const tasksInColumn = tasksStore.getTasksByColumnId(toColumnId);
            const newPosition = tasksInColumn.length;

            // Use moveTask action with optimistic update
            await tasksStore.moveTask(taskId, toColumnId, newPosition);
            wipLimitError.value = null;
        } catch (error) {
            console.error('Failed to move task:', error);

            // Check if it's a WIP limit error from server
            if (error.response?.status === 422 && error.response?.data?.wip_limit) {
                wipLimitError.value = {
                    allowed: false,
                    message: error.response.data.message,
                    wipLimit: error.response.data.wip_limit,
                    currentCount: error.response.data.current_count,
                };
                toast.warning(error.response.data.message, 'WIP Limit Exceeded');
            } else {
                toast.error('Failed to move task. Please try again.', 'Error');
            }
        } finally {
            kanbanStore.clearDrag();
            kanbanStore.setDragOverColumn(null);
        }
    };

    /**
     * Handle dragend event
     */
    const handleDragEnd = (event) => {
        // Remove dragging class
        if (event.target?.classList) {
            event.target.classList.remove('dragging');
        }

        // Clear drag state
        kanbanStore.clearDrag();
        kanbanStore.setDragOverColumn(null);

        // If drop was rejected (not on valid target), show cancel animation
        if (event.dataTransfer?.dropEffect === 'none') {
            isAnimatingCancel.value = true;
            setTimeout(() => {
                isAnimatingCancel.value = false;
            }, 300);
        }
    };

    /**
     * Mobile: Start long-press drag on mobile devices
     */
    const handleTouchStart = (event, taskId, fromColumnId) => {
        wipLimitError.value = null;
        const touchTimeout = setTimeout(() => {
            kanbanStore.startDrag(taskId, fromColumnId);
            event.target.classList.add('dragging-mobile');
        }, 500); // 500ms long-press

        // Cancel timeout on touch end
        const handleTouchEnd = () => {
            clearTimeout(touchTimeout);
            document.removeEventListener('touchend', handleTouchEnd);
        };

        document.addEventListener('touchend', handleTouchEnd);
    };

    /**
     * Handle touch move for drag feedback on mobile
     */
    const handleTouchMove = (event) => {
        if (kanbanStore.draggedTaskId) {
            event.preventDefault();
        }
    };

    /**
     * Handle touch end for mobile drag
     */
    const handleTouchEnd = async (event, toColumnId, column = null) => {
        if (!kanbanStore.draggedTaskId) return;

        const taskId = parseInt(kanbanStore.draggedTaskId);
        const fromColumnId = kanbanStore.draggedFromColumn;

        if (toColumnId !== undefined && fromColumnId !== undefined) {
            await handleDrop({ dataTransfer: { dropEffect: 'move' }, preventDefault: () => {} }, toColumnId, column);
        } else {
            kanbanStore.clearDrag();
        }

        // Remove mobile dragging class
        event.target?.classList?.remove('dragging-mobile');
    };

    /**
     * Check if currently dragging
     */
    const isDragging = () => {
        return kanbanStore.draggedTaskId !== null;
    };

    /**
     * Check if column is drag target
     */
    const isDragTarget = (columnId) => {
        return kanbanStore.draggedOverColumn === columnId;
    };

    /**
     * Clear WIP limit error
     */
    const clearWipError = () => {
        wipLimitError.value = null;
    };

    return {
        dragPreview,
        isAnimatingCancel,
        wipLimitError,
        handleDragStart,
        handleDragOver,
        handleDragLeave,
        handleDrop,
        handleDragEnd,
        handleTouchStart,
        handleTouchMove,
        handleTouchEnd,
        isDragging,
        isDragTarget,
        checkWipLimit,
        clearWipError,
    };
};
