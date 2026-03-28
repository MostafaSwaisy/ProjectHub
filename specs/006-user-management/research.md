# Research: User Management System

**Feature**: 006-user-management
**Date**: 2026-03-10

## Research Topics & Findings

### R1: Project Role Structure Migration

**Decision**: Extend project_members.role enum from `owner/editor/viewer` to `owner/lead/member/viewer`

**Rationale**:

- The spec requires four roles: Owner, Lead, Member, Viewer
- Current system has three: owner, editor, viewer
- `editor` maps naturally to `member` (same permissions: create/edit tasks, comment)
- `lead` is a new intermediate role between owner and member
- SQLite doesn't support ALTER COLUMN for enum changes, so migration will recreate the column or use string type

**Alternatives considered**:

- Keep `editor` name and add `lead` only → Rejected: inconsistent naming with spec
- Create separate `project_roles` table → Rejected: over-engineering for 4 fixed roles
- Use numeric permission levels → Rejected: less readable, harder to extend

**Migration approach**: Since SQLite stores enums as strings (no actual constraint), we can simply document the valid values and validate in code. Add a migration to convert existing `editor` values to `member`.

---

### R2: Permission Matrix Implementation

**Decision**: Define permissions as a PHP constant/config array mapping roles to allowed actions

**Rationale**:

- Four fixed roles don't warrant a full database-driven permission system
- Code-defined permissions are easier to test, version-control, and review
- Policies already exist and can reference the permission matrix
- Matches the existing pattern where policies check roles directly

**Permission Matrix**:

| Action | Owner | Lead | Member | Viewer |
|--------|-------|------|--------|--------|
| View project | ✅ | ✅ | ✅ | ✅ |
| Edit project | ✅ | ✅ | ❌ | ❌ |
| Delete project | ✅ | ❌ | ❌ | ❌ |
| Archive project | ✅ | ❌ | ❌ | ❌ |
| Create task | ✅ | ✅ | ✅ | ❌ |
| Edit any task | ✅ | ✅ | ❌ | ❌ |
| Edit own task | ✅ | ✅ | ✅ | ❌ |
| Delete task | ✅ | ✅ | ❌ | ❌ |
| Assign task | ✅ | ✅ | ❌ | ❌ |
| Comment | ✅ | ✅ | ✅ | ❌ |
| Invite members | ✅ | ✅ | ❌ | ❌ |
| Manage roles | ✅ | ❌ | ❌ | ❌ |
| Manage labels | ✅ | ✅ | ❌ | ❌ |
| View trash | ✅ | ✅ | ❌ | ❌ |
| Restore items | ✅ | ✅ | ❌ | ❌ |

**Alternatives considered**:

- Database-driven permissions table → Rejected: too complex for fixed roles
- Laravel Spatie Permission package → Rejected: adds dependency for simple use case
- Bitfield/bitmask permissions → Rejected: harder to read and maintain

---

### R3: Invitation System Design

**Decision**: Token-based invitation with database storage and email delivery

**Rationale**:

- Invitations need to persist (pending state, resend capability)
- Token provides secure acceptance without requiring prior authentication
- Supports both existing users and new user registration flows
- 7-day default expiry with resend capability

**Flow**:

1. Owner/Lead creates invitation → record in `invitations` table with unique token
2. System sends email with accept link containing token
3. Accept link routes to frontend → checks if user logged in
4. If logged in: API call to accept invitation → creates ProjectMember record
5. If not logged in: redirect to register page with invitation token in URL
6. After registration: auto-accept invitation → creates ProjectMember record

**Token strategy**: UUID v4 (36 chars), stored hashed in DB, unhashed in email link

**Alternatives considered**:

- Magic link (no token storage) → Rejected: can't track pending/declined status
- Invitation code (short alphanumeric) → Rejected: less secure, collision risk
- OAuth-style flow → Rejected: over-engineered for this use case

---

### R4: Avatar Upload Strategy

**Decision**: Laravel filesystem with public disk, stored in `storage/app/public/avatars/`

**Rationale**:

- Laravel's filesystem abstraction allows easy switching to S3 later
- Public disk with symlink is the standard Laravel pattern
- `useAvatar.js` composable already generates initials-based fallback
- Image validation: JPG/PNG only, max 5MB, resized to 256x256

**Implementation**:

- Add `avatar_url` column to `users` table (nullable string)
- ProfileController handles upload, validation, and old avatar cleanup
- Frontend AvatarUploader component with preview and crop
- Serve via `/storage/avatars/{filename}` URL

**Alternatives considered**:

- Base64 in database → Rejected: bloats DB, slow queries
- External service (Cloudinary, etc.) → Rejected: adds external dependency
- Gravatar only → Rejected: not all users have Gravatar

---

### R5: User Management Access Control

**Decision**: System-level admin role required for user management dashboard

**Rationale**:

- Current system has three system roles: admin, instructor, student
- Only `admin` should see the full user list and manage accounts
- `instructor` can see members of their own projects only
- `student` can see members of projects they belong to only
- This aligns with existing RoleMiddleware pattern

**Implementation**:

- UserController protected by `middleware('role:admin')`
- Admin can: list all users, search/filter, view/edit profiles, deactivate accounts
- Non-admin users access user information through project member endpoints only

**Alternatives considered**:

- All authenticated users see user list → Rejected: privacy concern
- Instructor + Admin access → Rejected: instructors don't need system-wide user view

---

### R6: Task Assignment Notification Strategy

**Decision**: In-app notifications via existing `notifications` table + optional email for invitations

**Rationale**:

- Notification model already exists with type, data (JSON), read_at fields
- Real-time push not needed for MVP (polling or page refresh sufficient)
- Email notifications only for invitations (higher urgency than task assignments)
- Activity log already tracks assignment changes

**Implementation**:

- Create Notification record when task assigned/reassigned
- Notification data includes: task_id, task_title, project_id, assigner_name
- Frontend polls for unread notification count (or uses existing patterns)
- Email sent only for project invitations via Laravel Mail

**Alternatives considered**:

- WebSocket real-time → Rejected: adds complexity, not needed for MVP
- Email for all notifications → Rejected: too noisy, users prefer in-app
- Laravel Echo + Pusher → Rejected: external dependency, deferred to future

---

### R7: Frontend Routing & Navigation

**Decision**: Add new routes under existing authenticated layout

**Rationale**:

- Existing router uses `AppLayout.vue` wrapper for authenticated pages
- New pages (Users, Profile) follow same pattern as Dashboard, Settings
- User management accessible from sidebar navigation
- Profile accessible from UserMenu dropdown in TopNavbar

**New Routes**:

```text
/users               → Users.vue (admin only)
/profile             → Profile.vue (any authenticated user)
/settings            → Settings.vue (already exists, may merge with Profile)
```

**Alternatives considered**:

- Separate admin panel → Rejected: over-engineering for single admin page
- Modal-based user management → Rejected: too complex for table + filters + pagination

---

## Summary of All Decisions

| Topic | Decision | Key Rationale |
|-------|----------|---------------|
| Role structure | owner/lead/member/viewer | Matches spec, backward-compatible migration |
| Permissions | Code-defined matrix | Simple, testable, version-controlled |
| Invitations | Token-based with email | Supports both existing and new users |
| Avatars | Laravel filesystem (public disk) | Standard pattern, easy S3 migration later |
| User mgmt access | Admin-only dashboard | Privacy, matches existing role system |
| Notifications | In-app + email for invitations | Pragmatic MVP approach |
| Frontend routing | New routes in existing layout | Consistent with current architecture |

All research topics resolved. No NEEDS CLARIFICATION items remain.
