<template>
  <div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Profile Picture</h3>

    <div class="flex items-center gap-6">
      <!-- Current Avatar -->
      <div class="flex-shrink-0">
        <div v-if="previewUrl" class="relative">
          <img
            :src="previewUrl"
            alt="Avatar preview"
            class="h-24 w-24 rounded-full object-cover"
          />
        </div>
        <div
          v-else
          class="h-24 w-24 rounded-full flex items-center justify-center text-sm font-semibold text-white"
          :style="{ backgroundColor: getInitialsColor(user?.name) }"
        >
          {{ initials }}
        </div>
      </div>

      <!-- Upload Controls -->
      <div class="flex-1">
        <div class="mb-4">
          <label class="block">
            <input
              type="file"
              ref="fileInput"
              accept="image/jpeg,image/png"
              @change="handleFileSelect"
              class="hidden"
            />
            <span
              class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 cursor-pointer inline-block font-medium"
            >
              {{ uploading ? 'Uploading...' : 'Choose Photo' }}
            </span>
          </label>
          <p class="text-xs text-gray-500 mt-2">JPG or PNG, max 5MB</p>
        </div>

        <!-- File Preview Info -->
        <div v-if="selectedFile" class="text-sm text-gray-600 mb-4">
          Selected: {{ selectedFile.name }}
        </div>

        <!-- Error Message -->
        <div v-if="error" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-md text-red-800 text-sm">
          {{ error }}
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-2">
          <button
            v-if="selectedFile || user?.avatar_url"
            type="button"
            @click="uploadAvatar"
            :disabled="uploading || !selectedFile"
            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:bg-gray-400 font-medium"
          >
            {{ uploading ? 'Uploading...' : 'Upload' }}
          </button>
          <button
            v-if="user?.avatar_url"
            type="button"
            @click="removeAvatar"
            :disabled="uploading"
            class="px-4 py-2 border border-red-300 text-red-700 rounded-md hover:bg-red-50 disabled:bg-gray-50 disabled:text-gray-400 font-medium"
          >
            {{ uploading ? 'Processing...' : 'Remove' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
  user: Object,
});

const emit = defineEmits(['avatar-updated']);

const fileInput = ref(null);
const selectedFile = ref(null);
const previewUrl = ref(null);
const uploading = ref(false);
const error = ref('');

const initials = computed(() => {
  if (!props.user?.name) return '';
  const parts = props.user.name.split(' ');
  return parts
    .slice(0, 2)
    .map((part) => part.charAt(0).toUpperCase())
    .join('');
});

const colors = ['bg-red-500', 'bg-blue-500', 'bg-green-500', 'bg-yellow-500', 'bg-purple-500', 'bg-pink-500', 'bg-indigo-500', 'bg-cyan-500'];

const getInitialsColor = (name) => {
  if (!name) return '#3b82f6';
  const charCode = name.charCodeAt(0);
  const colorIndex = charCode % colors.length;
  const colorClass = colors[colorIndex];
  const colorMap = {
    'bg-red-500': '#ef4444',
    'bg-blue-500': '#3b82f6',
    'bg-green-500': '#10b981',
    'bg-yellow-500': '#eab308',
    'bg-purple-500': '#a855f7',
    'bg-pink-500': '#ec4899',
    'bg-indigo-500': '#6366f1',
    'bg-cyan-500': '#06b6d4',
  };
  return colorMap[colorClass];
};

const handleFileSelect = (event) => {
  const file = event.target.files?.[0];
  if (!file) return;

  error.value = '';

  // Validate file type
  if (!['image/jpeg', 'image/png'].includes(file.type)) {
    error.value = 'Please select a JPG or PNG image';
    selectedFile.value = null;
    previewUrl.value = null;
    return;
  }

  // Validate file size (5MB)
  if (file.size > 5 * 1024 * 1024) {
    error.value = 'File size must be less than 5MB';
    selectedFile.value = null;
    previewUrl.value = null;
    return;
  }

  selectedFile.value = file;

  // Create preview
  const reader = new FileReader();
  reader.onload = (e) => {
    previewUrl.value = e.target.result;
  };
  reader.readAsDataURL(file);
};

const uploadAvatar = async () => {
  if (!selectedFile.value) return;

  uploading.value = true;
  error.value = '';

  try {
    const formData = new FormData();
    formData.append('avatar', selectedFile.value);

    await axios.post('/api/profile/avatar', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });

    emit('avatar-updated');
    selectedFile.value = null;
    fileInput.value.value = '';
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to upload avatar';
  } finally {
    uploading.value = false;
  }
};

const removeAvatar = async () => {
  if (!confirm('Are you sure you want to remove your avatar?')) {
    return;
  }

  uploading.value = true;
  error.value = '';

  try {
    await axios.delete('/api/profile/avatar');
    emit('avatar-updated');
    previewUrl.value = null;
    selectedFile.value = null;
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to remove avatar';
  } finally {
    uploading.value = false;
  }
};
</script>
