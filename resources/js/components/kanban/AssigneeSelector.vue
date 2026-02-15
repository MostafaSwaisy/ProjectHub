<template>
    <!-- T118-T120: Assignee Selector Dropdown Component -->
    <div class="assignee-selector">
        <select
            :value="modelValue"
            @change="handleChange"
            class="assignee-select"
        >
            <!-- T120: Unassigned Option -->
            <option :value="null">Unassigned</option>

            <!-- T119: Project Members -->
            <option
                v-for="member in members"
                :key="member.id"
                :value="member.id"
            >
                {{ member.name }}
            </option>
        </select>

        <!-- Avatar Preview -->
        <div v-if="selectedMember" class="assignee-preview">
            <div class="assignee-avatar">
                {{ getInitials(selectedMember.name) }}
            </div>
            <span class="assignee-name">{{ selectedMember.name }}</span>
        </div>
        <div v-else class="assignee-preview">
            <div class="assignee-avatar unassigned">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            </div>
            <span class="assignee-name">No assignee</span>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: {
        type: [Number, String, null],
        default: null,
    },
    members: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['update:modelValue']);

// Computed
const selectedMember = computed(() => {
    if (!props.modelValue) return null;
    return props.members.find(m => m.id === props.modelValue || m.id === parseInt(props.modelValue));
});

// Methods
const handleChange = (event) => {
    const value = event.target.value;
    // Convert to number or null
    const parsedValue = value === '' || value === 'null' ? null : parseInt(value);
    emit('update:modelValue', parsedValue);
};

const getInitials = (name) => {
    if (!name) return '?';
    return name
        .split(' ')
        .map(word => word[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
};
</script>

<style scoped>
.assignee-selector {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.assignee-select {
    width: 100%;
    padding: 10px 12px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 6px;
    color: var(--text-primary);
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s;
}

.assignee-select:hover {
    background: rgba(255, 255, 255, 0.08);
    border-color: rgba(255, 255, 255, 0.2);
}

.assignee-select:focus {
    outline: none;
    border-color: var(--primary-color);
    background: rgba(255, 255, 255, 0.08);
}

.assignee-select option {
    background: #1a1a24;
    color: var(--text-primary);
}

.assignee-preview {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 12px;
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.05);
    border-radius: 6px;
}

.assignee-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 12px;
    font-weight: 600;
    flex-shrink: 0;
}

.assignee-avatar.unassigned {
    background: rgba(255, 255, 255, 0.1);
    color: var(--text-secondary);
}

.assignee-name {
    font-size: 14px;
    color: var(--text-primary);
    font-weight: 500;
}
</style>
