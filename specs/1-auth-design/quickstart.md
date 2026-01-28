# Quick Start Guide: Authentication Pages Components

**Feature**: Improve Authentication Pages Design
**Date**: 2026-01-27

## Developer Setup

### Prerequisites
- Vue 3 project (ProjectHub already has this)
- Tailwind CSS 4 (already configured)
- Node.js 18+ and npm

### Installation

No additional npm packages needed. The components use:
- Vue 3 (built-in)
- Tailwind CSS (already configured)
- Existing `useAuth` composable

```bash
# Already installed in ProjectHub
npm install

# Build frontend during development
npm run dev

# Build for production
npm run build
```

## Component Usage Examples

### 1. Using AuthCard as Page Wrapper

```vue
<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
    <AuthCard title="Sign in to your account" subtitle="Welcome back">
      <!-- Form content here -->

      <template #footer>
        <p class="text-center text-sm text-gray-600 mt-6">
          Don't have an account?
          <router-link to="/auth/register" class="text-indigo-600 hover:text-indigo-700">
            Create account
          </router-link>
        </p>
      </template>
    </AuthCard>
  </div>
</template>

<script setup>
import AuthCard from '@/components/auth/AuthCard.vue'
</script>
```

### 2. Using FormField for Text Input

```vue
<template>
  <form @submit.prevent="handleSubmit" class="space-y-5">
    <FormField
      v-model="form.email"
      label="Email Address"
      name="email"
      type="email"
      placeholder="you@example.com"
      required
      :error="errors.email"
      @blur="validateEmail"
    />

    <button type="submit">Sign In</button>
  </form>
</template>

<script setup>
import { ref } from 'vue'
import FormField from '@/components/auth/FormField.vue'

const form = ref({
  email: ''
})

const errors = ref({})

const validateEmail = () => {
  if (!form.value.email) {
    errors.value.email = 'Email is required'
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.email)) {
    errors.value.email = 'Please enter a valid email'
  } else {
    errors.value.email = null
  }
}

const handleSubmit = async () => {
  validateEmail()
  if (!errors.value.email) {
    console.log('Form valid, submit:', form.value)
  }
}
</script>
```

### 3. Using PasswordInput with Requirements

```vue
<template>
  <PasswordInput
    v-model="form.password"
    label="Password"
    name="password"
    placeholder="••••••••"
    required
    :error="errors.password"
    helperText="Password must contain at least 8 characters and one number"
    @input="validatePassword"
  />
</template>

<script setup>
import { ref, reactive } from 'vue'
import PasswordInput from '@/components/auth/PasswordInput.vue'

const form = ref({
  password: ''
})

const errors = ref({})

const passwordRequirements = reactive({
  minLength: false,
  hasNumber: false,
  hasLetter: false
})

const validatePassword = () => {
  const password = form.value.password
  passwordRequirements.minLength = password.length >= 8
  passwordRequirements.hasNumber = /[0-9]/.test(password)
  passwordRequirements.hasLetter = /[a-zA-Z]/.test(password)

  const isValid = passwordRequirements.minLength &&
                  passwordRequirements.hasNumber &&
                  passwordRequirements.hasLetter

  if (!isValid) {
    errors.value.password = 'Password does not meet requirements'
  } else {
    errors.value.password = null
  }
}
</script>
```

### 4. Using SubmitButton with Loading State

```vue
<template>
  <form @submit.prevent="handleLogin" class="space-y-5">
    <FormField v-model="form.email" label="Email" name="email" type="email" />
    <PasswordInput v-model="form.password" label="Password" name="password" />

    <SubmitButton :loading="isLoading" :disabled="!isFormValid">
      Sign In
    </SubmitButton>
  </form>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useAuth } from '@/composables/useAuth'
import SubmitButton from '@/components/auth/SubmitButton.vue'

const { login, isLoading } = useAuth()

const form = ref({
  email: '',
  password: ''
})

const isFormValid = computed(() => {
  return form.value.email && form.value.password
})

const handleLogin = async () => {
  try {
    await login(form.value)
    // useAuth composable handles navigation
  } catch (err) {
    console.error('Login failed:', err)
  }
}
</script>
```

