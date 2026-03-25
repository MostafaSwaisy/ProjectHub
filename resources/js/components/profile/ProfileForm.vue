<template>
  <div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Profile Information</h3>

    <form @submit.prevent="handleSubmit" class="space-y-4">
      <!-- Name -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
        <input
          v-model="formData.name"
          type="text"
          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
          placeholder="Your full name"
        />
        <span v-if="errors.name" class="text-sm text-red-600">{{ errors.name }}</span>
      </div>

      <!-- Email -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
        <input
          v-model="formData.email"
          type="email"
          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
          placeholder="your@email.com"
        />
        <span v-if="errors.email" class="text-sm text-red-600">{{ errors.email }}</span>
      </div>

      <!-- Bio -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Bio</label>
        <textarea
          v-model="formData.bio"
          rows="4"
          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
          placeholder="Tell us about yourself..."
        ></textarea>
        <p class="text-xs text-gray-500 mt-1">{{ formData.bio?.length || 0 }}/500 characters</p>
        <span v-if="errors.bio" class="text-sm text-red-600">{{ errors.bio }}</span>
      </div>

      <!-- Save Button -->
      <div class="flex gap-2">
        <button
          type="submit"
          :disabled="saving"
          class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:bg-blue-400 font-medium"
        >
          {{ saving ? 'Saving...' : 'Save Changes' }}
        </button>
        <button
          v-if="hasChanges"
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
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
  user: Object,
});

const emit = defineEmits(['updated']);

const formData = ref({
  name: '',
  email: '',
  bio: '',
});

const originalData = ref({
  name: '',
  email: '',
  bio: '',
});

const errors = ref({});
const saving = ref(false);
const successMessage = ref('');

const hasChanges = computed(() => {
  return (
    formData.value.name !== originalData.value.name ||
    formData.value.email !== originalData.value.email ||
    formData.value.bio !== originalData.value.bio
  );
});

const handleSubmit = async () => {
  saving.value = true;
  errors.value = {};
  successMessage.value = '';

  try {
    await axios.put('/api/profile', formData.value);
    successMessage.value = 'Profile updated successfully!';
    originalData.value = { ...formData.value };
    emit('updated');

    setTimeout(() => {
      successMessage.value = '';
    }, 3000);
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    } else {
      errors.value.general = error.response?.data?.message || 'Failed to save profile';
    }
  } finally {
    saving.value = false;
  }
};

const resetForm = () => {
  formData.value = { ...originalData.value };
  errors.value = {};
};

watch(
  () => props.user,
  (newUser) => {
    if (newUser) {
      formData.value = {
        name: newUser.name,
        email: newUser.email,
        bio: newUser.bio || '',
      };
      originalData.value = { ...formData.value };
    }
  },
  { deep: true, immediate: true },
);

onMounted(() => {
  if (props.user) {
    formData.value = {
      name: props.user.name,
      email: props.user.email,
      bio: props.user.bio || '',
    };
    originalData.value = { ...formData.value };
  }
});
</script>
