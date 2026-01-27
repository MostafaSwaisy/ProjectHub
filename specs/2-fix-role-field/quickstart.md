# Quick Start Guide: Role Field Implementation

**Date**: 2026-01-27
**Feature**: Fix role field validation in registration form
**Target Users**: Developers implementing the feature

## Overview

This guide helps you understand and implement the role field validation and persistence feature for the registration form. The feature ensures users select a role (Student or Instructor) during registration with proper validation and error messaging.

## 5-Minute Context

**What's changing**: The registration form's role dropdown needs:
1. Proper validation (error if empty)
2. Error message display ("Please select a role")
3. Selection persistence through validation errors
4. Integration with form submit logic

**Files you'll modify**:
- `resources/js/pages/auth/Register.vue` - Add validateRole() and update isFormValid

**Files you won't change**:
- `resources/js/components/auth/FormSelect.vue` - Already complete
- Backend registration controller - Already accepts role

**Why it matters**: Without proper role validation, users can submit incomplete registrations, causing confusion in the system about their learning path.

## Data Model Quick Reference

### Form State (in Register.vue)

```javascript
// What the user enters
form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role: '',  // ← This is what we're validating
})

// Validation errors
errors = ref({
  // ... other fields
  role: null  // Error message or null
})

// Available role options
const roleOptions = [
  { value: 'student', label: 'Student', description: 'I am taking courses on ProjectHub' },
  { value: 'instructor', label: 'Instructor', description: 'I am teaching courses on ProjectHub' }
]
```

### Component Contract (FormSelect)

The FormSelect component **already supports everything you need**:

```vue
<FormSelect
  v-model="form.role"           <!-- Two-way binding to form.role -->
  label="I am a..."             <!-- Field label -->
  name="role"                   <!-- HTML name attribute -->
  placeholder="Select your role"
  :options="roleOptions"        <!-- Array of options with descriptions -->
  required                      <!-- Mark as required field -->
  :error="errors.role"          <!-- Show error if errors.role has value -->
  :disabled="isLoading"         <!-- Disable while submitting -->
  @blur="validateRole"          <!-- Trigger validation on blur -->
/>
```

## Implementation Checklist

### Step 1: Add validateRole() Function

In `resources/js/pages/auth/Register.vue`, add this function:

```javascript
const validateRole = () => {
  if (!form.value.role) {
    errors.value.role = 'Please select a role'
  } else {
    errors.value.role = null
  }
}
```

**Where to add**: Near the other validation functions (validateName, validateEmail, etc.)

**When it runs**:
- User blurs the role field (@blur event)
- Form submission (in handleRegister)

### Step 2: Update isFormValid Computed

Modify the isFormValid computed property to include role:

```javascript
const isFormValid = computed(() => {
  return (
    form.value.name &&
    form.value.email &&
    form.value.password &&
    form.value.password_confirmation &&
    form.value.role &&           // ← ADD THIS LINE
    !errors.value.name &&
    !errors.value.email &&
    !errors.value.password &&
    !errors.value.password_confirmation &&
    !errors.value.role            // ← ADD THIS LINE
  )
})
```

**Impact**: Submit button will now be disabled until role is selected AND no validation errors exist.

### Step 3: Update handleRegister() to Validate Role

Modify the handleRegister function to call validateRole:

```javascript
const handleRegister = async () => {
  // Validate all fields
  validateName()
  validateEmail()
  validatePassword()
  validatePasswordConfirmation()
  validateRole()  // ← ADD THIS LINE

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
```

## Testing Your Implementation

### Test Case 1: Empty Role Field

1. Open http://localhost:8000/auth/register
2. Fill name, email, password, password_confirmation
3. Leave role empty
4. Click "Create Account"
5. **Expected**: Error message "Please select a role" appears below role field, button remains disabled

### Test Case 2: Select Role

1. Open http://localhost:8000/auth/register
2. Fill all fields
3. Click on role dropdown
4. Select "Student"
5. **Expected**: Dropdown shows "Student" selected, submit button becomes enabled (if all other fields valid)

### Test Case 3: Role Persists Through Error

1. Open http://localhost:8000/auth/register
2. Select role "Instructor"
3. Fill name, email, password
4. Enter wrong password confirmation (doesn't match)
5. Click "Create Account"
6. **Expected**: Error message shows for password, but role field still shows "Instructor" selected

### Test Case 4: Submit with Role

1. Open http://localhost:8000/auth/register
2. Fill all fields correctly with role selected
3. Click "Create Account"
4. **Expected**: Form submits, shows success message, redirects to dashboard

## Common Issues & Troubleshooting

### Issue: Submit button stays disabled even with role selected

**Check**:
1. Is role field value set? → Check if form.role updates when you select
2. Are there other validation errors? → Check errors ref in Vue DevTools
3. Is validateRole being called? → Add console.log in validateRole function

**Fix**: Ensure isFormValid includes both `form.value.role &&` and `!errors.value.role`

### Issue: Error message doesn't disappear after selecting role

**Check**:
1. Is validateRole called again after selection? → It should run on blur or on change
2. Is errors.role actually being set to null? → Check in DevTools console

**Fix**: Add validateRole call on @change event in addition to @blur if needed

### Issue: Role value is lost after page refresh

**Check**:
1. This is expected behavior—form state is local to component
2. User would need to re-select after refresh

**Note**: This is normal and acceptable per design

## API Integration

Your registration form already includes role in the submission:

```javascript
await register(form.value)  // form.value includes role property
```

The backend expects:

```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "SecurePass123",
  "password_confirmation": "SecurePass123",
  "role": "student"  // Backend validates this is 'student' or 'instructor'
}
```

**What the backend does**:
1. Validates role is 'student' or 'instructor'
2. Maps string to role_id (student → 3, instructor → 2)
3. Creates user with role_id in database
4. Returns success or validation error

## File Structure Reference

```
resources/js/
├── pages/auth/
│   └── Register.vue              ← MODIFY THIS
├── components/auth/
│   ├── FormSelect.vue            ← NO CHANGES (already complete)
│   ├── AuthCard.vue              ← NO CHANGES
│   └── SubmitButton.vue          ← NO CHANGES
└── composables/
    └── useAuth.js                ← NO CHANGES (register already works)
```

## Success Criteria Verification

After implementation, verify these work:

- ✅ Role field shows error "Please select a role" when user tries to submit without selecting
- ✅ Submit button is disabled while role is empty
- ✅ Submit button becomes enabled when role is selected
- ✅ Role value persists when other field validation errors occur
- ✅ Role value is included in API submission
- ✅ Form redirect and success message work after successful registration

## Next Steps

1. Implement the 3 steps above in Register.vue
2. Run through the 4 test cases
3. Check browser console for any errors
4. Verify all validation messages display correctly
5. Test with different role selections

## Getting Help

- **Component API**: See `contracts/components.md` for FormSelect full contract
- **API Details**: See `contracts/api.md` for registration endpoint details
- **Data Model**: See `data-model.md` for complete form state structure
- **Full Spec**: See `spec.md` for complete requirements and user stories
