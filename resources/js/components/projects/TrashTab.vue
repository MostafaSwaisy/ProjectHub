<template>
    <div class="trash-tab">
        <!-- Filter buttons -->
        <div class="filter-section">
            <div class="filter-buttons">
                <button
                    class="filter-btn"
                    :class="{ active: !activeFilter }"
                    @click="handleFilterClick(null)"
                >
                    All
                </button>
                <button
                    class="filter-btn"
                    :class="{ active: activeFilter === 'tasks' }"
                    @click="handleFilterClick('tasks')"
                >
                    Tasks
                </button>
                <button
                    class="filter-btn"
                    :class="{ active: activeFilter === 'boards' }"
                    @click="handleFilterClick('boards')"
                >
                    Boards
                </button>
                <button
                    class="filter-btn"
                    :class="{ active: activeFilter === 'columns' }"
                    @click="handleFilterClick('columns')"
                >
                    Columns
                </button>
                <button
                    class="filter-btn"
                    :class="{ active: activeFilter === 'subtasks' }"
                    @click="handleFilterClick('subtasks')"
                >
                    Subtasks
                </button>
                <button
                    class="filter-btn"
                    :class="{ active: activeFilter === 'comments' }"
                    @click="handleFilterClick('comments')"
                >
                    Comments
                </button>
            </div>
        </div>

        <!-- Loading state -->
        <div v-if="loading" class="loading-state">
            <div class="spinner"></div>
            <p>Loading trash items...</p>
        </div>

        <!-- Empty state -->
        <div v-else-if="isEmpty" class="empty-state">
            <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            <h3>No deleted items</h3>
            <p>Items you delete will appear here in the trash.</p>
        </div>

        <!-- Trash items list -->
        <div v-else class="trash-items-container">
            <div class="trash-items-list">
                <div
                    v-for="item in filteredItems"
                    :key="`${item.type}-${item.id}`"
                    class="trash-item"
                >
                    <div class="item-content">
                        <!-- Item type icon -->
                        <div class="item-type-icon" :class="`type-${item.type}`">
                            <svg fill="currentColor" viewBox="0 0 20 20">
                                <template v-if="item.type === 'task'">
                                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 1 1 0 000-2H6a4 4 0 014 4v10a2 2 0 01-2 2H4a2 2 0 01-2-2V5zm3 4a1 1 0 100 2h3a1 1 0 100-2H7z" clip-rule="evenodd" />
                                </template>
                                <template v-else-if="item.type === 'board'">
                                    <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" />
                                </template>
                                <template v-else-if="item.type === 'column'">
                                    <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h3a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6z" />
                                </template>
                                <template v-else-if="item.type === 'subtask'">
                                    <path fill-rule="evenodd" d="M6.707 6.707a1 1 0 010 1.414L5.414 9l1.293 1.293a1 1 0 01-1.414 1.414l-2-2a1 1 0 010-1.414l2-2a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </template>
                                <template v-else-if="item.type === 'comment'">
                                    <path fill-rule="evenodd" d="M2 5a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V5zm3.293 1.293a1 1 0 011.414 0L10 9.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </template>
                            </svg>
                        </div>

                        <!-- Item details -->
                        <div class="item-details">
                            <div class="item-header">
                                <h4 class="item-title">{{ item.title }}</h4>
                                <span class="item-type-badge">{{ item.type }}</span>
                            </div>

                            <!-- Deletion metadata -->
                            <div class="item-meta">
                                <span class="meta-item">
                                    <svg class="meta-icon" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M5.5 13a3.5 3.5 0 01-.369-6.98 4 4 0 117.753-1.3A4.5 4.5 0 1113.5 13H11V9.413l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13H5.5z" />
                                    </svg>
                                    Deleted {{ formatDate(item.deleted_at) }}
                                </span>
                                <span v-if="item.deleted_by" class="meta-item">
                                    <svg class="meta-icon" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                    </svg>
                                    by {{ item.deleted_by.name }}
                                </span>
                            </div>

                            <!-- Parent reference -->
                            <div v-if="item.parent" class="item-parent">
                                <span class="parent-label">{{ item.parent.type }}:</span>
                                <span class="parent-title">{{ item.parent.title }}</span>
                                <span v-if="!item.parent.exists" class="parent-deleted">(deleted)</span>
                            </div>
                        </div>
                    </div>

                    <!-- Action buttons (placeholder for Phase 5) -->
                    <div class="item-actions">
                        <button class="action-btn restore-btn" title="Restore this item">
                            <svg fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 1119.778 5.363 1 1 0 11-1.497-1.322A5.002 5.002 0 1011 6v-2a1 1 0 011-1h-3a1 1 0 01-1-1v3z" clip-rule="evenodd" />
                            </svg>
                            Restore
                        </button>
                        <button class="action-btn delete-btn" title="Permanently delete this item">
                            <svg fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            Delete
                        </button>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="pagination.last_page > 1" class="pagination-section">
                <button
                    class="page-btn"
                    :disabled="pagination.current_page === 1"
                    @click="previousPage"
                >
                    Previous
                </button>
                <span class="page-info">
                    Page {{ pagination.current_page }} of {{ pagination.last_page }}
                </span>
                <button
                    class="page-btn"
                    :disabled="pagination.current_page === pagination.last_page"
                    @click="nextPage"
                >
                    Next
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useTrashStore } from '../../stores/trash.js';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';

