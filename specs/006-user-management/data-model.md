# Data Model: User Management System

**Feature**: 006-user-management
**Date**: 2026-03-10

## Entity Relationship Overview

```text
┌─────────┐     ┌──────────────┐     ┌──────────┐
│  User    │────<│ ProjectMember│>────│ Project  │
│          │     │ (role)       │     │          │
└────┬─────┘     └──────────────┘     └────┬─────┘
     │                                      │
     │  ┌──────────────┐                    │
     │──<│ Invitation   │>──────────────────┘
     │   │ (token,role) │
     │   └──────────────┘
     │
     │  ┌──────────────┐
     │──<│UserPreference│
     │   └──────────────┘
     │
     │  ┌──────────────┐
     └──<│ Task         │ (via assignee_id)
         └──────────────┘
```

## Entities

### User (MODIFY existing)

Extends existing `users` table with profile fields.

| Field | Type | Constraints | Notes |
|-------|------|-------------|-------|
| id | integer | PK, auto-increment | Exists |
| name | string | required | Exists |
| email | string | required, unique | Exists |
| email_verified_at | datetime | nullable | Exists |
| password | string | required, hashed | Exists |
| role_id | integer | FK → roles | Exists (system role) |
| avatar_url | string | nullable, max 500 | **NEW** - path to avatar image |
| bio | text | nullable, max 500 | **NEW** - short bio/description |
| remember_token | string | nullable | Exists |
| deleted_at | datetime | nullable | Exists (soft delete) |
| deleted_by | integer | nullable, FK → users | Exists |
| created_at | datetime | auto | Exists |
| updated_at | datetime | auto | Exists |

**Validation rules (profile update)**:

- name: required, string, max 255
- email: required, email, unique (excluding self)
- bio: nullable, string, max 500
- avatar: nullable, image (jpg/png), max 5MB

---

### Invitation (NEW)

Tracks pending project membership invitations.

| Field | Type | Constraints | Notes |
|-------|------|-------------|-------|
| id | integer | PK, auto-increment | |
| project_id | integer | FK → projects, required | Target project |
| email | string | required, email | Invitee email |
| role | string | required, in: lead/member/viewer | Role to assign on acceptance |
| token | string | unique, 64 chars | Hashed acceptance token |
| status | string | default: pending, in: pending/accepted/declined/expired | Current state |
| invited_by | integer | FK → users, required | User who sent invitation |
| accepted_at | datetime | nullable | When invitation was accepted |
| expires_at | datetime | required | Default: 7 days from creation |
| created_at | datetime | auto | |
| updated_at | datetime | auto | |

**Indexes**:

- unique(project_id, email) where status = 'pending' — prevent duplicate pending invitations
- index(token) — fast token lookup
- index(email) — find invitations by email
- index(status) — filter by status

**Validation rules**:

- email: required, email format
- role: required, in [lead, member, viewer] (owner cannot be assigned via invitation)
- project_id: required, exists in projects table

**State transitions**:

```text
pending → accepted (user accepts)
pending → declined (user declines)
pending → expired (auto-expire after 7 days)
pending → pending (resend resets expires_at)
```

---

### ProjectMember (MODIFY existing)

Update role values from `owner/editor/viewer` to `owner/lead/member/viewer`.

| Field | Type | Constraints | Notes |
|-------|------|-------------|-------|
| id | integer | PK | Exists |
| project_id | integer | FK → projects | Exists |
| user_id | integer | FK → users | Exists |
| role | string | in: owner/lead/member/viewer | **MODIFY** - add lead, rename editor→member |
| deleted_at | datetime | nullable | Exists |
| deleted_by | integer | nullable | Exists |
| created_at | datetime | auto | Exists |
| updated_at | datetime | auto | Exists |

**Migration**: Update all existing `editor` values to `member`.

---

### UserPreference (EXISTS - no changes)

Existing key-value store for user settings. Used for notification preferences.

| Field | Type | Constraints | Notes |
|-------|------|-------------|-------|
| id | integer | PK | Exists |
| user_id | integer | FK → users | Exists |
| key | string | required | Exists |
| value | text | nullable | Exists |
| created_at | datetime | auto | Exists |
| updated_at | datetime | auto | Exists |

**New preference keys to use**:

- `notification_frequency`: "realtime" | "daily" | "weekly" | "none"
- `notification_email`: "true" | "false"
- `notification_assignments`: "true" | "false"
- `notification_comments`: "true" | "false"

---

### Notification (EXISTS - no changes)

Existing notification model. Used for task assignment and invitation notifications.

| Field | Type | Constraints | Notes |
|-------|------|-------------|-------|
| id | integer | PK | Exists |
| user_id | integer | FK → users | Exists |
| type | string | required | Exists |
| data | json | required | Exists |
| read_at | datetime | nullable | Exists |
| created_at | datetime | auto | Exists |
| updated_at | datetime | auto | Exists |

**New notification types**:

- `task_assigned`: `{ task_id, task_title, project_id, project_title, assigned_by }`
- `task_unassigned`: `{ task_id, task_title, project_id, project_title, unassigned_by }`
- `invitation_received`: `{ invitation_id, project_id, project_title, invited_by, role }`
- `invitation_accepted`: `{ invitation_id, project_id, user_name }` (sent to inviter)
- `role_changed`: `{ project_id, project_title, old_role, new_role, changed_by }`

---

## Permission Matrix (Code-defined constant)

Not stored in database. Defined as a PHP constant/config array.

```text
PERMISSIONS = {
  'owner': [
    'project.view', 'project.edit', 'project.delete', 'project.archive',
    'task.create', 'task.edit', 'task.delete', 'task.assign',
    'member.invite', 'member.manage_roles',
    'label.create', 'label.edit', 'label.delete',
    'comment.create', 'comment.edit_own', 'comment.delete_own',
    'trash.view', 'trash.restore', 'trash.force_delete'
  ],
  'lead': [
    'project.view', 'project.edit',
    'task.create', 'task.edit', 'task.delete', 'task.assign',
    'member.invite',
    'label.create', 'label.edit', 'label.delete',
    'comment.create', 'comment.edit_own', 'comment.delete_own',
    'trash.view', 'trash.restore'
  ],
  'member': [
    'project.view',
    'task.create', 'task.edit_own',
    'comment.create', 'comment.edit_own', 'comment.delete_own'
  ],
  'viewer': [
    'project.view'
  ]
}
```

---

## Migration Summary

| Migration | Description | Tables Affected |
|-----------|-------------|-----------------|
| add_profile_fields_to_users | Add avatar_url, bio columns | users |
| create_invitations_table | Create invitations table | invitations (new) |
| update_project_members_roles | Rename editor→member, document new roles | project_members |
