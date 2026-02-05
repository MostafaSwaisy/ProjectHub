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
                    <!-- New Project Button -->
                    <button class="btn-new-project" @click="handleCreateProject">
                        <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        New Project
                    </button>

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

            <!-- T052: Active/Archived Tabs -->
            <div class="tabs-container">
                <button
                    class="tab-btn"
                    :class="{ active: !isArchivedTab }"
                    @click="switchTab(false)"
                >
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Active Projects
                </button>
                <button
                    class="tab-btn"
                    :class="{ active: isArchivedTab }"
                    @click="switchTab(true)"
                >
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                    </svg>
                    Archived Projects
                </button>
            </div>

            <!-- Filters -->
            <ProjectFilters
                :filters="projectsStore.filters"
                @update:filters="handleFiltersUpdate"
            />

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

        <!-- Project Modal -->
        <ProjectModal
            :is-open="showModal"
            :project="editingProject"
            :loading="modalLoading"
            @close="handleModalClose"
            @submit="handleSubmitProject"
        />

        <!-- Conflict Modal -->
        <ConflictModal
            :is-open="showConflictModal"
            :current-data="conflictData"
            @reload="handleReloadAfterConflict"
            @discard="handleDiscardAfterConflict"
        />

        <!-- Delete Confirmation Modal -->
        <DeleteConfirmModal
            :is-open="showDeleteModal"
            :project="deletingProject"
            :task-count="deletingProject?.task_completion?.total || 0"
            :loading="deleteLoading"
            @confirm="handleConfirmDelete"
            @cancel="handleCancelDelete"
        />

        <!-- Archive Confirmation Modal -->
        <ArchiveConfirmModal
            :is-open="showArchiveModal"
            :project="archivingProject"
            :is-archiving="isArchiving"
            :loading="archiveLoading"
            @confirm="handleConfirmArchive"
            @cancel="handleCancelArchive"
        />
    </AppLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useProjectsStore } from '../../stores/projects';
import { useToast } from '../../composables/useToast';
import AppLayout from '../../layouts/AppLayout.vue';
import ProjectCard from '../../components/projects/ProjectCard.vue';
import ProjectRow from '../../components/projects/ProjectRow.vue';
import EmptyState from '../../components/projects/EmptyState.vue';
import ProjectModal from '../../components/projects/ProjectModal.vue';
import ConflictModal from '../../components/projects/ConflictModal.vue';
import DeleteConfirmModal from '../../components/projects/DeleteConfirmModal.vue';
import ArchiveConfirmModal from '../../components/projects/ArchiveConfirmModal.vue';
import ProjectFilters from '../../components/projects/ProjectFilters.vue';

const router = useRouter();
const projectsStore = useProjectsStore();
const toast = useToast();

// Modal state
const showModal = ref(false);
const modalLoading = ref(false);
const editingProject = ref(null);

// Conflict modal state
const showConflictModal = ref(false);
const conflictData = ref(null);

// Delete modal state
const showDeleteModal = ref(false);
const deletingProject = ref(null);
const deleteLoading = ref(false);

// Archive modal state
const showArchiveModal = ref(false);
const archivingProject = ref(null);
const isArchiving = ref(true); // true = archiving, false = unarchiving
const archiveLoading = ref(false);

// Tab state
const isArchivedTab = ref(false);

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

const handleSubmitProject = async (formData) => {
    modalLoading.value = true;

    try {
        if (editingProject.value) {
            // T032/T035: Update existing project with optimistic locking
            const updateData = {
                ...formData,
                updated_at: editingProject.value.updated_at,
            };

            await projectsStore.updateProject(editingProject.value.id, updateData);
            toast.success('Project updated successfully');
        } else {
            // T030: Create new project
            await projectsStore.createProject(formData);
            toast.success('Project created successfully');
        }

        // Close modal and reset state
        handleModalClose();
    } catch (error) {
        // T036: Handle 409 Conflict for concurrent edits
        if (error.response?.status === 409) {
            conflictData.value = error.response.data.current_data;
            showConflictModal.value = true;
        } else {
            // Show error toast
            toast.error(
                error.response?.data?.errors
                    ? Object.values(error.response.data.errors).flat().join(', ')
                    : `Failed to ${editingProject.value ? 'update' : 'create'} project. Please try again.`
            );
        }
    } finally {
        modalLoading.value = false;
    }
};

const handleModalClose = () => {
    showModal.value = false;
    editingProject.value = null;
};

