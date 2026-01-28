<template>
  <button
    :type="type"
    :disabled="loading || disabled"
    :aria-busy="loading"
    :aria-disabled="loading || disabled"
    class="w-full py-3 px-4 rounded-lg font-medium transition focus:outline-none focus:ring-2 focus:ring-offset-2 min-h-[44px] flex items-center justify-center"
    :class="[
      variantClasses,
      loading || disabled ? 'opacity-75 cursor-not-allowed' : '',
    ]"
  >
    <span v-if="!loading" class="flex items-center justify-center">
      <slot>Submit</slot>
    </span>
    <span v-else class="flex items-center justify-center gap-2">
      <span class="animate-spin">⏳</span>
      <slot name="loading">
        <span>Loading...</span>
      </slot>
    </span>
  </button>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  loading: {
    type: Boolean,
    default: false,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  type: {
    type: String,
    default: 'submit',
    validator: (value) => ['button', 'submit', 'reset'].includes(value),
  },
  variant: {
    type: String,
    default: 'primary',
    validator: (value) => ['primary', 'secondary', 'outline'].includes(value),
  },
  fullWidth: {
    type: Boolean,
    default: true,
  },
})

const variantClasses = computed(() => {
  const baseClasses = props.fullWidth ? 'w-full' : ''

  switch (props.variant) {
    case 'primary':
      return `${baseClasses} bg-indigo-600 text-white hover:bg-indigo-700 focus:ring-indigo-500`
    case 'secondary':
      return `${baseClasses} bg-gray-200 text-gray-800 hover:bg-gray-300 focus:ring-gray-500`
    case 'outline':
      return `${baseClasses} border border-gray-300 text-gray-700 hover:bg-gray-50 focus:ring-gray-500`
    default:
      return `${baseClasses} bg-indigo-600 text-white hover:bg-indigo-700 focus:ring-indigo-500`
  }
})
</script>
