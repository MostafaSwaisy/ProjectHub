<template>
  <div class="mb-5">
    <label :for="`field-${name}`" class="block text-sm font-medium text-gray-700 mb-2">
      {{ label }}
      <span v-if="required" class="text-red-500 ml-1" aria-label="required">*</span>
    </label>

    <input
      :id="`field-${name}`"
      :value="modelValue"
      :name="name"
      :type="type"
      :placeholder="placeholder"
      :disabled="disabled"
      :required="required"
      :aria-required="required"
      :aria-invalid="!!error"
      :aria-describedby="ariaDescribedBy"
      :autocomplete="autocomplete"
      :min="min"
      :max="max"
      @input="$emit('update:modelValue', $event.target.value)"
      @blur="$emit('blur')"
      @change="$emit('change', modelValue)"
      class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 transition"
      :class="[
        error
          ? 'border-red-500 focus:ring-red-500 focus:border-red-500'
          : 'border-gray-300 focus:ring-indigo-500 focus:border-indigo-500',
        disabled ? 'bg-gray-100 cursor-not-allowed' : 'bg-white',
      ]"
    />

    <!-- Error message -->
    <p
      v-if="error"
      :id="`field-${name}-error`"
      role="alert"
      class="text-red-600 text-xs mt-1"
    >
      {{ error }}
    </p>

    <!-- Helper text -->
    <p
      v-if="helperText"
      :id="`field-${name}-help`"
      class="text-gray-500 text-xs mt-1"
    >
      {{ helperText }}
    </p>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue: {
    type: String,
    required: true,
  },
  label: {
    type: String,
    required: true,
  },
  name: {
    type: String,
    required: true,
  },
  type: {
    type: String,
    default: 'text',
  },
  placeholder: {
    type: String,
    default: undefined,
  },
  required: {
    type: Boolean,
    default: false,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  error: {
    type: String,
    default: null,
  },
  helperText: {
    type: String,
    default: null,
  },
  autocomplete: {
    type: String,
    default: undefined,
  },
  min: {
    type: [String, Number],
    default: undefined,
  },
  max: {
    type: [String, Number],
    default: undefined,
  },
})

defineEmits(['update:modelValue', 'blur', 'change'])

const ariaDescribedBy = computed(() => {
  const ids = []
  if (props.error) ids.push(`field-${props.name}-error`)
  if (props.helperText) ids.push(`field-${props.name}-help`)
  return ids.length > 0 ? ids.join(' ') : undefined
})
</script>
