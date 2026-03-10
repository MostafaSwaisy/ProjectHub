# Tasks: User Management System

**Input**: Design documents from `/specs/006-user-management/`
**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/

**Tests**: Not explicitly requested. Test tasks omitted.

**Organization**: Tasks grouped by user story for independent implementation and testing.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story (US1-US5)
- All paths relative to repository root

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Database migrations, configuration, and shared utilities needed by all stories

- [ ] T001 Create migration to add avatar_url and bio columns to users table in `database/migrations/2026_03_10_000001_add_profile_fields_to_users.php`
- [ ] T002 [P] Create migration to update project_members role values (editor→member, add lead) in `database/migrations/2026_03_10_000002_update_project_members_roles.php`
- [ ] T003 [P] Create migration to create invitations table in `database/migrations/2026_03_10_000003_create_invitations_table.php`
- [ ] T004 Run all migrations and verify database schema is correct via `php artisan migrate`
- [ ] T005 Create the permission matrix config in `config/permissions.php` defining owner/lead/member/viewer permission arrays per data-model.md Permission Matrix section
- [ ] T006 [P] Create storage symlink for avatar uploads via `php artisan storage:link` and ensure `storage/app/public/avatars/` directory exists

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core backend infrastructure that MUST be complete before ANY user story

**CRITICAL**: No user story work can begin until this phase is complete

- [ ] T007 Update `app/Models/User.php` to add avatar_url, bio to fillable array and add avatar accessor with fallback URL
- [ ] T008 [P] Create `app/Models/Invitation.php` model with fillable fields (project_id, email, role, token, status, email_sent, invited_by, accepted_at, expires_at), relationships (project, inviter), status scope methods (pending, expired), and token generation helper
- [ ] T009 [P] Update `app/Models/ProjectMember.php` to document new valid role values (owner, lead, member, viewer) in model comments
- [ ] T010 Update `app/Policies/ProjectPolicy.php` to use permission matrix from `config/permissions.php` instead of hardcoded role checks; add methods: canInvite, canManageRoles, canAssignTasks referencing the config
- [ ] T011 [P] Update `app/Policies/TaskPolicy.php` to use permission matrix for task operations; add self-assign logic (Members can only assign to themselves, Owner/Lead can assign anyone)
- [ ] T012 [P] Create `app/Policies/InvitationPolicy.php` with send (owner/lead), view (owner/lead), cancel (owner/lead), accept (matching email), decline (matching email) methods
- [ ] T013 Update `app/Http/Middleware/RoleMiddleware.php` to support checking project-level roles in addition to system-level roles
- [ ] T014 [P] Update `resources/js/composables/useProjectPermissions.js` to define permission matrix matching backend config (owner/lead/member/viewer) and expose hasPermission(action) helper
- [ ] T015 Add new API routes to `routes/api.php` for: profile endpoints (GET/PUT /profile, POST/DELETE /profile/avatar, PUT /profile/password, PUT /profile/preferences), user management (GET/PUT/DELETE /users, GET /users/{id}), invitation endpoints, permissions endpoint, and assignable members endpoint

**Checkpoint**: Foundation ready — user story implementation can now begin

---

## Phase 3: User Story 1 — View and Manage All Users (Priority: P1) MVP

**Goal**: Admin can view all system users with search, filter, pagination, and edit basic info

**Independent Test**: Navigate to /users as admin → see paginated user list → search by name → filter by role → click user to view/edit profile

### Implementation for User Story 1

