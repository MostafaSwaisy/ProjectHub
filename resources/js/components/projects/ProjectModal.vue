<template>
    <Modal
        :is-open="isOpen"
        :title="isEditMode ? 'Edit Project' : 'Create New Project'"
        size="md"
        :close-on-click-outside="!hasUnsavedChanges"
        @close="handleClose"
    >
        <!-- Form Content -->
        <form @submit.prevent="handleSubmit" class="project-form">
            <!-- Title Field -->
            <div class="form-group">
                <label for="project-title" class="form-label">
                    Project Title <span class="required">*</span>
                </label>
                <input
                    id="project-title"
                    v-model="formData.title"
                    type="text"
                    placeholder="Enter project title"
                    maxlength="100"
                    required
                    class="form-input"
                    :class="{ 'input-error': errors.title }"
                />
                <div class="input-meta">
                    <span v-if="errors.title" class="error-message">{{ errors.title }}</span>
                    <span class="char-count">{{ formData.title.length }}/100</span>
                </div>
            </div>

            <!-- Description Field -->
            <div class="form-group">
                <label for="project-description" class="form-label">
                    Description
                </label>
                <textarea
                    id="project-description"
                    v-model="formData.description"
                    placeholder="Add a description for your project (optional)"
                    maxlength="500"
                    rows="4"
                    class="form-input"
                    :class="{ 'input-error': errors.description }"
                ></textarea>
                <div class="input-meta">
                    <span v-if="errors.description" class="error-message">{{ errors.description }}</span>
                    <span class="char-count">{{ formData.description?.length || 0 }}/500</span>
                </div>
            </div>

            <!-- Status Fields Row -->
            <div class="form-row">
                <!-- Timeline Status -->
                <div class="form-group">
                    <label for="timeline-status" class="form-label">
                        Timeline Status
                    </label>
                    <select
                        id="timeline-status"
                        v-model="formData.timeline_status"
                        class="form-select"
                    >
                        <option value="On Track">On Track</option>
                        <option value="At Risk">At Risk</option>
                        <option value="Ahead">Ahead</option>
                    </select>
                </div>

                <!-- Budget Status -->
                <div class="form-group">
                    <label for="budget-status" class="form-label">
                        Budget Status
                    </label>
                    <select
                        id="budget-status"
                        v-model="formData.budget_status"
                        class="form-select"
                    >
                        <option value="Within Budget">Within Budget</option>
                        <option value="Over Budget">Over Budget</option>
                    </select>
                </div>
            </div>
        </form>

        <!-- Footer Actions -->
        <template #footer>
            <button
                type="button"
                class="btn btn-secondary"
                @click="handleClose"
                :disabled="loading"
            >
                Cancel
            </button>
            <button
                type="submit"
                class="btn btn-primary"
                @click="handleSubmit"
                :disabled="loading || !isFormValid"
            >
                <span v-if="loading" class="spinner"></span>
                <span v-else>{{ isEditMode ? 'Save Changes' : 'Create Project' }}</span>
            </button>
        </template>
    </Modal>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import Modal from '../shared/Modal.vue';

const props = defineProps({
    isOpen: {
        type: Boolean,
        default: false,
    },
    project: {
        type: Object,
        default: null,
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close', 'submit']);

// Form state
const formData = ref({
    title: '',
    description: '',
    timeline_status: 'On Track',
    budget_status: 'Within Budget',
});

const errors = ref({});
const initialFormData = ref(null);

// Check if in edit mode
const isEditMode = computed(() => !!props.project);

// Check if form has unsaved changes
const hasUnsavedChanges = computed(() => {
    if (!initialFormData.value) return false;
    return JSON.stringify(formData.value) !== JSON.stringify(initialFormData.value);
});

// Validate form
const isFormValid = computed(() => {
    return formData.value.title.trim().length > 0 &&
           formData.value.title.length <= 100 &&
           (!formData.value.description || formData.value.description.length <= 500);
});

// Watch for project prop changes (for edit mode)
watch(() => props.project, (newProject) => {
    if (newProject) {
        formData.value = {
            title: newProject.title || '',
            description: newProject.description || '',
            timeline_status: newProject.timeline_status || 'On Track',
            budget_status: newProject.budget_status || 'Within Budget',
        };
        initialFormData.value = { ...formData.value };
    }
}, { immediate: true });

// Watch for modal open/close
watch(() => props.isOpen, (isOpen) => {
    if (isOpen && !props.project) {
        // Reset form for create mode
        formData.value = {
            title: '',
            description: '',
            timeline_status: 'On Track',
            budget_status: 'Within Budget',
        };
        initialFormData.value = { ...formData.value };
        errors.value = {};
    }
});

// Validate field
const validateField = (field) => {
    errors.value[field] = null;

    if (field === 'title') {
        if (!formData.value.title.trim()) {
            errors.value.title = 'Title is required';
        } else if (formData.value.title.length > 100) {
            errors.value.title = 'Title must not exceed 100 characters';
        }
    }

    if (field === 'description') {
        if (formData.value.description && formData.value.description.length > 500) {
            errors.value.description = 'Description must not exceed 500 characters';
        }
    }
};

// Handle form submission
const handleSubmit = () => {
    // Validate all fields
    validateField('title');
    validateField('description');

    // Check if there are any errors
    const hasErrors = Object.values(errors.value).some(error => error !== null);
    if (hasErrors || !isFormValid.value) {
        return;
    }

    // Emit submit event with form data
    emit('submit', { ...formData.value });
};

// Handle modal close
const handleClose = () => {
    if (hasUnsavedChanges.value) {
        // T028: Will implement unsaved changes confirmation dialog
        const confirmed = confirm('You have unsaved changes. Are you sure you want to close?');
        if (!confirmed) return;
    }

    errors.value = {};
    emit('close');
};
</script>

<style scoped>
.project-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-label {
    font-size: 0.875rem;
    font-weight: 500;
    color: #f8fafc;
}

.required {
    color: #ef4444;
}

.form-input,
.form-select {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(148, 163, 184, 0.2);
    border-radius: 0.5rem;
    padding: 0.75rem 1rem;
    color: #f8fafc;
    font-size: 0.875rem;
    transition: all 0.2s;
}

.form-input:focus,
.form-select:focus {
    outline: none;
    border-color: #667eea;
    background: rgba(255, 255, 255, 0.08);
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-input::placeholder {
    color: #64748b;
}

.input-error {
    border-color: #ef4444;
}

.input-error:focus {
    border-color: #ef4444;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

.input-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    min-height: 1.25rem;
}

.error-message {
    color: #ef4444;
    font-size: 0.75rem;
}

.char-count {
    color: #64748b;
    font-size: 0.75rem;
    margin-left: auto;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.btn {
    padding: 0.625rem 1.25rem;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    border: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.btn-secondary {
    background: rgba(255, 255, 255, 0.05);
    color: #cbd5e1;
    border: 1px solid rgba(148, 163, 184, 0.2);
}

.btn-secondary:hover:not(:disabled) {
    background: rgba(255, 255, 255, 0.1);
    border-color: rgba(148, 163, 184, 0.4);
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.btn-primary:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
}

.btn-primary:active:not(:disabled) {
    transform: translateY(0);
}

.spinner {
    width: 1rem;
    height: 1rem;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

@media (max-width: 640px) {
    .form-row {
        grid-template-columns: 1fr;
    }
}
</style>
