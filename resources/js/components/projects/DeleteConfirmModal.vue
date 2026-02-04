<template>
    <Modal
        :is-open="isOpen"
        title="Delete Project"
        size="sm"
        :persistent="true"
        :show-close="false"
        :close-on-click-outside="false"
        :close-on-esc="false"
    >
        <!-- Warning Message -->
        <div class="delete-warning">
            <div class="warning-icon">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div>
                <p class="warning-title">This action cannot be undone</p>
                <p class="warning-message">
                    Deleting "<strong>{{ project?.title }}</strong>" will permanently remove:
                </p>
                <ul class="warning-list">
                    <li v-if="taskCount > 0">{{ taskCount }} task{{ taskCount !== 1 ? 's' : '' }}</li>
                    <li>All boards and columns</li>
                    <li>All comments and attachments</li>
                    <li>All project members and permissions</li>
                </ul>
            </div>
        </div>

        <!-- Name Typing Confirmation (for projects with tasks) -->
        <div v-if="requiresTyping" class="typing-confirmation">
            <label for="confirm-name" class="confirm-label">
                Type the project name to confirm:
            </label>
            <input
                id="confirm-name"
                v-model="confirmName"
                type="text"
                :placeholder="project?.title"
                class="confirm-input"
                :class="{ 'input-error': confirmName && !isNameMatching }"
                @input="validateName"
            />
            <p v-if="confirmName && !isNameMatching" class="error-message">
                Project name does not match
            </p>
        </div>

        <!-- Simple Confirmation (for projects without tasks) -->
        <p v-else class="simple-confirm-message">
            Are you sure you want to delete this project?
        </p>

        <!-- Footer Actions -->
        <template #footer>
            <button
                type="button"
                class="btn btn-secondary"
                @click="handleCancel"
                :disabled="loading"
            >
                Cancel
            </button>
            <button
                type="button"
                class="btn btn-danger"
                @click="handleConfirm"
                :disabled="loading || (requiresTyping && !isNameMatching)"
            >
                <span v-if="loading" class="spinner"></span>
                <span v-else>Delete Project</span>
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
    taskCount: {
        type: Number,
        default: 0,
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['confirm', 'cancel', 'close']);

const confirmName = ref('');

// Require typing confirmation if project has tasks
const requiresTyping = computed(() => props.taskCount > 0);

// Check if typed name matches project name
const isNameMatching = computed(() => {
    if (!props.project || !requiresTyping.value) return true;
    return confirmName.value.trim() === props.project.title.trim();
});

// Validate name input
const validateName = () => {
    // Validation happens via isNameMatching computed
};

// Reset form when modal opens/closes
watch(() => props.isOpen, (isOpen) => {
    if (isOpen) {
        confirmName.value = '';
    }
});

const handleConfirm = () => {
    if (requiresTyping.value && !isNameMatching.value) {
        return;
    }
    emit('confirm');
};

const handleCancel = () => {
    confirmName.value = '';
    emit('cancel');
    emit('close');
};
</script>

<style scoped>
.delete-warning {
    display: flex;
    gap: 1rem;
    padding: 1rem;
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.3);
    border-radius: 0.5rem;
    margin-bottom: 1.5rem;
}

.warning-icon {
    flex-shrink: 0;
    width: 2rem;
    height: 2rem;
    color: #ef4444;
}

.icon {
    width: 100%;
    height: 100%;
}

.warning-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: #f8fafc;
    margin: 0 0 0.5rem 0;
}

.warning-message {
    color: #cbd5e1;
    font-size: 0.875rem;
    line-height: 1.5;
    margin: 0 0 0.5rem 0;
}

.warning-message strong {
    color: #f8fafc;
}

.warning-list {
    margin: 0;
    padding-left: 1.5rem;
    color: #cbd5e1;
    font-size: 0.875rem;
}

.warning-list li {
    margin: 0.25rem 0;
}

.typing-confirmation {
    margin-bottom: 1rem;
}

.confirm-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    color: #f8fafc;
    margin-bottom: 0.5rem;
}

.confirm-input {
    width: 100%;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(148, 163, 184, 0.2);
    border-radius: 0.5rem;
    padding: 0.75rem 1rem;
    color: #f8fafc;
    font-size: 0.875rem;
    transition: all 0.2s;
}

.confirm-input:focus {
    outline: none;
    border-color: #ef4444;
    background: rgba(255, 255, 255, 0.08);
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

.confirm-input::placeholder {
    color: #64748b;
}

.input-error {
    border-color: #ef4444;
}

.input-error:focus {
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2);
}

.simple-confirm-message {
    color: #cbd5e1;
    font-size: 0.875rem;
    margin: 0 0 1rem 0;
}

.error-message {
    color: #ef4444;
    font-size: 0.75rem;
    margin-top: 0.25rem;
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

.btn-danger {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.btn-danger:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 6px 8px rgba(239, 68, 68, 0.3);
}

.btn-danger:active:not(:disabled) {
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
</style>
