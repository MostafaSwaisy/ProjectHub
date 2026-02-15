<template>
    <div class="comment-item" :class="{ 'is-reply': isReply }">
        <!-- Avatar -->
        <div class="avatar">
            {{ getInitials(comment.user?.name) }}
        </div>

        <!-- Content -->
        <div class="comment-content">
            <!-- Header -->
            <div class="comment-header">
                <span class="author-name">{{ comment.user?.name || 'Unknown' }}</span>
                <span class="timestamp">{{ formatTimestamp(comment.created_at) }}</span>
                <span v-if="wasEdited" class="edited-badge">(edited)</span>
            </div>

            <!-- Body -->
            <div v-if="!isEditing" class="comment-body">
                <p>{{ comment.body }}</p>
            </div>

            <!-- Edit Form -->
            <div v-else class="edit-form">
                <textarea
                    v-model="editText"
                    class="edit-input"
                    rows="3"
                    ref="editInput"
                ></textarea>
                <div class="edit-actions">
                    <button class="btn btn-secondary btn-sm" @click="cancelEdit">Cancel</button>
                    <button
                        class="btn btn-primary btn-sm"
                        :disabled="!editText.trim()"
                        @click="saveEdit"
                    >
                        Save
                    </button>
                </div>
            </div>

            <!-- Actions -->
            <div v-if="!isEditing" class="comment-actions">
                <button v-if="!isReply" class="action-btn" @click="$emit('reply', comment)">
                    Reply
                </button>
                <template v-if="isAuthor">
                    <button
                        v-if="canEditComment"
                        class="action-btn"
                        @click="startEdit"
                    >
                        Edit
                        <span class="time-remaining">({{ timeRemaining }})</span>
                    </button>
                    <button class="action-btn action-btn-danger" @click="$emit('delete', comment.id)">
                        Delete
                    </button>
                </template>
            </div>

            <!-- Replies -->
            <div v-if="replies.length > 0" class="replies">
                <CommentItem
                    v-for="reply in replies"
                    :key="reply.id"
                    :comment="reply"
                    :current-user-id="currentUserId"
                    :is-reply="true"
                    @edit="$emit('edit', $event)"
                    @delete="$emit('delete', $event)"
                />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, nextTick, onMounted, onUnmounted } from 'vue';
import { useCommentsStore } from '../../stores/comments';
import { useCommentEditing } from '../../composables/useCommentEditing';

const props = defineProps({
    comment: {
        type: Object,
        required: true,
    },
    currentUserId: {
        type: [Number, String],
        default: null,
    },
    isReply: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['reply', 'edit', 'delete']);

const commentsStore = useCommentsStore();
const { canEdit, canDelete, formatTimeRemaining, wasEdited: checkWasEdited } = useCommentEditing();

// Local state
const isEditing = ref(false);
const editText = ref('');
const editInput = ref(null);
const timeRemaining = ref('');
let timerInterval = null;

// Computed
const isAuthor = computed(() => {
    return props.currentUserId && (
        props.comment.user?.id === props.currentUserId ||
        props.comment.user_id === props.currentUserId
    );
});

const canEditComment = computed(() => {
    return canEdit(props.comment, props.currentUserId);
});

const wasEdited = computed(() => {
    return checkWasEdited(props.comment);
});

const replies = computed(() => {
    return commentsStore.getReplies(props.comment.id);
});

// Methods
const getInitials = (name) => {
    if (!name) return '?';
    return name
        .split(' ')
        .map(n => n[0])
        .join('')
        .toUpperCase()
        .substring(0, 2);
};

const formatTimestamp = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / (1000 * 60));
    const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
    const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));

    if (diffMins < 1) return 'just now';
    if (diffMins < 60) return `${diffMins}m ago`;
    if (diffHours < 24) return `${diffHours}h ago`;
    if (diffDays < 7) return `${diffDays}d ago`;

    return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
    });
};

const updateTimeRemaining = () => {
    timeRemaining.value = formatTimeRemaining(props.comment);
};

