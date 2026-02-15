<template>
    <!-- T050: Board Statistics Display -->
    <div class="board-stats">
        <div class="stat-item">
            <div class="stat-icon total">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <rect x="3" y="4" width="7" height="7"></rect>
                    <rect x="14" y="4" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect>
                    <rect x="14" y="14" width="7" height="7"></rect>
                </svg>
            </div>
            <div class="stat-content">
                <p class="stat-label">Total</p>
                <p class="stat-value">{{ totalTasks }}</p>
            </div>
        </div>

        <div class="stat-item">
            <div class="stat-icon in-progress">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <circle cx="12" cy="12" r="1"/>
                    <path d="M12 7a5 5 0 1 0 0 10 5 5 0 0 0 0-10"/>
                </svg>
            </div>
            <div class="stat-content">
                <p class="stat-label">In Progress</p>
                <p class="stat-value">{{ inProgressTasks }}</p>
            </div>
        </div>

        <div class="stat-item">
            <div class="stat-icon completed">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M9 16.2L4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4L9 16.2z"/>
                </svg>
            </div>
            <div class="stat-content">
                <p class="stat-label">Completed</p>
                <p class="stat-value">{{ completedTasks }}</p>
            </div>
        </div>

        <div class="stat-item">
            <div class="stat-icon overdue" :style="{ opacity: overdueCount > 0 ? 1 : 0.5 }">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"/>
                </svg>
            </div>
            <div class="stat-content">
                <p class="stat-label">Overdue</p>
                <p class="stat-value" :style="{ color: overdueCount > 0 ? 'var(--red-primary)' : 'var(--text-primary)' }">
                    {{ overdueCount }}
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useTasksStore } from '../../stores/tasks';
import { useKanbanStore } from '../../stores/kanban';

const tasksStore = useTasksStore();
const kanbanStore = useKanbanStore();

// Helper: find column ID by title (case-insensitive partial match)
const findColumnId = (keyword) => {
    const col = kanbanStore.columns.find(c =>
        c.title.toLowerCase().includes(keyword.toLowerCase())
    );
    return col ? col.id : null;
};

// Computed stats based on column placement
const totalTasks = computed(() => tasksStore.tasks.length);

const inProgressTasks = computed(() => {
    const colId = findColumnId('progress');
    if (!colId) return 0;
    return tasksStore.tasks.filter(t => t.column_id === colId).length;
});

const completedTasks = computed(() => {
    const colId = findColumnId('completed') || findColumnId('done');
    if (!colId) return 0;
    return tasksStore.tasks.filter(t => t.column_id === colId).length;
});

const overdueCount = computed(() => tasksStore.overdueCount);
</script>

<style scoped>
/* T050: Board Statistics Styling */

.board-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: var(--spacing-md);
}

.stat-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--radius-md);
    padding: var(--spacing-md);
    transition: all var(--transition-normal);
}

.stat-item:hover {
    background: rgba(255, 255, 255, 0.08);
    border-color: rgba(255, 255, 255, 0.2);
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.stat-icon {
    width: 40px;
    height: 40px;
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.stat-icon.total {
    background: rgba(100, 200, 255, 0.1);
    color: #64c8ff;
}

.stat-icon.in-progress {
    background: rgba(255, 107, 53, 0.1);
    color: var(--orange-primary);
}

.stat-icon.completed {
    background: rgba(34, 197, 94, 0.1);
    color: var(--green-primary);
}

.stat-icon.overdue {
    background: rgba(239, 68, 68, 0.1);
    color: var(--red-primary);
}

.stat-content {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.stat-label {
    font-size: var(--text-xs);
    color: var(--text-secondary);
    margin: 0;
    font-weight: var(--font-medium);
}

.stat-value {
    font-size: var(--text-2xl);
    font-weight: var(--font-bold);
    color: var(--text-primary);
    margin: 0;
    line-height: 1;
}

/* Mobile Responsive */
@media (max-width: 639px) {
    .board-stats {
        grid-template-columns: repeat(2, 1fr);
        gap: var(--spacing-sm);
    }

    .stat-item {
        flex-direction: column;
        text-align: center;
        padding: var(--spacing-sm);
    }

    .stat-icon {
        width: 32px;
        height: 32px;
    }

    .stat-value {
        font-size: var(--text-xl);
    }
}
</style>