- [ ] T016 [P] [US1] Create `app/Http/Requests/UpdateUserRequest.php` with validation rules: name (required, max:255), email (required, unique excluding target), role_id (required, exists:roles)
- [ ] T017 [US1] Create `app/Http/Controllers/UserController.php` with: index (paginated list with search/filter/sort per contracts/users-api.md), show (single user with projects), update (admin edit name/email/role), destroy (soft-delete, cannot delete self)
- [ ] T018 [US1] Register UserController routes in `routes/api.php` with auth:sanctum + role:admin middleware: GET /users, GET /users/{id}, PUT /users/{id}, DELETE /users/{id}
- [ ] T019 [P] [US1] Create `resources/js/stores/users.js` Pinia store with: users state, fetchUsers(filters, page), fetchUser(id), updateUser(id, data), deleteUser(id) actions, computed getters for loading/error state
- [ ] T020 [P] [US1] Create `resources/js/components/users/UserFilters.vue` with search input (debounced), role dropdown filter (admin/instructor/student), status filter (active/deleted), sort controls (name/email/created_at)
- [ ] T021 [P] [US1] Create `resources/js/components/users/UserAvatar.vue` component displaying avatar image or initials fallback using existing useAvatar.js composable, accepting user prop with name and avatar_url
- [ ] T022 [US1] Create `resources/js/components/users/UserTable.vue` with paginated table showing columns: avatar, name, email, system role, projects count, status, created_at; row click emits select event; pagination controls at bottom
- [ ] T023 [US1] Create `resources/js/components/users/UserDetailModal.vue` using shared Modal component; displays user info with editable fields (name, email, role dropdown); save button calls updateUser; deactivate button with confirmation calls deleteUser; no password reset (display-only note)
- [ ] T024 [US1] Create `resources/js/pages/Users.vue` page composing UserFilters + UserTable + UserDetailModal; fetches users on mount and filter change; handles user selection and edit flow
- [ ] T025 [US1] Add /users route to `resources/js/router/index.js` with requiresAuth meta and admin-only guard; add "Users" link to `resources/js/components/layout/Sidebar.vue` visible only to admin role

**Checkpoint**: Admin can view, search, filter, and edit users. Story 1 independently testable.

---

## Phase 4: User Story 2 — User Profile Management (Priority: P1)

**Goal**: Any user can view/edit own profile, upload avatar, change password, update notification preferences

**Independent Test**: Navigate to /profile → edit name → upload avatar → change password → update notification preferences → all changes persist

### Implementation for User Story 2

- [ ] T026 [P] [US2] Create `app/Http/Requests/UpdateProfileRequest.php` with validation: name (required, max:255), email (required, email, unique excluding self), bio (nullable, max:500)
- [ ] T027 [US2] Create `app/Http/Controllers/ProfileController.php` with: show (current user profile + preferences), update (name/email/bio), uploadAvatar (validate image jpg/png max 5MB, store in storage/app/public/avatars/, delete old avatar, update user.avatar_url), deleteAvatar (remove file, null avatar_url), changePassword (validate current password, new password min 8 and different from old), updatePreferences (save notification_frequency, notification_email, notification_assignments, notification_comments to user_preferences table)
- [ ] T028 [US2] Register ProfileController routes in `routes/api.php` with auth:sanctum middleware: GET /profile, PUT /profile, POST /profile/avatar, DELETE /profile/avatar, PUT /profile/password, PUT /profile/preferences
- [ ] T029 [P] [US2] Create `resources/js/components/profile/ProfileForm.vue` with editable fields for name, email, bio using shared Input component; save button with loading state; validation error display
- [ ] T030 [P] [US2] Create `resources/js/components/profile/AvatarUploader.vue` with image preview, file input (accept jpg/png), upload button, remove button; shows current avatar or initials fallback; 5MB size validation on client side
- [ ] T031 [P] [US2] Create `resources/js/components/profile/PasswordChangeForm.vue` with current password, new password, confirm password fields; client-side validation (min 8 chars, match confirmation); submit calls PUT /profile/password
- [ ] T032 [P] [US2] Create `resources/js/components/profile/NotificationPreferences.vue` with toggles/selects for: notification_frequency (realtime/daily/weekly/none), notification_email (on/off), notification_assignments (on/off), notification_comments (on/off); auto-save on change
- [ ] T033 [US2] Create `resources/js/pages/Profile.vue` page composing AvatarUploader + ProfileForm + PasswordChangeForm + NotificationPreferences in a tabbed or sectioned layout; fetches profile on mount
- [ ] T034 [US2] Add /profile route to `resources/js/router/index.js` with requiresAuth meta; add "Profile" link to `resources/js/components/layout/UserMenu.vue` dropdown
- [ ] T035 [US2] Update `resources/js/stores/auth.js` to include avatar_url and bio in user state; add updateProfile and uploadAvatar actions that call profile API and update local state

**Checkpoint**: Users can manage their own profiles. Story 2 independently testable.

---

## Phase 5: User Story 3 — Task Assignment to Users (Priority: P1)

**Goal**: Tasks can be assigned to project members with role-based restrictions and notifications

**Independent Test**: Open a task → click assignee → see member dropdown (filtered by role) → assign → notification created → view "My Tasks" filtered list

### Implementation for User Story 3

