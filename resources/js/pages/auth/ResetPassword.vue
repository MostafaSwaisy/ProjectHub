<template>
  <!-- T024: Modern Reset Password page with animated background and glassmorphic design -->
  <div class="resetpwd-container">
    <!-- Animated background with floating orbs -->
    <AnimatedBackground />

    <!-- Main content -->
    <div class="resetpwd-content">
      <!-- Reset password card -->
      <div class="resetpwd-card">
        <!-- Header -->
        <div class="resetpwd-header">
          <h1 class="resetpwd-title">Set your new password</h1>
          <p class="resetpwd-subtitle">Create a strong password to secure your account</p>
        </div>

        <!-- Error message -->
        <div v-if="error && !successMessage" class="error-banner">
          <span>{{ error }}</span>
          <button @click="clearError" class="error-close">✕</button>
        </div>

        <!-- Success message -->
        <div v-if="successMessage" class="success-banner">
          <span>{{ successMessage }}</span>
          <button @click="goToLogin" class="success-close">✕</button>
        </div>

        <!-- Form -->
        <form v-if="!successMessage" @submit.prevent="handleSubmit" class="resetpwd-form">
          <!-- Email field (read-only) -->
          <div class="form-group">
            <label for="email" class="form-label">Email Address</label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              placeholder="you@example.com"
              autocomplete="email"
              disabled
              class="form-input form-input-disabled"
            />
            <span class="form-helper">This is the email associated with your account</span>
          </div>

          <!-- Password field -->
          <div class="form-group">
            <label for="password" class="form-label">New Password</label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              placeholder="••••••••"
              autocomplete="new-password"
              required
              class="form-input"
              :disabled="isLoading"
              @blur="validatePassword"
              @input="validatePassword"
            />
            <span v-if="errors.password" class="form-error">{{ errors.password }}</span>
            <!-- Password strength indicator -->
            <PasswordStrengthIndicator :password="form.password" />
          </div>

          <!-- Confirm password field -->
          <div class="form-group">
            <label for="password-confirm" class="form-label">Confirm Password</label>
            <input
              id="password-confirm"
              v-model="form.password_confirmation"
              type="password"
              placeholder="••••••••"
              autocomplete="new-password"
              required
              class="form-input"
              :disabled="isLoading"
              @blur="validatePasswordConfirmation"
            />
            <span v-if="errors.password_confirmation" class="form-error">{{ errors.password_confirmation }}</span>
          </div>

          <!-- Submit button -->
          <Button
            type="submit"
            variant="primary"
            size="lg"
            :loading="isLoading"
            :disabled="!isFormValid || isLoading"
            full-width
          >
            {{ isLoading ? 'Resetting...' : 'Reset Password' }}
          </Button>
        </form>

        <!-- Footer -->
        <router-link to="/auth/login" class="back-link">
          ← Back to Sign In
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuth } from '@/composables/useAuth'
import AnimatedBackground from '@/components/shared/AnimatedBackground.vue'
import Button from '@/components/shared/Button.vue'
import PasswordStrengthIndicator from '@/components/auth/PasswordStrengthIndicator.vue'

const route = useRoute()
const router = useRouter()
const { resetPassword, isLoading, error, clearError } = useAuth()

