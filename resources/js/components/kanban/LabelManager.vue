<template>
    <!-- T092-T094: Label Manager Component with preset color palette -->
    <div class="label-manager">
        <div class="label-manager-header">
            <h3>Manage Labels</h3>
            <p class="header-subtitle">Create and organize labels for your project</p>
        </div>

        <!-- Create Label Form -->
        <form @submit.prevent="submitLabel" class="label-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="label-name">Label Name *</label>
                    <input
                        id="label-name"
                        v-model="formData.name"
                        type="text"
                        placeholder="e.g., Bug, Feature, Urgent"
                        maxlength="50"
                        required
                        class="form-input"
                    />
                </div>
            </div>

            <div class="form-group">
                <label>Color *</label>
                <div class="color-palette">
                    <div
                        v-for="color in colorPalette"
                        :key="color.value"
                        :title="color.name"
                        :class="['color-swatch', { selected: formData.color === color.value }]"
                        :style="{ backgroundColor: color.value }"
                        @click="selectColor(color.value)"
                    >
                        <svg
                            v-if="formData.color === color.value"
                            width="16"
                            height="16"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="white"
                            stroke-width="3"
                        >
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button
                    type="submit"
                    :disabled="loading || !formData.name || !formData.color"
                    class="btn btn-primary"
                >
                    {{ editingId ? 'Update Label' : 'Create Label' }}
                </button>
                <button
                    v-if="editingId"
                    type="button"
                    @click="cancelEdit"
                    class="btn btn-secondary"
                >
                    Cancel
                </button>
            </div>
        </form>

        <!-- Existing Labels List -->
        <div v-if="labels.length > 0" class="labels-list">
            <h4>Existing Labels</h4>
            <div class="labels-grid">
                <div
                    v-for="label in labels"
                    :key="label.id"
                    class="label-item"
                >
                    <div class="label-preview">
                        <span
                            class="label-color"
                            :style="{ backgroundColor: label.color }"
                        ></span>
                        <span class="label-name">{{ label.name }}</span>
                    </div>
                    <div class="label-actions">
                        <button
                            @click="editLabel(label)"
                            class="action-btn"
                            title="Edit label"
                        >
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                        </button>
                        <button
                            @click="confirmDelete(label)"
                            class="action-btn delete-btn"
                            title="Delete label"
                        >
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="empty-state">
            <p>No labels yet. Create your first label above!</p>
        </div>

        <!-- Error Display -->
        <div v-if="error" class="error-banner">
            {{ error }}
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useLabelsStore } from '@/stores/labels';
import { useRoute } from 'vue-router';

const route = useRoute();
const labelsStore = useLabelsStore();

// State
const formData = ref({
    name: '',
    color: '#3B82F6', // Default to blue
});

const editingId = ref(null);
const loading = ref(false);
const error = ref(null);

// Preset color palette (12 colors) from research.md
const colorPalette = [
    { name: 'Red', value: '#EF4444' },
    { name: 'Orange', value: '#F97316' },
    { name: 'Yellow', value: '#EAB308' },
    { name: 'Green', value: '#22C55E' },
    { name: 'Teal', value: '#14B8A6' },
    { name: 'Blue', value: '#3B82F6' },
    { name: 'Indigo', value: '#6366F1' },
    { name: 'Purple', value: '#A855F7' },
    { name: 'Pink', value: '#EC4899' },
    { name: 'Gray', value: '#6B7280' },
    { name: 'Slate', value: '#64748B' },
    { name: 'Emerald', value: '#10B981' },
];

// Computed
const labels = computed(() => labelsStore.labels);
const projectId = computed(() => parseInt(route.params.projectId || route.params.id));

// Methods
const selectColor = (color) => {
    formData.value.color = color;
};

const submitLabel = async () => {
    loading.value = true;
    error.value = null;

    try {
        if (editingId.value) {
            // Update existing label
            await labelsStore.updateLabel(projectId.value, editingId.value, formData.value);
        } else {
            // Create new label
            await labelsStore.createLabel(projectId.value, formData.value);
        }

        // Reset form
        formData.value = {
            name: '',
            color: '#3B82F6',
        };
        editingId.value = null;
    } catch (err) {
        error.value = err.response?.data?.message || 'Failed to save label';
    } finally {
        loading.value = false;
    }
};