- [ ] T036 [US3] Complete `app/Http/Controllers/ProjectController.php` members() method to return project members with user data (id, name, email, avatar_url, role) per contracts/permissions-api.md GET /projects/{project}/members
- [ ] T037 [US3] Add assignable members endpoint to `app/Http/Controllers/ProjectController.php`: method assignableMembers() returning members with roles owner/lead/member (excluding viewers) per contracts/permissions-api.md GET /projects/{project}/members/assignable
- [ ] T038 [US3] Update `app/Http/Controllers/TaskController.php` update() method to: check task.assign permission before allowing assignee_id change; validate assignee is a project member (not viewer); create Notification record (type: task_assigned) for new assignee; create Notification record (type: task_unassigned) for previous assignee if changed; log activity with old/new assignee data
- [ ] T039 [US3] Add member removal task unassignment logic to `app/Http/Controllers/ProjectController.php` removeMember() method: find all tasks assigned to removed user in this project, set assignee_id to null, return unassigned_tasks_count in response
- [ ] T040 [US3] Register new routes in `routes/api.php`: GET /projects/{project}/members/assignable with auth:sanctum middleware
- [ ] T041 [US3] Update `resources/js/components/kanban/AssigneeSelector.vue` to: fetch assignable members from /projects/{project}/members/assignable; show avatars (UserAvatar component) and role badges in dropdown; for Members role show only self in dropdown; for Owner/Lead show all assignable members; emit assignment change event
- [ ] T042 [US3] Update `resources/js/components/kanban/TaskCard.vue` to display assignee avatar (using UserAvatar component) and name on task cards when assignee_id is set
- [ ] T043 [US3] Update `resources/js/components/kanban/TaskDetailModal.vue` to show assignee section with avatar, name, and role; include AssigneeSelector for changing assignment
- [ ] T044 [US3] Update `resources/js/pages/MyTasks.vue` to fetch tasks filtered by assignee_id = current user; display grouped by project with priority and due date sorting
- [ ] T045 [US3] Update `resources/js/stores/tasks.js` to handle assignee changes: call PUT /tasks/{id} with assignee_id, update local task state optimistically, show toast notification on success

**Checkpoint**: Task assignment works with role-based restrictions and notifications. Story 3 independently testable.

---

## Phase 6: User Story 4 — Invite Users to Projects (Priority: P2)

**Goal**: Project owners/leads can invite users by email; invited users can accept/decline; registration supports invitation tokens

**Independent Test**: Owner sends invitation → email recorded → invitee accepts via token → automatically added as project member with assigned role

### Implementation for User Story 4

