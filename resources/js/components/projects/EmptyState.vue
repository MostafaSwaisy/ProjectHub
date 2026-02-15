<template>
    <div class="empty-state">
        <div class="empty-content">
            <!-- Icon -->
            <div class="icon-container">
                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>

            <!-- Message -->
            <h3 class="title">{{ title }}</h3>
            <p class="description">{{ description }}</p>

            <!-- Action Button -->
            <button
                v-if="showAction"
                class="action-button"
                @click="$emit('create')"
            >
                <svg class="plus-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Create Your First Project
            </button>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    title: {
        type: String,
        default: 'No projects found',
    },
    description: {
        type: String,
        default: 'Get started by creating your first project to organize your work and collaborate with your team.',
    },
    showAction: {
        type: Boolean,
        default: true,
    },
});

defineEmits(['create']);
</script>

<style scoped>
.empty-state {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 400px;
    padding: 3rem;
}

.empty-content {
    text-align: center;
    max-width: 28rem;
}

.icon-container {
    display: flex;
    justify-content: center;
    margin-bottom: 1.5rem;
}

.icon {
    width: 4rem;
    height: 4rem;
    color: #64748b;
}

.title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #f8fafc;
    margin: 0 0 0.5rem 0;
}

.description {
    font-size: 1rem;
    color: #94a3b8;
    line-height: 1.6;
    margin: 0 0 2rem 0;
}

.action-button {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 0.5rem;
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.action-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 12px rgba(0, 0, 0, 0.2);
}

.action-button:active {
    transform: translateY(0);
}

.plus-icon {
    width: 1.25rem;
    height: 1.25rem;
}
</style>
