<template>
  <!-- T044: My Tasks Page - tasks assigned to current user -->
  <div class="max-w-6xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">My Tasks</h1>
      <p class="mt-2 text-gray-600">View and manage your assigned tasks</p>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="text-center py-12">
      <div class="inline-block">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>
      <p class="mt-4 text-gray-600">Loading your tasks...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="groupedTasks.length === 0" class="rounded-md bg-blue-50 p-8 text-center">
      <p class="text-blue-800">No tasks assigned to you yet</p>
    </div>

    <!-- Tasks Grouped by Project -->
    <div v-else class="space-y-8">
      <div v-for="projectGroup in groupedTasks" :key="projectGroup.projectId" class="space-y-4">
        <!-- Project Header -->
        <h2 class="text-xl font-semibold text-gray-900">{{ projectGroup.projectName }}</h2>

        <!-- Tasks Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Task</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Priority</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Due Date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="task in projectGroup.tasks" :key="task.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                  <div class="flex items-center">
                    <span class="font-medium text-gray-900">#{{ task.id }}</span>
                    <span class="ml-2 text-gray-700">{{ task.title }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                  <span :class="[
                    'inline-block px-2 py-1 rounded text-white text-xs font-medium',
                    priorityClass(task.priority)
                  ]">
                    {{ formatPriority(task.priority) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                  {{ task.due_date ? formatDate(task.due_date) : '—' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                  <span :class="[
                    'inline-block px-2 py-1 rounded text-xs font-medium',
                    statusClass(task.column?.name)
                  ]">
                    {{ formatStatus(task.column?.name) }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Error Message -->
    <div v-if="hasError" class="mt-4 p-4 bg-red-50 border border-red-200 rounded-md text-red-800">
      {{ errorMessage }}
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAuth } from '../composables/useAuth';
import axios from 'axios';

const { user } = useAuth();
const tasks = ref([]);
const isLoading = ref(true);
const hasError = ref(false);
const errorMessage = ref('');

// Group tasks by project
const groupedTasks = computed(() => {
  const groups = {};

  tasks.value.forEach(task => {
    const projectId = task.column?.board?.project_id || 'unknown';
    const projectName = task.column?.board?.project?.title || 'Unknown Project';

    if (!groups[projectId]) {
      groups[projectId] = {
        projectId,
        projectName,
        tasks: [],
      };
    }

    groups[projectId].tasks.push(task);
  });

  return Object.values(groups).sort((a, b) => a.projectName.localeCompare(b.projectName));
});

// Load tasks assigned to current user
const loadMyTasks = async () => {
  isLoading.value = true;
  hasError.value = false;

  try {
    if (!user.value?.id) {
      throw new Error('User not authenticated');
    }

    // Fetch tasks filtered by assignee_id = current user
    const response = await axios.get('/api/tasks', {
      params: {
        assignee_id: user.value.id,
      },
    });

    tasks.value = response.data.data || response.data;

    // Sort by due date, then priority
    tasks.value.sort((a, b) => {
      if (a.due_date && !b.due_date) return -1;
      if (!a.due_date && b.due_date) return 1;
      if (a.due_date && b.due_date) {
        return new Date(a.due_date) - new Date(b.due_date);
      }
      // Sort by priority: high > medium > low
      const priorityOrder = { high: 0, medium: 1, low: 2 };
      return (priorityOrder[a.priority] || 3) - (priorityOrder[b.priority] || 3);
    });
  } catch (error) {
    hasError.value = true;
    errorMessage.value = error.message || 'Failed to load your tasks';
    console.error('Error loading tasks:', error);
  } finally {
    isLoading.value = false;
  }
};

// Formatting helpers
const formatPriority = (priority) => {
  const map = { high: 'High', medium: 'Medium', low: 'Low', critical: 'Critical' };
  return map[priority] || priority;
};

const priorityClass = (priority) => {
  const map = {
    high: 'bg-red-600',
    medium: 'bg-yellow-600',
    low: 'bg-green-600',
    critical: 'bg-red-700',
  };
  return map[priority] || 'bg-gray-600';
};

const formatStatus = (status) => {
  const map = {
    todo: 'To Do',
    in_progress: 'In Progress',
    in_review: 'In Review',
    done: 'Done',
  };
  return map[status] || status || 'Unknown';
};

const statusClass = (status) => {
  const map = {
    todo: 'bg-gray-100 text-gray-800',
    in_progress: 'bg-blue-100 text-blue-800',
    in_review: 'bg-purple-100 text-purple-800',
    done: 'bg-green-100 text-green-800',
  };
  return map[status] || 'bg-gray-100 text-gray-800';
};

const formatDate = (dateString) => {
  if (!dateString) return '—';
  const date = new Date(dateString);
  const today = new Date();
  const tomorrow = new Date(today);
  tomorrow.setDate(tomorrow.getDate() + 1);

  const dateNorm = new Date(date.getFullYear(), date.getMonth(), date.getDate());
  const todayNorm = new Date(today.getFullYear(), today.getMonth(), today.getDate());
  const tomorrowNorm = new Date(tomorrow.getFullYear(), tomorrow.getMonth(), tomorrow.getDate());

  if (dateNorm.getTime() === todayNorm.getTime()) {
    return 'Today';
  } else if (dateNorm.getTime() === tomorrowNorm.getTime()) {
    return 'Tomorrow';
  } else if (dateNorm < todayNorm) {
    const daysAgo = Math.floor((todayNorm - dateNorm) / (1000 * 60 * 60 * 24));
    return `${daysAgo}d ago`;
  } else {
    const daysUntil = Math.ceil((dateNorm - todayNorm) / (1000 * 60 * 60 * 24));
    return `in ${daysUntil}d`;
  }
};

onMounted(() => {
  loadMyTasks();
});
</script>
