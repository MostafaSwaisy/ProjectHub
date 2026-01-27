# Data Model: Authentication Pages Components

**Feature**: Improve Authentication Pages Design
**Date**: 2026-01-27

## Overview

This document defines the component structure, form state, and data flow for the improved authentication pages. All components are reusable Vue 3 single-file components that work together to create consistent, accessible auth experiences.

## Component Hierarchy

```
AuthPages (route-level)
├── Login.vue
│   ├── AuthCard
│   │   ├── FormField (for email)
│   │   ├── PasswordInput (for password)
│   │   ├── SubmitButton
│   │   ├── ErrorMessage
│   │   └── Links (Forgot Password, Register)
│   └── useAuth composable
│       └── API: POST /api/auth/login
│
├── Register.vue
│   ├── AuthCard
│   │   ├── FormField (for name)
│   │   ├── FormField (for email)
│   │   ├── PasswordInput (for password)
│   │   ├── PasswordInput (for password confirm)
│   │   ├── FormSelect (for role)
│   │   ├── SubmitButton
│   │   ├── ErrorMessage
│   │   └── Links (Back to Login)
│   └── useAuth composable
│       └── API: POST /api/auth/register
│
├── ForgotPassword.vue
│   ├── AuthCard
│   │   ├── FormField (for email)
│   │   ├── SubmitButton
│   │   ├── SuccessMessage
│   │   ├── ErrorMessage
│   │   └── Links (Back to Login)
│   └── useAuth composable
│       └── API: POST /api/auth/password/email
│
└── ResetPassword.vue
    ├── AuthCard
    │   ├── FormField (for email - pre-filled)
    │   ├── PasswordInput (for password)
    │   ├── PasswordInput (for password confirm)
    │   ├── SubmitButton
    │   ├── SuccessMessage
    │   ├── ErrorMessage
    │   └── Links (Back to Login)
    └── useAuth composable
        └── API: POST /api/auth/password/reset
```

## Core Components

### 1. AuthCard (Page Wrapper)

**Purpose**: Reusable wrapper for all auth page content, ensuring consistent styling and layout

**Props**:
```typescript
interface AuthCardProps {
  title: string           // Page title (e.g., "Sign in to your account")
  subtitle?: string       // Optional subtitle or description
  headingLevel?: 'h1'|'h2'|'h3'  // Semantic heading level (default: 'h1')
}
```

**Slots**:
- `default`: Main content (form)
- `footer`: Footer content (links, help text)

**Styling**:
- Max width: 28rem (448px)
- Centered on page
- Card background with shadow
- Responsive padding (8px mobile, 32px desktop)

**Accessibility**:
- `headingLevel` prop allows semantic HTML hierarchy
- Role="main" on card container
- Focus management for page load

---

### 2. FormField (Generic Input)

**Purpose**: Reusable form field with label, input, error message, and helper text

**Props**:
```typescript
interface FormFieldProps {
  modelValue: string      // Form field value (v-model)
  label: string          // Field label
  name: string           // HTML name attribute
  type?: string          // Input type (text, email, etc., default: 'text')
  placeholder?: string   // Placeholder text
  required?: boolean     // Required field indicator
  disabled?: boolean     // Disabled state
  error?: string         // Error message if validation failed
  helperText?: string    // Helper text below input
  autocomplete?: string  // Autocomplete attribute value
}
```

**Events**:
```typescript
emit('update:modelValue', value: string)  // v-model binding
emit('blur')                               // Field lost focus
emit('change', value: string)              // Value changed
```

**Features**:
- Label with required indicator (*)
- Input with proper focus states
- Error message with role="alert"
- Helper text in gray (xs, 12px)
- Accessible aria-describedby linking

**Validation States**:
- Normal: Gray border
- Focus: Blue border (indigo-500), outline ring
- Error: Red border, red text error message
- Disabled: Gray background, disabled cursor

**Accessibility**:
- `<label htmlFor>` properly linked
- `aria-required` when required
- `aria-invalid` when error present
- `aria-describedby` links to error/helper messages

---

### 3. PasswordInput (Password Field with Toggle)

**Purpose**: Specialized input for password fields with show/hide toggle

