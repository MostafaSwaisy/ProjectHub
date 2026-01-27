<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white rounded-lg shadow-lg p-8" role="main">
      <component :is="headingLevel" class="text-2xl font-bold text-gray-900 mb-2">
        {{ title }}
      </component>
      <p v-if="subtitle" class="text-gray-600 text-sm mb-6">
        {{ subtitle }}
      </p>

      <!-- Default slot for form content -->
      <div class="space-y-5">
        <slot />
      </div>

      <!-- Footer slot for links and help text -->
      <div v-if="$slots.footer" class="mt-6 pt-6 border-t border-gray-200">
        <slot name="footer" />
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    required: true,
  },
  subtitle: {
    type: String,
    default: undefined,
  },
  headingLevel: {
    type: String,
    default: 'h1',
    validator: (value) => ['h1', 'h2', 'h3'].includes(value),
  },
})
</script>
