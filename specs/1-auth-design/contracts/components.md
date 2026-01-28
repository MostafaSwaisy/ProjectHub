# Component API Contracts

**Feature**: Authentication Pages Design
**Date**: 2026-01-27

## Overview

This document defines the public API (props, events, slots) for all authentication components. These contracts ensure consistent component behavior and make testing straightforward.

---

## AuthCard

**Purpose**: Reusable wrapper for authentication page layouts

**Location**: `resources/js/components/auth/AuthCard.vue`

### Props

| Name | Type | Required | Default | Description |
|------|------|----------|---------|-------------|
| `title` | String | Yes | — | Page title (e.g., "Sign in to your account") |
| `subtitle` | String | No | undefined | Optional subtitle or description text |
| `headingLevel` | 'h1' \| 'h2' \| 'h3' | No | 'h1' | Semantic HTML heading level |

### Slots

| Name | Description | Fallback |
|------|-------------|----------|
| `default` | Main form content | Empty |
| `footer` | Footer content (links, help text) | Empty |

### CSS Classes Applied

- Container: `min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100`
- Card: `w-full max-w-md bg-white rounded-lg shadow-lg p-8`

### Example Usage

```vue
<AuthCard title="Sign In" subtitle="Welcome back">
  <!-- Form -->
  <template #footer>Footer content</template>
</AuthCard>
```

---

## FormField

**Purpose**: Generic text/email/number input field with label, error, and helper text

**Location**: `resources/js/components/auth/FormField.vue`

### Props

| Name | Type | Required | Default | Description |
|------|------|----------|---------|-------------|
| `modelValue` | String | Yes | — | Input value (v-model) |
| `label` | String | Yes | — | Field label text |
| `name` | String | Yes | — | HTML name attribute |
| `type` | String | No | 'text' | Input type (text, email, number, tel, url) |
| `placeholder` | String | No | undefined | Placeholder text |
| `required` | Boolean | No | false | Show required indicator (*) |
| `disabled` | Boolean | No | false | Disable the input |
| `error` | String | No | null | Error message to display |
| `helperText` | String | No | null | Helper text below input |
| `autocomplete` | String | No | undefined | HTML autocomplete attribute value |
| `min` | String \| Number | No | undefined | Minimum value (for number inputs) |
| `max` | String \| Number | No | undefined | Maximum value (for number inputs) |

### Events

| Name | Payload | Description |
|------|---------|-------------|
| `update:modelValue` | value: String | v-model update |
| `blur` | — | Input lost focus |
| `focus` | — | Input gained focus |
| `change` | value: String | Value changed |

### Accessibility

- `<label htmlFor>` automatically linked to input
- `aria-required="true"` when required
- `aria-invalid="true"` when error present
- `aria-describedby="[field]-error [field]-help"` when errors/helper present
- Focus outline visible (2px indigo ring)

### States

| State | Styling | Behavior |
|-------|---------|----------|
| Normal | Gray border, gray label | Input ready |
| Focus | Blue border, blue ring (2px) | User interacting |
| Error | Red border, red text | Validation failed |
| Disabled | Gray background, disabled cursor | Cannot interact |

### Example Usage

```vue
<FormField
  v-model="email"
  label="Email Address"
  name="email"
  type="email"
  placeholder="you@example.com"
  required
  :error="errors.email"
  helperText="We'll never share your email"
  @blur="validateEmail"
/>
```

---

## PasswordInput

**Purpose**: Password input field with show/hide toggle

**Location**: `resources/js/components/auth/PasswordInput.vue`

### Props

| Name | Type | Required | Default | Description |
|------|------|----------|---------|-------------|
| `modelValue` | String | Yes | — | Password value (v-model) |
| `label` | String | Yes | — | Field label text |
| `name` | String | Yes | — | HTML name attribute |
| `placeholder` | String | No | undefined | Placeholder text |
| `required` | Boolean | No | false | Show required indicator |
| `disabled` | Boolean | No | false | Disable the input |
| `error` | String | No | null | Error message |
| `helperText` | String | No | null | Helper text |
| `showRequirements` | Boolean | No | true | Show password requirements checklist |

