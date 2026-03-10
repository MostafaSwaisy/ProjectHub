<template>
  <div class="mb-6 space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
      <!-- Search Input -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
        <input
          v-model="searchModel"
          type="text"
          placeholder="Search by name or email..."
          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
          @input="onSearch"
        />
      </div>

      <!-- Role Filter -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">System Role</label>
        <select
          v-model="filters.role_id"
          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
          @change="emit('filters-changed', filters)"
        >
          <option :value="null">All Roles</option>
          <option v-for="role in roles" :key="role.id" :value="role.id">
            {{ role.name }}
          </option>
        </select>
      </div>

      <!-- Status Filter -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
        <select
          v-model="filters.status"
          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
          @change="emit('filters-changed', filters)"
        >
          <option value="active">Active</option>
          <option value="deleted">Deleted</option>
          <option :value="null">All</option>
        </select>
      </div>

      <!-- Sort -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
        <div class="flex gap-2">
          <select
            v-model="filters.sort"
            class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            @change="emit('filters-changed', filters)"
          >
            <option value="created_at">Created</option>
            <option value="name">Name</option>
            <option value="email">Email</option>
          </select>
          <select
            v-model="filters.direction"
            class="w-24 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            @change="emit('filters-changed', filters)"
          >
            <option value="desc">Desc</option>
            <option value="asc">Asc</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Clear Filters Button -->
    <div>
      <button
        @click="clearFilters"
        class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50"
      >
        Clear Filters
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const emit = defineEmits(['filters-changed']);

const searchModel = ref('');
const filters = ref({
  search: '',
  role_id: null,
  status: 'active',
  sort: 'created_at',
  direction: 'desc',
});

const roles = ref([]);
let searchTimeout;

const onSearch = () => {
  filters.value.search = searchModel.value;
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    emit('filters-changed', filters.value);
  }, 300);
};

const clearFilters = () => {
  searchModel.value = '';
  filters.value = {
    search: '',
    role_id: null,
    status: 'active',
    sort: 'created_at',
    direction: 'desc',
  };
  emit('filters-changed', filters.value);
};

const fetchRoles = async () => {
  try {
    const response = await axios.get('/api/roles');
    roles.value = response.data;
  } catch (error) {
    console.error('Error fetching roles:', error);
  }
};

onMounted(() => {
  fetchRoles();
});
</script>
