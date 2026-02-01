<template>
    <!-- T034: Dashboard with design system colors and dark theme -->
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1 class="dashboard-title">Dashboard</h1>
            <p class="dashboard-subtitle">Welcome to ProjectHub Analytics</p>
        </div>

        <div class="stats-grid">
            <!-- Total Projects Card -->
            <div class="stat-card">
                <p class="stat-label">Total Projects</p>
                <p class="stat-value">--</p>
            </div>

            <!-- Active Tasks Card -->
            <div class="stat-card">
                <p class="stat-label">Active Tasks</p>
                <p class="stat-value">--</p>
            </div>

            <!-- Team Members Card -->
            <div class="stat-card">
                <p class="stat-label">Team Members</p>
                <p class="stat-value">--</p>
            </div>

            <!-- Overdue Tasks Card -->
            <div class="stat-card stat-card-alert">
                <p class="stat-label">Overdue Tasks</p>
                <p class="stat-value stat-value-alert">--</p>
            </div>
        </div>
    </div>
</template>

<script setup>
// Dashboard component - to be implemented
</script>

<style scoped>
/* T034: Design system consistency - Dashboard */

.dashboard-container {
    min-height: 100vh;
    background-color: var(--black-primary);
    color: var(--text-primary);
    padding: var(--spacing-lg);
}

.dashboard-header {
    margin-bottom: var(--spacing-2xl);
}

.dashboard-title {
    font-size: var(--text-3xl);
    font-weight: var(--font-bold);
    color: var(--text-primary);
    margin: 0 0 var(--spacing-sm) 0;
}

.dashboard-subtitle {
    font-size: var(--text-base);
    color: var(--text-secondary);
    margin: 0;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: var(--spacing-lg);
    margin-top: var(--spacing-2xl);
}

.stat-card {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--radius-lg);
    padding: var(--spacing-lg);
    transition: all var(--transition-normal);
}

.stat-card:hover {
    background: rgba(255, 255, 255, 0.08);
    border-color: rgba(255, 255, 255, 0.2);
    box-shadow: var(--shadow-md);
}

.stat-card-alert {
    border-color: rgba(239, 68, 68, 0.3);
    background: rgba(239, 68, 68, 0.05);
}

.stat-card-alert:hover {
    border-color: rgba(239, 68, 68, 0.5);
    background: rgba(239, 68, 68, 0.1);
}

.stat-label {
    font-size: var(--text-sm);
    font-weight: var(--font-medium);
    color: var(--text-secondary);
    margin: 0;
}

.stat-value {
    font-size: var(--text-3xl);
    font-weight: var(--font-bold);
    color: var(--orange-primary);
    margin: var(--spacing-md) 0 0 0;
}

.stat-value-alert {
    color: var(--red-primary);
}

/* Mobile responsive */
@media (max-width: 639px) {
    .dashboard-container {
        padding: var(--spacing-md);
    }

    .dashboard-title {
        font-size: var(--text-2xl);
    }

    .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>
