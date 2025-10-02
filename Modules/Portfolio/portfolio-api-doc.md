# Portfolio API Documentation

## Base URL
```
https://api.example.com/api
```

## Authentication
All endpoints require Bearer token authentication.

```
Authorization: Bearer {token}
```

---

## Portfolio Endpoints

### 1. Get User Portfolio
```http
GET /portfolio
```

**Response 200:**
```json
{
  "data": {
    "id": 1,
    "user_id": 5,
    "title": "Senior Full Stack Developer",
    "bio": "Passionate developer with 5+ years of experience...",
    "avatar": "https://example.com/avatars/user.jpg",
    "website": "https://johndoe.com",
    "linkedin": "https://linkedin.com/in/johndoe",
    "github": "https://github.com/johndoe",
    "skills": [
      {
        "id": 1,
        "portfolio_id": 1,
        "name": "Laravel",
        "level": "Expert",
        "created_at": "2025-10-02T10:00:00.000000Z",
        "updated_at": "2025-10-02T10:00:00.000000Z"
      }
    ],
    "experiences": [
      {
        "id": 1,
        "portfolio_id": 1,
        "company": "Tech Corp",
        "role": "Senior Developer",
        "description": "Led development team...",
        "start_date": "2020-01-15",
        "end_date": "2023-05-20",
        "created_at": "2025-10-02T10:00:00.000000Z",
        "updated_at": "2025-10-02T10:00:00.000000Z"
      }
    ],
    "educations": [
      {
        "id": 1,
        "portfolio_id": 1,
        "institute": "University of Technology",
        "degree": "Bachelor",
        "field": "Computer Science",
        "start_date": "2015-09-01",
        "end_date": "2019-06-30",
        "created_at": "2025-10-02T10:00:00.000000Z",
        "updated_at": "2025-10-02T10:00:00.000000Z"
      }
    ],
    "projects": [
      {
        "id": 1,
        "portfolio_id": 1,
        "title": "E-commerce Platform",
        "description": "Built a scalable e-commerce solution...",
        "link": "https://project.com",
        "image": "https://example.com/projects/ecommerce.jpg",
        "created_at": "2025-10-02T10:00:00.000000Z",
        "updated_at": "2025-10-02T10:00:00.000000Z"
      }
    ],
    "created_at": "2025-10-02T10:00:00.000000Z",
    "updated_at": "2025-10-02T10:00:00.000000Z"
  }
}
```

**Response 404:**
```json
{
  "message": "Portfolio not found"
}
```

---

### 2. Create Portfolio
```http
POST /portfolio
```

**Request Body:**
```json
{
  "title": "Senior Full Stack Developer",
  "bio": "Passionate developer with 5+ years of experience...",
  "avatar": "https://example.com/avatars/user.jpg",
  "website": "https://johndoe.com",
  "linkedin": "https://linkedin.com/in/johndoe",
  "github": "https://github.com/johndoe"
}
```

**Validation Rules:**
- `title`: required, string, max:255
- `bio`: nullable, string
- `avatar`: nullable, string, max:255
- `website`: nullable, url, max:255
- `linkedin`: nullable, url, max:255
- `github`: nullable, url, max:255

**Response 201:**
```json
{
  "data": {
    "id": 1,
    "user_id": 5,
    "title": "Senior Full Stack Developer",
    "bio": "Passionate developer with 5+ years of experience...",
    "avatar": "https://example.com/avatars/user.jpg",
    "website": "https://johndoe.com",
    "linkedin": "https://linkedin.com/in/johndoe",
    "github": "https://github.com/johndoe",
    "skills": [],
    "experiences": [],
    "educations": [],
    "projects": [],
    "created_at": "2025-10-02T10:00:00.000000Z",
    "updated_at": "2025-10-02T10:00:00.000000Z"
  }
}
```

**Response 422:**
```json
{
  "message": "The title field is required.",
  "errors": {
    "title": [
      "The title field is required."
    ]
  }
}
```

---

### 3. Update Portfolio
```http
PUT /portfolio/{portfolio}
```

**Request Body:**
```json
{
  "title": "Lead Full Stack Developer",
  "bio": "Updated bio...",
  "avatar": "https://example.com/avatars/new-user.jpg",
  "website": "https://johndoe.dev",
  "linkedin": "https://linkedin.com/in/johndoe-updated",
  "github": "https://github.com/johndoe-updated"
}
```

**Validation Rules:**
- Same as Create Portfolio

**Response 200:**
```json
{
  "data": {
    "id": 1,
    "user_id": 5,
    "title": "Lead Full Stack Developer",
    "bio": "Updated bio...",
    "avatar": "https://example.com/avatars/new-user.jpg",
    "website": "https://johndoe.dev",
    "linkedin": "https://linkedin.com/in/johndoe-updated",
    "github": "https://github.com/johndoe-updated",
    "skills": [],
    "experiences": [],
    "educations": [],
    "projects": [],
    "created_at": "2025-10-02T10:00:00.000000Z",
    "updated_at": "2025-10-02T11:30:00.000000Z"
  }
}
```