- [ ] T046 [P] [US4] Create `app/Http/Requests/InviteUserRequest.php` with validation: email (required, email), role (required, in:lead,member,viewer); custom rule to check no duplicate pending invitation for same email+project; custom rule to check project has fewer than 20 pending invitations
- [ ] T047 [US4] Create `app/Http/Controllers/InvitationController.php` with: store (create invitation with hashed token, set email_sent=false, attempt email send via Laravel Mail, set email_sent=true on success), index (list project invitations with status filter), resend (reset expires_at, re-attempt email), cancel (delete pending invitation), accept (validate token, check email matches auth user, create ProjectMember, update invitation status to accepted), decline (validate token, update status to declined), pending (list current user's pending invitations by email)
- [ ] T048 [P] [US4] Create `app/Notifications/ProjectInvitationNotification.php` Laravel Mailable that sends invitation email with accept link containing the unhashed token; includes project name, inviter name, role, and expiry date
- [ ] T049 [US4] Register InvitationController routes in `routes/api.php`: POST/GET /projects/{project}/invitations (auth:sanctum), POST /projects/{project}/invitations/{invitation}/resend (auth:sanctum), DELETE /projects/{project}/invitations/{invitation} (auth:sanctum), POST /invitations/{token}/accept (auth:sanctum), POST /invitations/{token}/decline (auth:sanctum), GET /invitations/pending (auth:sanctum)
- [ ] T050 [US4] Update `app/Http/Controllers/Auth/RegisterController.php` to accept optional invitation_token field; after successful registration, if token is valid and email matches, auto-accept the invitation and create ProjectMember record
- [ ] T051 [P] [US4] Create `resources/js/stores/invitations.js` Pinia store with: invitations state, pendingInvitations state, fetchProjectInvitations(projectId), sendInvitation(projectId, email, role), resendInvitation(projectId, invitationId), cancelInvitation(projectId, invitationId), fetchPendingInvitations(), acceptInvitation(token), declineInvitation(token) actions
- [ ] T052 [US4] Create `resources/js/components/members/InviteMemberModal.vue` using shared Modal; email input field, role selector dropdown (Lead/Member/Viewer), send button; validates email format client-side; shows success/error toast; displays invitation limit warning when approaching 20
- [ ] T053 [P] [US4] Create `resources/js/components/members/PendingInvitations.vue` component showing list of pending invitations with email, role, sent date, expiry, email_sent status; resend button for failed/expired emails; cancel button with confirmation
- [ ] T054 [US4] Update `resources/js/pages/projects/KanbanView.vue` members tab to include InviteMemberModal trigger button and PendingInvitations section below the member list; conditionally show invite button only for users with member.invite permission
- [ ] T055 [US4] Update `resources/js/pages/auth/Register.vue` to check URL for invitation_token query parameter; pass it to registration API call; show message indicating they were invited to a project

- [ ] T056 [US4] Create `app/Console/Commands/ExpireInvitations.php` Artisan command that sets status='expired' on all invitations where status='pending' and expires_at < now(); register in `routes/console.php` to run daily via `Schedule::command('invitations:expire')->daily()`

**Checkpoint**: Full invitation workflow works end-to-end. Story 4 independently testable.

---

## Phase 7: User Story 5 — Define Roles and Permissions (Priority: P2)

**Goal**: Project owners can view role permissions, change member roles, and permissions are enforced consistently

**Independent Test**: Owner views permissions table → changes member role → permissions update immediately → restricted user is blocked from unauthorized action

### Implementation for User Story 5

- [ ] T057 [US5] Complete `app/Http/Controllers/ProjectController.php` addMember() method to create ProjectMember with validated role, log activity (type: member_added); complete updateMember() to change role with validation (cannot remove last owner), log activity (type: role_changed), create Notification (type: role_changed) for affected user
- [ ] T058 [US5] Add permissions endpoint to `app/Http/Controllers/ProjectController.php`: method permissions() returning the permission matrix from config/permissions.php with role labels, descriptions, and current user's role and permissions per contracts/permissions-api.md
- [ ] T059 [US5] Register remaining member management routes in `routes/api.php` if not already registered: POST /projects/{project}/members, PUT /projects/{project}/members/{user}, DELETE /projects/{project}/members/{user}, GET /projects/{project}/permissions
- [ ] T060 [P] [US5] Create `resources/js/components/members/MemberList.vue` component displaying project members with avatar, name, email, role badge; for owners: show role change dropdown and remove button per member; disable role change for last owner
- [ ] T061 [P] [US5] Create `resources/js/components/members/RoleSelector.vue` dropdown component with options: Owner, Lead, Member, Viewer; each option shows role label and brief description; emits role-change event
- [ ] T062 [US5] Create `resources/js/components/members/PermissionsTable.vue` component showing the permission matrix: rows for each action (project.view, task.create, etc.), columns for each role, checkmarks for permitted actions; read-only display
- [ ] T063 [US5] Update `resources/js/pages/projects/KanbanView.vue` members tab to replace existing member list with MemberList component; add PermissionsTable as a collapsible section; wire up role change and remove member actions to ProjectController endpoints
- [ ] T064 [US5] Update `resources/js/composables/useProjectPermissions.js` to expose canInvite, canManageRoles, canAssignTasks, canEditProject, canDeleteProject, canCreateTask, canEditTask, canDeleteTask computed properties based on current user's project role and the permission matrix
- [ ] T065 [US5] Apply permission guards across existing frontend components: hide create task button for Viewers in KanbanColumn.vue; hide edit/delete buttons based on role in TaskCard.vue; hide invite button for Member/Viewer in KanbanView.vue; show permission denied toast when blocked actions are attempted

**Checkpoint**: Full RBAC system works with role management UI. Story 5 independently testable.

---

## Phase 8: Polish & Cross-Cutting Concerns

**Purpose**: Integration, consistency, and final quality improvements

- [ ] T066 Create `app/Http/Controllers/NotificationController.php` with: index (list current user's notifications, paginated), unreadCount (return count of unread notifications), markAsRead (mark single notification read), markAllAsRead; register routes in `routes/api.php` with auth:sanctum: GET /notifications, GET /notifications/unread-count, POST /notifications/{notification}/read, POST /notifications/read-all
- [ ] T067 [P] Add activity logging for all new operations in controllers: user profile updates, invitation actions, role changes, member additions/removals in respective controllers
- [ ] T068 [P] Update `resources/js/components/layout/Sidebar.vue` to show notification badge count (unread notifications) by calling GET /api/notifications/unread-count
- [ ] T069 Update `resources/js/components/layout/TopNavbar.vue` to show current user avatar using UserAvatar component instead of plain initials
- [ ] T070 [P] Create notification dropdown or page showing recent notifications (task_assigned, invitation_received, role_changed) with mark-as-read functionality
- [ ] T071 Verify all API error responses are consistent: 401 for unauthenticated, 403 for unauthorized, 404 for not found, 409 for conflicts, 422 for validation errors across all new controllers
- [ ] T072 Run existing test suite (`php artisan test`) to verify no regressions from role migration and policy changes
- [ ] T073 Verify avatar upload/delete cleanup works correctly: old avatars are removed when replaced, avatars are removed when user is soft-deleted
- [ ] T074 End-to-end smoke test: register user → login → create project → invite member → member accepts → assign task → change role → verify permissions

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — start immediately
- **Foundational (Phase 2)**: Depends on Phase 1 migrations — BLOCKS all user stories
- **US1 (Phase 3)**: Depends on Phase 2 — no dependencies on other stories
- **US2 (Phase 4)**: Depends on Phase 2 — no dependencies on other stories
- **US3 (Phase 5)**: Depends on Phase 2 — uses UserAvatar from US1 (but can stub)
- **US4 (Phase 6)**: Depends on Phase 2 — uses Invitation model from setup
- **US5 (Phase 7)**: Depends on Phase 2 — uses permission matrix from setup
- **Polish (Phase 8)**: Depends on all desired user stories being complete

### User Story Dependencies

- **US1 (P1)**: Independent after Phase 2. Creates UserAvatar component reused by others.
- **US2 (P1)**: Independent after Phase 2. Creates avatar upload capability.
- **US3 (P1)**: Independent after Phase 2. May reuse UserAvatar from US1 (can inline if US1 not done).
- **US4 (P2)**: Independent after Phase 2. Uses Invitation model from Phase 1.
- **US5 (P2)**: Independent after Phase 2. Completes member management started in Phase 2.

### Within Each User Story

- Backend (controller + routes) before frontend (store + components + page)
- Shared components before page composition
- Request validation before controller logic

### Parallel Opportunities

**Phase 1** (all migrations in parallel):

```text
T001 (users migration) || T002 (project_members migration) || T003 (invitations migration)
T005 (permission config) || T006 (storage setup)
```

**Phase 2** (foundational, after migrations):

```text
T008 (Invitation model) || T009 (ProjectMember update) || T012 (InvitationPolicy)
T010 (ProjectPolicy) || T011 (TaskPolicy)
T014 (frontend permissions composable)
```

**After Phase 2** (user stories in parallel):

```text
US1 (Phase 3) || US2 (Phase 4) || US3 (Phase 5)
Then: US4 (Phase 6) || US5 (Phase 7)
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup (migrations + config)
2. Complete Phase 2: Foundational (models + policies + routes)
3. Complete Phase 3: User Story 1 (user management dashboard)
4. **STOP and VALIDATE**: Admin can view/search/filter/edit users
5. Deploy/demo if ready

### Incremental Delivery

1. Setup + Foundational → Foundation ready
2. Add US1 (User Dashboard) → Test → Deploy (MVP!)
3. Add US2 (Profile Management) → Test → Deploy
4. Add US3 (Task Assignment) → Test → Deploy
5. Add US4 (Invitations) → Test → Deploy
6. Add US5 (Roles & Permissions UI) → Test → Deploy
7. Polish phase → Final release

### Parallel Team Strategy

With multiple developers after Phase 2:

- Developer A: US1 (User Dashboard) + US4 (Invitations)
- Developer B: US2 (Profile) + US5 (Roles & Permissions)
- Developer C: US3 (Task Assignment) + Polish

---

## Notes

- [P] tasks = different files, no dependencies on in-progress tasks
- [USn] label maps task to specific user story
- Each story is independently completable and testable
- Commit after each task or logical group
- Stop at any checkpoint to validate story independently
- Permission matrix is the single source of truth for RBAC (config/permissions.php + useProjectPermissions.js)
- Existing soft-delete system is preserved — new models follow same patterns
