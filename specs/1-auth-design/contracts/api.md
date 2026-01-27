# API Contracts: Authentication Endpoints

**Feature**: Authentication Pages Design
**Date**: 2026-01-27

## Overview

These are the existing API contracts that the improved authentication frontend pages will consume. No backend changes are required - the frontend refactoring uses the same API endpoints.

---

## Login Endpoint

**Endpoint**: `POST /api/auth/login`

**Purpose**: Authenticate user with email and password

### Request

```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

**Headers**:
```
Content-Type: application/json
```

### Response (200 OK)

```json
{
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": {
      "id": 1,
      "name": "student"
    }
  }
}
```

### Response (401 Unauthorized)

```json
{
  "message": "The provided credentials are invalid."
}
```

### Error Cases

| Status | Message | When |
|--------|---------|------|
| 401 | The provided credentials are invalid. | Email/password combo doesn't match |
| 422 | Validation failed | Email or password field missing |
| 500 | Internal server error | Server error during authentication |

---

## Register Endpoint

**Endpoint**: `POST /api/auth/register`

**Purpose**: Create new user account

### Request

```json
{
  "name": "Jane Smith",
  "email": "jane@example.com",
  "password": "SecurePassword123",
  "password_confirmation": "SecurePassword123",
  "role": "instructor"
}
```

### Response (201 Created)

```json
{
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "user": {
    "id": 2,
    "name": "Jane Smith",
    "email": "jane@example.com",
    "role": {
      "id": 2,
      "name": "instructor"
    }
  }
}
```

### Response (422 Unprocessable Entity)

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email has already been taken."],
    "password": ["The password confirmation does not match."],
    "role": ["The selected role is invalid."]
  }
}
```

### Validation Rules

| Field | Rules | Error Message |
|-------|-------|---------------|
| `name` | required, string, max:255 | Name is required |
| `email` | required, email, unique:users | Email must be valid and unique |
| `password` | required, min:8, confirmed, regex:/[0-9]/ | Password must be 8+ chars with number |
| `password_confirmation` | required, same:password | Passwords must match |
| `role` | required, in:student,instructor | Role must be student or instructor |

### Error Cases

| Status | Field | When |
|--------|-------|------|
| 422 | name | Name missing or invalid |
| 422 | email | Email invalid, duplicate, or missing |
| 422 | password | Password too short, missing number, or missing |
| 422 | password_confirmation | Doesn't match password |
| 422 | role | Not student or instructor |
| 500 | (general) | Server error during registration |

---

## Forgot Password Endpoint

**Endpoint**: `POST /api/auth/password/email`

**Purpose**: Request password reset email

### Request

```json
{
  "email": "user@example.com"
}
```

### Response (200 OK)

```json
{
  "message": "We have emailed your password reset link!"
}
```

### Response (422 Unprocessable Entity)

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["We can't find a user with that email address."]
  }
}
```

### Behavior

- **Always returns 200**: For security, returns success even if email not found (prevents email enumeration)
- **Email sent if found**: Password reset link sent to user's email
- **Link expires in**: 60 minutes
- **Resend limit**: Throttled (typically 1 per minute per IP)

### Frontend Handling

Since API always returns 200:
- Always show success message: "If an account exists with that email..."
- Don't reveal whether email was found in UI
- Give user time to check email

---

## Reset Password Endpoint

**Endpoint**: `POST /api/auth/password/reset`

**Purpose**: Reset password with valid reset token

### Request

```json
{
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "email": "user@example.com",
  "password": "NewPassword123",
  "password_confirmation": "NewPassword123"
}
```

### Response (200 OK)

```json
{
  "message": "Your password has been reset!"
}
```

### Response (400 Bad Request - Invalid Token)

```json
{
  "message": "This password reset token is invalid."
}
```

### Response (422 Unprocessable Entity - Validation)

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "password": ["The password confirmation does not match."],
    "token": ["This password reset token has expired."]
  }
}
```

### Validation Rules

| Field | Rules | Error |
|-------|-------|-------|
| `token` | required, valid, not_expired (60 min) | Invalid or expired token |
| `email` | required, email, exists | Email must exist in system |
| `password` | required, min:8, confirmed, regex:/[0-9]/ | Password requirements |
| `password_confirmation` | required, same:password | Must match password |

### Token Format

Token is typically:
- Included in reset link URL: `/auth/reset-password?token=xxx&email=user@example.com`
- Extracted from URL and passed to API
- Single-use (consumed on success)

---

## Get Current User Endpoint

**Endpoint**: `GET /api/user`

**Purpose**: Get authenticated user's profile

### Request

