<template>
    <!-- T055: Label Selector Component with color picker -->
    <div class="label-selector">
        <div class="selected-labels">
            <div
                v-for="labelId in modelValue"
                :key="labelId"
                class="label-badge selected"
                :style="{ backgroundColor: getLabelColor(labelId) }"
            >
                {{ getLabelName(labelId) }}
                <button
                    @click.prevent="removeLabel(labelId)"
                    class="remove-btn"
                >
                    ×
                </button>
            </div>
        </div>

        <div class="label-dropdown">
            <button
                type="button"
                @click="toggleDropdown"
                class="dropdown-toggle"
            >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
                Add Label
            </button>

            <div v-if="showDropdown" class="dropdown-menu">
                <div
                    v-for="label in availableLabels"
                    :key="label.id"
                    @click="toggleLabel(label.id)"
                    class="label-option"
                    :class="{ selected: modelValue.includes(label.id) }"
                >
                    <div class="label-color" :style="{ backgroundColor: label.color }"></div>
                    <span class="label-name">{{ label.name }}</span>
                    <svg v-if="modelValue.includes(label.id)" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M9 16.2L4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4L9 16.2z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => [],
    },
    availableLabels: {
        type: Array,
        required: true,
    },
});

const emit = defineEmits(['update:modelValue']);

// Local state
const showDropdown = ref(false);

// Methods
const toggleLabel = (labelId) => {
    const newValue = props.modelValue.includes(labelId)
        ? props.modelValue.filter(id => id !== labelId)
        : [...props.modelValue, labelId];

    emit('update:modelValue', newValue);
};

const removeLabel = (labelId) => {
    emit('update:modelValue', props.modelValue.filter(id => id !== labelId));
};

const toggleDropdown = () => {
    showDropdown.value = !showDropdown.value;
};

const getLabelName = (labelId) => {
    const label = props.availableLabels.find(l => l.id === labelId);
    return label?.name || 'Unknown';
};

const getLabelColor = (labelId) => {
    const label = props.availableLabels.find(l => l.id === labelId);
    return label?.color || '#999';
};
</script>

<style scoped>
/* T055: Label Selector Styling */

.label-selector {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-sm);
}

.selected-labels {
    display: flex;
    flex-wrap: wrap;
    gap: var(--spacing-xs);
    min-height: 28px;
}

.label-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: var(--font-medium);
    color: white;
    animation: slideUp 0.2s ease-out;
}

.remove-btn {
    background: none;
    border: none;
    color: inherit;
    cursor: pointer;
    font-size: 16px;
    padding: 0;
    margin-left: 2px;
    opacity: 0.7;
    transition: opacity var(--transition-normal);
}

.remove-btn:hover {
    opacity: 1;
}

/* Dropdown */
.label-dropdown {
    position: relative;
}

.dropdown-toggle {
    display: flex;
    align-items: center;
    gap: var(--spacing-xs);
    width: 100%;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--radius-md);
    padding: 0.625rem 0.75rem;
    color: var(--text-primary);
    font-size: var(--text-sm);
    cursor: pointer;
    transition: all var(--transition-normal);
}

.dropdown-toggle:hover {
    background: rgba(255, 255, 255, 0.08);
    border-color: rgba(255, 255, 255, 0.2);
}

.dropdown-toggle svg {
    transition: transform var(--transition-normal);
}

.dropdown-menu {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: var(--black-primary);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--radius-md);
    margin-top: 4px;
    z-index: 100;
    box-shadow: var(--shadow-lg);
    animation: dropdownIn var(--transition-normal) ease-out;
    max-height: 250px;
    overflow-y: auto;
}

.label-option {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    padding: var(--spacing-sm) var(--spacing-md);
    cursor: pointer;
    transition: background var(--transition-normal);
}

.label-option:hover {
    background: rgba(255, 255, 255, 0.05);
}

.label-option.selected {
    background: rgba(255, 107, 53, 0.1);
}

.label-color {
    width: 16px;
    height: 16px;
    border-radius: 3px;
    flex-shrink: 0;
}

.label-name {
    flex: 1;
    font-size: var(--text-sm);
    color: var(--text-primary);
}

.label-option svg {
    color: var(--orange-primary);
    flex-shrink: 0;
}

/* Mobile Responsive */
@media (max-width: 639px) {
    .dropdown-menu {
        max-height: 200px;
    }
}
</style>
