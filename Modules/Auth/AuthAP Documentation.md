# Auth API Documentation

## Base URL
```
/api/auth
```

---

## 1. Register

### Endpoint
```
POST /api/auth/register
```

### Request Headers
```
Content-Type: application/json
Accept: application/json
```

### Request Body
```json
{
    "username": "john_doe",
    "password": "123456"
}
```

### Validation Rules
- `username`: required, string, max 255 characters, unique
- `password`: required, string, min 6 characters

### Success Response (201)
```json
{
    "message": "User registered successfully",
    "user": {
        "id": 1,
        "username": "john_doe",
        "created_at": "2025-01-02T14:50:52.000000Z"
    },
    "token": "1|abc123xyz789..."
}
```

### Error Response (422)
```json
{
  "message": "The username has already been taken.",
  "errors": {
    "username": [
      "The username has already been taken."
    ]
  }
}
```

---

## 2. Login

### Endpoint
```
POST /api/auth/login
```

### Request Headers
```
Content-Type: application/json
Accept: application/json
```

### Request Body
```json
{
  "username": "john_doe",
  "password": "123456"
}
```

### Validation Rules
- `username`: required, string
- `password`: required, string

### Success Response (200)
```json
{
  "message": "Login successful",
  "user": {
    "id": 1,
    "username": "john_doe",
    "created_at": "2025-01-02T14:50:52.000000Z"
  },
  "token": "1|abc123xyz789..."
}
```

### Error Response (422)
```json
{
  "message": "The provided credentials are incorrect.",
  "errors": {
    "username": [
      "The provided credentials are incorrect."
    ]
  }
}
```

---

## 3. Logout

### Endpoint
```
POST /api/auth/logout
```

### Request Headers
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer {token}
```

### Success Response (200)
```json
{
  "message": "Logout successful"
}
```

### Error Response (401)
```json
{
  "message": "Unauthenticated."
}
```

---

## Authentication Flow

### 1. Register/Login
Send credentials to respective endpoint

### 2. Store Token
Save the returned token from login/register response

### 3. Use Token
Include in subsequent requests:
```
Authorization: Bearer {token}
```

### 4. Logout
Send token to logout endpoint to invalidate it

---

## Error Codes

| Code | Description |
|------|-------------|
| 200  | Success |
| 201  | Created |
| 401  | Unauthorized |
| 422  | Validation Error |
| 500  | Server Error |

---

## Important Notes

1. **Token Management**:
    - Both register and login endpoints return a token
    - Store the token securely in client-side storage
    - Include token in Authorization header for protected routes

2. **Security**:
    - Passwords are automatically hashed using Laravel's hashing
    - Tokens are managed by Laravel Sanctum
    - Logout invalidates the current token

3. **Middleware**:
    - Register and Login are public routes
    - Logout requires authentication (auth:sanctum middleware)
