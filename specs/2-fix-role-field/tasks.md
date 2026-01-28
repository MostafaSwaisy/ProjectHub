# Tasks: Fix Role Field in Registration Form

**Input**: Design documents from `/specs/2-fix-role-field/`
**Dependencies**: plan.md, spec.md, data-model.md, contracts/, quickstart.md

**Tests**: This task list does NOT include test tasks. Tests can be added for validation and persistence via Vitest if desired.

**Organization**: Tasks are organized by user story priority (P1, P2, P3) to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story (US1, US2, US3)
- Exact file paths included

---

## Phase 1: Setup & Environment

**Purpose**: Verify development environment is ready for implementation

**⚠️ SKIP IF ALREADY DONE**: If you have development servers running and can access http://localhost:8000/auth/register, skip to Phase 2

- [ ] T001 Verify Laravel dev server is running (`php artisan serve`)
- [ ] T002 Verify Vite dev server is running (`npm run dev`)
- [ ] T003 Verify registration form loads at `http://localhost:8000/auth/register`
- [ ] T004 Verify FormSelect component renders with role options

---

## Phase 2: Foundational (Existing Infrastructure Review)

**Purpose**: Verify existing infrastructure supports the feature implementation

**⚠️ CRITICAL**: Foundational check before user story work begins

- [ ] T005 Review FormSelect component in `resources/js/components/auth/FormSelect.vue` - verify it supports: modelValue binding, error display, required prop, options array with descriptions
- [ ] T006 Review Register.vue form structure in `resources/js/pages/auth/Register.vue` - verify form.role exists and roleOptions data is defined
- [ ] T007 Verify form state management pattern - confirm errors ref tracks field errors and isFormValid computed checks field validity
- [ ] T008 Verify registration API endpoint - confirm POST /api/auth/register accepts role field in request body
- [ ] T009 Verify existing validators - review validateEmail() and validatePassword() functions to understand validation pattern

**Checkpoint**: Foundation review complete - ready to implement user stories

---

## Phase 3: User Story 1 - Select and Validate Role Selection (Priority: P1) 🎯 MVP

**Goal**: Users can select a role and form validates selection with error message when empty

**Independent Test**: Open http://localhost:8000/auth/register
1. Fill name, email, password without selecting role
2. Click "Create Account" → Error "Please select a role" appears, button disabled
3. Select "Student" → Button enables
4. Change to "Instructor" → Selection persists
5. Attempt submit with all fields filled including role → Request includes role

### Implementation for User Story 1

- [ ] T010 [P] [US1] Create validateRole() function in `resources/js/pages/auth/Register.vue` that checks if form.role is empty and sets errors.role = 'Please select a role' or null
- [ ] T011 [P] [US1] Update isFormValid computed property in `resources/js/pages/auth/Register.vue` to include: `form.value.role &&` and `!errors.value.role` conditions
- [ ] T012 [US1] Update handleRegister() function in `resources/js/pages/auth/Register.vue` to call validateRole() before form submission validation
- [ ] T013 [US1] Add @blur="validateRole" event listener to FormSelect component in Register.vue template
- [ ] T014 [US1] Test role validation: Submit form without role, verify error message appears and button stays disabled
- [ ] T015 [US1] Test role selection: Select each role option, verify selection is retained and button enables
- [ ] T016 [US1] Test role submission: Fill all fields including role, submit form, verify role value is included in API request

**Checkpoint**: User Story 1 complete - role validation works independently. Can register with valid role selection and get error without selection.

---

## Phase 4: User Story 2 - Clear Role Option Descriptions (Priority: P2)

**Goal**: Users see helpful descriptions for each role option to understand what each role means

**Independent Test**: Open http://localhost:8000/auth/register
1. Click on role dropdown
2. Hover over "Student" option → See description "I am taking courses on ProjectHub"
3. Hover over "Instructor" option → See description "I am teaching courses on ProjectHub"

### Implementation for User Story 2

- [ ] T017 [P] [US2] Verify roleOptions data structure in `resources/js/pages/auth/Register.vue` includes description property for each role option
- [ ] T018 [P] [US2] Verify FormSelect component in `resources/js/components/auth/FormSelect.vue` displays option descriptions (via title attribute or visible text)
- [ ] T019 [US2] Test role descriptions: Open registration form, access role dropdown, verify descriptions display for Student and Instructor options
- [ ] T020 [US2] Test description text: Verify exact text matches spec ("I am taking courses on ProjectHub" for Student, "I am teaching courses on ProjectHub" for Instructor)
- [ ] T021 [US2] Test accessibility: Use browser DevTools to verify aria-attributes for screen reader accessibility