**Props**:
```typescript
interface PasswordInputProps {
  modelValue: string       // Password value (v-model)
  label: string           // Field label
  name: string            // HTML name attribute
  placeholder?: string    // Placeholder text
  required?: boolean      // Required field indicator
  disabled?: boolean      // Disabled state
  error?: string          // Error message
  helperText?: string     // Helper text
  showStrengthMeter?: boolean  // Show password requirements (default: true)
}
```

**Events**: Same as FormField

**Features**:
- Toggle button to show/hide password
- Password strength indicator (optional)
- Displays requirements: 8+ chars, 1 number, 1 letter
- All FormField features (label, error, accessibility)

**Accessibility**:
- Toggle button has aria-label (e.g., "Show password")
- Visual feedback on button state
- All FormField aria attributes apply

---

### 4. FormSelect (Dropdown Field)

**Purpose**: Dropdown/select field for role selection in Register

**Props**:
```typescript
interface FormSelectProps {
  modelValue: string     // Selected value (v-model)
  label: string         // Field label
  name: string          // HTML name attribute
  options: Array<{      // Available options
    value: string
    label: string
    description?: string  // Role description
  }>
  required?: boolean     // Required field indicator
  disabled?: boolean     // Disabled state
  error?: string        // Error message
}
```

**Events**:
```typescript
emit('update:modelValue', value: string)
emit('change', value: string)
```

**Features**:
- Select dropdown with accessible options
- Option descriptions via title attribute
- Proper label association
- Validation state styling

---

### 5. SubmitButton (Action Button)

**Purpose**: Form submission button with loading state

**Props**:
```typescript
interface SubmitButtonProps {
  loading?: boolean      // Loading state (disables and shows spinner)
  disabled?: boolean     // Disabled state
  type?: 'button'|'submit'  // Button type (default: 'submit')
  variant?: 'primary'|'secondary'|'outline'  // Style variant
  fullWidth?: boolean    // Fill width of parent (default: true)
}
```

**Slots**:
- `default`: Button text
- `loading`: Loading text/spinner

**Features**:
- Loading spinner during submission
- Disabled state prevents double-submission
- Keyboard accessible (Enter key)
- Touch-friendly size (44px minimum height)

**Accessibility**:
- `aria-busy="true"` when loading
- `aria-disabled="true"` when disabled
- Clear button text (e.g., "Sign in", not "Submit")

---

### 6. ErrorMessage (Alert Display)

**Purpose**: Display form errors in accessible alert box

**Props**:
```typescript
interface ErrorMessageProps {
  message: string        // Error message text
  title?: string        // Optional error title
}
```

**Features**:
- Role="alert" for screen readers
- Red styling (background + border)
- Clear visual distinction from form fields
- Dismissible (optional X button)

