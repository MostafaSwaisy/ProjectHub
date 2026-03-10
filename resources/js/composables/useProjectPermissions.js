import { computed } from 'vue';
import { useAuth } from './useAuth';

/**
 * Permission matrix matching backend config/permissions.php
 */
const PERMISSION_MATRIX = {
    owner: {
        label: 'Owner',
        permissions: {
            'project.view': true,
            'project.edit': true,
            'project.delete': true,
            'member.view': true,
            'member.add': true,
            'member.remove': true,
            'member.invite': true,
            'member.role_change': true,
            'role.view': true,
            'role.manage': true,
            'task.view': true,
            'task.create': true,
            'task.edit': true,
            'task.delete': true,
            'task.assign': true,
            'task.comment': true,
            'label.manage': true,
        },
    },
    lead: {
        label: 'Lead',
        permissions: {
            'project.view': true,
            'project.edit': false,
            'project.delete': false,
            'member.view': true,
            'member.add': false,
            'member.remove': false,
            'member.invite': true,
            'member.role_change': false,
            'role.view': true,
            'role.manage': false,
            'task.view': true,
            'task.create': true,
            'task.edit': true,
            'task.delete': true,
            'task.assign': true,
            'task.comment': true,
            'label.manage': true,
        },
    },
    member: {
        label: 'Member',
        permissions: {
            'project.view': true,
            'project.edit': false,
            'project.delete': false,
            'member.view': true,
            'member.add': false,
            'member.remove': false,
            'member.invite': false,
            'member.role_change': false,
            'role.view': true,
            'role.manage': false,
            'task.view': true,
            'task.create': true,
            'task.edit': true,
            'task.delete': true,
            'task.assign': true,
            'task.comment': true,
            'label.manage': false,
        },
    },
    viewer: {
        label: 'Viewer',
        permissions: {
            'project.view': true,
            'project.edit': false,
            'project.delete': false,
            'member.view': true,
            'member.add': false,
            'member.remove': false,
            'member.invite': false,
            'member.role_change': false,
            'role.view': true,
            'role.manage': false,
            'task.view': true,
            'task.create': false,
            'task.edit': false,
            'task.delete': false,
            'task.assign': false,
            'task.comment': false,
            'label.manage': false,
        },
    },
};

/**
 * Composable for checking project-level permissions using permission matrix
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
     * @returns {'owner' | 'lead' | 'member' | 'viewer' | null}
     */
    const userRole = computed(() => {
        if (!project.value || !user.value) return null;
        if (isOwner.value) return 'owner';

        const members = project.value.members || [];
        const membership = members.find(m =>
            m.user_id === user.value.id || m.id === user.value.id
        );
        return membership?.role || null;
    });

    /**
     * Check if user has a specific permission
     */
    const hasPermission = (permission) => {
        const role = userRole.value;
        if (!role) return false;
        return PERMISSION_MATRIX[role]?.permissions[permission] ?? false;
    };

    /**
     * Permission check methods
     */
    const canInvite = computed(() => hasPermission('member.invite'));
    const canManageRoles = computed(() => hasPermission('member.role_change'));
    const canAssignTasks = computed(() => hasPermission('task.assign'));
    const canEditProject = computed(() => hasPermission('project.edit'));
    const canDeleteProject = computed(() => hasPermission('project.delete'));
    const canCreateTask = computed(() => hasPermission('task.create'));
    const canEditTask = computed(() => hasPermission('task.edit'));
    const canDeleteTask = computed(() => hasPermission('task.delete'));
    const canManageMembers = computed(() => isOwner.value);
    const canView = computed(() => {
        if (!project.value || !user.value) return false;
        if (isOwner.value || isMember.value) return true;
        return user.value.role?.name === 'admin';
    });

    return {
        isOwner,
        isMember,
        userRole,
        hasPermission,
        canInvite,
        canManageRoles,
        canAssignTasks,
        canEditProject,
        canDeleteProject,
        canCreateTask,
        canEditTask,
        canDeleteTask,
        canManageMembers,
        canView,
    };
}
