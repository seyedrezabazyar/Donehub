# Authentication API Documentation

Base URL: `/api/v1/auth`

---

## Public Endpoints

### 1. Check User
**Endpoint:** `POST /check-user`  
**Rate Limit:** login throttle

Checks if a user exists and returns available authentication methods.

**Request:**
```json
{
  "identifier": "user@example.com"  // Email or phone number
}
```

**Response:**
```json
{
  "exists": true,
  "methods": ["password", "otp"],
  "preferred_method": "password",
  "identifier": "user@example.com"
}
```

---

### 2. Login with Password
**Endpoint:** `POST /login-password`  
**Rate Limit:** login throttle

Authenticates user with password.

**Request:**
```json
{
  "identifier": "user@example.com",  // Email or phone number
  "password": "SecurePass123!"
}
```

**Response:**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "username": "johndoe",
    "email": "user@example.com",
    "phone": "+989123456789",
    "email_verified_at": "2025-01-15T10:30:00.000000Z",
    "phone_verified_at": null,
    "avatar": "https://...",
    "roles": ["user"],
    "permissions": ["read:profile"]
  },
  "tokens": {
    "access_token": "1|abcdef...",
    "refresh_token": "2|ghijkl...",
    "token_type": "Bearer",
    "expires_in": 7200,
    "expires_at": "2025-01-15T12:30:00.000000Z",
    "refresh_expires_in": 604800,
    "refresh_expires_at": "2025-01-22T10:30:00.000000Z"
  }
}
```

---

### 3. Send OTP
**Endpoint:** `POST /send-otp`  
**Rate Limit:** 1/minute, 3/hour

Sends OTP code to user's email or phone.

**Request:**
```json
{
  "identifier": "user@example.com",  // Email or phone number
  "type": "auto",                    // "auto", "email", or "sms"
  "purpose": "login"                 // "login" or "registration"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Code sent to email",
  "expires_in": 300,
  "identifier": "user@example.com",
  "type": "email"
}
```

---

### 4. Verify OTP
**Endpoint:** `POST /verify-otp`  
**Rate Limit:** 3/minute

Verifies OTP code and returns tokens.

**Request:**
```json
{
  "identifier": "user@example.com",
  "otp": "123456",
  "purpose": "login"  // "login" or "registration"
}
```

**Response (Login):**
```json
{
  "user": { /* user object */ },
  "tokens": { /* tokens object */ }
}
```

**Response (Registration):**
```json
{
  "verified": true,
  "identifier": "user@example.com",
  "message": "Verification successful"
}
```

---

## Protected Endpoints

**Authentication Required:** All endpoints below require `Authorization: Bearer {access_token}` header.

### 5. Get User Profile
**Endpoint:** `GET /user`

Returns authenticated user's profile.

**Response:**
```json
{
  "id": 1,
  "name": "John Doe",
  "username": "johndoe",
  "email": "user@example.com",
  "phone": "+989123456789",
  "email_verified_at": "2025-01-15T10:30:00.000000Z",
  "phone_verified_at": null,
  "avatar": "https://...",
  "roles": ["user"],
  "permissions": ["read:profile"],
  "can_change_username": true,
  "days_until_username_change": 0
}
```

---

### 6. Update Profile
**Endpoint:** `POST /profile/update`

Updates user profile information.

**Request:**
```json
{
  "name": "John Doe",
  "username": "newusername",      // Optional, can be changed once per year
  "email": "newemail@example.com", // Optional, requires verification
  "phone": "+989123456789",        // Optional, requires verification
  "avatar": "file"                 // Optional, multipart/form-data
}
```

**Response:**
```json
{
  "user": { /* updated user object */ },
  "message": "Profile updated successfully"
}
```

---

### 7. Set Password
**Endpoint:** `POST /password/set`

Sets password for OTP-only users.

**Request:**
```json
{
  "password": "NewSecurePass123!",
  "password_confirmation": "NewSecurePass123!"
}
```

**Response:**
```json
{
  "message": "Password set successfully"
}
```

---

### 8. Update Password
**Endpoint:** `POST /password/update`

Updates existing password.

**Request:**
```json
{
  "current_password": "OldPass123!",
  "password": "NewSecurePass123!",
  "password_confirmation": "NewSecurePass123!"
}
```

**Response:**
```json
{
  "message": "Password updated successfully"
}
```

---

### 9. Send Email Verification
**Endpoint:** `POST /email/send-verification`

Sends verification code to email.

**Response:**
```json
{
  "message": "Verification code sent to email",
  "expires_in": 300
}
```

---

### 10. Verify Email
**Endpoint:** `POST /email/verify`

Verifies email with OTP code.

**Request:**
```json
{
  "otp": "123456"
}
```

**Response:**
```json
{
  "message": "Email verified successfully",
  "user": { /* updated user object */ }
}
```

---

### 11. Send Phone Verification
**Endpoint:** `POST /phone/send-verification`

Sends verification code to phone.

**Response:**
```json
{
  "message": "Verification code sent to phone",
  "expires_in": 300
}
```

---

### 12. Verify Phone
**Endpoint:** `POST /phone/verify`

Verifies phone with OTP code.

**Request:**
```json
{
  "otp": "123456"
}
```

**Response:**
```json
{
  "message": "Phone verified successfully",
  "user": { /* updated user object */ }
}
```

---

### 13. Refresh Token
**Endpoint:** `POST /refresh`  
**Rate Limit:** 10/minute  
**Required Ability:** `token:refresh`

Refreshes access and refresh tokens using refresh token.

**Headers:**
```
Authorization: Bearer {refresh_token}
```

**Response:**
```json
{
  "tokens": {
    "access_token": "3|newtoken...",
    "refresh_token": "4|newrefresh...",
    "token_type": "Bearer",
    "expires_in": 7200,
    "expires_at": "2025-01-15T14:30:00.000000Z",
    "refresh_expires_in": 604800,
    "refresh_expires_at": "2025-01-22T12:30:00.000000Z"
  }
}
```

---

### 14. Logout
**Endpoint:** `POST /logout`

Revokes current access token.

**Response:**
```json
{
  "message": "Logged out successfully"
}
```

---

### 15. Logout All Devices
**Endpoint:** `POST /logout-all`

Revokes all user tokens.

**Response:**
```json
{
  "message": "Logged out from all devices",
  "tokens_revoked": 3
}
```

---

## Admin Endpoints

**Permission Required:** Specific permissions are required for each endpoint.

### User Management

#### 16. List Users
**Endpoint:** `GET /users`  
**Permission:** `users.view`

**Query Parameters:**
- `page` (int): Page number
- `per_page` (int): Items per page
- `search` (string): Search term
- `role` (string): Filter by role
- `verified` (boolean): Filter by verification status

**Response:**
```json
{
  "data": [
    { /* user object */ }
  ],
  "meta": {
    "current_page": 1,
    "total": 100,
    "per_page": 15
  }
}
```

---

#### 17. Get User
**Endpoint:** `GET /users/{id}`  
**Permission:** `users.view`

**Response:**
```json
{
  "user": { /* detailed user object */ }
}
```

---

#### 18. Get User Statistics
**Endpoint:** `GET /users/statistics`  
**Permission:** `users.view`

**Response:**
```json
{
  "total_users": 1000,
  "verified_users": 850,
  "locked_users": 10,
  "new_users_today": 15
}
```

---

#### 19. Update User
**Endpoint:** `PUT /users/{id}`  
**Permission:** `users.edit`

**Request:**
```json
{
  "name": "John Doe",
  "email": "user@example.com",
  "phone": "+989123456789",
  "is_admin": false
}
```

**Response:**
```json
{
  "user": { /* updated user object */ },
  "message": "User updated successfully"
}
```

---

#### 20. Toggle User Lock
**Endpoint:** `POST /users/{id}/toggle-lock`  
**Permission:** `users.edit`

**Request:**
```json
{
  "duration": 15  // Minutes, optional
}
```

**Response:**
```json
{
  "message": "User locked/unlocked",
  "locked": true,
  "locked_until": "2025-01-15T11:00:00.000000Z"
}
```

---

#### 21. Reset User Password
**Endpoint:** `POST /users/{id}/reset-password`  
**Permission:** `users.edit`

**Request:**
```json
{
  "password": "NewPass123!",
  "password_confirmation": "NewPass123!"
}
```

**Response:**
```json
{
  "message": "Password reset successfully"
}
```

---

#### 22. Verify User Email
**Endpoint:** `POST /users/{id}/verify-email`  
**Permission:** `users.edit`

**Response:**
```json
{
  "message": "Email verified successfully"
}
```

---

#### 23. Verify User Phone
**Endpoint:** `POST /users/{id}/verify-phone`  
**Permission:** `users.edit`

**Response:**
```json
{
  "message": "Phone verified successfully"
}
```

---

#### 24. Delete User
**Endpoint:** `DELETE /users/{id}`  
**Permission:** `users.delete`

**Response:**
```json
{
  "message": "User deleted successfully"
}
```

---

### Role Management

#### 25. List Roles
**Endpoint:** `GET /roles`  
**Permission:** `roles.view`

**Response:**
```json
{
  "roles": [
    {
      "id": 1,
      "name": "admin",
      "display_name": "Administrator",
      "description": "Full system access"
    }
  ]
}
```

---

#### 26. Get Role
**Endpoint:** `GET /roles/{id}`  
**Permission:** `roles.view`

**Response:**
```json
{
  "role": {
    "id": 1,
    "name": "admin",
    "display_name": "Administrator",
    "permissions": [ /* permission objects */ ]
  }
}
```

---

#### 27. Get Role Users
**Endpoint:** `GET /roles/{id}/users`  
**Permission:** `roles.view`

**Response:**
```json
{
  "users": [ /* user objects */ ]
}
```

---

#### 28. Create Role
**Endpoint:** `POST /roles`  
**Permission:** `roles.create`

**Request:**
```json
{
  "name": "editor",
  "display_name": "Content Editor",
  "description": "Can edit content"
}
```

**Response:**
```json
{
  "role": { /* created role object */ },
  "message": "Role created successfully"
}
```

---

#### 29. Update Role
**Endpoint:** `PUT /roles/{id}`  
**Permission:** `roles.edit`

**Request:**
```json
{
  "display_name": "Content Editor",
  "description": "Can edit content"
}
```

**Response:**
```json
{
  "role": { /* updated role object */ },
  "message": "Role updated successfully"
}
```

---

#### 30. Delete Role
**Endpoint:** `DELETE /roles/{id}`  
**Permission:** `roles.delete`

**Response:**
```json
{
  "message": "Role deleted successfully"
}
```

---

#### 31. Assign Role to User
**Endpoint:** `POST /roles/user/{userId}/assign`  
**Permission:** `users.manage_roles`

**Request:**
```json
{
  "role_id": 2
}
```

**Response:**
```json
{
  "message": "Role assigned successfully"
}
```

---

#### 32. Remove Role from User
**Endpoint:** `POST /roles/user/{userId}/remove`  
**Permission:** `users.manage_roles`

**Request:**
```json
{
  "role_id": 2
}
```

**Response:**
```json
{
  "message": "Role removed successfully"
}
```

---

### Permission Management

#### 33. List Permissions
**Endpoint:** `GET /permissions`  
**Permission:** `roles.view`

**Response:**
```json
{
  "permissions": [
    {
      "id": 1,
      "name": "users.view",
      "display_name": "View Users",
      "group": "users"
    }
  ]
}
```

---

#### 34. Get Role Permissions
**Endpoint:** `GET /permissions/role/{roleId}`  
**Permission:** `roles.view`

**Response:**
```json
{
  "permissions": [ /* permission objects */ ]
}
```

---

#### 35. Create Permission
**Endpoint:** `POST /permissions`  
**Permission:** `roles.edit`

**Request:**
```json
{
  "name": "posts.create",
  "display_name": "Create Posts",
  "group": "posts",
  "description": "Can create blog posts"
}
```

**Response:**
```json
{
  "permission": { /* created permission */ },
  "message": "Permission created successfully"
}
```

---

#### 36. Update Permission
**Endpoint:** `PUT /permissions/{id}`  
**Permission:** `roles.edit`

**Request:**
```json
{
  "display_name": "Create Posts",
  "description": "Can create blog posts"
}
```

**Response:**
```json
{
  "permission": { /* updated permission */ },
  "message": "Permission updated successfully"
}
```

---

#### 37. Update Role Permissions
**Endpoint:** `PUT /permissions/role/{roleId}`  
**Permission:** `roles.edit`

**Request:**
```json
{
  "permissions": [1, 2, 3, 4]  // Array of permission IDs
}
```

**Response:**
```json
{
  "message": "Role permissions updated successfully"
}
```

---

#### 38. Delete Permission
**Endpoint:** `DELETE /permissions/{id}`  
**Permission:** `roles.edit`

**Response:**
```json
{
  "message": "Permission deleted successfully"
}
```

---

## Error Responses

All endpoints may return the following error responses:

### Validation Error (422)
```json
{
  "message": "The given data was invalid",
  "errors": {
    "field_name": ["Error message"]
  }
}
```

### Unauthorized (401)
```json
{
  "message": "Unauthenticated"
}
```

### Forbidden (403)
```json
{
  "message": "Insufficient permissions"
}
```

### Not Found (404)
```json
{
  "message": "Resource not found"
}
```

### Rate Limit (429)
```json
{
  "message": "Too many requests"
}
```

### Server Error (500)
```json
{
  "message": "Internal server error"
}
```

---

## Password Requirements

- Minimum 8 characters
- At least one uppercase letter
- At least one lowercase letter
- At least one number
- At least one special character
- Must not be compromised (checked against breach database)

---

## Phone Number Format

Phone numbers are automatically normalized. Supported formats:

**Iranian Numbers:**
- `09123456789`
- `989123456789`
- `+989123456789`
- `00989123456789`

**International Numbers:**
- `+1234567890`
- `001234567890`

---

## OTP Configuration

- **Length:** 6 digits
- **Expiry:** 5 minutes (300 seconds)
- **Max Attempts:** 3 attempts per OTP
- **Rate Limit:** 1 send per minute, 3 per hour

---

## Token Configuration

- **Access Token Lifetime:** 2 hours (7200 seconds)
- **Refresh Token Lifetime:** 7 days (604800 seconds)
- **Token Type:** Bearer

---

## Health Check

**Endpoint:** `GET /auth/health`

**Response:**
```json
{
  "status": "ok",
  "service": "Auth Module",
  "timestamp": "2025-09-29T10:30:00.000000Z"
}
```
