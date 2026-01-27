<template>
  <AuthCard title="Set your new password" subtitle="Create a strong password to secure your account">
    <ErrorMessage v-if="error && !successMessage" :message="error" @dismiss="clearError" />

    <SuccessMessage
      v-if="successMessage"
      title="Password reset successful!"
      :message="successMessage"
      :auto-dismiss-ms="3000"
      @dismiss="goToLogin"
    />

    <form v-if="!successMessage" @submit.prevent="handleSubmit" class="space-y-5">
      <FormField
        v-model="form.email"
        label="Email Address"
        name="email"
        type="email"
        placeholder="you@example.com"
        autocomplete="email"
        required
        :error="errors.email"
        :disabled="true"
        helperText="This is the email associated with your account"
      />

      <PasswordInput
        v-model="form.password"
        label="New Password"
        name="password"
        placeholder="••••••••"
        autocomplete="new-password"
        required
        :error="errors.password"
        :disabled="isLoading"
        :show-requirements="true"
        @blur="validatePassword"
        @input="validatePassword"
      />

      <PasswordInput
        v-model="form.password_confirmation"
        label="Confirm Password"
        name="password_confirmation"
        placeholder="••••••••"
        autocomplete="new-password"
        required
        :error="errors.password_confirmation"
        :disabled="isLoading"
        :show-requirements="false"
        @blur="validatePasswordConfirmation"
      />

      <SubmitButton :loading="isLoading" :disabled="!isFormValid">
        Reset Password
        <template #loading>
          <span>Resetting...</span>
        </template>
      </SubmitButton>
    </form>

    <template #footer>
      <router-link to="/auth/login" class="block text-center text-sm text-indigo-600 hover:text-indigo-700 font-medium">
        ← Back to Sign In
      </router-link>
    </template>
  </AuthCard>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuth } from '@/composables/useAuth'
import { validators } from '@/composables/useFormValidation'
import AuthCard from '@/components/auth/AuthCard.vue'
import FormField from '@/components/auth/FormField.vue'
import PasswordInput from '@/components/auth/PasswordInput.vue'
import SubmitButton from '@/components/auth/SubmitButton.vue'
import ErrorMessage from '@/components/auth/ErrorMessage.vue'
import SuccessMessage from '@/components/auth/SuccessMessage.vue'

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
    !errors.value.email &&
    !errors.value.password &&
    !errors.value.password_confirmation
  )
})

const validatePassword = () => {
  const error = validators.password(form.value.password)
  errors.value.password = error
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
