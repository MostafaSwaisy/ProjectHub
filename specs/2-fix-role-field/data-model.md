# Data Model Design: Fix Role Field in Registration Form

**Date**: 2026-01-27
**Feature**: Role field validation and persistence in registration form

## Form Data Model

### RegistrationForm (Vue Component State)

```javascript
// Form data object
form = ref({
  name: String,              // User's full name
  email: String,             // User's email address
  password: String,          // User's password
  password_confirmation: String, // Password confirmation
  role: String,              // Selected role: 'student' | 'instructor' | ''
})

// Validation error tracking
errors = ref({
  name: null | String,       // Error message or null
  email: null | String,      // Error message or null
  password: null | String,   // Error message or null
  password_confirmation: null | String, // Error message or null
  role: null | String,       // Error message or null
})

// Form state
isLoading = Boolean,         // API request in progress
successMessage = String,     // Success feedback message
```

### Role Data Structure

```javascript
// Available role options
const roleOptions = [
  {
    value: 'student',         // Internal identifier for database
    label: 'Student',         // Display name in dropdown
    description: 'I am taking courses on ProjectHub' // Helpful description
  },
  {
    value: 'instructor',      // Internal identifier for database
    label: 'Instructor',      // Display name in dropdown
    description: 'I am teaching courses on ProjectHub' // Helpful description
  }
]
```

### Form Submission Payload

```javascript
// Data sent to POST /api/auth/register
{
  name: String,              // Required, 2+ characters
  email: String,             // Required, valid email format
  password: String,          // Required, 8+ chars, 1 number, 1 letter
  password_confirmation: String, // Required, must match password
  role: String,              // Required, must be 'student' or 'instructor'
}
```

## Validation Rules

### Role Field Validation

| Rule | Condition | Error Message | Trigger |
|------|-----------|---------------|---------|
| Required | role is empty or null | "Please select a role" | On blur, on submit |
| Valid Value | role is 'student' or 'instructor' | "Invalid role selection" | On change (backend only) |

### Validation Timing

- **On Blur**: Check if role field is empty → show error
- **On Change**: Update form state, recheck form validity
- **On Submit**: Validate all fields including role → proceed or show errors
- **Persistence**: Form state (including role) persists until form is reset or page is reloaded

## State Transitions

### Form State Flow

```
[Empty Form]
    ↓
[User fills name, email, password, selects role]
    ↓
[All fields valid] → [Submit enabled]
    ↓
[User submits]
    ↓
[isLoading = true, form disabled]
    ↓
API Response
├─ Success → [Success message shown] → [Redirect to dashboard]
└─ Error → [Error message shown, form re-enabled, role retained]
```

### Role Selection Lifecycle

1. **Initial**: role = ''
2. **User selects role**: role = 'student' | 'instructor'
3. **Field loses focus**: Validation runs, error set if empty
4. **User corrects other field**: Role value persists
5. **Form submitted**: role included in payload
6. **Success**: Form reset, user redirected
7. **Error**: Form state retained, user can fix errors and resubmit

## Database Schema (Backend Reference)

### users table
```sql
CREATE TABLE users (
  id INTEGER PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  role_id INTEGER NOT NULL,  -- Foreign key to roles table
  email_verified_at TIMESTAMP,
  FOREIGN KEY (role_id) REFERENCES roles(id)
);

CREATE TABLE roles (
  id INTEGER PRIMARY KEY,
  name VARCHAR(255) UNIQUE NOT NULL,  -- 'admin', 'instructor', 'student'
  description TEXT
);
```

### Role Options (Seeded Data)
- ID 1: 'admin' (internal use)
- ID 2: 'instructor' (user-selectable)
- ID 3: 'student' (user-selectable)

## Key Entities

### Role Entity
- **Definition**: A categorization of user type with specific permissions and features
- **Values**: 'student' (learner), 'instructor' (teacher)
- **Storage**: Mapped to role_id foreign key in users table
- **Constraints**: Must be one of the predefined role values
- **Business Rule**: Each user must have exactly one role assigned at registration

### RegistrationForm Entity
- **Definition**: The client-side form state containing user input for new account creation
- **Scope**: Lives only in the Vue component during form interaction
- **Persistence**: Persists until form submission succeeds or page is reloaded
- **Validation**: All fields validated before backend submission
- **Error Handling**: Field-level error tracking with user-facing messages

## Computed Properties

### Form Validity
```javascript
const isFormValid = computed(() => {
  return (
    form.value.name &&                        // Has name
    form.value.email &&                       // Has email
    form.value.password &&                    // Has password
    form.value.password_confirmation &&       // Has confirmation
    form.value.role &&                        // HAS ROLE (NEW)
    !errors.value.name &&                     // No name error
    !errors.value.email &&                    // No email error
    !errors.value.password &&                 // No password error
    !errors.value.password_confirmation &&    // No confirmation error
    !errors.value.role                        // No role error (NEW)
  )
})
```

## Change Summary (From Current State)

**Current State**: Role dropdown exists but validation may be incomplete
**New State**: Complete role validation with:
1. Required field validation (show error if empty)
2. Error message display ("Please select a role")
3. Selection persistence across validation cycles
4. Form validity check includes role field
5. Submit button disabled until role is selected

**Files Affected**:
- resources/js/pages/auth/Register.vue (validation logic, error handling)
- No schema changes (role_id already in database)
- No new components needed (FormSelect already complete)
