<template>
  <div class="dashboard-stats">
    <h2 class="dashboard-stats__title">Overview</h2>

    <!-- Error State -->
    <div v-if="error" class="dashboard-stats__error">
      <div class="error-card">
        <svg class="error-card__icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <p class="error-card__message">{{ error }}</p>
        <button class="error-card__button" @click="handleRetry">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          Retry
        </button>
      </div>
    </div>

    <!-- Skeleton Loaders -->
    <div v-else-if="loading" class="dashboard-stats__grid">
      <div v-for="i in 4" :key="i" class="stat-skeleton">
        <div class="stat-skeleton__label"></div>
        <div class="stat-skeleton__value"></div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else-if="!hasProjects" class="dashboard-stats__empty">
      <!-- Stats Grid (shown first, above message) -->
      <div class="dashboard-stats__grid dashboard-stats__grid--empty">
        <StatCard label="Projects" :value="0" />
        <StatCard label="Active Tasks" :value="0" />
        <StatCard label="Team Members" :value="0" />
        <StatCard label="Overdue" :value="0" :alert="true" />
      </div>

      <!-- Empty State Message -->
      <div class="empty-state">
        <svg class="empty-state__icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="empty-state__title">No Projects Yet</h3>
        <p class="empty-state__message">Create your first project to get started tracking tasks and collaborating with your team.</p>
        <router-link to="/projects?create=true" class="empty-state__button">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Create Your First Project
        </router-link>
      </div>
    </div>

    <!-- Stats Grid -->
    <div v-else class="dashboard-stats__grid">
      <StatCard
        label="Projects"
        :value="stats.total_projects"
        icon="<svg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z' /></svg>"
      />
      <StatCard
        label="Active Tasks"
        :value="stats.active_tasks"
        icon="<svg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2' /></svg>"
      />
      <StatCard
        label="Team Members"
        :value="stats.team_members"
        icon="<svg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z' /></svg>"
      />
      <StatCard
        label="Overdue"
        :value="stats.overdue_tasks"
        :alert="true"
        icon="<svg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='currentColor'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' /></svg>"
      />
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { storeToRefs } from 'pinia';
import { useDashboardStore } from '../../stores/dashboard';
import StatCard from './StatCard.vue';

const dashboardStore = useDashboardStore();
const { stats, loading, error } = storeToRefs(dashboardStore);
const { hasProjects } = dashboardStore;

onMounted(async () => {
  await dashboardStore.fetchStats();
});

const handleRetry = async () => {
  await dashboardStore.retry();
};
</script>

<style scoped>
.dashboard-stats {
  margin-bottom: 32px;
}

.dashboard-stats__title {
  font-size: 24px;
  font-weight: 700;
  color: var(--text-primary);
  margin-bottom: 20px;
}

.dashboard-stats__grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
}

.dashboard-stats__grid--empty {
  grid-template-columns: repeat(4, 1fr);
  margin-bottom: 40px;
}

/* Error State */
.dashboard-stats__error {
  margin-bottom: 20px;
}

.error-card {
  background: linear-gradient(135deg, rgba(220, 38, 38, 0.1), rgba(220, 38, 38, 0.05));
  border: 1px solid var(--red-primary);
  border-radius: 16px;
  padding: 24px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
  text-align: center;
}

.error-card__icon {
  width: 48px;
  height: 48px;
  color: var(--red-primary);
}

.error-card__message {
  color: var(--text-primary);
  font-size: 16px;
  margin: 0;
}

.error-card__button {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  background: var(--orange-gradient);
  border: none;
  border-radius: 8px;
  color: white;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.error-card__button:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
}

.error-card__button svg {
  width: 20px;
  height: 20px;
}

/* Skeleton Loaders */
.stat-skeleton {
  background: var(--glass-bg);
  border: 1px solid var(--glass-border);
  border-radius: 16px;
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.stat-skeleton__label,
.stat-skeleton__value {
  background: linear-gradient(90deg, var(--glass-border) 25%, var(--glass-border-hover) 50%, var(--glass-border) 75%);
  background-size: 200% 100%;
  border-radius: 4px;
  animation: skeleton-loading 1.5s ease-in-out infinite;
}

.stat-skeleton__label {
  height: 14px;
  width: 60%;
}

.stat-skeleton__value {
  height: 36px;
  width: 40%;
}

@keyframes skeleton-loading {
  0% {
    background-position: 200% 0;
  }
  100% {
    background-position: -200% 0;
  }
}

/* Empty State */
.dashboard-stats__empty {
  margin-bottom: 20px;
}

.empty-state {
  text-align: center;
  padding: 60px 40px;
  background: linear-gradient(180deg, rgba(0, 0, 0, 0.4) 0%, rgba(0, 0, 0, 0.6) 100%);
  border-radius: 16px;
  border: 1px solid rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.05),
              0 8px 32px rgba(0, 0, 0, 0.3);
}

.empty-state__icon {
  width: 80px;
  height: 80px;
  color: var(--orange-primary);
  margin: 0 auto 24px;
  filter: drop-shadow(0 4px 12px rgba(255, 107, 53, 0.3));
}

.empty-state__title {
  font-size: 28px;
  font-weight: 700;
  color: var(--text-primary);
  margin-bottom: 12px;
  text-shadow: 0 2px 8px rgba(0, 0, 0, 0.5);
}

.empty-state__message {
  font-size: 16px;
  color: var(--text-secondary);
  margin-bottom: 32px;
  max-width: 600px;
  margin-left: auto;
  margin-right: auto;
  line-height: 1.6;
}

.empty-state__button {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  padding: 16px 32px;
  background: var(--orange-gradient);
  border: 2px solid var(--orange-primary);
  border-radius: 12px;
  color: white;
  font-weight: 700;
  font-size: 18px;
  text-decoration: none;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 8px 24px rgba(255, 107, 53, 0.4),
              0 0 40px rgba(255, 107, 53, 0.2),
              inset 0 1px 0 rgba(255, 255, 255, 0.3);
  position: relative;
  overflow: hidden;
}

.empty-state__button::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
  transition: left 0.5s ease;
}

.empty-state__button:hover::before {
  left: 100%;
}

.empty-state__button:hover {
  transform: translateY(-4px) scale(1.05);
  box-shadow: 0 12px 32px rgba(255, 107, 53, 0.5),
              0 0 60px rgba(255, 107, 53, 0.3),
              inset 0 1px 0 rgba(255, 255, 255, 0.4);
  border-color: var(--orange-light);
}

.empty-state__button:active {
  transform: translateY(-2px) scale(1.02);
}

.empty-state__button svg {
  width: 24px;
  height: 24px;
  stroke: currentColor;
  filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
}

@media (max-width: 768px) {
  .dashboard-stats__grid {
    grid-template-columns: 1fr;
  }

  .dashboard-stats__grid--empty {
    grid-template-columns: 1fr;
  }

  .dashboard-stats__title {
    font-size: 20px;
  }

  .empty-state {
    padding: 40px 24px;
  }

  .empty-state__icon {
    width: 64px;
    height: 64px;
  }

  .empty-state__title {
    font-size: 24px;
  }

  .empty-state__message {
    font-size: 14px;
  }

  .empty-state__button {
    padding: 14px 28px;
    font-size: 16px;
  }
}
</style>
