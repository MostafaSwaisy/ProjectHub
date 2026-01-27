<template>
  <AuthCard title="Create your account" subtitle="Join ProjectHub today">
    <ErrorMessage v-if="error" :message="error" @dismiss="clearError" />

    <SuccessMessage
      v-if="successMessage"
      title="Registration successful!"
      :message="successMessage"
      :auto-dismiss-ms="3000"
      @dismiss="handleSuccessDismiss"
    />

    <form v-if="!successMessage" @submit.prevent="handleRegister" class="space-y-5">
      <FormField
        v-model="form.name"
        label="Full Name"
        name="name"
        type="text"
        placeholder="John Doe"
        autocomplete="name"
        required
        :error="errors.name"
        :disabled="isLoading"
        @blur="validateName"
      />

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

      <PasswordInput
        v-model="form.password"
        label="Password"
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

      <FormSelect
        v-model="form.role"
        label="I am a..."
        name="role"
        placeholder="Select your role"
        :options="roleOptions"
        required
        :error="errors.role"
        :disabled="isLoading"
        @blur="validateRole"
      />

      <SubmitButton :loading="isLoading" :disabled="!isFormValid">
        Create Account
        <template #loading>
          <span>Creating account...</span>
        </template>
      </SubmitButton>
    </form>

    <template #footer>
      <p class="text-center text-sm text-gray-600">
        Already have an account?
        <router-link to="/auth/login" class="text-indigo-600 hover:text-indigo-700 font-medium">
          Sign in
        </router-link>
      </p>
    </template>
  </AuthCard>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '@/composables/useAuth'
import { validators } from '@/composables/useFormValidation'
import AuthCard from '@/components/auth/AuthCard.vue'
import FormField from '@/components/auth/FormField.vue'
import PasswordInput from '@/components/auth/PasswordInput.vue'
import FormSelect from '@/components/auth/FormSelect.vue'
import SubmitButton from '@/components/auth/SubmitButton.vue'
import ErrorMessage from '@/components/auth/ErrorMessage.vue'
import SuccessMessage from '@/components/auth/SuccessMessage.vue'

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

const roleOptions = [
  {
    value: 'student',
    label: 'Student',
    description: 'I am taking courses on ProjectHub',
  },
  {
    value: 'instructor',
    label: 'Instructor',
    description: 'I am teaching courses on ProjectHub',
  },
]

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
  const error = validators.email(form.value.email)
  if (!form.value.email) {
    errors.value.email = 'Email is required'
  } else {
    errors.value.email = error
  }
}

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
