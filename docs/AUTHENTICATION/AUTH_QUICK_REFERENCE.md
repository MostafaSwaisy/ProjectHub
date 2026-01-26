# Authentication Quick Reference Guide

## Created Files Checklist

### Controllers (4 files)
- [x] RegisterController.php - User registration with token generation
- [x] LoginController.php - User login with credential verification
- [x] LogoutController.php - Token invalidation and logout
- [x] PasswordResetController.php - Password reset flow

### Request Validation Classes (4 files)
- [x] RegisterRequest.php - Validates registration input
- [x] LoginRequest.php - Validates login input
- [x] PasswordResetRequest.php - Validates password reset request
- [x] ResetPasswordRequest.php - Validates password update with token

### Email Templates (1 file)
- [x] password-reset.blade.php - HTML email template for password reset

## API Endpoints Overview

### Authentication Endpoints

#### 1. Register User (Public)
```
POST /api/auth/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "SecurePass123!",
  "role": 1
}

Response (201):
{
  "message": "User registered successfully.",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role_id": 1
  },
  "token": "plainTextToken_here"
}

Error Response (422):
{
  "message": "Validation failed",
  "errors": {
    "email": ["The email has already been taken."]
  }
}
```

#### 2. Login User (Public)
```
POST /api/auth/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "SecurePass123!"
}

Response (200):
{
  "message": "Login successful.",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role_id": 1
  },
  "token": "plainTextToken_here"
}

Error Response (401):
{
  "message": "Invalid credentials."
}
```

#### 3. Logout User (Protected)
```
POST /api/auth/logout
Authorization: Bearer plainTextToken_here
Content-Type: application/json

Response (200):
{
  "message": "Logout successful."
}

Error Response (401):
{
  "message": "Unauthenticated"
}
```

#### 4. Request Password Reset (Public)
```
POST /api/auth/forgot-password
Content-Type: application/json

{
  "email": "john@example.com"
}

Response (200):
{
  "message": "Password reset link has been sent to your email address."
}

Error Response (422):
{
  "message": "Validation failed",
  "errors": {
    "email": ["The email does not exist in our system."]
  }
}
```

#### 5. Reset Password with Token (Public)
```
POST /api/auth/reset-password
Content-Type: application/json

{
  "token": "reset_token_from_email",
  "email": "john@example.com",
  "password": "NewSecurePass123!",
  "password_confirmation": "NewSecurePass123!"
}

Response (200):
{
  "message": "Password has been reset successfully."
}

Error Response (422):
{
  "message": "The password reset token is invalid."
}
```

## Validation Rules Summary

### RegisterRequest
| Field | Rules | Notes |
|-------|-------|-------|
| name | required, string, max:255 | User full name |
| email | required, email, unique:users | Unique email address |
| password | required, min:8, mixed case, numbers, symbols | Strong password required |
| role | required, exists:roles,id | Must reference existing role |

### LoginRequest
| Field | Rules | Notes |
|-------|-------|-------|
| email | required, email | Must be valid email format |
| password | required, string | Case-sensitive |

### PasswordResetRequest
| Field | Rules | Notes |
|-------|-------|-------|
| email | required, email, exists:users | Must exist in system |

### ResetPasswordRequest
| Field | Rules | Notes |
|-------|-------|-------|
| token | required, string | From password reset email |
| email | required, email, exists:users | Must match reset request |
| password | required, min:8, mixed case, numbers, symbols | New password must be strong |

## Password Requirements

