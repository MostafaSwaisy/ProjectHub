<template>
  <div class="mb-5">
    <label :for="`field-${name}`" class="block text-sm font-medium text-gray-700 mb-2">
      {{ label }}
      <span v-if="required" class="text-red-500 ml-1" aria-label="required">*</span>
    </label>

    <div class="relative">
      <input
        :id="`field-${name}`"
        :value="modelValue"
        :name="name"
        :type="showPassword ? 'text' : 'password'"
        :placeholder="placeholder"
        :disabled="disabled"
        :required="required"
        :aria-required="required"
        :aria-invalid="!!error"
        :aria-describedby="ariaDescribedBy"
        @input="$emit('update:modelValue', $event.target.value)"
        @blur="$emit('blur')"
        @focus="$emit('focus')"
        class="w-full px-3 py-2 pr-10 border rounded-lg focus:outline-none focus:ring-2 transition"
        :class="[
          error
            ? 'border-red-500 focus:ring-red-500 focus:border-red-500'
            : 'border-gray-300 focus:ring-indigo-500 focus:border-indigo-500',
          disabled ? 'bg-gray-100 cursor-not-allowed' : 'bg-white',
        ]"
      />

      <!-- Toggle password visibility button -->
      <button
        type="button"
        :aria-label="showPassword ? 'Hide password' : 'Show password'"
        @click="showPassword = !showPassword"
        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded px-1"
        :disabled="disabled"
      >
        <span v-if="showPassword" class="text-lg">👁️‍🗨️</span>
        <span v-else class="text-lg">👁️</span>
      </button>
    </div>

    <!-- Password requirements checklist -->
    <div v-if="showRequirements && modelValue" class="mt-3 space-y-1">
      <p class="text-xs font-medium text-gray-600 mb-2">Password requirements:</p>
      <div class="space-y-1">
        <div class="flex items-center text-xs" :class="requirementsMet.minLength ? 'text-green-600' : 'text-gray-600'">
          <span class="mr-2">{{ requirementsMet.minLength ? '✓' : '○' }}</span>
          <span>At least 8 characters</span>
        </div>
        <div class="flex items-center text-xs" :class="requirementsMet.hasNumber ? 'text-green-600' : 'text-gray-600'">
          <span class="mr-2">{{ requirementsMet.hasNumber ? '✓' : '○' }}</span>
          <span>At least one number (0-9)</span>
        </div>
        <div class="flex items-center text-xs" :class="requirementsMet.hasLetter ? 'text-green-600' : 'text-gray-600'">
          <span class="mr-2">{{ requirementsMet.hasLetter ? '✓' : '○' }}</span>
          <span>At least one letter (a-zA-Z)</span>
        </div>
      </div>
    </div>

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
import { ref, computed } from 'vue'

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
  showRequirements: {
    type: Boolean,
    default: true,
  },
})

defineEmits(['update:modelValue', 'blur', 'focus', 'input'])

const showPassword = ref(false)

const requirementsMet = computed(() => ({
  minLength: props.modelValue.length >= 8,
  hasNumber: /[0-9]/.test(props.modelValue),
  hasLetter: /[a-zA-Z]/.test(props.modelValue),
}))

const ariaDescribedBy = computed(() => {
  const ids = []
  if (props.error) ids.push(`field-${props.name}-error`)
  if (props.helperText) ids.push(`field-${props.name}-help`)
  return ids.length > 0 ? ids.join(' ') : undefined
})
</script>
