<template>
  <div class="input-group">
    <!-- Label -->
    <label v-if="label" :for="inputId" class="input-label">
      {{ label }}
      <span v-if="required" class="input-required">*</span>
    </label>

    <!-- Input wrapper with icon support -->
    <div class="input-wrapper">
      <!-- Prepend icon -->
      <span v-if="$slots['prepend-icon']" class="input-icon-prepend">
        <slot name="prepend-icon" />
      </span>

      <!-- Input field -->
      <input
        :id="inputId"
        :type="type"
        :class="['input-field', { 'input-error': !!error, 'input-filled': modelValue }]"
        :placeholder="placeholder"
        :disabled="disabled"
        :autocomplete="autocomplete"
        :required="required"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        @blur="$emit('blur', $event)"
        @focus="$emit('focus', $event)"
      />

      <!-- Clear button -->
      <button
        v-if="clearable && modelValue"
        type="button"
        class="input-clear"
        @click="handleClear"
        aria-label="Clear input"
      >
        ✕
      </button>

      <!-- Append icon -->
      <span v-if="$slots['append-icon']" class="input-icon-append">
        <slot name="append-icon" />
      </span>
    </div>

    <!-- Error message -->
    <span v-if="error" class="input-error-message">
      {{ error }}
    </span>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: ''
  },
  type: {
    type: String,
    default: 'text',
    validator: (value) =>
      ['text', 'email', 'password', 'number', 'date', 'search'].includes(value)
  },
  label: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: ''
  },
  error: {
    type: String,
    default: ''
  },
  disabled: {
    type: Boolean,
    default: false
  },
  required: {
    type: Boolean,
    default: false
  },
  autocomplete: {
    type: String,
    default: ''
  },
  icon: {
    type: String,
    default: ''
  },
  clearable: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue', 'blur', 'focus', 'clear'])

const inputId = computed(() => `input-${Math.random().toString(36).slice(2, 9)}`)

const handleClear = () => {
  emit('update:modelValue', '')
  emit('clear')
}
</script>

<style scoped>
.input-group {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-sm);
  width: 100%;
}

.input-label {
  font-size: var(--text-sm);
  font-weight: var(--font-medium);
  color: var(--text-primary);
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.input-required {
  color: var(--red-primary);
}

.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  background-color: var(--black-tertiary);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: var(--radius-md);
  transition: all var(--transition-normal);
}

.input-wrapper:focus-within {
  border-color: var(--orange-primary);
  box-shadow: var(--shadow-orange);
}

.input-field {
  flex: 1;
  background: transparent;
  border: none;
  color: var(--text-primary);
  padding: 0.75rem 1rem;
  font-family: var(--font-family);
  font-size: var(--text-base);
  outline: none;
  min-height: 44px;
}

.input-field::placeholder {
  color: var(--text-muted);
}

.input-field:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Icon styles */
.input-icon-prepend,
.input-icon-append {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 44px;
  height: 44px;
  color: var(--text-secondary);
  flex-shrink: 0;
}

.input-icon-prepend {
  padding-left: var(--spacing-sm);
}

.input-icon-append {
  padding-right: var(--spacing-sm);
}

/* Clear button */
.input-clear {
  position: absolute;
  right: 12px;
  background: none;
  border: none;
  color: var(--text-secondary);
  cursor: pointer;
  font-size: var(--text-lg);
  padding: 0 var(--spacing-sm);
  transition: color var(--transition-normal);
}

.input-clear:hover {
  color: var(--text-primary);
}

/* Error state */
.input-wrapper.input-error {
  border-color: var(--red-primary);
  box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

.input-error-message {
  font-size: var(--text-sm);
  color: var(--red-primary);
  margin-top: -var(--spacing-sm);
}

/* Filled state animation */
.input-filled {
  color: var(--text-primary);
}

/* Touch target size */
@media (max-width: 639px) {
  .input-field {
    min-height: 48px;
  }

  .input-icon-prepend,
  .input-icon-append {
    width: 48px;
    height: 48px;
  }
}
</style>