dayjs.extend(relativeTime);

const props = defineProps({
    projectId: {
        type: [String, Number],
        required: true,
    },
});

const trashStore = useTrashStore();
const currentPage = ref(1);

// Computed
const items = computed(() => trashStore.items);
const filteredItems = computed(() => trashStore.filteredItems);
const loading = computed(() => trashStore.loading);
const error = computed(() => trashStore.error);
const pagination = computed(() => trashStore.pagination);
const activeFilter = computed(() => trashStore.activeFilter);
const isEmpty = computed(() => trashStore.isEmpty && !loading.value);

// Methods
const formatDate = (dateString) => {
    if (!dateString) return 'Unknown';
    return dayjs(dateString).fromNow();
};

const handleFilterClick = async (type) => {
    trashStore.setFilter(type);
    currentPage.value = 1;
    await loadTrash();
};

const loadTrash = async () => {
    try {
        await trashStore.fetchTrashItems(props.projectId, activeFilter.value, currentPage.value, 20);
    } catch (err) {
        console.error('Error loading trash:', err);
    }
};

const previousPage = async () => {
    if (currentPage.value > 1) {
        currentPage.value--;
        await loadTrash();
    }
};

const nextPage = async () => {
    if (currentPage.value < pagination.value.last_page) {
        currentPage.value++;
        await loadTrash();
    }
};

// Lifecycle
onMounted(() => {
    loadTrash();
});
</script>

<style scoped>
.trash-tab {
    padding: var(--spacing-lg);
    background-color: var(--black-primary);
    min-height: 400px;
}

/* Filter Section */
.filter-section {
    margin-bottom: var(--spacing-lg);
}

.filter-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: var(--spacing-sm);
}

.filter-btn {
    padding: var(--spacing-xs) var(--spacing-md);
    background: rgba(255, 255, 255, 0.05);
    color: var(--text-secondary);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--radius-md);
    font-size: var(--text-sm);
    cursor: pointer;
    transition: all 0.2s;
}

.filter-btn:hover {
    background: rgba(255, 255, 255, 0.1);
    color: var(--text-primary);
}

.filter-btn.active {
    background: #667eea;
    color: white;
    border-color: #667eea;
}

/* Loading State */
.loading-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 300px;
    gap: var(--spacing-md);
}

.spinner {
    width: 2rem;
    height: 2rem;
    border: 2px solid rgba(255, 255, 255, 0.1);
    border-top-color: #667eea;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Empty State */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 300px;
    gap: var(--spacing-md);
    padding: var(--spacing-2xl);
}

.empty-icon {
    width: 3rem;
    height: 3rem;
    color: var(--text-secondary);
}

.empty-state h3 {
    color: var(--text-primary);
    margin: 0;
    font-size: var(--text-lg);
}

.empty-state p {
    color: var(--text-secondary);
    margin: 0;
    font-size: var(--text-sm);
}

/* Trash Items Container */
.trash-items-container {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-md);
}

.trash-items-list {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-sm);
}

/* Trash Item */
.trash-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--spacing-md);
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--radius-md);
    transition: all 0.2s;
}

.trash-item:hover {
    background: rgba(255, 255, 255, 0.08);
    border-color: rgba(255, 255, 255, 0.2);
}

