<template>
  <!-- T021: Modern Register page with animated background and glassmorphic design -->
  <div class="register-container">
    <!-- Animated background with floating orbs -->
    <AnimatedBackground />

    <!-- Main content -->
    <div class="register-content">
      <!-- Register card -->
      <div class="register-card">
        <!-- Header -->
        <div class="register-header">
          <h1 class="register-title">Create your account</h1>
          <p class="register-subtitle">Join ProjectHub today</p>
        </div>

        <!-- Error message -->
        <div v-if="error" class="error-banner">
          <span>{{ error }}</span>
          <button @click="clearError" class="error-close">✕</button>
        </div>

        <!-- Success message -->
        <div v-if="successMessage" class="success-banner">
          <span>{{ successMessage }}</span>
          <button @click="handleSuccessDismiss" class="success-close">✕</button>
        </div>

        <!-- Form -->
        <form v-if="!successMessage" @submit.prevent="handleRegister" class="register-form">
          <!-- Name field -->
          <div class="form-group">
            <label for="name" class="form-label">Full Name</label>
            <input
              id="name"
              v-model="form.name"
              type="text"
              placeholder="John Doe"
              autocomplete="name"
              required
              class="form-input"
              :disabled="isLoading"
              @blur="validateName"
            />
            <span v-if="errors.name" class="form-error">{{ errors.name }}</span>
          </div>

          <!-- Email field -->
          <div class="form-group">
            <label for="email" class="form-label">Email Address</label>
            <input
              id="email"
              v-model="form.email"
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

          <!-- Password field -->
          <div class="form-group">
            <label for="password" class="form-label">Password</label>
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

          <!-- Role selection field -->
          <div class="form-group">
            <label for="role" class="form-label">I am a...</label>
            <select
              id="role"
              v-model="form.role"
              required
              class="form-input form-select"
              :disabled="isLoading"
              @blur="validateRole"
            >
              <option value="">Select your role</option>
              <option value="3">Student - I am taking courses on ProjectHub</option>
              <option value="2">Instructor - I am teaching courses on ProjectHub</option>
            </select>
            <span v-if="errors.role" class="form-error">{{ errors.role }}</span>
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
            {{ isLoading ? 'Creating account...' : 'Create Account' }}
          </Button>
        </form>

        <!-- Footer -->
        <p class="register-footer">
          Already have an account?
          <router-link to="/auth/login" class="signin-link">Sign in</router-link>
        </p>

        <!-- Social signup (T030) -->
        <div class="social-divider">
          <span>Or continue with</span>
        </div>
        <div class="social-buttons">
          <button type="button" class="social-button" disabled title="Coming soon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.834 8.207 11.472.6.111.82-.261.82-.577 0-.285-.011-1.04-.017-2.04-3.338.724-4.042-1.61-4.042-1.61-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.73.083-.73 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222 0 1.604-.015 2.896-.015 3.286 0 .319.217.694.823.576 4.765-1.635 8.206-6.167 8.206-11.471 0-6.627-5.373-12-12-12z"/>
            </svg>
            GitHub
          </button>
          <button type="button" class="social-button" disabled title="Coming soon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
              <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
              <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
              <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
              <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
            </svg>
            Google
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '@/composables/useAuth'
import AnimatedBackground from '@/components/shared/AnimatedBackground.vue'
import Button from '@/components/shared/Button.vue'
import PasswordStrengthIndicator from '@/components/auth/PasswordStrengthIndicator.vue'

const router = useRouter()
const { register, isLoading, error, clearError } = useAuth()

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role: '',
})

const errors = ref({})
const successMessage = ref('')

const isFormValid = computed(() => {
  return (
    form.value.name &&
    form.value.email &&
    form.value.password &&
    form.value.password_confirmation &&
    form.value.role &&
    !errors.value.name &&
    !errors.value.email &&
    !errors.value.password &&
    !errors.value.password_confirmation &&
    !errors.value.role
  )
})

