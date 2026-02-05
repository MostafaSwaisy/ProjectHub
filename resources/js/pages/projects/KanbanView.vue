<template>
    <!-- T078: Kanban View Page - integrates KanbanBoard component -->
    <div class="kanban-view">
        <!-- Loading state -->
        <div v-if="loading" class="loading-state">
            <div class="spinner"></div>
            <p>Loading project...</p>
        </div>

        <!-- Error state -->
        <div v-else-if="error" class="error-state">
            <p class="error-message">{{ error }}</p>
            <button class="retry-btn" @click="loadProject">Retry</button>
        </div>

        <!-- Archived project banner -->
        <div v-else-if="project && project.is_archived" class="archived-banner">
            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
            </svg>
            <span>This project is archived and in read-only mode. Unarchive it to make changes.</span>
        </div>

        <!-- Kanban Board -->
        <KanbanBoard
            v-if="project"
            :project-id="projectId"
            :is-archived="project.is_archived"
        />
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import KanbanBoard from '../../components/kanban/KanbanBoard.vue';

const route = useRoute();

// Get project ID from route params
const projectId = computed(() => route.params.id);

// T055: Project state for checking archived status
const project = ref(null);
const loading = ref(false);
const error = ref(null);

// Fetch project details
const loadProject = async () => {
    loading.value = true;
    error.value = null;

    try {
        const response = await axios.get(`/api/projects/${projectId.value}`);
        project.value = response.data.data;
    } catch (err) {
        error.value = err.response?.data?.message || 'Failed to load project';
        console.error('Error loading project:', err);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    loadProject();
});
</script>

<style scoped>
.kanban-view {
    width: 100%;
    height: 100%;
}

.loading-state,
.error-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 400px;
    gap: 1rem;
}

.spinner {
    width: 3rem;
    height: 3rem;
    border: 3px solid rgba(255, 255, 255, 0.1);
    border-top-color: #667eea;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.error-message {
    color: #ef4444;
    font-size: 1rem;
}

.retry-btn {
    background: rgba(239, 68, 68, 0.2);
    color: #ef4444;
    border: 1px solid #ef4444;
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    cursor: pointer;
    transition: all 0.2s;
}

.retry-btn:hover {
    background: rgba(239, 68, 68, 0.3);
}

.archived-banner {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 1.5rem;
    margin: 1rem;
    background: rgba(245, 158, 11, 0.1);
    border: 1px solid rgba(245, 158, 11, 0.3);
    border-radius: 0.5rem;
    color: #f59e0b;
    font-size: 0.875rem;
    font-weight: 500;
}

.archived-banner .icon {
    width: 1.5rem;
    height: 1.5rem;
    flex-shrink: 0;
}
</style>
