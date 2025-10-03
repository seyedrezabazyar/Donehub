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
  }
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

## Authentication Flow

### 1. Register/Login
Send credentials to respective endpoint

### 2. Store Token
Save the returned token from login response

### 3. Use Token
Include in subsequent requests:
```
Authorization: Bearer {token}
```

---

## Error Codes

| Code | Description |
|------|-------------|
| 200  | Success |
| 201  | Created |
| 422  | Validation Error |
| 401  | Unauthorized |
| 500  | Server Error |
