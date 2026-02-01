<template>
  <button
    :class="[
      'button',
      `button-${variant}`,
      `button-${size}`,
      { 'button-loading': loading, 'button-disabled': disabled || loading, 'full-width': fullWidth }
    ]"
    :disabled="disabled || loading"
    :type="type"
    @click="$emit('click', $event)"
  >
    <!-- Icon slot (before text) -->
    <span v-if="$slots.icon" class="button-icon">
      <slot name="icon" />
    </span>

    <!-- Main content -->
    <span class="button-content">
      <slot />
    </span>

    <!-- Loading spinner -->
    <span v-if="loading" class="button-spinner">
      <svg class="spinner" viewBox="0 0 24 24">
        <circle cx="12" cy="12" r="10" class="spinner-circle" />
      </svg>
    </span>
  </button>
</template>

<script setup>
defineProps({
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'secondary', 'ghost', 'danger'].includes(value)
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value)
  },
  loading: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  },
  fullWidth: {
    type: Boolean,
    default: false
  },
  type: {
    type: String,
    default: 'button',
    validator: (value) => ['button', 'submit', 'reset'].includes(value)
  }
})

defineEmits(['click'])
</script>

<style scoped>
.button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: var(--spacing-sm);
  font-family: var(--font-family);
  font-weight: var(--font-medium);
  border: none;
  cursor: pointer;
  transition: all var(--transition-normal);
  position: relative;
  white-space: nowrap;
  border-radius: var(--radius-md);
}

/* Full width variant */
.button.full-width {
  width: 100%;
}

/* Size variants */
.button-sm {
  padding: 0.5rem 1rem;
  font-size: var(--text-sm);
  min-height: 36px;
}

.button-md {
  padding: 0.75rem 1.5rem;
  font-size: var(--text-base);
  min-height: 44px;
}

.button-lg {
  padding: 1rem 2rem;
  font-size: var(--text-lg);
  min-height: 52px;
}

/* Primary variant */
.button-primary {
  background: var(--gradient-orange);
  color: var(--text-primary);
  box-shadow: var(--shadow-md);
}

.button-primary:hover:not(.button-disabled) {
  box-shadow: var(--shadow-orange);
  transform: translateY(-2px);
}

.button-primary:active:not(.button-disabled) {
  transform: translateY(0);
}

/* Secondary variant */
.button-secondary {
  background: transparent;
  color: var(--blue-primary);
  border: 1px solid var(--blue-primary);
  box-shadow: none;
}

.button-secondary:hover:not(.button-disabled) {
  background: rgba(37, 99, 235, 0.1);
  box-shadow: var(--shadow-blue);
}

/* Ghost variant */
.button-ghost {
  background: transparent;
  color: var(--text-primary);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.button-ghost:hover:not(.button-disabled) {
  background: rgba(255, 255, 255, 0.1);
  border-color: rgba(255, 255, 255, 0.3);
}

/* Danger variant */
.button-danger {
  background: var(--red-primary);
  color: var(--text-primary);
  box-shadow: var(--shadow-md);
}

.button-danger:hover:not(.button-disabled) {
  box-shadow: var(--shadow-red);
  opacity: 0.9;
}

/* Disabled state */
.button-disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Loading state */
.button-loading {
  pointer-events: none;
}

.button-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.button-content {
  flex: 1;
}

.button-spinner {
  position: absolute;
  right: 0;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 1em;
  height: 1em;
  animation: spin 1s linear infinite;
}

.spinner {
  width: 1em;
  height: 1em;
  stroke-width: 2;
  fill: none;
}

.spinner-circle {
  stroke: currentColor;
  stroke-dasharray: 60;
  stroke-dashoffset: 0;
  animation: dash 1.5s ease-in-out infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

@keyframes dash {
  0% {
    stroke-dasharray: 60;
    stroke-dashoffset: 0;
  }
  50% {
    stroke-dasharray: 20;
    stroke-dashoffset: 40;
  }
  100% {
    stroke-dasharray: 60;
    stroke-dashoffset: 60;
  }
}

/* Focus states */
.button:focus-visible {
  outline: 2px solid var(--orange-primary);
  outline-offset: 2px;
}

/* Mobile/touch optimization */
@media (hover: none) and (pointer: coarse) {
  .button:active:not(.button-disabled) {
    opacity: 0.8;
    transform: scale(0.98);
  }
}
</style>
