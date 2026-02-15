<template>
    <Modal
        :is-open="isOpen"
        :title="title"
        size="sm"
        :close-on-click-outside="false"
        :close-on-esc="false"
        :persistent="true"
        :show-close="false"
    >
        <!-- Message -->
        <p class="confirm-message">{{ message }}</p>

        <!-- Footer Actions -->
        <template #footer>
            <button
                type="button"
                class="btn btn-secondary"
                @click="handleCancel"
                :disabled="loading"
            >
                {{ cancelText }}
            </button>
            <button
                type="button"
                :class="['btn', danger ? 'btn-danger' : 'btn-primary']"
                @click="handleConfirm"
                :disabled="loading"
            >
                <span v-if="loading" class="spinner"></span>
                <span v-else>{{ confirmText }}</span>
            </button>
        </template>
    </Modal>
</template>

<script setup>
import Modal from './Modal.vue';

const props = defineProps({
    isOpen: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: 'Confirm Action',
    },
    message: {
        type: String,
        required: true,
    },
    confirmText: {
        type: String,
        default: 'Confirm',
    },
    cancelText: {
        type: String,
        default: 'Cancel',
    },
    danger: {
        type: Boolean,
        default: false,
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['confirm', 'cancel', 'close']);

const handleConfirm = () => {
    emit('confirm');
};

const handleCancel = () => {
    emit('cancel');
    emit('close');
};
</script>

<style scoped>
.confirm-message {
    color: #cbd5e1;
    font-size: 0.875rem;
    line-height: 1.6;
    margin: 0;
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
