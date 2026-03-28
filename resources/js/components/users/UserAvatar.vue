<template>
  <div class="flex items-center gap-2">
    <img
      v-if="avatarUrl"
      :src="avatarUrl"
      :alt="user.name"
      class="w-10 h-10 rounded-full object-cover bg-gray-200"
    />
    <div
      v-else
      class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-semibold text-white"
      :style="{ backgroundColor: getInitialsColor(user.name) }"
    >
      {{ initials }}
    </div>
    <div v-if="showName" class="flex flex-col">
      <span class="text-sm font-medium text-gray-900">{{ user.name }}</span>
      <span v-if="showEmail" class="text-xs text-gray-500">{{ user.email }}</span>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  user: {
    type: Object,
    required: true,
    validator: (value) => {
      return value.name && (value.avatar_url !== undefined || value.email);
    },
  },
  showName: {
    type: Boolean,
    default: true,
  },
  showEmail: {
    type: Boolean,
    default: false,
  },
});

const initials = computed(() => {
  const parts = props.user.name.split(' ');
  return parts
    .slice(0, 2)
    .map((part) => part.charAt(0).toUpperCase())
    .join('');
});

const avatarUrl = computed(() => {
  if (props.user.avatar_url) {
    if (props.user.avatar_url.startsWith('http')) {
      return props.user.avatar_url;
    }
    return `/storage/avatars/${props.user.avatar_url}`;
  }
  return null;
});

const colors = ['bg-red-500', 'bg-blue-500', 'bg-green-500', 'bg-yellow-500', 'bg-purple-500', 'bg-pink-500', 'bg-indigo-500', 'bg-cyan-500'];

const getInitialsColor = (name) => {
  const charCode = name.charCodeAt(0);
  const colorIndex = charCode % colors.length;
  const colorClass = colors[colorIndex];
  // Convert Tailwind class to hex color
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
</script>
