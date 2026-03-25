<template>
  <div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">My Profile</h1>
      <p class="mt-2 text-gray-600">Manage your profile settings and preferences</p>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="text-center py-12">
      <div class="inline-block">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>
      <p class="mt-4 text-gray-600">Loading your profile...</p>
    </div>

    <!-- Profile Content -->
    <div v-else class="space-y-8">
      <!-- Avatar Section -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Profile Picture</h3>
        <AvatarUploader :user="currentUser" @updated="onAvatarUpdated" />
      </div>

      <!-- Tabs Navigation -->
      <div class="border-b border-gray-200">
        <nav class="flex space-x-8" aria-label="Profile tabs">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            :class="[
              'py-4 px-1 border-b-2 font-medium text-sm',
              activeTab === tab.id
                ? 'border-blue-600 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
            ]"
          >
            {{ tab.label }}
          </button>
        </nav>
      </div>

      <!-- Profile Information Tab -->
      <div v-show="activeTab === 'profile'">
        <ProfileForm :user="currentUser" @updated="onProfileUpdated" />
      </div>

      <!-- Password Tab -->
      <div v-show="activeTab === 'password'">
        <PasswordChangeForm @updated="onPasswordUpdated" />
      </div>

      <!-- Notifications Tab -->
      <div v-show="activeTab === 'notifications'">
        <NotificationPreferences :preferences="preferences" @updated="onPreferencesUpdated" />
      </div>
    </div>

    <!-- Error Message -->
    <div v-if="hasError" class="mt-4 p-4 bg-red-50 border border-red-200 rounded-md text-red-800">
      {{ errorMessage }}
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuth } from '../stores/auth';
import AvatarUploader from '../components/profile/AvatarUploader.vue';
import ProfileForm from '../components/profile/ProfileForm.vue';
import PasswordChangeForm from '../components/profile/PasswordChangeForm.vue';
import NotificationPreferences from '../components/profile/NotificationPreferences.vue';

const authStore = useAuth();
const currentUser = ref(null);
const preferences = ref({});
const activeTab = ref('profile');
const isLoading = ref(true);
const hasError = ref(false);
const errorMessage = ref('');

const tabs = [
  { id: 'profile', label: 'Profile Information' },
  { id: 'password', label: 'Password' },
  { id: 'notifications', label: 'Notifications' },
];

const loadProfile = async () => {
  isLoading.value = true;
  hasError.value = false;

  try {
    // Fetch profile data from API
    const response = await fetch('/api/profile', {
      headers: {
        'Authorization': `Bearer ${authStore.token}`,
        'Accept': 'application/json',
      },
    });

    if (!response.ok) {
      throw new Error('Failed to load profile');
    }

    const data = await response.json();
    currentUser.value = data.data;
    preferences.value = data.preferences || {};
  } catch (error) {
    hasError.value = true;
    errorMessage.value = error.message || 'Failed to load profile';
  } finally {
    isLoading.value = false;
  }
};

const onAvatarUpdated = async () => {
  await loadProfile();
};

const onProfileUpdated = async () => {
  await loadProfile();
};

const onPasswordUpdated = async () => {
  // Password change doesn't require reload
  activeTab.value = 'profile';
};

const onPreferencesUpdated = async () => {
  await loadProfile();
};

onMounted(() => {
  loadProfile();
});
</script>
