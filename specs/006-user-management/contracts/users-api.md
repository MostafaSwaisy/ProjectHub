# API Contract: User Management

**Base URL**: `/api`
**Auth**: All endpoints require `auth:sanctum` middleware
**Access**: Admin role only (via `role:admin` middleware)

---

## GET /users

List all users with search, filter, and pagination.

**Query Parameters**:

| Param | Type | Required | Description |
|-------|------|----------|-------------|
| search | string | No | Search by name or email (partial match) |
| role | string | No | Filter by system role: admin, instructor, student |
| status | string | No | Filter: active, deleted |
| sort_by | string | No | Sort field: name, email, created_at (default: created_at) |
| sort_dir | string | No | Sort direction: asc, desc (default: desc) |
| per_page | integer | No | Items per page: 10, 20, 50 (default: 20) |
| page | integer | No | Page number (default: 1) |

**Response 200**:

```json
{
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "avatar_url": "/storage/avatars/abc123.jpg",
      "bio": "Project manager",
      "role": {
        "id": 2,
        "name": "instructor"
      },
      "projects_count": 5,
      "tasks_count": 12,
      "created_at": "2026-01-15T10:00:00Z",
      "status": "active"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 20,
    "total": 98
  }
}
```

---

## GET /users/{id}

Get a single user's details.

**Response 200**:

```json
{
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "avatar_url": "/storage/avatars/abc123.jpg",
    "bio": "Project manager",
    "role": {
      "id": 2,
      "name": "instructor"
    },
    "projects": [
      {
        "id": 1,
        "title": "Project Alpha",
        "role": "owner"
      }
    ],
    "created_at": "2026-01-15T10:00:00Z",
    "updated_at": "2026-03-01T14:30:00Z",
    "status": "active"
  }
}
```

**Response 404**: User not found

---

## PUT /users/{id}

Admin update of user details (name, email, system role).

**Request Body**:

```json
{
  "name": "John Updated",
  "email": "john.new@example.com",
  "role_id": 2
}
```

**Validation**:

- name: required, string, max 255
- email: required, email, unique (excluding target user)
- role_id: required, exists in roles table

**Response 200**:

```json
{
  "data": { "...user object..." },
  "message": "User updated successfully"
}
```

**Response 422**: Validation error

---

## DELETE /users/{id}

Soft-delete a user account. Admin cannot delete themselves.

**Response 200**:

```json
{
  "message": "User deactivated successfully"
}
```

**Response 403**: Cannot delete own account
**Response 404**: User not found