### Events

| Name | Payload | Description |
|------|---------|-------------|
| `update:modelValue` | value: String | v-model update |
| `blur` | — | Input lost focus |
| `focus` | — | Input gained focus |
| `input` | value: String | User typing (for real-time validation) |

### Password Requirements (if showRequirements=true)

Display checklist showing:
- ✓ At least 8 characters (green when met, red when not)
- ✓ At least one number (0-9)
- ✓ At least one letter (a-zA-Z)

### Features

- Toggle button to show/hide password text
- Toggle button has aria-label (e.g., "Show password")
- Visual feedback when toggle is activated

### Example Usage

```vue
<PasswordInput
  v-model="password"
  label="Password"
  name="password"
  placeholder="••••••••"
  required
  :error="errors.password"
  @input="validatePassword"
/>
```

---

## FormSelect

**Purpose**: Dropdown/select field for role selection

**Location**: `resources/js/components/auth/FormSelect.vue`

### Props

| Name | Type | Required | Default | Description |
|------|------|----------|---------|-------------|
| `modelValue` | String | Yes | — | Selected value (v-model) |
| `label` | String | Yes | — | Field label |
| `name` | String | Yes | — | HTML name attribute |
| `options` | Array<{value, label, description?}> | Yes | — | Available options |
| `required` | Boolean | No | false | Required field indicator |
| `disabled` | Boolean | No | false | Disable the select |
| `error` | String | No | null | Error message |
| `placeholder` | String | No | 'Select an option' | Placeholder text |

### Events

| Name | Payload | Description |
|------|---------|-------------|
| `update:modelValue` | value: String | v-model update |
| `change` | value: String | Selection changed |
| `blur` | — | Select lost focus |

### Option Structure

```typescript
{
  value: string        // Internal value
  label: string        // Display text
  description?: string // Tooltip on hover (optional)
}
```

### Accessibility

- `aria-required` when required
- `aria-invalid` when error present
- Option descriptions available via title attribute

### Example Usage

```vue
<FormSelect
  v-model="role"
  label="I am a..."
  name="role"
  :options="[
    { value: 'student', label: 'Student', description: 'I am taking courses' },
    { value: 'instructor', label: 'Instructor', description: 'I am teaching courses' }
  ]"
  required
  :error="errors.role"
/>
```

---

## SubmitButton

**Purpose**: Form submission button with loading state

**Location**: `resources/js/components/auth/SubmitButton.vue`

### Props

| Name | Type | Required | Default | Description |
|------|------|----------|---------|-------------|
| `loading` | Boolean | No | false | Loading state (shows spinner, disables button) |
| `disabled` | Boolean | No | false | Disable the button |
| `type` | 'button' \| 'submit' | No | 'submit' | Button type |
| `variant` | 'primary' \| 'secondary' \| 'outline' | No | 'primary' | Style variant |
| `fullWidth` | Boolean | No | true | Fill width of parent container |

### Slots

| Name | Description |
|------|-------------|
| `default` | Button text |
| `loading` | Loading indicator/text (shown when loading=true) |

### Styling

| Variant | Colors | Hover |
|---------|--------|-------|
| `primary` | Indigo-600 background, white text | Indigo-700 |
| `secondary` | Gray-200 background, gray-800 text | Gray-300 |
| `outline` | Gray border, gray text | Gray-50 background |

### Touch Target

- Minimum 44px height (for mobile accessibility)

### States

| State | Behavior |
|-------|----------|
| Normal | Clickable, shows button text |
| Loading | Disabled, shows spinner + loading slot text |
| Disabled | Not clickable, grayed out |

### Example Usage

```vue
<SubmitButton :loading="isSubmitting" :disabled="!formValid">
  Sign In
  <template #loading>
    <span>Signing in...</span>
  </template>
</SubmitButton>
```