**Accessibility**:
- Announces immediately to screen readers
- High color contrast (red #EF4444)
- Clear, specific error messages

---

### 7. SuccessMessage (Status Display)

**Purpose**: Display success messages and confirmations

**Props**:
```typescript
interface SuccessMessageProps {
  message: string       // Success message text
  title?: string       // Optional success title
  dismissible?: boolean  // Allow user to dismiss
}
```

**Features**:
- Role="status" for screen readers
- Green styling (background + border)
- Icon indication (checkmark)
- Optional auto-dismiss timer

---

## Form State Management

### Login Form State

```typescript
interface LoginFormState {
  email: string              // User's email
  password: string           // User's password
  errors: {
    email?: string           // Email validation error
    password?: string        // Password validation error
    general?: string         // General auth error
  }
  loading: boolean           // Form submission loading state
  touched: {
    email: boolean           // Field touched state
    password: boolean
  }
}
```

### Register Form State

```typescript
interface RegisterFormState {
  name: string               // User's full name
  email: string              // User's email
  password: string           // User's password
  passwordConfirmation: string  // Password confirmation
  role: 'student'|'instructor'  // Selected role
  errors: {
    name?: string
    email?: string
    password?: string
    passwordConfirmation?: string
    role?: string
    general?: string
  }
  loading: boolean
  touched: {
    name: boolean
    email: boolean
    password: boolean
    passwordConfirmation: boolean
    role: boolean
  }
  passwordValid: {            // Password requirement validation
    minLength: boolean        // 8+ characters
    hasNumber: boolean        // At least one number
    hasLetter: boolean        // At least one letter
  }
}
```

### ForgotPassword Form State

```typescript
interface ForgotPasswordFormState {
  email: string              // Email to send reset link to
  errors: {
    email?: string
    general?: string
  }
  loading: boolean
  touched: {
    email: boolean
  }
  submitted: boolean         // Form successfully submitted
}
```

### ResetPassword Form State

```typescript
interface ResetPasswordFormState {
  token: string              // Reset token from URL
  email: string              // User's email (from URL)
  password: string           // New password
  passwordConfirmation: string  // Confirm new password
  errors: {
    password?: string
    passwordConfirmation?: string
    token?: string
    general?: string
  }
  loading: boolean
  touched: {
    password: boolean
    passwordConfirmation: boolean
  }
  passwordValid: {            // Same as Register
    minLength: boolean
    hasNumber: boolean
    hasLetter: boolean
  }
  submitted: boolean         // Password reset successful
}
```

## Validation Rules

### Email Validation
- Required field
- Valid email format (HTML5 + regex)
- Tested against `/^[^\s@]+@[^\s@]+\.[^\s@]+$/`

### Password Validation (Register/Reset)
- Required field
- Minimum 8 characters
- At least one number (0-9)
- At least one letter (a-zA-Z)
- Display requirements as user types

### Password Confirmation Validation
- Required field
- Must match password field exactly
- Real-time comparison as user types

### Name Validation
- Required field
- Minimum 2 characters
- No special characters (except space, hyphen, apostrophe)

### Role Validation
- Required field
- Must be 'student' or 'instructor'
- Display description on selection

## API Integration

### Login Request/Response

```typescript
// Request
POST /api/auth/login
{
  email: string
  password: string
}

// Response (200)
{
  token: string
  user: {
    id: number
    email: string
    name: string
    role: { name: 'student'|'instructor'|'admin' }
  }
}

// Error Response (401)
{
  message: "Invalid credentials"
}
```

### Register Request/Response

```typescript
// Request
POST /api/auth/register
{
  name: string
  email: string
  password: string
  password_confirmation: string
  role: 'student'|'instructor'
}

// Response (201)
{
  token: string
  user: { ... }  // Same structure as login
}

// Error Response (422)
{
  message: string
  errors: {
    email?: string[]
    password?: string[]
    role?: string[]
  }
}
```

### Password Reset Requests

```typescript
// Forgot Password Request
POST /api/auth/password/email
{
  email: string
}

// Response (200)
{
  message: "Password reset email sent"
}

// Reset Password Request
POST /api/auth/password/reset
{
  token: string
  email: string
  password: string
  password_confirmation: string
}

// Response (200)
{
  message: "Password reset successfully"
}
```

## Accessibility Checklist per Component

### AuthCard
- ✅ Main landmark role
- ✅ Proper heading hierarchy
- ✅ Sufficient color contrast (4.5:1)
- ✅ Focus visible on all interactive elements

### FormField
- ✅ Label properly associated with input
- ✅ aria-required for required fields
- ✅ aria-invalid for fields with errors
- ✅ aria-describedby linking error/helper messages
- ✅ Placeholder not used as label replacement
- ✅ Focus outline visible (2px minimum)

### PasswordInput
- ✅ All FormField requirements
- ✅ Toggle button has aria-label
- ✅ Button state clearly indicated visually

### SubmitButton
- ✅ Clear button text
- ✅ aria-busy during loading
- ✅ aria-disabled when disabled
- ✅ Keyboard accessible (Enter key)
- ✅ Touch target 44px minimum

### ErrorMessage
- ✅ role="alert"
- ✅ Color contrast 4.5:1
- ✅ Not only conveyed by color
- ✅ Scrolled into view when appears

### SuccessMessage
- ✅ role="status"
- ✅ Color contrast 4.5:1
- ✅ Not only conveyed by color

## Performance Considerations

### Component Size Targets
- Individual component: < 3KB minified + gzipped
- Page (complete Vue component): < 10KB minified + gzipped

### Bundle Optimization
- Components are tree-shakeable
- Form validation logic extracted to composable
- No unused Tailwind CSS utilities included

### Rendering Performance
- No unnecessary re-renders (proper v-model usage)
- Validation debounced if needed (but typically instant)
- Form submission prevents multiple clicks