const handleCreateProject = () => {
    editingProject.value = null;
    showModal.value = true;
};

const handleEdit = (project) => {
    // T037: Open edit modal with project data
    // T039: Permission check is already in ProjectCard/ProjectRow (only shows edit button if can_edit)
    editingProject.value = project;
    showModal.value = true;
};

const handleReloadAfterConflict = () => {
    // Reload the project data from server
    editingProject.value = conflictData.value;
    conflictData.value = null;
    showConflictModal.value = false;
    // Keep modal open with refreshed data
};

const handleDiscardAfterConflict = () => {
    // Discard changes and close everything
    conflictData.value = null;
    showConflictModal.value = false;
    handleModalClose();
};

const switchTab = (archived) => {
    isArchivedTab.value = archived;
    projectsStore.setFilters({ archived });
    loadProjects();
};

const handleArchive = (project) => {
    // Determine if we're archiving or unarchiving based on current state
    archivingProject.value = project;
    isArchiving.value = !project.is_archived;
    showArchiveModal.value = true;
};

const handleConfirmArchive = async () => {
    if (!archivingProject.value) return;

    archiveLoading.value = true;

    try {
        if (isArchiving.value) {
            await projectsStore.archiveProject(archivingProject.value.id);
            toast.success('Project archived successfully');
        } else {
            await projectsStore.unarchiveProject(archivingProject.value.id);
            toast.success('Project unarchived successfully');
        }

        // Close modal and reset state
        showArchiveModal.value = false;
        archivingProject.value = null;

        // Reload projects to reflect the change
        loadProjects();
    } catch (error) {
        if (error.response?.status === 403) {
            toast.error('You do not have permission to archive this project. Only the project owner can archive it.');
        } else {
            toast.error(
                error.response?.data?.message || `Failed to ${isArchiving.value ? 'archive' : 'unarchive'} project. Please try again.`
            );
        }
    } finally {
        archiveLoading.value = false;
    }
};

const handleCancelArchive = () => {
    showArchiveModal.value = false;
    archivingProject.value = null;
};

const handleDelete = (project) => {
    // T044: Open delete confirmation modal
    // T045: Permission check is already in ProjectCard/ProjectRow (only shows delete button if can_delete)
    deletingProject.value = project;
    showDeleteModal.value = true;
};

const handleConfirmDelete = async () => {
    if (!deletingProject.value) return;

    deleteLoading.value = true;

    try {
        await projectsStore.deleteProject(deletingProject.value.id);

        // Close modal and reset state
        showDeleteModal.value = false;
        deletingProject.value = null;

        // Show success toast
        toast.success('Project deleted successfully');

        // T047: Project is already optimistically removed from list by the store action
    } catch (error) {
        // T046: Show error message if non-owner attempts delete (403 Forbidden)
        if (error.response?.status === 403) {
            toast.error('You do not have permission to delete this project. Only the project owner can delete it.');
        } else {
            toast.error(
                error.response?.data?.message || 'Failed to delete project. Please try again.'
            );
        }
    } finally {
        deleteLoading.value = false;
    }
};

const handleCancelDelete = () => {
    showDeleteModal.value = false;
    deletingProject.value = null;
};

// T064: Handle filter updates with debounce
let filterDebounceTimer = null;

const handleFiltersUpdate = (newFilters) => {
    // Clear existing timer
    if (filterDebounceTimer) {
        clearTimeout(filterDebounceTimer);
    }

    // Update filters in store
    projectsStore.setFilters(newFilters);

    // Debounce the refetch (300ms delay)
    filterDebounceTimer = setTimeout(() => {
        loadProjects();
    }, 300);
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

.btn-new-project {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 0.625rem 1.25rem;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.btn-new-project:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
}

.btn-new-project:active {
    transform: translateY(0);
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

.tabs-container {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 2rem;
    border-bottom: 2px solid rgba(148, 163, 184, 0.2);
}

.tab-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: transparent;
    border: none;
    padding: 0.75rem 1.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: #94a3b8;
    cursor: pointer;
    position: relative;
    transition: all 0.2s;
    border-bottom: 2px solid transparent;
    margin-bottom: -2px;
}

.tab-btn:hover {
    color: #f8fafc;
    background: rgba(255, 255, 255, 0.05);
}

.tab-btn.active {
    color: #667eea;
    border-bottom-color: #667eea;
}

.tab-btn .icon {
    width: 1rem;
    height: 1rem;
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
