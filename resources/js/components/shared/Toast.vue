<template>
    <Teleport to="body">
        <div class="toast-container">
            <TransitionGroup name="toast">
                <div
                    v-for="toast in toasts"
                    :key="toast.id"
                    :class="['toast', `toast-${toast.type}`]"
                    @click="removeToast(toast.id)"
                >
                    <!-- Icon -->
                    <div class="toast-icon">
                        <svg v-if="toast.type === 'success'" class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <svg v-else-if="toast.type === 'error'" class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <svg v-else-if="toast.type === 'warning'" class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <svg v-else class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>

                    <!-- Content -->
                    <div class="toast-content">
                        <p class="toast-title" v-if="toast.title">{{ toast.title }}</p>
                        <p class="toast-message">{{ toast.message }}</p>
                    </div>

                    <!-- Close Button -->
                    <button class="toast-close" @click.stop="removeToast(toast.id)">
                        <svg class="icon-small" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>

<script setup>
import { toastState, removeToast } from '../../composables/useToast';

const { toasts } = toastState;
</script>

<style scoped>
.toast-container {
    position: fixed;
    top: 1rem;
    right: 1rem;
    z-index: 9999;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    pointer-events: none;
}

.toast {
    pointer-events: auto;
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    min-width: 300px;
    max-width: 400px;
    padding: 1rem;
    background: rgba(21, 21, 21, 0.95);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 0.75rem;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
    cursor: pointer;
    transition: all 0.2s;
}

.toast:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.4);
}

.toast-success {
    border-left: 3px solid #22c55e;
}

.toast-error {
    border-left: 3px solid #ef4444;
}

.toast-warning {
    border-left: 3px solid #fb923c;
}

.toast-info {
    border-left: 3px solid #3b82f6;
}

.toast-icon {
    flex-shrink: 0;
    width: 1.5rem;
    height: 1.5rem;
}

.toast-success .toast-icon {
    color: #22c55e;
}

.toast-error .toast-icon {
    color: #ef4444;
}

.toast-warning .toast-icon {
    color: #fb923c;
}

.toast-info .toast-icon {
    color: #3b82f6;
}

.icon {
    width: 100%;
    height: 100%;
}

.toast-content {
    flex: 1;
    min-width: 0;
}

.toast-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: #f8fafc;
    margin: 0 0 0.25rem 0;
}

.toast-message {
    font-size: 0.875rem;
    color: #cbd5e1;
    margin: 0;
    line-height: 1.4;
}

.toast-close {
    flex-shrink: 0;
    background: none;
    border: none;
    padding: 0;
    width: 1.25rem;
    height: 1.25rem;
    color: #64748b;
    cursor: pointer;
    transition: color 0.2s;
}

.toast-close:hover {
    color: #f8fafc;
}

.icon-small {
    width: 100%;
    height: 100%;
}

/* Toast animations */
.toast-enter-active,
.toast-leave-active {
    transition: all 0.3s ease;
}

.toast-enter-from {
    opacity: 0;
    transform: translateX(100%);
}

.toast-leave-to {
    opacity: 0;
    transform: translateX(50%);
}

.toast-move {
    transition: transform 0.3s ease;
}

/* Mobile styles */
@media (max-width: 640px) {
    .toast-container {
        top: auto;
        bottom: 1rem;
        left: 1rem;
        right: 1rem;
    }

    .toast {
        min-width: auto;
        max-width: none;
    }
}
</style>