**Checkpoint**: User Story 2 complete - role descriptions are visible and accurate. Users can understand each role before selecting.

---

## Phase 5: User Story 3 - Maintain Role Selection on Validation Errors (Priority: P3)

**Goal**: Role selection persists when user encounters validation errors in other fields and corrects them

**Independent Test**: Open http://localhost:8000/auth/register
1. Select role "Student"
2. Fill name, email, password
3. Leave password_confirmation empty → Attempt submit
4. See validation error for password_confirmation
5. Verify role field still shows "Student" selected
6. Fix password_confirmation and submit → Role is still sent with request

### Implementation for User Story 3

- [ ] T022 [P] [US3] Test role persistence with email error: Select role, enter invalid email, submit, verify role value persists while error shown
- [ ] T023 [P] [US3] Test role persistence with password error: Select role, enter weak password, submit, verify role value persists while error shown
- [ ] T024 [P] [US3] Test role persistence with confirmation error: Select role, enter mismatched password_confirmation, submit, verify role value persists while error shown
- [ ] T025 [US3] Test role persistence through multiple corrections: Select role, cause 3+ validation errors sequentially, correct each one, verify role persists throughout
- [ ] T026 [US3] Test role value in submission payload: Cause validation error, correct errors, submit form, verify role is included in final API request

**Checkpoint**: User Story 3 complete - role selection persists through all validation error scenarios. User doesn't need to re-select role when fixing other fields.

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Final validation, testing, and polish across all user stories

- [ ] T027 [P] Run complete registration flow end-to-end: Register with Student role, verify success and role assignment in dashboard/profile
- [ ] T028 [P] Run complete registration flow end-to-end: Register with Instructor role, verify success and role assignment in dashboard/profile
- [ ] T029 Browser compatibility testing: Test role selection and validation in Chrome, Firefox, Safari, and Edge
- [ ] T030 Mobile device testing: Test role dropdown and selection on mobile viewport (375px width minimum)
- [ ] T031 Keyboard navigation testing: Test selecting role via Tab key and Arrow keys without mouse
- [ ] T032 Accessibility testing: Use accessibility inspector to verify proper aria-labels, aria-required, aria-invalid, aria-describedby on role field
- [ ] T033 Performance testing: Open DevTools, verify form validation completes within 500ms of user action
- [ ] T034 Run quickstart.md test cases: Execute all 4 test cases from quickstart.md, verify each passes
- [ ] T035 Code review: Review Register.vue changes for code quality, naming consistency, and adherence to existing patterns
- [ ] T036 [P] Commit implementation: Stage changes and create git commit with detailed message about role validation implementation

**Checkpoint**: All user stories complete and validated. Feature ready for deployment.

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies - verify environment ready
- **Foundational (Phase 2)**: No dependencies - review existing code
- **User Story 1 (Phase 3)**: Depends on Foundational completion
- **User Story 2 (Phase 4)**: Depends on User Story 1 completion (but can start after US1 if independent)
- **User Story 3 (Phase 5)**: Depends on User Story 1 completion (validation must work first)
- **Polish (Phase 6)**: Depends on all user stories complete

### User Story Dependencies

- **User Story 1 (P1) - Role Validation**: Can start immediately after Foundational. No dependencies on US2/US3
- **User Story 2 (P2) - Descriptions**: Can start after Foundational. Independent of US1 (descriptions already exist)
- **User Story 3 (P3) - Persistence**: Depends on US1 completion (validation must exist to test persistence through errors)

### Within Each User Story

1. **Implementation tasks** complete first (T010-T013 for US1)
2. **Test/verification tasks** run after implementation (T014-T016 for US1)
3. **Move to next story** only after current story is verified complete

### Parallel Opportunities

- **Phase 1**: All tasks are sequential (dependency chain)
- **Phase 2**: All review tasks marked [P] can run in parallel (T005-T009 independent reviews)
- **Phase 3 (US1)**: Implementation tasks T010-T012 can run in parallel (different functions/properties), but T013-T016 should run after implementation is complete
- **Phase 4 (US2)**: Verification tasks T017-T020 can run in parallel (reading existing code)
- **Phase 6**: All validation tasks marked [P] can run in parallel by different testers

---

## Parallel Example Execution

### If Single Developer (Sequential)

```
1. Complete Phase 1 (Setup)
2. Complete Phase 2 (Foundational review)
3. Complete Phase 3 (US1 validation)
   → VALIDATE independently
4. Complete Phase 4 (US2 descriptions)
   → VALIDATE independently
5. Complete Phase 3 (US3 persistence)
   → VALIDATE independently
6. Complete Phase 6 (Polish & testing)
```

