# Quickstart: User Management System

**Feature**: 006-user-management
**Date**: 2026-03-10

## Prerequisites

- PHP 8.2+ with SQLite
- Node.js 18+ with npm
- Composer installed
- Existing ProjectHub codebase on `006-user-management` branch

## Setup Steps

### 1. Install dependencies (if not already done)

```bash
composer install
npm install
```

### 2. Run new migrations

```bash
php artisan migrate
```

This will run:

- `add_profile_fields_to_users` — Adds `avatar_url` and `bio` to users table
- `create_invitations_table` — Creates the invitations table
- `update_project_members_roles` — Migrates `editor` role to `member` in project_members

### 3. Create storage symlink (for avatar uploads)

```bash
php artisan storage:link
```

### 4. Seed roles (if not already seeded)

Ensure the `roles` table has: admin, instructor, student

### 5. Start development servers

```bash
# Terminal 1: Laravel backend
php artisan serve

# Terminal 2: Vite frontend
npm run dev
```

## Implementation Order

Build features in this order for incremental, testable progress:

### Phase 1: Foundation (P1 stories)

1. **Database migrations** — Profile fields, invitations table, role updates
2. **Permission matrix** — Define role→permission mapping as PHP config
3. **Update policies** — Refactor ProjectPolicy, TaskPolicy to use new roles
4. **Profile API** — ProfileController with GET/PUT/password/avatar/preferences
5. **Profile frontend** — Profile.vue page with ProfileForm, AvatarUploader, PasswordChangeForm

### Phase 2: User Management (P1)

6. **User management API** — UserController with list/show/update/delete
7. **User management frontend** — Users.vue page with UserTable, UserFilters, UserDetailModal
8. **Navigation updates** — Add Users and Profile links to Sidebar and UserMenu

### Phase 3: Task Assignment (P1)

9. **Assignment notifications** — Create notification on assignee change in TaskController
10. **Enhanced AssigneeSelector** — Update component with avatars, role badges
11. **My Tasks view** — Enhance MyTasks.vue with assignment-focused features

### Phase 4: Invitations (P2)

12. **Invitation API** — InvitationController with send/list/accept/decline/resend
13. **Invitation email** — Laravel Mail notification for invitation delivery
14. **Invitation frontend** — InviteMemberModal, PendingInvitations components
15. **Registration extension** — Accept invitation token during registration

### Phase 5: Roles & Permissions UI (P2)

16. **Complete member management** — Finish ProjectController member methods
17. **Member management UI** — MemberList, RoleSelector in KanbanView members tab
18. **Permission display** — Roles & Permissions view in project settings
19. **Frontend permission guards** — Update useProjectPermissions composable

### Phase 6: Testing & Polish

20. **Backend tests** — PHPUnit tests for all new controllers and policies
21. **Frontend testing** — Component tests for critical flows
22. **Integration testing** — End-to-end invitation and assignment flows

## Key Files to Modify

| File | Change Type | Priority |
|------|-------------|----------|
| `app/Models/User.php` | Add avatar_url, bio | Phase 1 |
| `app/Http/Controllers/ProfileController.php` | New controller | Phase 1 |
| `app/Policies/ProjectPolicy.php` | Update for new roles | Phase 1 |
| `app/Policies/TaskPolicy.php` | Update for new roles | Phase 1 |
| `app/Http/Controllers/UserController.php` | New controller | Phase 2 |
| `app/Http/Controllers/TaskController.php` | Add notifications | Phase 3 |
| `app/Models/Invitation.php` | New model | Phase 4 |
| `app/Http/Controllers/InvitationController.php` | New controller | Phase 4 |
| `app/Http/Controllers/ProjectController.php` | Complete members | Phase 5 |
| `resources/js/pages/Profile.vue` | New page | Phase 1 |
| `resources/js/pages/Users.vue` | New page | Phase 2 |
| `resources/js/stores/users.js` | New store | Phase 2 |
| `resources/js/stores/invitations.js` | New store | Phase 4 |
| `resources/js/composables/useProjectPermissions.js` | Update | Phase 5 |
| `resources/js/router/index.js` | Add routes | Phase 1 |
| `routes/api.php` | Add new routes | Phase 1 |

## Testing Strategy

```bash
# Run backend tests
php artisan test --filter=UserManagement
php artisan test --filter=Profile
php artisan test --filter=Invitation
php artisan test --filter=RolePermission

# Run frontend tests
npm run test
```

## API Quick Reference

```text
# User Management (admin only)
GET    /api/users
GET    /api/users/{id}
PUT    /api/users/{id}
DELETE /api/users/{id}

# Profile (self-service)
GET    /api/profile
PUT    /api/profile
POST   /api/profile/avatar
DELETE /api/profile/avatar
PUT    /api/profile/password
PUT    /api/profile/preferences

# Invitations
POST   /api/projects/{project}/invitations
GET    /api/projects/{project}/invitations
POST   /api/projects/{project}/invitations/{invitation}/resend
DELETE /api/projects/{project}/invitations/{invitation}
POST   /api/invitations/{token}/accept
POST   /api/invitations/{token}/decline
GET    /api/invitations/pending

# Members & Permissions
GET    /api/projects/{project}/members
POST   /api/projects/{project}/members
PUT    /api/projects/{project}/members/{user}
DELETE /api/projects/{project}/members/{user}
GET    /api/projects/{project}/permissions
GET    /api/projects/{project}/members/assignable
```
