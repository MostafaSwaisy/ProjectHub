<template>
    <!-- T078: Kanban View Page - integrates KanbanBoard component -->
    <div class="kanban-view">
        <KanbanBoard :project-id="projectId" />
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import KanbanBoard from '../../components/kanban/KanbanBoard.vue';

const route = useRoute();

// Get project ID from route params
const projectId = computed(() => route.params.id);
</script>

<style scoped>
.kanban-view {
    width: 100%;
    height: 100%;
}
</style>
