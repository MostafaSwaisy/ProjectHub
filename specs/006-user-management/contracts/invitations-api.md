# API Contract: Project Invitations

**Base URL**: `/api`
**Auth**: Most endpoints require `auth:sanctum` middleware
**Access**: Project owner/lead for sending; any user for accepting/declining

---

## POST /projects/{project}/invitations

Send an invitation to join a project.

**Access**: Project owner or lead only

**Request Body**:

```json
{
  "email": "newuser@example.com",
  "role": "member"
}
```

**Validation**:

- email: required, valid email format
- role: required, in [lead, member, viewer]
- Cannot invite someone who is already a project member
- Cannot have duplicate pending invitation for same email + project

**Response 201**:

```json
{
  "data": {
    "id": 15,
    "email": "newuser@example.com",
    "role": "member",
    "status": "pending",
    "invited_by": {
      "id": 1,
      "name": "John Doe"
    },
    "expires_at": "2026-03-17T10:00:00Z",
    "created_at": "2026-03-10T10:00:00Z"
  },
  "message": "Invitation sent successfully"
}
```

**Response 409**: User already a member or invitation already pending
**Response 403**: Insufficient permissions
**Response 422**: Validation error

---

## GET /projects/{project}/invitations

List all invitations for a project.

**Access**: Project owner or lead only

**Query Parameters**:

| Param | Type | Required | Description |
|-------|------|----------|-------------|
| status | string | No | Filter: pending, accepted, declined, expired |

**Response 200**:

```json
{
  "data": [
    {
      "id": 15,
      "email": "newuser@example.com",
      "role": "member",
      "status": "pending",
      "invited_by": {
        "id": 1,
        "name": "John Doe"
      },
      "expires_at": "2026-03-17T10:00:00Z",
      "created_at": "2026-03-10T10:00:00Z"
    }
  ]
}
```

---

## POST /projects/{project}/invitations/{invitation}/resend

Resend an expired or pending invitation (resets expiry).

**Access**: Project owner or lead only

**Response 200**:

```json
{
  "data": {
    "id": 15,
    "status": "pending",
    "expires_at": "2026-03-17T10:00:00Z"
  },
  "message": "Invitation resent successfully"
}
```

**Response 400**: Can only resend pending/expired invitations

---

## DELETE /projects/{project}/invitations/{invitation}

Cancel/revoke a pending invitation.

**Access**: Project owner or lead only

**Response 200**:

```json
{
  "message": "Invitation cancelled"
}
```

**Response 400**: Can only cancel pending invitations

---

## POST /invitations/{token}/accept

Accept an invitation using the token from the email link.

**Access**: Authenticated user only (email must match invitation)

**Response 200**:

```json
{
  "data": {
    "project_id": 5,
    "project_title": "Project Alpha",
    "role": "member"
  },
  "message": "Invitation accepted. You are now a member of Project Alpha."
}
```

**Response 404**: Invalid or expired token
**Response 403**: Email mismatch (logged-in user email != invitation email)
**Response 409**: Already a member of this project

---

## POST /invitations/{token}/decline

Decline an invitation.

**Access**: Authenticated user only (email must match invitation)

**Response 200**:

```json
{
  "message": "Invitation declined"
}
```

**Response 404**: Invalid or expired token

---

## GET /invitations/pending

List all pending invitations for the current user (by email).

**Access**: Authenticated user

**Response 200**:

```json
{
  "data": [
    {
      "id": 15,
      "project": {
        "id": 5,
        "title": "Project Alpha"
      },
      "role": "member",
      "invited_by": {
        "id": 1,
        "name": "John Doe"
      },
      "expires_at": "2026-03-17T10:00:00Z",
      "created_at": "2026-03-10T10:00:00Z"
    }
  ]
}
```

---

## POST /auth/register (MODIFY existing)

Extend registration to accept optional invitation token.

**Additional Request Field**:

```json
{
  "name": "New User",
  "email": "newuser@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "invitation_token": "optional-token-from-email-link"
}
```

**Behavior**: If `invitation_token` is valid and email matches, auto-accept the invitation after registration completes.
