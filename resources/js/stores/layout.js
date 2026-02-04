/**
 * T013-T015: Layout Pinia Store
 * Manages sidebar state, current route, and mobile responsiveness
 */

import { defineStore } from 'pinia'

export const useLayoutStore = defineStore('layout', {
    state: () => ({
        sidebarCollapsed: false,
        currentRoute: '/',
        isMobile: false
    }),

    getters: {
        /**
         * T015: Determine active navigation item based on current route
         * @param {object} state - Store state
         * @returns {string|null} Active nav item key
         */
        activeNavItem: (state) => {
            const path = state.currentRoute
            if (path === '/dashboard') return 'dashboard'
            if (path.startsWith('/projects')) return 'projects'
            if (path.startsWith('/tasks')) return 'tasks'
            if (path.startsWith('/team')) return 'team'
            if (path.startsWith('/settings')) return 'settings'
            return null
        }
    },

    actions: {
        /**
         * T014: Toggle sidebar collapsed state
         */
        toggleSidebar() {
            this.sidebarCollapsed = !this.sidebarCollapsed
            this.persistSidebarState()
        },

        /**
         * T014: Set sidebar collapsed state explicitly
         * @param {boolean} value - Collapsed state
         */
        setSidebarCollapsed(value) {
            this.sidebarCollapsed = value
            this.persistSidebarState()
        },

        /**
         * T014: Update current route (called from router navigation guard)
         * @param {string} path - Route path
         */
        setCurrentRoute(path) {
            this.currentRoute = path
        },

        /**
         * T014: Initialize layout on app mount
         */
        initializeLayout() {
            // Restore sidebar state from localStorage
            const saved = localStorage.getItem('layout.sidebarCollapsed')
            if (saved !== null) {
                this.sidebarCollapsed = JSON.parse(saved)
            }

            // Set up responsive listener
            this.updateIsMobile()
            window.addEventListener('resize', this.updateIsMobile.bind(this))
        },

        /**
         * T014: Update isMobile state based on window width
         */
        updateIsMobile() {
            this.isMobile = window.innerWidth < 768
            if (this.isMobile) {
                // Auto-collapse sidebar on mobile
                this.sidebarCollapsed = true
            }
        },

        /**
         * T014: Persist sidebar state to localStorage
         */
        persistSidebarState() {
            localStorage.setItem('layout.sidebarCollapsed', JSON.stringify(this.sidebarCollapsed))
        }
    }
})
