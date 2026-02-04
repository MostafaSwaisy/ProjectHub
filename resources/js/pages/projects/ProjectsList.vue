<template>
    <!-- T012: Placeholder Projects List Page -->
    <AppLayout>
        <div class="page-container">
            <div class="page-header">
                <h1 class="page-title">Projects</h1>
                <p class="page-subtitle">Manage and view all your projects</p>
            </div>
            <div class="placeholder-message">
                <p>Projects list functionality will be implemented in Phase 5 (US3)</p>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '../../layouts/AppLayout.vue'
</script>

<style scoped>
.page-container {
    padding: 2rem 0;
}

.page-header {
    margin-bottom: 2rem;
}

.page-title {
    font-size: 2rem;
    font-weight: 700;
    color: #f8fafc;
    margin: 0 0 0.5rem 0;
}

.page-subtitle {
    font-size: 1rem;
    color: #94a3b8;
    margin: 0;
}

.placeholder-message {
    padding: 3rem;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(148, 163, 184, 0.2);
    border-radius: 0.75rem;
    text-align: center;
    color: #cbd5e1;
}
</style>
