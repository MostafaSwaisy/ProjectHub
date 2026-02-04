import { defineStore } from 'pinia';
import axios from 'axios';

export const useDashboardStore = defineStore('dashboard', {
  state: () => ({
    stats: {
      total_projects: 0,
      active_tasks: 0,
      team_members: 0,
      overdue_tasks: 0
    },
    loading: false,
    error: null
  }),

  actions: {
    /**
     * Fetch dashboard statistics from API
     * Includes automatic retry on failure
     */
    async fetchStats() {
      this.loading = true;
      this.error = null;

      try {
        const response = await axios.get('/api/dashboard/stats');
        this.stats = response.data.data.stats;
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to load dashboard statistics';
        console.error('Dashboard stats fetch error:', err);
      } finally {
        this.loading = false;
      }
    },

    /**
     * Retry fetching statistics after error
     */
    async retry() {
      await this.fetchStats();
    },

    /**
     * Reset store to initial state
     */
    reset() {
      this.stats = {
        total_projects: 0,
        active_tasks: 0,
        team_members: 0,
        overdue_tasks: 0
      };
      this.loading = false;
      this.error = null;
    }
  },

  getters: {
    /**
     * Check if user has any projects
     */
    hasProjects: (state) => state.stats.total_projects > 0,

    /**
     * Check if user has overdue tasks requiring attention
     */
    hasOverdueTasks: (state) => state.stats.overdue_tasks > 0
  }
});
