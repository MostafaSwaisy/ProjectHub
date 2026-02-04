<template>
  <div class="password-strength">
    <!-- Strength bars -->
    <div class="strength-bars">
      <div
        v-for="n in 4"
        :key="n"
        :class="['strength-bar', { 'strength-bar-filled': n <= strength.score }]"
        :style="{ backgroundColor: strength.color }"
      />
    </div>

    <!-- Strength label -->
    <span :class="['strength-label', `strength-${strength.label.toLowerCase()}`]">
      {{ strength.label }}
    </span>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  password: {
    type: String,
    default: ''
  }
})

/**
 * Calculate password strength based on criteria
 * 0: Weak (< 6 chars)
 * 1: Fair (6-10 chars, lowercase)
 * 2: Good (10+ chars, lowercase + uppercase)
 * 3: Strong (10+ chars, lowercase + uppercase + numbers)
 * 4: Very Strong (10+ chars, lowercase + uppercase + numbers + special)
 */
const strength = computed(() => {
  const pwd = props.password
  let score = 0
  let label = 'Weak'
  let color = '#EF4444' // Red

  if (pwd.length === 0) {
    return { score: 0, label: 'None', color: '#6B7280' }
  }

  // Length check
  if (pwd.length >= 6) score++
  if (pwd.length >= 10) score++

  // Character variety checks
  if (/[a-z]/.test(pwd) && /[A-Z]/.test(pwd)) score++
  if (/[0-9]/.test(pwd)) score++
  if (/[^a-zA-Z0-9]/.test(pwd)) score++

  // Cap at 4
  score = Math.min(score, 4)

  // Determine label and color
  if (score === 0 || pwd.length === 0) {
    label = 'Weak'
    color = '#EF4444' // Red
  } else if (score === 1) {
    label = 'Fair'
    color = '#F59E0B' // Amber
  } else if (score === 2) {
    label = 'Good'
    color = '#3B82F6' // Blue
  } else if (score === 3) {
    label = 'Strong'
    color = '#22C55E' // Green
  } else {
    label = 'Very Strong'
    color = '#10B981' // Emerald
  }

  return { score, label, color }
})
</script>

<style scoped>
.password-strength {
  display: flex;
  align-items: center;
  gap: var(--spacing-md);
}

.strength-bars {
  display: flex;
  gap: 4px;
  flex: 1;
}

.strength-bar {
  flex: 1;
  height: 4px;
  background-color: rgba(255, 255, 255, 0.1);
  border-radius: 2px;
  transition: all var(--transition-fast);
}

.strength-bar-filled {
  background-color: currentColor;
  box-shadow: 0 0 8px currentColor;
}

.strength-label {
  font-size: var(--text-xs);
  font-weight: var(--font-semibold);
  white-space: nowrap;
  min-width: 80px;
  text-align: right;
}

.strength-weak {
  color: #EF4444;
}

.strength-fair {
  color: #F59E0B;
}

.strength-good {
  color: #3B82F6;
}

.strength-strong {
  color: #22C55E;
}

.strength-very-strong {
  color: #10B981;
}

.strength-none {
  color: var(--text-muted);
}
</style>
