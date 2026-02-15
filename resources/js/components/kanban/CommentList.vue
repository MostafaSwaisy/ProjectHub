<template>
    <div class="comment-list">
        <!-- Add Comment Form -->
        <div class="add-comment">
            <div class="comment-input-wrapper">
                <textarea
                    v-model="newComment"
                    placeholder="Write a comment..."
                    class="comment-input"
                    rows="2"
                    @keydown.enter.ctrl="addComment"
                ></textarea>
                <button
                    class="submit-btn"
                    :disabled="!newComment.trim() || submitting"
                    @click="addComment"
                >
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="22" y1="2" x2="11" y2="13"></line>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                    </svg>
                </button>
            </div>
            <p class="hint-text">Press Ctrl+Enter to send</p>
        </div>

        <!-- Comments List -->
        <div v-if="loading" class="loading-state">
            <div class="spinner"></div>
            <span>Loading comments...</span>
        </div>

        <div v-else-if="comments.length === 0" class="empty-state">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            </svg>
            <p>No comments yet</p>
        </div>

        <div v-else class="comments-container">
            <CommentItem
                v-for="comment in sortedComments"
                :key="comment.id"
                :comment="comment"
                :current-user-id="currentUserId"
                @reply="startReply"
                @edit="editComment"
                @delete="deleteComment"
            />
        </div>

        <!-- Reply Modal -->
        <div v-if="replyingTo" class="reply-modal" @click.self="cancelReply">
            <div class="reply-content">
                <div class="reply-header">
                    <span>Replying to {{ replyingTo.user?.name }}</span>
                    <button class="close-btn" @click="cancelReply">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>
                <div class="reply-original">
                    <p>{{ replyingTo.body }}</p>
                </div>
                <textarea
                    v-model="replyText"
                    placeholder="Write a reply..."
                    class="comment-input"
                    rows="3"
                    ref="replyInput"
                ></textarea>
                <div class="reply-actions">
                    <button class="btn btn-secondary" @click="cancelReply">Cancel</button>
                    <button
                        class="btn btn-primary"
                        :disabled="!replyText.trim() || submitting"
                        @click="submitReply"
                    >
                        Reply
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import { useCommentsStore } from '../../stores/comments';
import { useAuthStore } from '../../stores/auth';
import CommentItem from './CommentItem.vue';

const props = defineProps({
    taskId: {
        type: [Number, String],
        required: true,
    },
});

const emit = defineEmits(['updated']);

const commentsStore = useCommentsStore();
const authStore = useAuthStore();

// Local state
const newComment = ref('');
const replyingTo = ref(null);
const replyText = ref('');
const replyInput = ref(null);
const submitting = ref(false);

// Computed
const loading = computed(() => commentsStore.loading);
const comments = computed(() => commentsStore.comments);
const sortedComments = computed(() => {
    return [...comments.value]
        .filter(c => !c.parent_id)
        .sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
});
const currentUserId = computed(() => authStore.user?.id);

// Lifecycle
onMounted(() => {
    if (props.taskId) {
        commentsStore.fetchComments(props.taskId);
    }
});

// Watch for task changes
watch(() => props.taskId, (newTaskId) => {
    if (newTaskId) {
        commentsStore.clearComments();
        commentsStore.fetchComments(newTaskId);
    }
});

// Methods
const addComment = async () => {
    const body = newComment.value.trim();
    if (!body || submitting.value) return;

    submitting.value = true;
    try {
        await commentsStore.addComment(props.taskId, {
            body,
            user: authStore.user,
        });
        newComment.value = '';
        emit('updated');
    } catch (error) {
        console.error('Failed to add comment:', error);
    } finally {
        submitting.value = false;
    }
};

const startReply = (comment) => {
    replyingTo.value = comment;
    replyText.value = '';
    nextTick(() => {
        replyInput.value?.focus();
    });
};

const cancelReply = () => {
    replyingTo.value = null;
    replyText.value = '';
};