---

### 4. Delete Portfolio
```http
DELETE /portfolio/{portfolio}
```

**Response 204:**
```
No Content
```

---

## Skills Endpoints

### 1. Get All Skills
```http
GET /portfolio/{portfolio}/skills
```

**Response 200:**
```json
{
  "data": [
    {
      "id": 1,
      "portfolio_id": 1,
      "name": "Laravel",
      "level": "Expert",
      "created_at": "2025-10-02T10:00:00.000000Z",
      "updated_at": "2025-10-02T10:00:00.000000Z"
    },
    {
      "id": 2,
      "portfolio_id": 1,
      "name": "Vue.js",
      "level": "Advanced",
      "created_at": "2025-10-02T10:00:00.000000Z",
      "updated_at": "2025-10-02T10:00:00.000000Z"
    }
  ]
}
```

---

### 2. Create Skill
```http
POST /portfolio/{portfolio}/skills
```

**Request Body:**
```json
{
  "name": "Laravel",
  "level": "Expert"
}
```

**Validation Rules:**
- `name`: required, string, max:255
- `level`: nullable, string, max:255

**Response 201:**
```json
{
  "data": {
    "id": 1,
    "portfolio_id": 1,
    "name": "Laravel",
    "level": "Expert",
    "created_at": "2025-10-02T10:00:00.000000Z",
    "updated_at": "2025-10-02T10:00:00.000000Z"
  }
}
```

---

### 3. Update Skill
```http
PUT /portfolio/{portfolio}/skills/{skill}
```

**Request Body:**
```json
{
  "name": "Laravel",
  "level": "Master"
}
```

**Validation Rules:**
- Same as Create Skill

**Response 200:**
```json
{
  "data": {
    "id": 1,
    "portfolio_id": 1,
    "name": "Laravel",
    "level": "Master",
    "created_at": "2025-10-02T10:00:00.000000Z",
    "updated_at": "2025-10-02T11:00:00.000000Z"
  }
}
```

---

### 4. Delete Skill
```http
DELETE /portfolio/{portfolio}/skills/{skill}
```

**Response 204:**
```
No Content
```

---

## Experiences Endpoints

### 1. Get All Experiences
```http
GET /portfolio/{portfolio}/experiences
```

**Response 200:**
```json
{
  "data": [
    {
      "id": 1,
      "portfolio_id": 1,
      "company": "Tech Corp",
      "role": "Senior Developer",
      "description": "Led development team...",
      "start_date": "2020-01-15",
      "end_date": "2023-05-20",
      "created_at": "2025-10-02T10:00:00.000000Z",
      "updated_at": "2025-10-02T10:00:00.000000Z"
    }
  ]
}
```

---

### 2. Create Experience
```http
POST /portfolio/{portfolio}/experiences
```

**Request Body:**
```json
{
  "company": "Tech Corp",
  "role": "Senior Developer",
  "description": "Led development team of 5 developers...",
  "start_date": "2020-01-15",
  "end_date": "2023-05-20"
}
```

**Validation Rules:**
- `company`: required, string, max:255
- `role`: required, string, max:255
- `description`: nullable, string
- `start_date`: nullable, date
- `end_date`: nullable, date, after_or_equal:start_date

**Response 201:**
```json
{
  "data": {
    "id": 1,
    "portfolio_id": 1,
    "company": "Tech Corp",
    "role": "Senior Developer",
    "description": "Led development team of 5 developers...",
    "start_date": "2020-01-15",
    "end_date": "2023-05-20",
    "created_at": "2025-10-02T10:00:00.000000Z",
    "updated_at": "2025-10-02T10:00:00.000000Z"
  }
}
```

---

### 3. Update Experience
```http
PUT /portfolio/{portfolio}/experiences/{experience}
```

**Request Body:**
```json
{
  "company": "Tech Corp Inc",
  "role": "Lead Developer",
  "description": "Updated description...",
  "start_date": "2020-01-15",
  "end_date": "2023-06-30"
}
```

**Validation Rules:**
- Same as Create Experience

**Response 200:**
```json
{
  "data": {
    "id": 1,
    "portfolio_id": 1,
    "company": "Tech Corp Inc",
    "role": "Lead Developer",
    "description": "Updated description...",
    "start_date": "2020-01-15",
    "end_date": "2023-06-30",
    "created_at": "2025-10-02T10:00:00.000000Z",
    "updated_at": "2025-10-02T11:00:00.000000Z"
  }
}
```

---

### 4. Delete Experience
```http
DELETE /portfolio/{portfolio}/experiences/{experience}
```

**Response 204:**
```
No Content
```

---

## Educations Endpoints

### 1. Get All Educations
```http
GET /portfolio/{portfolio}/educations
```

