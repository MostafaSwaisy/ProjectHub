import { computed } from 'vue';
import { useAuth } from './useAuth';

export function usePermissions() {
    const { user, isAuthenticated } = useAuth();

    /**
     * Check if user has specific role
     * @param {string|string[]} roles
     * @returns {boolean}
     */
    const hasRole = (roles) => {
        if (!isAuthenticated.value) {
            return false;
        }

        const rolesArray = Array.isArray(roles) ? roles : [roles];
        return rolesArray.includes(user.value?.role?.name);
    };

    /**
     * Check if user is an admin
     * @returns {boolean}
     */
    const isAdmin = computed(() => hasRole('admin'));

    /**
     * Check if user is an instructor
     * @returns {boolean}
     */
    const isInstructor = computed(() => hasRole('instructor'));

    /**
     * Check if user is a student
     * @returns {boolean}
     */
    const isStudent = computed(() => hasRole('student'));

    /**
     * Check if user can view project
     * Admins and project members can view
     * @param {Object} project
     * @returns {boolean}
     */
    const canViewProject = (project) => {
        if (!isAuthenticated.value) {
            return false;
        }

        // Admins can view all
        if (isAdmin.value) {
            return true;
        }

        // Instructors can view their own projects
        if (isInstructor.value && project.instructor_id === user.value.id) {
            return true;
        }

        // Check if user is a member
        return project.members?.some(member => member.user_id === user.value.id);
    };

    /**
     * Check if user can edit project
     * Only instructors (owners) and admins can edit
     * @param {Object} project
     * @returns {boolean}
     */
    const canEditProject = (project) => {
        if (!isAuthenticated.value) {
            return false;
        }

        // Admins can edit all
        if (isAdmin.value) {
            return true;
        }

        // Only instructors can edit their own projects
        return isInstructor.value && project.instructor_id === user.value.id;
    };

    /**
     * Check if user can delete project
     * @param {Object} project
     * @returns {boolean}
     */
    const canDeleteProject = (project) => canEditProject(project);

    /**
     * Check if user can view task
     * @param {Object} task
     * @param {Object} project
     * @returns {boolean}
     */
    const canViewTask = (task, project) => {
        if (!isAuthenticated.value) {
            return false;
        }

        // Admins can view all
        if (isAdmin.value) {
            return true;
        }

        // Instructors can view tasks in their projects
        if (isInstructor.value && project.instructor_id === user.value.id) {
            return true;
        }

        // Check if user is a project member
        return canViewProject(project);
    };

    /**
     * Check if user can update task
     * Project members and admins can update
     * @param {Object} task
     * @param {Object} project
     * @returns {boolean}
     */
    const canUpdateTask = (task, project) => {
        if (!isAuthenticated.value) {
            return false;
        }

        // Admins can update all
        if (isAdmin.value) {
            return true;
        }

        // Instructors can update tasks in their projects
        if (isInstructor.value && project.instructor_id === user.value.id) {
            return true;
        }

        // Check if user is a project member
        return canViewProject(project);
    };

    /**
     * Check if user can delete task
     * Only assignee and project admin can delete
     * @param {Object} task
     * @param {Object} project
     * @returns {boolean}
     */
    const canDeleteTask = (task, project) => {
        if (!isAuthenticated.value) {
            return false;
        }

        // Admins can delete all
        if (isAdmin.value) {
            return true;
        }

        // Instructors can delete tasks in their projects
        if (isInstructor.value && project.instructor_id === user.value.id) {
            return true;
        }

        // Assignee can delete the task
        return task.assignee_id === user.value.id;
    };

    /**
     * Check if user can create task in project
     * @param {Object} project
     * @returns {boolean}
     */
    const canCreateTask = (project) => {
        if (!isAuthenticated.value) {
            return false;
        }

        // Admins can create in any project
        if (isAdmin.value) {
            return true;
        }

        // Instructors can create in their projects
        if (isInstructor.value && project.instructor_id === user.value.id) {
            return true;
        }

        // Project members can create tasks
        return canViewProject(project);
    };

    /**
     * Can perform generic action (used for guards)
     * @param {string} action - 'view', 'create', 'edit', 'delete'
     * @param {Object} resource - resource object (project, task, etc.)
     * @param {Object} context - additional context (project for tasks, etc.)
     * @returns {boolean}
     */
    const can = (action, resource, context = {}) => {
        switch (action) {
            case 'view':
                if (resource.type === 'project') return canViewProject(resource);
                if (resource.type === 'task') return canViewTask(resource, context.project);
                break;
            case 'create':
                if (resource.type === 'task') return canCreateTask(context.project);
                break;
            case 'edit':
            case 'update':
                if (resource.type === 'project') return canEditProject(resource);
                if (resource.type === 'task') return canUpdateTask(resource, context.project);
                break;
            case 'delete':
                if (resource.type === 'project') return canDeleteProject(resource);
                if (resource.type === 'task') return canDeleteTask(resource, context.project);
                break;
        }
        return false;
    };

    return {
        // State
        user,
        isAuthenticated,

        // Roles
        hasRole,
        isAdmin,
        isInstructor,
        isStudent,

        // Project permissions
        canViewProject,
        canEditProject,
        canDeleteProject,

        // Task permissions
        canViewTask,
        canUpdateTask,
        canDeleteTask,
        canCreateTask,

        // Generic permission check
        can,
    };
}
