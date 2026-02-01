<template>
    <!-- T048: Task Card with priority borders and hover effects -->
    <div
        class="task-card"
        :class="[
            `priority-${task.priority}`,
            { 'is-overdue': isOverdue, 'is-due-soon': isDueSoon }
        ]"
        draggable="true"
        @dragstart="$emit('drag-start', $event)"
        @dragend="$emit('drag-end', $event)"
        @click="openTaskDetails"
    >
        <!-- Priority Indicator Border -->
        <div class="priority-indicator"></div>

        <!-- Task Header -->
        <div class="task-header">
            <div class="task-id-title">
                <span class="task-id">{{ task.id }}</span>
                <h3 class="task-title">{{ task.title }}</h3>
            </div>
            <button
                class="task-menu-btn"
                @click.stop="toggleMenu"
            >
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                    <circle cx="12" cy="5" r="2"/>
                    <circle cx="12" cy="12" r="2"/>
                    <circle cx="12" cy="19" r="2"/>
                </svg>
            </button>
        </div>

        <!-- Task Menu Dropdown -->
        <div v-if="showMenu" class="task-menu">
            <button class="menu-item" @click.stop="$emit('edit')">Edit</button>
            <button class="menu-item" @click.stop="$emit('duplicate')">Duplicate</button>

            <!-- Mobile: "Move to..." option for task relocation -->
            <button class="menu-item move-to-btn" @click.stop="toggleMoveToMenu">
                Move to...
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" :class="{ 'rotate': showMoveToMenu }">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </button>

            <div v-if="showMoveToMenu" class="submenu">
                <button
                    v-for="column in availableColumns"
                    :key="column.id"
                    :disabled="column.id === task.status"
                    class="submenu-item"
                    @click.stop="moveTaskToColumn(column.id)"
                >
                    {{ column.title }}
                </button>
            </div>

            <button class="menu-item" @click.stop="$emit('archive')">Archive</button>
            <button class="menu-item menu-item-danger" @click.stop="$emit('delete')">Delete</button>
        </div>

        <!-- Task Description Preview -->
        <p v-if="task.description" class="task-description">
            {{ truncateText(task.description, 80) }}
        </p>

        <!-- Labels -->
        <div v-if="task.labels && task.labels.length > 0" class="task-labels">
            <span
                v-for="label in displayLabels"
                :key="label.id"
                class="label-badge"
                :style="{ backgroundColor: label.color }"
            >
                {{ label.name }}
            </span>
            <span v-if="hiddenLabelsCount > 0" class="label-more">
                +{{ hiddenLabelsCount }}
            </span>
        </div>

        <!-- Due Date & Assignee -->
        <div class="task-meta">
            <div v-if="task.due_date" class="due-date">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
                <span>{{ formatDueDate(task.due_date) }}</span>
            </div>
            <div v-if="task.assignees && task.assignees.length > 0" class="assignees">
                <div
                    v-for="assignee in task.assignees.slice(0, 2)"
                    :key="assignee.id"
                    class="assignee-avatar"
                    :title="assignee.name"
                >
                    {{ getInitials(assignee.name) }}
                </div>
                <span v-if="task.assignees.length > 2" class="assignee-more">
                    +{{ task.assignees.length - 2 }}
                </span>
            </div>
        </div>

        <!-- Subtask Progress -->
        <div v-if="task.subtasks && task.subtasks.length > 0" class="subtask-progress">
            <div class="progress-bar">
                <div
                    class="progress-fill"
                    :style="{ width: subtaskProgressPercent + '%' }"
                ></div>
            </div>
            <span class="progress-text">{{ completedSubtasks }}/{{ task.subtasks.length }}</span>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    task: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['drag-start', 'drag-end', 'edit', 'duplicate', 'archive', 'delete', 'details', 'move-to']);

// Local state
const showMenu = ref(false);
const showMoveToMenu = ref(false);

// Check if task is overdue or due soon
const isOverdue = computed(() => {
    if (!props.task.due_date || props.task.status === 'done') return false;
    return new Date(props.task.due_date) < new Date();
});

const isDueSoon = computed(() => {
    if (!props.task.due_date || props.task.status === 'done' || isOverdue.value) return false;
    const daysUntilDue = Math.ceil((new Date(props.task.due_date) - new Date()) / (1000 * 60 * 60 * 24));
    return daysUntilDue <= 2;
});

// Computed: Display max 3 labels, show +N for rest
const displayLabels = computed(() => {
    return (props.task.labels || []).slice(0, 3);
});

