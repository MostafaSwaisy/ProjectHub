import { defineStore } from 'pinia';
import axios from 'axios';

export const useProjectsStore = defineStore('projects', {
    state: () => ({
        projects: [],
        currentProject: null,
        loading: false,
        error: null,
        pagination: null,
        filters: {
            search: '',
            status: '',
            role: 'all',
            archived: false,
            sort: 'updated_at',
            order: 'desc',
        },
        viewMode: 'grid', // 'grid' or 'list'
    }),

    getters: {
        hasProjects: (state) => state.projects.length > 0,
        activeProjects: (state) => state.projects.filter(p => !p.is_archived),
        archivedProjects: (state) => state.projects.filter(p => p.is_archived),
        totalCount: (state) => state.pagination?.total || 0,
    },

    actions: {
        // Placeholder actions - will be implemented in later tasks
        async fetchProjects() {
            // T017: Implement fetchProjects
        },

        async createProject(data) {
            // T024: Implement createProject
        },

        async updateProject(id, data) {
            // T034: Implement updateProject
        },

        async deleteProject(id) {
            // T043: Implement deleteProject
        },

        async archiveProject(id) {
            // T051: Implement archiveProject
        },

        async unarchiveProject(id) {
            // T051: Implement unarchiveProject
        },

        async duplicateProject(id, data) {
            // T087: Implement duplicateProject
        },

        setFilters(newFilters) {
            this.filters = { ...this.filters, ...newFilters };
        },

        setViewMode(mode) {
            this.viewMode = mode;
            localStorage.setItem('projectsViewMode', mode);
        },

        clearError() {
            this.error = null;
        },
    },
});
