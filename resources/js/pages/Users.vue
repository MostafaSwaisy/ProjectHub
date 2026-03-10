<template>
  <div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">User Management</h1>
      <p class="mt-2 text-gray-600">Manage all users in the system</p>
    </div>

    <!-- Filters -->
    <UserFilters @filters-changed="onFiltersChanged" />

    <!-- Loading State -->
    <div v-if="isLoading" class="text-center py-12">
      <div class="inline-block">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>
      <p class="mt-4 text-gray-600">Loading users...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="hasError" class="rounded-md bg-red-50 p-4 mb-6">
      <p class="text-sm text-red-800">{{ errorMessage }}</p>
    </div>

    <!-- Table -->
    <UserTable
      v-else
      :users="users"
      :pagination="pagination"
      @user-selected="onUserSelected"
      @page-changed="onPageChanged"
    />

    <!-- Detail Modal -->
    <UserDetailModal
      v-model="showDetailModal"
      :user="selectedUser"
      @user-updated="onUserUpdated"
      @user-deleted="onUserDeleted"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useUsers } from '../stores/users';
import UserFilters from '../components/users/UserFilters.vue';
import UserTable from '../components/users/UserTable.vue';
import UserDetailModal from '../components/users/UserDetailModal.vue';

const userStore = useUsers();
const users = ref([]);
const pagination = ref({});
const showDetailModal = ref(false);
const selectedUser = ref(null);
const currentFilters = ref({});

const isLoading = ref(false);
const hasError = ref(false);
const errorMessage = ref('');

const loadUsers = async (page = 1) => {
  isLoading.value = true;
  hasError.value = false;

  try {
    await userStore.fetchUsers(currentFilters.value, page);
    users.value = userStore.users;
    pagination.value = userStore.pagination;
  } catch (error) {
    hasError.value = true;
    errorMessage.value = error.message || 'Failed to load users';
  } finally {
    isLoading.value = false;
  }
};

const onFiltersChanged = (filters) => {
  currentFilters.value = filters;
  loadUsers(1);
};

const onPageChanged = (page) => {
  loadUsers(page);
};

const onUserSelected = (user) => {
  selectedUser.value = user;
  showDetailModal.value = true;
};

const onUserUpdated = () => {
  loadUsers(pagination.value.current_page);
};

const onUserDeleted = () => {
  loadUsers(pagination.value.current_page);
};

onMounted(() => {
  loadUsers();
});
</script>
