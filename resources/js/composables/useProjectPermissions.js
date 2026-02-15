import { computed } from 'vue';
import { useAuth } from './useAuth';

/**
 * Composable for checking project-level permissions
 * @param {Ref<Object>} project - Reactive project object
 * @returns {Object} Permission check methods
 */
export function useProjectPermissions(project) {
    const { user } = useAuth();

    /**
     * Check if current user is the project owner/instructor
     */
    const isOwner = computed(() => {
        if (!project.value || !user.value) return false;
        return project.value.instructor_id === user.value.id;
    });

    /**
     * Check if current user is a member of the project
     */
    const isMember = computed(() => {
        if (!project.value || !user.value) return false;
        const members = project.value.members || [];
        return members.some(m => m.user_id === user.value.id || m.id === user.value.id);
    });

    /**
     * Get current user's role in the project
     * @returns {'instructor' | 'editor' | 'viewer' | null}
     */
    const userRole = computed(() => {
        if (!project.value || !user.value) return null;
        if (isOwner.value) return 'instructor';

        const members = project.value.members || [];
        const membership = members.find(m =>
            m.user_id === user.value.id || m.id === user.value.id
        );
        return membership?.role || null;
    });

    /**
     * Check if user can edit project details
     * Instructors and editors can edit
     */
    const canEdit = computed(() => {
        if (!project.value || !user.value) return false;
        if (isOwner.value) return true;
        return userRole.value === 'editor';
    });

    /**
     * Check if user can delete the project
     * Only the owner/instructor can delete
     */
    const canDelete = computed(() => isOwner.value);

    /**
     * Check if user can archive/unarchive the project
     * Only the owner/instructor can archive
     */
    const canArchive = computed(() => isOwner.value);

    /**
     * Check if user can manage team members
     * Only the owner/instructor can manage members
     */
    const canManageMembers = computed(() => isOwner.value);

    /**
     * Check if user can view the project
     * Owners, members, and admins can view
     */
    const canView = computed(() => {
        if (!project.value || !user.value) return false;
        if (isOwner.value || isMember.value) return true;
        // Admin check
        return user.value.role?.name === 'admin';
    });

    return {
        isOwner,
        isMember,
        userRole,
        canEdit,
        canDelete,
        canArchive,
        canManageMembers,
        canView,
    };
}
