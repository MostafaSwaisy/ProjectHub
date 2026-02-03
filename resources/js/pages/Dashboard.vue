<template>
    <!-- T037: Dashboard with DashboardStats component -->
    <AppLayout>
        <div class="dashboard-container">
            <div class="dashboard-header">
                <h1 class="dashboard-title">Dashboard</h1>
                <p class="dashboard-subtitle">Welcome to ProjectHub Analytics</p>
            </div>

            <!-- T036-T040: Real-time dashboard statistics -->
            <DashboardStats />
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '../layouts/AppLayout.vue';
import DashboardStats from '../components/dashboard/DashboardStats.vue';
</script>

<style scoped>
.dashboard-container {
    color: var(--text-primary);
    padding: 32px;
}

.dashboard-header {
    margin-bottom: 32px;
}

.dashboard-title {
    font-size: 32px;
    font-weight: 700;
    color: var(--text-primary);
    margin: 0 0 8px 0;
}

.dashboard-subtitle {
    font-size: 16px;
    color: var(--text-secondary);
    margin: 0;
}

/* Mobile responsive */
@media (max-width: 768px) {
    .dashboard-container {
        padding: 20px;
    }

    .dashboard-title {
        font-size: 24px;
    }

    .dashboard-subtitle {
        font-size: 14px;
    }
}
</style>
