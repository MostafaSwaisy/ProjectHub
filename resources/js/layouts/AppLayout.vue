<template>
    <!-- T016: Main authenticated application layout -->
    <div class="app-layout">
        <!-- Top Navigation Bar -->
        <TopNavbar />

        <!-- Sidebar Navigation -->
        <Sidebar />

        <!-- Main Content Area -->
        <main
            class="app-main"
            :class="{ 'sidebar-collapsed': layoutStore.sidebarCollapsed }"
        >
            <!-- Page Content Slot -->
            <div class="app-content">
                <slot />
            </div>
        </main>

        <!-- Mobile Overlay (when sidebar is open on mobile) -->
        <div
            v-if="layoutStore.isMobile && !layoutStore.sidebarCollapsed"
            class="mobile-overlay"
            @click="layoutStore.setSidebarCollapsed(true)"
        ></div>

        <!-- Toast Notifications -->
        <Toast />
    </div>
</template>

<script setup>
import { onMounted, onUnmounted } from 'vue'
import { useLayoutStore } from '../stores/layout'
import TopNavbar from '../components/layout/TopNavbar.vue'
import Sidebar from '../components/layout/Sidebar.vue'
import Toast from '../components/shared/Toast.vue'

const layoutStore = useLayoutStore()

// Initialize layout on mount
onMounted(() => {
    layoutStore.initializeLayout()
})

// Cleanup on unmount
onUnmounted(() => {
    window.removeEventListener('resize', layoutStore.updateIsMobile)
})
</script>

<style scoped>
.app-layout {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
}

.app-main {
    margin-left: 16rem; /* 256px sidebar width */
    margin-top: 4rem; /* 64px navbar height */
    padding: 2rem;
    min-height: calc(100vh - 4rem);
    transition: margin-left 300ms cubic-bezier(0.4, 0, 0.2, 1);
}

.app-main.sidebar-collapsed {
    margin-left: 4rem; /* 64px collapsed sidebar width */
}

.app-content {
    max-width: 1600px;
    margin: 0 auto;
    width: 100%;
}

/* Mobile Overlay */
.mobile-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    z-index: 40;
    animation: fadeIn 200ms ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .app-main {
        margin-left: 0;
        padding: 1rem;
    }

    .app-main.sidebar-collapsed {
        margin-left: 0;
    }
}
</style>
