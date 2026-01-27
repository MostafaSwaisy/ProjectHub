<template>
  <div
    role="alert"
    class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-700 mb-5"
  >
    <div class="flex items-start justify-between">
      <div class="flex-1">
        <h3 v-if="title" class="font-medium text-red-900 mb-1">{{ title }}</h3>
        <p class="text-sm">{{ message }}</p>
      </div>
      <button
        v-if="dismissible"
        @click="$emit('dismiss')"
        class="ml-4 text-red-700 hover:text-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 rounded"
        aria-label="Dismiss error"
      >
        ✕
      </button>
    </div>
  </div>
</template>

<script setup>
defineProps({
  message: {
    type: String,
    required: true,
  },
  title: {
    type: String,
    default: 'Error',
  },
  dismissible: {
    type: Boolean,
    default: true,
  },
})

defineEmits(['dismiss'])
</script>
