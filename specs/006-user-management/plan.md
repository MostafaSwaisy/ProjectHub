# Implementation Plan: User Management System

**Branch**: `006-user-management` | **Date**: 2026-03-10 | **Spec**: [spec.md](./spec.md)
**Input**: Feature specification from `/specs/006-user-management/spec.md`

## Summary

Build a comprehensive user management system for ProjectHub covering: user dashboard with search/filter/pagination, self-service profile management with avatar uploads, task assignment UI improvements with notifications, project invitation system via email, and role-based access control (RBAC) with four project roles (Owner, Lead, Member, Viewer). The system extends the existing User, ProjectMember, and Role models, adds a new Invitation model, and enhances the frontend with dedicated management pages and improved task assignment components.

## Technical Context

**Language/Version**: PHP 8.2+ (Laravel 12.47.0), JavaScript ES2022 (Vue 3.5.27)
**Primary Dependencies**: Laravel 12, Vue 3.5.27, Vue Router 4.3.0, Pinia 2.2.0, Axios 1.11.0, Laravel Sanctum 4.2.4, Tailwind CSS 4.1.18
**Storage**: SQLite via Laravel Eloquent ORM (existing schema with users, roles, projects, project_members, tasks tables)
**Testing**: PHPUnit 11.5.48 (backend), Vitest (frontend)
**Target Platform**: Web application (SPA frontend + REST API backend)
**Project Type**: Web (Laravel monolith with Vue SPA frontend)
**Performance Goals**: User list loads <2s for 1000 users, permission checks <100ms, task assignment <10s end-to-end
**Constraints**: SQLite database (no advanced features like JSON indexing), must preserve existing soft-delete system, backward-compatible with existing role structure
**Scale/Scope**: Up to 1000 users, ~50 projects, ~5000 tasks

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

No constitution defined (template only). Proceeding with project conventions observed from codebase:

- ✅ Follow existing Laravel controller/model/policy patterns
- ✅ Use existing soft-delete traits (HasSoftDeleteUser, HasCascadeSoftDeletes)
- ✅ Follow existing Pinia store patterns (composition API with defineStore)
- ✅ Follow existing component organization (feature-based folders)
- ✅ Use existing shared components (Button, Modal, Input, Dropdown, Toast)
- ✅ Maintain existing API route structure (`/api/` prefix, auth:sanctum middleware)
- ✅ Activity logging for auditable operations

## Project Structure

### Documentation (this feature)

```text
specs/006-user-management/
├── plan.md              # This file
├── research.md          # Phase 0 output
├── data-model.md        # Phase 1 output
├── quickstart.md        # Phase 1 output
├── contracts/           # Phase 1 output
│   ├── users-api.md
│   ├── profile-api.md
│   ├── invitations-api.md
│   └── permissions-api.md
└── tasks.md             # Phase 2 output (/speckit.tasks)
```

### Source Code (repository root)