The password validation uses Laravel's `Illuminate\Validation\Rules\Password` class with:
- **Minimum length:** 8 characters
- **Mixed case:** Must contain both uppercase and lowercase letters
- **Numbers:** Must contain at least one numeric digit
- **Symbols:** Must contain at least one special character (!@#$%^&*)

Example valid passwords:
- `SecurePass123!`
- `MyPassword@2024`
- `Correct#Horse123`

Example invalid passwords:
- `password123` (no uppercase, no symbols)
- `PASSWORD123!` (no lowercase)
- `Pass12!` (less than 8 characters)

## Token Management

### Sanctum Token Generation
```php
// Generate token
$token = $user->createToken('API Token');

// Access the plain text token (only available on creation)
$plainTextToken = $token->plainTextToken;

// Store in frontend (localStorage, sessionStorage, etc.)
localStorage.setItem('api_token', plainTextToken);
```

### Using Tokens in Requests
```javascript
// JavaScript/Axios example
const token = localStorage.getItem('api_token');

axios.post('/api/auth/logout', {}, {
  headers: {
    'Authorization': `Bearer ${token}`
  }
});
```

### Token Deletion
```php
// On logout - delete current token
$request->user()->currentAccessToken()->delete();

// Delete all tokens for a user
$user->tokens()->delete();

// Delete specific token
$user->tokens()->where('id', $tokenId)->delete();
```

## Password Reset Flow

### User Flow
1. User clicks "Forgot Password"
2. User enters email address
3. System sends password reset email with unique token
4. User clicks reset link in email
5. User enters new password
6. System validates token and updates password
7. User can login with new password

### System Flow
```
PasswordResetRequest (validate email)
    ↓
sendReset() → Password::sendResetLink()
    ↓
User receives email with token
    ↓
ResetPasswordRequest (validate token + new password)
    ↓
resetPassword() → Password::reset()
    ↓
User password updated in database
```

## Error Responses Reference

| Status | Scenario | Message |
|--------|----------|---------|
| 201 | Registration successful | "User registered successfully." |
| 200 | Login successful | "Login successful." |
| 200 | Logout successful | "Logout successful." |
| 200 | Password reset link sent | "Password reset link has been sent to your email address." |
| 200 | Password reset successful | "Password has been reset successfully." |
| 401 | Invalid login credentials | "Invalid credentials." |
| 401 | Missing/invalid token | "Unauthenticated" |
| 422 | Expired reset token | "The password reset token is invalid." |
| 422 | Validation failed | Returns validation error details |
| 500 | Server error | "Registration/Login/Logout failed." |

## Implementation Notes

### Important Requirements
1. **Sanctum Configuration**: Ensure Laravel Sanctum is installed and configured
2. **Mail Configuration**: Configure `MAIL_*` environment variables for password reset emails
3. **User Model**: Must have fillable attributes: name, email, password, role_id
4. **Role Model**: Must exist with proper relationships
5. **Database**: Ensure all required tables and migrations are run

### Security Best Practices Implemented
- Passwords hashed using bcrypt (Hash::make)
- Password validation enforces strong requirements
- Unique email validation at registration
- Token-based authentication with Sanctum
- Proper HTTP status codes
- Exception handling with user-friendly messages
- CSRF protection (automatic in Laravel)
- Sanctum token deletion on logout

### Environment Variables Needed
```env
MAIL_DRIVER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=465
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@projecthub.local
MAIL_FROM_NAME="ProjectHub"
```

## File Locations (Absolute Paths)

```
d:\GGProject\ProjectHub\
├── app\Http\Controllers\Auth\
│   ├── RegisterController.php
│   ├── LoginController.php
│   ├── LogoutController.php
│   └── PasswordResetController.php
├── app\Http\Requests\Auth\
│   ├── RegisterRequest.php
│   ├── LoginRequest.php
│   ├── PasswordResetRequest.php
│   └── ResetPasswordRequest.php
└── resources\views\emails\
    └── password-reset.blade.php
```

## Next Steps

1. **Configure Routes**: Add routes to `routes/api.php`
2. **Test Endpoints**: Use provided cURL/Postman examples
3. **Setup Frontend**: Implement auth composable to consume these endpoints
4. **Configure Mail**: Set up email credentials for password reset
5. **Run Migrations**: Ensure all database tables exist
6. **Test Authentication Flow**: Register, Login, Logout, Password Reset

## Related Tasks
- AUTH-006: Login.vue page (frontend)
- AUTH-007: Register.vue page (frontend)
- AUTH-008: ResetPassword.vue page (frontend)
- AUTH-009: useAuth composable (frontend)
- RBAC-001 to RBAC-006: Authorization & policies