const validateName = () => {
  if (!form.value.name) {
    errors.value.name = 'Name is required'
  } else if (form.value.name.length < 2) {
    errors.value.name = 'Name must be at least 2 characters'
  } else {
    errors.value.name = null
  }
}

const validateEmail = () => {
  if (!form.value.email) {
    errors.value.email = 'Email is required'
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.email)) {
    errors.value.email = 'Please enter a valid email address'
  } else {
    errors.value.email = null
  }
}

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

const validateRole = () => {
  if (!form.value.role) {
    errors.value.role = 'Please select a role'
  } else {
    errors.value.role = null
  }
}

const handleRegister = async () => {
  // Validate all fields
  validateName()
  validateEmail()
  validatePassword()
  validatePasswordConfirmation()
  validateRole()

  if (!isFormValid.value) {
    return
  }

  try {
    await register(form.value)
    successMessage.value = 'Welcome to ProjectHub! Your account has been created successfully.'
  } catch (err) {
    console.error('Registration error:', err)
  }
}

const handleSuccessDismiss = () => {
  router.push('/dashboard')
}
</script>

<style scoped>
/* T021: Modern Register page styles */

.register-container {
  position: relative;
  width: 100%;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: var(--black-primary);
  overflow: hidden;
}

.register-content {
  position: relative;
  z-index: 10;
  width: 100%;
  max-width: 500px;
  padding: var(--spacing-md);
}

.register-card {
  background: rgba(21, 21, 21, 0.8);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: var(--radius-xl);
  padding: var(--spacing-2xl);
  box-shadow: var(--shadow-lg);
  animation: slideUp 0.6s ease-out;
}

.register-header {
  margin-bottom: var(--spacing-lg);
  text-align: center;
}

.register-title {
  font-size: var(--text-2xl);
  font-weight: var(--font-bold);
  color: var(--text-primary);
  margin: 0 0 var(--spacing-sm) 0;
}

.register-subtitle {
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

.register-form {
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

.form-select {
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23E5E7EB' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 1rem center;
  padding-right: 2.5rem;
}

.form-error {
  font-size: var(--text-xs);
  color: var(--red-primary);
  animation: shake 0.4s ease-out;
}

.register-footer {
  text-align: center;
  font-size: var(--text-sm);
  color: var(--text-secondary);
  margin-top: var(--spacing-lg);
}

.signin-link {
  color: var(--orange-primary);
  text-decoration: none;
  font-weight: var(--font-medium);
  transition: color var(--transition-normal);
}

.signin-link:hover {
  color: var(--orange-light);
}

.social-divider {
  display: flex;
  align-items: center;
  gap: var(--spacing-md);
  margin-top: var(--spacing-lg);
  font-size: var(--text-xs);
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.social-divider::before,
.social-divider::after {
  content: '';
  flex: 1;
  height: 1px;
  background-color: rgba(255, 255, 255, 0.1);
}

.social-buttons {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: var(--spacing-md);
  margin-top: var(--spacing-lg);
}

.social-button {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: var(--spacing-sm);
  background-color: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: var(--radius-md);
  color: var(--text-secondary);
  padding: var(--spacing-md);
  font-size: var(--text-sm);
  font-weight: var(--font-medium);
  cursor: pointer;
  transition: all var(--transition-normal);
  min-height: 44px;
}

.social-button:hover:not(:disabled) {
  background-color: rgba(255, 255, 255, 0.08);
  border-color: rgba(255, 255, 255, 0.2);
  color: var(--text-primary);
}

.social-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
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
  .register-content {
    max-width: 100%;
  }

  .register-card {
    padding: var(--spacing-lg);
    margin: var(--spacing-md);
  }

  .register-title {
    font-size: var(--text-xl);
  }

  .social-buttons {
    grid-template-columns: 1fr;
  }
}
</style>
