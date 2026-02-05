<template>
    <Modal
        :is-open="isOpen"
        title="Duplicate Project"
        size="md"
        @close="handleClose"
    >
        <form @submit.prevent="handleSubmit" class="duplicate-form">
            <!-- Original Project Info -->
            <div class="info-box">
                <svg class="icon-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <p class="info-text">
                        Duplicating: <strong>{{ project?.title }}</strong>
                    </p>
                    <p class="info-subtext">
                        You will become the owner of the duplicated project.
                    </p>
                </div>
            </div>

            <!-- New Project Title -->
            <div class="form-group">
                <label for="duplicate-title" class="form-label">
                    New Project Title <span class="required">*</span>
                </label>
                <input
                    id="duplicate-title"
                    v-model="formData.title"
                    type="text"
                    placeholder="Enter title for duplicated project"
                    maxlength="100"
                    required
                    class="form-input"
                    :class="{ 'input-error': errors.title }"
                    @blur="validateField('title')"
                    @input="errors.title && validateField('title')"
                />
                <div class="input-meta">
                    <span v-if="errors.title" class="error-message">{{ errors.title }}</span>
                    <span class="char-count">{{ formData.title.length }}/100</span>
                </div>
            </div>

            <!-- Duplication Options -->
            <div class="form-group">
                <label class="form-label">Duplication Options</label>

                <div class="option-card">
                    <div class="option-header">
                        <input
                            id="include-structure"
                            type="radio"
                            :value="false"
                            v-model="formData.include_tasks"
                            class="option-radio"
                        />
                        <label for="include-structure" class="option-label">
                            <span class="option-title">Structure Only</span>
                            <span class="option-description">
                                Copy boards and columns without tasks
                            </span>
                        </label>
                    </div>
                </div>

                <div class="option-card">
                    <div class="option-header">
                        <input
                            id="include-tasks"
                            type="radio"
                            :value="true"
                            v-model="formData.include_tasks"
                            class="option-radio"
                        />
                        <label for="include-tasks" class="option-label">
                            <span class="option-title">Structure + Tasks</span>
                            <span class="option-description">
                                Copy boards, columns, and all tasks (without assignees)
                            </span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Warning Notice -->
            <div class="warning-box">
                <svg class="icon-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <p class="warning-text">
                    Team members will <strong>not</strong> be copied to the duplicated project.
                </p>
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
                <span v-else>
                    <svg class="icon-btn" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                    Duplicate Project
                </span>
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

const emit = defineEmits(['close', 'duplicate']);

// Form state
const formData = ref({
    title: '',
    include_tasks: false,
});

const errors = ref({});

// Computed
const isFormValid = computed(() => {
    return formData.value.title.trim().length > 0 &&
           formData.value.title.length <= 100;
});

// Watch for modal open
watch(() => props.isOpen, (isOpen) => {
    if (isOpen && props.project) {
        // Set default title as "Copy of {original title}"
        const defaultTitle = `Copy of ${props.project.title}`;
        formData.value.title = defaultTitle.substring(0, 100); // Ensure max length
        formData.value.include_tasks = false;
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
};

// Handle form submission
const handleSubmit = () => {
    // Validate
    validateField('title');

    // Check if there are any errors
    const hasErrors = Object.values(errors.value).some(error => error !== null);
    if (hasErrors || !isFormValid.value) {
        return;
    }

    // Emit duplicate event with form data
    emit('duplicate', {
        title: formData.value.title,
        include_tasks: formData.value.include_tasks,
    });
};

// Handle modal close
const handleClose = () => {
    errors.value = {};
    emit('close');
};
</script>

<style scoped>
.duplicate-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.info-box {
    display: flex;
    gap: 1rem;
    padding: 1rem;
    background: rgba(102, 126, 234, 0.1);
    border: 1px solid rgba(102, 126, 234, 0.3);
    border-radius: 0.5rem;
}

.icon-info {
    width: 1.5rem;
    height: 1.5rem;
    color: #667eea;
    flex-shrink: 0;
}

.info-text {
    font-size: 0.875rem;
    color: #cbd5e1;
    margin: 0 0 0.25rem 0;
}

.info-text strong {
    color: #f8fafc;
}

.info-subtext {
    font-size: 0.75rem;
    color: #94a3b8;
    margin: 0;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.form-label {
    font-size: 0.875rem;
    font-weight: 500;
    color: #f8fafc;
}

.required {
    color: #ef4444;
}

.form-input {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(148, 163, 184, 0.2);
    border-radius: 0.5rem;
    color: #f8fafc;
    padding: 0.75rem 1rem;
    font-size: 0.875rem;
    transition: all 0.2s;
}

.form-input:hover {
    background: rgba(255, 255, 255, 0.08);
    border-color: rgba(148, 163, 184, 0.4);
}

.form-input:focus {
    outline: none;
    background: rgba(255, 255, 255, 0.08);
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.input-error {
    border-color: #ef4444;
}

.input-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.75rem;
}

.error-message {
    color: #ef4444;
}

.char-count {
    color: #64748b;
}

.option-card {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(148, 163, 184, 0.2);
    border-radius: 0.5rem;
    padding: 1rem;
    transition: all 0.2s;
    cursor: pointer;
}

.option-card:hover {
    background: rgba(255, 255, 255, 0.05);
    border-color: rgba(148, 163, 184, 0.4);
}

.option-card:has(input:checked) {
    background: rgba(102, 126, 234, 0.1);
    border-color: #667eea;
}

.option-header {
    display: flex;
    gap: 0.75rem;
    align-items: flex-start;
}

.option-radio {
    margin-top: 0.25rem;
    cursor: pointer;
}

.option-label {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    cursor: pointer;
    flex: 1;
}

.option-title {
    font-size: 0.875rem;
    font-weight: 500;
    color: #f8fafc;
}

.option-description {
    font-size: 0.75rem;
    color: #94a3b8;
}

.warning-box {
    display: flex;
    gap: 0.75rem;
    padding: 1rem;
    background: rgba(245, 158, 11, 0.1);
    border: 1px solid rgba(245, 158, 11, 0.3);
    border-radius: 0.5rem;
}

.icon-warning {
    width: 1.25rem;
    height: 1.25rem;
    color: #f59e0b;
    flex-shrink: 0;
}

.warning-text {
    font-size: 0.75rem;
    color: #fbbf24;
    margin: 0;
}

.warning-text strong {
    font-weight: 600;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.625rem 1.25rem;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    border: none;
}

.btn:disabled {
    opacity: 0.6;
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

.icon-btn {
    width: 1rem;
    height: 1rem;
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
</style>
