# Research & Analysis: Fix Role Field in Registration Form

**Date**: 2026-01-27
**Feature**: Fix role field validation, error messages, and selection persistence in registration form
**Status**: Complete

## Research Questions Addressed

### Q1: How is form state managed in Vue 3 registration form?

**Decision**: Use reactive refs with inline validation functions (existing pattern)

**Rationale**:
- Current implementation uses Vue 3 composition API with `ref()` for form data and `errors` ref for validation state
- Pinia store exists for auth state (login, logout, API calls) but form state is local to component
- This is appropriate for single-page form state
- Inline validation functions (validateEmail, validatePassword, etc.) are already working in Register.vue

**Alternatives Considered**:
- Pinia store for form state: Would add overhead for a single form. Local refs are simpler and sufficient.
- Form libraries (Vee-Validate, Formik): Would require additional dependency. Existing pattern is lightweight and maintainable.

**Implementation Impact**: No changes to state management architecture needed. Use existing pattern for role validation.

---

### Q2: What is the existing registration API contract?

**Decision**: Use existing POST /api/auth/register endpoint with role field

**Rationale**:
- Verified in routes/api.php that RegisterController handles POST /api/auth/register
- Controller already expects role parameter (visible from previous Laravel setup)
- Database users table has role_id column
- No API contract changes needed—role field already accepted

**Alternatives Considered**:
- Create separate role assignment endpoint: Unnecessary—existing endpoint handles it
- Modify registration response structure: No need—role already assigned on backend

**Implementation Impact**: Backend is ready. Frontend just needs to ensure role is always included in registration payload.

---

### Q3: Does FormSelect component fully support the required functionality?

**Decision**: Yes. Component is complete and ready to use as-is.

**Rationale**:
- FormSelect already accepts modelValue (v-model binding)
- Already supports error prop for error display
- Already supports required prop for accessibility (aria-required)
- Already supports options array with label and description properties
- Already handles @blur and @change events
- Already provides aria-describedby for screen reader error linking

**Evidence**:
- Reviewed resources/js/components/auth/FormSelect.vue
- All required props are defined and working
- Error display and accessibility attributes are implemented

**Alternatives Considered**:
- Custom role selector: Unnecessary—FormSelect handles all requirements
- Native HTML select: FormSelect already provides styled, accessible select

**Implementation Impact**: No component changes required. Just need to ensure it's used correctly in Register.vue with proper validation.

---

### Q4: What is the current role data structure?

**Decision**: Two hardcoded role options in Register.vue with descriptions

**Rationale**:
- Reviewed resources/js/pages/auth/Register.vue line 125-136
- roleOptions array defined with Student and Instructor objects
- Each has value, label, and description properties
- Matches FormSelect component's expected structure (value, label, description)

**Alternatives Considered**:
- Load roles from backend API: Not needed—two roles are fixed by business model
- Database-driven role list: Unnecessary complexity for two static options

**Implementation Impact**: roleOptions data structure is already correct. No changes needed.

---

### Q5: How is form validation currently handled?

**Decision**: Use inline validator functions with error state updates

**Rationale**:
- Pattern is established in Register.vue for name, email, password validation
- Each validator function checks a specific field and updates errors[field]
- Validation occurs on blur and form submit
- isFormValid computed property checks all conditions before enabling submit

**Alternatives Considered**:
- Centralized validation schema: Would require schema library. Current approach is simpler.
- Backend-only validation: Would create worse UX—need immediate feedback

**Implementation Impact**: Follow existing pattern for role validation. Add validateRole() function that checks form.role is not empty.

---

## Verified Dependencies

### Frontend Stack (Vue 3 + Vite)
- ✅ Vue 3.5.27: Supports composition API and script setup syntax
- ✅ Vue Router 4.3: Handles SPA routing, lazy-loaded auth pages
- ✅ Pinia 2.2: Auth state management for logged-in state
- ✅ Axios 1.11: HTTP client for API calls
- ✅ Tailwind CSS 4: Styling utilities (already configured)

### Backend Stack (Laravel)
- ✅ Laravel 12.47: RESTful API framework
- ✅ Laravel Sanctum 4.2.4: API token authentication
- ✅ User model: Already has role_id foreign key
- ✅ Registration controller: Already handles role field

### Testing Stack
- ✅ Vitest 1.0: Frontend component and unit tests
- ✅ PHPUnit 11.5: Backend integration tests
- ✅ @vue/test-utils 2.4: Vue component testing

## Assumptions Confirmed

- ✅ FormSelect component works with string modelValue
- ✅ Form state persists through validation error cycles (reactive refs)
- ✅ Backend registration API validates role field
- ✅ User model role relationship is properly configured
- ✅ No additional database migrations needed

## Risk Assessment

**Technical Risk**: LOW
- All required infrastructure exists
- No new dependencies needed
- No breaking API changes required
- Component is battle-tested from Phase 2 auth design

**Scope Risk**: LOW
- Feature is well-defined and bounded
- Clear acceptance criteria exist
- No hidden dependencies discovered

**Schedule Risk**: LOW
- Estimated effort is 4-6 tasks based on similar auth features
- No blocking unknowns remain

## Next Steps

1. ✅ Phase 0 Research: COMPLETE
2. → Phase 1: Design & Data Models (generate data-model.md, contracts, quickstart.md)
3. → Phase 2: Implementation Tasks (generate tasks.md with specific implementation steps)
