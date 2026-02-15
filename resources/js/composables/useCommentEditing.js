import { computed } from 'vue';

/**
 * Composable for managing comment edit window logic
 * Comments can only be edited within 15 minutes of creation
 */
export function useCommentEditing() {
    const EDIT_WINDOW_MINUTES = 15;

    /**
     * Check if a comment is still within the edit window
     * @param {Object} comment - The comment object with created_at timestamp
     * @returns {boolean} - True if comment can still be edited
     */
    const isWithinEditWindow = (comment) => {
        if (!comment?.created_at) return false;

        const createdAt = new Date(comment.created_at);
        const now = new Date();
        const diffMs = now - createdAt;
        const diffMinutes = diffMs / (1000 * 60);

        return diffMinutes < EDIT_WINDOW_MINUTES;
    };

    /**
     * Check if the current user can edit a comment
     * Must be within edit window AND be the author
     * @param {Object} comment - The comment object
     * @param {number} currentUserId - The current user's ID
     * @returns {boolean} - True if user can edit this comment
     */
    const canEdit = (comment, currentUserId) => {
        if (!comment || !currentUserId) return false;

        // Must be the author
        const isAuthor = comment.user?.id === currentUserId || comment.user_id === currentUserId;
        if (!isAuthor) return false;

        // Must be within edit window
        return isWithinEditWindow(comment);
    };

    /**
     * Check if the current user can delete a comment
     * Only the author can delete their own comments
     * @param {Object} comment - The comment object
     * @param {number} currentUserId - The current user's ID
     * @returns {boolean} - True if user can delete this comment
     */
    const canDelete = (comment, currentUserId) => {
        if (!comment || !currentUserId) return false;
        return comment.user?.id === currentUserId || comment.user_id === currentUserId;
    };

    /**
     * Get the remaining time in the edit window
     * @param {Object} comment - The comment object
     * @returns {Object} - { minutes, seconds, expired }
     */
    const timeUntilLocked = (comment) => {
        if (!comment?.created_at) {
            return { minutes: 0, seconds: 0, expired: true };
        }

        const createdAt = new Date(comment.created_at);
        const lockTime = new Date(createdAt.getTime() + EDIT_WINDOW_MINUTES * 60 * 1000);
        const now = new Date();

        if (now >= lockTime) {
            return { minutes: 0, seconds: 0, expired: true };
        }

        const remainingMs = lockTime - now;
        const minutes = Math.floor(remainingMs / (1000 * 60));
        const seconds = Math.floor((remainingMs % (1000 * 60)) / 1000);

        return { minutes, seconds, expired: false };
    };

    /**
     * Format the remaining time as a human-readable string
     * @param {Object} comment - The comment object
     * @returns {string} - Formatted time string (e.g., "12:45 remaining")
     */
    const formatTimeRemaining = (comment) => {
        const { minutes, seconds, expired } = timeUntilLocked(comment);

        if (expired) {
            return 'Edit window expired';
        }

        if (minutes === 0) {
            return `${seconds}s remaining`;
        }

        return `${minutes}m ${seconds}s remaining`;
    };

    /**
     * Check if a comment has been edited
     * @param {Object} comment - The comment object
     * @returns {boolean} - True if comment was edited
     */
    const wasEdited = (comment) => {
        return comment?.edited_at !== null && comment?.edited_at !== undefined;
    };

    return {
        EDIT_WINDOW_MINUTES,
        isWithinEditWindow,
        canEdit,
        canDelete,
        timeUntilLocked,
        formatTimeRemaining,
        wasEdited,
    };
}