```text
app/
├── Http/
│   ├── Controllers/
│   │   ├── UserController.php           # NEW - User management dashboard API
│   │   ├── ProfileController.php        # NEW - Self-service profile management
│   │   ├── InvitationController.php     # NEW - Project invitation workflow
│   │   └── ProjectController.php        # MODIFY - Complete member management methods
│   ├── Middleware/
│   │   └── RoleMiddleware.php           # EXISTS - May need updates
│   └── Requests/
│       ├── UpdateProfileRequest.php     # NEW - Profile validation
│       ├── InviteUserRequest.php        # NEW - Invitation validation
│       └── UpdateUserRequest.php        # NEW - Admin user edit validation
├── Models/
│   ├── User.php                         # MODIFY - Add avatar, bio accessors
│   ├── Invitation.php                   # NEW - Invitation model
│   └── ProjectMember.php                # MODIFY - Update role enum
├── Policies/
│   ├── ProjectPolicy.php               # MODIFY - Update for new roles
│   ├── TaskPolicy.php                   # MODIFY - Update for new roles
│   └── InvitationPolicy.php            # NEW - Invitation authorization
├── Notifications/
│   ├── TaskAssignedNotification.php     # NEW - Task assignment notification
│   └── ProjectInvitationNotification.php # NEW - Invitation email notification
└── Traits/
    └── HasSoftDeleteUser.php            # EXISTS - No changes needed

database/
└── migrations/
    ├── xxxx_add_profile_fields_to_users.php       # NEW
    ├── xxxx_create_invitations_table.php           # NEW
    └── xxxx_update_project_members_roles.php       # NEW

resources/js/
├── components/
│   ├── users/                           # NEW - User management components
│   │   ├── UserTable.vue
│   │   ├── UserFilters.vue
│   │   ├── UserDetailModal.vue
│   │   └── UserAvatar.vue
│   ├── profile/                         # NEW - Profile components
│   │   ├── ProfileForm.vue
│   │   ├── AvatarUploader.vue
│   │   ├── PasswordChangeForm.vue
│   │   └── NotificationPreferences.vue
│   ├── members/                         # NEW - Member/invitation components
│   │   ├── MemberList.vue
│   │   ├── InviteMemberModal.vue
│   │   ├── RoleSelector.vue
│   │   └── PendingInvitations.vue
│   └── kanban/
│       └── AssigneeSelector.vue         # MODIFY - Enhanced with avatars
├── pages/
│   ├── Users.vue                        # NEW - User management page
│   ├── Profile.vue                      # NEW - Profile settings page
│   └── projects/
│       └── KanbanView.vue               # MODIFY - Members tab enhancement
├── stores/
│   ├── users.js                         # NEW - User management store
│   ├── invitations.js                   # NEW - Invitations store
│   └── tasks.js                         # MODIFY - Assignment notifications
├── composables/
│   ├── useProjectPermissions.js         # MODIFY - New role permissions
│   └── usePermissions.js                # MODIFY - Update role checks
└── router/
    └── index.js                         # MODIFY - Add new routes

tests/
├── Feature/
│   ├── UserManagementTest.php           # NEW
│   ├── ProfileTest.php                  # NEW
│   ├── InvitationTest.php               # NEW
│   └── RolePermissionTest.php           # NEW
└── Unit/
    ├── InvitationModelTest.php          # NEW
    └── RolePermissionTest.php           # NEW
```

**Structure Decision**: Follows existing Laravel monolith pattern with Vue SPA. New controllers, models, and components are organized into feature-based directories matching existing conventions. No new top-level directories needed.

## Complexity Tracking

No constitution violations to justify.

## Key Implementation Decisions

### 1. Role Transition Strategy

**Current project roles**: owner, editor, viewer
**New project roles**: owner, lead, member, viewer

Decision: Add `lead` and `member` roles, migrate `editor` → `member` via database migration. This is backward-compatible since existing `editor` users become `member` users with equivalent permissions.

### 2. Permission Model

Decision: Use a permission matrix defined in code (not database) with a config/constant approach. Each role maps to a set of allowed actions. This avoids schema complexity while being easily extensible.

```text
Owner:  all actions
Lead:   create/edit/delete tasks, assign tasks, invite members, manage labels
Member: create/edit own tasks, comment, view all
Viewer: read-only access
```

### 3. Invitation Flow

Decision: Create `invitations` table with token-based acceptance. Invitations link to a project + role. If the invitee has an account, they accept via authenticated endpoint. If not, the registration flow includes an optional invitation token that auto-joins the project.

### 4. Avatar Storage

Decision: Store avatars in `storage/app/public/avatars/` using Laravel's filesystem. Serve via symbolic link. Fallback to initials-based avatar (already implemented in `useAvatar.js` composable).

### 5. Notification Strategy

Decision: Use the existing `notifications` table with JSON data field. Create notification records when tasks are assigned/reassigned and when invitations are sent. Email notifications via Laravel's notification system for invitations only.
