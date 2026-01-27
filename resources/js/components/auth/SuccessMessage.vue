<template>
  <div
    role="status"
    aria-live="polite"
    class="bg-green-50 border border-green-200 rounded-lg p-4 text-green-700 mb-5"
  >
    <div class="flex items-start justify-between">
      <div class="flex-1">
        <div class="flex items-start gap-3">
          <span class="text-xl">✓</span>
          <div class="flex-1">
            <h3 v-if="title" class="font-medium text-green-900 mb-1">{{ title }}</h3>
            <p class="text-sm">{{ message }}</p>
          </div>
        </div>
      </div>
      <button
        v-if="dismissible"
        @click="handleDismiss"
        class="ml-4 text-green-700 hover:text-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 rounded"
        aria-label="Dismiss message"
      >
        ✕
      </button>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'

const props = defineProps({
  message: {
    type: String,
    required: true,
  },
  title: {
    type: String,
    default: 'Success',
  },
  dismissible: {
    type: Boolean,
    default: true,
  },
  autoDismissMs: {
    type: Number,
    default: null,
  },
})

const emit = defineEmits(['dismiss'])

const handleDismiss = () => {
  emit('dismiss')
}

onMounted(() => {
  if (props.autoDismissMs) {
    setTimeout(() => {
      handleDismiss()
    }, props.autoDismissMs)
  }
})
</script>
