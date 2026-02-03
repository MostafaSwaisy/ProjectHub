<template>
    <!-- T019: Top Navigation Bar Component -->
    <header class="top-navbar">
        <div class="navbar-content">
            <!-- Left Section: Hamburger Menu (Mobile) + Logo -->
            <div class="navbar-left">
                <!-- Mobile Hamburger Menu -->
                <button
                    v-if="layoutStore.isMobile"
                    class="hamburger-btn"
                    @click="layoutStore.toggleSidebar()"
                    :aria-label="layoutStore.sidebarCollapsed ? 'Open Menu' : 'Close Menu'"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="hamburger-icon">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"
                        />
                    </svg>
                </button>

                <!-- Logo (visible on mobile when sidebar is collapsed) -->
                <div class="navbar-logo">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="logo-icon">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                        />
                    </svg>
                    <span class="logo-text">ProjectHub</span>
                </div>
            </div>

            <!-- Center Section: Search Box (Placeholder) -->
            <div class="navbar-center">
                <div class="search-box">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="search-icon">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                        />
                    </svg>
                    <input
                        type="text"
                        placeholder="Search projects and tasks..."
                        class="search-input"
                        disabled
                    />
                </div>
            </div>

            <!-- Right Section: Notifications + User Menu -->
            <div class="navbar-right">
                <!-- Notifications Icon (Placeholder) -->
                <button class="icon-btn" title="Notifications" disabled>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="icon">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                        />
                    </svg>
                    <span class="notification-badge">3</span>
                </button>

                <!-- User Menu -->
                <UserMenu />
            </div>
        </div>
    </header>
</template>

<script setup>
import { useLayoutStore } from '../../stores/layout'
import UserMenu from './UserMenu.vue'

const layoutStore = useLayoutStore()
</script>

<style scoped>
.top-navbar {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: 4rem;
    background: rgba(15, 23, 42, 0.8);
    backdrop-filter: blur(16px);
    border-bottom: 1px solid rgba(148, 163, 184, 0.1);
    z-index: 45;
}

.navbar-content {
    height: 100%;
    padding: 0 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

/* Left Section */
.navbar-left {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.hamburger-btn {
    padding: 0.5rem;
    background: transparent;
    border: none;
    color: #cbd5e1;
    cursor: pointer;
    border-radius: 0.375rem;
    transition: all 200ms ease-out;
}

.hamburger-btn:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #f8fafc;
}

.hamburger-icon {
    width: 1.5rem;
    height: 1.5rem;
}

.navbar-logo {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #f97316;
    font-size: 1.125rem;
    font-weight: 700;
}

.logo-icon {
    width: 1.75rem;
    height: 1.75rem;
}

.logo-text {
    display: none;
}

@media (max-width: 768px) {
    .logo-text {
        display: inline;
    }
}

/* Center Section */
.navbar-center {
    flex: 1;
    max-width: 32rem;
}

.search-box {
    position: relative;
    width: 100%;
}

.search-icon {
    position: absolute;
    left: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    width: 1.25rem;
    height: 1.25rem;
    color: #94a3b8;
    pointer-events: none;
}

.search-input {
    width: 100%;
    padding: 0.625rem 1rem 0.625rem 2.75rem;
    background: rgba(30, 41, 59, 0.6);
    border: 1px solid rgba(148, 163, 184, 0.2);
    border-radius: 0.5rem;
    color: #f8fafc;
    font-size: 0.875rem;
    transition: all 200ms ease-out;
}

.search-input::placeholder {
    color: #64748b;
}

.search-input:focus {
    outline: none;
    border-color: #f97316;
    background: rgba(30, 41, 59, 0.8);
}

.search-input:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

@media (max-width: 640px) {
    .navbar-center {
        display: none;
    }
}

/* Right Section */
.navbar-right {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.icon-btn {
    position: relative;
    padding: 0.5rem;
    background: transparent;
    border: none;
    color: #cbd5e1;
    cursor: pointer;
    border-radius: 0.375rem;
    transition: all 200ms ease-out;
}

.icon-btn:hover:not(:disabled) {
    background: rgba(255, 255, 255, 0.1);
    color: #f8fafc;
}

.icon-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.icon {
    width: 1.5rem;
    height: 1.5rem;
}

.notification-badge {
    position: absolute;
    top: 0.25rem;
    right: 0.25rem;
    min-width: 1.125rem;
    height: 1.125rem;
    padding: 0 0.25rem;
    background: #f97316;
    color: white;
    font-size: 0.625rem;
    font-weight: 700;
    border-radius: 9999px;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
