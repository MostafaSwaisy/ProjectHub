<template>
  <div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Change Password</h3>

    <form @submit.prevent="handleSubmit" class="space-y-4">
      <!-- Current Password -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
        <input
          v-model="formData.current_password"
          type="password"
          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
          placeholder="Enter your current password"
        />
        <span v-if="errors.current_password" class="text-sm text-red-600">{{ errors.current_password[0] }}</span>
      </div>

      <!-- New Password -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
        <input
          v-model="formData.password"
          type="password"
          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
          placeholder="Enter a new password (minimum 8 characters)"
        />
        <p class="text-xs text-gray-500 mt-1" :class="{ 'text-red-600': formData.password.length > 0 && formData.password.length < 8 }">
          {{ formData.password.length }}/8+ characters
        </p>
        <span v-if="errors.password" class="text-sm text-red-600">{{ errors.password[0] }}</span>
      </div>

      <!-- Confirm Password -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
        <input
          v-model="formData.password_confirmation"
          type="password"
          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
          placeholder="Confirm your new password"
        />
        <span v-if="errors.password_confirmation" class="text-sm text-red-600">{{ errors.password_confirmation[0] }}</span>
      </div>

      <!-- Save Button -->
      <div class="flex gap-2">
        <button
          type="submit"
          :disabled="saving || !isFormValid"
          class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:bg-blue-400 font-medium"
        >
          {{ saving ? 'Updating...' : 'Update Password' }}
        </button>
        <button
          type="button"
          @click="resetForm"
          class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 font-medium"
        >
          Cancel
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
import { ref, computed } from 'vue';
import axios from 'axios';

const emit = defineEmits(['updated']);

const formData = ref({
  current_password: '',
  password: '',
  password_confirmation: '',
});

const errors = ref({});
const saving = ref(false);
const successMessage = ref('');

const isFormValid = computed(() => {
  return (
    formData.value.current_password.length > 0 &&
    formData.value.password.length >= 8 &&
    formData.value.password === formData.value.password_confirmation
  );
});

const handleSubmit = async () => {
  saving.value = true;
  errors.value = {};
  successMessage.value = '';

  try {
    await axios.put('/api/profile/password', formData.value);
    successMessage.value = 'Password updated successfully!';
    resetForm();
    emit('updated');

    setTimeout(() => {
      successMessage.value = '';
    }, 3000);
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    } else {
      errors.value.general = error.response?.data?.message || 'Failed to update password';
    }
  } finally {
    saving.value = false;
  }
};

const resetForm = () => {
  formData.value = {
    current_password: '',
    password: '',
    password_confirmation: '',
  };
  errors.value = {};
};
</script>
