import { reactive, computed } from 'vue'

export function useFormValidation() {
  const state = reactive({
    data: {},
    errors: {},
    touched: {},
    loading: false,
  })

  const hasErrors = computed(() => {
    return Object.values(state.errors).some((error) => !!error)
  })

  const setFieldValue = (field, value) => {
    state.data[field] = value
  }

  const setFieldError = (field, error) => {
    state.errors[field] = error
  }

  const markFieldTouched = (field) => {
    state.touched[field] = true
  }

  const clearErrors = () => {
    state.errors = {}
  }

  const clearError = (field) => {
    state.errors[field] = null
  }

  const setLoading = (loading) => {
    state.loading = loading
  }

  const reset = () => {
    state.data = {}
    state.errors = {}
    state.touched = {}
    state.loading = false
  }

  return {
    state,
    hasErrors,
    setFieldValue,
    setFieldError,
    markFieldTouched,
    clearErrors,
    clearError,
    setLoading,
    reset,
  }
}

// Common validation functions
export const validators = {
  required: (value) => {
    if (!value || (typeof value === 'string' && !value.trim())) {
      return 'This field is required'
    }
    return null
  },

  email: (value) => {
    if (!value) return null
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    return emailRegex.test(value) ? null : 'Please enter a valid email address'
  },

  minLength: (min) => (value) => {
    if (!value) return null
    return value.length >= min ? null : `Minimum ${min} characters required`
  },

  password: (value) => {
    if (!value) return 'Password is required'
    if (value.length < 8) return 'Password must be at least 8 characters'
    if (!/[0-9]/.test(value)) return 'Password must contain at least one number'
    if (!/[a-zA-Z]/.test(value)) return 'Password must contain at least one letter'
    return null
  },

  passwordConfirmation: (password) => (confirmation) => {
    if (!confirmation) return 'Password confirmation is required'
    if (confirmation !== password) return 'Passwords do not match'
    return null
  },

  checkPasswordRequirements: (value) => {
    return {
      minLength: value.length >= 8,
      hasNumber: /[0-9]/.test(value),
      hasLetter: /[a-zA-Z]/.test(value),
    }
  },
}
