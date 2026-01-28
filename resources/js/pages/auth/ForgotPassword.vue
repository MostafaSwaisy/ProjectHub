<template>
  <AuthCard title="Reset your password" subtitle="We'll help you get back into your account">
    <ErrorMessage v-if="error" :message="error" @dismiss="clearError" />

    <SuccessMessage
      v-if="successMessage"
      title="Check your email"
      :message="successMessage"
      dismissible
      @dismiss="goToLogin"
    />

    <form v-if="!successMessage" @submit.prevent="handleSubmit" class="space-y-5">
      <p class="text-sm text-gray-600">
        Enter your email address and we'll send you a link to reset your password.
      </p>

      <FormField
        v-model="email"
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

      <SubmitButton :loading="isLoading" :disabled="!email">
        Send Reset Link
        <template #loading>
          <span>Sending...</span>
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
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '@/composables/useAuth'
import { validators } from '@/composables/useFormValidation'
import AuthCard from '@/components/auth/AuthCard.vue'
import FormField from '@/components/auth/FormField.vue'
import SubmitButton from '@/components/auth/SubmitButton.vue'
import ErrorMessage from '@/components/auth/ErrorMessage.vue'
import SuccessMessage from '@/components/auth/SuccessMessage.vue'

const router = useRouter()
const { requestPasswordReset, isLoading, error, clearError } = useAuth()

const email = ref('')
const errors = ref({})
const successMessage = ref('')

const validateEmail = () => {
  const error = validators.email(email.value)
  if (!email.value) {
    errors.value.email = 'Email is required'
  } else {
    errors.value.email = error
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
