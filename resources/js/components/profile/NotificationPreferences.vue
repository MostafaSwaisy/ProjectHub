<template>
  <div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Notification Preferences</h3>

    <form @submit.prevent="handleSubmit" class="space-y-6">
      <!-- Notification Frequency -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Notification Frequency</label>
        <select
          v-model="formData.notification_frequency"
          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
        >
          <option value="realtime">Real-time (immediate notifications)</option>
          <option value="daily">Daily digest</option>
          <option value="weekly">Weekly digest</option>
          <option value="none">Disabled</option>
        </select>
        <p class="text-xs text-gray-500 mt-1">How often you want to receive notifications</p>
      </div>

      <!-- Email Notifications -->
      <div class="flex items-center">
        <input
          id="notification_email"
          v-model="formData.notification_email"
          type="checkbox"
          class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
        />
        <label for="notification_email" class="ml-2 text-sm text-gray-700">
          <span class="font-medium">Email notifications</span>
          <p class="text-xs text-gray-500">Receive notifications via email</p>
        </label>
      </div>

      <!-- Task Assignment Notifications -->
      <div class="flex items-center">
        <input
          id="notification_assignments"
          v-model="formData.notification_assignments"
          type="checkbox"
          class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
        />
        <label for="notification_assignments" class="ml-2 text-sm text-gray-700">
          <span class="font-medium">Task assignments</span>
          <p class="text-xs text-gray-500">Get notified when tasks are assigned to you</p>
        </label>
      </div>

      <!-- Comment Notifications -->
      <div class="flex items-center">
        <input
          id="notification_comments"
          v-model="formData.notification_comments"
          type="checkbox"
          class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
        />
        <label for="notification_comments" class="ml-2 text-sm text-gray-700">
          <span class="font-medium">Task comments</span>
          <p class="text-xs text-gray-500">Get notified about comments on your tasks</p>
        </label>
      </div>

      <!-- Save Button -->
      <div class="flex gap-2">
        <button
          type="submit"
          :disabled="saving"
          class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:bg-blue-400 font-medium"
        >
          {{ saving ? 'Saving...' : 'Save Preferences' }}
        </button>
      </div>
    </form>

    <!-- Success Message -->
    <div v-if="successMessage" class="mt-4 p-3 bg-green-50 border border-green-200 rounded-md text-green-800 text-sm">
      {{ successMessage }}
    </div>

    <!-- Error Message -->
    <div v-if="errors.general" class="mt-4 p-3 bg-red-50 border border-red-200 rounded-md text-red-800 text-sm">
      {{ errors.general }}
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
  preferences: Object,
});

const emit = defineEmits(['updated']);

const formData = ref({
  notification_frequency: 'realtime',
  notification_email: true,
  notification_assignments: true,
  notification_comments: true,
});

const errors = ref({});
const saving = ref(false);
const successMessage = ref('');

const handleSubmit = async () => {
  saving.value = true;
  errors.value = {};
  successMessage.value = '';

  try {
    await axios.put('/api/profile/preferences', formData.value);
    successMessage.value = 'Preferences updated successfully!';
    emit('updated');

    setTimeout(() => {
      successMessage.value = '';
    }, 3000);
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    } else {
      errors.value.general = error.response?.data?.message || 'Failed to save preferences';
    }
  } finally {
    saving.value = false;
  }
};

watch(
  () => props.preferences,
  (newPreferences) => {
    if (newPreferences) {
      formData.value = {
        notification_frequency: newPreferences.notification_frequency || 'realtime',
        notification_email: newPreferences.notification_email !== false,
        notification_assignments: newPreferences.notification_assignments !== false,
        notification_comments: newPreferences.notification_comments !== false,
      };
    }
  },
  { deep: true, immediate: true },
);

onMounted(() => {
  if (props.preferences) {
    formData.value = {
      notification_frequency: props.preferences.notification_frequency || 'realtime',
      notification_email: props.preferences.notification_email !== false,
      notification_assignments: props.preferences.notification_assignments !== false,
      notification_comments: props.preferences.notification_comments !== false,
    };
  }
});
</script>