**Response 200:**
```json
{
  "data": [
    {
      "id": 1,
      "portfolio_id": 1,
      "institute": "University of Technology",
      "degree": "Bachelor",
      "field": "Computer Science",
      "start_date": "2015-09-01",
      "end_date": "2019-06-30",
      "created_at": "2025-10-02T10:00:00.000000Z",
      "updated_at": "2025-10-02T10:00:00.000000Z"
    }
  ]
}
```

---

### 2. Create Education
```http
POST /portfolio/{portfolio}/educations
```

**Request Body:**
```json
{
  "institute": "University of Technology",
  "degree": "Bachelor",
  "field": "Computer Science",
  "start_date": "2015-09-01",
  "end_date": "2019-06-30"
}
```

**Validation Rules:**
- `institute`: required, string, max:255
- `degree`: nullable, string, max:255
- `field`: nullable, string, max:255
- `start_date`: nullable, date
- `end_date`: nullable, date, after_or_equal:start_date

**Response 201:**
```json
{
  "data": {
    "id": 1,
    "portfolio_id": 1,
    "institute": "University of Technology",
    "degree": "Bachelor",
    "field": "Computer Science",
    "start_date": "2015-09-01",
    "end_date": "2019-06-30",
    "created_at": "2025-10-02T10:00:00.000000Z",
    "updated_at": "2025-10-02T10:00:00.000000Z"
  }
}
```

---

### 3. Update Education
```http
PUT /portfolio/{portfolio}/educations/{education}
```

**Request Body:**
```json
{
  "institute": "MIT",
  "degree": "Master",
  "field": "Software Engineering",
  "start_date": "2019-09-01",
  "end_date": "2021-06-30"
}
```

**Validation Rules:**
- Same as Create Education

**Response 200:**
```json
{
  "data": {
    "id": 1,
    "portfolio_id": 1,
    "institute": "MIT",
    "degree": "Master",
    "field": "Software Engineering",
    "start_date": "2019-09-01",
    "end_date": "2021-06-30",
    "created_at": "2025-10-02T10:00:00.000000Z",
    "updated_at": "2025-10-02T11:00:00.000000Z"
  }
}
```

---

### 4. Delete Education
```http
DELETE /portfolio/{portfolio}/educations/{education}
```

**Response 204:**
```
No Content
```

---

## Projects Endpoints

### 1. Get All Projects
```http
GET /portfolio/{portfolio}/projects
```

**Response 200:**
```json
{
  "data": [
    {
      "id": 1,
      "portfolio_id": 1,
      "title": "E-commerce Platform",
      "description": "Built a scalable e-commerce solution...",
      "link": "https://project.com",
      "image": "https://example.com/projects/ecommerce.jpg",
      "created_at": "2025-10-02T10:00:00.000000Z",
      "updated_at": "2025-10-02T10:00:00.000000Z"
    }
  ]
}
```

---

### 2. Create Project
```http
POST /portfolio/{portfolio}/projects
```

**Request Body:**
```json
{
  "title": "E-commerce Platform",
  "description": "Built a scalable e-commerce solution with Laravel and Vue.js",
  "link": "https://project.com",
  "image": "https://example.com/projects/ecommerce.jpg"
}
```

**Validation Rules:**
- `title`: required, string, max:255
- `description`: nullable, string
- `link`: nullable, url, max:255
- `image`: nullable, string, max:255

**Response 201:**
```json
{
  "data": {
    "id": 1,
    "portfolio_id": 1,
    "title": "E-commerce Platform",
    "description": "Built a scalable e-commerce solution with Laravel and Vue.js",
    "link": "https://project.com",
    "image": "https://example.com/projects/ecommerce.jpg",
    "created_at": "2025-10-02T10:00:00.000000Z",
    "updated_at": "2025-10-02T10:00:00.000000Z"
  }
}
```

---

### 3. Update Project
```http
PUT /portfolio/{portfolio}/projects/{project}
```

**Request Body:**
```json
{
  "title": "Advanced E-commerce Platform",
  "description": "Updated description...",
  "link": "https://new-project.com",
  "image": "https://example.com/projects/new-ecommerce.jpg"
}
```

**Validation Rules:**
- Same as Create Project

**Response 200:**
```json
{
  "data": {
    "id": 1,
    "portfolio_id": 1,
    "title": "Advanced E-commerce Platform",
    "description": "Updated description...",
    "link": "https://new-project.com",
    "image": "https://example.com/projects/new-ecommerce.jpg",
    "created_at": "2025-10-02T10:00:00.000000Z",
    "updated_at": "2025-10-02T11:00:00.000000Z"
  }
}
```

---

### 4. Delete Project
```http
DELETE /portfolio/{portfolio}/projects/{project}
```

**Response 204:**
```
No Content
```

---

## Error Responses

### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```

### 404 Not Found
```json
{
  "message": "Not Found"
}
```

### 422 Validation Error
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field_name": [
      "Error message here"
    ]
  }
}
```

### 500 Server Error
```json
{
  "message": "Server Error"
}
```
