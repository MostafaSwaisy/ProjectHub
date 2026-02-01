<template>
  <!-- T023: Modern Forgot Password page with animated background and glassmorphic design -->
  <div class="forgotpwd-container">
    <!-- Animated background with floating orbs -->
    <AnimatedBackground />

    <!-- Main content -->
    <div class="forgotpwd-content">
      <!-- Forgot password card -->
      <div class="forgotpwd-card">
        <!-- Header -->
        <div class="forgotpwd-header">
          <h1 class="forgotpwd-title">Reset your password</h1>
          <p class="forgotpwd-subtitle">We'll help you get back into your account</p>
        </div>

        <!-- Error message -->
        <div v-if="error" class="error-banner">
          <span>{{ error }}</span>
          <button @click="clearError" class="error-close">✕</button>
        </div>

        <!-- Success message -->
        <div v-if="successMessage" class="success-banner">
          <span>{{ successMessage }}</span>
          <button @click="goToLogin" class="success-close">✕</button>
        </div>

        <!-- Form -->
        <form v-if="!successMessage" @submit.prevent="handleSubmit" class="forgotpwd-form">
          <!-- Help text -->
          <p class="help-text">
            Enter your email address and we'll send you a link to reset your password.
          </p>

          <!-- Email field -->
          <div class="form-group">
            <label for="email" class="form-label">Email Address</label>
            <input
              id="email"
              v-model="email"
              type="email"
              placeholder="you@example.com"
              autocomplete="email"
              required
              class="form-input"
              :disabled="isLoading"
              @blur="validateEmail"
            />
            <span v-if="errors.email" class="form-error">{{ errors.email }}</span>
          </div>

          <!-- Submit button -->
          <Button
            type="submit"
            variant="primary"
            size="lg"
            :loading="isLoading"
            :disabled="!email || isLoading"
            full-width
          >
            {{ isLoading ? 'Sending...' : 'Send Reset Link' }}
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
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '@/composables/useAuth'
import AnimatedBackground from '@/components/shared/AnimatedBackground.vue'
import Button from '@/components/shared/Button.vue'

const router = useRouter()
const { requestPasswordReset, isLoading, error, clearError } = useAuth()

const email = ref('')
const errors = ref({})
const successMessage = ref('')

const validateEmail = () => {
  if (!email.value) {
    errors.value.email = 'Email is required'
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
    errors.value.email = 'Please enter a valid email address'
  } else {
    errors.value.email = null
  }
}

const handleSubmit = async () => {
  validateEmail()

  if (errors.value.email) {
    return
  }

  try {
    await requestPasswordReset(email.value)
    successMessage.value =
      'If an account exists with this email, you will receive a password reset link shortly. Please check your inbox.'
  } catch (err) {
    console.error('Password reset request error:', err)
  }
}

const goToLogin = () => {
  router.push('/auth/login')
}
</script>

<style scoped>
/* T023: Modern Forgot Password page styles */

.forgotpwd-container {
  position: relative;
  width: 100%;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: var(--black-primary);
  overflow: hidden;
}

.forgotpwd-content {
  position: relative;
  z-index: 10;
  width: 100%;
  max-width: 420px;
  padding: var(--spacing-md);
}

.forgotpwd-card {
  background: rgba(21, 21, 21, 0.8);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: var(--radius-xl);
  padding: var(--spacing-2xl);
  box-shadow: var(--shadow-lg);
  animation: slideUp 0.6s ease-out;
}

.forgotpwd-header {
  margin-bottom: var(--spacing-lg);
  text-align: center;
}

.forgotpwd-title {
  font-size: var(--text-2xl);
  font-weight: var(--font-bold);
  color: var(--text-primary);
  margin: 0 0 var(--spacing-sm) 0;
}

.forgotpwd-subtitle {
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

.help-text {
  font-size: var(--text-sm);
  color: var(--text-secondary);
  margin-bottom: var(--spacing-lg);
  line-height: var(--line-height-relaxed);
}

.forgotpwd-form {
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

.form-input::placeholder {
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
  .forgotpwd-content {
    max-width: 100%;
  }

  .forgotpwd-card {
    padding: var(--spacing-lg);
    margin: var(--spacing-md);
  }

  .forgotpwd-title {
    font-size: var(--text-xl);
  }
}
</style>
