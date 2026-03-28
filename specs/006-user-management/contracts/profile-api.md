# API Contract: User Profile

**Base URL**: `/api`
**Auth**: All endpoints require `auth:sanctum` middleware
**Access**: Authenticated user (self-service)

---

## GET /profile

Get the current user's full profile.

**Response 200**:

```json
{
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "avatar_url": "/storage/avatars/abc123.jpg",
    "bio": "Project manager with 5 years experience",
    "role": {
      "id": 2,
      "name": "instructor"
    },
    "preferences": {
      "notification_frequency": "realtime",
      "notification_email": true,
      "notification_assignments": true,
      "notification_comments": true
    },
    "created_at": "2026-01-15T10:00:00Z",
    "updated_at": "2026-03-01T14:30:00Z"
  }
}
```

---

## PUT /profile

Update the current user's profile information.

**Request Body**:

```json
{
  "name": "John Updated",
  "email": "john.new@example.com",
  "bio": "Senior project manager"
}
```

**Validation**:

- name: required, string, max 255
- email: required, email, unique (excluding self)
- bio: nullable, string, max 500

**Response 200**:

```json
{
  "data": { "...updated profile object..." },
  "message": "Profile updated successfully"
}
```

**Response 422**: Validation error

---

## POST /profile/avatar

Upload or update avatar image.

**Request**: `multipart/form-data`

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| avatar | file | Yes | Image file (jpg, png), max 5MB |

**Response 200**:

```json
{
  "data": {
    "avatar_url": "/storage/avatars/new-abc123.jpg"
  },
  "message": "Avatar updated successfully"
}
```

**Response 422**: Invalid file type or size

---

## DELETE /profile/avatar

Remove the current user's avatar (revert to initials).

**Response 200**:

```json
{
  "message": "Avatar removed successfully"
}
```

---

## PUT /profile/password

Change the current user's password.

**Request Body**:

```json
{
  "current_password": "old-password",
  "password": "new-password",
  "password_confirmation": "new-password"
}
```

**Validation**:

- current_password: required, must match current password
- password: required, min 8, confirmed, different from current_password

**Response 200**:

```json
{
  "message": "Password changed successfully"
}
```

**Response 422**: Validation error (wrong current password, too short, etc.)

---

## PUT /profile/preferences

Update notification and display preferences.

**Request Body**:

```json
{
  "notification_frequency": "realtime",
  "notification_email": true,
  "notification_assignments": true,
  "notification_comments": false
}
```

**Validation**:

- notification_frequency: in [realtime, daily, weekly, none]
- notification_email: boolean
- notification_assignments: boolean
- notification_comments: boolean

**Response 200**:

```json
{
  "data": {
    "notification_frequency": "realtime",
    "notification_email": true,
    "notification_assignments": true,
    "notification_comments": false
  },
  "message": "Preferences updated successfully"
}
```
