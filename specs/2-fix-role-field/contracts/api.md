# API Contracts: Registration Endpoint

**Date**: 2026-01-27
**Feature**: Role field validation in registration
**Status**: ✅ Existing endpoint is fully compliant

## POST /api/auth/register

### Description

Register a new user account with email, password, and role assignment.

### Request

```http
POST /api/auth/register HTTP/1.1
Content-Type: application/json
Accept: application/json

{
  "name": "string",
  "email": "string",
  "password": "string",
  "password_confirmation": "string",
  "role": "string"
}
```

### Request Parameters

| Parameter | Type | Required | Validation | Description |
|-----------|------|----------|-----------|-------------|
| `name` | string | Yes | 2-255 characters | User's full name |
| `email` | string | Yes | Valid email, unique | User's email address |
| `password` | string | Yes | 8+ chars, 1 number, 1 letter | User's password (will be hashed) |
| `password_confirmation` | string | Yes | Must match password | Password confirmation for verification |
| `role` | string | Yes | 'student' or 'instructor' | User's role selection |

### Success Response

```json
HTTP/1.1 200 OK
Content-Type: application/json

{
  "success": true,
  "message": "Registration successful",
  "user": {
    "id": 123,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "student",
    "role_id": 3,
    "email_verified_at": "2026-01-27T11:30:00Z",
    "created_at": "2026-01-27T11:30:00Z"
  },
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
}
```

### Error Responses

#### 422 Validation Error

```json
HTTP/1.1 422 Unprocessable Entity
Content-Type: application/json

{
  "message": "The given data was invalid.",
  "errors": {
    "name": ["The name field is required."],
    "email": ["The email has already been taken."],
    "password": ["The password must be at least 8 characters."],
    "role": ["The role must be either student or instructor."]
  }
}
```

#### 400 Bad Request

```json
HTTP/1.1 400 Bad Request
Content-Type: application/json

{
  "success": false,
  "message": "Invalid request data"
}
```

### Validation Rules (Backend)

| Field | Rule | Error Message |
|-------|------|---------------|
| `name` | Required, string, 2-255 chars | "The name field is required." |
| `email` | Required, valid email, unique in DB | "The email has already been taken." |
| `password` | Required, string, 8+ chars, 1 number, 1 letter | "The password must be at least 8 characters." |
| `password_confirmation` | Required, matches password field | "The password confirmation does not match." |
| `role` | Required, enum: 'student', 'instructor' | "The role must be either student or instructor." |

### Implementation Details

**Endpoint**: `app/Http/Controllers/Auth/RegisterController.php`

**Process**:
1. Receive JSON request with all fields including role
2. Validate all fields (including role enum validation)
3. Hash password using bcrypt
4. Create user with role_id mapped from role string
5. Mark email as verified (if auto-verification enabled)
6. Generate authentication token
7. Return user data with token

**Database**:
- Inserts into `users` table with `role_id` foreign key
- `role_id` is mapped from the role string:
  - 'student' → role_id 3
  - 'instructor' → role_id 2
  - Invalid role → 422 error

**Response Headers**:
- `Content-Type: application/json`
- `Authorization: Bearer {token}` in response

### Status Codes

| Code | Meaning | When |
|------|---------|------|
| 200 | Success | Registration successful, user created, token issued |
| 400 | Bad Request | Malformed JSON or missing required fields |
| 422 | Validation Error | Validation fails on any field (including role) |
| 500 | Server Error | Database error or unexpected exception |

### Notes for Frontend

1. **Role is Required**: Always include role field in request—form should prevent submission without it
2. **Enum Validation**: Backend validates role is exactly 'student' or 'instructor', frontend should enforce same
3. **Token Handling**: Response includes authentication token—save to localStorage for future API calls
4. **Email Uniqueness**: Backend checks email isn't already registered—show appropriate error if duplicate
5. **Password Requirements**: Frontend shows requirements to user, backend validates same rules

### cURL Example

```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Jane Smith",
    "email": "jane@example.com",
    "password": "SecurePass123",
    "password_confirmation": "SecurePass123",
    "role": "student"
  }'
```

### Axios Example (Frontend)

```javascript
// From useAuth composable
const register = async (userData) => {
  try {
    const response = await api.post('/auth/register', {
      name: userData.name,
      email: userData.email,
      password: userData.password,
      password_confirmation: userData.password_confirmation,
      role: userData.role  // Include role field
    })

    // Save token and user data
    localStorage.setItem('token', response.data.token)
    localStorage.setItem('user', JSON.stringify(response.data.user))

    return response.data
  } catch (error) {
    throw error  // Error messages handled by component
  }
}
```

### Backward Compatibility

✅ **No breaking changes**: Role field already accepted by backend
✅ **Existing users unaffected**: Only impacts new registrations
✅ **Existing tokens remain valid**: No authentication changes
✅ **API response format unchanged**: Role included in existing response

---

## Related Endpoints

### POST /api/auth/login

Login with email and password. Returns authentication token.

### GET /api/user

Get current authenticated user. Requires valid token.
Returns user object including role and role_id.

### POST /api/auth/logout

Logout current user. Invalidates token.
