<template>
    <Modal
        :is-open="isOpen"
        :title="isArchiving ? 'Archive Project' : 'Unarchive Project'"
        size="sm"
        :persistent="true"
        :show-close="false"
        :close-on-click-outside="false"
        :close-on-esc="false"
    >
        <!-- Archive Warning Message -->
        <div v-if="isArchiving" class="archive-warning">
            <div class="warning-icon">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                </svg>
            </div>
            <div>
                <p class="warning-title">Archive this project?</p>
                <p class="warning-message">
                    Archiving "<strong>{{ project?.title }}</strong>" will:
                </p>
                <ul class="warning-list">
                    <li>Move it to the Archived tab</li>
                    <li>Make the project read-only (kanban board cannot be edited)</li>
                    <li>Exclude it from dashboard statistics</li>
                    <li>Keep all data intact (can be unarchived anytime)</li>
                </ul>
            </div>
        </div>

        <!-- Unarchive Info Message -->
        <div v-else class="unarchive-info">
            <div class="info-icon">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="info-title">Unarchive this project?</p>
                <p class="info-message">
                    Unarchiving "<strong>{{ project?.title }}</strong>" will:
                </p>
                <ul class="info-list">
                    <li>Move it back to Active projects</li>
                    <li>Restore full editing capabilities</li>
                    <li>Include it in dashboard statistics again</li>
                </ul>
            </div>
        </div>

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
                :class="isArchiving ? 'btn btn-warning' : 'btn btn-primary'"
                @click="handleConfirm"
                :disabled="loading"
            >
                <span v-if="loading" class="spinner"></span>
                <span v-else>{{ isArchiving ? 'Archive Project' : 'Unarchive Project' }}</span>
            </button>
        </template>
    </Modal>
</template>

<script setup>
import { computed } from 'vue';
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
    isArchiving: {
        type: Boolean,
        default: true, // true = archiving, false = unarchiving
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
.archive-warning,
.unarchive-info {
    display: flex;
    gap: 1rem;
    padding: 1rem;
    border-radius: 0.5rem;
    margin-bottom: 1.5rem;
}

.archive-warning {
    background: rgba(245, 158, 11, 0.1);
    border: 1px solid rgba(245, 158, 11, 0.3);
}

.unarchive-info {
    background: rgba(34, 197, 94, 0.1);
    border: 1px solid rgba(34, 197, 94, 0.3);
}

.warning-icon {
    flex-shrink: 0;
    width: 2rem;
    height: 2rem;
    color: #f59e0b;
}

.info-icon {
    flex-shrink: 0;
    width: 2rem;
    height: 2rem;
    color: #22c55e;
}

.icon {
    width: 100%;
    height: 100%;
}

.warning-title,
.info-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: #f8fafc;
    margin: 0 0 0.5rem 0;
}

.warning-message,
.info-message {
    color: #cbd5e1;
    font-size: 0.875rem;
    line-height: 1.5;
    margin: 0 0 0.5rem 0;
}

.warning-message strong,
.info-message strong {
    color: #f8fafc;
}

.warning-list,
.info-list {
    margin: 0;
    padding-left: 1.5rem;
    color: #cbd5e1;
    font-size: 0.875rem;
}

.warning-list li,
.info-list li {
    margin: 0.25rem 0;
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

.btn-warning {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.btn-warning:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 6px 8px rgba(245, 158, 11, 0.3);
}

.btn-warning:active:not(:disabled) {
    transform: translateY(0);
}

.btn-primary {
    background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
    color: white;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.btn-primary:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 6px 8px rgba(34, 197, 94, 0.3);
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
</style>
