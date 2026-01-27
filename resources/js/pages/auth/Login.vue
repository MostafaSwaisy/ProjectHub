<template>
  <AuthCard title="Sign in to your account" subtitle="Welcome back">
    <ErrorMessage v-if="error" :message="error" @dismiss="clearError" />

    <form @submit.prevent="handleLogin" class="space-y-5">
      <FormField
        v-model="form.email"
        label="Email Address"
        name="email"
        type="email"
        placeholder="you@example.com"
        autocomplete="email"
        required
        :error="errors.email"
        :disabled="isLoading"
        @blur="validateEmail"
      />

      <div class="relative">
        <PasswordInput
          v-model="form.password"
          label="Password"
          name="password"
          placeholder="••••••••"
          autocomplete="current-password"
          required
          :error="errors.password"
          :disabled="isLoading"
          :show-requirements="false"
          @blur="validatePassword"
        />
        <router-link
          to="/auth/forgot-password"
          class="text-sm text-indigo-600 hover:text-indigo-700 font-medium absolute right-0 -top-8"
        >
          Forgot password?
        </router-link>
      </div>

      <SubmitButton :loading="isLoading" :disabled="!isFormValid">
        Sign In
        <template #loading>
          <span>Signing in...</span>
        </template>
      </SubmitButton>
    </form>

    <template #footer>
      <p class="text-center text-sm text-gray-600">
        Don't have an account?
        <router-link to="/auth/register" class="text-indigo-600 hover:text-indigo-700 font-medium">
          Create account
        </router-link>
      </p>
    </template>
  </AuthCard>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '@/composables/useAuth'
import AuthCard from '@/components/auth/AuthCard.vue'
import FormField from '@/components/auth/FormField.vue'
import PasswordInput from '@/components/auth/PasswordInput.vue'
import SubmitButton from '@/components/auth/SubmitButton.vue'
import ErrorMessage from '@/components/auth/ErrorMessage.vue'

const router = useRouter()
const { login, isLoading, error, clearError } = useAuth()

const form = ref({
  email: '',
  password: '',
})

const errors = ref({})

const isFormValid = computed(() => {
  return form.value.email && form.value.password && !errors.value.email && !errors.value.password
})

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
  } else {
    errors.value.password = null
  }
}

const handleLogin = async () => {
  validateEmail()
  validatePassword()

  if (!isFormValid.value) {
    return
  }

  try {
    await login(form.value)
    router.push('/dashboard')
  } catch (err) {
    console.error('Login error:', err)
  }
}
</script>
