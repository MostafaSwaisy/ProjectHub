# Component Contracts: FormSelect & Register

**Date**: 2026-01-27
**Feature**: Role field validation and persistence

## FormSelect Component Contract

**Location**: `resources/js/components/auth/FormSelect.vue`
**Status**: ✅ Complete and Compliant - No changes required

### Props API

```typescript
interface FormSelectProps {
  modelValue: string;           // Current selected value (v-model binding)
  label: string;                // Form label text
  name: string;                 // HTML name attribute for accessibility
  options: Array<{              // Array of selectable options
    value: string;              // Option identifier
    label: string;              // Display text for option
    description?: string;       // Optional descriptive text (shown as title attribute)
  }>;
  required?: boolean;           // Default: false. Marks field as required
  disabled?: boolean;           // Default: false. Disables user interaction
  error?: string | null;        // Default: null. Error message to display
  placeholder?: string;         // Default: "Select an option". Disabled placeholder option
}
```

### Events API

```typescript
interface FormSelectEmits {
  'update:modelValue': (value: string) => void;  // v-model update on selection change
  'change': (value: string) => void;             // Change event with selected value
  'blur': () => void;                            // Blur event for validation triggers
}
```

### Template Slots

No named slots. Component is self-contained.

### Accessibility (WCAG 2.1 AA)

- ✅ Proper `<label>` with `for` attribute
- ✅ `aria-required="true"` when required prop is true
- ✅ `aria-invalid="true"` when error prop has value
- ✅ `aria-describedby` linking to error element ID when present
- ✅ Keyboard navigation: Tab to focus, Arrow keys to select, Enter to confirm
- ✅ Screen reader: Announces label, required status, error messages, option descriptions

### Contract Fulfillment for Role Field

| Requirement | Component Support | Evidence |
|-------------|------------------|----------|
| Display role options | ✅ `options` prop accepts array | Accepts Student/Instructor options |
| Show descriptions | ✅ `description` in options | Title attribute displays on hover |
| Required validation | ✅ `required` prop | Sets aria-required="true" |
| Error display | ✅ `error` prop | Shows error message when error is set |
| Selection persistence | ✅ v-model binding | Persists value through validation cycles |
| Accessibility | ✅ Full WCAG 2.1 AA support | Aria labels, descriptions, error linking |

### Usage Example

```vue
<FormSelect
  v-model="form.role"
  label="I am a..."
  name="role"
  placeholder="Select your role"
  :options="[
    { value: 'student', label: 'Student', description: 'I am taking courses on ProjectHub' },
    { value: 'instructor', label: 'Instructor', description: 'I am teaching courses on ProjectHub' }
  ]"
  required
  :error="errors.role"
  :disabled="isLoading"
  @blur="validateRole"
/>
```

---

## Register Component Contract

**Location**: `resources/js/pages/auth/Register.vue`
**Status**: ⚠️ Enhancement Required

### Current Implementation

The Register component wraps the registration form with:
- Form fields (name, email, password, password_confirmation)
- Role dropdown (FormSelect component)
- Submit button with loading state
- Error and success message display

### Required Enhancements

#### 1. validateRole() Function

```typescript
/**
 * Validate role field is not empty
 * Updates errors.value.role with error message or null
 */
const validateRole = () => {
  if (!form.value.role) {
    errors.value.role = 'Please select a role'
  } else {
    errors.value.role = null
  }
}
```

#### 2. isFormValid Computed Property

Must include role validation:

```typescript
const isFormValid = computed(() => {
  return (
    form.value.name &&
    form.value.email &&
    form.value.password &&
    form.value.password_confirmation &&
    form.value.role &&                  // ← ADD THIS
    !errors.value.name &&
    !errors.value.email &&
    !errors.value.password &&
    !errors.value.password_confirmation &&
    !errors.value.role                  // ← ADD THIS
  )
})
```

#### 3. handleRegister() Function

Current implementation already includes role in form data:
```typescript
await register(form.value)  // form includes role property
```

This is correct—no changes needed here.

### Data State

```typescript
const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role: '',                  // Already exists
})

const errors = ref({})       // Already exists, tracks errors by field

const roleOptions = [        // Already exists with descriptions
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
```

### Event Handlers

#### onBlur for Role Field

```typescript
// In FormSelect component:
@blur="validateRole"

// Triggers this validation function:
const validateRole = () => {
  if (!form.value.role) {
    errors.value.role = 'Please select a role'
  } else {
    errors.value.role = null
  }
}
```

### Form Submission Flow

```
1. User clicks "Create Account"
2. @submit.prevent="handleRegister"
3. Validate all fields:
   - validateName()
   - validateEmail()
   - validatePassword()
   - validatePasswordConfirmation()
   - validateRole()           // ← NEW
4. Check isFormValid.value
5. If valid, submit form with all fields including role
6. Show success message or error message based on API response
```

### Testing Contract

| Test Case | Input | Expected Output |
|-----------|-------|-----------------|
| Submit without role | Form filled except role empty | Error: "Please select a role", button disabled |
| Select role, validation error in other field | Role selected, email invalid | Shows email error, role value persists |
| Select role, then change it | Select Student, then Instructor | Instructor value displayed, retained |
| Submit with role | All valid fields including role | API call includes role in payload |

---

## Integration Contract

### FormSelect ↔ Register Integration

```
FormSelect Component
├─ Props: modelValue (v-model), error, disabled, options
├─ Events: update:modelValue, blur
└─ Behavior:
    ├─ Displays role options with descriptions
    ├─ Updates form.role on selection
    ├─ Triggers validateRole() on blur
    └─ Shows error message when errors.role is set

Register Component
├─ Data: form.role, errors.role, roleOptions
├─ Methods: validateRole(), handleRegister()
├─ Computed: isFormValid (includes role validation)
└─ Behavior:
    ├─ Initializes role = ''
    ├─ Validates role on blur and submit
    ├─ Includes role in registration request
    └─ Persists role through error cycles
```

### Data Flow

```
User selects role
    ↓
FormSelect emits update:modelValue
    ↓
form.role updates
    ↓
isFormValid recomputed (triggers submit button enable)
    ↓
User blurs field
    ↓
validateRole() runs
    ↓
errors.role updated
    ↓
FormSelect displays error if present
```

---

## API Contract

See [api.md](./api.md) for registration endpoint contract details.