**Headers**:
```
Authorization: Bearer {token}
```

### Response (200 OK)

```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "role": {
    "id": 1,
    "name": "student"
  },
  "created_at": "2026-01-27T10:00:00.000Z",
  "updated_at": "2026-01-27T10:00:00.000Z"
}
```

### Response (401 Unauthorized)

```json
{
  "message": "Unauthenticated."
}
```

**Triggered when**:
- No Authorization header provided
- Token is invalid
- Token has expired

### Usage in Frontend

```javascript
// Check if user is authenticated on app startup
const { token } = useAuth()
if (token.value) {
  try {
    const user = await fetchCurrentUser()
    // User authenticated, continue to app
  } catch (err) {
    // Token invalid, redirect to login
  }
}
```

---

## Logout Endpoint

**Endpoint**: `POST /api/auth/logout`

**Purpose**: Invalidate current session/token

### Request

**Headers**:
```
Authorization: Bearer {token}
```

### Response (204 No Content)

Empty response, status 204

### Response (401 Unauthorized)

```json
{
  "message": "Unauthenticated."
}
```

### Behavior

- Revokes the current token
- User can immediately log in again with credentials
- No email confirmation needed

---

## Authentication Flow Diagram

```
1. USER ENTERS LOGIN CREDENTIALS
   ↓
2. FRONTEND VALIDATES LOCALLY
   - Check email format
   - Check password not empty
   ↓
3. FRONTEND SUBMITS TO POST /api/auth/login
   ↓
4. BACKEND VALIDATES
   - Email exists
   - Password correct
   ↓
5. BACKEND RETURNS TOKEN + USER
   ↓
6. FRONTEND STORES TOKEN
   - localStorage['auth_token']
   - axios default header: Authorization: Bearer {token}
   ↓
7. FRONTEND NAVIGATES TO DASHBOARD
   ↓
8. FUTURE REQUESTS INCLUDE BEARER TOKEN
   - All API calls automatically include token
   - Backend validates token on each request
```

---

## Error Handling in Frontend

### HTTP Error Mapping

| Status | Frontend Action | Example |
|--------|-----------------|---------|
| 200-299 | Success handling | Show confirmation, navigate |
| 400 | Field validation | Show field-specific errors |
| 401 | Not authenticated | Clear token, redirect to login |
| 403 | Not authorized | Show permission error |
| 422 | Validation failed | Map errors to form fields |
| 500+ | Server error | Show general error message |

### Error Message Structure

```javascript
// Backend sends validation errors like:
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["Email is required"],
    "password": ["Password too short"]
  }
}

// Frontend maps to form state:
form.errors = response.data.errors
// Result: errors.email = "Email is required"
```

### Retry Logic

For transient errors (5xx):
- Show user: "Something went wrong, please try again"
- Implement exponential backoff for retries
- Max 3 retries with 1s, 2s, 4s delays

---

## Token Management

### Token Storage

```javascript
// Store after login
localStorage.setItem('auth_token', response.data.token)

// Retrieve on app startup
const token = localStorage.getItem('auth_token')

// Clear on logout
localStorage.removeItem('auth_token')
```

### Token in Requests

```javascript
// Axios automatically includes in all requests
axios.defaults.headers.common['Authorization'] = `Bearer ${token}`

// Or manually per request
axios.get('/api/user', {
  headers: {
    Authorization: `Bearer ${token}`
  }
})
```

### Token Refresh

Currently not implemented. If token expires:
- API returns 401
- Frontend clears token
- User redirected to login

Future consideration: Implement refresh token endpoint if needed.

---

## Rate Limiting

These endpoints may have rate limiting. Frontend should:
- Show friendly error: "Too many attempts, please try again later"
- Disable form submission temporarily
- Encourage exponential backoff

**Typical limits**:
- Login: 5 attempts per minute per IP
- Register: 1 per minute per email
- Forgot password: 1 per minute per IP

---

## Frontend Responsibility

### Input Validation

Frontend must validate:
- Email format (basic regex)
- Password requirements (8+ chars, 1 number, 1 letter)
- Confirmation matches (for register/reset)
- No empty required fields

**Note**: Backend will also validate - frontend validation is for UX only.

### Security Considerations

- Never log passwords or tokens
- Clear sensitive data from forms after successful submission
- Use HTTPS in production (enforced by Sanctum)
- Don't store sensitive data in localStorage except token
- Token should be httpOnly cookie in production (current: localStorage)

### Accessibility

- Form labels properly associated
- Error messages in alert role
- Success messages in status role
- Keyboard navigation throughout
- WCAG 2.1 AA compliance