const submitReply = async () => {
    const body = replyText.value.trim();
    if (!body || !replyingTo.value || submitting.value) return;

    submitting.value = true;
    try {
        await commentsStore.addComment(props.taskId, {
            body,
            parent_id: replyingTo.value.id,
            user: authStore.user,
        });
        cancelReply();
        emit('updated');
    } catch (error) {
        console.error('Failed to add reply:', error);
    } finally {
        submitting.value = false;
    }
};

const editComment = async ({ commentId, body }) => {
    try {
        await commentsStore.updateComment(commentId, body);
        emit('updated');
    } catch (error) {
        console.error('Failed to edit comment:', error);
    }
};

const deleteComment = async (commentId) => {
    if (!confirm('Are you sure you want to delete this comment?')) return;

    try {
        await commentsStore.deleteComment(commentId);
        emit('updated');
    } catch (error) {
        console.error('Failed to delete comment:', error);
    }
};
</script>

<style scoped>
.comment-list {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-lg);
}

/* Add Comment */
.add-comment {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-xs);
}

.comment-input-wrapper {
    display: flex;
    gap: var(--spacing-sm);
    align-items: flex-end;
}

.comment-input {
    flex: 1;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--radius-md);
    padding: var(--spacing-sm) var(--spacing-md);
    color: var(--text-primary);
    font-family: inherit;
    font-size: var(--text-sm);
    resize: vertical;
    min-height: 60px;
    transition: all var(--transition-normal);
}

.comment-input:focus {
    outline: none;
    border-color: rgba(255, 107, 53, 0.5);
    background: rgba(255, 255, 255, 0.08);
}

.comment-input::placeholder {
    color: var(--text-secondary);
}

.submit-btn {
    background: var(--orange-primary);
    border: none;
    border-radius: var(--radius-md);
    padding: var(--spacing-sm);
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    height: 40px;
    width: 40px;
    transition: all var(--transition-normal);
}

.submit-btn:hover:not(:disabled) {
    background: var(--orange-light);
}

.submit-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.hint-text {
    font-size: var(--text-xs);
    color: var(--text-secondary);
    margin: 0;
}

/* Loading & Empty States */
.loading-state,
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: var(--spacing-sm);
    padding: var(--spacing-xl);
    color: var(--text-secondary);
}

.spinner {
    width: 24px;
    height: 24px;
    border: 2px solid rgba(255, 255, 255, 0.1);
    border-top-color: var(--orange-primary);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.empty-state p {
    font-size: var(--text-sm);
    margin: 0;
}

/* Comments Container */
.comments-container {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-md);
}

/* Reply Modal */
.reply-modal {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1100;
}

.reply-content {
    background: rgba(10, 10, 10, 0.95);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--radius-lg);
    padding: var(--spacing-xl);
    max-width: 500px;
    width: 90vw;
    display: flex;
    flex-direction: column;
    gap: var(--spacing-md);
}

.reply-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: var(--text-sm);
    color: var(--text-secondary);
}

.close-btn {
    background: none;
    border: none;
    color: var(--text-secondary);
    cursor: pointer;
    padding: 0;
    transition: color var(--transition-normal);
}

.close-btn:hover {
    color: var(--text-primary);
}

.reply-original {
    background: rgba(255, 255, 255, 0.05);
    border-left: 3px solid var(--orange-primary);
    padding: var(--spacing-sm) var(--spacing-md);
    border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
}

.reply-original p {
    margin: 0;
    font-size: var(--text-sm);
    color: var(--text-secondary);
}

.reply-actions {
    display: flex;
    gap: var(--spacing-sm);
    justify-content: flex-end;
}

.btn {
    padding: var(--spacing-sm) var(--spacing-md);
    border: none;
    border-radius: var(--radius-md);
    font-weight: var(--font-medium);
    font-size: var(--text-sm);
    cursor: pointer;
    transition: all var(--transition-normal);
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
</style>
