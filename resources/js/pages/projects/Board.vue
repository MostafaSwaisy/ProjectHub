<template>
    <!-- T034: Project Board with design system colors and dark theme -->
    <div class="board-container">
        <div class="board-header">
            <h1 class="board-title">Project Board</h1>
            <p class="board-subtitle">Kanban board view - Phase 5 implementation</p>
        </div>

        <div class="board-content">
            <p class="board-placeholder">
                🚀 Full kanban board with drag-drop, task cards, labels, and filtering will be implemented in Phase 5
            </p>
        </div>
    </div>
</template>

<script setup>
// Project board component - to be implemented in Phase 5 (US2)
</script>

<style scoped>
/* T034: Design system consistency - Project Board */

.board-container {
    min-height: 100vh;
    background-color: var(--black-primary);
    color: var(--text-primary);
    padding: var(--spacing-lg);
}

.board-header {
    margin-bottom: var(--spacing-2xl);
}

.board-title {
    font-size: var(--text-3xl);
    font-weight: var(--font-bold);
    color: var(--text-primary);
    margin: 0 0 var(--spacing-sm) 0;
}

.board-subtitle {
    font-size: var(--text-base);
    color: var(--text-secondary);
    margin: 0;
}

.board-content {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 400px;
    background: rgba(255, 255, 255, 0.05);
    border: 2px dashed rgba(255, 255, 255, 0.1);
    border-radius: var(--radius-lg);
    padding: var(--spacing-2xl);
}

.board-placeholder {
    font-size: var(--text-lg);
    color: var(--text-secondary);
    text-align: center;
    margin: 0;
    line-height: var(--line-height-relaxed);
}

/* Mobile responsive */
@media (max-width: 639px) {
    .board-container {
        padding: var(--spacing-md);
    }

    .board-title {
        font-size: var(--text-2xl);
    }

    .board-content {
        min-height: 300px;
    }

    .board-placeholder {
        font-size: var(--text-base);
    }
}
</style>
