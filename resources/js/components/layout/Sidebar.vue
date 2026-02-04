<template>
    <!-- T017-T018: Sidebar Navigation Component -->
    <aside
        class="sidebar"
        :class="{
            'sidebar-collapsed': layoutStore.sidebarCollapsed,
            'sidebar-mobile': layoutStore.isMobile
        }"
    >
        <!-- Logo Section -->
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <svg
                    class="logo-icon"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                    />
                </svg>
                <span v-if="!layoutStore.sidebarCollapsed" class="logo-text">ProjectHub</span>
            </div>
        </div>

        <!-- Navigation Links -->
        <nav class="sidebar-nav">
            <router-link
                v-for="item in navItems"
                :key="item.key"
                :to="item.path"
                class="nav-item"
                :class="{ active: layoutStore.activeNavItem === item.key }"
                @click="handleNavClick"
            >
                <component :is="item.icon" class="nav-icon" />
                <span v-if="!layoutStore.sidebarCollapsed" class="nav-label">
                    {{ item.label }}
                </span>
                <span v-if="item.badge && !layoutStore.sidebarCollapsed" class="nav-badge">
                    {{ item.badge }}
                </span>
            </router-link>
        </nav>

        <!-- Collapse Toggle Button (Desktop) -->
        <button
            v-if="!layoutStore.isMobile"
            class="sidebar-toggle"
            @click="layoutStore.toggleSidebar()"
            :title="layoutStore.sidebarCollapsed ? 'Expand Sidebar' : 'Collapse Sidebar'"
        >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="toggle-icon">
                <path
                    v-if="layoutStore.sidebarCollapsed"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M9 5l7 7-7 7"
                />
                <path
                    v-else
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M15 19l-7-7 7-7"
                />
            </svg>
        </button>
    </aside>
</template>

<script setup>
import { computed, h } from 'vue'
import { useLayoutStore } from '../../stores/layout'
import { useRouter } from 'vue-router'

const layoutStore = useLayoutStore()
const router = useRouter()

// Navigation items
const navItems = computed(() => [
    {
        key: 'dashboard',
        label: 'Dashboard',
        path: '/dashboard',
        icon: () =>
            h('svg', { viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor' }, [
                h('path', {
                    'stroke-linecap': 'round',
                    'stroke-linejoin': 'round',
                    'stroke-width': '2',
                    d: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'
                })
            ])
    },
    {
        key: 'projects',
        label: 'Projects',
        path: '/projects',
        icon: () =>
            h('svg', { viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor' }, [
                h('path', {
                    'stroke-linecap': 'round',
                    'stroke-linejoin': 'round',
                    'stroke-width': '2',
                    d: 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z'
                })
            ])
    },
    {
        key: 'tasks',
        label: 'My Tasks',
        path: '/tasks',
        icon: () =>
            h('svg', { viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor' }, [
                h('path', {
                    'stroke-linecap': 'round',
                    'stroke-linejoin': 'round',
                    'stroke-width': '2',
                    d: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'
                })
            ])
    },
    {
        key: 'team',
        label: 'Team',
        path: '/team',
        icon: () =>
            h('svg', { viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor' }, [
                h('path', {
                    'stroke-linecap': 'round',
                    'stroke-linejoin': 'round',
                    'stroke-width': '2',
                    d: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'
                })
            ])
    },
    {
        key: 'settings',
        label: 'Settings',
        path: '/settings',
        icon: () =>
            h('svg', { viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor' }, [
                h('path', {
                    'stroke-linecap': 'round',
                    'stroke-linejoin': 'round',
                    'stroke-width': '2',
                    d: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'
                }),
                h('path', {
                    'stroke-linecap': 'round',
                    'stroke-linejoin': 'round',
                    'stroke-width': '2',
                    d: 'M15 12a3 3 0 11-6 0 3 3 0 016 0z'
                })
            ])
    }
])

// T018: Auto-close sidebar on mobile after navigation
const handleNavClick = () => {
    if (layoutStore.isMobile) {
        layoutStore.setSidebarCollapsed(true)
    }
}
</script>

<style scoped>
.sidebar {
    position: fixed;
    left: 0;
    top: 0;
    bottom: 0;
    width: 16rem;
    background: rgba(15, 23, 42, 0.8);
    backdrop-filter: blur(16px);
    border-right: 1px solid rgba(148, 163, 184, 0.1);
    display: flex;
    flex-direction: column;
    z-index: 50;
    transition: width 300ms cubic-bezier(0.4, 0, 0.2, 1),
        transform 300ms cubic-bezier(0.4, 0, 0.2, 1);
}

.sidebar-collapsed {
    width: 4rem;
}

.sidebar-mobile {
    transform: translateX(0);
}

.sidebar-mobile.sidebar-collapsed {
    transform: translateX(-100%);
}

/* Logo Section */
.sidebar-header {
    padding: 1.5rem 1rem;
    border-bottom: 1px solid rgba(148, 163, 184, 0.1);
}

.sidebar-logo {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    color: #f97316;
    font-size: 1.25rem;
    font-weight: 700;
}

.logo-icon {
    width: 2rem;
    height: 2rem;
    flex-shrink: 0;
}

.logo-text {
    white-space: nowrap;
    overflow: hidden;
}

.sidebar-collapsed .logo-text {
    display: none;
}

/* Navigation */
.sidebar-nav {
    flex: 1;
    padding: 1rem 0;
    overflow-y: auto;
}

.nav-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    margin: 0.25rem 0.75rem;
    border-radius: 0.5rem;
    color: #cbd5e1;
    font-size: 0.875rem;
    font-weight: 500;
    text-decoration: none;
    transition: all 200ms ease-out;
    position: relative;
}

.nav-item:hover {
    background: rgba(255, 255, 255, 0.05);
    color: #f8fafc;
}

.nav-item.active {
    background: linear-gradient(135deg, rgba(249, 115, 22, 0.2) 0%, rgba(249, 115, 22, 0.1) 100%);
    color: #f97316;
    border-left: 3px solid #f97316;
}

.nav-icon {
    width: 1.5rem;
    height: 1.5rem;
    flex-shrink: 0;
}

.nav-label {
    flex: 1;
    white-space: nowrap;
    overflow: hidden;
}

.nav-badge {
    padding: 0.125rem 0.5rem;
    background: #f97316;
    color: white;
    font-size: 0.75rem;
    border-radius: 9999px;
    font-weight: 600;
}

.sidebar-collapsed .nav-label,
.sidebar-collapsed .nav-badge {
    display: none;
}

.sidebar-collapsed .nav-item {
    justify-content: center;
    padding-left: 0.75rem;
    padding-right: 0.75rem;
}

/* Toggle Button */
.sidebar-toggle {
    padding: 0.75rem;
    margin: 1rem;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(148, 163, 184, 0.1);
    border-radius: 0.5rem;
    color: #cbd5e1;
    cursor: pointer;
    transition: all 200ms ease-out;
}

.sidebar-toggle:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #f8fafc;
}

.toggle-icon {
    width: 1.25rem;
    height: 1.25rem;
    margin: 0 auto;
    display: block;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .sidebar {
        width: 16rem;
    }

    .sidebar.sidebar-collapsed {
        transform: translateX(-100%);
    }
}
</style>
