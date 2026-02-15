import { reactive } from 'vue';

// Global toast state
export const toastState = reactive({
    toasts: [],
    nextId: 1,
});

// Add a toast
export function addToast({ type = 'info', title = '', message, duration = 5000 }) {
    const id = toastState.nextId++;

    const toast = {
        id,
        type,
        title,
        message,
    };

    toastState.toasts.push(toast);

    // Auto remove after duration
    if (duration > 0) {
        setTimeout(() => {
            removeToast(id);
        }, duration);
    }

    return id;
}

// Remove a toast
export function removeToast(id) {
    const index = toastState.toasts.findIndex(t => t.id === id);
    if (index > -1) {
        toastState.toasts.splice(index, 1);
    }
}

// Composable hook
export function useToast() {
    const success = (message, title = '') => {
        return addToast({ type: 'success', title, message });
    };

    const error = (message, title = '') => {
        return addToast({ type: 'error', title, message, duration: 7000 });
    };

    const warning = (message, title = '') => {
        return addToast({ type: 'warning', title, message });
    };

    const info = (message, title = '') => {
        return addToast({ type: 'info', title, message });
    };

    return {
        success,
        error,
        warning,
        info,
        addToast,
        removeToast,
    };
}