const hiddenLabelsCount = computed(() => {
    const total = props.task.labels?.length || 0;
    return Math.max(0, total - 3);
});

// Computed: Subtask progress
const completedSubtasks = computed(() => {
    return (props.task.subtasks || []).filter(s => s.completed).length;
});

const subtaskProgressPercent = computed(() => {
    const total = props.task.subtasks?.length || 0;
    if (total === 0) return 0;
    return Math.round((completedSubtasks.value / total) * 100);
});

// T093: Available columns for "Move to..." mobile menu
const availableColumns = computed(() => {
    return [
        { id: 'todo', title: 'To Do' },
        { id: 'in_progress', title: 'In Progress' },
        { id: 'in_review', title: 'In Review' },
        { id: 'done', title: 'Done' },
    ];
});

// Methods
const toggleMenu = () => {
    showMenu.value = !showMenu.value;
    showMoveToMenu.value = false;
};

// T093: Toggle "Move to..." submenu
const toggleMoveToMenu = () => {
    showMoveToMenu.value = !showMoveToMenu.value;
};

// T093: Move task to column (mobile alternative to drag-drop)
const moveTaskToColumn = (columnId) => {
    if (columnId !== props.task.status) {
        emit('move-to', columnId);
        showMenu.value = false;
        showMoveToMenu.value = false;
    }
};

const openTaskDetails = () => {
    emit('details');
};

const truncateText = (text, maxLength) => {
    if (!text || text.length <= maxLength) return text;
    return text.substring(0, maxLength) + '...';
};

const formatDueDate = (dateString) => {
    const date = new Date(dateString);
    const today = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);

    // Normalize dates to compare without time
    const dateNorm = new Date(date.getFullYear(), date.getMonth(), date.getDate());
    const todayNorm = new Date(today.getFullYear(), today.getMonth(), today.getDate());
    const tomorrowNorm = new Date(tomorrow.getFullYear(), tomorrow.getMonth(), tomorrow.getDate());

    if (dateNorm.getTime() === todayNorm.getTime()) {
        return 'Today';
    } else if (dateNorm.getTime() === tomorrowNorm.getTime()) {
        return 'Tomorrow';
    } else if (dateNorm < todayNorm) {
        const daysAgo = Math.floor((todayNorm - dateNorm) / (1000 * 60 * 60 * 24));
        return `${daysAgo}d ago`;
    } else {
        const daysUntil = Math.ceil((dateNorm - todayNorm) / (1000 * 60 * 60 * 24));
        return `in ${daysUntil}d`;
    }
};

const getInitials = (name) => {
    if (!name) return '?';
    return name
        .split(' ')
        .map(n => n[0])
        .join('')
        .toUpperCase()
        .substring(0, 2);
};
</script>

<style scoped>
/* T048: Task Card Styling */

.task-card {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--radius-md);
    padding: var(--spacing-md);
    cursor: grab;
    transition: all var(--transition-normal);
    position: relative;
    overflow: hidden;
}

.task-card:hover {
    background: rgba(255, 255, 255, 0.08);
    border-color: rgba(255, 255, 255, 0.2);
    box-shadow: var(--shadow-md);
    transform: translateY(-2px);
}

.task-card:active {
    cursor: grabbing;
}

.task-card.dragging {
    opacity: 0.5;
    transform: rotate(2deg);
}

/* Priority Indicator Border */
.priority-indicator {
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background-color: var(--gray-400);
}

.task-card.priority-high .priority-indicator {
    background-color: var(--red-primary);
    animation: urgentPulse 2s infinite;
}

.task-card.priority-medium .priority-indicator {
    background-color: var(--orange-primary);
}

.task-card.priority-low .priority-indicator {
    background-color: var(--green-primary);
}

/* Overdue/Due Soon Styling */
.task-card.is-overdue {
    border-color: rgba(239, 68, 68, 0.4);
    background: rgba(239, 68, 68, 0.05);
}

.task-card.is-due-soon {
    border-color: rgba(255, 107, 53, 0.4);
    background: rgba(255, 107, 53, 0.05);
}

/* Task Header */
.task-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: var(--spacing-sm);
    margin-bottom: var(--spacing-sm);
}

.task-id-title {
    display: flex;
    align-items: baseline;
    gap: var(--spacing-xs);
    flex: 1;
    min-width: 0;
}

.task-id {
    font-family: 'Monaco', 'Courier New', monospace;
    font-size: var(--text-xs);
    color: var(--text-secondary);
    font-weight: var(--font-medium);
    flex-shrink: 0;
}