.item-content {
    display: flex;
    gap: var(--spacing-md);
    flex: 1;
    min-width: 0;
}

/* Item Type Icon */
.item-type-icon {
    width: 2rem;
    height: 2rem;
    min-width: 2rem;
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: var(--text-sm);
}

.item-type-icon svg {
    width: 1.25rem;
    height: 1.25rem;
}

.type-task {
    background: rgba(59, 130, 246, 0.3);
    color: #3b82f6;
}

.type-board {
    background: rgba(168, 85, 247, 0.3);
    color: #a855f7;
}

.type-column {
    background: rgba(236, 72, 153, 0.3);
    color: #ec4899;
}

.type-subtask {
    background: rgba(34, 197, 94, 0.3);
    color: #22c55e;
}

.type-comment {
    background: rgba(249, 115, 22, 0.3);
    color: #f97316;
}

/* Item Details */
.item-details {
    flex: 1;
    min-width: 0;
}

.item-header {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    margin-bottom: var(--spacing-xs);
}

.item-title {
    color: var(--text-primary);
    font-size: var(--text-base);
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.item-type-badge {
    display: inline-block;
    padding: 0.125rem 0.5rem;
    background: rgba(255, 255, 255, 0.1);
    color: var(--text-secondary);
    font-size: var(--text-xs);
    border-radius: var(--radius-sm);
    white-space: nowrap;
}

/* Item Meta */
.item-meta {
    display: flex;
    gap: var(--spacing-md);
    font-size: var(--text-xs);
    color: var(--text-secondary);
    margin-bottom: var(--spacing-xs);
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.meta-icon {
    width: 0.75rem;
    height: 0.75rem;
}

/* Item Parent */
.item-parent {
    display: flex;
    align-items: center;
    gap: var(--spacing-xs);
    font-size: var(--text-xs);
    color: var(--text-secondary);
}

.parent-label {
    color: var(--text-secondary);
}

.parent-title {
    color: var(--text-primary);
    font-weight: 500;
}

.parent-deleted {
    color: #ef4444;
}

/* Item Actions */
.item-actions {
    display: flex;
    gap: var(--spacing-sm);
    flex-shrink: 0;
}

.action-btn {
    display: flex;
    align-items: center;
    gap: var(--spacing-xs);
    padding: var(--spacing-xs) var(--spacing-md);
    border: 1px solid transparent;
    border-radius: var(--radius-md);
    font-size: var(--text-sm);
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
}

.action-btn svg {
    width: 1rem;
    height: 1rem;
}

.restore-btn {
    color: #22c55e;
    background: rgba(34, 197, 94, 0.1);
    border-color: rgba(34, 197, 94, 0.3);
}

.restore-btn:hover {
    background: rgba(34, 197, 94, 0.2);
    border-color: #22c55e;
}

.delete-btn {
    color: #ef4444;
    background: rgba(239, 68, 68, 0.1);
    border-color: rgba(239, 68, 68, 0.3);
}

.delete-btn:hover {
    background: rgba(239, 68, 68, 0.2);
    border-color: #ef4444;
}

/* Pagination */
.pagination-section {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: var(--spacing-md);
    margin-top: var(--spacing-lg);
}

.page-btn {
    padding: var(--spacing-sm) var(--spacing-md);
    background: rgba(255, 255, 255, 0.05);
    color: var(--text-primary);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--radius-md);
    cursor: pointer;
    transition: all 0.2s;
    font-size: var(--text-sm);
}

.page-btn:hover:not(:disabled) {
    background: rgba(255, 255, 255, 0.1);
    border-color: rgba(255, 255, 255, 0.2);
}

.page-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.page-info {
    color: var(--text-secondary);
    font-size: var(--text-sm);
}

/* Mobile responsive */
@media (max-width: 639px) {
    .trash-tab {
        padding: var(--spacing-md);
    }

    .trash-item {
        flex-direction: column;
        align-items: flex-start;
    }

    .item-actions {
        margin-top: var(--spacing-sm);
        width: 100%;
    }

    .action-btn {
        flex: 1;
        justify-content: center;
    }

    .item-meta {
        flex-direction: column;
        gap: var(--spacing-xs);
    }

    .filter-buttons {
        gap: var(--spacing-xs);
    }

    .filter-btn {
        font-size: var(--text-xs);
        padding: var(--spacing-xs) var(--spacing-sm);
    }
}
</style>
