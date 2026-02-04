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
        async fetchProjects(page = 1) {
            this.loading = true;
            this.error = null;

            try {
                const params = {
                    page,
                    per_page: 20,
                    archived: this.filters.archived,
                    sort: this.filters.sort,
                    order: this.filters.order,
                };

                // Add optional filters
                if (this.filters.search) {
                    params.search = this.filters.search;
                }
                if (this.filters.status) {
                    params.status = this.filters.status;
                }
                if (this.filters.role && this.filters.role !== 'all') {
                    params.role = this.filters.role;
                }

                const response = await axios.get('/api/projects', { params });

                this.projects = response.data.data;
                this.pagination = response.data.meta;
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to load projects';
                console.error('Error fetching projects:', error);
            } finally {
                this.loading = false;
            }
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
