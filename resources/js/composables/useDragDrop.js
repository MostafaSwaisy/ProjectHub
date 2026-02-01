import { ref } from 'vue';
import { useKanbanStore } from '../stores/kanban';

/**
 * T046: Drag and Drop Composable
 * Handles HTML5 native drag-drop API for kanban board
 */
export const useDragDrop = () => {
    const kanbanStore = useKanbanStore();
    const dragPreview = ref(null);
    const isAnimatingCancel = ref(false);

    /**
     * Handle dragstart event on task card
     */
    const handleDragStart = (event, taskId, fromColumn) => {
        if (!event.dataTransfer) return;

        kanbanStore.startDrag(taskId, fromColumn);
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/html', event.target.innerHTML);

        // Create custom drag image (optional - can be enhanced with preview)
        const dragImage = new Image();
        event.dataTransfer.setDragImage(dragImage, 0, 0);

        // Add visual feedback class
        event.target.classList.add('dragging');
    };

    /**
     * Handle dragover event on column
     */
    const handleDragOver = (event, columnId) => {
        if (!event.dataTransfer) return;

        event.preventDefault();
        event.dataTransfer.dropEffect = 'move';
        kanbanStore.setDragOverColumn(columnId);
    };

    /**
     * Handle dragleave event on column
     */
    const handleDragLeave = (event) => {
        // Only clear if leaving the actual column element
        if (event.target.classList?.contains('kanban-column')) {
            kanbanStore.setDragOverColumn(null);
        }
    };

    /**
     * Handle drop event on column
     */
    const handleDrop = async (event, toColumn, tasksStore) => {
        event.preventDefault();
        if (!event.dataTransfer) return;

        const taskId = parseInt(kanbanStore.draggedTaskId);
        const fromColumn = kanbanStore.draggedFromColumn;

        // Don't allow drop if invalid
        if (!taskId || !fromColumn || !tasksStore) {
            kanbanStore.clearDrag();
            return;
        }

        try {
            // Optimistically update local state
            tasksStore.updateTaskLocal(taskId, { status: toColumn });

            // Send update to server
            const projectId = getCurrentProjectId();
            await tasksStore.updateTask(projectId, taskId, { status: toColumn });
        } catch (error) {
            // Revert on error
            console.error('Failed to move task:', error);
            tasksStore.updateTaskLocal(taskId, { status: fromColumn });
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
    const handleTouchStart = (event, taskId, fromColumn) => {
        const touchTimeout = setTimeout(() => {
            kanbanStore.startDrag(taskId, fromColumn);
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
    const handleTouchEnd = (event, toColumn, tasksStore) => {
        if (!kanbanStore.draggedTaskId) return;

        const taskId = parseInt(kanbanStore.draggedTaskId);
        const fromColumn = kanbanStore.draggedFromColumn;

        if (toColumn && fromColumn && tasksStore) {
            handleDrop({ dataTransfer: { dropEffect: 'move' } }, toColumn, tasksStore);
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
     * Get current project ID from route or store
     * This needs to be implemented based on your routing setup
     */
    const getCurrentProjectId = () => {
        // TODO: Get from route params or store
        return 1;
    };

    return {
        dragPreview,
        isAnimatingCancel,
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
    };
};
