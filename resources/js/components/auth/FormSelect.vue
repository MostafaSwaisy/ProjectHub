<template>
  <div class="mb-5">
    <label :for="`field-${name}`" class="block text-sm font-medium text-gray-700 mb-2">
      {{ label }}
      <span v-if="required" class="text-red-500 ml-1" aria-label="required">*</span>
    </label>

    <select
      :id="`field-${name}`"
      :value="modelValue"
      :name="name"
      :disabled="disabled"
      :required="required"
      :aria-required="required"
      :aria-invalid="!!error"
      :aria-describedby="ariaDescribedBy"
      @change="$emit('update:modelValue', $event.target.value)"
      @blur="$emit('blur')"
      class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 transition bg-white cursor-pointer"
      :class="[
        error
          ? 'border-red-500 focus:ring-red-500 focus:border-red-500'
          : 'border-gray-300 focus:ring-indigo-500 focus:border-indigo-500',
        disabled ? 'bg-gray-100 cursor-not-allowed' : '',
      ]"
    >
      <option value="" disabled>{{ placeholder }}</option>
      <option
        v-for="option in options"
        :key="option.value"
        :value="option.value"
        :title="option.description"
      >
        {{ option.label }}
      </option>
    </select>

    <!-- Error message -->
    <p
      v-if="error"
      :id="`field-${name}-error`"
      role="alert"
      class="text-red-600 text-xs mt-1"
    >
      {{ error }}
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
  options: {
    type: Array,
    required: true,
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
  placeholder: {
    type: String,
    default: 'Select an option',
  },
})

defineEmits(['update:modelValue', 'change', 'blur'])

const ariaDescribedBy = computed(() => {
  return props.error ? `field-${props.name}-error` : undefined
})
</script>