---

## ErrorMessage

**Purpose**: Display form validation errors in accessible alert

**Location**: `resources/js/components/auth/ErrorMessage.vue`

### Props

| Name | Type | Required | Default | Description |
|------|------|----------|---------|-------------|
| `message` | String | Yes | — | Error message text |
| `title` | String | No | 'Error' | Optional error title |
| `dismissible` | Boolean | No | true | Show dismiss button |

### Events

| Name | Payload | Description |
|------|---------|-------------|
| `dismiss` | — | User clicked dismiss button |

### Styling

- Background: Red-50 (#FEF2F2)
- Border: Red-200 (#FECACA)
- Text: Red-700 (#B91C1C)
- Color contrast: 4.5:1 (WCAG AA)

### Accessibility

- `role="alert"` - announces immediately to screen readers
- Border + text color (not color alone) conveys error

### Example Usage

```vue
<ErrorMessage
  v-if="generalError"
  title="Login Failed"
  :message="generalError"
  @dismiss="clearError"
/>
```

---

## SuccessMessage

**Purpose**: Display success confirmations in accessible status message

**Location**: `resources/js/components/auth/SuccessMessage.vue`

### Props

| Name | Type | Required | Default | Description |
|------|------|----------|---------|-------------|
| `message` | String | Yes | — | Success message text |
| `title` | String | No | 'Success' | Optional title |
| `dismissible` | Boolean | No | true | Show dismiss button |
| `autoDismissMs` | Number | No | null | Auto-dismiss after milliseconds (null = no auto-dismiss) |

### Events

| Name | Payload | Description |
|------|---------|-------------|
| `dismiss` | — | User clicked dismiss or auto-dismissed |

### Styling

- Background: Green-50 (#F0FDF4)
- Border: Green-200 (#BBEF63)
- Text: Green-700 (#15803D)
- Icon: Green-500 (checkmark)
- Color contrast: 4.5:1 (WCAG AA)

### Accessibility

- `role="status"` - announces to screen readers
- Border + icon + text (not color alone) conveys success

### Example Usage

```vue
<SuccessMessage
  v-if="passwordReset"
  title="Password Reset"
  message="Your password has been successfully reset."
  :autoDismissMs="3000"
  @dismiss="goToLogin"
/>
```

---

## Testing Contracts

### Component Testing Checklist

For each component, tests should verify:

- ✅ Props are passed correctly
- ✅ v-model binding works bidirectionally
- ✅ Events are emitted with correct payload
- ✅ Slots render correctly
- ✅ Error states display properly
- ✅ Disabled states work
- ✅ Accessibility attributes are present
- ✅ Focus states are visible
- ✅ Keyboard navigation works

### Example Component Test

```javascript
import { mount } from '@vue/test-utils'
import FormField from '@/components/auth/FormField.vue'

describe('FormField', () => {
  it('accepts props', () => {
    const wrapper = mount(FormField, {
      props: {
        label: 'Email',
        name: 'email',
        modelValue: '',
        type: 'email',
        required: true
      }
    })

    expect(wrapper.find('label').text()).toContain('Email')
    expect(wrapper.find('input').attributes('type')).toBe('email')
    expect(wrapper.find('label').text()).toContain('*') // required indicator
  })

  it('emits update:modelValue', async () => {
    const wrapper = mount(FormField, {
      props: {
        label: 'Email',
        name: 'email',
        modelValue: ''
      }
    })

    await wrapper.find('input').setValue('test@example.com')
    expect(wrapper.emitted('update:modelValue')[0]).toEqual(['test@example.com'])
  })

  it('displays error state', () => {
    const wrapper = mount(FormField, {
      props: {
        label: 'Email',
        name: 'email',
        modelValue: '',
        error: 'Invalid email'
      }
    })

    expect(wrapper.find('input').classes()).toContain('border-red-500')
    expect(wrapper.text()).toContain('Invalid email')
  })
})
```
