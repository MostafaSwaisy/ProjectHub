<template>
    <!-- T054: Task Detail Modal for viewing task information -->
    <div class="modal-backdrop" @click.self="$emit('close')">
        <div class="modal-content">
            <div class="modal-header">
                <div class="header-left">
                    <div class="task-id">{{ task.id }}</div>
                    <h2>{{ task.title }}</h2>
                </div>
                <button class="close-btn" @click="$emit('close')">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <div class="modal-body">
                <!-- Task Description -->
                <div v-if="task.description" class="detail-section">
                    <h3>Description</h3>
                    <p>{{ task.description }}</p>
                </div>

                <!-- Status and Priority -->
                <div class="detail-grid">
                    <div class="detail-item">
                        <h4>Status</h4>
                        <div class="status-badge" :class="`status-${task.status}`">
                            {{ formatStatus(task.status) }}
                        </div>
                    </div>
                    <div class="detail-item">
                        <h4>Priority</h4>
                        <div class="priority-badge" :class="`priority-${task.priority}`">
                            {{ formatPriority(task.priority) }}
                        </div>
                    </div>
                </div>

                <!-- Due Date -->
                <div v-if="task.due_date" class="detail-section">
                    <h4>Due Date</h4>
                    <p>{{ formatDate(task.due_date) }}</p>
                </div>

                <!-- Labels -->
                <div v-if="task.labels && task.labels.length > 0" class="detail-section">
                    <h4>Labels</h4>
                    <div class="labels-container">
                        <span
                            v-for="label in task.labels"
                            :key="label.id"
                            class="label-badge"
                            :style="{ backgroundColor: label.color }"
                        >
                            {{ label.name }}
                        </span>
                    </div>
                </div>

                <!-- Assignee -->
                <div v-if="task.assignee" class="detail-section">
                    <h4>Assigned To</h4>
                    <div class="assignee-item">
                        <div class="assignee-avatar">{{ getInitials(task.assignee.name) }}</div>
                        <span>{{ task.assignee.name }}</span>
                    </div>
                </div>

                <!-- Subtasks Section -->
                <div class="detail-section">
                    <h4>Subtasks</h4>
                    <SubtaskList
                        :task-id="task.id"
                        @updated="$emit('updated')"
                    />
                </div>

                <!-- Comments Section -->
                <div class="detail-section">
                    <h4>Comments</h4>
                    <CommentList
                        :task-id="task.id"
                        @updated="$emit('updated')"
                    />
                </div>

                <!-- Created and Updated Info -->
                <div class="detail-meta">
                    <p>Created: {{ formatDate(task.created_at) }}</p>
                    <p v-if="task.updated_at">Updated: {{ formatDate(task.updated_at) }}</p>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer">
                <button class="btn btn-secondary" @click="$emit('edit')">
                    Edit Task
                </button>
                <button class="btn btn-danger" @click="confirmDelete">
                    Delete Task
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import SubtaskList from './SubtaskList.vue';
import CommentList from './CommentList.vue';

const props = defineProps({
    task: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['close', 'edit', 'delete', 'updated']);

// Methods
const formatStatus = (status) => {
    const map = {
        todo: 'To Do',
        in_progress: 'In Progress',
        in_review: 'In Review',
        done: 'Done',
        archived: 'Archived',
    };
    return map[status] || status;
};

const formatPriority = (priority) => {
    return priority?.charAt(0).toUpperCase() + priority?.slice(1);
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
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

const confirmDelete = () => {
    if (confirm('Are you sure you want to delete this task? This action cannot be undone.')) {
        emit('delete');
        emit('close');
    }
};
</script>

<style scoped>
/* T054: Task Detail Modal Styling */

.modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    animation: backdropFadeIn var(--transition-normal) ease-out;
}

.modal-content {
    background: rgba(10, 10, 10, 0.95);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--radius-lg);
    max-width: 600px;
    width: 90vw;
    max-height: 90vh;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    box-shadow: var(--shadow-lg);
    animation: modalSlideIn var(--transition-normal) ease-out;
}

/* Modal Header */
.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: var(--spacing-md);
    padding: var(--spacing-2xl);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    flex-shrink: 0;
}

.header-left {
    display: flex;
    align-items: baseline;
    gap: var(--spacing-md);
    flex: 1;
    min-width: 0;
}

.task-id {
    font-family: 'Monaco', 'Courier New', monospace;
    font-size: var(--text-sm);
    color: var(--text-secondary);
    font-weight: var(--font-medium);
    flex-shrink: 0;
}