### If Multiple Developers (Parallel After Foundational)

```
Developer A - Phase 3:
- T010: Create validateRole()
- T011: Update isFormValid
- T012: Update handleRegister()
- T013-T016: Test US1

Developer B - Phase 4 (can start after US1 testing):
- T017-T021: Verify descriptions exist

Developer C - Phase 6 (can start as Phase 3/4/5 complete):
- T027-T036: Cross-cutting testing and polish
```

---

## Implementation Strategy

### MVP Approach (Fastest Path)

```
1. Phase 1: Setup (verify environment) - 2 min
2. Phase 2: Foundational (review code) - 10 min
3. Phase 3: User Story 1 (implement validation) - 20 min
   ✅ STOP HERE FOR MVP
   → Validation works, can register with valid role
   → Forms prevent submission without role
4. Validate independently
5. Deploy/Demo
```

**MVP Result**: Role field validation is working. Users must select role, see error without selection, role persists through corrections.

### Complete Implementation (All Stories)

```
1. Phase 1: Setup - 2 min
2. Phase 2: Foundational - 10 min
3. Phase 3: User Story 1 (validation) - 20 min
4. Phase 4: User Story 2 (descriptions) - 5 min
5. Phase 5: User Story 3 (persistence) - 10 min
6. Phase 6: Polish & Testing - 30 min
```

**Total Estimated Time**: ~75 minutes for complete implementation with testing

### Quality Gates

- **After Phase 3**: Role validation must work independently before proceeding
- **After Phase 4**: Descriptions must be visible and accurate
- **After Phase 5**: Selection must persist through validation errors
- **After Phase 6**: Cross-browser and accessibility testing complete

---

## Notes for Implementation

### Code Pattern Reference

Look at existing validation functions in `resources/js/pages/auth/Register.vue`:

```javascript
// Pattern to follow for validateRole():
const validateEmail = () => {
  const error = validators.email(form.value.email)
  if (!form.value.email) {
    errors.value.email = 'Email is required'
  } else {
    errors.value.email = error
  }
}

// Apply same pattern for validateRole()
// But simpler - just check if form.value.role is empty or has valid value
```

### Key Files

- **Main implementation**: `resources/js/pages/auth/Register.vue` (Add validateRole, update isFormValid, update handleRegister)
- **No changes needed**: `resources/js/components/auth/FormSelect.vue` (already complete)
- **No changes needed**: Backend registration controller (already accepts role)

### Testing with Real Data

Use test account credentials from database:
- Email: `admin@example.com`, Password: `password`, Role: `admin`
- Email: `instructor@example.com`, Password: `password`, Role: `instructor`
- Email: `student@example.com`, Password: `password`, Role: `student`

Or register a new test user:
- Name: `Test User`
- Email: `test@example.com`
- Password: `TestPass123`
- Role: Select Student or Instructor
- Password Confirmation: `TestPass123`

### Commits

Suggested commit structure:
1. After Phase 3: "Feat: Add role field validation to registration form"
2. After Phase 4: "Docs: Verify role descriptions are displayed (already implemented)"
3. After Phase 5: "Test: Verify role persistence through validation errors"
4. After Phase 6: "Perf: Complete testing and polish for role field feature"

Or single commit with all changes:
"Feat: Complete role field validation, descriptions, and persistence in registration form"

---

## Quick Reference: What to Change

### File 1: `resources/js/pages/auth/Register.vue`

**Line ~153-161 (Add new function)**:
```javascript
const validateRole = () => {
  if (!form.value.role) {
    errors.value.role = 'Please select a role'
  } else {
    errors.value.role = null
  }
}
```

**Line ~138-151 (Update computed)**:
Add `form.value.role &&` and `!errors.value.role` to isFormValid

**Line ~199-209 (Update handleRegister)**:
Add `validateRole()` call before validation check

**Line ~67-77 (Add to FormSelect)**:
Ensure `@blur="validateRole"` event listener is present

That's it! 3-4 small changes to Register.vue. No other files need modification.

---

## Verification Checklist

After completing all tasks, verify:

- [ ] Role field shows error "Please select a role" when empty
- [ ] Submit button is disabled until role is selected
- [ ] Role descriptions are visible in dropdown
- [ ] Role selection persists when other fields have validation errors
- [ ] Complete registration succeeds with role selected
- [ ] Backend receives role value in registration request
- [ ] User is assigned correct role after registration
- [ ] Form works in Chrome, Firefox, Safari, Edge
- [ ] Form works on mobile (375px+ width)
- [ ] Keyboard navigation works (Tab, Arrow keys)
- [ ] Screen reader announces role field and errors properly
- [ ] Form validates within 500ms
