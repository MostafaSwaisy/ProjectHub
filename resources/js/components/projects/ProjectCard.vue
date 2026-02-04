<template>
    <div
        class="project-card"
        :class="{ 'archived': project.is_archived }"
        @click="handleCardClick"
    >
        <!-- Project Header -->
        <div class="project-header">
            <h3 class="project-title">{{ project.title }}</h3>
            <div class="project-actions" @click.stop>
                <button
                    v-if="project.permissions.can_edit"
                    class="action-btn"
                    @click="$emit('edit', project)"
                    title="Edit"
                >
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </button>
                <button
                    v-if="project.permissions.can_archive && !project.is_archived"
                    class="action-btn"
                    @click="$emit('archive', project)"
                    title="Archive"
                >
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                    </svg>
                </button>
                <button
                    v-if="project.permissions.can_delete"
                    class="action-btn danger"
                    @click="$emit('delete', project)"
                    title="Delete"
                >
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Project Description -->
        <p class="project-description">
            {{ truncatedDescription }}
        </p>

        <!-- Status Badges -->
        <div class="status-badges">
            <span class="badge" :class="timelineStatusClass">
                {{ project.timeline_status }}
            </span>
            <span class="badge" :class="budgetStatusClass">
                {{ project.budget_status }}
            </span>
            <span v-if="project.is_archived" class="badge badge-archived">
                Archived
            </span>
        </div>

        <!-- Task Progress -->
        <div class="progress-section">
            <div class="progress-header">
                <span class="progress-text">
                    {{ project.task_completion.completed }} / {{ project.task_completion.total }} tasks
                </span>
                <span class="progress-percentage">
                    {{ project.task_completion.percentage }}%
                </span>
            </div>
            <div class="progress-bar">
                <div
                    class="progress-fill"
                    :style="{ width: project.task_completion.percentage + '%' }"
                ></div>
            </div>
        </div>

        <!-- Team Members -->
        <div class="team-members">
            <div class="avatars">
                <div
                    v-for="(member, index) in displayMembers"
                    :key="member.id"
                    class="avatar"
                    :style="{ zIndex: displayMembers.length - index }"
                    :title="member.name"
                >
                    {{ member.name.charAt(0).toUpperCase() }}
                </div>
                <div v-if="remainingCount > 0" class="avatar more">
                    +{{ remainingCount }}
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    project: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['click', 'edit', 'archive', 'delete']);

const truncatedDescription = computed(() => {
    if (!props.project.description) return 'No description';
    return props.project.description.length > 150
        ? props.project.description.substring(0, 150) + '...'
        : props.project.description;
});

const timelineStatusClass = computed(() => {
    const status = props.project.timeline_status;
    if (status === 'On Track') return 'badge-success';
    if (status === 'At Risk') return 'badge-warning';
    if (status === 'Ahead') return 'badge-info';
    return '';
});

const budgetStatusClass = computed(() => {
    const status = props.project.budget_status;
    return status === 'Within Budget' ? 'badge-success' : 'badge-danger';
});

const displayMembers = computed(() => {
    const members = props.project.members || [];
    return members.slice(0, 5);
});

const remainingCount = computed(() => {
    const total = props.project.total_members || 0;
    return Math.max(0, total - 5);
});

const handleCardClick = () => {
    emit('click', props.project);
};
</script>

<style scoped>
.project-card {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(148, 163, 184, 0.2);
    border-radius: 0.75rem;
    padding: 1.5rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.project-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
    border-color: rgba(148, 163, 184, 0.4);
}

.project-card.archived {
    opacity: 0.7;
    background: rgba(100, 100, 100, 0.05);
}

.project-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.75rem;
}

.project-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #f8fafc;
    margin: 0;
    flex: 1;
}

.project-actions {
    display: flex;
    gap: 0.5rem;
    opacity: 0;
    transition: opacity 0.2s;
}

.project-card:hover .project-actions {
    opacity: 1;
}

.action-btn {
    background: rgba(255, 255, 255, 0.1);
    border: none;
    border-radius: 0.375rem;
    padding: 0.5rem;
    cursor: pointer;
    transition: all 0.2s;
    color: #cbd5e1;
}

.action-btn:hover {
    background: rgba(255, 255, 255, 0.2);
    color: #f8fafc;
}

.action-btn.danger:hover {
    background: rgba(239, 68, 68, 0.2);
    color: #ef4444;
}

.icon {
    width: 1.25rem;
    height: 1.25rem;
}

.project-description {
    color: #94a3b8;
    font-size: 0.875rem;
    line-height: 1.5;
    margin-bottom: 1rem;
}

.status-badges {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-bottom: 1rem;
}

.badge {
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
}

.badge-success {
    background: rgba(34, 197, 94, 0.2);
    color: #22c55e;
}

.badge-warning {
    background: rgba(251, 146, 60, 0.2);
    color: #fb923c;
}

.badge-info {
    background: rgba(59, 130, 246, 0.2);
    color: #3b82f6;
}

.badge-danger {
    background: rgba(239, 68, 68, 0.2);
    color: #ef4444;
}

.badge-archived {
    background: rgba(107, 114, 128, 0.2);
    color: #6b7280;
}

.progress-section {
    margin-bottom: 1rem;
}

.progress-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
    font-size: 0.75rem;
    color: #94a3b8;
}

.progress-bar {
    height: 0.5rem;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 9999px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #3b82f6, #8b5cf6);
    transition: width 0.3s ease;
}

.team-members {
    margin-top: 1rem;
}

.avatars {
    display: flex;
    align-items: center;
}

.avatar {
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.75rem;
    font-weight: 600;
    border: 2px solid #1e293b;
    margin-right: -0.5rem;
}

.avatar.more {
    background: rgba(255, 255, 255, 0.1);
    color: #cbd5e1;
}
</style>