const editLabel = (label) => {
    formData.value = {
        name: label.name,
        color: label.color,
    };
    editingId.value = label.id;
};

const cancelEdit = () => {
    formData.value = {
        name: '',
        color: '#3B82F6',
    };
    editingId.value = null;
};

const confirmDelete = async (label) => {
    if (!confirm(`Delete label "${label.name}"? This will remove it from all tasks.`)) {
        return;
    }

    loading.value = true;
    error.value = null;

    try {
        await labelsStore.deleteLabel(projectId.value, label.id);
    } catch (err) {
        error.value = err.response?.data?.message || 'Failed to delete label';
    } finally {
        loading.value = false;
    }
};

// Lifecycle
onMounted(async () => {
    if (projectId.value) {
        try {
            await labelsStore.fetchLabels(projectId.value);
        } catch (err) {
            error.value = 'Failed to load labels';
        }
    }
});
</script>

<style scoped>
.label-manager {
    padding: 20px;
}

.label-manager-header {
    margin-bottom: 24px;
}

.label-manager-header h3 {
    font-size: 20px;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0 0 4px 0;
}

.header-subtitle {
    font-size: 14px;
    color: var(--text-secondary);
    margin: 0;
}

/* Label Form */
.label-form {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 32px;
}

.form-row {
    display: flex;
    gap: 16px;
    margin-bottom: 16px;
}

.form-group {
    margin-bottom: 16px;
}

.form-group:last-child {
    margin-bottom: 0;
}

.form-group label {
    display: block;
    font-size: 14px;
    font-weight: 500;
    color: var(--text-primary);
    margin-bottom: 8px;
}

.form-input {
    width: 100%;
    padding: 8px 12px;
    background: rgba(0, 0, 0, 0.3);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 6px;
    color: var(--text-primary);
    font-size: 14px;
    transition: all 0.2s;
}

.form-input:focus {
    outline: none;
    border-color: var(--primary-color);
    background: rgba(0, 0, 0, 0.4);
}

/* Color Palette */
.color-palette {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(40px, 1fr));
    gap: 8px;
    max-width: 520px;
}

.color-swatch {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    cursor: pointer;
    border: 2px solid transparent;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.color-swatch:hover {
    transform: scale(1.1);
    border-color: rgba(255, 255, 255, 0.3);
}

.color-swatch.selected {
    border-color: white;
    box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.2);
}

/* Form Actions */
.form-actions {
    display: flex;
    gap: 12px;
    margin-top: 16px;
}

.btn {
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    border: none;
}

.btn-primary {
    background: var(--primary-color);
    color: white;
}

.btn-primary:hover:not(:disabled) {
    background: var(--primary-hover);
}

.btn-primary:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.btn-secondary {
    background: rgba(255, 255, 255, 0.1);
    color: var(--text-primary);
}

.btn-secondary:hover {
    background: rgba(255, 255, 255, 0.15);
}

/* Labels List */
.labels-list {
    margin-top: 32px;
}

.labels-list h4 {
    font-size: 16px;
    font-weight: 600;
    color: var(--text-primary);
    margin: 0 0 16px 0;
}

.labels-grid {
    display: grid;
    gap: 8px;
}

.label-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    transition: all 0.2s;
}

.label-item:hover {
    background: rgba(255, 255, 255, 0.08);
    border-color: rgba(255, 255, 255, 0.2);
}

.label-preview {
    display: flex;
    align-items: center;
    gap: 12px;
}

.label-color {
    width: 24px;
    height: 24px;
    border-radius: 4px;
    flex-shrink: 0;
}

.label-name {
    font-size: 14px;
    font-weight: 500;
    color: var(--text-primary);
}

.label-actions {
    display: flex;
    gap: 8px;
}

.action-btn {
    padding: 6px;
    background: rgba(255, 255, 255, 0.1);
    border: none;
    border-radius: 4px;
    color: var(--text-secondary);
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.action-btn:hover {
    background: rgba(255, 255, 255, 0.15);
    color: var(--text-primary);
}

.delete-btn:hover {
    background: rgba(239, 68, 68, 0.2);
    color: #EF4444;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: var(--text-secondary);
}

/* Error Banner */
.error-banner {
    margin-top: 16px;
    padding: 12px 16px;
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.3);
    border-radius: 6px;
    color: #EF4444;
    font-size: 14px;
}
</style>