### 5. Using ErrorMessage for Validation Errors

```vue
<template>
  <div class="space-y-5">
    <ErrorMessage v-if="generalError" :message="generalError" />

    <FormField
      v-model="form.email"
      label="Email"
      name="email"
      type="email"
      :error="fieldErrors.email"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import ErrorMessage from '@/components/auth/ErrorMessage.vue'
import FormField from '@/components/auth/FormField.vue'

const generalError = ref('')
const fieldErrors = ref({
  email: null
})

const handleFormSubmit = async () => {
  try {
    // Attempt submission
  } catch (error) {
    if (error.response?.status === 422) {
      // Field-level errors
      fieldErrors.value = error.response.data.errors
    } else {
      // General error
      generalError.value = error.response?.data?.message || 'An error occurred'
    }
  }
}
</script>
```

### 6. Using SuccessMessage for Confirmations

```vue
<template>
  <div class="space-y-5">
    <SuccessMessage
      v-if="passwordReset"
      title="Success!"
      message="Your password has been reset. You can now sign in with your new password."
      @dismiss="goToLogin"
    />

    <form v-else @submit.prevent="handleReset">
      <!-- Reset form -->
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import SuccessMessage from '@/components/auth/SuccessMessage.vue'

const router = useRouter()
const passwordReset = ref(false)

const goToLogin = () => {
  router.push('/auth/login')
}
</script>
```

## Form Validation Composable

### Creating a reusable form validation composable

```javascript
// composables/useFormValidation.js
import { reactive, computed } from 'vue'

export function useFormValidation() {
  const state = reactive({
    data: {},
    errors: {},
    touched: {},
    loading: false
  })

  const hasErrors = computed(() => {
    return Object.values(state.errors).some(error => !!error)
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

  const setLoading = (loading) => {
    state.loading = loading
  }

  return {
    state,
    hasErrors,
    setFieldValue,
    setFieldError,
    markFieldTouched,
    clearErrors,
    setLoading
  }
}
```

### Using the validation composable

```vue
<script setup>
import { useFormValidation } from '@/composables/useFormValidation'

const form = useFormValidation()

const validateEmail = () => {
  const email = form.state.data.email
  if (!email) {
    form.setFieldError('email', 'Email is required')
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    form.setFieldError('email', 'Please enter a valid email')
  } else {
    form.setFieldError('email', null)
  }
  form.markFieldTouched('email')
}
</script>
```

## Testing Components

### Unit Test Example with Vitest

```javascript
// tests/unit/components/auth/FormField.spec.js
import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import FormField from '@/components/auth/FormField.vue'

describe('FormField.vue', () => {
  it('renders label and input', () => {
    const wrapper = mount(FormField, {
      props: {
        label: 'Email',
        name: 'email',
        modelValue: ''
      }
    })

    expect(wrapper.find('label').text()).toBe('Email')
    expect(wrapper.find('input[name="email"]').exists()).toBe(true)
  })

  it('emits update:modelValue on input', async () => {
    const wrapper = mount(FormField, {
      props: {
        label: 'Email',
        name: 'email',
        modelValue: ''
      }
    })

    await wrapper.find('input').setValue('test@example.com')

    expect(wrapper.emitted('update:modelValue')).toBeTruthy()
    expect(wrapper.emitted('update:modelValue')[0]).toEqual(['test@example.com'])
  })

  it('displays error message when provided', () => {
    const wrapper = mount(FormField, {
      props: {
        label: 'Email',
        name: 'email',
        modelValue: '',
        error: 'Invalid email format'
      }
    })

    expect(wrapper.text()).toContain('Invalid email format')
  })

  it('shows required indicator for required fields', () => {
    const wrapper = mount(FormField, {
      props: {
        label: 'Email',
        name: 'email',
        modelValue: '',
        required: true
      }
    })

    expect(wrapper.find('label').text()).toContain('*')
  })
})
```

### Integration Test Example

