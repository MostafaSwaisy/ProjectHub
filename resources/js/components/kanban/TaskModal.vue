<template>
    <!-- T053: Task Modal for create/edit with glasmorphic styling -->
    <div class="modal-backdrop" @click.self="$emit('close')">
        <div class="modal-content">
            <div class="modal-header">
                <h2>{{ isEditing ? 'Edit Task' : 'Create Task' }}</h2>
                <button class="close-btn" @click="$emit('close')">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <form @submit.prevent="submitForm" class="task-form">
                <!-- Title Field -->
                <div class="form-group">
                    <label for="title">Task Title *</label>
                    <input
                        id="title"
                        v-model="formData.title"
                        type="text"
                        placeholder="Enter task title"
                        required
                        class="form-input"
                    />
                    <span v-if="errors.title" class="error-message">{{ errors.title }}</span>
                </div>

                <!-- Description Field -->
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea
                        id="description"
                        v-model="formData.description"
                        placeholder="Add task description (optional)"
                        rows="3"
                        class="form-input"
                    ></textarea>
                </div>

                <!-- Priority Field -->
                <div class="form-group">
                    <label for="priority">Priority</label>
                    <select v-model="formData.priority" id="priority" class="form-input">
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>

                <!-- Status Field -->
                <div class="form-group">
                    <label for="status">Status</label>
                    <select v-model="formData.status" id="status" class="form-input">
                        <option value="todo">To Do</option>
                        <option value="in_progress">In Progress</option>
                        <option value="in_review">In Review</option>
                        <option value="done">Done</option>
                    </select>
                </div>

                <!-- Due Date Field -->
                <div class="form-group">
                    <label for="due_date">Due Date</label>
                    <input
                        id="due_date"
                        v-model="formData.due_date"
                        type="date"
                        class="form-input"
                    />
                </div>

                <!-- Labels Field -->
                <div class="form-group">
                    <label>Labels</label>
                    <LabelSelector
                        v-model="formData.labels"
                        :available-labels="availableLabels"
                    />
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" @click="$emit('close')">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        {{ isEditing ? 'Update Task' : 'Create Task' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useTasksStore } from '../../stores/tasks';
import LabelSelector from './LabelSelector.vue';

const props = defineProps({
    task: {
        type: Object,
        default: null,
    },
    projectId: {
        type: [String, Number],
        required: true,
    },
});

const emit = defineEmits(['save', 'close']);

const tasksStore = useTasksStore();

// Form state
const formData = ref({
    title: '',
    description: '',
    priority: 'medium',
    status: 'todo',
    due_date: '',
    labels: [],
});

const errors = ref({});
const availableLabels = ref([
    { id: 1, name: 'Bug', color: '#EF4444' },
    { id: 2, name: 'Feature', color: '#FF6B35' },
    { id: 3, name: 'Enhancement', color: '#4F46E5' },
    { id: 4, name: 'Documentation', color: '#22C55E' },
    { id: 5, name: 'Urgent', color: '#EC4899' },
]);

// Computed: Check if editing
const isEditing = computed(() => props.task !== null);

// Lifecycle
onMounted(() => {
    if (isEditing.value && props.task) {
        // Load task data for editing
        formData.value = {
            title: props.task.title || '',
            description: props.task.description || '',
            priority: props.task.priority || 'medium',
            status: props.task.status || 'todo',
            due_date: props.task.due_date || '',
            labels: (props.task.labels || []).map(l => l.id),
        };
    }
});

// Methods
const validateForm = () => {
    errors.value = {};

    if (!formData.value.title || formData.value.title.trim() === '') {
        errors.value.title = 'Title is required';
    }

    if (formData.value.title && formData.value.title.length > 200) {
        errors.value.title = 'Title must be less than 200 characters';
    }

    return Object.keys(errors.value).length === 0;
};

const submitForm = async () => {
    if (!validateForm()) return;

    try {
        if (isEditing.value) {
            // Update existing task
            await tasksStore.updateTask(props.projectId, props.task.id, formData.value);
        } else {
            // Create new task
            await tasksStore.createTask(props.projectId, formData.value);
        }

        emit('save');
    } catch (error) {
        errors.value.submit = error.message;
        console.error('Failed to save task:', error);
    }
};
</script>

<style scoped>
/* T053: Task Modal Styling */

.modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    animation: backdropFadeIn var(--transition-normal) ease-out;
}

.modal-content {
    background: rgba(10, 10, 10, 0.95);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--radius-lg);
    padding: var(--spacing-2xl);
    max-width: 500px;
    width: 90vw;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: var(--shadow-lg);
    animation: modalSlideIn var(--transition-normal) ease-out;
}

/* Modal Header */
.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-2xl);
}

.modal-header h2 {
    font-size: var(--text-2xl);
    font-weight: var(--font-bold);
    color: var(--text-primary);
    margin: 0;
}

.close-btn {
    background: none;
    border: none;
    color: var(--text-secondary);
    cursor: pointer;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all var(--transition-normal);
}

.close-btn:hover {
    color: var(--text-primary);
    transform: rotate(90deg);
}

/* Form */
.task-form {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-lg);
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-sm);
}

.form-group label {
    font-size: var(--text-sm);
    font-weight: var(--font-medium);
    color: var(--text-primary);
}

.form-input {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--radius-md);
    padding: 0.625rem 0.75rem;
    color: var(--text-primary);
    font-family: inherit;
    font-size: var(--text-sm);
    transition: all var(--transition-normal);
}

.form-input:focus {
    outline: none;
    background: rgba(255, 255, 255, 0.08);
    border-color: rgba(255, 107, 53, 0.5);
    box-shadow: 0 0 0 2px rgba(255, 107, 53, 0.1);
}

.form-input::placeholder {
    color: var(--text-secondary);
}

textarea.form-input {
    resize: vertical;
    min-height: 80px;
}

/* Error Message */
.error-message {
    font-size: var(--text-xs);
    color: var(--red-primary);
    margin-top: -8px;
}

/* Form Actions */
.form-actions {
    display: flex;
    gap: var(--spacing-md);
    margin-top: var(--spacing-lg);
}

.btn {
    flex: 1;
    padding: 0.625rem 1rem;
    border: none;
    border-radius: var(--radius-md);
    font-weight: var(--font-medium);
    font-size: var(--text-sm);
    cursor: pointer;
    transition: all var(--transition-normal);
}

.btn-primary {
    background: var(--orange-primary);
    color: white;
}

.btn-primary:hover {
    background: var(--orange-light);
    box-shadow: var(--shadow-orange);
}

.btn-secondary {
    background: rgba(255, 255, 255, 0.1);
    color: var(--text-primary);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.btn-secondary:hover {
    background: rgba(255, 255, 255, 0.15);
}

/* Mobile Responsive */
@media (max-width: 639px) {
    .modal-content {
        padding: var(--spacing-lg);
        max-width: 100%;
        width: 95vw;
        max-height: 95vh;
    }

    .modal-header h2 {
        font-size: var(--text-xl);
    }

    .form-input {
        font-size: 16px; /* Prevents zoom on iOS */
    }
}
</style>
