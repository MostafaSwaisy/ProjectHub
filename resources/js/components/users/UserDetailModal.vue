<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <!-- Overlay -->
      <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="close"></div>

      <!-- Modal -->
      <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
        <!-- Header -->
        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
          <div class="flex items-center justify-between">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Edit User</h3>
            <button @click="close" class="text-gray-400 hover:text-gray-600">
              <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Content -->
        <div class="bg-white px-4 py-5 sm:p-6">
          <div class="space-y-4">
            <!-- Name -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
              <input
                v-model="formData.name"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              />
              <span v-if="errors.name" class="text-sm text-red-600">{{ errors.name }}</span>
            </div>

            <!-- Email -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
              <input
                v-model="formData.email"
                type="email"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              />
              <span v-if="errors.email" class="text-sm text-red-600">{{ errors.email }}</span>
            </div>

            <!-- System Role -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">System Role</label>
              <select
                v-model="formData.role_id"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              >
                <option v-for="role in roles" :key="role.id" :value="role.id">
                  {{ role.name }}
                </option>
              </select>
              <span v-if="errors.role_id" class="text-sm text-red-600">{{ errors.role_id }}</span>
            </div>

            <!-- Password Note -->
            <div class="rounded-md bg-blue-50 p-4">
              <p class="text-sm text-blue-800">
                <strong>Note:</strong> Users can reset their own passwords using the forgot password flow. Administrators cannot force-reset passwords.
              </p>
            </div>

            <!-- User Status -->
            <div class="rounded-md bg-gray-50 p-4">
              <p class="text-sm text-gray-700">
                <strong>Status:</strong>
                <span :class="user?.deleted_at ? 'text-red-600' : 'text-green-600'">
                  {{ user?.deleted_at ? 'Deleted' : 'Active' }}
                </span>
              </p>
              <p v-if="user?.created_at" class="text-sm text-gray-600 mt-1">
                <strong>Created:</strong> {{ formatDate(user.created_at) }}
              </p>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
          <button
            @click="save"
            :disabled="saving"
            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 disabled:bg-blue-400 sm:ml-3 sm:w-auto sm:text-sm"
          >
            {{ saving ? 'Saving...' : 'Save' }}
          </button>
          <button
            v-if="!user?.deleted_at"
            @click="deactivate"
            :disabled="saving"
            class="w-full inline-flex justify-center rounded-md border border-red-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-red-700 hover:bg-red-50 disabled:bg-gray-50 disabled:text-gray-400 sm:ml-3 sm:w-auto sm:text-sm"
          >
            {{ saving ? 'Deactivating...' : 'Deactivate' }}
          </button>
          <button
            @click="close"
            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
          >
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
  modelValue: Boolean,
  user: Object,
});

const emit = defineEmits(['update:modelValue', 'user-updated', 'user-deleted']);

const isOpen = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value),
});

const formData = ref({
  name: '',
  email: '',
  role_id: null,
});

const errors = ref({});
const saving = ref(false);
const roles = ref([]);

const close = () => {
  isOpen.value = false;
  errors.value = {};
};

const save = async () => {
  saving.value = true;
  errors.value = {};

  try {
    await axios.put(`/api/users/${props.user.id}`, formData.value);
    emit('user-updated');
    close();
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    } else {
      errors.value.general = error.response?.data?.message || 'Failed to save user';
    }
  } finally {
    saving.value = false;
  }
};

const deactivate = async () => {
  if (!confirm('Are you sure you want to deactivate this user? This action can be undone.')) {
    return;
  }

  saving.value = true;

  try {
    await axios.delete(`/api/users/${props.user.id}`);
    emit('user-deleted');
    close();
  } catch (error) {
    errors.value.general = error.response?.data?.message || 'Failed to deactivate user';
  } finally {
    saving.value = false;
  }
};

const fetchRoles = async () => {
  try {
    const response = await axios.get('/api/roles');
    roles.value = response.data;
  } catch (error) {
    console.error('Error fetching roles:', error);
  }
};

const formatDate = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleDateString();
};

watch(
  () => props.user,
  (newUser) => {
    if (newUser) {
      formData.value = {
        name: newUser.name,
        email: newUser.email,
        role_id: newUser.role_id,
      };
      errors.value = {};
    }
  },
  { deep: true },
);

onMounted(() => {
  fetchRoles();
});
</script>