const form = ref({
  token: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const errors = ref({})
const successMessage = ref('')

const isFormValid = computed(() => {
  return (
    form.value.email &&
    form.value.password &&
    form.value.password_confirmation &&
    !errors.value.password &&
    !errors.value.password_confirmation
  )
})

const validatePassword = () => {
  if (!form.value.password) {
    errors.value.password = 'Password is required'
  } else if (form.value.password.length < 8) {
    errors.value.password = 'Password must be at least 8 characters'
  } else {
    errors.value.password = null
  }
  // Also revalidate confirmation if it's filled
  if (form.value.password_confirmation) {
    validatePasswordConfirmation()
  }
}

const validatePasswordConfirmation = () => {
  if (!form.value.password_confirmation) {
    errors.value.password_confirmation = 'Password confirmation is required'
  } else if (form.value.password !== form.value.password_confirmation) {
    errors.value.password_confirmation = 'Passwords do not match'
  } else {
    errors.value.password_confirmation = null
  }
}

onMounted(() => {
  // Extract token and email from query parameters (T024)
  form.value.token = route.query.token || ''
  form.value.email = route.query.email || ''

  if (!form.value.token || !form.value.email) {
    error.value = 'Invalid reset link. Please request a new password reset.'
  }
})

const handleSubmit = async () => {
  validatePassword()
  validatePasswordConfirmation()

  if (!isFormValid.value) {
    return
  }

  try {
    await resetPassword(form.value)
    successMessage.value = 'Your password has been reset successfully. You can now sign in with your new password.'
  } catch (err) {
    console.error('Password reset error:', err)
  }
}

const goToLogin = () => {
  router.push('/auth/login')
}
</script>

<style scoped>
/* T024: Modern Reset Password page styles */

.resetpwd-container {
  position: relative;
  width: 100%;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: var(--black-primary);
  overflow: hidden;
}

.resetpwd-content {
  position: relative;
  z-index: 10;
  width: 100%;
  max-width: 480px;
  padding: var(--spacing-md);
}

.resetpwd-card {
  background: rgba(21, 21, 21, 0.8);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: var(--radius-xl);
  padding: var(--spacing-2xl);
  box-shadow: var(--shadow-lg);
  animation: slideUp 0.6s ease-out;
}

.resetpwd-header {
  margin-bottom: var(--spacing-lg);
  text-align: center;
}

.resetpwd-title {
  font-size: var(--text-2xl);
  font-weight: var(--font-bold);
  color: var(--text-primary);
  margin: 0 0 var(--spacing-sm) 0;
}

.resetpwd-subtitle {
  font-size: var(--text-sm);
  color: var(--text-secondary);
  margin: 0;
}

.error-banner,
.success-banner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-radius: var(--radius-md);
  padding: var(--spacing-md);
  margin-bottom: var(--spacing-lg);
  font-size: var(--text-sm);
}

.error-banner {
  background-color: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.3);
  color: #FCA5A5;
}

.success-banner {
  background-color: rgba(34, 197, 94, 0.1);
  border: 1px solid rgba(34, 197, 94, 0.3);
  color: #86EFAC;
}

.error-close,
.success-close {
  background: none;
  border: none;
  color: inherit;
  cursor: pointer;
  padding: 0;
  font-size: var(--text-lg);
}

.resetpwd-form {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-lg);
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-sm);
}

.form-label {
  font-size: var(--text-sm);
  font-weight: var(--font-medium);
  color: var(--text-primary);
}

.form-input {
  background-color: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: var(--radius-md);
  color: var(--text-primary);
  padding: 0.75rem 1rem;
  font-family: var(--font-family);
  font-size: var(--text-base);
  transition: all var(--transition-normal);
  min-height: 44px;
}

.form-input:focus {
  outline: none;
  border-color: var(--orange-primary);
  box-shadow: var(--shadow-orange);
  background-color: rgba(255, 255, 255, 0.08);
}

.form-input:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.form-input-disabled {
  opacity: 0.6;
}

.form-input::placeholder {
  color: var(--text-muted);
}

.form-helper {
  font-size: var(--text-xs);
  color: var(--text-muted);
}

.form-error {
  font-size: var(--text-xs);
  color: var(--red-primary);
  animation: shake 0.4s ease-out;
}

.back-link {
  display: block;
  text-align: center;
  margin-top: var(--spacing-lg);
  font-size: var(--text-sm);
  color: var(--orange-primary);
  text-decoration: none;
  font-weight: var(--font-medium);
  transition: color var(--transition-normal);
}

.back-link:hover {
  color: var(--orange-light);
}

/* Animations */
@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes shake {
  0%, 100% {
    transform: translateX(0);
  }
  25% {
    transform: translateX(-5px);
  }
  75% {
    transform: translateX(5px);
  }
}

/* Mobile optimization */
@media (max-width: 639px) {
  .resetpwd-content {
    max-width: 100%;
  }

  .resetpwd-card {
    padding: var(--spacing-lg);
    margin: var(--spacing-md);
  }

  .resetpwd-title {
    font-size: var(--text-xl);
  }
}
</style>
