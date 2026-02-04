<template>
    <Modal
        :is-open="isOpen"
        title="Concurrent Edit Conflict"
        size="md"
        :persistent="true"
        :show-close="false"
        :close-on-click-outside="false"
        :close-on-esc="false"
    >
        <!-- Warning Message -->
        <div class="conflict-warning">
            <div class="warning-icon">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <p class="warning-message">
                This project was modified by another user while you were editing.
                Your changes cannot be saved to prevent data loss.
            </p>
        </div>

        <!-- Current Server Data Preview -->
        <div v-if="currentData" class="server-data">
            <h4 class="section-title">Current Server Version:</h4>
            <div class="data-grid">
                <div class="data-field">
                    <span class="field-label">Title:</span>
                    <span class="field-value">{{ currentData.title }}</span>
                </div>
                <div class="data-field">
                    <span class="field-label">Description:</span>
                    <span class="field-value">{{ currentData.description || 'No description' }}</span>
                </div>
                <div class="data-field">
                    <span class="field-label">Timeline Status:</span>
                    <span class="field-value">{{ currentData.timeline_status }}</span>
                </div>
                <div class="data-field">
                    <span class="field-label">Budget Status:</span>
                    <span class="field-value">{{ currentData.budget_status }}</span>
                </div>
                <div class="data-field">
                    <span class="field-label">Last Modified:</span>
                    <span class="field-value">{{ formatDate(currentData.updated_at) }}</span>
                </div>
            </div>
        </div>

        <!-- Footer Actions -->
        <template #footer>
            <button
                type="button"
                class="btn btn-secondary"
                @click="handleDiscard"
            >
                Discard My Changes
            </button>
            <button
                type="button"
                class="btn btn-primary"
                @click="handleReload"
            >
                Reload Current Data
            </button>
        </template>
    </Modal>
</template>

<script setup>
import Modal from '../shared/Modal.vue';

const props = defineProps({
    isOpen: {
        type: Boolean,
        default: false,
    },
    currentData: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['reload', 'discard', 'close']);

const formatDate = (dateString) => {
    if (!dateString) return 'Unknown';
    const date = new Date(dateString);
    return date.toLocaleString();
};

const handleReload = () => {
    emit('reload');
    emit('close');
};

const handleDiscard = () => {
    emit('discard');
    emit('close');
};
</script>

<style scoped>
.conflict-warning {
    display: flex;
    gap: 1rem;
    padding: 1rem;
    background: rgba(251, 146, 60, 0.1);
    border: 1px solid rgba(251, 146, 60, 0.3);
    border-radius: 0.5rem;
    margin-bottom: 1.5rem;
}

.warning-icon {
    flex-shrink: 0;
    width: 2rem;
    height: 2rem;
    color: #fb923c;
}

.icon {
    width: 100%;
    height: 100%;
}

.warning-message {
    color: #f8fafc;
    font-size: 0.875rem;
    line-height: 1.5;
    margin: 0;
}

.server-data {
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid rgba(148, 163, 184, 0.2);
    border-radius: 0.5rem;
    padding: 1rem;
}

.section-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: #f8fafc;
    margin: 0 0 0.75rem 0;
}

.data-grid {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.data-field {
    display: flex;
    gap: 0.75rem;
}

.field-label {
    font-size: 0.75rem;
    color: #94a3b8;
    font-weight: 500;
    min-width: 100px;
}

.field-value {
    font-size: 0.75rem;
    color: #cbd5e1;
    flex: 1;
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

.btn-secondary {
    background: rgba(255, 255, 255, 0.05);
    color: #cbd5e1;
    border: 1px solid rgba(148, 163, 184, 0.2);
}

.btn-secondary:hover {
    background: rgba(255, 255, 255, 0.1);
    border-color: rgba(148, 163, 184, 0.4);
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
}

.btn-primary:active {
    transform: translateY(0);
}
</style>
