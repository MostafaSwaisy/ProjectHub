<template>
    <AppLayout>
        <div class="page-container">
            <!-- Header -->
            <div class="page-header">
                <div class="header-left">
                    <h1 class="page-title">
                        Projects
                        <span v-if="projectsStore.totalCount > 0" class="count">
                            ({{ projectsStore.totalCount }})
                        </span>
                    </h1>
                    <p class="page-subtitle">Manage and view all your projects</p>
                </div>
                <div class="header-actions">
                    <!-- View Toggle -->
                    <div class="view-toggle">
                        <button
                            class="toggle-btn"
                            :class="{ active: viewMode === 'grid' }"
                            @click="setViewMode('grid')"
                            title="Grid View"
                        >
                            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                        </button>
                        <button
                            class="toggle-btn"
                            :class="{ active: viewMode === 'list' }"
                            @click="setViewMode('list')"
                            title="List View"
                        >
                            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Loading State -->
            <div v-if="projectsStore.loading" class="loading-container">
                <div class="spinner"></div>
                <p>Loading projects...</p>
            </div>

            <!-- Error State -->
            <div v-else-if="projectsStore.error" class="error-container">
                <p class="error-message">{{ projectsStore.error }}</p>
                <button class="retry-btn" @click="loadProjects">
                    Try Again
                </button>
            </div>

            <!-- Empty State -->
            <EmptyState
                v-else-if="!projectsStore.hasProjects"
                title="No projects found"
                description="Get started by creating your first project to organize your work and collaborate with your team."
                @create="handleCreateProject"
            />

            <!-- Projects Grid View -->
            <div v-else-if="viewMode === 'grid'" class="projects-grid">
                <ProjectCard
                    v-for="project in projectsStore.projects"
                    :key="project.id"
                    :project="project"
                    @click="navigateToBoard"
                    @edit="handleEdit"
                    @archive="handleArchive"
                    @delete="handleDelete"
                />
            </div>

            <!-- Projects List View -->
            <div v-else class="projects-list">
                <table class="projects-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Timeline</th>
                            <th>Budget</th>
                            <th>Progress</th>
                            <th>Members</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <ProjectRow
                            v-for="project in projectsStore.projects"
                            :key="project.id"
                            :project="project"
                            @click="navigateToBoard"
                            @edit="handleEdit"
                            @archive="handleArchive"
                            @delete="handleDelete"
                        />
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div
                v-if="projectsStore.hasProjects && projectsStore.pagination"
                class="pagination"
            >
                <button
                    class="page-btn"
                    :disabled="projectsStore.pagination.current_page === 1"
                    @click="goToPage(projectsStore.pagination.current_page - 1)"
                >
                    Previous
                </button>
                <span class="page-info">
                    Page {{ projectsStore.pagination.current_page }} of {{ projectsStore.pagination.last_page }}
                </span>
                <button
                    class="page-btn"
                    :disabled="projectsStore.pagination.current_page === projectsStore.pagination.last_page"
                    @click="goToPage(projectsStore.pagination.current_page + 1)"
                >
                    Next
                </button>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useProjectsStore } from '../../stores/projects';
import AppLayout from '../../layouts/AppLayout.vue';
import ProjectCard from '../../components/projects/ProjectCard.vue';
import ProjectRow from '../../components/projects/ProjectRow.vue';
import EmptyState from '../../components/projects/EmptyState.vue';

const router = useRouter();
const projectsStore = useProjectsStore();

// View mode with localStorage persistence
const viewMode = computed({
    get: () => projectsStore.viewMode,
    set: (mode) => projectsStore.setViewMode(mode),
});

// Load saved view mode from localStorage on mount
onMounted(() => {
    const savedViewMode = localStorage.getItem('projectsViewMode');
    if (savedViewMode) {
        projectsStore.viewMode = savedViewMode;
    }
    loadProjects();
});

const loadProjects = () => {
    projectsStore.fetchProjects();
};

const setViewMode = (mode) => {
    viewMode.value = mode;
};

const goToPage = (page) => {
    projectsStore.fetchProjects(page);
};

const navigateToBoard = (project) => {
    router.push(`/projects/${project.id}/kanban`);
};

const handleCreateProject = () => {
    // T029: Will open create modal
    console.log('Create project clicked');
};

const handleEdit = (project) => {
    // T037: Will open edit modal
    console.log('Edit project', project.id);
};

const handleArchive = (project) => {
    // T051: Will implement archive
    console.log('Archive project', project.id);
};

const handleDelete = (project) => {
    // T044: Will open delete confirmation
    console.log('Delete project', project.id);
};
</script>

<style scoped>
.page-container {
    padding: 2rem;
    max-width: 1400px;
    margin: 0 auto;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.header-left {
    flex: 1;
}

.page-title {
    font-size: 2rem;
    font-weight: 700;
    color: #f8fafc;
    margin: 0 0 0.5rem 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.count {
    font-size: 1.5rem;
    color: #94a3b8;
    font-weight: 400;
}

.page-subtitle {
    font-size: 1rem;
    color: #94a3b8;
    margin: 0;
}

.header-actions {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.view-toggle {
    display: flex;
    gap: 0.25rem;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 0.5rem;
    padding: 0.25rem;
}

.toggle-btn {
    background: transparent;
    border: none;
    padding: 0.5rem;
    border-radius: 0.375rem;
    cursor: pointer;
    color: #94a3b8;
    transition: all 0.2s;
}

.toggle-btn:hover {
    color: #f8fafc;
    background: rgba(255, 255, 255, 0.1);
}

.toggle-btn.active {
    background: rgba(255, 255, 255, 0.15);
    color: #f8fafc;
}

.icon {
    width: 1.25rem;
    height: 1.25rem;
}

.loading-container,
.error-container {
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

.projects-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.projects-list {
    margin-bottom: 2rem;
}

.projects-table {
    width: 100%;
    border-collapse: collapse;
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(148, 163, 184, 0.2);
    border-radius: 0.75rem;
    overflow: hidden;
}

.projects-table thead tr {
    background: rgba(255, 255, 255, 0.05);
}

.projects-table th {
    padding: 1rem;
    text-align: left;
    font-weight: 600;
    color: #f8fafc;
    font-size: 0.875rem;
    border-bottom: 1px solid rgba(148, 163, 184, 0.2);
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 1rem;
}

.page-btn {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(148, 163, 184, 0.2);
    color: #cbd5e1;
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    cursor: pointer;
    transition: all 0.2s;
}

.page-btn:hover:not(:disabled) {
    background: rgba(255, 255, 255, 0.1);
    border-color: rgba(148, 163, 184, 0.4);
}

.page-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.page-info {
    color: #94a3b8;
    font-size: 0.875rem;
}

@media (max-width: 768px) {
    .page-container {
        padding: 1rem;
    }

    .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }

    .projects-grid {
        grid-template-columns: 1fr;
    }

    .projects-table {
        font-size: 0.75rem;
    }
}
</style>
