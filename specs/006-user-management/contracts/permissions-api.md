# API Contract: Roles & Permissions + Member Management

**Base URL**: `/api`
**Auth**: All endpoints require `auth:sanctum` middleware

---

## GET /projects/{project}/members

List all members of a project with their roles.

**Access**: Any project member

**Response 200**:

```json
{
  "data": [
    {
      "id": 1,
      "user": {
        "id": 10,
        "name": "John Doe",
        "email": "john@example.com",
        "avatar_url": "/storage/avatars/abc123.jpg"
      },
      "role": "owner",
      "joined_at": "2026-01-15T10:00:00Z"
    },
    {
      "id": 2,
      "user": {
        "id": 11,
        "name": "Jane Smith",
        "email": "jane@example.com",
        "avatar_url": null
      },
      "role": "lead",
      "joined_at": "2026-02-01T14:00:00Z"
    }
  ]
}
```

---

## POST /projects/{project}/members

Add an existing user as a project member directly (without invitation).

**Access**: Project owner only

**Request Body**:

```json
{
  "user_id": 15,
  "role": "member"
}
```

**Validation**:

- user_id: required, exists in users table
- role: required, in [lead, member, viewer]
- User must not already be a member

**Response 201**:

```json
{
  "data": {
    "id": 5,
    "user": { "id": 15, "name": "New Member", "email": "new@example.com" },
    "role": "member",
    "joined_at": "2026-03-10T10:00:00Z"
  },
  "message": "Member added successfully"
}
```

**Response 409**: User already a member

---

## PUT /projects/{project}/members/{user}

Update a member's role within the project.

**Access**: Project owner only

**Request Body**:

```json
{
  "role": "lead"
}
```

**Validation**:

- role: required, in [owner, lead, member, viewer]
- Cannot change the last owner's role (project must always have at least one owner)
- Cannot change own role if you are the only owner

**Response 200**:

```json
{
  "data": {
    "id": 2,
    "user": { "id": 11, "name": "Jane Smith" },
    "role": "lead"
  },
  "message": "Member role updated successfully"
}
```

**Response 400**: Cannot remove last owner
**Response 403**: Insufficient permissions

---

## DELETE /projects/{project}/members/{user}

Remove a member from the project.

**Access**: Project owner only (or member can remove self)

**Behavior**:

- Unassigns all tasks assigned to the removed member within this project
- Creates activity log entry
- Notifies the removed member

**Response 200**:

```json
{
  "message": "Member removed from project",
  "data": {
    "unassigned_tasks_count": 3
  }
}
```

**Response 400**: Cannot remove the last owner
**Response 403**: Insufficient permissions

---

## GET /projects/{project}/permissions

Get the permission matrix for the project (what each role can do).

**Access**: Any project member

**Response 200**:

```json
{
  "data": {
    "roles": {
      "owner": {
        "label": "Owner",
        "description": "Full control over the project",
        "permissions": [
          "project.view", "project.edit", "project.delete", "project.archive",
          "task.create", "task.edit", "task.delete", "task.assign",
          "member.invite", "member.manage_roles",
          "label.create", "label.edit", "label.delete",
          "comment.create", "comment.edit_own", "comment.delete_own",
          "trash.view", "trash.restore", "trash.force_delete"
        ]
      },
      "lead": {
        "label": "Lead",
        "description": "Can manage tasks, invite members, and edit project",
        "permissions": [
          "project.view", "project.edit",
          "task.create", "task.edit", "task.delete", "task.assign",
          "member.invite",
          "label.create", "label.edit", "label.delete",
          "comment.create", "comment.edit_own", "comment.delete_own",
          "trash.view", "trash.restore"
        ]
      },
      "member": {
        "label": "Member",
        "description": "Can create tasks, comment, and collaborate",
        "permissions": [
          "project.view",
          "task.create", "task.edit_own",
          "comment.create", "comment.edit_own", "comment.delete_own"
        ]
      },
      "viewer": {
        "label": "Viewer",
        "description": "Read-only access to the project",
        "permissions": [
          "project.view"
        ]
      }
    },
    "current_user_role": "owner",
    "current_user_permissions": ["project.view", "project.edit", "..."]
  }
}
```

---

## GET /projects/{project}/members/assignable

Get list of members who can be assigned tasks (excludes viewers).

**Access**: Any project member with task.assign permission

**Response 200**:

```json
{
  "data": [
    {
      "id": 10,
      "name": "John Doe",
      "email": "john@example.com",
      "avatar_url": "/storage/avatars/abc123.jpg",
      "role": "owner"
    },
    {
      "id": 11,
      "name": "Jane Smith",
      "avatar_url": null,
      "role": "lead"
    }
  ]
}
```

---

## Task Assignment (extends existing task endpoints)

### PUT /tasks/{id} (MODIFY existing)

Add assignee_id to task update. When assignee changes, create notification.

**Additional behavior on assignee change**:

- Create notification (type: `task_assigned`) for new assignee
- Create notification (type: `task_unassigned`) for previous assignee (if any)
- Log activity (type: `task_assigned`) with old and new assignee data
- Only users with `task.assign` permission can change assignee_id
- Assignee must be a member of the project (not a viewer)
