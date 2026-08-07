# API Standards

## Overview

This document defines the API development standards used throughout the Laravel API Base project.

Following these standards ensures:

- Consistent API design
- Predictable request and response formats
- Better maintainability
- Easier frontend integration
- Scalable architecture

All API endpoints MUST follow these standards.

---

# API Versioning

All endpoints must be versioned.

Current version:

```
/api/v1
```

Examples:

```
GET    /api/v1/users

POST   /api/v1/auth/login

POST   /api/v1/auth/logout

GET    /api/v1/auth/me
```

Never expose endpoints without versioning.

❌

```
/api/users
```

✅

```
/api/v1/users
```

---

# REST API Conventions

Use RESTful naming conventions.

## Resource Names

Always use plural resource names.

Examples

```
users

companies

roles

permissions

customers

products
```

Avoid

```
user

company

role
```

---

# HTTP Methods

| Method | Description          |
| ------ | -------------------- |
| GET    | Retrieve resource(s) |
| POST   | Create resource      |
| PUT    | Replace resource     |
| PATCH  | Update resource      |
| DELETE | Delete resource      |

Examples

```
GET /users

GET /users/{id}

POST /users

PUT /users/{id}

PATCH /users/{id}

DELETE /users/{id}
```

---

# URL Naming

Use lowercase.

Use hyphens where required.

Examples

```
/user-profiles

/order-items

/payment-history
```

Avoid camelCase.

Avoid snake_case.

---

# UUID

All public resources should use UUID.

Example

```
GET /users/5d78204b-1db7-4d9b-bf2d-5337e65d3d12
```

Never expose auto-increment IDs in URLs.

---

# Request Headers

Required

```
Accept: application/json
```

Authenticated requests

```
Authorization: Bearer {token}
```

Content Type

```
Content-Type: application/json
```

---

# Authentication

Authentication uses Laravel Sanctum.

Login

```
POST /api/v1/auth/login
```

Logout

```
POST /api/v1/auth/logout
```

Profile

```
GET /api/v1/auth/me
```

---

# Standard Success Response

Every successful response MUST follow this structure.

```json
{
    "success": true,
    "status": 200,
    "message": "Request completed successfully.",
    "data": {},
    "errors": null,
    "meta": {}
}
```

---

# Standard Error Response

```json
{
    "success": false,
    "status": 400,
    "message": "Bad request.",
    "data": null,
    "errors": null,
    "meta": {}
}
```

---

# Validation Errors

Validation responses must return HTTP 422.

Example

```json
{
    "success": false,
    "status": 422,
    "message": "Validation failed.",
    "data": null,
    "errors": {
        "email": ["The email field is required."]
    },
    "meta": {}
}
```

---

# Pagination Response

Collection endpoints should return pagination metadata.

Example

```json
{
    "success": true,
    "status": 200,
    "message": "Users retrieved successfully.",
    "data": [{}],
    "errors": null,
    "meta": {
        "current_page": 1,
        "last_page": 5,
        "per_page": 20,
        "total": 96
    }
}
```

---

# Filtering

Use query parameters.

Example

```
GET /users?status=active
```

Multiple filters

```
GET /users?status=active&company=abc
```

---

# Searching

Use the search parameter.

Example

```
GET /users?search=john
```

---

# Sorting

Example

```
GET /users?sort=name
```

Descending

```
GET /users?sort=-created_at
```

---

# Includes

Relationships should be loaded using include.

Example

```
GET /users?include=roles
```

Multiple

```
GET /users?include=roles,company
```

---

# HTTP Status Codes

| Code | Meaning           |
| ---- | ----------------- |
| 200  | OK                |
| 201  | Created           |
| 204  | No Content        |
| 400  | Bad Request       |
| 401  | Unauthorized      |
| 403  | Forbidden         |
| 404  | Not Found         |
| 409  | Conflict          |
| 422  | Validation Error  |
| 429  | Too Many Requests |
| 500  | Server Error      |

---

# Naming Conventions

## JSON Keys

Use snake_case.

Example

```json
{
    "first_name": "John"
}
```

---

# Date Format

Use ISO-8601.

Example

```
2026-08-06T14:20:00Z
```

---

# Boolean Values

Always return actual booleans.

Correct

```json
{
    "active": true
}
```

Avoid

```json
{
    "active": "true"
}
```

---

# Null Values

Return null instead of empty strings where applicable.

Correct

```json
{
    "phone": null
}
```

Avoid

```json
{
    "phone": ""
}
```

---

# API Resources

Never return Eloquent models directly.

Always use API Resources.

Correct

```
UserResource
```

Avoid

```
return User::find(...)
```

---

# Validation

All validation must be handled using Form Requests.

Never validate inside Controllers.

Correct

```
UserStoreRequest

UserUpdateRequest
```

---

# Business Logic

Controllers must remain thin.

Business logic belongs in Services.

Correct

```
Controller

↓

Service

↓

Model
```

---

# Error Handling

All exceptions should be handled centrally.

Controllers should never return custom error responses.

---

# Testing

Every endpoint must include Feature Tests.

Tests should verify:

- Success responses
- Validation
- Authentication
- Authorization
- Database changes
- Error responses

---

# Performance

- Avoid N+1 queries.
- Use eager loading.
- Paginate collections.
- Select only required columns where appropriate.
- Cache expensive operations when justified.

---

# Security

- Authenticate protected endpoints.
- Authorize sensitive actions.
- Validate all user input.
- Never expose internal IDs.
- Never expose sensitive fields such as passwords or tokens.
- Rate-limit public endpoints.

---

# Documentation

Every new API endpoint must include:

- Feature test
- Form Request
- API Resource
- Service method
- Route definition
- cURL example
- README update (if applicable)

---

# API Design Principles

This project follows:

- RESTful design
- SOLID principles
- Separation of Concerns
- Service Layer Architecture
- Standardized Responses
- Versioned APIs
- Test-Driven Development
- Clean Code principles

Following these standards ensures the API remains consistent, scalable, maintainable, and easy to integrate across all future projects built from this template.