const startEdit = () => {
    editText.value = props.comment.body;
    isEditing.value = true;
    nextTick(() => {
        editInput.value?.focus();
    });
};

const cancelEdit = () => {
    isEditing.value = false;
    editText.value = '';
};

const saveEdit = () => {
    const body = editText.value.trim();
    if (!body) return;

    emit('edit', { commentId: props.comment.id, body });
    isEditing.value = false;
};

// Lifecycle
onMounted(() => {
    if (isAuthor.value && canEditComment.value) {
        updateTimeRemaining();
        timerInterval = setInterval(updateTimeRemaining, 1000);
    }
});

onUnmounted(() => {
    if (timerInterval) {
        clearInterval(timerInterval);
    }
});
</script>

<style scoped>
.comment-item {
    display: flex;
    gap: var(--spacing-sm);
}

.comment-item.is-reply {
    margin-left: var(--spacing-xl);
    padding-left: var(--spacing-md);
    border-left: 2px solid rgba(255, 255, 255, 0.1);
}

/* Avatar */
.avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: var(--orange-primary);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: var(--font-bold);
    flex-shrink: 0;
}

.is-reply .avatar {
    width: 28px;
    height: 28px;
    font-size: 10px;
}

/* Content */
.comment-content {
    flex: 1;
    min-width: 0;
}

/* Header */
.comment-header {
    display: flex;
    align-items: baseline;
    gap: var(--spacing-sm);
    flex-wrap: wrap;
    margin-bottom: var(--spacing-xs);
}

.author-name {
    font-size: var(--text-sm);
    font-weight: var(--font-semibold);
    color: var(--text-primary);
}

.timestamp {
    font-size: var(--text-xs);
    color: var(--text-secondary);
}

.edited-badge {
    font-size: var(--text-xs);
    color: var(--text-secondary);
    font-style: italic;
}

/* Body */
.comment-body p {
    margin: 0;
    font-size: var(--text-sm);
    color: var(--text-primary);
    line-height: var(--line-height-relaxed);
    white-space: pre-wrap;
    word-break: break-word;
}

/* Edit Form */
.edit-form {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-sm);
}

.edit-input {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: var(--radius-md);
    padding: var(--spacing-sm);
    color: var(--text-primary);
    font-family: inherit;
    font-size: var(--text-sm);
    resize: vertical;
}

.edit-input:focus {
    outline: none;
    border-color: var(--orange-primary);
}

.edit-actions {
    display: flex;
    gap: var(--spacing-sm);
    justify-content: flex-end;
}

/* Actions */
.comment-actions {
    display: flex;
    gap: var(--spacing-md);
    margin-top: var(--spacing-xs);
}

.action-btn {
    background: none;
    border: none;
    color: var(--text-secondary);
    font-size: var(--text-xs);
    cursor: pointer;
    padding: 0;
    transition: color var(--transition-normal);
}

.action-btn:hover {
    color: var(--orange-primary);
}

.action-btn-danger:hover {
    color: var(--red-primary);
}

.time-remaining {
    color: var(--text-secondary);
    opacity: 0.7;
}

/* Buttons */
.btn {
    padding: var(--spacing-xs) var(--spacing-sm);
    border: none;
    border-radius: var(--radius-sm);
    font-weight: var(--font-medium);
    font-size: var(--text-xs);
    cursor: pointer;
    transition: all var(--transition-normal);
}

.btn-sm {
    padding: 4px 8px;
}

.btn-primary {
    background: var(--orange-primary);
    color: white;
}

.btn-primary:hover:not(:disabled) {
    background: var(--orange-light);
}

.btn-primary:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.btn-secondary {
    background: rgba(255, 255, 255, 0.1);
    color: var(--text-primary);
}

.btn-secondary:hover {
    background: rgba(255, 255, 255, 0.15);
}

/* Replies */
.replies {
    margin-top: var(--spacing-md);
    display: flex;
    flex-direction: column;
    gap: var(--spacing-md);
}
</style>