```javascript
// tests/integration/auth-flow.spec.js
import { describe, it, expect, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { createRouter, createMemoryHistory } from 'vue-router'
import Login from '@/pages/auth/Login.vue'

describe('Login Flow', () => {
  let wrapper

  beforeEach(() => {
    const router = createRouter({
      history: createMemoryHistory(),
      routes: [
        { path: '/auth/login', component: Login },
        { path: '/dashboard', component: { template: '<div>Dashboard</div>' } }
      ]
    })

    wrapper = mount(Login, {
      global: {
        plugins: [router]
      }
    })
  })

  it('completes login with valid credentials', async () => {
    // Fill in form
    await wrapper.find('input[type="email"]').setValue('test@example.com')
    await wrapper.find('input[type="password"]').setValue('password123')

    // Submit form
    await wrapper.find('form').trigger('submit')

    // Wait for API response and navigation
    await wrapper.vm.$nextTick()

    // Verify navigation to dashboard
    expect(wrapper.vm.$route.path).toBe('/dashboard')
  })
})
```

## Tailwind CSS Styling Tips

### Button Variants

```vue
<!-- Primary button -->
<button class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
  Sign In
</button>

<!-- Secondary button -->
<button class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-300">
  Cancel
</button>

<!-- Outline button -->
<button class="border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50">
  Learn More
</button>
```

### Form Input States

```vue
<!-- Normal input -->
<input class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500">

<!-- Error state -->
<input class="border border-red-500 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-500">

<!-- Disabled state -->
<input class="bg-gray-100 border border-gray-300 rounded-lg px-3 py-2 cursor-not-allowed">
```

### Responsive Design

```vue
<!-- Stack on mobile, side-by-side on desktop -->
<div class="flex flex-col md:flex-row gap-4">
  <div class="flex-1">
    <!-- Left content -->
  </div>
  <div class="flex-1">
    <!-- Right content -->
  </div>
</div>
```

## Accessibility Testing

### Using axe DevTools
1. Install axe DevTools browser extension
2. Open the auth page
3. Click axe DevTools icon
4. Run scan
5. Fix any violations marked as "critical" or "serious"

### Manual Keyboard Navigation
1. Reload page
2. Press Tab to navigate through form fields
3. Verify visual focus indicator is clearly visible
4. Press Enter on button to submit
5. Verify error messages appear in proper order

### Screen Reader Testing
1. Use NVDA (Windows) or JAWS (Windows) or VoiceOver (Mac)
2. Navigate page with screen reader
3. Verify:
   - Page title is announced
   - Form labels are associated with inputs
   - Error messages are announced immediately
   - Button purpose is clear

## Debugging Tips

### Common Issues

**Issue**: Form field not updating
```javascript
// ✗ Wrong - v-model not set up
<input value="test" @input="form.email = $event.target.value" />

// ✓ Correct - use v-model
<FormField v-model="form.email" />
```

**Issue**: Validation running but not showing errors
```javascript
// ✗ Wrong - error prop not set
<FormField v-model="form.email" label="Email" />

// ✓ Correct - pass error to component
<FormField v-model="form.email" label="Email" :error="errors.email" />
```

**Issue**: Button not submitting form
```javascript
// ✗ Wrong - type="button" prevents submission
<SubmitButton type="button">Submit</SubmitButton>

// ✓ Correct - use type="submit"
<SubmitButton type="submit">Submit</SubmitButton>
```

## Next Steps

1. **Create Components**: Start with `AuthCard.vue` as the foundation
2. **Add FormField**: Build the most reusable component next
3. **Add PasswordInput**: Extend FormField for password-specific needs
4. **Refactor Pages**: Update Login.vue using the new components
5. **Test Thoroughly**: Add unit and integration tests
6. **Performance Check**: Verify bundle size and load times
7. **Accessibility Audit**: Run axe and manual keyboard testing
8. **Deploy**: Merge PR to main branch

## Resources

- Vue 3 Guide: https://vuejs.org/guide/
- Tailwind CSS Docs: https://tailwindcss.com/docs
- WCAG Guidelines: https://www.w3.org/WAI/WCAG21/quickref/
- Vue Test Utils: https://test-utils.vuejs.org/
- Vitest: https://vitest.dev/