.task-title {
    font-size: var(--text-sm);
    font-weight: var(--font-semibold);
    color: var(--text-primary);
    margin: 0;
    line-height: var(--line-height-snug);
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    word-break: break-word;
}

/* Task Menu */
.task-menu-btn {
    background: none;
    border: none;
    color: var(--text-secondary);
    cursor: pointer;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--radius-sm);
    transition: all var(--transition-normal);
    flex-shrink: 0;
}

.task-menu-btn:hover {
    background: rgba(255, 255, 255, 0.1);
    color: var(--text-primary);
}

.task-menu {
    position: absolute;
    top: 100%;
    right: 0;
    background: var(--black-primary);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--radius-md);
    overflow: hidden;
    z-index: 10;
    min-width: 120px;
    box-shadow: var(--shadow-lg);
    animation: dropdownIn var(--transition-normal) ease-out;
}

.menu-item {
    width: 100%;
    padding: var(--spacing-sm) var(--spacing-md);
    background: none;
    border: none;
    color: var(--text-primary);
    text-align: left;
    cursor: pointer;
    font-size: var(--text-sm);
    transition: all var(--transition-normal);
}

.menu-item:hover {
    background: rgba(255, 255, 255, 0.1);
    color: var(--orange-primary);
}

.menu-item-danger:hover {
    background: rgba(239, 68, 68, 0.2);
    color: var(--red-primary);
}

/* T093: Move to submenu */
.move-to-btn {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: var(--spacing-sm);
}

.move-to-btn svg {
    transition: transform var(--transition-normal);
}

.move-to-btn svg.rotate {
    transform: rotate(180deg);
}

.submenu {
    background: rgba(255, 255, 255, 0.05);
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    animation: dropdownIn var(--transition-normal) ease-out;
}

.submenu-item {
    width: 100%;
    padding: var(--spacing-sm) var(--spacing-md);
    background: none;
    border: none;
    color: var(--text-primary);
    text-align: left;
    cursor: pointer;
    font-size: var(--text-sm);
    transition: all var(--transition-normal);
    padding-left: calc(var(--spacing-md) + var(--spacing-md));
}

.submenu-item:hover:not(:disabled) {
    background: rgba(255, 255, 255, 0.1);
    color: var(--orange-primary);
}

.submenu-item:disabled {
    background: rgba(255, 107, 53, 0.1);
    color: var(--orange-primary);
    font-weight: var(--font-medium);
    cursor: default;
}

/* Task Description */
.task-description {
    font-size: var(--text-xs);
    color: var(--text-secondary);
    margin: 0 0 var(--spacing-sm) 0;
    line-height: var(--line-height-relaxed);
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

/* Task Labels */
.task-labels {
    display: flex;
    flex-wrap: wrap;
    gap: var(--spacing-xs);
    margin-bottom: var(--spacing-sm);
}

.label-badge {
    display: inline-block;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: var(--font-medium);
    color: white;
    white-space: nowrap;
}

.label-more {
    font-size: 11px;
    color: var(--text-secondary);
    padding: 2px 4px;
}

/* Task Meta (Due Date & Assignees) */
.task-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: var(--spacing-sm);
    margin-bottom: var(--spacing-sm);
    font-size: var(--text-xs);
}

.due-date {
    display: flex;
    align-items: center;
    gap: 4px;
    color: var(--text-secondary);
}

.task-card.is-overdue .due-date {
    color: var(--red-primary);
    font-weight: var(--font-medium);
}

.task-card.is-due-soon .due-date {
    color: var(--orange-primary);
    font-weight: var(--font-medium);
}

.assignees {
    display: flex;
    align-items: center;
    gap: 4px;
}

.assignee-avatar {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: var(--orange-primary);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    font-weight: var(--font-bold);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.assignee-more {
    font-size: 10px;
    color: var(--text-secondary);
    margin-left: 2px;
}

/* Subtask Progress */
.subtask-progress {
    display: flex;
    align-items: center;
    gap: var(--spacing-xs);
}

.progress-bar {
    flex: 1;
    height: 3px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 2px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: var(--orange-primary);
    transition: width var(--transition-normal);
}

.progress-text {
    font-size: 10px;
    color: var(--text-secondary);
    white-space: nowrap;
}

/* Mobile Responsive */
@media (max-width: 639px) {
    .task-card {
        padding: var(--spacing-sm);
    }

    .task-title {
        font-size: var(--text-xs);
    }

    .task-menu-btn {
        width: 36px;
        height: 36px;
    }
}
</style>