.modal-header h2 {
    font-size: var(--text-2xl);
    font-weight: var(--font-bold);
    color: var(--text-primary);
    margin: 0;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    word-break: break-word;
}

.close-btn {
    background: none;
    border: none;
    color: var(--text-secondary);
    cursor: pointer;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all var(--transition-normal);
    flex-shrink: 0;
}

.close-btn:hover {
    color: var(--text-primary);
    transform: rotate(90deg);
}

/* Modal Body */
.modal-body {
    flex: 1;
    padding: var(--spacing-2xl);
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: var(--spacing-2xl);
}

.detail-section {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-sm);
}

.detail-section h3,
.detail-section h4 {
    font-size: var(--text-sm);
    font-weight: var(--font-semibold);
    color: var(--text-secondary);
    margin: 0;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.detail-section p {
    color: var(--text-primary);
    margin: 0;
    line-height: var(--line-height-relaxed);
}

/* Detail Grid */
.detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: var(--spacing-md);
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-sm);
}

.detail-item h4 {
    font-size: var(--text-xs);
    font-weight: var(--font-semibold);
    color: var(--text-secondary);
    margin: 0;
    text-transform: uppercase;
}

/* Status and Priority Badges */
.status-badge,
.priority-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: var(--text-xs);
    font-weight: var(--font-medium);
    width: fit-content;
}

.status-todo {
    background: rgba(100, 200, 255, 0.1);
    color: #64c8ff;
}

.status-in_progress {
    background: rgba(255, 107, 53, 0.1);
    color: var(--orange-primary);
}

.status-in_review {
    background: rgba(168, 85, 247, 0.1);
    color: #a855f7;
}

.status-done {
    background: rgba(34, 197, 94, 0.1);
    color: var(--green-primary);
}

.priority-high {
    background: rgba(239, 68, 68, 0.1);
    color: var(--red-primary);
}

.priority-medium {
    background: rgba(255, 107, 53, 0.1);
    color: var(--orange-primary);
}

.priority-low {
    background: rgba(34, 197, 94, 0.1);
    color: var(--green-primary);
}

/* Labels */
.labels-container {
    display: flex;
    flex-wrap: wrap;
    gap: var(--spacing-sm);
}

.label-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: var(--font-medium);
    color: white;
}

/* Assignees */
.assignees-list {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-sm);
}

.assignee-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
}

.assignee-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: var(--orange-primary);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: var(--font-bold);
    flex-shrink: 0;
}

/* Subtasks */
.subtasks-list {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-sm);
}

.subtask-item {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
}

.subtask-item input {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.subtask-item label {
    cursor: pointer;
    color: var(--text-primary);
    font-size: var(--text-sm);
}

.subtask-item input:checked + label {
    color: var(--text-secondary);
    text-decoration: line-through;
}

/* Metadata */
.detail-meta {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-xs);
    padding-top: var(--spacing-md);
    border-top: 1px solid rgba(255, 255, 255, 0.05);
}

.detail-meta p {
    font-size: var(--text-xs);
    color: var(--text-secondary);
    margin: 0;
}

/* Modal Footer */
.modal-footer {
    display: flex;
    gap: var(--spacing-md);
    padding: var(--spacing-2xl);
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    flex-shrink: 0;
}

.btn {
    flex: 1;
    padding: 0.625rem 1rem;
    border: none;
    border-radius: var(--radius-md);
    font-weight: var(--font-medium);
    font-size: var(--text-sm);
    cursor: pointer;
    transition: all var(--transition-normal);
}

.btn-secondary {
    background: rgba(255, 255, 255, 0.1);
    color: var(--text-primary);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.btn-secondary:hover {
    background: rgba(255, 255, 255, 0.15);
}

.btn-danger {
    background: rgba(239, 68, 68, 0.1);
    color: var(--red-primary);
}

.btn-danger:hover {
    background: rgba(239, 68, 68, 0.2);
}

/* Mobile Responsive */
@media (max-width: 639px) {
    .modal-content {
        max-width: 100%;
        width: 95vw;
        max-height: 95vh;
    }

    .modal-header,
    .modal-body,
    .modal-footer {
        padding: var(--spacing-lg);
    }

    .modal-header h2 {
        font-size: var(--text-lg);
    }

    .detail-grid {
        grid-template-columns: 1fr;
    }
}
</style>
