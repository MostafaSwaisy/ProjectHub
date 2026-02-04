<template>
  <div
    class="stat-card"
    :class="{ 'stat-card--alert': isAlert }"
  >
    <div class="stat-card__content">
      <div class="stat-card__header">
        <span v-if="icon" class="stat-card__icon" v-html="icon"></span>
        <h3 class="stat-card__label">{{ label }}</h3>
      </div>
      <div class="stat-card__value">
        {{ formattedValue }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  label: {
    type: String,
    required: true
  },
  value: {
    type: Number,
    required: true
  },
  icon: {
    type: String,
    default: null
  },
  alert: {
    type: Boolean,
    default: false
  }
});

const formattedValue = computed(() => {
  return props.value.toLocaleString();
});

const isAlert = computed(() => {
  return props.alert && props.value > 0;
});
</script>

<style scoped>
.stat-card {
  background: var(--glass-bg);
  border: 1px solid var(--glass-border);
  border-radius: 16px;
  padding: 24px;
  backdrop-filter: blur(10px);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2),
              inset 0 1px 0 rgba(255, 255, 255, 0.1);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3),
              0 0 20px rgba(255, 107, 53, 0.1),
              inset 0 1px 0 rgba(255, 255, 255, 0.15);
  border-color: var(--glass-border-hover);
}

.stat-card--alert {
  border-color: var(--red-primary);
  background: linear-gradient(135deg, rgba(220, 38, 38, 0.1), rgba(220, 38, 38, 0.05));
}

.stat-card--alert:hover {
  border-color: var(--red-secondary);
  box-shadow: 0 8px 24px rgba(220, 38, 38, 0.2);
}

.stat-card__content {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.stat-card__header {
  display: flex;
  align-items: center;
  gap: 8px;
}

.stat-card__icon {
  color: var(--orange-primary);
  font-size: 20px;
  line-height: 1;
  display: flex;
  align-items: center;
  justify-content: center;
}

.stat-card__icon :deep(svg) {
  width: 20px;
  height: 20px;
  stroke: currentColor;
}

.stat-card--alert .stat-card__icon {
  color: var(--red-primary);
}

.stat-card__label {
  font-size: 14px;
  font-weight: 500;
  color: var(--text-secondary);
  margin: 0;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.stat-card__value {
  font-size: 36px;
  font-weight: 700;
  color: var(--text-primary);
  line-height: 1;
}

.stat-card--alert .stat-card__value {
  color: var(--red-primary);
}

@media (max-width: 768px) {
  .stat-card {
    padding: 20px;
  }

  .stat-card__value {
    font-size: 28px;
  }
}
</style>
