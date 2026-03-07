<template>
    <!-- T078: Kanban View Page with tabs for Boards, Members, and Trash -->
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

        <!-- Main project view -->
        <div v-else-if="project">
            <!-- Archived project banner -->
            <div v-if="project.is_archived" class="archived-banner">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                </svg>
                <span>This project is archived and in read-only mode. Unarchive it to make changes.</span>
            </div>

            <!-- Tab Navigation -->
            <div class="tab-navigation">
                <div class="tab-buttons">
                    <button
                        class="tab-btn"
                        :class="{ active: activeTab === 'boards' }"
                        @click="activeTab = 'boards'"
                    >
                        <svg fill="currentColor" viewBox="0 0 20 20">
                            <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" />
                        </svg>
                        Boards
                    </button>
                    <button
                        class="tab-btn"
                        :class="{ active: activeTab === 'members' }"
                        @click="activeTab = 'members'"
                    >
                        <svg fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM8 7a1 1 0 11-2 0 1 1 0 012 0zM14 6a3 3 0 11-6 0 3 3 0 016 0zM15 7a1 1 0 11-2 0 1 1 0 012 0zM12.93 11a6 6 0 00-11.86 0v3h12.14v-3zM16 7a1 1 0 100-2 1 1 0 000 2zM19 12.5a1 1 0 01-1 1h-2a2 2 0 01-2-2v-1.5a1 1 0 011-1h2a2 2 0 012 2v1.5z" />
                        </svg>
                        Members
                    </button>
                    <button
                        class="tab-btn"
                        :class="{ active: activeTab === 'trash' }"
                        @click="activeTab = 'trash'"
                    >
                        <svg fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        Trash
                    </button>
                </div>
            </div>

            <!-- Tab Content -->
            <div class="tab-content">
                <!-- Boards Tab -->
                <KanbanBoard
                    v-show="activeTab === 'boards'"
                    :project-id="projectId"
                    :is-archived="project.is_archived"
                />

                <!-- Members Tab (Placeholder) -->
                <div v-show="activeTab === 'members'" class="members-tab">
                    <div class="placeholder">
                        <p>Members management will be implemented in Phase 5</p>
                    </div>
                </div>

                <!-- Trash Tab -->
                <TrashTab
                    v-show="activeTab === 'trash'"
                    :project-id="projectId"
                />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import KanbanBoard from '../../components/kanban/KanbanBoard.vue';
import TrashTab from '../../components/projects/TrashTab.vue';

const route = useRoute();

// Get project ID from route params
const projectId = computed(() => route.params.id);

// T055: Project state for checking archived status
const project = ref(null);
const loading = ref(false);
const error = ref(null);
const activeTab = ref('boards'); // T029: Track active tab

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
    display: flex;
    flex-direction: column;
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

/* T029: Tab Navigation */
.tab-navigation {
    background-color: var(--black-primary);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    padding: 0 var(--spacing-lg);
}

.tab-buttons {
    display: flex;
    gap: var(--spacing-md);
}

.tab-btn {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    padding: var(--spacing-md) var(--spacing-lg);
    background: transparent;
    border: none;
    border-bottom: 2px solid transparent;
    color: var(--text-secondary);
    cursor: pointer;
    transition: all 0.2s;
    font-size: var(--text-base);
    font-weight: 500;
}

.tab-btn svg {
    width: 1.25rem;
    height: 1.25rem;
}

.tab-btn:hover {
    color: var(--text-primary);
    background: rgba(255, 255, 255, 0.05);
}

.tab-btn.active {
    color: #667eea;
    border-bottom-color: #667eea;
}

/* Tab Content */
.tab-content {
    flex: 1;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.members-tab,
.placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 300px;
    color: var(--text-secondary);
    font-size: var(--text-base);
}

/* Mobile responsive */
@media (max-width: 639px) {
    .tab-buttons {
        gap: var(--spacing-sm);
        overflow-x: auto;
        padding: var(--spacing-sm);
    }

    .tab-btn {
        padding: var(--spacing-sm) var(--spacing-md);
        font-size: var(--text-sm);
    }

    .tab-btn svg {
        width: 1rem;
        height: 1rem;
    }
}
</style>
