import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

/**
 * Comments Pinia Store
 * Manages comment CRUD operations for tasks
 */
export const useCommentsStore = defineStore('comments', () => {
    // State
    const comments = ref([]);
    const loading = ref(false);
    const error = ref(null);

    // Computed
    const commentCount = computed(() => comments.value.length);

    const rootComments = computed(() =>
        comments.value.filter(c => !c.parent_id)
    );

    const getCommentById = computed(() => (id) => {
        return comments.value.find(c => c.id === id);
    });

    const getReplies = computed(() => (parentId) => {
        return comments.value.filter(c => c.parent_id === parentId);
    });

    // Actions: Fetch comments for a task
    const fetchComments = async (taskId) => {
        loading.value = true;
        error.value = null;
        try {
            const response = await axios.get(`/api/tasks/${taskId}/comments`);
            comments.value = response.data.data || response.data;
            return comments.value;
        } catch (err) {
            error.value = err.response?.data?.message || err.message;
            console.error('Failed to fetch comments:', err);
            throw err;
        } finally {
            loading.value = false;
        }
    };

    // Actions: Add comment (with optimistic update)
    const addComment = async (taskId, commentData) => {
        const tempId = `temp-${Date.now()}`;
        const tempComment = {
            id: tempId,
            task_id: taskId,
            body: commentData.body,
            parent_id: commentData.parent_id || null,
            user: commentData.user,
            created_at: new Date().toISOString(),
            edited_at: null,
            is_editable: true,
            _pending: true,
        };

        // Optimistic add
        comments.value.push(tempComment);

        try {
            const response = await axios.post(`/api/tasks/${taskId}/comments`, {
                body: commentData.body,
                parent_id: commentData.parent_id || null,
            });
            const newComment = response.data.data || response.data;

            // Replace temp comment with real one
            const index = comments.value.findIndex(c => c.id === tempId);
            if (index !== -1) {
                comments.value[index] = newComment;
            }
            return newComment;
        } catch (err) {
            // Rollback on failure
            comments.value = comments.value.filter(c => c.id !== tempId);
            error.value = err.response?.data?.message || err.message;
            console.error('Failed to add comment:', err);
            throw err;
        }
    };

    // Actions: Update comment (15-minute window enforced server-side)
    const updateComment = async (commentId, body) => {
        const comment = getCommentById.value(commentId);
        if (!comment) throw new Error('Comment not found');

        const originalBody = comment.body;

        // Optimistic update
        comment.body = body;
        comment.edited_at = new Date().toISOString();

        try {
            const response = await axios.patch(`/api/comments/${commentId}`, { body });
            const updatedComment = response.data.data || response.data;

            const index = comments.value.findIndex(c => c.id === commentId);
            if (index !== -1) {
                comments.value[index] = updatedComment;
            }
            return updatedComment;
        } catch (err) {
            // Rollback on failure
            comment.body = originalBody;
            comment.edited_at = null;
            error.value = err.response?.data?.message || err.message;
            console.error('Failed to update comment:', err);
            throw err;
        }
    };

    // Actions: Delete comment
    const deleteComment = async (commentId) => {
        const comment = getCommentById.value(commentId);
        if (!comment) throw new Error('Comment not found');

        const commentIndex = comments.value.findIndex(c => c.id === commentId);
        const removedComment = comments.value[commentIndex];

        // Optimistic delete (including replies)
        const idsToRemove = [commentId, ...getReplies.value(commentId).map(r => r.id)];
        comments.value = comments.value.filter(c => !idsToRemove.includes(c.id));

        try {
            await axios.delete(`/api/comments/${commentId}`);
            return true;
        } catch (err) {
            // Rollback on failure
            comments.value.splice(commentIndex, 0, removedComment);
            error.value = err.response?.data?.message || err.message;
            console.error('Failed to delete comment:', err);
            throw err;
        }
    };

    // Actions: Clear comments (when switching tasks)
    const clearComments = () => {
        comments.value = [];
        error.value = null;
    };

    // Actions: Reset store
    const resetComments = () => {
        comments.value = [];
        loading.value = false;
        error.value = null;
    };

    return {
        // State
        comments,
        loading,
        error,
        // Computed
        commentCount,
        rootComments,
        getCommentById,
        getReplies,
        // Actions
        fetchComments,
        addComment,
        updateComment,
        deleteComment,
        clearComments,
        resetComments,
    };
});
